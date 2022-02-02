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

    $logger = new ILog($appconfig['order_email_delay']['logger']['username'], date('ymdhms') . ".log", $appconfig['order_email_delay']['logger']['log_folder'], $appconfig['order_email_delay']['logger']['priority']);

    $mor = new MorCommon();
    $db = $mor->standAloneAppConnect();
    if( !$db ){
        $logger->debug( "Cannot connect to database" );
    }

    $params = getOrderEmailSystemParams($db);





    /*********************************************************************************************************************************************
    /*********************************************************************************************************************************************
    /*********************************************************************************************************************************************
     * * getOrderEmailSystemParams: 
     * *    Function will return system parameters for order email delay 
     * * Arguments: 
     * *    db: Database connection  
     * *
     * * Return: Array with order email delay paramenters 
     * *
     *********************************************************************************************************************************************
     *********************************************************************************************************************************************
     *********************************************************************************************************************************************/
    function getOrderEmailSystemParams( $db ){
        global $appconfig, $logger;

        $syspm = new MorSyspm($db);     
        $where = "WHERE PARAMETER IN ( " . $appconfig['order_email_delay']['params'] . " ) AND SUBSYSTEM = 'MOR'";
        echo($where);
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

