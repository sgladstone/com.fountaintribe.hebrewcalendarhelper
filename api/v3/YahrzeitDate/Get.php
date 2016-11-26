<?php

/**  
 * YahrzeitDate.Get API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_yahrzeit_date_Get_spec(&$spec) {
  $spec['year']['api.required'] = 1;
  $spec['month']['api.required'] = 0;
  $spec['day']['api.required'] = 0;
  $spec['mourner_contact_ids']['api.required'] = 0;
 // $spec['deceased_contact_ids']['api.required'] = 0;
}

/**
 * YahrzeitDate.Get API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_yahrzeit_date_Get($params) {
  if (array_key_exists('year', $params) && is_numeric($params['year'] )) {
  	
  	if( array_key_exists('mourner_contact_ids', $params) ){
  		$mourner_contact_ids = $params['mourner_contact_ids'];
  	}else{
  		throw new API_Exception(/*errorMessage*/ '\'mourner_contact_ids\' is a required parm.', /*errorCode*/ 1234);
  	}
 
  	 if( strlen($mourner_contact_ids) > 0 ){
  	 	
  	 	$mourner_cids_for_sql = $mourner_contact_ids;
  	    $iyear = $params['year'];
  	    $imonth  = $params['month'];
  	    $iday = $params['day'];
  	    
  	    
  	    if( strlen($imonth ) > 0){
  	    	$sql_month = " and month(y.yahrzeit_date)  = '$imonth'";
  	    }else{
  	    	$sql_month = "";
  	    }
  	    
  	    if( strlen($iday ) > 0){
  	    	$sql_day = " and day(y.yahrzeit_date) = '$iday'";
  	    }else{
  	    	$sql_day = "";
  	    }
  	     
  	    $all_yahs = array();
  	 	require_once( 'utils/HebrewCalendar.php');
  	  	$yahrzeit_table_name = HebrewCalendar::YAHRZEIT_TEMP_TABLE_NAME;    	//"civicrm_fountaintribe_yahrzeits_temp";
			
			$sql_str = "SELECT y.mourner_contact_id as mourner_cid, d.id as deceased_cid,
					d.display_name as deceased_display_name,
					y.yahrzeit_type , date(y.yahrzeit_date) as yah_date
							FROM ".$yahrzeit_table_name." y
							JOIN civicrm_contact d ON y.deceased_contact_id = d.id and d.is_deleted <> 1
							WHERE y.mourner_contact_id IN (  $mourner_cids_for_sql ) ".
							$sql_month.$sql_day.
							" AND year(y.yahrzeit_date) = '$iyear'
							  AND y.yahrzeit_type = '0' ";   // 0 = Hebrew observance. 1 = English observance
 			
						
			
			$dao =& CRM_Core_DAO::executeQuery( $sql_str,   CRM_Core_DAO::$_nullArray ) ;
			    
			$tmp_display_name = "";
			$count = 1;
			while ( $dao->fetch() ) {
				
			      $deceased_display_name = $dao->deceased_display_name; 
			      $deceased_cid = $dao->deceased_cid;
			      $yah_date = $dao->yah_date;
			      $mourner_cid = $dao->mourner_cid;
			      
			      $tmp_cur = array('deceased_cid' => $deceased_cid, 'mourner_cid' => $mourner_cid, 'deceased_display_name' => $deceased_display_name, 'yahrzeit_date' => $yah_date);
			     $all_yahs[$count] = $tmp_cur;
			      
			      $count = $count + 1;
			      //$tmp_greetings = "Yahrzeit of ".$tmp_display_name ;         
			 }
			 $dao->free( ); 
			 $returnValues =  $all_yahs;
   
			
  	 /*
    $returnValues = array( // OK, return several data rows
      12 => array('id' => 12, 'name' => 'Test Milton'),
      34 => array('id' => 34, 'name' => 'Test Sally'),
      56 => array('id' => 56, 'name' => 'Fifty six'),
    );
    */
  	 }
    // ALTERNATIVE: $returnValues = array(); // OK, success
    // ALTERNATIVE: $returnValues = array("Some value"); // OK, return a single value

    // Spec: civicrm_api3_create_success($values = 1, $params = array(), $entity = NULL, $action = NULL)
    return civicrm_api3_create_success($returnValues, $params, 'NewEntity', 'NewAction');
  } else {
    throw new API_Exception(/*errorMessage*/ '\'Year\' is a required parm, and it must be numeric.', /*errorCode*/ 1234);
  }
}

