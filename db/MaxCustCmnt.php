<?php

class MaxCustCmnt extends IDBTable {
    function __construct($db) {
        parent::__construct($db);

        $this->tablename = 'CUST_CMNT';

        $this->dbcolumns = array(
                'MAX_SEQ'=>'MAX_SEQ'
        );  
        $this->dbcolumns_function = array(
                'MAX_SEQ'=>'MAX(SEQ#)+1 MAX_SEQ'
        );  
    }   
}
