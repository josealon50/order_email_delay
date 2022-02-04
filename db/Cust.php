<?php

class Cust extends IDBTable {

	public function __construct($db) {
		parent::__construct($db);
		$this->tablename        = 'CUST';
		$this->dbcolumns        = array(
										  'CUST_CD'=>'CUST_CD'
										, 'CO_APP_FOR'=>'CO_APP_FOR'
										, 'CO_SIGNER_FOR'=>'CO_SIGNER_FOR'
										, 'ST_CD'=>'ST_CD'
										, 'ZONE_CD'=>'ZONE_CD'
										, 'SRT_CD'=>'SRT_CD'
										, 'TITLE'=>'TITLE'
										, 'FNAME'=>'FNAME'
										, 'INIT'=>'INIT'
										, 'LNAME'=>'LNAME'
										, 'ADDR1'=>'ADDR1'
										, 'ADDR2'=>'ADDR2'
										, 'CITY'=>'CITY'
										, 'COUNTRY'=>'COUNTRY'
										, 'ZIP_CD'=>'ZIP_CD'
										, 'HOME_PHONE'=>'HOME_PHONE'
										, 'BUS_PHONE'=>'BUS_PHONE'
										, 'EXT'=>'EXT'
										, 'CR_LN'=>'CR_LN'
										, 'ACCT_OPN_DT'=>'ACCT_OPN_DT'
										, 'ACCT_CLOSE_DT'=>'ACCT_CLOSE_DT'
										, 'MAIL_SVC_CD'=>'MAIL_SVC_CD'
										, 'CORP_NAME'=>'CORP_NAME'
										, 'CUST_TP_CD'=>'CUST_TP_CD'
										, 'COUNTY'=>'COUNTY'
										, 'CR_SCORE'=>'CR_SCORE'
										, 'OCC_TP'=>'OCC_TP'
										, 'SSN'=>'SSN'
										, 'YRS'=>'YRS'
										, 'MONTHS'=>'MONTHS'
										, 'TET_CD'=>'TET_CD'
										, 'TET_ID#'=>'TET_ID#'
										, 'SE_ZONE_CD'=>'SE_ZONE_CD'
										, 'NAME_CHG_DT'=>'NAME_CHG_DT'
										, 'ADDR_CHG_DT'=>'ADDR_CHG_DT'
										, 'CUST_CMNT'=>'CUST_CMNT'
										, 'OUT_OF_TERR'=>'OUT_OF_TERR'
										, 'BEG_ADDR_DT'=>'BEG_ADDR_DT'
										, 'CUST_NUM'=>'CUST_NUM'
										, 'OPEN_AR_TERM_CD'=>'OPEN_AR_TERM_CD'
										, 'PREFIX'=>'PREFIX'
										, 'SUFFIX'=>'SUFFIX'
										, 'MNAME'=>'MNAME'
										, 'SNAME'=>'SNAME'
										, 'ST_EMPLOY'=>'ST_EMPLOY'
										, 'HPHONE_STAT'=>'HPHONE_STAT'
										, 'HPHONE_EXT'=>'HPHONE_EXT'
										, 'RES_IND'=>'RES_IND'
										, 'DOB'=>'DOB'
										, 'AGE'=>'AGE'
										, 'MARITAL_STAT'=>'MARITAL_STAT'
										, 'NUM_DEPN'=>'NUM_DEPN'
										, 'MOM_MAIDEN_NAME'=>'MOM_MAIDEN_NAME'
										, 'DL_NUMBER'=>'DL_NUMBER'
										, 'DL_ST_CD'=>'DL_ST_CD'
										, 'DL_EXP_DT'=>'DL_EXP_DT'
										, 'DEFAULT_TAX_CD'=>'DEFAULT_TAX_CD'
										, 'ALT_CUST_CD'=>'ALT_CUST_CD'
										, 'CUST_TP_PRC_CD'=>'CUST_TP_PRC_CD'
										, 'EMAIL_ADDR'=>'EMAIL_ADDR'
										, 'EMAIL_ADDR_SHIP_TO'=>'EMAIL_ADDR_SHIP_TO'
										, 'CCC_CD'=>'CCC_CD'
										, 'CII_CD'=>'CII_CD'
										, 'MAX_CR_LIMIT'=>'MAX_CR_LIMIT'
										, 'ADDR_TP_CD'=>'ADDR_TP_CD'
										);

		$this->dbcolumns_date        = array(
											  'DOB'
											, 'ACCT_OPN_DT'
											, 'ACCT_CLOSE_DT'
											, 'NAME_CHG_DT'
											, 'ADDR_CHG_DT'
											, 'DL_EXP_DT'
											, 'BEG_ADDR_DT'
											);

 		$this->setAutoIDColumn("CUST_CD");

		$this->errorMsg 			= "";

	}

	/**
	 * Extend the base insert class to add business logic for the GERS
	 * application. When inserting a CUST record a customer code must be 
	 * generated.
	 */
	public function insert($autoid=true, $appupdate=false) {
		$custcd = $this->generateCustCd();

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
	protected function getCustCodeSequence($addr) {
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
	protected function generateCustCd() {
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

	/**
	 * Generate the next logical customer code from the existing customer code.
	 * This entails adding one the ordinal value of the last character in the string.
	 * Valid characters are 0-9 and A-Z.
	 * 
	 * @param $custcode The customer code
	 * @return String the next logical customer code.
	 */
	protected function getNextCustCd($custcode) {
		$baseCustCd = substr($custcode, 0, 9);

		$lastChar = substr($custcode, 9, 1);

		$ordVal = ord($lastChar);
		if ($ordVal === ord("9") ) {
			$ordVal = 64; // 64 = @ which comes before A in the ASCII Table
		}

		$charOrdValue = $ordVal+1;

		return $baseCustCd.chr($charOrdValue);

	}

	protected function getErrorEx() {
		return $this->errorMsg;
	}

	/**
	 * Verify customer code is not already in use.
	 *
	 * @param $custcode The customer code to be verified.
	 * @return mixed true is the customer code is already assigned, false if the 
	 *         customer code is not in use. -1 is returned is a database error is encountered.
	 */
	protected function existsCustCd($custcode) {
	  $sql = "SELECT CUST_CD FROM CUST WHERE CUST_CD = '".$custcode."' ";

      $stmt = oci_parse($this->dbresource->getConnection(), $sql);

      if (! $stmt) {
      	$this->errorMsg = oci_error()."\n".$sql;
      	return -1;
      }

      $result = oci_execute($stmt);

      if (! $result) {
      	$this->errorMsg = oci_error()."\n".$sql;
        return -1;
      }

      if (oci_fetch_array($stmt, OCI_ASSOC+OCI_RETURN_NULLS) == false) {    	
      	return false;
      }
 	      	
	  return true;

	}
}

?>
