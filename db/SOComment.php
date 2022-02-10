<?php

class SOComment extends IDBTable {
	function __construct($db) {
		parent::__construct($db);
		$this->tablename = 'SO_CMNT';
		$this->dbcolumns = array(
                        'SO_WR_DT'=>'SO_WR_DT',
                        'SO_STORE_CD'=>'SO_STORE_CD',
                        'SO_SEQ_NUM'=>'SO_SEQ_NUM',
                        'SEQ#'=>'SEQ#',
                        'DT'=>'DT',
                        'TEXT'=>'TEXT',
                        'DEL_DOC_NUM'=>'DEL_DOC_NUM',
                        'PERM'=>'PERM',
                        'CMNT_TYPE'=>'CMNT_TYPE',
                        'XPOS_UPDATEABLE'=>'XPOS_UPDATEABLE',
                        'EMP_CD'=>'EMP_CD',
                        'ORIGIN_CD'=>'ORIGIN_CD',
		);

		$this->dbcolumns_date = array( 'DT' );

		$this->setAutoIDColumn('SO_WR_DT');
	}
}
