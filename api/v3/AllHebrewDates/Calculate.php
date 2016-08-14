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
 
function civicrm_api3_all_hebrew_dates_Calculate($params) {
 
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
			throw new API_Exception("Yahrzeit Error: ".$rtn_data['error_message'],  1234);
				
				
		}else{
			
				$rtn_data = fillYahrzeitTempTable( $tmpHebCal, $extended_date_table , $yahrzeit_table_name, $tmp_contact_ids   );
			
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
			throw new API_Exception(/*errorMessage*/ 'Could not create yahrzeit temp table', /*errorCode*/ 1234);
		}
	
}



function insert_yahrzeit_record_into_temp_table($TempTableName,  $yahrzeit_type, $mourner_contact_id, $mourner_name,  $deceased_contact_id,
		$deceased_name,   $formated_english_deceased_date,  $deceased_date_before_sunset_formated,
		$hebrew_deceased_date, $yahrzeit_date_tmp, $yahrzeit_date_formated_tmp, $relationship_name_formated,
		$mourner_observance_preference, $plaque_location, $yahrzeit_relationship_id   ) {

		 require_once 'utils/HebrewCalendar.php';
		 $tmpHebCal = new HebrewCalendar();
		 	
		 if(isset($yahrzeit_relationship_id)){
		 	
		 }else{
		 	
		 	$yahrzeit_relationship_id = "";
		 }
		 //print "<br>yahrzeit date raw: ".$yahrzeit_date_tmp;
		 $yar = explode( '-', $yahrzeit_date_tmp);
		 $deceased_date_before_sunset = '0';
		 $hebrew_date_format = 'hebrew';
		 $yahrzeit_hebrew_date_format_hebrew  = $tmpHebCal->util_convert2hebrew_date($yar[0], $yar[1], $yar[2], $deceased_date_before_sunset, $hebrew_date_format);


		 $hebrew_date_format = 'dd MM yy';
		 $yahrzeit_hebrew_date_format_english  = $tmpHebCal->util_convert2hebrew_date($yar[0], $yar[1], $yar[2], $deceased_date_before_sunset, $hebrew_date_format);
		 //print "<br>yar. heb. date: ".$yahrzeit_hebrew_date;


		  
	  $sql_date_format = "Y-m-d";
	   

	  $yahrzeit_date_raw  =   $yahrzeit_date_tmp ; // $row['yahrzeit_date_sort'] ;
	  if( $yahrzeit_date_raw <> "0000-00-00") {
	  	 
	  	 
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

	  	 

	  }
	  // verify this is a valid SQL date
	  if( strlen( $yahrzeit_date_tmp ) > 10 ){
	  	$yahrzeit_date_tmp = "null" ;

	  }else{
	  	$yahrzeit_date_tmp = "'$yahrzeit_date_tmp'" ;

	  }
	  $insert_sql = "INSERT INTO $TempTableName ( mourner_contact_id,
	  mourner_name, 
	  deceased_contact_id,
	  deceased_name, 
	  deceased_date, d_before_sunset,
	  hebrew_deceased_date, yahrzeit_date ,
	  yahrzeit_hebrew_date_format_hebrew, yahrzeit_hebrew_date_format_english,
	  yahrzeit_date_display, relationship_name_formatted, yahrzeit_type,
	  mourner_observance_preference, plaque_location,
	  yahrzeit_erev_shabbat_before, yahrzeit_shabbat_morning_before,
	  yahrzeit_erev_shabbat_after , yahrzeit_shabbat_morning_after,
	  yahrzeit_date_morning, yahrzeit_relationship_id
	  )
			VALUES($mourner_contact_id, %1 , $deceased_contact_id,
			%2, 
			'$formated_english_deceased_date', '$deceased_date_before_sunset_formated',
			'$hebrew_deceased_date' , $yahrzeit_date_tmp,
			%3, %4,
			%5, '$relationship_name_formated', %6,
			'$mourner_observance_preference', '$plaque_location',
			'$sql_friday_before', '$sql_saturday_before', '$sql_friday_after', '$sql_saturday_after', '$sql_yahrzeit_date_morning', '$yahrzeit_relationship_id'  )";
	  	
		 //	print "<br><br><b>Insert sql: </b>".$insert_sql;

			$params_a = array();
				
			if(strlen($mourner_name) > 0 ){
				$params_a[1] =  array( $mourner_name, 'String' );
			}else{
				$params_a[1] =  array( ' ', 'String' );
					
			}

			if(strlen($deceased_name) > 0){
				$params_a[2] =  array( $deceased_name, 'String' );
			}else{
				$params_a[2] =  array( ' ', 'String' );
			}

			

			if(strlen($yahrzeit_hebrew_date_format_hebrew) > 0 ){
				$params_a[3] =  array($yahrzeit_hebrew_date_format_hebrew, 'String');
			}else{
				$params_a[3] =  array( ' ', 'String' );
			}

			if(strlen($yahrzeit_hebrew_date_format_english) > 0 ){
				$params_a[4] =  array($yahrzeit_hebrew_date_format_english, 'String');
			}else{
				$params_a[4] =  array( ' ', 'String' );
			}

			if(strlen($yahrzeit_date_formated_tmp) > 0 ){
				$params_a[5] =  array($yahrzeit_date_formated_tmp, 'String');
			}else{
				$params_a[5] =  array( ' ', 'String' );
			}

			if(strlen($yahrzeit_type) > 0 ){
				$params_a[6] =  array($yahrzeit_type, 'String');
			}else{
				$params_a[6] =  array( ' ', 'String' );
			}
				
				
			//print "<br>sql params<br>: ";
			//print_r($params_a);
				
			$dao = 		CRM_Core_DAO::executeQuery( $insert_sql,   $params_a ) ;
			// print "<br> done with insert";
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
	AND contact_a.is_deceased = 1 $contactids_sql
	AND contact_a.is_deleted <> 1  ".$mourner_count_where ;


	$config = CRM_Core_Config::singleton( );

	$tmp_system_date_format = 	$config->dateInputFormat;
	if($tmp_system_date_format == 'dd/mm/yy'){
		$gregorian_date_format = "dd MM yyyy";
		 
	}else if($tmp_system_date_format == 'mm/dd/yy'){
		$gregorian_date_format = "MM dd, yyyy";
		 
	}else{
		print "<br>Configuration Issue: Unrecognized System date format: ".$tmp_system_date_format;
		 
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
	 
	// print "<br><br>pref field : ".$global_pref_field_name;

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
	//  print "<br><br>sql to fill temp table: ".$sql;
	 
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
	
	 
	// print "<br><br>pref field : ".$global_pref_field_name;

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

		//	print "<br><br>dname: ".$deceased_display_name." dyear: ".$deceased_year." dmonth: ".$deceased_month." dday: ".$deceased_day." ddate: ".$deceased_date;
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
		  
		
			// TODO: Use SQL, not API. API creates infinite loop becase this is called from a hook.
			/*
			$con_update_result = civicrm_api3('Contact', 'create', array(
					'sequential' => 1,
					'id' => $deceased_contact_id,
					'contact_sub_type' => $subtypes_for_api,
			));
			
			if($con_update_result['is_error'] <> 0 )
			    
			    $rtn_data['error_message'] = "API error on update of contact id  $deceased_contact_id : ".$con_update_result['error_message'];
			    return $rtn_data;
			    
			    */
			}
			
		}else{
		  // Nothing to do, contact already has 'Deceased' as a subtype. 
		
		}
		
		$relationship_name_formated = $tmpHebCal->determine_relationship_name($mourner_contact_id, $deceased_contact_id  ) ;

		$hebrew_deceased_date  = $tmpHebCal->util_convert2hebrew_date($deceased_year, $deceased_month, $deceased_day, $deceased_date_before_sunset, $hebrew_date_format);

		//      print " <br>Hebrew deceased date: ".$hebrew_deceased_date;

		$gregorian_date_format_plain = 'yyyy-mm-dd';

		$next_flag = 'next';
		$prev_flag = 'prev';

		$params = array(
		  'version' => 3,
		  'sequential' => 1,
		  'previous_next_flag' => $next_flag,
		  'gregorian_year' => $deceased_year,
		  'gregorian_month' => $deceased_month,
		  'gregorian_day' => $deceased_day,
		  'gregorian_before_after_sunset_flag' => $deceased_date_before_sunset,
		  'result_evening_start_flag' =>  $erev_start_flag,
		  'result_date_format' => $gregorian_date_format_plain

		);
		//$result = civicrm_api('YahrzeitDate', 'get', $params);

		//print_r( $result);

			


		$yahrzeit_date_tmp_next  = $tmpHebCal->util_get_yahrzeit_date($next_flag,  $deceased_year, $deceased_month, $deceased_day, $deceased_date_before_sunset, $erev_start_flag, $gregorian_date_format_plain);
		//    print "<br>Next yahrzeit date: ".$yahrzeit_date_tmp_next;

		$yahrzeit_date_tmp_prev  = $tmpHebCal->util_get_yahrzeit_date($prev_flag,  $deceased_year, $deceased_month, $deceased_day, $deceased_date_before_sunset, $erev_start_flag, $gregorian_date_format_plain);
		//    print "<br>Prev yahrzeit date: ".$yahrzeit_date_tmp_prev;


		$yahrzeit_date_formated_tmp_next  = $tmpHebCal->util_get_yahrzeit_date($next_flag,  $deceased_year, $deceased_month, $deceased_day, $deceased_date_before_sunset, $erev_start_flag, $gregorian_date_format );
		//print "<br>Next yahrzeit date (formatted): ".$yahrzeit_date_formated_tmp_next;

		$yahrzeit_date_formated_tmp_prev  = $tmpHebCal->util_get_yahrzeit_date($prev_flag,  $deceased_year, $deceased_month, $deceased_day, $deceased_date_before_sunset, $erev_start_flag, $gregorian_date_format );
		// print "<br>Prev. yahrzeit date (formatted): ".$yahrzeit_date_formated_tmp_prev;

		 

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




		if(strlen($deceased_year) > 0 && strlen($deceased_month) > 0 && strlen($deceased_day) > 0){
			$tmp_date = new DateTime($deceased_year.'-'.$deceased_month.'-'.$deceased_day);

			$formated_english_deceased_date = $tmp_date->format('F d, Y');
		}else{
			$formated_english_deceased_date = "Unknown date";

		}
		// print "<br>Formatted English deceased date".$formated_english_deceased_date  ;

		// Calculate English yahrzeit for mourners who observe the English anniversary.
		$tmp_yahrzeit_date_observe_english_next  = $tmpHebCal->getYahrzeitDateEnglishObservance($deceased_year, $deceased_month, $deceased_day, $next_flag);
		// print "<br>Next English yahr: ".$tmp_yahrzeit_date_observe_english_next;
		$tmp_yahrzeit_date_observe_english_formated_next =  $tmpHebCal->getYahrzeitDateEnglishObservanceFormated($deceased_year, $deceased_month, $deceased_day, $next_flag);


		$tmp_yahrzeit_date_observe_english_prev  = $tmpHebCal->getYahrzeitDateEnglishObservance($deceased_year, $deceased_month, $deceased_day, $prev_flag);
		// print "<br>Prev. English yahr: ".$tmp_yahrzeit_date_observe_english_prev;
		$tmp_yahrzeit_date_observe_english_formated_prev =  $tmpHebCal->getYahrzeitDateEnglishObservanceFormated($deceased_year, $deceased_month, $deceased_day, $prev_flag);
		// $mourner_observance_preference = 'Unknown';
		$plaque_location = 'Unknown or No Plaque';


		$yahrzeit_type_hebrew = '0' ;  // 'Hebrew'
		$yahrzeit_type_english = '1'; // English
		if(strlen($mourner_contact_id) == 0){
			$mourner_contact_id = 0;

		}

		$params = array(
		  'version' => 3,
		  'sequential' => 1,
		  'name' => HebrewCalendar::YAH_DECEASED_CUSTOM_FIELD_GROUP_NAME,
		);
		$result = civicrm_api('CustomGroup', 'getsingle', $params);

		if(isset($result['table_name'])){
			$heb_cal_table_name = $result['table_name'];
			$heb_cal_set_id = $result['id'];
		}else{
			$heb_cal_table_name = "";
		}

		if( strlen( $heb_cal_table_name) > 0){
			 

			$params = array(
			  'version' => 3,
			  'sequential' => 1,
			  'custom_group_id' =>  $heb_cal_set_id,
			  'name' => HebrewCalendar::YAH_NEXT_HEB_YAHRZEIT_NAME,
			);
			$result = civicrm_api('CustomField', 'getsingle', $params);
			
			if( isset ( $result['column_name'])){
				$col_name_next_heb_yahrzeit = $result['column_name'];
			}else{
				$col_name_next_heb_yahrzeit = "";
				
			}


			$params = array(
			  'version' => 3,
			  'sequential' => 1,
			  'custom_group_id' =>  $heb_cal_set_id,
			  'name' => HebrewCalendar::YAH_NEXT_ENGLISH_YAHRZEIT_NAME,
			);
			$result = civicrm_api('CustomField', 'getsingle', $params);
			
			if( isset ( $result['column_name'])){
				$col_name_next_english_yahrzeit = $result['column_name'];
			}else{
				$col_name_next_english_yahrzeit = "";
			}
			 
			$params = array(
			  'version' => 3,
			  'sequential' => 1,
			  'custom_group_id' =>  $heb_cal_set_id,
			  'name' => HebrewCalendar::YAH_HEB_DEATH_DATE_NAME,
			);
			$result = civicrm_api('CustomField', 'getsingle', $params);
				
			if( isset ( $result['column_name'])){
				$col_name_hebrew_date_of_death = $result['column_name'];
			}else{
				$col_name_hebrew_date_of_death = ""; 
			}
			
			
			if( strlen($col_name_next_heb_yahrzeit) > 0 && strlen($col_name_next_english_yahrzeit) > 0  ){   
					$dao_exists =& CRM_Core_DAO::executeQuery(
							"select count(*) as count from $heb_cal_table_name where entity_id =  $deceased_contact_id ", 
							CRM_Core_DAO::$_nullArray ) ;
		
					$rec_exists = false;
					if($dao_exists->fetch()){
						if( $dao_exists->count == "1" ){
							$rec_exists = true;
						}else{
							$rec_exists = false;
						}
		
					}
					 
					
					
					$yah_date_cleaned_for_sql  = "null";
					// 'Cannot determine yahrzeit date'
					if(  is_numeric (substr($yahrzeit_date_tmp_next, 0 , 4)) ){
						$yah_date_cleaned_for_sql = "'".$yahrzeit_date_tmp_next."'" ;
						
					}else{
						// all years must be numeric
						$yah_date_cleaned_for_sql  = "null";
						
					}
					
					//   $hebrew_data = $tmpHebCal::retrieve_hebrew_demographic_dates( $deceased_contact_id);
					if( $rec_exists){
						$sql_deceased_contact_record	= "UPDATE $heb_cal_table_name SET
						$col_name_next_heb_yahrzeit  = $yah_date_cleaned_for_sql,
						$col_name_next_english_yahrzeit = date_add( '$tmp_yahrzeit_date_observe_english_next' , INTERVAL 1 day),
						$col_name_hebrew_date_of_death = '".$hebrew_deceased_date."'
						WHERE entity_id =  $deceased_contact_id ";
		
					}else{
						$sql_deceased_contact_record = "INSERT INTO $heb_cal_table_name (entity_id , $col_name_next_heb_yahrzeit, $col_name_next_english_yahrzeit, $col_name_hebrew_date_of_death   )
						VALUES( $deceased_contact_id , $yah_date_cleaned_for_sql , date_add( '$tmp_yahrzeit_date_observe_english_next' , INTERVAL 1 day),
						'".$hebrew_deceased_date."'
		 )  ";
					}
					 
					$dao_update_dececased =& CRM_Core_DAO::executeQuery( $sql_deceased_contact_record,   CRM_Core_DAO::$_nullArray ) ;
					$dao_update_dececased->free();
			 
			 
		}
	}
		 
		 if(isset($deceased_date_before_sunset_formated)){
		 	// do nothing.
		 }else{
		 	$deceased_date_before_sunset_formated = "";
		 }
		 
		 
		//print "<br><br>Yahrzeit date formatted for SQL insert: ".$yahrzeit_date_tmp_next;
		insert_yahrzeit_record_into_temp_table($TempTableName,  $yahrzeit_type_hebrew, $mourner_contact_id, $mourner_name,  $deceased_contact_id,
				$deceased_name,  $formated_english_deceased_date,  $deceased_date_before_sunset_formated,
				$hebrew_deceased_date,
				$yahrzeit_date_tmp_next,
				$yahrzeit_date_formated_tmp_next,
				$relationship_name_formated,
				$mourner_observance_preference, $plaque_location, $yahrzeit_relationship_id   ) ;
			

			
		insert_yahrzeit_record_into_temp_table($TempTableName,  $yahrzeit_type_hebrew, $mourner_contact_id, $mourner_name, $deceased_contact_id,
				$deceased_name,   $formated_english_deceased_date,  $deceased_date_before_sunset_formated,
				$hebrew_deceased_date,
				$yahrzeit_date_tmp_prev,
				$yahrzeit_date_formated_tmp_prev,
				$relationship_name_formated,
				$mourner_observance_preference, $plaque_location, $yahrzeit_relationship_id   ) ;
			
		insert_yahrzeit_record_into_temp_table($TempTableName,  $yahrzeit_type_english, $mourner_contact_id, $mourner_name,  $deceased_contact_id,
				$deceased_name,  $formated_english_deceased_date,  $deceased_date_before_sunset_formated,
				$hebrew_deceased_date,
				$tmp_yahrzeit_date_observe_english_next,
				$tmp_yahrzeit_date_observe_english_formated_next,
				$relationship_name_formated,
				$mourner_observance_preference, $plaque_location, $yahrzeit_relationship_id   ) ;
			
		insert_yahrzeit_record_into_temp_table($TempTableName,  $yahrzeit_type_english, $mourner_contact_id, $mourner_name,  $deceased_contact_id,
				$deceased_name,  $formated_english_deceased_date,  $deceased_date_before_sunset_formated,
				$hebrew_deceased_date,
				$tmp_yahrzeit_date_observe_english_prev,
				$tmp_yahrzeit_date_observe_english_formated_prev,
				$relationship_name_formated,
				$mourner_observance_preference, $plaque_location, $yahrzeit_relationship_id   ) ;
		
		$mourner_contacts_count = $mourner_contacts_count + 1; 

	}

	$dao->free( );
	
	$rtn_data['mourner_contacts_count'] = $mourner_contacts_count; 
	
	return $rtn_data;
	
	

}


