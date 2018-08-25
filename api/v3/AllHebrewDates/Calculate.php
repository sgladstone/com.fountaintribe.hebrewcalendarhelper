<?php

/**
 * AllHebrewDates.Calculate API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_all_hebrew_dates_Calculate_spec(&$spec) {
  //$spec['magicword']['api.required'] = 1;
}

/**
 * AllHebrewDates.Calculate API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 
*/


function civicrm_api3_all_hebrew_dates_calculate($params) {
	// if (array_key_exists('magicword', $params) && $params['magicword'] == 'sesame') {
	$returnValues = array( // OK, return several data rows
	);
	
	
	
	$tmp_contact_ids = "";
	if(isset($params['contact_ids'])){
		$tmp_contact_ids = $params['contact_ids'];
	}else{
		$tmp_contact_ids = "";
	}
	
	 
	require_once 'utils/HebrewCalendar.php';
	$yahrzeit_table_name =  HebrewCalendar::YAHRZEIT_TEMP_TABLE_NAME;    
	 
	
	// check that table now exists
	//$table_missing = true;
	$tmpHebCal = new HebrewCalendar();
		
		
	$cur_schema_name = $tmpHebCal->getSQLschema();
	 
	$table_sql = "SELECT table_name FROM information_schema.tables
	WHERE
	table_schema = '$cur_schema_name'
	AND table_name = '$yahrzeit_table_name'"  ;


	//  print "<Br>sql: ".$table_sql;
	$table_dao =& CRM_Core_DAO::executeQuery( $table_sql ,   CRM_Core_DAO::$_nullArray ) ;

	//   print "<br>sql: ".$yahrzeit_sql;
	if( $table_dao->fetch( ) ) {
		// print "<br>Table already exists.";
		$table_exists = true;
	}else{
		$table_exists = false;
	}
	 
	 
	$table_dao->free();
	
	if( $table_exists){

		// deal with getting general stuff, ie table that tracks if individual was born/died before sunset or not. 
		$result = civicrm_api3('CustomGroup', 'get', array(
				'sequential' => 1,
				'name' =>  HebrewCalendar::EXTENDED_DATE_CUSTOM_FIELD_GROUP_NAME,
				'extends' => "Individual",
		));
		
		
		if($result['is_error'] <> 0 || $result['count'] == 0  ){
			$rtn_data['error_message'] = "Could not find custom field set '". HebrewCalendar::EXTENDED_DATE_CUSTOM_FIELD_GROUP_TITLE."' ";
			//return $rtn_data;
				
		}else{
			$tmp_values = $result['values'][0];
			$extended_date_table = $tmp_values['table_name'];
			//$set_id = $tmp_values['id'];
		
			if(strlen( $extended_date_table) == 0){
				$rtn_data['error_message'] = "Could not get SQL table name for custom field set '".HebrewCalendar::EXTENDED_DATE_CUSTOM_FIELD_GROUP_TITLE."'";
				//return $rtn_data;
					
			}
		}
		
		
		
		// Now deal with birthday-related stuff.
		
		$rtn_data = $tmpHebCal->scrubBirthCalculatedFields( $tmp_contact_ids );
		
		if(isset( $rtn_data['error_message']) && strlen($rtn_data['error_message']) > 0   ){
			throw new API_Exception("Hebrew Birthday Error: ".$rtn_data['error_message'],  1234);
		}
		

		
		
		$rtn_data = $tmpHebCal->calculateBirthDates( $extended_date_table,  $tmp_contact_ids);
		
		if( isset( $rtn_data['error_message'] )  && strlen($rtn_data['error_message']) > 0 ){
			//return $rtn_data;
			$rtn_data['error_message_birthday_calcs'] = $rtn_data['error_message'];
			throw new API_Exception("Error: ".$rtn_data['error_message'],  1234);
		}
		
		// Should be poplulated now: $rtn_data['contacts_updated_birthdays']  ;
		$record_count_birthdays = $rtn_data['contacts_updated_birthdays'] ; 
		// All done with birthday calculations. 
		
		
		
		// Now deal with yahrzeit data
		if( array_key_exists('contact_ids', $params) && strlen($params['contact_ids'])  > 0   ){
			 
			// no need to truncate table, as we will do a surgical update for the impacted contacts.
			// we do need to remove any records for these deceased contacts.
			$sql_cleanup = "delete FROM $yahrzeit_table_name WHERE deceased_contact_id IN (".$params['contact_ids'].")";
		}else{
			// clean temp table
			$sql_cleanup = "TRUNCATE TABLE $yahrzeit_table_name";
		}			
		$dao =& CRM_Core_DAO::executeQuery( $sql_cleanup,   CRM_Core_DAO::$_nullArray ) ;
		$dao->free();
		
		
		$rtn_data = $tmpHebCal->scrubDeceasedCalculatedFields( $tmp_contact_ids  );
		
		
		if(isset( $rtn_data['error_message']) && strlen($rtn_data['error_message']) > 0   ){
		    
		    CRM_Core_Error::debug("Just did scrub: ".$rtn_data['error_message'] , "");	
			throw new API_Exception("Yahrzeit Error: ".$rtn_data['error_message'],  1234);
				
				
		}else{
			
			//CRM_Core_Error::debug("About to do fill table: ".$tmp_contact_ids, "");
				$rtn_data = fillYahrzeitTempTable( $tmpHebCal, $extended_date_table , $yahrzeit_table_name, $tmp_contact_ids   );
			//	CRM_Core_Error::debug("About to do parasha: ".$tmp_contact_ids, "");
				$parashat_rtn = $tmpHebCal->fillYahrzeitParashat($tmp_contact_ids );
			
				$rtn_data['record_count_birthdays'] = $record_count_birthdays; 
				
				if( isset( $rtn_data['error_message']) && strlen($rtn_data['error_message']) > 0   ){
					throw new API_Exception("Error: ".$rtn_data['error_message'],  1234);
				
				
				}else if(isset($rtn_data['error_message_birthday_calcs']) && strlen( $rtn_data['error_message_birthday_calcs'] ) > 0    ){
					throw new API_Exception("Error: ".$rtn_data['error_message'],  1234);
					
				}else{
				 	$returnValues =  $rtn_data ; 
					return civicrm_api3_create_success($returnValues, $params, 'AllHebrewDates', 'calculate');
				}
					// ALTERNATIVE: $returnValues = array(); // OK, success
					// ALTERNATIVE: $returnValues = array("Some value"); // OK, return a single value
			
					// Spec: civicrm_api3_create_success($values = 1, $params = array(), $entity = NULL, $action = NULL)
				}
		} else {
			throw new API_Exception(/*errorMessage*/ 'Could not find yahrzeit temp table', /*errorCode*/ 1234);
		}
	
}



function insert_yahrzeit_record_into_temp_table($TempTableName,  $yahrzeit_type, $mourner_contact_id, $mourner_name,  $deceased_contact_id,
		$deceased_name,  
		$english_deceased_date_raw,  
		$deceased_date_before_sunset_formated,
		$hebrew_deceased_date,
		$yahrzeit_date_tmp,
		$relationship_name_formated,
		$mourner_observance_preference, $plaque_location, $yahrzeit_relationship_id   ) {

		 require_once 'utils/HebrewCalendar.php';
		 $tmpHebCal = new HebrewCalendar();
		 
		//CRM_Core_Error::debug("insert_record:".$deceased_name." ".$deceased_contact_id	, "");
		 if(isset($yahrzeit_relationship_id)){
		 	
		 }else{
		 	
		 	$yahrzeit_relationship_id = "";
		 }
	//	CRM_Core_Error::debug( "<br>yahrzeit date raw: ".$yahrzeit_date_tmp, "" ) ;
		 
		 $yahrzeit_date_raw  =   $yahrzeit_date_tmp ; 
	  
	   $sql_date_format = "Y-m-d";
	 
	 $have_valid_yah_date = false;
	  if( $yahrzeit_date_raw <> "0000-00-00"  &&  is_numeric (substr($yahrzeit_date_raw, 0 , 4)) ) {
	       
	       
	       $have_valid_yah_date = true;
	      
	    //  CRM_Core_Error::debug("yahrzeit date raw has a numeric year", "");
		 $yar = explode( '-', $yahrzeit_date_tmp);
		 $deceased_date_before_sunset = '0';
		 $hebrew_date_format = 'hebrew';
		 $yahrzeit_hebrew_date_format_hebrew  = $tmpHebCal->util_convert2hebrew_date($yar[0], $yar[1], $yar[2], $deceased_date_before_sunset, $hebrew_date_format);


		 $hebrew_date_format = 'dd MM yy';
		 $yahrzeit_hebrew_date_format_english  = $tmpHebCal->util_convert2hebrew_date($yar[0], $yar[1], $yar[2], $deceased_date_before_sunset, $hebrew_date_format);
		 //print "<br>yar. heb. date: ".$yahrzeit_hebrew_date;

         // Get Hebrew date in machine-sortable format, ie all numbers. 
		  $hebrew_date_format = 'mm/dd/yy';
		  $yahrzeit_hebrew_date_format_sortable  = $tmpHebCal->util_convert2hebrew_date($yar[0], $yar[1], $yar[2], $deceased_date_before_sunset, $hebrew_date_format);
		  
		  $yah_tmp_sortable_arr = explode("/", $yahrzeit_hebrew_date_format_sortable);
		  $yah_hebrew_month_num = $yah_tmp_sortable_arr[0];
		  
		  $yah_hebrew_day_num = $yah_tmp_sortable_arr[1];
		  
		  $yah_hebrew_year_num = $yah_tmp_sortable_arr[2];
		  
		 
	  	//CRM_Core_Error::debug("Debug: yahrzeit date raw, will do shabbat calcs : ",  $yahrzeit_date_raw );
	  	// take care of tokens for Friday, Saturday before the yahrzeit, and the Friday, Saturday after the yahrzeit.
	  	$yah_timestamp = strtotime($yahrzeit_date_raw);
	  	$yah_day_of_week = date( 'w', $yah_timestamp);
	  	 
	  	$raw_yahrzeit_date_morning  = strtotime(date("Y-m-d", $yah_timestamp) ." +1 day") ;

	  	if($yah_day_of_week == 5){
	  		// The yahrzeit starts at erev Shabbat (ie Friday night), return the yahrzeit date itself.
	  		// A synagogue in this situation will read the name during services that same shabbat.
	  		$raw_friday_before = $yah_timestamp ;
	  		$raw_friday_after = $yah_timestamp ;
	  		$raw_saturday_before = strtotime(date("Y-m-d", $yah_timestamp) ." +1 day") ;
	  		$raw_saturday_after = strtotime(date("Y-m-d", $yah_timestamp) ." +1 day") ;

	  	}else if($yah_day_of_week == 6){
	  		// The yahrzeit starts on a Saturday night.
	  		// So the Shabbat morning before the yahrzeit is the same English date as the start of the yahrzeit date.
	  		$raw_friday_before = strtotime(date("Y-m-d", $yah_timestamp) ." previous Friday") ;
	  		$raw_friday_after  = strtotime(date("Y-m-d", $yah_timestamp) ." next Friday") ;
	  		 
	  		$raw_saturday_before = $yah_timestamp;
	  		$raw_saturday_after  = strtotime(date("Y-m-d", $yah_timestamp) ." next Saturday") ;
	  		 
	  	}else{

	  		$raw_friday_before =  strtotime(date("Y-m-d", $yah_timestamp) ." previous Friday") ;
	  		$raw_friday_after  = strtotime(date("Y-m-d", $yah_timestamp) ." next Friday") ;
	  		 
	  		$raw_saturday_before = strtotime(date("Y-m-d", $yah_timestamp) ." previous Saturday") ;
	  		$raw_saturday_after  =  strtotime(date("Y-m-d", $yah_timestamp) ." next Saturday");


	  	} 	
	  	
	  	$sql_friday_before = date($sql_date_format, $raw_friday_before );
	  	$sql_friday_after = date($sql_date_format, $raw_friday_after );

	  	$sql_saturday_before = date($sql_date_format, $raw_saturday_before );
	  	$sql_saturday_after = date($sql_date_format, $raw_saturday_after );

	  	$sql_yahrzeit_date_morning = date($sql_date_format, $raw_yahrzeit_date_morning );
	  	 
	  	 $have_valid_yah_date = true;

	  }else{
	  	 //CRM_Core_Error::debug("Debug: yahrzeit date raw does not have numeric year, will NOT do shabbat calcs : ",  $yahrzeit_date_raw );
	  	
		  //	$sql_friday_before = "";
		  //	$sql_friday_after = "";
		  	
		  //	$sql_saturday_before = "";
		  //	$sql_saturday_after = "";
		  	
		  //	$sql_yahrzeit_date_morning = "";
		  	 
		  	 $have_valid_yah_date = false;
		  	  
	  	
	  }
	  // verify this is a valid SQL date
	  if($have_valid_yah_date ){
	     	$yahrzeit_date_tmp = "'$yahrzeit_date_tmp'" ;

	  }else{
	       //  CRM_Core_Error::debug("yah date is NULL", "");
	  	$yahrzeit_date_tmp = "null" ;
	  

	  }
	  
	  
	  if( strlen($english_deceased_date_raw) > 0 ){
	  	
	  	 $english_deceased_date_sql = "'".$english_deceased_date_raw."'";
	  }else{
	  	$english_deceased_date_sql = "null";
	  }
	  
	  
	 if(  $have_valid_yah_date){
	  // prepare for dealing with Shabbat before the yahrzeit.

   //CRM_Core_Error::debug("Do shabbat calcs", "");
	     $saturday_before_yah_sql_format = date($sql_date_format, $raw_saturday_before );
	 
	   $shabbat_before_sunset_flag = "1";
	  
	
	
	  // Get Hebrew date for the Shabbat BEFORE the yahrzeit in a machine-sortable format, ie all numbers. 
	  $shabbat_before_english_arr = explode( "-", $saturday_before_yah_sql_format ) ;
	  
	  $hebrew_date_format = 'dd MM yy';
	   $shabbat_before_hebrew_date_format_english  = $tmpHebCal->util_convert2hebrew_date($shabbat_before_english_arr[0], $shabbat_before_english_arr[1], $shabbat_before_english_arr[2],  $shabbat_before_sunset_flag, $hebrew_date_format); 
	  
	  $hebrew_date_format = 'mm/dd/yy';
	  $yahrzeit_shabbat_hebrew_date_format_sortable  = $tmpHebCal->util_convert2hebrew_date($shabbat_before_english_arr[0], $shabbat_before_english_arr[1], $shabbat_before_english_arr[2],  $shabbat_before_sunset_flag, $hebrew_date_format);
	  
	  $yah_tmp_sortable_arr = explode("/", $yahrzeit_shabbat_hebrew_date_format_sortable);
	  $yah_shabbat_before_hebrew_month_num = $yah_tmp_sortable_arr[0];
	  
	  $yah_shabbat_before_hebrew_day_num = $yah_tmp_sortable_arr[1];
	  
	  $yah_shabbat_before_hebrew_year_num = $yah_tmp_sortable_arr[2];

	  
	  // prepare for dealing with Shabbat after the yahrzeit.
	  $saturday_after_yah_sql_format = date($sql_date_format, $raw_saturday_after );
	 
	  // get Hebrew date for the Shabbat AFTER the yahrzeit in a machine-sortable format, ie all numbers.
	  $shabbat_after_english_arr = explode( "-",  $saturday_after_yah_sql_format ) ;
	  
	  $hebrew_date_format = 'dd MM yy';
	  $shabbat_after_hebrew_date_format_english =  $tmpHebCal->util_convert2hebrew_date($shabbat_after_english_arr[0], $shabbat_after_english_arr[1], $shabbat_after_english_arr[2],  $shabbat_before_sunset_flag, $hebrew_date_format);
	 
	  
	  $hebrew_date_format = 'mm/dd/yy';
	  $yahrzeit_shabbat_hebrew_date_format_sortable  = $tmpHebCal->util_convert2hebrew_date($shabbat_after_english_arr[0], $shabbat_after_english_arr[1], $shabbat_after_english_arr[2],  $shabbat_before_sunset_flag, $hebrew_date_format);
	   
	  $yah_tmp_sortable_arr = explode("/", $yahrzeit_shabbat_hebrew_date_format_sortable);
	  $yah_shabbat_after_hebrew_month_num = $yah_tmp_sortable_arr[0];
	   
	  $yah_shabbat_after_hebrew_day_num = $yah_tmp_sortable_arr[1];
	   
	  $yah_shabbat_after_hebrew_year_num = $yah_tmp_sortable_arr[2];
	  
	 }
	  
	  /*
	  // '$hebrew_deceased_date' 
	  if(strlen($yahrzeit_hebrew_date_format_english) > 0 ){
	  	  $yahrzeit_hebrew_year = substr( $yahrzeit_hebrew_date_format_english, -4   ) ;
	  }else{
	  	$yahrzeit_hebrew_year = 0;
	  }
	  
      */
	  
	// print "<br>SQL INSERT: deceased name: $deceased_name   yah_date: ".$yahrzeit_date_tmp ;
	if( isset($yah_shabbat_before_hebrew_year_num) == false){
	    $yah_shabbat_before_hebrew_year_num ="";
	}
	
	if( isset( $yah_shabbat_before_hebrew_month_num) == false){
	    $yah_shabbat_before_hebrew_month_num = "";
	}
	
	if( isset($yah_shabbat_before_hebrew_day_num) == false){
	  $yah_shabbat_before_hebrew_day_num = "";
		}
	
		
		if( isset($yah_shabbat_after_hebrew_year_num) == false){
	    $yah_shabbat_after_hebrew_year_num ="";
	}
	
	if( isset( $yah_shabbat_after_hebrew_month_num) == false){
	    $yah_shabbat_after_hebrew_month_num = "";
	}
	
	if( isset($yah_shabbat_after_hebrew_day_num) == false){
	  $yah_shabbat_after_hebrew_day_num = "";
		}
		
//	if(true ||  $have_valid_yah_date){
	   // CRM_Core_Error::debug("insert sql has yahrzeit date cols.", "");
	    
	   $col_names_part =    "mourner_contact_id,
	  mourner_name, 
	  deceased_contact_id,
	  deceased_name, 
	  deceased_date, 
	  d_before_sunset,
	  hebrew_deceased_date, 
	  yahrzeit_relationship_id,
	  yahrzeit_hebrew_date_format_hebrew, 
	  yahrzeit_hebrew_date_format_english,
	  yahrzeit_hebrew_year, 
	  yahrzeit_hebrew_month,
	  yahrzeit_hebrew_day,
	  relationship_name_formatted, 
	  yahrzeit_type,
	  mourner_observance_preference, 
	  plaque_location,
	  yahrzeit_date ,
	  yahrzeit_date_morning, 
	  shabbat_before_hebrew_date_format_english,
	  shabbat_before_hebrew_year_num,
	  shabbat_before_hebrew_month_num,
	  shabbat_before_hebrew_day_num,
	  yahrzeit_erev_shabbat_before,
	  yahrzeit_shabbat_morning_before,
	  yahrzeit_erev_shabbat_after , 
	  yahrzeit_shabbat_morning_after,
	   shabbat_after_hebrew_year_num,
	  shabbat_after_hebrew_month_num,
	  shabbat_after_hebrew_day_num,
	  shabbat_after_hebrew_date_format_english";
	  
	  // 31 columns.
	  
	  $insert_sql = "INSERT INTO $TempTableName ( $col_names_part )
			VALUES($mourner_contact_id, %1 , $deceased_contact_id,
			%2, 
			 $english_deceased_date_sql , '$deceased_date_before_sunset_formated',
			 %3 , '$yahrzeit_relationship_id', 
			%4, 
			%5, %6, %7, %8,
		 '$relationship_name_formated', %9,
			'$mourner_observance_preference', %10,
			 $yahrzeit_date_tmp, 	%11,
			%12, 
			 %13, 
			 %14, %15, %16, %17, %18, %19, %20, %21,
			%22 , %23 )";
	    
/*	}else{
	 //    CRM_Core_Error::debug("insert sql does NOT have Hebrew yahrzeit cols, shabbat cols", "");
	     $col_names_part =    "mourner_contact_id,
	  mourner_name, 
	  deceased_contact_id,
	  deceased_name, 
	  deceased_date, 
	  d_before_sunset,
	  hebrew_deceased_date, 
	  yahrzeit_relationship_id,
	  yahrzeit_hebrew_date_format_hebrew, 
	  yahrzeit_hebrew_date_format_english,
	  yahrzeit_hebrew_year, 
	  yahrzeit_hebrew_month,
	  yahrzeit_hebrew_day,
	  relationship_name_formatted, 
	  yahrzeit_type,
	  mourner_observance_preference, 
	  plaque_location";
	  // 17 cols. 
	  
	  $insert_sql = "INSERT INTO $TempTableName ( $col_names_part )
			VALUES($mourner_contact_id, %1 , $deceased_contact_id,
			%2, 
			 $english_deceased_date_sql , '$deceased_date_before_sunset_formated',
			 %3 , '$yahrzeit_relationship_id', 
			%4, 
			%5, %6, %7, %8,
		 '$relationship_name_formated', %9,
			'$mourner_observance_preference', %10	  )";
	  	  
	}  
		
	*/  
	  	
	  	
	  	$sql_null_date = "";
	  	
	  //	CRM_Core_Error::debug("Insert sql: ".$insert_sql, "");
		// 	print "<br><br><b>Insert sql: </b>".;
  //shabbat_before_hebrew_date_format_english
  // shabbat_before_hebrew_date_format_english
			$params_a = array();
				
			if(strlen($mourner_name) > 0 ){
				$params_a[1] =  array( $mourner_name, 'String' );
			}else{
				$params_a[1] =  array( '', 'String' );
					
			}

			if(strlen($deceased_name) > 0){
				$params_a[2] =  array( $deceased_name, 'String' );
			}else{
				$params_a[2] =  array( '', 'String' );
			}

			if(strlen($hebrew_deceased_date) > 0 ){
				$params_a[3] =  array($hebrew_deceased_date, 'String');
			}else{
				$params_a[3] =  array( '', 'String' );
			}
			
			

			if( isset($yahrzeit_hebrew_date_format_hebrew) && strlen($yahrzeit_hebrew_date_format_hebrew) > 0 ){
				$params_a[4] =  array($yahrzeit_hebrew_date_format_hebrew, 'String');
			}else{
				$params_a[4] =  array( '', 'String' );
			}

			if(isset( $yahrzeit_hebrew_date_format_english) && strlen($yahrzeit_hebrew_date_format_english) > 0 ){
				$params_a[5] =  array($yahrzeit_hebrew_date_format_english, 'String');
			}else{
				$params_a[5] =  array( ' ', 'String' );
			}

			

			if( isset($yah_hebrew_year_num ) && strlen($yah_hebrew_year_num) > 0 ){
				$params_a[6] =  array($yah_hebrew_year_num, 'Integer');
			}else{
				$params_a[6] =  array( 0, 'Integer' );
			}
			
			if( isset( $yah_hebrew_month_num) && strlen($yah_hebrew_month_num) > 0 ){
				$params_a[7] =  array($yah_hebrew_month_num, 'Integer');
			}else{
				$params_a[7] =  array( 0, 'Integer' );
			}
			
			if( isset( $yah_hebrew_day_num ) && strlen($yah_hebrew_day_num) > 0 ){
				$params_a[8] =  array($yah_hebrew_day_num, 'Integer');
			}else{
				$params_a[8] =  array( 0, 'Integer' );
			}
			
			
			
			if(strlen($yahrzeit_type) > 0 ){
				$params_a[9] =  array($yahrzeit_type, 'String');
			}else{
				$params_a[9] =  array( ' ', 'String' );
			}
			
			// '$plaque_location'
				
			if(strlen($plaque_location) > 0 ){
				$params_a[10] =  array($plaque_location, 'String');
			}else{
				$params_a[10] =  array( '', 'String' );
			}
			
			
				// $sql_yahrzeit_date_morning
			if(isset( $sql_yahrzeit_date_morning ) &&  strlen($sql_yahrzeit_date_morning) > 0 ){
				$params_a[11] =  array($sql_yahrzeit_date_morning, 'String');
			}else{
				$params_a[11] =  array( 	$sql_null_date, 'Date' );
			}
			//
			if(isset($shabbat_before_hebrew_date_format_english) && strlen($shabbat_before_hebrew_date_format_english) > 0 ){
				$params_a[12] =  array($shabbat_before_hebrew_date_format_english, 'String');
			}else{
				$params_a[12] =  array( '', 'String' );
			}
			
			if( isset($yah_shabbat_before_hebrew_year_num) && strlen($yah_shabbat_before_hebrew_year_num) >0 ){
			    $params_a[13] = array( $yah_shabbat_before_hebrew_year_num, 'Integer');
			    
			}else{
			    $params_a[13] =  array( 0, 'Integer' );
			}
			
			if(isset($yah_shabbat_before_hebrew_month_num ) && strlen($yah_shabbat_before_hebrew_month_num) > 0  ){
			    $params_a[14] =  array($yah_shabbat_before_hebrew_month_num , 'Integer' );
			}else{
			    $params_a[14] =  array( 0, 'Integer' );
			}
			
			
			if(isset( $yah_shabbat_before_hebrew_day_num) && strlen($yah_shabbat_before_hebrew_day_num) > 0  ){
			    $params_a[15] =  array($yah_shabbat_before_hebrew_day_num , 'Integer' );
			}else{
			    $params_a[15] =  array( 0, 'Integer' );
			}
			
			
			if(isset( $sql_friday_before) && strlen($sql_friday_before) > 0  ){
			    $params_a[16] =  array( $sql_friday_before , 'String' );
			}else{
			    $params_a[16] =  array( 	$sql_null_date, 'Date' );
			}
			
			
			if(isset($sql_saturday_before ) && strlen($sql_saturday_before) > 0  ){
			    $params_a[17] =  array( $sql_saturday_before, 'String' );
			}else{
			    $params_a[17] =  array( 	$sql_null_date, 'Date' );
			}	
			
			if(isset($sql_friday_after ) && strlen($sql_friday_after) > 0  ){
			    $params_a[18] =  array( $sql_friday_after, 'String' );
			}else{
			    $params_a[18] =  array( 	$sql_null_date, 'Date' );
			}
			
			if(isset($sql_saturday_after ) && strlen( $sql_saturday_after) > 0  ){
			    $params_a[19] =  array($sql_saturday_after , 'String' );
			}else{
			    $params_a[19] =  array( 	$sql_null_date, 'Date' );
			}	
			
			if(isset(	$yah_shabbat_after_hebrew_year_num ) && strlen(	$yah_shabbat_after_hebrew_year_num) > 0  ){
			    $params_a[20] =  array( 	$yah_shabbat_after_hebrew_year_num , 'Integer' );
			}else{
			    $params_a[20] =  array( 0, 'Integer' );
			}
			
			if(isset($yah_shabbat_after_hebrew_month_num ) && strlen($yah_shabbat_after_hebrew_month_num) > 0  ){
			    $params_a[21] =  array($yah_shabbat_after_hebrew_month_num , 'Integer' );
			}else{
			    $params_a[21] =  array( 0, 'Integer' );
			}	
			
			if(isset( $yah_shabbat_after_hebrew_day_num ) && strlen( $yah_shabbat_after_hebrew_day_num) > 0  ){
			    $params_a[22] =  array($yah_shabbat_after_hebrew_day_num , 'Integer' );
			}else{
			    $params_a[22] =  array( 0, 'Integer' );
			}
			
			
			// $shabbat_after_hebrew_date_format_english
			if(isset( $shabbat_after_hebrew_date_format_english ) &&  strlen($shabbat_after_hebrew_date_format_english) > 0 ){
				$params_a[23] =  array($shabbat_after_hebrew_date_format_english, 'String');
			}else{
				$params_a[23] =  array( '', 'String' );
			}
			
			
		//	CRM_Core_Error::debug(CRM_Core_DAO::composeQuery($insert_sql, $params_a), "");
			
			$dao = 		CRM_Core_DAO::executeQuery( $insert_sql,   $params_a ) ;
			$dao->free();
				
}


 




function fillYahrzeitTempTable($tmpHebCal , $extended_date_table,  $TempTableName, $contact_ids ){
	
	$rtn_data = array(); 
	
	
	//$extended_birth_date  = "";
	$extended_death_date  = "";
	
	
	
	// Now get field name for date of death before sunset
	$ed_result = civicrm_api3('CustomField', 'get', array(
			'sequential' => 1,
			'custom_group_id' => HebrewCalendar::EXTENDED_DATE_CUSTOM_FIELD_GROUP_NAME,
			'name' => HebrewCalendar::EXTENDED_DATE_CUSTOM_FIELD_DEATH_NAME ,
	));
	
	if($ed_result['is_error'] <> 0 || $ed_result['count'] == 0  ){
		$rtn_data['error_message'] = "Could not find custom field: '".HebrewCalendar::EXTENDED_DATE_CUSTOM_FIELD_DEATH_NAME."' ";
		return $rtn_data;
	
	}else{
		$tmp_values = $ed_result['values'][0];
		$extended_death_date = $tmp_values['column_name'];
	
	
	}
	
	if(  strlen($extended_death_date) == 0 ){
		$rtn_data['error_message'] = "Could not get SQL field name for '".HebrewCalendar::EXTENDED_DATE_CUSTOM_FIELD_DEATH_NAME."'";
		return $rtn_data;
	}
	
	
	/*
	$custom_field_yahrzeit_group_label = "Yahrzeit Details";
	$custom_field_yahrzeit_details_label = "Mourner observes the English date";



	$customFieldLabels = array($custom_field_yahrzeit_details_label );
	$extended_yahrzeit_table = "";
	$outCustomColumnNames = array();


	$error_msg = getCustomTableFieldNames($custom_field_yahrzeit_group_label, $customFieldLabels, $extended_yahrzeit_table, $outCustomColumnNames ) ;

	$yahrzeit_detail_column_name  =  $outCustomColumnNames[$custom_field_yahrzeit_details_label];

	if(strlen( $yahrzeit_detail_column_name) == 0){
		// print "<br>Error: There is no field with the name: '".$yahrzeit_detail_column_name."' ";
		return;
	}
	*/
	
	$extended_yahrzeit_table = ""; 
	$result = civicrm_api3('CustomGroup', 'get', array(
			'sequential' => 1,
			'name' => HebrewCalendar::YAH_RELATIONSHIPTYPE_CUSTOM_FIELD_GROUP_NAME,
			'extends' => 'Relationship', 
		
	));
	
	if($result['is_error'] == 0 && $result['count'] > 0  ){	
		//Yahrzeit_Details
		$tmp = $result['values'][0];
		if(isset($tmp['table_name'])){
			$extended_yahrzeit_table = $tmp['table_name'];
		}
	}
	
	if(strlen($extended_yahrzeit_table) == 0 ){
		$rtn_data['error_message'] = "Error: Could not get SQL table name for '".HebrewCalendar::YAH_RELATIONSHIPTYPE_CUSTOM_FIELD_GROUP_NAME."'";
		return $rtn_data;
	}
	
	
	
	$result = civicrm_api3('CustomField', 'get', array(
			'sequential' => 1,
			'custom_group_id' => HebrewCalendar::YAH_RELATIONSHIPTYPE_CUSTOM_FIELD_GROUP_NAME,
			'name' => HebrewCalendar::YAH_RELATIONSHIPTYPE_CUSTOM_FIELD_NAME,
	));
	
	$yahrzeit_detail_column_name = ""; 
	if($result['is_error'] == 0 && $result['count'] > 0  ){	
		$yahrzeit_detail_column_name = $result['values'][0]['column_name'];
		
	}


	if(strlen($yahrzeit_detail_column_name) == 0 ){
		$rtn_data['error_message'] = "Could not get SQL field name for '".HebrewCalendar::YAH_RELATIONSHIPTYPE_CUSTOM_FIELD_NAME."'";
		return $rtn_data;
	}
	
	if( strlen( $contact_ids) > 0){
		$contactids_sql = " AND contact_a.id IN ( $contact_ids )";
		 
	}else{
		$contactids_sql = "";
	}

	//
	//list($error_msg, $extended_date_table,  $extended_birth_date , $extended_death_date) = getCustomTableFieldNames();

	$lastname_tmp = "" ;

	$yahr_rel_type_id =  "0";

	$reltype_sql = "SELECT id as rel_type_id
	FROM  `civicrm_relationship_type` reltype
	WHERE reltype.name_a_b
	IN (
	'".HebrewCalendar::YAHRZEIT_RELATIONSHIP_TYPE_A_B_NAME."'
	) ";

	$reltype_dao =& CRM_Core_DAO::executeQuery( $reltype_sql,   CRM_Core_DAO::$_nullArray ) ;

	while ( $reltype_dao->fetch( ) ) {
		$yahr_rel_type_id = $reltype_dao->rel_type_id;
	}

	$reltype_dao->free();

	// Figure out count of how many mourners each deceased person has.
	$mourner_count_join = " JOIN ( SELECT contact_a.id as deceased_contact_id , count( contact_b.id) as mourner_count
FROM civicrm_contact AS contact_a
LEFT JOIN ".$extended_date_table." edt ON contact_a.id = edt.entity_id
LEFT JOIN civicrm_relationship as rel ON rel.contact_id_a = contact_a.id and rel.is_active = 1 and contact_a.is_deleted <> 1
AND rel.relationship_type_id = $yahr_rel_type_id
LEFT JOIN civicrm_contact as contact_b ON rel.contact_id_b = contact_b.id and contact_b.is_deleted <> 1
LEFT JOIN civicrm_relationship_type as reltype ON reltype.ID = rel.relationship_type_id
AND reltype.name_a_b IN ( '".HebrewCalendar::YAHRZEIT_RELATIONSHIP_TYPE_A_B_NAME."' )
left join ".$extended_yahrzeit_table." as yd ON rel.id = yd.entity_id AND rel.contact_id_b is NOT NULL
WHERE contact_a.contact_type = 'Individual' AND contact_a.is_deceased = 1 AND contact_a.is_deleted <> 1 $contactids_sql
AND (contact_b.id IS NULL or contact_b.is_deleted <> 1 )
GROUP BY contact_a.id ) as mourner_count ON mourner_count.deceased_contact_id = contact_a.id ";


	
	
	// Only include the record listing the deceased with no mourner IF the deceased has no mourners.
	$mourner_count_where = " AND ( contact_b.id IS NOT NULL OR ( contact_b.id is NULL AND mourner_count.mourner_count = 0 )   ) ";

	$tmp_subtype_name = HebrewCalendar::YAH_DECEASED_CONTACT_TYPE_NAME ; 
	//$tmp_subtype_name = "Deceased";
	
	
	
	$sql = "SELECT contact_b.id as contact_id, contact_b.sort_name as sort_name,
	contact_a.id as deceased_contact_id,
	contact_a.contact_sub_type LIKE '%".$tmp_subtype_name."%' as deceased_subtype_is_good,  
	contact_a.sort_name as deceased_name,
	reltype.name_a_b , contact_a.deceased_date,
	year(contact_a.deceased_date) as dyear,
	month(contact_a.deceased_date) as dmonth,
	day(contact_a.deceased_date) as dday,
	contact_a.deceased_date as ddate,
	edt.$extended_death_date  as d_before_sunset,
	yd.$yahrzeit_detail_column_name as mourner_preference,
	rel.id as relationship_id
	FROM civicrm_contact AS contact_a ".$mourner_count_join."
	LEFT JOIN $extended_date_table edt ON contact_a.id = edt.entity_id
	LEFT JOIN civicrm_relationship as rel ON rel.contact_id_a = contact_a.id
	and rel.is_active = 1 and contact_a.is_deleted <> 1 AND rel.relationship_type_id = $yahr_rel_type_id
	LEFT JOIN civicrm_contact as contact_b ON rel.contact_id_b = contact_b.id and contact_b.is_deleted <> 1
	LEFT JOIN civicrm_relationship_type as reltype ON reltype.ID = rel.relationship_type_id
	AND reltype.name_a_b IN ( '".HebrewCalendar::YAHRZEIT_RELATIONSHIP_TYPE_A_B_NAME."'  )
	left join $extended_yahrzeit_table as yd ON rel.id = yd.entity_id
	WHERE contact_a.contact_type = 'Individual'
	AND contact_a.deceased_date is not null
	AND contact_a.is_deceased = 1 $contactids_sql
	AND contact_a.is_deleted <> 1  ".$mourner_count_where ;


	
	
	$config = CRM_Core_Config::singleton( );

	$tmp_system_date_format = 	$config->dateInputFormat;
	if($tmp_system_date_format == 'dd/mm/yy'){
		$gregorian_date_format = "dd MM yyyy";
		 
	}else if($tmp_system_date_format == 'mm/dd/yy'){
		$gregorian_date_format = "MM dd, yyyy";
		 
	}else if( $tmp_system_date_format == 'd M yy' ){
		$gregorian_date_format = "dd MM yyyy";
	}else{
		//$gregorian_date_format = "MM dd, yyyy";
		//print "<br>Configuration Issue: Unrecognized System date format: ".$tmp_system_date_format;
		 
	}
	 
	// Get default yahrzeit setting for mourners with no preference (or no mourner)
	$params = array(
	  'version' => 3,
	  'sequential' => 1,
	  'name' => 'Use_Hebrew_Calendar_to_Calculate_Yahrzeits',
	);
	$result = civicrm_api('CustomField', 'getsingle', $params);

    if( isset($result['id'])){
		$fid = $result['id'];
    }else{
    	$fid = "";
    }
    
	$global_pref_field_name = "custom_".$fid;
	 
	

	$params = array(
			'version' => 3,
			'sequential' => 1,
			'contact_sub_type' => 'primary_organization',
			'return'  =>  $global_pref_field_name,
	);
	$result = civicrm_api('Contact', 'get', $params);
	if(isset($result['values'])){
		$values = $result['values'];
		$default_yahrzeit_cal_preference_raw = $values[0][$global_pref_field_name];
	}else{
		$default_yahrzeit_cal_preference_raw = "";
	}
	
	
	$default_yahrzeit_cal_pref = "hebrew";
	if( $default_yahrzeit_cal_preference_raw == "0"){
		$default_yahrzeit_cal_pref = "english";
	}else{
		$default_yahrzeit_cal_pref = "hebrew";
	}
	
	 
	// Check of mourner preference should be ignored.
	$params = array(
	  'version' => 3,
	  'sequential' => 1,
	  'name' => 'Use_The_Mourner_Preference_to_Calculate_Yahrzeits',
	);
	$result = civicrm_api('CustomField', 'getsingle', $params);

	if(isset($result['id'])){
		$fid = $result['id'];
		$global_pref_field_name = "custom_".$fid;
	}else{
		$fid = "";
	}
	
	 


	$params = array(
			'version' => 3,
			'sequential' => 1,
			'contact_sub_type' => 'primary_organization',
			'return'  =>  $global_pref_field_name,
	);
	$result = civicrm_api('Contact', 'get', $params);
	
	if(isset($result['values'])){
		$values = $result['values'];
		$honor_mourner_pref  = $values[0][$global_pref_field_name];
	}else{
		$honor_mourner_pref = "";
	}
	 
	
	 
	//  print "<br><br> honor mourner pref: ".$honor_mourner_pref;
    $mourner_contacts_count = 0; 
	
   // CRM_Core_Error::debug("Debug: Yahrzeit sql: ", $sql ); 
    
	$dao =& CRM_Core_DAO::executeQuery( $sql,   CRM_Core_DAO::$_nullArray ) ;
	while ( $dao->fetch( ) ) {

		// print "<br>got a record!";
	 //   figure out the previous and next yahrzeit for each record, then put the data in the temp table.
		$deceased_name = $dao->deceased_name  ;
		$deceased_contact_id = $dao->deceased_contact_id;
		$mourner_contact_id = $dao->contact_id;
		$is_contact_subtype_good = $dao->deceased_subtype_is_good;

	
		$mourner_contact_id = $dao->contact_id;
		$mourner_name =  $dao->sort_name ;
		$deceased_year = $dao->dyear;
		$deceased_month = $dao->dmonth;
		$deceased_day = $dao->dday;
		$deceased_date = $dao->ddate;
		$deceased_date_before_sunset = $dao->d_before_sunset;
		$yahrzeit_relationship_id = $dao->relationship_id;

			
		if( $honor_mourner_pref <> "0" ){
		 //print "<br>Check mourner pref from db";
			$mourner_observance_preference = $dao->mourner_preference;
		}else{
				
			$mourner_observance_preference = "";
		}
			
		require_once 'utils/HebrewCalendar.php';
		$tmpHebCal = new HebrewCalendar();

		// CRM_Core_Error::debug(" dname: ".$deceased_name." dyear: ".$deceased_year." dmonth: ".$deceased_month." dday: ".$deceased_day." ddate: ".$deceased_date) ;
		$hebrew_date_format = 'dd MM yy';
		$erev_start_flag = '1';
		
	//	$rtn_data['error_message'] = "contact id $deceased_contact_id - About to deal with subtype";
	//	return $rtn_data;
		// If the deceased contact does NOT have the contact subtype of "Deceased" then fix it.
		if(	$is_contact_subtype_good <> "1"){
		    // get all existing subtypes for this contact.
		    
		    $con_sub_result = civicrm_api3('Contact', 'get', array(
					'sequential' => 1,
					'id' => $deceased_contact_id,
			));  
			
			
			//$rtn_data['error_message'] = "subtypes for id $deceased_contact_id : is good? ".$is_contact_subtype_good;
			//return $rtn_data;
			if($con_sub_result['is_error']  == 0 && $con_sub_result['count']  == 1 ){
				
				$tmp_contact_subtypes = $con_sub_result['values'][0]['contact_sub_type'];
		      
				
				
				
				$clean_contact_subtypes = array();
				
				if(is_array( $tmp_contact_subtypes ) == false && strlen( $tmp_contact_subtypes ) == 0 ){
					// this is because there is no contact subtype yet.
					$clean_contact_subtypes = array(); 
				}else if( is_array( $tmp_contact_subtypes ) == false && strlen( $tmp_contact_subtypes ) > 0 ){
					
					$tmp = $tmp_contact_subtypes;
					$clean_contact_subtypes[] = $tmp ;
				}else if(is_array( $tmp_contact_subtypes )  ){
					$clean_contact_subtypes = $tmp_contact_subtypes ;
				}
				
				
				
				
				
				//$rtn_data['error_message'] = "Stuff: ".$clean_contact_subtypes_as_str;
				//return $rtn_data;
				 
				$clean_contact_subtypes[] = HebrewCalendar::YAH_DECEASED_CONTACT_TYPE_NAME;
				
				//$clean_contact_subtypes_as_str = implode(", ", $clean_contact_subtypes);
				//$rtn_data['error_message'] = "After update Stuff: ".$clean_contact_subtypes_as_str;
				//return $rtn_data;
				
				$subtypes_for_api = CRM_Utils_Array::implodePadded($clean_contact_subtypes);
				
				//$tmp_str = implode(", ", $subtypes_for_api );
				//$rtn_data['error_message'] = "API Stuff: ".$subtypes_for_api;
				//return $rtn_data;
		  	
			$con_update_result = civicrm_api3('Contact', 'create', array(
					'sequential' => 1,
					'id' => $deceased_contact_id,
					'contact_sub_type' => $subtypes_for_api,
			));
			
			if($con_update_result['is_error'] <> 0 ){
			    
			    $rtn_data['error_message'] = "API error on update of contact id  $deceased_contact_id : ".$con_update_result['error_message'];
			    return $rtn_data;
			}
			    
			}
			
		}else{
		  // Nothing to do, contact already has 'Deceased' as a subtype. 
		
		}
		
		
		
		//
		
		if($mourner_observance_preference <> '1' && $mourner_observance_preference <> '0'){
			// the Mourner preference is unknown,  use the system default preference.
			//  print "<br>Deceased: ".$deceased_name." mourner pref is unknown";
			if( $default_yahrzeit_cal_pref == 'hebrew'){
				$mourner_observance_preference = 0; // Hebrew calendar
					
			}else{
				$mourner_observance_preference = 1; // English calendar
		
			}
		
		}else if( $mourner_observance_preference == '0' ){
			$mourner_observance_preference = 0; // Hebrew calendar
			//print "<br>Deceased: ".$deceased_name." mourner pref is Hebrew";
		}else if(  $mourner_observance_preference == '1'){
			$mourner_observance_preference = 1; // English calendar
			//print "<br>Deceased: ".$deceased_name." mourner pref is English";
		
		}
		
		if( $deceased_date_before_sunset == '1'){
			$deceased_date_before_sunset_formated = 'Yes';
		}else if( $deceased_date_before_sunset == '0'){
			$deceased_date_before_sunset_formated = 'No';
		}
		
		// get English date of death formatted, if it exists.
		if(strlen($deceased_year) > 0 && strlen($deceased_month) > 0 && strlen($deceased_day) > 0){
			//$deceased_date_english_raw = new DateTime($deceased_year.'-'.$deceased_month.'-'.$deceased_day);
			$deceased_date_english_raw = $deceased_year.'-'.$deceased_month.'-'.$deceased_day;
				
			//$formated_english_deceased_date = $tmp_date->format('F d, Y');
		}else{
			//$formated_english_deceased_date = "Unknown date";
			$deceased_date_english_raw = "";
		}
		
		//
		$plaque_location = 'Unknown or No Plaque';		
		
		$yahrzeit_type_hebrew = '0' ;  // 'Hebrew'
		$yahrzeit_type_english = '1'; // English
		if(strlen($mourner_contact_id) == 0){
			$mourner_contact_id = 0;
		
		}
		
		if(isset($deceased_date_before_sunset_formated)){
			// do nothing.
		}else{
			$deceased_date_before_sunset_formated = "";
		}
		
		//
		$relationship_name_formated = $tmpHebCal->determine_relationship_name($mourner_contact_id, $deceased_contact_id  ) ;

		$hebrew_deceased_date  = $tmpHebCal->util_convert2hebrew_date($deceased_year, $deceased_month, $deceased_day, $deceased_date_before_sunset, $hebrew_date_format);


		$gregorian_date_format_plain = 'yyyy-mm-dd';

		$yahr_years_to_get = array( 1, 2, 3, 4, -1, -2, -3, -4) ;      // 1=next yahr, 2=yahr after next, etc.  -1 previous yahr, -2, yahr before previous one, etc.
		// //$prev_flag = 'prev',  'next';
		//
		//CRM_Core_Error::debug("About to loop through records", "");
		 
		foreach( $yahr_years_to_get as $year_offset){
	    
			$yahrzeit_date_tmp  = $tmpHebCal->util_get_yahrzeit_date($year_offset,  $deceased_year, $deceased_month, $deceased_day, $deceased_date_before_sunset, $erev_start_flag, $gregorian_date_format_plain);
			
			//print "<br>returned yahrzeit date tmp: ".$yahrzeit_date_tmp;
			
			$yahrzeit_date_formated_tmp = "";
			$tmp_yahrzeit_date_observe_english_formated = "";
			
			// Calculate English yahrzeit for mourners who observe the English anniversary.
			$tmp_yahrzeit_date_observe_english  = $tmpHebCal->getYahrzeitDateEnglishObservance($deceased_year, $deceased_month, $deceased_day, $year_offset);
				
			if( $year_offset == 1){
				// this is only done for the next yahrzeits ( ie year_offset =1 )
				$rtn_count =  $tmpHebCal->updateCiviCRMCalcYahrzeitFields( $deceased_contact_id, $yahrzeit_date_tmp, $tmp_yahrzeit_date_observe_english, $hebrew_deceased_date  );
			}
					
		//	print "<br> year_offset: $year_offset Deceased name:  $deceased_name ;  Hebrew yahrzeit date: ".$yahrzeit_date_tmp;
			
			insert_yahrzeit_record_into_temp_table($TempTableName,  $yahrzeit_type_hebrew, $mourner_contact_id, $mourner_name,  $deceased_contact_id,
					$deceased_name,  $deceased_date_english_raw,  $deceased_date_before_sunset_formated,
					$hebrew_deceased_date,
					$yahrzeit_date_tmp,
					$relationship_name_formated,
					$mourner_observance_preference, $plaque_location, $yahrzeit_relationship_id   ) ;
				
			insert_yahrzeit_record_into_temp_table($TempTableName,  $yahrzeit_type_english, $mourner_contact_id, $mourner_name,  $deceased_contact_id,
					$deceased_name,  $deceased_date_english_raw,  $deceased_date_before_sunset_formated,
					$hebrew_deceased_date,
					$tmp_yahrzeit_date_observe_english,
					$relationship_name_formated,
					$mourner_observance_preference, $plaque_location, $yahrzeit_relationship_id   ) ;
		
		}
			
			
		
		$mourner_contacts_count = $mourner_contacts_count + 1; 

	}

	$dao->free( );
	
	//CRM_Core_Error::debug("Mourner contact count: ". $mourner_contacts_count, "");
	$rtn_data['mourner_contacts_count'] = $mourner_contacts_count; 
	
	return $rtn_data;

}





