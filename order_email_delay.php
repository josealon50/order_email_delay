<?php
/**
 * * Order Email Delay: Email to customers that notifies that order is delayed due to current supply chain issues. Script will identify 
 * * customers that have the following conditions. 
 * *    - Orders are still open
 * *    - Orders are not SPLIT
 * *    - Only SAL orders
 * *    - Sales only written in showrooms.
 * *    - Order is at least 30 days old.
 * *    - It has been 180 day since the customer was notified.
 * *    - Order has at least one line that does not have reserver quantity
 * *    - Order will not be filled in the next 14 days.
 * * 
 * * Arguments: 
 * * Out: 
 * *
 * *-------------------------------------------------------------------------------------------------------------------------------------
 * * 01/11/21   JL  Created Script
 * *
 * *
***/
    include_once('../config.php');
    include_once('autoload.php');

    date_default_timezone_set('America/Los_Angeles');

    global $appconfig, $logger;

    $logger = new ILog($appconfig['order_email_delay']['logger']['username'], sprintf( $appconfig['order_email_delay']['logger']['log_name'], date('ymdhms')), $appconfig['order_email_delay']['logger']['log_folder'], $appconfig['order_email_delay']['logger']['priority']);

    $mor = new MorCommon();
    $db = $mor->standAloneAppConnect();
    if( !$db ){
        $logger->debug( "Cannot connect to database" );
    }

    $logger->debug( "Querying order email system params" );
    $params = getOrderEmailSystemParams( $db );
    $logger->debug( print_r( $params, 1 ) );

    $logger->debug( "Querying for delayed orders" );
    $ordersDelayed = getOrdersDelayed( $db, $params ); 
    
    foreach( $ordersDelayed as $order ){
        //Only records with name and email set 
        if ( $order['NAME'] == '' || $order['EMAIL_ADDR'] == '' ){
            $logger->debug( "Data inconsistency found" );
            $logger->debug( print_r($order, 1) );
            continue;
        }
        //Validate email address
        if ( !filter_var($order['EMAIL_ADDR'], FILTER_VALIDATE_EMAIL) ){ 
            $logger->debug( "Email invalid " . $order['EMAIL_ADDR'] . " for " . $order['CUST_CD'] );
            continue;
        }

        $body = getEmailBody( $appconfig['order_email_delay']['replacements'], $order, $appconfig['order_email_delay']['email_body'] );

    } 
    
    
    /*********************************************************************************************************************************************
    /*********************************************************************************************************************************************
    /*********************************************************************************************************************************************
     * * getEmailBody: 
     * *    Function will return body of email with variables replaced 
     * * Arguments: 
     * *    replacements: Replacement variables   
     * *    data: Variables data 
     * *    body: Body of email with replacement variable data
     * *
     * * Return: Body of email with replacement variables replaced
     * *
     *********************************************************************************************************************************************
     *********************************************************************************************************************************************
     *********************************************************************************************************************************************/
    function getEmailBody( $replacements, $data, $body ){
        global $appconfig, $logger;

        foreach( $replacements as $key => $value ){
            $body = str_replace( $value, $data[$key], $body ); 
        }

        return $body;
    }
    
    /*********************************************************************************************************************************************
    /*********************************************************************************************************************************************
    /*********************************************************************************************************************************************
     * * getOrdersDelayed: 
     * *    Function will return orders that are delayed 
     * * Arguments: 
     * *    db: Database connection  
     * *    params: Array of system params
     * *
     * * Return: Array with order delayed 
     * *
     *********************************************************************************************************************************************
     *********************************************************************************************************************************************
     *********************************************************************************************************************************************/
    function getOrdersDelayed( $db, $params ) {
        global $appconfig, $logger;
        
        $delays = new OrderEmailDelay( $db, $params['SO_AGE_IN_DAYS_TO_NOTIFY_OF_DELAY'], $params['CUST_DAYS_BETWEEN_ORDER_DELAY_NOTICE'] );
        $where = "WHERE non_splitorders.DEL_DOC_NUM = SO_LN.DEL_DOC_NUM 
                    AND SO_LN.VOID_FLAG = 'N' AND SO_LN.QTY > 0 
                    AND SO_LN.EST_FILL_DT > SYSDATE + " . $params['ACCEPTABLE_DAYS_TO_WAIT_FOR_INV'] . "
                    AND SO_LN.ITM_CD = ITM.ITM_CD 
                    AND ITM.ITM_TP_CD = 'INV'";
        $postclause = "ORDER BY CUST_CD"; 

        $result = $delays->query( $where, $postclause );
        if ( $result < 0 ){
            $logger->error( "Could not query for order delays" );
            exit(1);
        }

        $orders = []; 
        while ( $delays->next() ){
            $tmp = [];
            $tmp['CUST_CD'] = $delays->get_CUST_CD();
            $tmp['EMAIL_ADDR'] = $delays->get_EMAIL_ADDR();
            $tmp['NAME'] = $delays->get_NAME();

            array_push( $orders, $tmp );
        }

        return $orders;

    }

    /*********************************************************************************************************************************************
    /*********************************************************************************************************************************************
    /*********************************************************************************************************************************************
     * * getOrderEmailSystemParams: 
     * *    Function will return system parameters for order email delay 
     * * Arguments: 
     * *    db: Database connection  
     * *
     * * Return: Array with order email delay parameters 
     * *
     *********************************************************************************************************************************************
     *********************************************************************************************************************************************
     *********************************************************************************************************************************************/
    function getOrderEmailSystemParams( $db ){
        global $appconfig, $logger;

        $syspm = new MorSyspm($db);     
        $where = "WHERE PARAMETER IN ( " . $appconfig['order_email_delay']['params'] . " ) AND SUBSYSTEM = 'MOR'";
        $result = $syspm->query( $where );
        if( $result < 0 ){
            $logger->error( "Could not query table MOR_SYSPM" );
            exit(1);
        }

        $parameters = array();
        while( $syspm->next() ){
            $parameters[$syspm->get_PARAMETER()] = $syspm->get_VALUE();
        }

        return $parameters;

    }


?>

