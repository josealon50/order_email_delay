<?php

class CustComment extends IDBTable {
	public function __construct($db) {
		parent::__construct($db);

        $this->tablename        = 'CUST_CMNT';

        $this->dbcolumns        = array(     'CUST_CD' => 'CUST_CD'  
                                            ,'SEQ#' => 'SEQ#' 
                                            ,'CMNT_DT' => 'CMNT_DT'
                                            ,'TEXT' => 'TEXT'
                                            ,'EMP_CD_OP' => 'EMP_CD_OP'
                                    );
		$this->dbcolumns_date    = array(
                                           "CMNT_DT"
                                        );


	}
}

?>
