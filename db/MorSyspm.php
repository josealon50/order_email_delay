<?php

class MorSyspm extends IDBTable {
	public function __construct($db) {
		parent::__construct($db);

        $this->tablename        = 'MOR_SYSPM';

        $this->dbcolumns        = array(     'ID' => 'ID'  
                                            ,'SUBSYSTEM' => 'SUBSYSTEM' 
                                            ,'PARAMETER' => 'PARAMETER'
                                            ,'VALUE' => 'VALUE'
                                            ,'CREATED_AT' => 'CREATED_AT'
                                            ,'UPDATED_AT' => 'UPDATED_AT'
                                    );
		$this->dbcolumns_date    = array(
                                           "UPDATED_AT"
                                         , "CREATED_AT"
                                        );

		$this->setAutoIDColumn("ID");

	}
}

?>
