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
 * * Arguments: If argument email is passed script will send emails, if argument no_email script will not send emails
 * * Out: Email and No email send CSV
 * *
 * *-------------------------------------------------------------------------------------------------------------------------------------
 * * 01/11/21   JL  Created Script
 * *
 * *
***/
    include_once('./config.php');
    include_once('autoload.php');
    include_once( './libs/PHPMailer/PHPMailerAutoload.php' );

    date_default_timezone_set('America/Los_Angeles');

    global $appconfig, $logger;

    $logger = new ILog($appconfig['order_email_delay']['logger']['username'], sprintf( $appconfig['order_email_delay']['logger']['log_name'], date('ymdhms')), $appconfig['order_email_delay']['logger']['log_folder'], $appconfig['order_email_delay']['logger']['priority']);

    $sendEmail = ($argc > 1 ? ($argv[1] == 'email') ? true : false : false  );
    $host = array( 'host' => $appconfig['order_email_delay']['email']['host'], 'port' => $appconfig['order_email_delay']['email']['port'] );
    $from  = array( 'from' => $appconfig['order_email_delay']['email']['from'], 'name' => $appconfig['order_email_delay']['email']['name'] );

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
    $logger->debug( print_r($ordersDelayed, 1) );
    
    $emailsNotSent = [];
    $emailsSent = [];
    foreach( $ordersDelayed as $order ){
        //Only records with name and email set 
        if ( $order['NAME'] == '' || $order['EMAIL_ADDR'] == '' ){
            $logger->debug( "Data inconsistency found" );
            $logger->debug( print_r($order, 1) );
            array_push( $emailsNotSent, $order );
            continue;
        }
        //Validate email address
        if ( !filter_var($order['EMAIL_ADDR'], FILTER_VALIDATE_EMAIL) ){ 
            $logger->debug( "Email invalid " . $order['EMAIL_ADDR'] . " for " . $order['CUST_CD'] );
            array_push( $emailsNotSent, $order );
            continue;
        }
        if( $sendEmail ) {
            $body = getEmailBody( $appconfig['order_email_delay']['replacements'], $order, $appconfig['order_email_delay']['email_body'] );
            $message = array( 'subject' => $appconfig['order_email_delay']['email']['subject'], 'body' => $body );
            $to = array($order['EMAIL_ADDR']);
            $error = $mor->email( $host, $to, $from, [], $message );
            if( $error ){
                $logger->debug( "Email was not sent for: " . print_r($order, 1) );
                array_push( $emailsNotSent, $order );
                continue;
            }
            $error = postCustomerDelayComment( $db, $order['CUST_CD'] );
            if( !$error ) exit(1); 
            
            //Query SO First for sales order details
            $sale = getSalesOrder( $db, $order['DEL_DOC_NUM'] );
            $error = postSalesOrderDelayComment( $db, $sale );
            if( !$error ) exit(1); 

            //Update customer 
            $updt = updateCustomerNotificationDate( $db, $order['CUST_CD'] );
            if( !$updt ) $logger->error( "Fail update SO" );
        }
        //Save sent emails
        array_push( $emailsSent, $order );
        

    } 

    //Generate csv for emails sent
    $filename = sprintf( $appconfig['order_email_delay']['email_sent_filename'], date('YmdHis' ));
    $error_filename = sprintf( $appconfig['order_email_delay']['error_filename'], date('YmdHis' ));
    $attachments = array( $appconfig['order_email_delay']['out'] . $filename, $appconfig['order_email_delay']['out'] . $error_filename  );

    if( $mor->generateCSV($appconfig['order_email_delay']['out'], $filename, [ 'DEL_DOC_NUM', 'CUST_CD', 'EMAIL_ADDR', 'NAME' ], $emailsSent) ){
        $logger->debug( "Error generating csv" );
        exit(1);
    }
    
    if( count($emailsNotSent) > 0 ){
        //Email errors  
        if( $mor->generateCSV($appconfig['order_email_delay']['out'], $error_filename, [ 'DEL_DOC_NUM', 'CUST_CD', 'EMAIL_ADDR', 'NAME' ], $emailsNotSent) ){
            $logger->debug( "Error generating csv" );
            exit(1);
        }
        
    }
    $to = array( $appconfig['order_email_delay']['email_error_to'] );
    if( !$mor->email( $host, $to, $from, $attachments, $appconfig['order_email_delay']['email_error_message'] )){
        $logger->error( 'Error email was not sent' );
    }


    /*********************************************************************************************************************************************
    /*********************************************************************************************************************************************
    /*********************************************************************************************************************************************
     * * updateCustomerNotificationDate: 
     * *    Function will update notification date in cust table 
     * * Arguments: 
     * *    db: Database connection   
     * *    customerCode: Customer Code
     * *
     * * Return: Boolean true else otherwise 
     * *
     *********************************************************************************************************************************************
     *********************************************************************************************************************************************
     *********************************************************************************************************************************************/
    function updateCustomerNotificationDate( $db, $customerCode ){
        global $appconfig, $logger; 
        
        $now = new IDate(); 
        $cust = new Cust( $db );
        $where = "WHERE CUST_CD = '" . $customerCode . "' ";

        $result = $cust->query($where);
        if( $result < 0 ){
            $logger->debug( 'Could not find customer info: ' . $customerCode  );
            exit(1);
        }
        
        $cust->set_SO_DELAY_NOTIFICATION_DT( $now->toStringOracle() );

        $updt = $cust->update( $where, false );
        if( $updt < 0 ){
            $logger->error( "Could not update customer SO_DELAY_NOTIFY_DT" );
            return false;
        }

        return true;

    }

    /*********************************************************************************************************************************************
    /*********************************************************************************************************************************************
    /*********************************************************************************************************************************************
     * * getSalesOrder: 
     * *    Function will query SO for DEL_DOC_NUM 
     * * Arguments: 
     * *    db: Database connection   
     * *    sale: Customer and sales info
     * *
     * * Return: Array with sales order information 
     * *
     *********************************************************************************************************************************************
     *********************************************************************************************************************************************
     *********************************************************************************************************************************************/
    function getSalesOrder( $db, $delDocNum ){
        global $appconfig, $logger;

        $so = new SalesOrder($db);
        $where  = "WHERE DEL_DOC_NUM = '" . $delDocNum . "' ";
        $result = $so->query( $where );

        if( $result < 0 ){
            $logger->error( "Could not query table SO" );
            exit(1);
        }
        
        $sale = [];
        while( $so->next() ){
            $sale['SO_WR_DT'] = $so->get_SO_WR_DT('d-M-Y');
            $sale['SO_STORE_CD'] = $so->get_SO_STORE_CD();
            $sale['SO_SEQ_NUM'] = $so->get_SO_SEQ_NUM();
            $sale['DEL_DOC_NUM'] = $delDocNum;
            $sale['ORIGIN_CD'] = $so->get_ORIGIN_CD();
        }

        return $sale;

            
    }

    /*********************************************************************************************************************************************
    /*********************************************************************************************************************************************
    /*********************************************************************************************************************************************
     * * postSalesOrderDelayComment: 
     * *    Function will save order delay comment 
     * * Arguments: 
     * *    db: Database connection   
     * *    so: Sales Order info
     * *
     * * Return: Boolean true succesful else otherwise 
     * *
     *********************************************************************************************************************************************
     *********************************************************************************************************************************************
     *********************************************************************************************************************************************/
    function postSalesOrderDelayComment( $db, $sale ){
        global $appconfig, $logger;
        
        $now = new IDate();
        $soWrDt = new IDate();
        $seq = getMaxSeqSoComment( $db, $sale );
        $soWrDt->setDate( $sale['SO_WR_DT'] );

        $soComment = new SOComment($db);
        $soComment->set_SO_WR_DT( $soWrDt->toStringOracle() );
        $soComment->set_SO_STORE_CD( $sale['SO_STORE_CD'] );
        $soComment->set_SO_SEQ_NUM( $sale['SO_SEQ_NUM'] );
        $soComment->set_DT( $now->toStringOracle() );
        $soComment->set_TEXT( sprintf($appconfig['order_email_delay']['so_comment_msg'], $sale['DEL_DOC_NUM']) );
        $soComment->set_DEL_DOC_NUM( $sale['DEL_DOC_NUM'] );
        $soComment->set_CMNT_TYPE( $appconfig['order_email_delay']['cmnt_type'] );
        $soComment->set_ORIGIN_CD( $sale['ORIGIN_CD'] );
        $col = "set_SEQ#";
        $soComment->$col( $seq );
        //$soComment->set_EMP_CD();
        //$soComment->set_XPOS_UPDATEABLE( $now->toStringOracle() );
        //$soComment->set_PERM( $now->toStringOracle() );

        $result = $soComment->insert( false, false );
        if( !$result ) {
            $logger->error( "INSERT into SO_CMNT failed" );
            return false;
        }

        return true;



    }

    /*********************************************************************************************************************************************
    /*********************************************************************************************************************************************
    /*********************************************************************************************************************************************
     * * postCustomerDelayComment: 
     * *    Function will create comment into cust comment 
     * * Arguments: 
     * *    db: Database connection   
     * *    custCd: Customer code 
     * *
     * * Return: Int Max sequence number 
     * *
     *********************************************************************************************************************************************
     *********************************************************************************************************************************************
     *********************************************************************************************************************************************/
    function postCustomerDelayComment( $db, $custCd ){
        global $appconfig, $logger;

        $now = new IDate();
        $seq = getMaxSeqCustComment( $db, $custCd );

        //Insert into customer comment that we contact them
        $custComment = new CustComment($db);
        $custComment->set_CUST_CD( $custCd );
        $custComment->set_CMNT_DT( $now->toStringOracle() );
        $col = "set_SEQ#";
        $custComment->$col( $seq );
        $custComment->set_TEXT( $appconfig['order_email_delay']['cust_comment'] );
        $custComment->set_EMP_CD_OP( $appconfig['order_email_delay']['emp_cd'] );

        $result = $custComment->insert( false, false );
        
        if( !$result ) {
            $logger->error( "INSERT into CUST_COMMENT failed" );
            return false;
        }
        return true;

    }
    
    /*********************************************************************************************************************************************
    /*********************************************************************************************************************************************
    /*********************************************************************************************************************************************
     * * getMaxSeqCustComment: 
     * *    Function will max sequence number for customer comment 
     * * Arguments: 
     * *    db: Database connection   
     * *    custCd: Customer code 
     * *
     * * Return: Int Max sequence number 
     * *
     *********************************************************************************************************************************************
     *********************************************************************************************************************************************
     *********************************************************************************************************************************************/
    function getMaxSeqCustComment( $db, $custCd ){
        global $appconfig, $logger;

        $max = new MaxCustCmnt($db);
        $where = "WHERE CUST_CD = '" . $custCd . "' ";
        $result = $max->query( $where );

        if( $result < 0 ){
            $logger->debug( "Could not get mas sequence cust comment" );
        }

        while( $max->next() ){
            return $max->get_MAX_SEQ();
        }

        return 1;



    }
    /*********************************************************************************************************************************************
    /*********************************************************************************************************************************************
    /*********************************************************************************************************************************************
     * * getMaxSeqSoComment: 
     * *    Function will get max seq number from so comment 
     * * Arguments: 
     * *    db: Database connection   
     * *    delDocNum: DEL_DOC_NUM 
     * *
     * * Return: (Int) Max sequence number null otherwise 
     * *
     *********************************************************************************************************************************************
     *********************************************************************************************************************************************
     *********************************************************************************************************************************************/
    function getMaxSeqSoComment( $db, $sale ){
        global $appconfig, $logger;
        
        $max = new MaxSoCmnt( $db );
        $where = "WHERE DEL_DOC_NUM = '" . $sale['DEL_DOC_NUM'] . "' AND SO_WR_DT = '" . $sale['SO_WR_DT'] . "' AND SO_STORE_CD = '" . $sale['SO_STORE_CD'] . "' ";
        $result = $max->query( $where );

        if( $result < 0 ){
            $logger->error( "Could not query table SO_CMNT");
            exit(1);
        }

        while( $max->next() ){
            return is_null($max->get_MAX_SEQ()) ? 1 : $max->get_MAX_SEQ(); 
        }
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
                    AND SO_LN.VOID_FLAG = 'N' 
                    AND SO_LN.QTY > 0 
                    AND SO_LN.EST_FILL_DT > SYSDATE + " . $params['ACCEPTABLE_DAYS_TO_WAIT_FOR_INV'] . "
                    AND SO_LN.ITM_CD = ITM.ITM_CD 
                    AND ITM.ITM_TP_CD = 'INV'";
        $postclause = "GROUP BY non_splitorders.DEL_DOC_NUM, SO_LN.ITM_CD, CUST_CD, EMAIL_ADDR, NAME ORDER BY CUST_CD"; 

        $result = $delays->query( $where, $postclause );

        if ( $result < 0 ){
            $logger->error( "Could not query for order delays" );
            exit(1);
        }

        $orders = []; 
        while ( $delays->next() ){
            $tmp = [];
            $tmp['DEL_DOC_NUM'] = $delays->get_DEL_DOC_NUM();
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

