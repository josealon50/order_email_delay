<?php

class MaxSoCmnt extends IDBTable {
    function __construct($db) {
        parent::__construct($db);

        $this->tablename = 'SO_CMNT';

        $this->dbcolumns = array(
                'MAX_SEQ'=>'MAX_SEQ'
        );  
        $this->dbcolumns_function = array(
                'MAX_SEQ'=>'MAX(SEQ#)+1 MAX_SEQ'
        );  
    }   
}
