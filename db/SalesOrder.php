<?php

class SalesOrder extends IDBTable {

	public function __construct($db) {
		parent::__construct($db);
		$this->tablename        = 'SO';
		$this->dbcolumns        = array(
										  'DEL_DOC_NUM'=>'DEL_DOC_NUM' 
										, 'CUST_CD'=>'CUST_CD' 
										, 'CAUSE_CD'=>'CAUSE_CD' 
										, 'VOID_CAUSE_CD'=>'VOID_CAUSE_CD' 
										, 'EMP_CD_OP'=>'EMP_CD_OP' 
										, 'EMP_CD_KEYER'=>'EMP_CD_KEYER' 
										, 'FIN_CUST_CD'=>'FIN_CUST_CD' 
										, 'SHIP_TO_ST_CD'=>'SHIP_TO_ST_CD' 
										, 'SHIP_TO_ZONE_CD'=>'SHIP_TO_ZONE_CD' 
										, 'TAX_CD'=>'TAX_CD' 
										, 'TET_CD'=>'TET_CD' 
										, 'ORD_TP_CD'=>'ORD_TP_CD' 
										, 'ORD_SRT_CD'=>'ORD_SRT_CD' 
										, 'SE_TP_CD'=>'SE_TP_CD' 
										, 'SO_STORE_CD'=>'SO_STORE_CD' 
										, 'PU_DEL_STORE_CD'=>'PU_DEL_STORE_CD' 
										, 'SE_CENTER_STORE_CD'=>'SE_CENTER_STORE_CD' 
										, 'SE_ZONE_CD'=>'SE_ZONE_CD' 
										, 'SO_EMP_SLSP_CD1'=>'SO_EMP_SLSP_CD1' 
										, 'SO_EMP_SLSP_CD2'=>'SO_EMP_SLSP_CD2' 
										, 'SO_EMP_SLSP_CD3'=>'SO_EMP_SLSP_CD3' 
										, 'DISC_CD'=>'DISC_CD' 
										, 'DISC_EMP_APP_CD'=>'DISC_EMP_APP_CD' 
										, 'DISC_EMP_CD'=>'DISC_EMP_CD' 
										, 'INT_FI_APP_CD'=>'INT_FI_APP_CD' 
										, 'PRT_EMP_CD'=>'PRT_EMP_CD' 
										, 'PRT_APP_EMP_CD'=>'PRT_APP_EMP_CD' 
										, 'PRT_CON_CD'=>'PRT_CON_CD' 
										, 'SO_DOC_NUM'=>'SO_DOC_NUM' 
										, 'SO_WR_DT'=>'SO_WR_DT' 
										, 'SO_SEQ_NUM'=>'SO_SEQ_NUM' 
										, 'SHIP_TO_TITLE'=>'SHIP_TO_TITLE' 
										, 'SHIP_TO_F_NAME'=>'SHIP_TO_F_NAME' 
										, 'SHIP_TO_L_NAME'=>'SHIP_TO_L_NAME' 
										, 'SHIP_TO_ADDR1'=>'SHIP_TO_ADDR1' 
										, 'SHIP_TO_ADDR2'=>'SHIP_TO_ADDR2' 
										, 'SHIP_TO_ZIP_CD'=>'SHIP_TO_ZIP_CD' 
										, 'SHIP_TO_H_PHONE'=>'SHIP_TO_H_PHONE' 
										, 'SHIP_TO_B_PHONE'=>'SHIP_TO_B_PHONE' 
										, 'SHIP_TO_EXT'=>'SHIP_TO_EXT' 
										, 'SHIP_TO_CITY'=>'SHIP_TO_CITY' 
										, 'SHIP_TO_COUNTRY'=>'SHIP_TO_COUNTRY' 
										, 'SHIP_TO_CORP'=>'SHIP_TO_CORP' 
										, 'SHIP_TO_COUNTY'=>'SHIP_TO_COUNTY' 
										, 'HOLD_UNTIL_DT'=>'HOLD_UNTIL_DT' 
										, 'PCT_OF_SALE1'=>'PCT_OF_SALE1' 
										, 'PCT_OF_SALE2'=>'PCT_OF_SALE2' 
										, 'PCT_OF_SALE3'=>'PCT_OF_SALE3' 
										, 'ORDER_PO_NUM'=>'ORDER_PO_NUM' 
										, 'SO_WR_SEC'=>'SO_WR_SEC' 
										, 'SHIP_TO_OUT_OF_TERR'=>'SHIP_TO_OUT_OF_TERR' 
										, 'CRED_RPT'=>'CRED_RPT' 
										, 'BILL_ORD_NUM'=>'BILL_ORD_NUM' 
										, 'SHIP_TO_SSN'=>'SHIP_TO_SSN' 
										, 'NUM_ORDERS'=>'NUM_ORDERS' 
										, 'RENEW_WARR'=>'RENEW_WARR' 
										, 'WARR_CUST'=>'WARR_CUST' 
										, 'SHIP_TO_CUST_CD'=>'SHIP_TO_CUST_CD' 
										, 'SHIP_TO_CUST_NUM'=>'SHIP_TO_CUST_NUM' 
										, 'PU_DEL'=>'PU_DEL' 
										, 'PU_DEL_DT'=>'PU_DEL_DT' 
										, 'DEL_CHG'=>'DEL_CHG' 
										, 'SETUP_CHG'=>'SETUP_CHG' 
										, 'TAX_CHG'=>'TAX_CHG' 
										, 'STAT_CD'=>'STAT_CD' 
										, 'FINAL_DT'=>'FINAL_DT' 
										, 'FINAL_STORE_CD'=>'FINAL_STORE_CD' 
										, 'MASF_FLAG'=>'MASF_FLAG' 
										, 'ORIG_DEL_DOC_NUM'=>'ORIG_DEL_DOC_NUM' 
										, 'SER_CNTR_CD'=>'SER_CNTR_CD' 
										, 'SER_CNTR_VOID_FLAG'=>'SER_CNTR_VOID_FLAG' 
										, 'ORIG_FI_AMT'=>'ORIG_FI_AMT' 
										, 'APPROVAL_CD'=>'APPROVAL_CD' 
										, 'RESO_DEPOSIT'=>'RESO_DEPOSIT' 
										, 'LAYAWAY'=>'LAYAWAY' 
										, 'LAYAWAY_PMT'=>'LAYAWAY_PMT' 
										, 'LAYAWAY_1ST_PMT_DUE'=>'LAYAWAY_1ST_PMT_DUE' 
										, 'CASH_AND_CARRY'=>'CASH_AND_CARRY' 
										, 'PRT_DT'=>'PRT_DT' 
										, 'PRT_PU_SLIP_CNT'=>'PRT_PU_SLIP_CNT' 
										, 'PRT_IVC_CNT'=>'PRT_IVC_CNT' 
										, 'PRT_PRG_NM'=>'PRT_PRG_NM' 
										, 'PU_SLIP_STAT_CD'=>'PU_SLIP_STAT_CD' 
										, 'GRID_NUM'=>'GRID_NUM' 
										, 'PRT_INS_CNT'=>'PRT_INS_CNT' 
										, 'REQUESTED_FI_AMT'=>'REQUESTED_FI_AMT' 
										, 'ARC_FLAG'=>'ARC_FLAG' 
										, 'PU_DEL_TIME_BEG'=>'PU_DEL_TIME_BEG' 
										, 'PU_DEL_TIME_END'=>'PU_DEL_TIME_END' 
										, 'DRIVER_EMP_CD'=>'DRIVER_EMP_CD' 
										, 'HELPER_EMP_CD'=>'HELPER_EMP_CD' 
										, 'FRAN_SALE'=>'FRAN_SALE' 
										, 'USR_FLD_1'=>'USR_FLD_1' 
										, 'USR_FLD_2'=>'USR_FLD_2' 
										, 'USR_FLD_3'=>'USR_FLD_3' 
										, 'USR_FLD_4'=>'USR_FLD_4' 
										, 'USR_FLD_5'=>'USR_FLD_5' 
										, 'POS_LAY_NUM'=>'POS_LAY_NUM' 
										, 'ALT_DOC_NUM'=>'ALT_DOC_NUM' 
										, 'ORIGIN_CD'=>'ORIGIN_CD' 
										, 'DEL_STAT'=>'DEL_STAT'
										, 'NEED_BY_DT'=>'NEED_BY_DT' 
									);

		$this->dbcolumns_date  	= array(
										  'SO_WR_DT'
										, 'HOLD_UNTIL_DT'
										, 'PU_DEL_DT'
										, 'FINAL_DT'
										, 'LAYAWAY_1ST_PMT_DUE'
										, 'NEED_BY_DT'

										);

 		$this->setAutoIDColumn("DEL_DOC_NUM");

	}

	/**
	 * Extend the base insert class to add business logic for the GERS
	 * application. When inserting a CUST record a customer code must be 
	 * generated.
	 */
	public function insert($autoid=true, $appupdate=false) {
		$custcd = $this->generateSODocNum();

		$this->set_CUST_CD($custcd);	
        return parent::insert($autoid, $appupdate);
	}

	/**
	 * Generate the sequnce for the last 5 digits of the customer code.
	 *
	 * @param $addr The address for the customer. Grab the first 4 numerics
	 *              found in the address.
	 * @return String The sequence to be used for the customer code.
	 *              If the numerics in the address is less than 4 characters then
	 *              right pad with "Z".
	 */
	protected function getSOSeqNum($addr) {
		$retVal = "";
		$len = strlen($addr);
		for ($i = 0; $i < $len; $i++) {
			$ch = $addr[$i];
			if(ord($addr[$i]) >= 48 && ord($addr[$i]) <= 57) {
				$retVal .= $ch;
			}

			if (strlen($retVal) == 4) {
				return $retVal."0";
			}
		}

		return str_pad($retVal, 4, "Z")."0";
	}	

	/**
	 * Generate a customer code for the customer record being created.
	 * Customer code is formated usig the following rules:
	 * 1. First four characters of the last name. If the last name
	 *    is < 4 characters then right pad with "9".
	 * 2. The First character of the first name.
	 * 3. The sequence is the last 5 characters of the customer code.
	 *    It consists of the first 4 numeric characters from the ADDR1
	 *    field. If < 4 then right pad with "Z". The last character is
	 *    set to zero. 
	 * The cust code will then be check against the database and if it
	 * is already in use then the last character will be incremented by
	 * Valid last values of the last character are 0-9 and A-Z.
	 *
	 * @return String the customer code
	 */
	protected function generateSODocNum() {
		$custcd = str_pad(substr($this->get_LNAME(), 0 ,4), 4, "9")
		                 .substr($this->get_FNAME(), 0 ,1)
		                 .$this->getCustCodeSequence($this->get_ADDR1());
		
		$bGotCustCode = false;
		while (! $bGotCustCode ){
			$result = $this->existsCustCd($custcd);

			if ($result === -1) {
				return -1;
			}
			else if ($result === false) {			
				$bGotCustCode = true;
			}
			else {			
				$custcd = $this->getNextCustCd($custcd);			
			}
		}
	                 
		return $custcd;
	}


	protected function getErrorEx() {
		return $this->errorMsg;
	}

    /*-----------------------------------------------------------------------------------
     *-------------------------------- getSalesOrderByDelDocNum -------------------------
     *-----------------------------------------------------------------------------------
     *
     * Function getSalesOrderByDelDocNum: 
     *      Routine will query table SO by DEL_DOC_NUM
     *
     * Parameters:
     *      (String) DEL_DOC_NUM
     *      (String) postclause
     *
     * Return:
     *      (IDBT Cursor) Cursor to table SO or NULL if empty
     *      
     */
    public function getSalesOrderByDelDocNum( $delDocNum, $postclause = '' ){
        global $appconfig, $app, $errmsg, $logger;
        $where = "WHERE DEL_DOC_NUM = '" . $delDocNum . "'";
        $result = $this->query($where, $postclause);
    
		if ($result < 0) {
			$errmsg = 'Database Query Error. '.$this->getError();
			$return['error'] = true;
			$return['msg']   = $errmsg;
			echo json_encode($return);
			return;
		}
        
        if( $row = $this->next() ){
            return $this;
        }        
        return NULL;
    } 

    /*-----------------------------------------------------------------------------------
     *-------------------------- updateSalesOrderByDelDocNum ----------------------------
     *-----------------------------------------------------------------------------------
     *
     * Function updateSalesOrderByDelDocNum: 
     *      Routine will update table SO by  DEL_DOC_NUM 
     *
     * Parameters:
     *      (String) DEL_DOC_NUM
     *      (Array) Values to update 
     *              $key => Column
     *              $value = Value to update column
     *
     * Return:
     *      (IDBT Cursor) Cursor to table SO
     *      
     */
    public function updateSalesOrderByDelDocNum( $delDocNum, $updt ){
        global $appconfig, $app, $errmsg, $logger;
        $where = "WHERE DEL_DOC_NUM = '" . $delDocNum  . "'";

        foreach( $updt as $key => $value ){
            $col = "set_" . $key;
            $this->$col( $value );
        }
        $result = $this->update($where, false);

        if($result < 0){
            return false; 

        }

        return $this;
    } 
}
?>
