<?php

class OrderEmailDelay extends IDBTable {
	protected $withClause;

	public function __construct($db, $soAgeInDaysToNotifyOfDelay, $custDaysBetweenORderDelayNotice ) {
		parent::__construct($db);

        $this->withClause =  "  WITH non_splitorders AS ( 
                                    SELECT SO_DOC_NUM
                                         , DEL_DOC_NUM
                                         , SO.CUST_CD
                                         , CUST.EMAIL_ADDR
                                         , INITCAP(CUST.FNAME) || ' ' || INITCAP(CUST.LNAME) NAME
                                         , COUNT(*)
                                    FROM SO, STORE, CUST
                                    WHERE SO.STAT_CD = 'O'
                                    AND SO.ORD_TP_CD = 'SAL'
                                    AND SYSDATE - SO_WR_DT >= $soAgeInDaysToNotifyOfDelay
                                    AND SO.SO_STORE_CD = STORE.STORE_CD
                                    AND STORE.STORE_TP_CD = 'S'
                                    AND STORE.STORE_CD <> '00'
                                    AND SO.CUST_CD = CUST.CUST_CD
                                     AND SYSDATE – CUST.SO_DELAY_NOTIFY_DT >= $custDaysBetweenORderDelayNotice
                                    GROUP BY SO_DOC_NUM
                                           , DEL_DOC_NUM
                                           , SO.CUST_CD
                                           , CUST.EMAIL_ADDR
                                           , INITCAP(CUST.FNAME) || ' ' || INITCAP(CUST.LNAME)
                                    HAVING COUNT(*) = 1
                                )";

        $this->tablename        = 'non_splitorders, so_ln, itm ';

		$this->dbcolumns        = array(     'CUST_CD' => 'CUST_CD' 
                                            ,'EMAIL_ADDR' => 'EMAIL_ADDR'
                                            ,'NAME' => 'NAME'
                                    );
	}

	public function QueryResult() { 
		return $this->query_result; 
	} 

    /**
     *  Genereates the SQL used for a query and executes the query.
     *
     * @param String $where A string containing the where clause. The string must be a valid SQL where clase. e.g "where id = '1' and name = 'JOHN'" Optional
     * @param String $postclauses A string that contains SQL clauses that appear at the end of the SELECT statement. Clauses like "order by" and "group by"
     *                             are examples of the clauses that may be part of this string. This string must be a valid SQL.
     * @return int -1 if there was a database error (usually an SQL syntax error), otherwise it returns the number sero, 0. Unlike the mysqli implementation
     *                that returns a number that is >=0 which indicating the number of records returned in the query.
     */
     function query($where="", $postclauses="") {
        $select      = "select ";
        $column_list = "";
        $from        = "from ".$this->tablename." ";
        $first = true;

        foreach ($this->dbcolumns as $col => $label) {
            if (!$first) {
                $column_list .=", ";
            }
            else  {
                $first=false;
            }
            
            // See if there is a function applied against the column
            if (isset($this->dbcolumns_function[$col])) {
            	$column_list.=$this->dbcolumns_function[$col];
            }
            else {
                // Check if $col is defined as a date
                if (in_array($col,$this->dbcolumns_date)) {
                    $column_list.="TO_CHAR(".$col.", 'DD-MON-YYYY HH24:MI:SS') ".$col; ;
                }
                else {
            	   $column_list.=$col;
                }
            }
        }	
        
        $this->last_sql = $this->withClause.$select.$column_list." ".$from.$where." ".$postclauses;

//error_log($this->last_sql."\n", 3, "fbi.log");
        // Perform the query          
        $this->query_result = $this->execStmt($this->last_sql);
 
        if (!$this->query_result) {               
            // Database error   
            echo    $this->last_sql."\n"; 
            return -1;
        }
        else { 
            return oci_num_rows($this->query_result);
        }
    }
}

?>
