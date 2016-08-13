<?php

class HebrewCalendar{

	// Put Purim in Adar II, if its not a leap year it should be shifted to Adar.
	// yom hazikaron needs to be shifted if it or the day after fall on a shabbat.
	// yom haatzmaut needs to be shifted if it or the day before fall on a shabbat.
	private  $jewish_holidays_major = array("rosh_hashana" => "1/1",
			"rosh_hashana_2" => "1/2",
			"yom_kippur" => "1/10",
			"sukkot" => "1/15",
			"shemini_atzeret" => "1/22",
			"simchat_torah" => "1/23",
			"hannukah_1" => "3/24",
			"hannukah_2" => "3/25",
			"hannukah_3" => "3/26",
			"hannukah_4" => "3/27",
			"hannukah_5" => "3/28",
			"hannukah_6" => "3/29",
			"hannukah_7" => "3/30",
			"hannukah_8" => "4/1",
			"purim" => "7/14",
			"passover" => "8/15",
			"shavuot" => "10/6",
			"tisha_b_av" => "12/9" ,
			"yom_ha_shoah" => "8/27",
			"yom_hazikaron" => "9/4",
			"yom_haatzmaut" => "9/5",
			"yom_yerushalayim" => "9/28");
		
		
	private  $jewish_holidays_minor = array('aaa' => 'a');

	// Should always be set to false in a production environment.
	const ALWAYS_CLEAR_TEMP_TABLE = false;
	
	const YAHRZEIT_TEMP_TABLE_NAME = "civicrm_fountaintribe_yahrzeits_temp";  //  old table name: "pogstone_temp_yahrzeits";

	// After how many minutes should yahrzeit cache data be recalculated?
	// In a production environment this is typically 12 hours, ie 720 minutes.
	const YAHRZEIT_CACHE_TIMEOUT = "15";


	const HEBREW_MONTH_TISHREI = "1";
	const HEBREW_MONTH_HESHVAN = "2";
	const HEBREW_MONTH_KISLEV = "3";
	const HEBREW_MONTH_TEVET = "4";
	const HEBREW_MONTH_SHEVAT = "5";
	const HEBREW_MONTH_ADAR = "6";
	const HEBREW_MONTH_ADAR_2 = "7";
	const HEBREW_MONTH_NISAN = "8";
	const HEBREW_MONTH_IYYAR = "9";
	const HEBREW_MONTH_SIVAN = "10";
	const HEBREW_MONTH_TAMUZ = "11";
	const HEBREW_MONTH_AV    = "12";
	const HEBREW_MONTH_ELUL  = "13";
	
	
	const EXTENDED_DATE_CUSTOM_FIELD_GROUP_TITLE = "Extended Date Information";
	const EXTENDED_DATE_CUSTOM_FIELD_GROUP_NAME = "Extended_Date_Information";
	
	const EXTENDED_DATE_CUSTOM_FIELD_BIRTH_TITLE = "Birth Date Before Sunset";
	const EXTENDED_DATE_CUSTOM_FIELD_BIRTH_NAME = "Birth_Date_Before_Sunset";
	
	const EXTENDED_DATE_CUSTOM_FIELD_DEATH_TITLE = "Death Date Before Sunset";
	const EXTENDED_DATE_CUSTOM_FIELD_DEATH_NAME = "Death_Date_Before_Sunset";
	
	
	// Original title: Yahrzeit Details   
	// Original name: Yahrzeit_Details
	const YAH_RELATIONSHIPTYPE_CUSTOM_FIELD_GROUP_TITLE = "Yahrzeit Preferences";
	const YAH_RELATIONSHIPTYPE_CUSTOM_FIELD_GROUP_NAME = "Yahrzeit_Preferences";
	
	
	const YAH_RELATIONSHIPTYPE_CUSTOM_FIELD_TITLE = "Does mourner observe the English date?";
	const YAH_RELATIONSHIPTYPE_CUSTOM_FIELD_NAME = "Mourner_observes_the_English_date";
	
	const YAH_DECEASED_CONTACT_TYPE_NAME = "Deceased";
	const YAH_DECEASED_CONTACT_TYPE_TITLE = "Deceased";
	
	const YAH_DECEASED_CUSTOM_FIELD_GROUP_TITLE = "Yahrzeit Dates (Calculated Automatically)";
	// Original name: Hebrew_Calendar_Demographics
	const YAH_DECEASED_CUSTOM_FIELD_GROUP_NAME = "Yahrzeit_Dates";
	
	// custom fields for above custom group
	const YAH_NEXT_HEB_YAHRZEIT_NAME = "Next_Hebrew_Yahrzeit";
	const YAH_NEXT_HEB_YAHRZEIT_TITLE = "Next Hebrew Yahrzeit (Starts at sunset on this date)";
	
	const YAH_NEXT_ENGLISH_YAHRZEIT_NAME = "Next_English_Yahrzeit";
	const YAH_NEXT_ENGLISH_YAHRZEIT_TITLE = "Next English Yahrzeit";
	
	const YAH_HEB_DEATH_DATE_NAME = "Hebrew_Date_of_Death";
	const YAH_HEB_DEATH_DATE_TITLE = "Hebrew Date of Death";
	
	
	const HEB_BIRTH_CUSTOM_FIELD_GROUP_TITLE = "Hebrew Birth Dates (Calculated Automatically)";
	const HEB_BIRTH_CUSTOM_FIELD_GROUP_NAME = "Hebrew_Birth_Dates";
	
	const HEB_BIRTH_DATE_NAME = "Hebrew_Date_of_Birth";
	const HEB_BIRTH_DATE_TITLE = "Hebrew Date of Birth";


	const HEB_NEXT_BIRTHDAY_NAME = "Next_Hebrew_Birthday";
	const HEB_NEXT_BIRTHDAY_TITLE = "Next Hebrew Birthday";
	
	const HEB_EARLIEST_BARBAT_MITZVAH_NAME = "Hebrew_Earliest_BarBat_Date";
	const HEB_EARLIEST_BARBAT_MITZVAH_TITLE = "Earliest Possible Bar/Bat Mitzvah Date (Starts at sunset on this date)";
	
	const RELIGIOUS_CUSTOM_FIELD_GROUP_NAME = "Religious";
	const RELIGIOUS_CUSTOM_FIELD_GROUP_TITLE = "Religious";
	
	const PLAQUE_CUSTOM_FIELD_GROUP_TITLE = "Memorial Plaque Info";
	const PLAQUE_CUSTOM_FIELD_GROUP_NAME = "Plaque Info";
	
	
// Yahrzeit - correct spelling, per hebcal.com

	// TODO: Fix original names, titles,  in older databases. 
// Yarzheit - incorrect spelling, fix via SQL as needed.  also spaces used to be used in the 'name'

	// original title: 'Yarzheit observed by'
	// original name: 'Yarzheit observed by'
	const YAHRZEIT_RELATIONSHIP_TYPE_A_B_TITLE = "Yahrzeit observed by";
	const YAHRZEIT_RELATIONSHIP_TYPE_A_B_NAME = "Yahrzeit_observed_by";
	
	const YAHRZEIT_RELATIONSHIP_TYPE_B_A_TITLE =  "Yahrzeit observed in memory of";
	const YAHRZEIT_RELATIONSHIP_TYPE_B_A_NAME = "Yahrzeit_observed_in_memory_of";
	
	
	function determine_relationship_name($mourner_contact_id, $deceased_contact_id  ){
	
		if(strlen($mourner_contact_id) == 0){
			return '';
		}
	
	
		$formated_rel_name = '';
		$sql = "(SELECT label_b_a as label , name_b_a as rel_api_name,  civicrm_option_value.label as gender_label
		FROM `civicrm_relationship` rel, civicrm_relationship_type reltype, civicrm_contact con
		LEFT JOIN civicrm_option_value ON civicrm_option_value.option_group_id = 3 AND con.gender_id = civicrm_option_value.value
		where
		rel.relationship_type_id = reltype.id
		and contact_id_a = $mourner_contact_id and contact_id_b = $deceased_contact_id
		and contact_id_b = con.id
		and rel.is_active = 1  )
		UNION
		(SELECT label_a_b as label , name_a_b as rel_api_name,  civicrm_option_value.label as gender_label
		FROM `civicrm_relationship` rel, civicrm_relationship_type reltype, civicrm_contact con
		LEFT JOIN civicrm_option_value ON civicrm_option_value.option_group_id = 3 AND con.gender_id = civicrm_option_value.value
		where
		rel.relationship_type_id = reltype.id
		and contact_id_a = $deceased_contact_id and contact_id_b = $mourner_contact_id
		and contact_id_a = con.id
		and rel.is_active = 1)
	
		";
	
		$dao =& CRM_Core_DAO::executeQuery( $sql,   CRM_Core_DAO::$_nullArray ) ;
		$first_time = true;
		while ( $dao->fetch( ) ) {
	
			$tmp_label = $dao->label;
			$tmp_rel_api_name = $dao->rel_api_name;
			$deceased_gender = $dao->gender_label;
	
			$seperator = ', ';
			 
			// print "<hr>Current relationship label from db: ".$tmp_label;
	
			if($tmp_rel_api_name <> HebrewCalendar::YAHRZEIT_RELATIONSHIP_TYPE_A_B_NAME) {
	
				// print "<br>current label is not yahzeit, so need to clean up for return value. ";
				if(strlen($formated_rel_name) > 0){
					//  print "<br>  a) formatted rel name: ".$formated_rel_name;
	
	
	
					$formated_rel_name = $formated_rel_name.$seperator;
					//   print "<br>  b) formatted rel name: ".$formated_rel_name ;
				}
				//  print "<br>Dirty current label: ".$tmp_label;
	
				$tmp_label = strtolower ( $tmp_label);
				$pos = strripos($tmp_label , ' of' );
				if($pos){
					$tmp_label = substr( $tmp_label , 0,  $pos);
				}
	
				$pos = strripos($tmp_label , ' by' );
				if($pos){
					$tmp_label = substr( $tmp_label , 0,  $pos);
				}
	
				// Cleaned current label. (lower cased, and of,by removed) : $tmp_label;
	
				if($deceased_gender == "Female"){
					if($tmp_label == "parent"){ $tmp_label = "mother"; }
					else if($tmp_label == "spouse"){ $tmp_label = "wife"; }
					else if($tmp_label == "sibling"){ $tmp_label = "sister"; }
					else if($tmp_label == "grandparent"){ $tmp_label = "grandmother"; }
					else if($tmp_label == "step-parent"){ $tmp_label = "step-mother"; }
					else if($tmp_label == "child"){ $tmp_label = "daughter"; }
					else if($tmp_label == "step-child"){ $tmp_label = "step-daughter"; }
					else if($tmp_label == "grandchild"){ $tmp_label = "granddaughter"; }
					else if($tmp_label == "aunt is"){ $tmp_label = "niece";}
					else if($tmp_label == "uncle is"){ $tmp_label = "niece";}
					else if($tmp_label == "parent-in-law"){ $tmp_label = "mother-in-law" ; }
					else if($tmp_label == "child-in-law"){ $tmp_label = "daughter-in-law" ; }
					else if($tmp_label == "sibling-in-law"){ $tmp_label = "sister-in-law" ; }
	
	
				}else if($deceased_gender == "Male"){
					if($tmp_label == "parent"){ $tmp_label = "father"; }
					else if($tmp_label == "spouse"){ $tmp_label = "husband"; }
					else if($tmp_label == "sibling"){ $tmp_label = "brother"; }
					else if($tmp_label == "grandparent"){ $tmp_label = "grandfather"; }
					else if($tmp_label == "step-parent"){ $tmp_label = "step-father"; }
					else if($tmp_label == "child"){ $tmp_label = "son"; }
					else if($tmp_label == "step-child"){ $tmp_label = "step-son"; }
					else if($tmp_label == "grandchild"){ $tmp_label = "grandson"; }
					else if($tmp_label == "aunt is"){ $tmp_label = "nephew";}
					else if($tmp_label == "uncle is"){ $tmp_label = "nephew";}
					else if($tmp_label == "parent-in-law"){ $tmp_label = "father-in-law" ; }
					else if($tmp_label == "child-in-law"){ $tmp_label = "son-in-law" ; }
					else if($tmp_label == "sibling-in-law"){ $tmp_label = "brother-in-law" ; }
	
				}else{
					if($tmp_label == "aunt is"){ $tmp_label = "niece/nephew";}
					else if($tmp_label == "uncle is"){ $tmp_label = "niece/nephew";}
				}
	
				$formated_rel_name = $formated_rel_name.$tmp_label;
				$first_time = false;
			}
	
	
		}
		$dao->free( );
	
		return $formated_rel_name;
	}
	
	
	
	/******************************************************************
	 *   This function takes in a English date, and returns the name of
	 *   the Jewish holiday. If there is no holiday, then  an empty
	 *   string is returned.
	 ******************************************************************/
	function get_rosh_hodesh_name($iyear, $imonth, $iday){

		$tmp_name = "";
		$date_before_sunset = 1;
		$hebrew_date_format = 'mm/dd/yy' ;
		$heb_date =  self::util_convert2hebrew_date($iyear, $imonth, $iday, $date_before_sunset, $hebrew_date_format);

		$heb_date_array = explode ( "/" , $heb_date ) ;
		$heb_month = $heb_date_array[0];
		$heb_day = $heb_date_array[1];
		$heb_year = $heb_date_array[2];

		if($heb_month <> "1"){
			if($heb_day == "1"){
				$julian_date = gregoriantojd($imonth,$iday,$iyear);
				//$month_name = self::util_get_hebrew_month_name( $julian_date, $heb_date);
				$tmp_name = "Rosh Hodesh ".$month_name ;


			}else if( $heb_day == "30"){
				// TODO: Need to advance Jullian date and Hebrew date to the next day.
				$tmp_name = "Rosh Hodesh ".$month_name ;

			}else{
				$tmp_name = "";

			}
		}

		return $tmp_name;

	}



	/******************************************************************
	 *   This function takes in a English date, and returns the name of
	 *   the Jewish holiday. If there is no holiday, then  an empty
	 *   string is returned.
	 ******************************************************************/
	function get_jewish_holiday_name($iyear, $imonth, $iday){

		$date_before_sunset = 1;
		$hebrew_date_format = 'mm/dd/yy' ;
		$heb_date =  self::util_convert2hebrew_date($iyear, $imonth, $iday, $date_before_sunset, $hebrew_date_format);

		$heb_date_array = explode ( "/" , $heb_date ) ;
		$heb_month = $heb_date_array[0];
		$heb_day = $heb_date_array[1];
		$heb_year = $heb_date_array[2];

		$heb_mm_dd = $heb_month.'/'.$heb_day;
		// Do special Purim logic for leap years.
		// When there are 2 Adars, then Purim is celebrated during Adar II.
		if(self::is_hebrew_year_leap_year( $heb_year)){
			$this->jewish_holidays_major['purim'] = "7/14";
		}else{
			$this->jewish_holidays_major['purim'] = "6/14";
		}



		//If the 5th of Iyar falls on a Friday or Saturday, Yom HaAtzmaut is moved up to the preceding Thursday.
		// If the 5th of Iyar is on a Monday,  Yom HaAtzmaut is postponed to Tuesday.
		// Also, Yom HaZikaron is always observed the day before Yom HaAtzmaut.
		$gregorian_date_format =  'dd-mm-yyyy';
		$erev_start_flag = '0';
		$iyar_heb_month = '9';
		$iyar_5_heb_day = '5';

		$iyar_5_gregorian_date_tmp = self::util_convert_hebrew2gregorian_date($heb_year, $iyar_heb_month, $iyar_5_heb_day, $erev_start_flag , $gregorian_date_format);
		$iyar_5_tmp = new DateTime($iyar_5_gregorian_date_tmp);
		$iyar_5_timestamp = $iyar_5_tmp->getTimestamp();
		$iyar_5_day_of_week = date('w', $iyar_5_timestamp);
		if( $iyar_5_day_of_week == '5' ){
	  // Iyar 5 falls on a Friday, back it up 1 day.
			$this->jewish_holidays_major['yom_haatzmaut'] = "9/4";
			$this->jewish_holidays_major['yom_hazikaron'] = "9/3";
			 

		}else if( $iyar_5_day_of_week == '6'){
			// Iyar 5 falls on a Saturday, back it up 2 days.
			$this->jewish_holidays_major['yom_haatzmaut'] = "9/3";
			$this->jewish_holidays_major['yom_hazikaron'] = "9/2";

		}else if($iyar_5_day_of_week == '1'){
			// Iyar 5 falls on a Monday, push it out 1 day to Tuesday.
			$this->jewish_holidays_major['yom_haatzmaut'] = "9/6";
			$this->jewish_holidays_major['yom_hazikaron'] = "9/5";
		}
		/************************************************************************/
		// At this point, we have the correct adjusted dates for purim, yom_haatzmaut and yom_hazikaron



		// Now we may need to adjust the last 2 nighs of Hannukah, if there is no "Kislev 30" this year.
		//$heb_kislev_month = '3';
		$heb_kislev_last_day = '30';

		if(!( self::verify_hebrew_date( $heb_year  , HebrewCalendar::HEBREW_MONTH_KISLEV, $heb_kislev_last_day) ) ) {
			$this->jewish_holidays_major['hannukah_7'] = "4/1";
			$this->jewish_holidays_major['hannukah_8'] = "4/2";
		}


		// Go ahead and get human-readable names of holidays.
		switch($heb_mm_dd){
	 	case $this->jewish_holidays_major['rosh_hashana'] :
	 		$holiday_name = "Rosh Hashana";
	 		break;
	 	case $this->jewish_holidays_major['rosh_hashana_2'] :
	 		$holiday_name = "Rosh Hashana II";
	 		break;
	 	case $this->jewish_holidays_major['yom_kippur'] :
	 		$holiday_name = "Yom Kippur";
	 		break;
	 	case $this->jewish_holidays_major['sukkot'] :
	 		$holiday_name = "Sukkot";
			 break;
	 	case $this->jewish_holidays_major['shemini_atzeret'] :
	 		$holiday_name = "Shemini Atzeret";
	 		break;
	 	case $this->jewish_holidays_major['simchat_torah'] :
	 		$holiday_name = "Simchat Torah";
	 		break;
	 	case $this->jewish_holidays_major['hannukah_1'] :
	 		$holiday_name = "Hannukah 1 Candle";
	 		break;
	 	case $this->jewish_holidays_major['hannukah_2'] :
	 		$holiday_name = "Hannukah 2 Candles";
	 		break;
	 	case $this->jewish_holidays_major['hannukah_3'] :
	 		$holiday_name = "Hannukah 3 Candles";
	 		break;
	 	case $this->jewish_holidays_major['hannukah_4'] :
	 		$holiday_name = "Hannukah 4 Candles";
	 		break;
	 	case $this->jewish_holidays_major['hannukah_5'] :
	 		$holiday_name = "Hannukah 5 Candles";
	 		break;
	 	case $this->jewish_holidays_major['hannukah_6'] :
	 		$holiday_name = "Hannukah 6 Candles";
	 		break;
	 	case $this->jewish_holidays_major['hannukah_7'] :
	 		$holiday_name = "Hannukah 7 Candles";
	 		break;
	 	case $this->jewish_holidays_major['hannukah_8'] :
	 		$holiday_name = "Hannukah 8 Candles";
	 		break;
	 	case $this->jewish_holidays_major['purim'] :
	 		$holiday_name = "Purim";
	 		break;
	 	case $this->jewish_holidays_major['passover'] :
	 		$holiday_name = "Passover";
	 		break;
	 	case $this->jewish_holidays_major['shavuot'] :
	 		$holiday_name = "Shavuot";
	 		break;
	 	case $this->jewish_holidays_major['tisha_b_av'] :
	 		$holiday_name = "Tish'a B'Av";
	 		break;
	 	case $this->jewish_holidays_major['yom_ha_shoah'] :
	 		$holiday_name = "Yom HaShoah";
	 		break;
	 	case $this->jewish_holidays_major['yom_hazikaron'] :
	 		$holiday_name = "Yom HaZikaron";
	 		break;
	 	case $this->jewish_holidays_major['yom_haatzmaut'] :
	 		$holiday_name = "Yom HaAtzmaut";
	 		break;
	 	case $this->jewish_holidays_major['yom_yerushalayim'] :
	 		$holiday_name = "Yom Yerushalayim";
	 		break;
		}

		return $holiday_name;
	}



	/******************************************************************
	 * This function takes a Hebrew date as input parms: yyyy, mm, dd
	 * and returns it nicely formated as: dd HebrewMonthName yyyy
	 *
	 *
	 *********************************************************************/
	function util_formatHebrewDate(&$iyear, &$imonth, &$iday){

		if($imonth==''){

			return "Month is required";
		}else if($iday=='' ){
			return "Day is required";

		}else if($iyear==''){

			return "Year is required";
		}else{
			$julian_date =  cal_to_jd ( CAL_JEWISH  ,  $imonth , $iday ,$iyear  );
			$heb_month_name = jdmonthname($julian_date,4);
			 
			$formated_hebrew_date = "$iday $heb_month_name $iyear" ;
			return $formated_hebrew_date ;
		}

	}



	/****************************************************************************
	 *   This function takes a English date then returns the sunset time.
	 *
	 *
	 *
	 ******************************************************************************/
	function retrieve_sunset_or_candlelighting_times($iyear, $imonth, $iday, $sunset_or_candle){

		if(strlen($iyear) < 1){
			return 'Unknow year, cannot do sunset/candlelighting time';

		}

		$tmp_date = $iyear.'-'.$imonth.'-'.$iday." 5:00";
		$date = new DateTime($tmp_date);
		$caldate_timestamp=  $date->getTimestamp();

		$dst_in_use = date("I", $caldate_timestamp);
		//$tmp_UTC_offset =  date("O", $tmp_time);
		//print "DST: ".$dst_in_use."<br>";
		//print "UTC offset: ".$tmp_UTC_offset."<br>";


		$week_day_num = date( 'w', $caldate_timestamp);
		// Check if this is Friday.
		if($week_day_num == '5'){

			//$cal_pref_table_name = "civicrm_value_primary_organization_calendar_pr_16";
			require_once('utils/util_custom_fields.php');

			$custom_field_group_label = "Calendar Preferences";
			$customFieldLabels = array();

			$custom_field_zenith_label = "Zenith Used to Calculate Sunset";
			$customFieldLabels[] = $custom_field_zenith_label   ;

			$custom_field_minutes_offset = "Number of Minutes Offset";
			$customFieldLabels[] = $custom_field_minutes_offset ;


			$custom_fields_candle_offset = "Number of Minutes before sundown to light candles";
			$customFieldLabels[] = $custom_fields_candle_offset ;

			$outCustomColumnNames = array();


			$error_msg = getCustomTableFieldNames($custom_field_group_label, $customFieldLabels, $sql_table_name, $outCustomColumnNames ) ;



			if(strlen( $error_msg) > 0){
				print "<br>Configuration error: ".$error_msg;
				return "";


			}
			$sql_zenith_field  =  $outCustomColumnNames[$custom_field_zenith_label];
			$sql_minutes_offset_field = $outCustomColumnNames[$custom_field_minutes_offset];
			$sql_minutes_candle_offset = $outCustomColumnNames[$custom_fields_candle_offset];
			//

			$sql = "Select geo_code_1, geo_code_2, ".$sql_zenith_field." as zenith,
	".$sql_minutes_offset_field." as minutes_offset,
	".$sql_minutes_candle_offset." as candle_offset
	from civicrm_contact AS contact_a
	left join civicrm_address on contact_a.id = civicrm_address.contact_id
	left join civicrm_state_province on civicrm_address.state_province_id = civicrm_state_province.id
	left join ".$sql_table_name." as cal_prefs on contact_a.id = cal_prefs.entity_id
	WHERE
	contact_a.contact_sub_type =  'Primary_Organization' AND
	civicrm_address.is_primary = 1
	order by contact_a.id ";
			$zenith = '';

			// print "<br>sunset sql: ".$sql;
			$dao =& CRM_Core_DAO::executeQuery( $sql,   CRM_Core_DAO::$_nullArray ) ;

			if ( $dao->fetch( ) ) {
	   $latitude = $dao->geo_code_1;
	   $longitude = $dao->geo_code_2;
	   $zenith = $dao->zenith;
	   $minutes_offset = $dao->minutes_offset;
	   $candle_offset = $dao->candle_offset;
	    
			}else{
	   $no_data = true;

			}


			$dao->free( );

			if($no_data){ return "Unknown Location, cannot do candlelighting/sunset time";  }


			$default_zenith = 89.2;
			if(strlen($zenith) == 0 || $zenith == 0 ){
				$zenith = $default_zenith;

			}


			if(strlen($minutes_offset) == 0){
				$minutes_offset = 0;

			}


			/*
			 $civilian_zenith=96;
			 $nautical_zenith=102;
			 $astronomical_zenith=108;
			 */

			$dateTimeZoneUTC = new DateTimeZone('UTC');
			$local_timezone = new DateTimeZone(date_default_timezone_get());

			$dateTimeUTC = new DateTime($tmp_date, $dateTimeZoneUTC);
			$dateTimeLocal = new DateTime($tmp_date, $local_timezone);


			$tmp_off= timezone_offset_get($local_timezone ,$dateTimeUTC );
			$tmp_UTC_offset = round($tmp_off/3600);
			 
			//print "<br>offset to GMT/UTC: ".$tmp_UTC_offset;
			// print "<br>zenith: ".$zenith;




			//$sunset_time = date_sunset($tmp_time, SUNFUNCS_RET_STRING, $latitude, $longitude, $zenith, $tmp_UTC_offset);
			$sunset_time = date_sunset($caldate_timestamp, SUNFUNCS_RET_STRING, $latitude, $longitude, $zenith, $tmp_UTC_offset);




			///	print "<br>Sunset time: ".$sunset_time;
			// SUNFUNCS_RET_DOUBLE
			//$tmp = date( 'M j, Y; g:i a' , $caldate_timestamp) ;
			//print "<br>caldate: ".$tmp;

			//print "<br>lat: ".$latitude;
			$working_time_orig = date_sunset($caldate_timestamp, SUNFUNCS_RET_TIMESTAMP, $latitude, $longitude, $zenith, $tmp_UTC_offset);

			$sunset_time_str =  date_sunset($caldate_timestamp, SUNFUNCS_RET_STRING, $latitude, $longitude, $zenith, $tmp_UTC_offset);

			//print "<br>sunset time str: ".$sunset_time_str ;
			//$tmp = date( 'M j, Y; g:i a' , $working_time_orig) ;

			//print "<br>sunset: ".$tmp;
			//print "<br>min. offset : ".$minutes_offset;

			// Add in configured offset.
			$working_time_adj  = strtotime("+$minutes_offset", $working_time_orig);

			//print "<br>time org:".$working_time_orig;
			//print "<br>time adj:".$working_time_adj;

			if(strlen($candle_offset) == 0 || $candle_offset ==0 ){
				$candle_offset = "-18";


			}
			$working_candle_time = strtotime("+$candle_offset minutes", $working_time_adj );

			$format_sunset_time = date('g:i', $working_time_adj);


			$format_candle_time = date('g:i', $working_candle_time );

			$am_pm_symbol = 'pm';
			if($sunset_or_candle == 'sunset'){
					
				$output_time_formated  =  $format_sunset_time.$am_pm_symbol ;

			}else if($sunset_or_candle == 'candle' ){
					
					
				// $output_time_formated  = $format_candle_time.$am_pm_symbol  ;

		  // print "<br>candle time:".$output_time_formated;

			}else{
				print "<br>Unknown output flag for sunset/candlelighting: ".$sunset_or_candle."<br>";

			}


			// Format sunset time as hours and minutes.
			$sunset_hour = floor($sunset_time);
			//print "<br>sunset time: ".$sunset_time;

			//print "<br>sunset hour: ".$sunset_hour;
			$tmp_min = $sunset_time - $sunset_hour;

			$sunset_minutes = round($tmp_min * 60) ;

			$sunset_minutes_adjusted = $sunset_minutes + $minutes_offset;



			$sunset_time = $sunset_time_str;
			$candle_time_array = explode ( ":" ,$sunset_time ) ;
			$sunset_hour = $candle_time_array[0] ;
			$sunset_min = $candle_time_array[1] ;


			if($sunset_hour > 12){
				$sunset_hour = $sunset_hour - 12;

			}

			$am_pm_symbol = 'pm';   // sunset is only in the evening.
		 $sunset_time_formated = $sunset_hour.':'.$sunset_min.$am_pm_symbol ;
		 $output_time_formated =  $sunset_time_formated;
		 	
		 if($sunset_or_candle == 'candle' ){
	  	$minutes_before_sunset = '18 minutes' ;

	  	$tmp_1 = date_create('2000-10-18'.' '.$sunset_hour.':'.$sunset_min);
	  	date_sub($tmp_1, date_interval_create_from_date_string($minutes_before_sunset));
	  	$tmp_2 = date_format($tmp_1, 'g:i');
	  		
	  	$am_pm_symbol = 'pm';   // candlelighting is only in the evening.
	  	$output_time_formated  = $tmp_2.$am_pm_symbol  ;

		 }
		 	
		}else{
			//$sunset_time_formated;


		}



		return $output_time_formated;
	}
	
	
	function scrubBirthCalculatedFields( $contact_ids  ){
		$rtn_data = array();
		
		if( strlen( $contact_ids) > 0){
			$contactids_sql = " AND bcg.entity_id IN ( $contact_ids )";
		
		}else{
			$contactids_sql = "";
		}
		
		
		$params = array(
				'version' => 3,
				'sequential' => 1,
				'name' => HebrewCalendar::HEB_BIRTH_CUSTOM_FIELD_GROUP_NAME,
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
					'name' => HebrewCalendar::HEB_BIRTH_DATE_NAME,
			);
			$result = civicrm_api('CustomField', 'getsingle', $params);
		
			if( isset ( $result['column_name'])){
				$col_name_heb_birth_date = $result['column_name'];
			}else{
				$col_name_heb_birth_date = "";
		
			}
		
		
			$params = array(
					'version' => 3,
					'sequential' => 1,
					'custom_group_id' =>  $heb_cal_set_id,
					'name' => HebrewCalendar::HEB_EARLIEST_BARBAT_MITZVAH_NAME,
			);
			$result = civicrm_api('CustomField', 'getsingle', $params);
		
			if( isset ( $result['column_name'])){
				$col_name_earliest_barbat = $result['column_name'];
			}else{
				$col_name_earliest_barbat = "";
			}
		
			$params = array(
					'version' => 3,
					'sequential' => 1,
					'custom_group_id' =>  $heb_cal_set_id,
					'name' => HebrewCalendar::HEB_NEXT_BIRTHDAY_NAME,
			);
			$result = civicrm_api('CustomField', 'getsingle', $params);
		
			if( isset ( $result['column_name'])){
				$col_name_hebrew_next_birthday = $result['column_name'];
			}else{
				$col_name_hebrew_next_birthday = "";
			}
				
			$sql = "update  ".$heb_cal_table_name." bcg SET ".
					"bcg.".$col_name_heb_birth_date." = null,  ".
					"bcg.".$col_name_earliest_barbat." = null, ".
					"bcg.".$col_name_hebrew_next_birthday." = null ".
					" WHERE 1=1 ".$contactids_sql;
						
					//$rtn_data['error_message'] = "SQL - ".$sql;
					//return $rtn_data;
					$dao_update =& CRM_Core_DAO::executeQuery( $sql,   CRM_Core_DAO::$_nullArray ) ;
					$dao_update->free();
						
		}else{
			// something is wrong, could not find the table.
				
		}
		
		//$rtn_data['error_message'] = "Debug, birthday scrub is done";
		//return $rtn_data;
	}
	
	// set as null: all CiviCRM custom fields that are read-only fields that get calculated by this extension. 
	function scrubDeceasedCalculatedFields( $contact_ids  ){
		$rtn_data = array();
		
		if( strlen( $contact_ids) > 0){
			$contactids_sql = " AND dcg.entity_id IN ( $contact_ids )";
				
		}else{
			$contactids_sql = "";
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
			
			$sql = "update  ".$heb_cal_table_name." dcg SET ".
					"dcg.".$col_name_hebrew_date_of_death." = null,  ".
					"dcg.".$col_name_next_english_yahrzeit." = null, ".
					"dcg.".$col_name_next_heb_yahrzeit." = null ".
			   " WHERE 1=1 ".$contactids_sql;
			
			//$rtn_data['error_message'] = "SQL - ".$sql;
			//return $rtn_data;
			$dao_update =& CRM_Core_DAO::executeQuery( $sql,   CRM_Core_DAO::$_nullArray ) ;
			$dao_update->free();
			
		}else{
			// something is wrong, could not find the table. 
			
		}
		
		
		
		
	}

	// This is normally called whenever this extension is enabled. 
	function createExtensionConfigs(){
		$this->createRelationshipType();
		$this->createCustomCRMConfigs();
		$this->createYahrzeitTempTable();
		
	}
	
	// This is normally called whenever this extension is disabled.
	function removeExtensionConfigs(){
		$this->removeYahrzeitTempTable();
		
	}
	
	private function removeYahrzeitTempTable(){
		
		$yahrzeit_table_name =  HebrewCalendar::YAHRZEIT_TEMP_TABLE_NAME;
		
		$sql_drop = "DROP TABLE IF EXISTS ".$yahrzeit_table_name;
		
		$dao =& CRM_Core_DAO::executeQuery( $sql_drop,   CRM_Core_DAO::$_nullArray ) ;
		$dao->free();
		
	}
	
	
	private function createYahrzeitTempTable(){
		
		
		$this->removeYahrzeitTempTable();
		
		$yahrzeit_table_name =  HebrewCalendar::YAHRZEIT_TEMP_TABLE_NAME;
		
		$sql_create = "CREATE TABLE $yahrzeit_table_name (
		mourner_contact_id int NOT NULL,
		mourner_name varchar(500) NOT NULL,
		deceased_contact_id int NOT NULL,
		deceased_name varchar(500) NOT NULL,
		deceased_date varchar(500) NOT NULL,
		d_before_sunset varchar(5),
		hebrew_deceased_date varchar(256),
		yahrzeit_date datetime,
		yahrzeit_hebrew_date_format_hebrew varchar(256),
		yahrzeit_hebrew_date_format_english varchar(256),
		yahrzeit_date_display varchar(256),
		relationship_name_formatted varchar(256),
		yahrzeit_type varchar(256),
		mourner_observance_preference varchar(256),
		plaque_location varchar(500),
		yahrzeit_erev_shabbat_before datetime,
		yahrzeit_shabbat_morning_before datetime,
		yahrzeit_erev_shabbat_after datetime,
		yahrzeit_shabbat_morning_after datetime,
		yahrzeit_date_morning datetime,
		yahrzeit_relationship_id varchar(25),
		created_date TIMESTAMP  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ";
		
		$dao =& CRM_Core_DAO::executeQuery( $sql_create,   CRM_Core_DAO::$_nullArray ) ;
		$dao->free();
		
		
		
	}
	
	
	private function createRelationshipType(){
		
		// It does not make sense to have a household observe a yahrzeit, as
		// then its impossible to track how the mourner is related to the dececased person.
		// Such as "Spouse of", "Child of", etc which are commonly used in yahrzeit letters/emails.
		
		$result = civicrm_api3('RelationshipType', 'get', array(
				'sequential' => 1,
				'name_a_b' => HebrewCalendar::YAHRZEIT_RELATIONSHIP_TYPE_A_B_NAME,
		));
		
		
		if($result['is_error'] <> 0 || $result['count'] == 0  ){
			
		
				$result = civicrm_api3('RelationshipType', 'create', array(
						'sequential' => 1,
						'name_a_b' => HebrewCalendar::YAHRZEIT_RELATIONSHIP_TYPE_A_B_NAME,
						'label_a_b' => HebrewCalendar::YAHRZEIT_RELATIONSHIP_TYPE_A_B_TITLE,
						'name_b_a' => HebrewCalendar::YAHRZEIT_RELATIONSHIP_TYPE_B_A_NAME,
						'label_b_a' => HebrewCalendar::YAHRZEIT_RELATIONSHIP_TYPE_B_A_TITLE,
						'contact_type_a' => "Individual",
						'contact_type_b' => "Individual",
				));
		}else{
			// nothing to do, relationship type already exists.
			
		}
		
	}

	private function formatTableNameForCreateAPI( $input_parm ){
		
		$parm_cleaned = strtolower( str_replace( " ", "_", $input_parm) );
		
		$crmstandardprefix = "civicrm_value_"; 
		
		$api_table_name = $crmstandardprefix.$parm_cleaned;
		
		return $api_table_name; 
	}
	
   // If custom data fields, custom contact types do not exist, create them. Otherwise do nothing.
	private function createCustomCRMConfigs(){
		
		
		
		// Make sure custom contact type 'Deceased' exists. 
		$result = civicrm_api3('ContactType', 'get', array(
				'sequential' => 1,
				'name' => "Deceased",
		));
		
		if($result['is_error'] <> 0 || $result['count'] == 0  ){
			$result = civicrm_api3('ContactType', 'create', array(
					'sequential' => 1,
					'name' => HebrewCalendar::YAH_DECEASED_CONTACT_TYPE_NAME,
					'label' => HebrewCalendar::YAH_DECEASED_CONTACT_TYPE_TITLE,
					'parent_id' => "Individual",
			));
				
			
			
		}
		
		
		
		
		$result = civicrm_api3('CustomGroup', 'get', array(
				'sequential' => 1,
				'name' => HebrewCalendar::EXTENDED_DATE_CUSTOM_FIELD_GROUP_NAME,
		));
		
		// TODO: Check that it extends individual. 
		
		$tmp_api_table_name = $this->formatTableNameForCreateAPI( HebrewCalendar::EXTENDED_DATE_CUSTOM_FIELD_GROUP_NAME ); 
		
		
		if($result['is_error'] <> 0 || $result['count'] == 0  ){
			// create it custom data set.
			$create_result = civicrm_api3('CustomGroup', 'create', array(
					'sequential' => 1,
					'title' => HebrewCalendar::EXTENDED_DATE_CUSTOM_FIELD_GROUP_TITLE,
					'extends' => "Individual",
					'name' => HebrewCalendar::EXTENDED_DATE_CUSTOM_FIELD_GROUP_NAME,
					'table_name' => $tmp_api_table_name, 
					'help_pre' => "",
					'weight' => 1, 
			));
				
		}else{
				
			// Nothing to do. 	
				
		}
		
		
		// Check if birth custom field exists, create it if needed.
		$result = civicrm_api3('CustomField', 'get', array(
				'sequential' => 1,
				'custom_group_id' => HebrewCalendar::EXTENDED_DATE_CUSTOM_FIELD_GROUP_NAME,
				'name' => HebrewCalendar::EXTENDED_DATE_CUSTOM_FIELD_BIRTH_NAME,
		));
		
		if($result['is_error'] <> 0 || $result['count'] == 0  ){
			$result = civicrm_api3('CustomField', 'create', array(
					'sequential' => 1,
					'custom_group_id' => HebrewCalendar::EXTENDED_DATE_CUSTOM_FIELD_GROUP_NAME,
					'label' => HebrewCalendar::EXTENDED_DATE_CUSTOM_FIELD_BIRTH_TITLE,
					'name' => HebrewCalendar::EXTENDED_DATE_CUSTOM_FIELD_BIRTH_NAME,
					'data_type' => "Boolean",
					'html_type' => "Radio",
					'is_searchable' => "1",
			));
		
		}
		
		
		// Check if death custom field exists, create it if needed.
		$result = civicrm_api3('CustomField', 'get', array(
				'sequential' => 1,
				'custom_group_id' => HebrewCalendar::EXTENDED_DATE_CUSTOM_FIELD_GROUP_NAME,
				'name' => HebrewCalendar::EXTENDED_DATE_CUSTOM_FIELD_DEATH_NAME,
		));
		
		if($result['is_error'] <> 0 || $result['count'] == 0  ){
			$result = civicrm_api3('CustomField', 'create', array(
					'sequential' => 1,
					'custom_group_id' => HebrewCalendar::EXTENDED_DATE_CUSTOM_FIELD_GROUP_NAME,
					'label' => HebrewCalendar::EXTENDED_DATE_CUSTOM_FIELD_DEATH_TITLE,
					'name' => HebrewCalendar::EXTENDED_DATE_CUSTOM_FIELD_DEATH_NAME,
					'data_type' => "Boolean",
					'html_type' => "Radio",
					'is_searchable' => "1",
			));
		
		}
		
		
		// if needed, Create custom data set used for "relationship type" = "yahrzeit observed in memory of"
		
		$result = civicrm_api3('CustomGroup', 'get', array(
				'sequential' => 1,
				'name' => HebrewCalendar::YAH_RELATIONSHIPTYPE_CUSTOM_FIELD_GROUP_NAME,
		));
		
		// TODO: Check that it extends correct relationship type.		
		
		if($result['is_error'] <> 0 || $result['count'] == 0  ){
			// we need to create it. But first we need to get the relationship type id.
			$reltype_result = civicrm_api3('RelationshipType', 'get', array(
					'sequential' => 1,
					'name_a_b' => HebrewCalendar::YAHRZEIT_RELATIONSHIP_TYPE_A_B_NAME,
			));
			
			$yah_relationtype_id = "";
			if($reltype_result['is_error'] <> 0 || $reltype_result['count'] > 0  ){
				$tmp_reltype = $reltype_result['values'][0];
				$yah_relationtype_id = $tmp_reltype['id'];
			
				$tmp_api_table_name = $this->formatTableNameForCreateAPI( HebrewCalendar::YAH_RELATIONSHIPTYPE_CUSTOM_FIELD_GROUP_NAME);
			
				// finally, we can create the custom data set.
				$create_result = civicrm_api3('CustomGroup', 'create', array(
						'sequential' => 1,
						'title' => HebrewCalendar::YAH_RELATIONSHIPTYPE_CUSTOM_FIELD_GROUP_TITLE,
						'extends' => "Relationship",
						'extends_entity_column_value' => $yah_relationtype_id, 
						'name' => HebrewCalendar::YAH_RELATIONSHIPTYPE_CUSTOM_FIELD_GROUP_NAME,
						'table_name' => $tmp_api_table_name, 
						'help_pre' => "",
						'weight' => 2, 
				));
				
			}else{
				// custom relationship type does not exist. So we cannot create the custom field group.
				
			}
			
		}else{
		
			// Nothing to do.
		
		}
		
		// see if the custom field "mourner observes the English date" exists. If not, create it.
		// Check if death custom field exists, create it if needed.
		$result = civicrm_api3('CustomField', 'get', array(
				'sequential' => 1,
				'custom_group_id' => HebrewCalendar::YAH_RELATIONSHIPTYPE_CUSTOM_FIELD_GROUP_NAME,
				'name' => HebrewCalendar::YAH_RELATIONSHIPTYPE_CUSTOM_FIELD_NAME,
		));
		
		if($result['is_error'] <> 0 || $result['count'] == 0  ){
			$result = civicrm_api3('CustomField', 'create', array(
					'sequential' => 1,
					'custom_group_id' => HebrewCalendar::YAH_RELATIONSHIPTYPE_CUSTOM_FIELD_GROUP_NAME,
					'label' => HebrewCalendar::YAH_RELATIONSHIPTYPE_CUSTOM_FIELD_TITLE,
					'name' => HebrewCalendar::YAH_RELATIONSHIPTYPE_CUSTOM_FIELD_NAME,
					'data_type' => "Boolean",
					'html_type' => "Radio",
					'is_searchable' => "1",
			));
		
		}
		
		
		// if needed, Create custom data set for contacts of type 'Deceased'
		$result = civicrm_api3('CustomGroup', 'get', array(
				'sequential' => 1,
				'name' => HebrewCalendar::YAH_DECEASED_CUSTOM_FIELD_GROUP_NAME,
		));
		
		// TODO: Check that it extends correct contact type.
		
		if($result['is_error'] <> 0 || $result['count'] == 0  ){
			// we need to create it. But first we need to verify the contact type we are using exists.
			$con_type_result = civicrm_api3('ContactType', 'get', array(
					'sequential' => 1,
					'name' => HebrewCalendar::YAH_DECEASED_CONTACT_TYPE_NAME,
			));
				
			//$yah_contact_type_id = "";
			if($con_type_result['is_error'] <> 0 || $con_type_result['count'] > 0  ){
					
				$tmp_api_table_name = $this->formatTableNameForCreateAPI( HebrewCalendar::YAH_DECEASED_CUSTOM_FIELD_GROUP_NAME);
				
				// finally, we can create the custom data set.
				$create_result = civicrm_api3('CustomGroup', 'create', array(
						'sequential' => 1,
						'title' => HebrewCalendar::YAH_DECEASED_CUSTOM_FIELD_GROUP_TITLE,
						'extends' => "Individual",  
						'extends_entity_column_value' => HebrewCalendar::YAH_DECEASED_CONTACT_TYPE_NAME,
						'name' => HebrewCalendar::YAH_DECEASED_CUSTOM_FIELD_GROUP_NAME,
						'help_pre' => "All fields in this area are calculated automatically.",
						'table_name' => $tmp_api_table_name, 
						'weight' => 2,
				));
				
		
			}else{
				// custom contact type for 'Deceased' does not exist,
				// So we cannot create the custom field group.
		
			}
			
				
		}else{
		
			// Nothing to do.
		
		}
		
		// if needed,  create custom fields for above group
		$result = civicrm_api3('CustomField', 'get', array(
				'sequential' => 1,
				'custom_group_id' => HebrewCalendar::YAH_DECEASED_CUSTOM_FIELD_GROUP_NAME,
				'name' => HebrewCalendar::YAH_NEXT_HEB_YAHRZEIT_NAME,
		));
		
		if($result['is_error'] <> 0 || $result['count'] == 0  ){
			$result = civicrm_api3('CustomField', 'create', array(
					'sequential' => 1,
					'custom_group_id' => HebrewCalendar::YAH_DECEASED_CUSTOM_FIELD_GROUP_NAME,
					'label' => HebrewCalendar::YAH_NEXT_HEB_YAHRZEIT_TITLE,
					'name' => HebrewCalendar::YAH_NEXT_HEB_YAHRZEIT_NAME,
					'data_type' => "Date",
					'html_type' => "Select Date",
					'is_required' => "0",
					'is_searchable' => "1",
					'is_search_range' =>  "1",
					'is_view' => 1,
					'mask' => "MM dd, yyyy",
					'date_format' => "MM d, yy",
					'help_pre' => "",
					'help_post' => "",
					
			));
		
		}
		
		//
		$result = civicrm_api3('CustomField', 'get', array(
				'sequential' => 1,
				'custom_group_id' => HebrewCalendar::YAH_DECEASED_CUSTOM_FIELD_GROUP_NAME,
				'name' => HebrewCalendar::YAH_NEXT_ENGLISH_YAHRZEIT_NAME,
		));
		
		if($result['is_error'] <> 0 || $result['count'] == 0  ){
			$result = civicrm_api3('CustomField', 'create', array(
					'sequential' => 1,
					'custom_group_id' => HebrewCalendar::YAH_DECEASED_CUSTOM_FIELD_GROUP_NAME,
					'label' => HebrewCalendar::YAH_NEXT_ENGLISH_YAHRZEIT_TITLE,
					'name' => HebrewCalendar::YAH_NEXT_ENGLISH_YAHRZEIT_NAME,
					'data_type' => "Date",
					'html_type' => "Select Date",
					'is_required' => "0",
					'is_searchable' => "1",
					'is_search_range' =>  "1",
					'is_view' => 1,
					'mask' => "MM dd, yyyy",
					'date_format' => "MM d, yy",
					'help_pre' => "",
					'help_post' => "",
						
			));
		
		}
		
		//
		$result = civicrm_api3('CustomField', 'get', array(
				'sequential' => 1,
				'custom_group_id' => HebrewCalendar::YAH_DECEASED_CUSTOM_FIELD_GROUP_NAME,
				'name' => HebrewCalendar::YAH_HEB_DEATH_DATE_NAME,
		));
		
		if($result['is_error'] <> 0 || $result['count'] == 0  ){
			$result = civicrm_api3('CustomField', 'create', array(
					'sequential' => 1,
					'custom_group_id' => HebrewCalendar::YAH_DECEASED_CUSTOM_FIELD_GROUP_NAME,
					'label' => HebrewCalendar::YAH_HEB_DEATH_DATE_TITLE,
					'name' => HebrewCalendar::YAH_HEB_DEATH_DATE_NAME,
					'data_type' => "String",
					'html_type' => "Text",
					'is_required' => "0",
					'is_searchable' => "1",
					'is_view' => 1,
					'help_pre' => "",
					'help_post' => "",
		
			));
		
		}
		
		
		// if needed, Create custom data set for tracking Hebrew birthdays
		$result = civicrm_api3('CustomGroup', 'get', array(
				'sequential' => 1,
				'name' => HebrewCalendar::HEB_BIRTH_CUSTOM_FIELD_GROUP_NAME,
		));
		
		// TODO: Check that it extends correct contact type.
		
		if($result['is_error'] <> 0 || $result['count'] == 0  ){
			
			$tmp_api_table_name = $this->formatTableNameForCreateAPI( HebrewCalendar::HEB_BIRTH_CUSTOM_FIELD_GROUP_NAME);
			
			
			$create_result = civicrm_api3('CustomGroup', 'create', array(
					'sequential' => 1,
					'title' => HebrewCalendar::HEB_BIRTH_CUSTOM_FIELD_GROUP_TITLE,
					'extends' => "Individual",
					'name' => HebrewCalendar::HEB_BIRTH_CUSTOM_FIELD_GROUP_NAME,
					'help_pre' => "All fields in this area are calculated automatically.",
					'table_name' => $tmp_api_table_name, 
					'weight' => 3,
			));
			
			
			
		}else{
			// nothing to do
		}
		
		
		//
		$result = civicrm_api3('CustomField', 'get', array(
				'sequential' => 1,
				'custom_group_id' => HebrewCalendar::HEB_BIRTH_CUSTOM_FIELD_GROUP_NAME,
				'name' => HebrewCalendar::HEB_BIRTH_DATE_NAME,
		));
		
		if($result['is_error'] <> 0 || $result['count'] == 0  ){
			$result = civicrm_api3('CustomField', 'create', array(
					'sequential' => 1,
					'custom_group_id' => HebrewCalendar::HEB_BIRTH_CUSTOM_FIELD_GROUP_NAME,
					'label' =>  HebrewCalendar::HEB_BIRTH_DATE_TITLE,
					'name' => HebrewCalendar::HEB_BIRTH_DATE_NAME,
					'data_type' => "String",
					'html_type' => "Text",
					'is_required' => "0",
					'is_searchable' => "1",
					'is_view' => 1,
					'help_pre' => "",
					'help_post' => "",
		
			));
		
		}
		
		
		$result = civicrm_api3('CustomField', 'get', array(
				'sequential' => 1,
				'custom_group_id' => HebrewCalendar::HEB_BIRTH_CUSTOM_FIELD_GROUP_NAME,
				'name' => HebrewCalendar::HEB_NEXT_BIRTHDAY_NAME,
		));
		
		if($result['is_error'] <> 0 || $result['count'] == 0  ){
			$result = civicrm_api3('CustomField', 'create', array(
					'sequential' => 1,
					'custom_group_id' => HebrewCalendar::HEB_BIRTH_CUSTOM_FIELD_GROUP_NAME,
					'label' =>  HebrewCalendar::HEB_NEXT_BIRTHDAY_TITLE,
					'name' => HebrewCalendar::HEB_NEXT_BIRTHDAY_NAME,
					'data_type' => "Date",
					'html_type' => "Select Date",
					'is_required' => "0",
					'is_searchable' => "1",
					'is_search_range' =>  "1",
					'is_view' => 1,
					'mask' => "MM dd, yyyy",
					'date_format' => "MM d, yy",
					'help_pre' => "",
					'help_post' => "",
		
			));
		
		}
		
		$result = civicrm_api3('CustomField', 'get', array(
				'sequential' => 1,
				'custom_group_id' => HebrewCalendar::HEB_BIRTH_CUSTOM_FIELD_GROUP_NAME,
				'name' => HebrewCalendar::HEB_EARLIEST_BARBAT_MITZVAH_NAME,
		));
		
		if($result['is_error'] <> 0 || $result['count'] == 0  ){
			$result = civicrm_api3('CustomField', 'create', array(
					'sequential' => 1,
					'custom_group_id' => HebrewCalendar::HEB_BIRTH_CUSTOM_FIELD_GROUP_NAME,
					'label' =>  HebrewCalendar::HEB_EARLIEST_BARBAT_MITZVAH_TITLE,
					'name' => HebrewCalendar::HEB_EARLIEST_BARBAT_MITZVAH_NAME,
					'data_type' => "Date",
					'html_type' => "Select Date",
					'is_required' => "0",
					'is_searchable' => "1",
					'is_search_range' =>  "1",
					'is_view' => 1,
					'mask' => "MM dd, yyyy",
					'date_format' => "MM d, yy",
					'help_pre' => "",
					'help_post' => "",
		
			));
		
		}
		
		// if needed, create custom field group for tracking Religious information. 
		$result = civicrm_api3('CustomGroup', 'get', array(
				'sequential' => 1,
				'name' => HebrewCalendar::RELIGIOUS_CUSTOM_FIELD_GROUP_NAME,
		));
		
		// TODO: Check that it extends correct contact type.
		
		if($result['is_error'] <> 0 || $result['count'] == 0  ){
				
			$tmp_api_table_name = $this->formatTableNameForCreateAPI( HebrewCalendar::RELIGIOUS_CUSTOM_FIELD_GROUP_NAME);
				
				
			$create_result = civicrm_api3('CustomGroup', 'create', array(
					'sequential' => 1,
					'title' => HebrewCalendar::RELIGIOUS_CUSTOM_FIELD_GROUP_TITLE,
					'extends' => "Individual",
					'name' => HebrewCalendar::RELIGIOUS_CUSTOM_FIELD_GROUP_NAME,
					'help_pre' => "",
					'table_name' => $tmp_api_table_name,
					'weight' => 3,
			));
				
				
				
		}else{
			// nothing to do
		}
		
		// if needed, create plaque custom field set
		//PLAQUE_CUSTOM_FIELD_GROUP_NAME
		$result = civicrm_api3('CustomGroup', 'get', array(
				'sequential' => 1,
				'name' => HebrewCalendar::PLAQUE_CUSTOM_FIELD_GROUP_NAME,
		));
		
		// TODO: Check that it extends correct contact type.
		
		if($result['is_error'] <> 0 || $result['count'] == 0  ){
		
			$tmp_api_table_name = $this->formatTableNameForCreateAPI( HebrewCalendar::PLAQUE_CUSTOM_FIELD_GROUP_NAME);
		
		
			$create_result = civicrm_api3('CustomGroup', 'create', array(
					'sequential' => 1,
					'title' => HebrewCalendar::PLAQUE_CUSTOM_FIELD_GROUP_TITLE,
					'extends' => "Individual",
					'extends_entity_column_value' => HebrewCalendar::YAH_DECEASED_CONTACT_TYPE_NAME,
					'name' => HebrewCalendar::PLAQUE_CUSTOM_FIELD_GROUP_NAME,
					'help_pre' => "",
					'table_name' => $tmp_api_table_name,
					'weight' => 3,
			));
		
		
		
		}else{
			// nothing to do
		}
		
		
	}
	
	
	
	
	function calculateBirthDates( $extended_dates_table_name,    $contact_ids){
	
		$eb_result = civicrm_api3('CustomField', 'get', array(
				'sequential' => 1,
				'custom_group_id' => HebrewCalendar::EXTENDED_DATE_CUSTOM_FIELD_GROUP_NAME,
				'name' => HebrewCalendar::EXTENDED_DATE_CUSTOM_FIELD_BIRTH_NAME ,
		));
		
		if($eb_result['is_error'] <> 0 || $eb_result['count'] == 0  ){
			$rtn_data['error_message'] = "Could not find custom field: '".HebrewCalendar::EXTENDED_DATE_CUSTOM_FIELD_BIRTH_NAME."' ";
			return $rtn_data;
		
		}else{
				
			$tmp_values = $eb_result['values'][0];
			 $extended_birth_date_col_name = $tmp_values['column_name'];
			$extended_birth_custom_field_id = $tmp_values['id'];
		
		
		}
		
		if(  strlen( $extended_birth_date_col_name) == 0 ){
			$rtn_data['error_message'] = "Could not get SQL column name for '".HebrewCalendar::EXTENDED_DATE_CUSTOM_FIELD_BIRTH_NAME."'";
			return $rtn_data;
		}
		
		// done with validation steps
		
		
		
		if( strlen( $contact_ids) > 0){
			$contactids_sql = " AND c.id IN ( $contact_ids )";
	
		}else{
			$contactids_sql = "";
		}
	
		$result = civicrm_api3('CustomField', 'get', array(
				'sequential' => 1,
				'custom_group_id' => HebrewCalendar::HEB_BIRTH_CUSTOM_FIELD_GROUP_NAME,
				'name' => HebrewCalendar::HEB_BIRTH_DATE_NAME,
		));
	
		if($result['is_error'] == 0 && $result['count'] == 1  ){
				
				
			$tmp_id = $result['values'][0]['id'];    //$result['id'];
			$api_name_heb_birth_date =   'custom_'.$tmp_id;
	
		}else{
			$rtn_data['error_message'] = "Error: Missing custom field: '".HebrewCalendar::HEB_BIRTH_DATE_NAME."'";
			return $rtn_data;
		}
	
	// $api_name_earliest_barbat
		$result = civicrm_api3('CustomField', 'get', array(
				'sequential' => 1,
				'custom_group_id' => HebrewCalendar::HEB_BIRTH_CUSTOM_FIELD_GROUP_NAME,
				'name' => HebrewCalendar::HEB_EARLIEST_BARBAT_MITZVAH_NAME,
		));
		
		if($result['is_error'] == 0 && $result['count'] == 1  ){
		
		
			$tmp_id = $result['values'][0]['id'];    //$result['id'];
			$api_name_earliest_barbat =   'custom_'.$tmp_id;
		
		}else{
			$rtn_data['error_message'] = "Error: Missing custom field: '".HebrewCalendar::HEB_EARLIEST_BARBAT_MITZVAH_NAME."'";
			return $rtn_data;
		}
		
		// $api_name_next_hebrew_birthday
		$result = civicrm_api3('CustomField', 'get', array(
				'sequential' => 1,
				'custom_group_id' => HebrewCalendar::HEB_BIRTH_CUSTOM_FIELD_GROUP_NAME,
				'name' => HebrewCalendar::HEB_NEXT_BIRTHDAY_NAME,
		));
		
		if($result['is_error'] == 0 && $result['count'] == 1  ){
		
		
			$tmp_id = $result['values'][0]['id'];    //$result['id'];
			$api_name_next_hebrew_birthday =   'custom_'.$tmp_id;
		
		}else{
			$rtn_data['error_message'] = "Error: Missing custom field: '".HebrewCalendar::HEB_NEXT_BIRTHDAY_NAME."'";
			return $rtn_data;
		}
		
		
		
		
		
		$birth_year = null;
		$birth_month = null;
		$birth_day = null;
		$birth_date_before_sunset = null;
	
		$sql = "select c.id as contact_id ,
	  		year(c.birth_date) as birth_year,
		month(c.birth_date) as birth_month,
		day(c.birth_date) as birth_day,
	  		civicrm_option_value.label as gender_label,
	  		 edt.".$extended_birth_date_col_name." as birth_date_before_sunset
	  		  FROM civicrm_contact c
	  		  JOIN ".$extended_dates_table_name." edt ON c.id = edt.entity_id
	  		  LEFT JOIN civicrm_option_value ON civicrm_option_value.option_group_id = 3 AND c.gender_id = civicrm_option_value.value
	          where c.is_deleted <> 1 AND
	  		  		c.contact_type = 'Individual' AND
	  		  		c.birth_date is not null ".$contactids_sql;
		 
		 
		 
		$contacts_updated = 0;
		$dao =& CRM_Core_DAO::executeQuery( $sql,   CRM_Core_DAO::$_nullArray ) ;
		while ( $dao->fetch( ) ) {
			$contact_id = $dao->contact_id ;
			$birth_year = $dao->birth_year;
			$birth_month = $dao->birth_month;
			$birth_day = $dao->birth_day;
			$birth_date_before_sunset = $dao->birth_date_before_sunset;
			$gender = $dao->gender_label;
	
				
				
			$hebrew_date_format =  'dd MM yy';
			$hebrew_birth_date_formated = self::util_convert2hebrew_date($birth_year, $birth_month, $birth_day, $birth_date_before_sunset, $hebrew_date_format);
			
			// $rtn_data["hebrew_date_of_birth"] = $hebrew_birth_date_formated;
				
			$heb_date_format = 'hebrew';
			$hebrew_birth_date_formated_as_hebrew = self::util_convert2hebrew_date($birth_year, $birth_month, $birth_day, $birth_date_before_sunset, $heb_date_format);
			
			// $rtn_data["hebrew_date_of_birth_hebrew"] = $hebrew_birth_date_formated_as_hebrew;
				
				
			$config = CRM_Core_Config::singleton( );
				
			$tmp_system_date_format = 	$config->dateInputFormat;
			if($tmp_system_date_format == 'dd/mm/yy'){
				$gregorian_date_format = "dd MM yyyy" ;
				 
			}else if($tmp_system_date_format == 'mm/dd/yy' ){
				$gregorian_date_format = "MM dd, yyyy";
				 
			}else{
	
				$rtn_data['error_message'] = "Configuration Issue: Unrecognized System date format: ".$tmp_system_date_format;
				return $rtn_data ;
	
			}
	
			$erev_start_flag = '1';
			$gregorian_date_format = "MM dd, yyyy";
			if($gender == 'Male'){
				$bar_bat_mitzvah_flag = "bar";
				// $bar_bat_label = "Bar Mitzvah";  // Son of the commandments.
			}else if( $gender == 'Female' ){
				$bar_bat_mitzvah_flag = "bat";
				// $bar_bat_label = "Bat Mitzvah";  // Daughter of the commandments.
			}else{
				// unrecognized gender option, treat as 'bar' which means person must be 13 or older.
				$bar_bat_mitzvah_flag = "bar";
				// $bar_bat_label = "B'nai Mitzvah";  // Technically this is the plural, ie "Sons and daughters of the commandments" . Need input from community on this.
			}
			
			
			$tmp_date_gregorian_format = 'crm_api';
			
			$bat_mitzvah_date_formated = self::util_get_bar_bat_mizvah_date($birth_year, $birth_month, $birth_day, $birth_date_before_sunset, $erev_start_flag, $bar_bat_mitzvah_flag, $tmp_date_gregorian_format);
			
			$next_hebrew_birthday_formated = self::util_get_next_hebrew_birthday_date($birth_year, $birth_month, $birth_day, $birth_date_before_sunset, $erev_start_flag,  $tmp_date_gregorian_format);
			
			/*
			if( $contact_id == 4){
				$rtn_data['error_message'] = "Debug:  $contact_id next heb birthday: ".$next_hebrew_birthday_formated;
				return $rtn_data;
			}
			*/
			//$rtn_data["bar_bat_mitzvah_label"] =  $bar_bat_label ;
			//$rtn_data["earliest_bar_bat_mitzvah_date"]  = $bat_mitzvah_date_formated ;
			
			
			// Now update the contact with the newly calculated values.
			$api_parms = array(
					'sequential' => 1,
					'id' =>  $contact_id,
			);
			
			
			$api_parms[$api_name_heb_birth_date] = $hebrew_birth_date_formated;		
		
			
			if( is_numeric(substr( $bat_mitzvah_date_formated, 0 , 4)) ){
				
				$api_parms[$api_name_earliest_barbat] = $bat_mitzvah_date_formated;
			}
			
			if( is_numeric(substr( $next_hebrew_birthday_formated, 0 , 4)) ){
			
				$api_parms[$api_name_next_hebrew_birthday] = $next_hebrew_birthday_formated;
			}
			/*
			 * $api_name_heb_birth_date => $hebrew_birth_date_formated,
					$api_name_earliest_barbat => $bat_mitzvah_date_formated,
			 */
			
			$result = civicrm_api3('Contact', 'create', $api_parms);
			
			if( $result['is_error'] == 0 && $result['count'] == 1){
				$contacts_updated = $contacts_updated + 1;
	
			}
				
		}
		$dao->free();
		 
		$rtn_data['contacts_updated_birthdays'] = $contacts_updated;
		return $rtn_data;
		 
	}
	
	/*****************************************************************************
	 *
	 * This function queries info from the CiviCRM database and returns
	 * the calculated Hebrew dates as an array.
	 ******************************************************************************/
	function retrieve_hebrew_demographic_dates(&$cur_id_parm ){

		$record_found = false;

		//require_once('utils/util_custom_fields.php');

		//$custom_field_group_label = "Extended Date Information";
		//$custom_field_birthdate_sunset_label = "Birth Date Before Sunset";
		//$custom_field_deathdate_sunset_label = "Death Date Before Sunset" ;


		//$customFieldLabels = array($custom_field_birthdate_sunset_label   , $custom_field_deathdate_sunset_label );
		$extended_date_table = "";
		$extended_birth_date  = "";
		$extended_death_date = "";
		//$outCustomColumnNames = array();

		// Call CiviCRM API to get db field names for custom fields. 
		// The following variables are used in an SQL statement:  $extended_date_table $extended_birth_date $extended_death_date
		$result = civicrm_api3('CustomGroup', 'get', array(
				'sequential' => 1,
				'name' =>  HebrewCalendar::EXTENDED_DATE_CUSTOM_FIELD_GROUP_NAME,
				'extends' => "Individual",
		));
		
		
		if($result['is_error'] <> 0 || $result['count'] == 0  ){
			$rtn_data['error_message'] = "Could not find custom field set '". HebrewCalendar::EXTENDED_DATE_CUSTOM_FIELD_GROUP_TITLE."' ";
			return $rtn_data;
			
		}else{
			$tmp_values = $result['values'][0];
			$extended_date_table = $tmp_values['table_name'];
			$set_id = $tmp_values['id'];
			
			
		}
		
		if(strlen( $extended_date_table) == 0){
			$rtn_data['error_message'] = "Could not get SQL table name for set '".HebrewCalendar::EXTENDED_DATE_CUSTOM_FIELD_GROUP_TITLE."'";
			return $rtn_data;
			
		}
		//$error_msg = getCustomTableFieldNames($custom_field_group_label, $customFieldLabels, $extended_date_table, $outCustomColumnNames ) ;

		//$extended_birth_date  =  $outCustomColumnNames[$custom_field_birthdate_sunset_label];
		$eb_result = civicrm_api3('CustomField', 'get', array(
				'sequential' => 1,
				'custom_group_id' => HebrewCalendar::EXTENDED_DATE_CUSTOM_FIELD_GROUP_NAME,
				'name' => HebrewCalendar::EXTENDED_DATE_CUSTOM_FIELD_BIRTH_NAME , 
		));
		
		if($eb_result['is_error'] <> 0 || $eb_result['count'] == 0  ){
			$rtn_data['error_message'] = "Could not find custom field: '".HebrewCalendar::EXTENDED_DATE_CUSTOM_FIELD_BIRTH_NAME."' ";
			return $rtn_data;
				
		}else{
			
			$tmp_values = $eb_result['values'][0];
			$extended_birth_date = $tmp_values['column_name'];
				
				
		}
		
		if(  strlen($extended_birth_date) == 0 ){
			$rtn_data['error_message'] = "Could not get SQL column name for '".HebrewCalendar::EXTENDED_DATE_CUSTOM_FIELD_BIRTH_NAME."'";
			return $rtn_data;
		}
		//$extended_death_date  =  $outCustomColumnNames[$custom_field_deathdate_sunset_label];
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

		

		
		
		if( isset($error_msg) && $error_msg <> ''){
			//$screen->assign("hebrew_date_of_birth", $error_msg );
			$rtn_data['error_message'] = $error_msg;
			return $rtn_data;
		}

		
		// fetch the details about the individual.
		$query = "
		SELECT civicrm_contact.first_name as first_name,
		year(civicrm_contact.birth_date) as birth_year,
		month(civicrm_contact.birth_date) as birth_month,
		day(civicrm_contact.birth_date) as birth_day,
		civicrm_contact.is_deceased as is_deceased,
		civicrm_contact.contact_type as contact_type,
		civicrm_option_value.label as gender_label,
		$extended_date_table.$extended_birth_date as birth_date_before_sunset,
		year(civicrm_contact.deceased_date) as deceased_year,
		month(civicrm_contact.deceased_date) as deceased_month,
		day(civicrm_contact.deceased_date) as deceased_day,
		$extended_date_table.$extended_death_date as deceased_date_before_sunset,
		civicrm_option_value.label as gender_label
		FROM civicrm_contact
		LEFT JOIN $extended_date_table ON civicrm_contact.id = $extended_date_table.entity_id
		LEFT JOIN civicrm_option_value ON civicrm_option_value.option_group_id = 3 AND civicrm_contact.gender_id = civicrm_option_value.value
		WHERE civicrm_contact.id = %1 AND civicrm_contact.contact_type = 'Individual'  " ;




		$p = array( 1 => array( $cur_id_parm, 'Integer' ) );
		$dao =& CRM_Core_DAO::executeQuery( $query, $p );
		 
		$first_name = null;
		$birth_year = null;
		$birth_month = null;
		$birth_day = null;
		$birth_date_before_sunset = null;
		$is_deceased = null;
		$deceased_year = null;
		$deceased_month = null;
		$deceased_day = null;
		$deceased_date_before_sunset =null;
		$gender = null;


		if ( $dao->fetch( ) ) {
			$record_found = true;

			$first_name = $dao->first_name;
			$birth_year = $dao->birth_year;
			$birth_month = $dao->birth_month;
			$birth_day = $dao->birth_day;
			$birth_date_before_sunset = $dao->birth_date_before_sunset;
			$is_deceased = $dao->is_deceased;
			$gender = $dao->gender_label;
			$rtn_data["contact_type"] = $dao->contact_type;
			 
			

			if(  $is_deceased  ){
				$rtn_data["is_deceased"] = true;
				$deceased_year = $dao->deceased_year;
				$deceased_month = $dao->deceased_month;
				$deceased_day = $dao->deceased_day;
				$deceased_date_before_sunset = $dao->deceased_date_before_sunset;
			}else{
				$rtn_data["is_deceased"] = false;
			}
		}
		$dao->free( );
		 
		if(  $record_found ){
			$hebrew_date_format =  'dd MM yy';
			$hebrew_birth_date_formated = self::util_convert2hebrew_date($birth_year, $birth_month, $birth_day, $birth_date_before_sunset, $hebrew_date_format);
			//$screen->assign("hebrew_date_of_birth", $hebrew_birth_date_formated);
			$rtn_data["hebrew_date_of_birth"] = $hebrew_birth_date_formated;

			$heb_date_format = 'hebrew';
			$hebrew_birth_date_formated_as_hebrew = self::util_convert2hebrew_date($birth_year, $birth_month, $birth_day, $birth_date_before_sunset, $heb_date_format);
			//$screen->assign("hebrew_date_of_birth", $hebrew_birth_date_formated);
			$rtn_data["hebrew_date_of_birth_hebrew"] = $hebrew_birth_date_formated_as_hebrew;

			 
			$config = CRM_Core_Config::singleton( );

			$tmp_system_date_format = 	$config->dateInputFormat;
			if($tmp_system_date_format == 'dd/mm/yy'){
				$gregorian_date_format = "dd MM yyyy" ;
				 
			}else if($tmp_system_date_format == 'mm/dd/yy' ){
				$gregorian_date_format = "MM dd, yyyy";
				 
			}else{
				print "<br>Configuration Issue: Unrecognized System date format: ".$tmp_system_date_format;
			}




			if( $is_deceased ){
				$hebrew_death_date_formated = self::util_convert2hebrew_date($deceased_year, $deceased_month, $deceased_day, $deceased_date_before_sunset, $hebrew_date_format);
				//$screen->assign("hebrew_date_of_death" , $hebrew_death_date_formated );
				$rtn_data["hebrew_date_of_death"] = $hebrew_death_date_formated ;
				$erev_start_flag = '1';
				// $gregorian_date_format = "MM dd, yyyy";
				$yahrzeit_date_formated = self::util_get_next_yahrzeit_date( $deceased_year, $deceased_month, $deceased_day, $deceased_date_before_sunset, $erev_start_flag, $gregorian_date_format );
				//$screen->assign("yahrzeit_date", $yahrzeit_date_formated);
				$rtn_data["yahrzeit_date_observe_hebrew"] = $yahrzeit_date_formated;
				// Make sure we get the next English yahrzeit date.
				$next_flag = 'next';
				$rtn_data["yahrzeit_date_observe_english"] = self::getYahrzeitDateEnglishObservanceFormated( $deceased_year, $deceased_month, $deceased_day, $next_flag );

			}else{

				if ( $hebrew_birth_date_formated =="Cannot determine Hebrew date" ){
					// TODO: Need better error handling.
					return $rtn_data;
				}

				$erev_start_flag = '1';
				$gregorian_date_format = "MM dd, yyyy";
				if($gender == 'Male'){
					$bar_bat_mitzvah_flag = "bar";
					$bar_bat_label = "Bar Mitzvah";
				}else if( $gender == 'Female' ){
					$bar_bat_mitzvah_flag = "bat";
					$bar_bat_label = "Bat Mitzvah";
				}else{
					//$screen->assign("bar_bat_mitzvah_label" , "bar/bat mitzvah");
					//$screen->assign("earliest_bar_bat_mitzvah_date",  " Cannot determine Bar/Bat Mitzvah date due to unrecognized gender." );
					$rtn_data["bar_bat_mitzvah_label"] = "bar/bat mitzvah" ;
					$rtn_data["earliest_bar_bat_mitzvah_date"] =  " Cannot determine Bar/Bat Mitzvah date due to unrecognized gender." ;
					return $rtn_data;
				}

				$bat_mitzvah_date_formated = self::util_get_bar_bat_mizvah_date($birth_year, $birth_month, $birth_day, $birth_date_before_sunset, $erev_start_flag, $bar_bat_mitzvah_flag, $gregorian_date_format);
				//$screen->assign("bar_bat_mitzvah_label" , $bar_bat_label);
				//$screen->assign("earliest_bar_bat_mitzvah_date", $bat_mitzvah_date_formated);
				$rtn_data["bar_bat_mitzvah_label"] =  $bar_bat_label ;
				$rtn_data["earliest_bar_bat_mitzvah_date"]  = $bat_mitzvah_date_formated ;


			}
		} // end if record found.
		return $rtn_data;

	}  // end of function

	/*****************************************************************
	 **  Prepare mail-merge tokens related to yahrzeits.
	 **
	 **
	 *****************************************************************/
	function process_yahrzeit_tokens( &$values, &$contactIDs ,  &$token_yahrzeits_long, &$token_yahrzeits_short, 
			&$token_yah_dec_name, &$token_yah_english_date,  &$token_yah_hebrew_date, &$token_yah_dec_death_english_date,  &$token_yah_dec_death_hebrew_date ,  
			&$token_yah_relationship_name,
			&$token_yah_erev_shabbat_before, &$token_yah_shabbat_morning_before, &$token_yah_erev_shabbat_after, &$token_yah_shabbat_morning_after,
			&$token_yah_english_date_morning ,  &$token_date_portion ){

				//  yahrzeit_morning_format_english
				// print "<br><br>Inside process yahrzeit tokens: ".$token_yah_english_date_morning ;

				$default_parm = "";
				 
				$date_token_as_array = explode("_",  $token_date_portion  );
				
				$date_interval_from_token = $date_token_as_array[0];
				$date_number_from_token = $date_token_as_array[1];
				
				if( $date_interval_from_token == "day" ){
					$interval_type_sql_str = "DAY"; 			
				}else{
					CRM_Core_Error::debug("Error in Yahrzeit token: interval type is not valid: ",  $date_interval_from_token );
					return;
				}
				
				if( is_numeric($date_number_from_token )){
					// nothing to do
				}else{
					CRM_Core_Error::debug("Error in Yahrzeit token: number of days is not a number: ",  $date_number_from_token );
					return;
				}
				
				/*
				 * 
				$english_start_date = '2011-10-01';
				$english_end_date = '2011-10-15';
				
				session_start();

				if(isset($_SESSION['yahrzeit_sql']))
				{
					// This is being called just after running a yahrzeit custom search.

					// Identifying the user
					$yahrzeit_data= $_SESSION['yahrzeit_sql'];
					//  $tmp_yah_tablename = $_SESSION['yahrzeit_temp_tablename'];
					 
					// print "<br><br>Found Yahrzeit temp table in session: ".$tmp_yah_tablename;
					// Information for the user.
				}
				else
				{
					// print "<br><br>Could not find yahrzeit data in the session. Please run the yahrzeit custom search first. ";
					 
				}
				*/


				// TODO: Get table name and column names from API.
				//require_once 'CRM/Hebrew/HebrewDates.php';
				//require_once('utils/util_custom_fields.php');
				/*
				$custom_field_group_label = "Extended Date Information";
				$custom_field_birthdate_sunset_label = "Birth Date Before Sunset";
				$custom_field_deathdate_sunset_label = "Death Date Before Sunset" ;

           
				$customFieldLabels = array($custom_field_birthdate_sunset_label   , $custom_field_deathdate_sunset_label );
				$extended_date_table = "";
				$outCustomColumnNames = array();


				$error_msg = getCustomTableFieldNames($custom_field_group_label, $customFieldLabels, $extended_date_table, $outCustomColumnNames ) ;

				$extended_birth_date  =  $outCustomColumnNames[$custom_field_birthdate_sunset_label];
				$extended_death_date  =  $outCustomColumnNames[$custom_field_deathdate_sunset_label];

				// list($error_msg, $extended_date_table,  $extended_birth_date , $extended_death_date) = getCustomTableFieldNames();

				if($error_msg <> ''){

					return ;
				}
				*/

				$i = 1;
				$cid_list = "";
				if( isset(  $contactIDs) ){
					foreach ( $contactIDs as $cid ) {
						$cid_list = $cid_list.$cid;
						if( $i < count($contactIDs) ){
							$cid_list = $cid_list.' ,';
						}
						$i = $i +1;
					}
				}


				if( $i == 1){

					return;
				}


				$yahrzeit_temp_table_name =  HebrewCalendar::YAHRZEIT_TEMP_TABLE_NAME;

				$yizkor_sql_str = "SELECT DISTINCT mourner_contact_id as contact_id, mourner_contact_id as id, mourner_name as sort_name, 
						deceased_name as deceased_name,
    deceased_contact_table.display_name as deceased_display_name, deceased_contact_id,  contact_b.deceased_date,
    contact_b.deceased_date as ddate,
    d_before_sunset, hebrew_deceased_date,
     concat( year(yahrzeit_date), '-', month(yahrzeit_date), '-', day(yahrzeit_date)) as yahrzeit_date_sort , yahrzeit_date_display, relationship_name_formatted,
      yahrzeit_type, mourner_observance_preference
       FROM ".$yahrzeit_temp_table_name." contact_b INNER JOIN civicrm_contact contact_a ON contact_a.id = contact_b.mourner_contact_id
       JOIN civicrm_contact deceased_contact_table ON deceased_contact_table.id = contact_b.deceased_contact_id
       WHERE contact_b.created_date >= DATE_SUB(CURDATE(), INTERVAL 10 MINUTE) AND (yahrzeit_type = mourner_observance_preference)
       AND yahrzeit_date >= CURDATE()
       AND contact_b.mourner_contact_id in (   $cid_list )
       ORDER BY sort_name asc";
				 
				//   print "<br>Yizkor sql: ".$yizkor_sql_str;
				 // INTERVAL 30 DAY
				 

				$html_table_begin =  '<table border=0 style="border-spacing: 0; border-collapse: collapse; width: 100%">
 			<tr><th style="text-align: left;">In memory of</th>
 			<th style="text-align: left;">Hebrew Date of Death</th>
 			<th style="text-align: left;">English Date of death</th>
 			<th style="text-align: left;">Next Yahrzeit</th>
 			<th style="text-align: left;">Relationship</th>
 			<th style="text-align: left;">Observance Type</th>
 			</tr>';
				$html_table_end = ' </table>  	 ';
				$no_rec = true;
				$prev_cid = "";
				$cur_cid_html = "";
				$dao =& CRM_Core_DAO::executeQuery( $yizkor_sql_str,   CRM_Core_DAO::$_nullArray ) ;

				while ( $dao->fetch( ) ) {
					//  print "<br>Have yizkor record!";
					$cur_cid = $dao->contact_id;
					 
					if ( $cur_cid != $prev_cid ){
						if ( $prev_cid != ""){
							// Wrap up table for previous contact.
							$cur_cid_html = $cur_cid_html.$html_table_end;
							$values[$prev_cid][$token_yahrzeits_long] = $values[$prev_cid][$token_yahrzeits_short] = $cur_cid_html;
						}

						// start html table for this contact
						$cur_cid_html = "";
						$cur_cid_html = $cur_cid_html.$html_table_begin;

					}
					 
					$no_rec = false;
					//   figure out the next yahrzeit for each record,
					$deceased_name = $dao->deceased_name;
					$mourner_contact_id = $dao->contact_id;
					$mourner_name = $dao->sort_name;
					//$deceased_year = $dao->dyear;
					//$deceased_month = $dao->dmonth;
					//$deceased_day = $dao->dday;
					$hebrew_deceased_date = $dao->hebrew_deceased_date;
					$english_deceased_date = $dao->ddate;
					$deceased_date_before_sunset = $dao->d_before_sunset;
					$relationship_to_mourner = $dao->relationship_name_formatted;
					$yahrzeit_date_display = $dao->yahrzeit_date_display;
					$yahrzeit_type = $dao->yahrzeit_type;

					//$hebrew_date_format = 'dd MM yy';
					$erev_start_flag = '1';


					//$hebrew_deceased_date  = self::util_convert2hebrew_date($deceased_year, $deceased_month, $deceased_day, $deceased_date_before_sunset, $hebrew_date_format);
					//$gregorian_date_format_plain = 'yyyy-mm-dd';
					//$yahrzeit_date_tmp = self::util_get_next_yahrzeit_date( $deceased_year, $deceased_month, $deceased_day, $deceased_date_before_sunset, $erev_start_flag, $gregorian_date_format_plain);

					//$gregorian_date_format = "MM dd, yyyy";
					//$yahrzeit_date_formated_tmp = self::util_get_next_yahrzeit_date( $deceased_year, $deceased_month, $deceased_day, $deceased_date_before_sunset, $erev_start_flag, $gregorian_date_format );


					if( $deceased_date_before_sunset == '1'){
						$deceased_date_before_sunset_formated = 'Yes';
					}else if( $deceased_date_before_sunset == '0'){
						$deceased_date_before_sunset_formated = 'No';
					}

					if($yahrzeit_type == '1'){
						$yahrzeit_type_formatted = 'English';
					}else{
						$yahrzeit_type_formatted = 'Hebrew';
					}



					$yahrzeit_html_row =   '<tr><td>'.$deceased_name.'</td><td>'.$hebrew_deceased_date.'</td><td>'.$english_deceased_date.
					'</td><td>'.$yahrzeit_date_display.'</td><td>'.$relationship_to_mourner.'</td><td>'.$yahrzeit_type_formatted.'</td> </tr>';


					$cur_cid_html = $cur_cid_html.$yahrzeit_html_row;
					//if(array_key_exists($mourner_contact_id,  $values)){
					 
					// $values[$mourner_contact_id][$token_yahrzeits_long] = $values[$mourner_contact_id][$token_yahrzeits_short] = $html_table_begin.$yahrzeit_html_row.$html_table_end;


					//	 }
					$prev_cid = $cur_cid;

				}

				$dao->free( );

				if ( $prev_cid != ""){
					// Wrap up table for previous contact.
					$cur_cid_html = $cur_cid_html.$html_table_end;
					$values[$prev_cid][$token_yahrzeits_long] = $cur_cid_html;
				}


				foreach ( $contactIDs as $cid ) {
					if(array_key_exists($cid,  $values)){
						if ( $values[$cid][$token_yahrzeits_long] == ""){
							$values[$cid][$token_yahrzeits_long] = $values[$cid][$token_yahrzeits_short] = "No yahrzeits found.";

						}

					}
				}

				
				// All done with Yizkor (all yahrzeits) token
				
				
				/****  Deal with the rest of the yahrzeit tokens, that consider date. ( for example: only yahrzeits in 7 days) ***************/
				// $token_date_portion

				//$yahrzeit_sql = $yahrzeit_data;
				/*
				$yahrzeit_date = $dao->yahrzeit_date;
				$yahrzeit_hebrew_date_format_english = $dao->yahrzeit_hebrew_date_format_english;
				// TODO: Put next field into a token.
				$yahrzeit_hebrew_date_format_hebrew = $dao->yahrzeit_hebrew_date_format_hebrew;
				$yahrzeit_date_raw = $dao->yahrzeit_date_sort;
				$yahrzeit_morning_format_english =  $dao->yahrzeit_morning_format_english ;
				*/
				
//				new col. name: yahrzeit_date_morning,
//				old col. name: yahrzeit_morning_format_english ,

			$yahrzeit_sql = "SELECT mourner_contact_id as contact_id, 
						mourner_contact_id as id, mourner_name as sort_name, deceased_name as deceased_name,
    deceased_contact_table.display_name as deceased_display_name, deceased_contact_id, 
						contact_b.deceased_date, 
						yahrzeit_date, yahrzeit_hebrew_date_format_english, yahrzeit_hebrew_date_format_hebrew,
						yahrzeit_date_morning, 
    contact_b.deceased_date as ddate,
    d_before_sunset, hebrew_deceased_date,
     concat( year(yahrzeit_date), '-', month(yahrzeit_date), '-', day(yahrzeit_date)) as yahrzeit_date_sort , yahrzeit_date_display, 
						relationship_name_formatted,
      yahrzeit_type, mourner_observance_preference
       FROM ".$yahrzeit_temp_table_name." contact_b INNER JOIN civicrm_contact contact_a ON contact_a.id = contact_b.mourner_contact_id
       JOIN civicrm_contact deceased_contact_table ON deceased_contact_table.id = contact_b.deceased_contact_id
       WHERE contact_b.created_date >= DATE_SUB(CURDATE(), INTERVAL 10 MINUTE) AND (yahrzeit_type = mourner_observance_preference)
       AND yahrzeit_date >= CURDATE()
       AND contact_b.mourner_contact_id in (   $cid_list )
        AND date(yahrzeit_date) = DATE(  CURDATE() + INTERVAL ".$date_number_from_token." ".$interval_type_sql_str." )
       ORDER BY sort_name asc";
					
				//print "<br><br>SQL: ".$yahrzeit_sql;
				 
		if( strlen($yahrzeit_sql) > 0 ){
			$dao =& CRM_Core_DAO::executeQuery( $yahrzeit_sql ,   CRM_Core_DAO::$_nullArray ) ;

			// Figure out how to format date for this locale
			$config = CRM_Core_Config::singleton( );

			$tmp_system_date_format = 	$config->dateInputFormat;
			if($tmp_system_date_format == 'dd/mm/yy'){
				$gregorian_date_format = "j F Y";
				 
			}else if($tmp_system_date_format == 'mm/dd/yy'){
				$gregorian_date_format = "F j, Y";
				 
			}else{
				print "<br>Configuration Issue: Unrecognized System date format: ".$tmp_system_date_format;
				 
			}


					$tmp_deceasedids_for_con= array();
					// print "<br<br>About to process yahrzeit records. ";
					while ( $dao->fetch( ) ) {
						 
						
						$cid = $dao->contact_id;
						$mourner_name = $dao->sort_name;
						$deceased_name = $dao->deceased_name;
						$deceased_display_name = $dao->deceased_display_name;
						$english_deceased_date = $dao->deceased_date;
						$hebrew_deceased_date = $dao->hebrew_deceased_date;
						$yahrzeit_date = $dao->yahrzeit_date;
						$yahrzeit_date_display = $dao->yahrzeit_date_display;
						$relationship_name_formatted = $dao->relationship_name_formatted;
						$yahrzeit_hebrew_date_format_english = $dao->yahrzeit_hebrew_date_format_english;
						$yahrzeit_hebrew_date_format_hebrew = $dao->yahrzeit_hebrew_date_format_hebrew;
						$yahrzeit_date_raw = $dao->yahrzeit_date_sort;
						$yahrzeit_morning_format_english =  $dao->yahrzeit_date_morning;
					// $yahrzeit_morning_format_english


						$default_seperator = ", ";

						if(array_key_exists($cid,  $values)){
							// print "<br>Fill in token values.";
							if( isset( $tmp_deceasedids_for_con[$cid] )){
								$arr_dec_ids = explode(";",  $tmp_deceasedids_for_con[$cid] );
							}else{
								$tmp_deceasedids_for_con[$cid] = "";
								$arr_dec_ids = array();
							}
								
							if( in_array($dao->deceased_contact_id, $arr_dec_ids   )  == false){

								if( strlen($relationship_name_formatted ) == 0 ){
									
									// There is no relationship indicated in the database, use something generic for token. 
									$relationship_name_formatted = "loved one";
								}
								
								//if( isset( $tmp_deceasedids_for_con[$cid]) ){
								$tmp_deceasedids_for_con[$cid] = $tmp_deceasedids_for_con[$cid].";".$dao->deceased_contact_id;
								//}
								if(isset( $values[$cid][$token_yah_dec_name]) && strlen( $values[$cid][$token_yah_dec_name] ) > 0 ){   
									$seper = $default_seperator; 
								}else{  $seper = "";    }  ;

								if(isset( $values[$cid][$token_yah_dec_name])){
									$values[$cid][$token_yah_dec_name] = $values[$cid][$token_yah_dec_name].$seper.$deceased_display_name ;
								}else{
									$values[$cid][$token_yah_dec_name] = $deceased_display_name ;
								}

								if(isset( $values[$cid][$token_yah_english_date] ) && strlen( $values[$cid][$token_yah_english_date] ) > 0 ){   
									$seper = $default_seperator;  
								}else{ 
										$seper = "";  
									}  ;
									
								if( isset( $values[$cid][$token_yah_english_date] )){	
									$values[$cid][$token_yah_english_date] = $values[$cid][$token_yah_english_date].$seper.$yahrzeit_date_display;
								}else{
									$values[$cid][$token_yah_english_date] = $seper.$yahrzeit_date_display;
								}

								if( isset($values[$cid][$token_yah_hebrew_date])  && strlen( $values[$cid][$token_yah_hebrew_date])  > 0 ){   
									$seper = $default_seperator; 
								}else{  
									$seper = "";    
								}  ;
								
								if( isset( $values[$cid][$token_yah_hebrew_date] )){
									$values[$cid][$token_yah_hebrew_date] = $values[$cid][$token_yah_hebrew_date].$seper.$yahrzeit_hebrew_date_format_english;
								}else{
									$values[$cid][$token_yah_hebrew_date] = $yahrzeit_hebrew_date_format_english;
								}

								if( isset($values[$cid][$token_yah_dec_death_english_date]) && strlen( $values[$cid][$token_yah_dec_death_english_date])  > 0 ){   
									$seper = $default_seperator;  
								}else{ 
										$seper = "";   
								}  ;
								
								
								if(isset($values[$cid][$token_yah_dec_death_english_date])){
									$values[$cid][$token_yah_dec_death_english_date] =  $values[$cid][$token_yah_dec_death_english_date].$seper.$english_deceased_date ;
								}else{
									$values[$cid][$token_yah_dec_death_english_date] =  $english_deceased_date ;
								}

								if( isset($values[$cid][$token_yah_dec_death_hebrew_date]) && strlen( $values[$cid][$token_yah_dec_death_hebrew_date])  > 0 ){   
									$seper = $default_seperator; 
								}else{  $seper = "";    }  ;
								
								if(isset( $values[$cid][$token_yah_dec_death_hebrew_date] )){
								$values[$cid][$token_yah_dec_death_hebrew_date] =  $values[$cid][$token_yah_dec_death_hebrew_date].$seper.$hebrew_deceased_date ;
								}else{
									$values[$cid][$token_yah_dec_death_hebrew_date] =  $hebrew_deceased_date ;
								}

								
								if(isset( $values[$cid][$token_yah_relationship_name]) && strlen( $values[$cid][$token_yah_relationship_name] ) > 0 ){    
									$seper = $default_seperator; 
								}else{  $seper = "";    }  ;
								
								if(isset($values[$cid][$token_yah_relationship_name] )){
									$values[$cid][$token_yah_relationship_name] = $values[$cid][$token_yah_relationship_name].$seper.$relationship_name_formatted ;
								}else{
									$values[$cid][$token_yah_relationship_name] = $relationship_name_formatted ;
								}

								if( isset( $values[$cid][$token_yah_english_date_morning]) && strlen( $values[$cid][$token_yah_english_date_morning] ) > 0 ){   
									$seper = $default_seperator; 
								}else{  $seper = "";    }  ;
								
								if( isset($values[$cid][$token_yah_english_date_morning])){
									$values[$cid][$token_yah_english_date_morning] = $values[$cid][$token_yah_english_date_morning].$seper.$yahrzeit_morning_format_english ;
								}else{
									$values[$cid][$token_yah_english_date_morning] = $yahrzeit_morning_format_english ;
								}

								// take care of tokens for Friday, Saturday before the yahrzeit, and the Friday, Saturday after the yahrzeit.
								$yah_timestamp = strtotime($yahrzeit_date_raw);
								$yah_day_of_week = date( 'w', $yah_timestamp);
								 

								if($yah_day_of_week == 5){
									// The yahrzeit starts at erev Shabbat (ie Friday night), return the yahrzeit date itself.
									// A synagogue in this situation will read the name during services that same shabbat.
									$formatted_friday_before = date( $gregorian_date_format,  $yah_timestamp );
									$formatted_friday_after = date($gregorian_date_format,  $yah_timestamp );

									// Since the yahrzeit itself is a Friday, shabbat morning is the next day.
									$formatted_saturday_before = date($gregorian_date_format,  strtotime(date("Y-m-d", $yah_timestamp) ." +1 day"));
									$formatted_saturday_after = date($gregorian_date_format,  strtotime(date("Y-m-d", $yah_timestamp) ." +1 day"));
									 
								}else if($yah_day_of_week == 6){
									// The yahrzeit starts on a Saturday night.
									// So the Shabbat morning before the yahrzeit is the same English date as the start of the yahrzeit date.
									$formatted_saturday_before = date($gregorian_date_format,  $yah_timestamp );
									$formatted_saturday_after = date($gregorian_date_format,  strtotime(date("Y-m-d", $yah_timestamp) ." next Saturday"));

									// Do the usual process for getting erev Shabbat before and after.
									$formatted_friday_before = date($gregorian_date_format,  strtotime(date("Y-m-d", $yah_timestamp) ." previous Friday"));
									$formatted_friday_after = date($gregorian_date_format,  strtotime(date("Y-m-d", $yah_timestamp) ." next Friday"));

								}else{
									$formatted_friday_before = date($gregorian_date_format,  strtotime(date("Y-m-d", $yah_timestamp) ." previous Friday"));
									$formatted_friday_after = date($gregorian_date_format,  strtotime(date("Y-m-d", $yah_timestamp) ." next Friday"));

									$formatted_saturday_before = date($gregorian_date_format,  strtotime(date("Y-m-d", $yah_timestamp) ." previous Saturday"));
									$formatted_saturday_after = date($gregorian_date_format,  strtotime(date("Y-m-d", $yah_timestamp) ." next Saturday"));


								}

								if(isset( $values[$cid][$token_yah_erev_shabbat_before] ) && strlen( $values[$cid][$token_yah_erev_shabbat_before] ) > 0 ){   
									$seper = $default_seperator;  }else{  $seper = "";    }  ;
								
								if( isset( $values[$cid][$token_yah_erev_shabbat_before] )){
									$values[$cid][$token_yah_erev_shabbat_before] = $values[$cid][$token_yah_erev_shabbat_before].$seper.$formatted_friday_before;
								}else{
									$values[$cid][$token_yah_erev_shabbat_before] = $formatted_friday_before;
								}

								if(isset( $values[$cid][$token_yah_shabbat_morning_before] ) && strlen( $values[$cid][$token_yah_shabbat_morning_before] ) > 0 ){  
									$seper = $default_seperator;  
								}else{  $seper = "";    }  ;
								
								if(isset($values[$cid][$token_yah_shabbat_morning_before])){
									$values[$cid][$token_yah_shabbat_morning_before] = $values[$cid][$token_yah_shabbat_morning_before].$seper.$formatted_saturday_before;
								}else{
									$values[$cid][$token_yah_shabbat_morning_before] = $formatted_saturday_before;
								}

								if(isset( $values[$cid][$token_yah_erev_shabbat_after] ) && strlen( $values[$cid][$token_yah_erev_shabbat_after] ) > 0 ){   
									$seper = $default_seperator;  
								}else{  $seper = "";    }  ;
								
								if(isset($values[$cid][$token_yah_erev_shabbat_after])){
									$values[$cid][$token_yah_erev_shabbat_after] =  $values[$cid][$token_yah_erev_shabbat_after].$seper.$formatted_friday_after;
								}else{
									$values[$cid][$token_yah_erev_shabbat_after] =  $formatted_friday_after;
								}

								if(isset( $values[$cid][$token_yah_shabbat_morning_after] ) && strlen( $values[$cid][$token_yah_shabbat_morning_after] ) > 0 ){  
									$seper = $default_seperator; 
								}else{  $seper = "";    }  ;
								
								
								if( isset($values[$cid][$token_yah_shabbat_morning_after])){
									$values[$cid][$token_yah_shabbat_morning_after] =  $values[$cid][$token_yah_shabbat_morning_after].$seper.$formatted_saturday_after;
								}else{
									$values[$cid][$token_yah_shabbat_morning_after] =  $formatted_saturday_after;
								}

							}
						}

					}
					$dao->free( );

				}

	}

	/******************************************************************
	 *
	 *
	 *
	 *********************************************************************/
	function util_get_hebrew_month_name( &$julian_date, &$hebrew_date){

		/* TODO: Use month spellings from HebCal.com for all months.
		PHP spellings (hebcal.com spellings):
		[1] => Tishri  (Tishrei)
		[2] => Heshvan (Cheshvan)
		[3] => Kislev ()
		[4] => Tevet ()
		[5] => Shevat (Sh'vat)
            [6] => AdarI (Adar I)
            [7] => AdarII (Adar II)
            [8] => Nisan ()
            [9] => Iyyar ()
            [10] => Sivan ()
            [11] => Tammuz (Tamuz)
            [12] => Av ()
            [13] => Elul
            */
		
		list($hebrewMonth, $hebrewDay, $hebrewYear) = split('/',$hebrew_date);

		if($hebrewMonth == HebrewCalendar::HEBREW_MONTH_TISHREI){
			return ts("Tishrei");	
		}else if($hebrewMonth == HebrewCalendar::HEBREW_MONTH_HESHVAN){
			return ts("Cheshvan");
		}else if($hebrewMonth == HebrewCalendar::HEBREW_MONTH_KISLEV){
			return ts("Kislev");
		}else if($hebrewMonth == HebrewCalendar::HEBREW_MONTH_TEVET){
			return ts("Tevet");
		}else if($hebrewMonth == HebrewCalendar::HEBREW_MONTH_SHEVAT){
			return ts("Sh'vat");
		}else if( $hebrewMonth == HebrewCalendar::HEBREW_MONTH_ADAR ){
			/* Its Adar or AdarI */
			
			$hebrew_leap_year = $this->is_hebrew_year_leap_year($hebrewYear);
			if( $hebrew_leap_year){
				return ts("Adar I");
			}else{
				return ts("Adar");
			}
		}else if($hebrewMonth == HebrewCalendar::HEBREW_MONTH_ADAR_2){
			return ts("Adar II");
		}else if($hebrewMonth == HebrewCalendar::HEBREW_MONTH_NISAN){
			return ts("Nisan");
		}else if($hebrewMonth == HebrewCalendar::HEBREW_MONTH_IYYAR){
			return ts("Iyyar");
		}else if($hebrewMonth == HebrewCalendar::HEBREW_MONTH_SIVAN){
			return ts("Sivan");
		}else if($hebrewMonth == HebrewCalendar::HEBREW_MONTH_TAMUZ){
			return ts("Tamuz");
		}else if($hebrewMonth == HebrewCalendar::HEBREW_MONTH_AV){
			return ts("Av");
		}else if($hebrewMonth == HebrewCalendar::HEBREW_MONTH_ELUL){
			return ts("Elul");
		}else{
			 
			 //Old logic: just use the PHP function to get the month name. */
			//return jdmonthname($julian_date,4);
			
			// something went wrong,
			return "Unknown Month";
		}

	}


	/******************************************************************
	 *   Return true if the Hebrew year is a leap year. Otherwise
	 * return false.
	 *
	 *
	 *********************************************************************/
	function is_hebrew_year_leap_year( $hebrewYear){
		
		
  // https://en.wikipedia.org/wiki/Hebrew_calendar
  //  To determine whether year n of the calendar is a leap year, 
  // find the remainder on dividing [(7 × n) + 1] by 19. 
  // If the remainder  is 6 or less it is a leap year; if it is 7 or more it is not.
		
		$tmp = (7 * $hebrewYear) + 1;
		$remainder = $tmp % 19; 
		
		if( $remainder <= 6 ){
			return true;
		}else{
			return false;
		}
		
		
		
		
		/*
		// Check if the 1st of Adar II is a valid day. If it is, then its a leap year. 
		$tmp_adarII_month = '7';
		$tmp_adarII_day = '01';

		$hebrew_leap_year  = self::ver_hebrew_date($hebrewYear , $tmp_adarII_month, $tmp_adarII_day);
		if( $hebrew_leap_year == '1'){
			return true;
		}else{
			return false;
		}
		*/

	}
	
	
	
	/***************************************************************
	 *  Get the current date from the server and return the
	 * formatted Hebrew Date.
	 *
	 ****************************************************************/
	function util_convert_today2hebrew_date(&$hebrew_format ){


		$today = date_create();

		$gregorianMonth = $today->format('n');
		$gregorianDay = $today->format('j');
		$gregorianYear = $today->format('Y');

		//date_default_timezone_set('America/Chicago');
		$ibeforesunset = '1';
		$today_hebrew_formated = self::util_convert2hebrew_date($gregorianYear, $gregorianMonth, $gregorianDay, $ibeforesunset, $hebrew_format);
		return $today_hebrew_formated;


	}


	/******************************************************************
	 *
	 *
	 *
	 *********************************************************************/
	function util_get_next_hebrew_birthday_date(&$iyear, &$imonth, &$iday, &$ibeforesunset, &$erev_start_flag,  &$gregorian_date_format){
		//date_default_timezone_set('America/Chicago');
		$heb_format_tmp = 'mm/dd/yy';
		$birthdate_hebrew = self::util_convert2hebrew_date($iyear, $imonth, $iday, $ibeforesunset, $heb_format_tmp );
		
		//  birthdate_hebrew ( will be used for bar bat Mitzvah calculation: $birthdate_hebrew ;
		
		$dob_heb_array = explode( '/',$birthdate_hebrew);   //
		//list($hebrewbirthMonth, $hebrewbirthDay, $hebrewbirthYear) = split('/',$birthdate_hebrew);
		
		if(count( $dob_heb_array ) == 3){
			$hebrewbirthMonth = $dob_heb_array[0];
			$hebrewbirthDay = $dob_heb_array[1];
			$hebrewbirthYear = $dob_heb_array[2];
		}else{
			$hebrewbirthMonth = "";
			$hebrewbirthDay = "";
			$hebrewbirthYear = "";
				
		}
		
		
		$heb_year_format = "yy";
		$current_hebrew_year = self::util_convert_today2hebrew_date($heb_year_format);
		
		$yesterday =  date( mktime(0, 0, 0, date("m") , date("d") - 1, date("Y")));
		$yesterday_formatted = date('M j Y',  $yesterday);
		
		$purpose = 'birthday';
		
		
		if(strlen( $gregorian_date_format) > 0 ){
			$tmpformat = $gregorian_date_format;
		}else{
			$tmpformat = 'yyyy-mm-dd';
		}
		
		
		//$current_year_heb_birthday  = self::util_convert_hebrew2gregorian_date($current_hebrew_year , $hebrewbirthMonth, $hebrewbirthDay, $erev_start_flag, $tmpformat);
		
		
		//if($current_year_heb_birthday ==  "Date requested does not exist."  ){
			
		$current_year_heb_birthday = self::util_adjust_hebrew_date($hebrewbirthYear,  $current_hebrew_year , $hebrewbirthMonth, $hebrewbirthDay, $erev_start_flag, $tmpformat, $purpose);
			//$heb_date_is_adjusted = " (adjusted)";
		//}
		
		
		if( strtotime($current_year_heb_birthday)  >= $yesterday  ){
			// Current year heb birthday: $current_year_heb_birthday  is equal to or later than yesterday: $yesterday.
			$correct_heb_birthday = $current_year_heb_birthday;
		
		}else{
			// Current year heb birthday has already past the date: $yesterday. Advance hebrew year by 1.
			
			$next_hebrew_year = $current_hebrew_year + 1;
			
			//CRM_Core_Error::debug("Current Hebrew year birthday is in the past, check next Hebrew year ", $next_hebrew_year  );
			
			$full_format = 'MM dd, yy sunset';
			$heb_date_is_adjusted = "";
			// $next_year_heb_birthday  = self::util_convert_hebrew2gregorian_date($next_hebrew_year, $hebrewbirthMonth, $hebrewbirthDay, $erev_start_flag, $tmpformat);
		
			//CRM_Core_Error::debug("next year heb birthday: ", $next_year_heb_birthday );
			
			//if($next_year_heb_birthday ==  "Date requested does not exist."  ){
		
			$next_year_heb_birthday = self::util_adjust_hebrew_date($hebrewbirthYear , $next_hebrew_year, $hebrewbirthMonth, $hebrewbirthDay, $erev_start_flag, $tmpformat, $purpose);
				//$heb_date_is_adjusted = " (adjusted)";
				//CRM_Core_Error::debug("(Adjusted) next year heb birthday: ", $next_year_heb_birthday );
			//}
		
		
			$correct_heb_birthday = $next_year_heb_birthday;
		}
		
		
		return $correct_heb_birthday;
		
	}
	
	
	
	/******************************************************************
	 *
	 *
	 *
	 *********************************************************************/
	function  util_get_bar_bat_mizvah_date(&$iyear, &$imonth, &$iday, &$ibeforesunset, &$erev_start_flag,  &$bar_bat_mitzvah_flag, &$gregorian_date_format  ){

		//date_default_timezone_set('America/Chicago');
		$heb_format_tmp = 'mm/dd/yy';
		$birthdate_hebrew = self::util_convert2hebrew_date($iyear, $imonth, $iday, $ibeforesunset, $heb_format_tmp );

		//  birthdate_hebrew ( will be used for bar bat Mitzvah calculation: $birthdate_hebrew ;
		$dob_heb_array = explode( '/',$birthdate_hebrew);   // 
		//list($hebrewbirthMonth, $hebrewbirthDay, $hebrewbirthYear) = split('/',$birthdate_hebrew);
		
		if(count( $dob_heb_array ) == 3){
			$hebrewbirthMonth = $dob_heb_array[0];
			$hebrewbirthDay = $dob_heb_array[1];
			$hebrewbirthYear = $dob_heb_array[2];
		}else{
			$hebrewbirthMonth = "";
			$hebrewbirthDay = "";
			$hebrewbirthYear = "";
			
		}
				
		$bar_bat_mitzvah_year = '';
		if(  $bar_bat_mitzvah_flag == 'bat'){
			// Technically a girl can be done as early as 12, but most congregations wait until 13.
			// TODO: Make this configurable by the congregation
			$bar_bat_mitzvah_year = $hebrewbirthYear + 13;
		}else if( $bar_bat_mitzvah_flag == 'bar'){
			$bar_bat_mitzvah_year = $hebrewbirthYear + 13;
		}else{
			return "bar_bat_mitzvah_flag must be either bar or bat. " ;
		}

		//$tmpformat = 'MM dd, yy sunset';
		if(strlen( $gregorian_date_format) > 0 ){
			$tmpformat = $gregorian_date_format;
		}else{
			$tmpformat = 'yyyy-mm-dd';
		}
		//$bar_bat_gregorian_date  = self::util_convert_hebrew2gregorian_date($bar_bat_mitzvah_year, $hebrewbirthMonth, $hebrewbirthDay, $erev_start_flag, $tmpformat);


		//if($bar_bat_gregorian_date ==  "Date requested does not exist."  ){
		$purpose = 'barbat';
		$bar_bat_gregorian_date = self::util_adjust_hebrew_date($hebrewbirthYear, $bar_bat_mitzvah_year, $hebrewbirthMonth, $hebrewbirthDay, $erev_start_flag, $tmpformat, $purpose);
		//	$heb_date_is_adjusted = " (adjusted)";
		//}

		return $bar_bat_gregorian_date;


	}

	/**********************************************************************************
	 *  Adjusts an invalid Hebrew date to a valid one. 
	 *  
     * There are only 2 times this can happen in a non-leap year:
     * 
     * Chesvan can have either 29 or 30 days. 
     * Kislev can have either 29 or 30 days.
     * 
     * In a leap year: AdarI always has 30 days. (Adar does not exist) and AdarII always has 29 days.
     * In a non-leap year: Adar always has 29 days.
     * 
	 *  Then converts the adjusted date to a Gregorian date. The Gregorian date is returned.
	 * 
	 * PHP numbers for impacted months:
	 *  2 => Cheshvan
     *  3 => Kislev
	 *  6 => AdarI or Adar
     *  7 => AdarII
	 ***********************************************************************************/
	function util_adjust_hebrew_date(&$original_hyear,  &$ihyear, &$original_hmonth, &$original_hday, &$erev_start_flag, &$tmpformat, &$purpose){

		
		// $original_hyear is the original Hebrew year that the death or birth occured in.
		// $ihyear is the Hebrew year that we want to observe the yahrzeit or birthday in.
		
		// purpose can be: 'yahrzeit', 'barbat' or 'birthday' (barbat and birthday are treated the same) 
		if( $purpose == "yahrzeit" ||  $purpose == "barbat" ||  $purpose == "birthday"){
			// nothing to do. 
		}else{
			return "Cannot adjust date: Unrecognized conversion purpose: ".$purpose;
			
		}
		
		if( $original_hyear  == "0000" ||  $original_hyear  == "0" || strlen(  $original_hyear) == 0 ){
			return "Cannot adjust date: Unrecognized original Hebrew year: ".$original_hyear;
		}
		
		$tmp_hday = $original_hday;
		$tmp_hmonth = $original_hmonth;
				
	//	CRM_Core_Error::debug("Inside util_adjust_hebrew_date function for Hebrew date: original year: ".$original_hyear , $ihyear."-".$original_hmonth."-".$original_hday);
		
		// is it Adar, AdarI or AdarII ( ie is month = '6' or '7'? )
		if( $original_hmonth == HebrewCalendar::HEBREW_MONTH_ADAR || $original_hmonth == HebrewCalendar::HEBREW_MONTH_ADAR_2 ){
				$is_original_leap_year_tmp = $this->is_hebrew_year_leap_year($original_hyear);
				$is_leap_year_tmp = $this->is_hebrew_year_leap_year($ihyear);
				
				// deal with leap year stuff.   4 possibilies:  
				// A) The original year and the observance year are both LEAP years.
				// B) The original year and the observance year are both non-leap years. 
				// C) The original year is a non-leap year, yet the observance year is a LEAP year
				// D) The original year is a LEAP year, yet the observance year is a non-leap year
				if( $is_original_leap_year_tmp == true &&  $is_leap_year_tmp == true){
					// A) birth/death occured in a LEAP year, and current observance is also a LEAP year. 
					// No need to touch AdarI or AdarII dates.	
					
				}else if( $is_original_leap_year_tmp <> true &&  $is_leap_year_tmp <> true ){
					// B) birth/death occured in a non-leap year AND current observance is also a non-leap year.
					// No need to touch AdarI or AdarII dates.
					
				}else if(  $is_original_leap_year_tmp <> true &&  $is_leap_year_tmp == true){
					// C) birth/death occured in a non-leap year YET observance is a LEAP year.
					
					if( $purpose == "barbat" || $purpose == "birthday"){
						// Adar (non-leap) birthdays are observed in AdarII. 
						$tmp_hmonth = HebrewCalendar::HEBREW_MONTH_ADAR_2;
					}else if( $purpose == "yahrzeit" ){
						// Adar (non-leap) yahrzeits are observed in AdarI. 
						$tmp_hmonth = HebrewCalendar::HEBREW_MONTH_ADAR;
						
					}
					
	
				}else if($is_original_leap_year_tmp == true &&  $is_leap_year_tmp <> true){
					// D) birth/death occured in a LEAP year YET observance is a non-leap year. 
					if( $original_hmonth == HebrewCalendar::HEBREW_MONTH_ADAR && $original_hday == '30' ) {
						// Original date was Adar I 30 , ie Rosh Hodesh Adar II during a leap year. 
						// This means move date back one day, to Shevat 30, which is also Rosh Hodesh Adar
						// This logic holds for borth birthdays and yahrzeits. 
						$tmp_hmonth = HebrewCalendar::HEBREW_MONTH_SHEVAT;	
						$tmp_hday  = '30';
					}
					
					// Since its non-leap, current observance is Adar.  
					if( $original_hmonth == HebrewCalendar::HEBREW_MONTH_ADAR_2){
						$tmp_hmonth = HebrewCalendar::HEBREW_MONTH_ADAR;
					}
					
					
				}else{	
					return "An impossible situation occured during comparison of leap year vs non-leap year. ";	
				}
		}  // done with Adar, AdarI and AdarII stuff.
		
		// Is original Hebrew month Chesvan or Kislev? (has nothing to do with leap year or non-leap year. )
		if( $original_hmonth == HebrewCalendar::HEBREW_MONTH_HESHVAN || $original_hmonth == HebrewCalendar::HEBREW_MONTH_KISLEV  ){
			// ONLY do this when 30th does not occur in observance year. 
			if($original_hday == '30'){
				// Does the 30th of the input month occur in this observance year?
				$valid_hebrew_date  = self::verify_hebrew_date($ihyear , $original_hmonth, $original_hday);
				
				if( $valid_hebrew_date == 0){
				   // the 30 does not occur in this observance year, need to decide to move it up or move it back.
				   $first_observance_heb_year = $original_hyear + 1;
					$is_first_observance_valid_hebrew_date = self::verify_hebrew_date( $first_observance_heb_year, $original_hmonth, $original_hday);
					
					if( $is_first_observance_valid_hebrew_date == 0 ){
						// since there is NO 30th for the first observance year, 
						// so move observance forward by one day (eg no Chesvan 30 on first observance
						// so observe yahrzeit/birthay on Kiselv 1. (which is also 1st day of Rosh Hodesh)
						// Meaning on Cheshvan 30 when it exists
						// and on Kislev 1 when Cheshvan 30 doesn’t exist.
						// for example: for a person that died/born on Chesvan 30, this means observe the yarzeit/birthday on Kislev 1.
						// for example: for a person that died/born on Kiselv 30, this means observe the yarzeit/birthday on Tevet 1.
						$tmp_hmonth = $original_hmonth + 1; 
						$tmp_hday  = '1';
						
					}else{
						// There IS a 30th for the first observance, so move move observance back
						// for example: for a person that died/born on Chesvan 30, this means observe the yarzeit/birthday on Cheshvan 29.
						// for example:for a person that died/born on Kiselv 30, this means observe the yarzeit/birthday on Kiselv 29.
						$tmp_hmonth = $original_hmonth;
						$tmp_hday  = '29';
						
						
					}
					// 30th does not occur, move back 1 day. OR move ahead one day, depending on 
					// what happens on 1st yahrzeit/birthday
					/*
					A) there IS NO Cheshvan 30 for the first yahrzeit, then the Yahrtzeit falls on 
					Kislev 1 and all the following Yahrtzeits will be on the 
					1st day of Rosh Hodesh. Meaning on Cheshvan 30 when it exists 
					and on Kislev 1 when Cheshvan 30 doesn’t exist.
					OR
					B) There IS Cheshvan 30, then the Yahrtzeit falls on
					 Cheshvan 30 and all the following Yahrtzeits will 
					 fall on the last day of Cheshvan. Meaning on Cheshvan 
					 30 (1st day Rosh Hodesh) when it exists and 
					 on Cheshvan 29 when Cheshvan 30 doesn’t exist.
						*/
					
				}
			}
		}
		
		
		// the rest is general code. 
		$tmp_new_date = self::util_convert_hebrew2gregorian_date($ihyear, $tmp_hmonth, $tmp_hday, $erev_start_flag, $tmpformat);
		//CRM_Core_Error::debug("Almost done with adjust function", $ihyear."-".$tmp_hmonth."-".$tmp_hday);
		// CRM_Core_Error::debug("Inside util_adjust_hebrew_date function: About to return Gregorian date: ", $tmp_new_date);
		return $tmp_new_date;
	}


	/**********************************************************************************
	 * Input is Gregorian date of death, plus if the death
	 *  occured before sunset or not.
	 * it returns the formatted Gregorian date of the yarhzeit.
	 ***********************************************************************************/
	function util_get_next_yahrzeit_date(&$iyear, &$imonth, &$iday, &$gregorian_ibeforesunset, $erev_start_flag, &$gregorian_format){
		$next_flag = 'next';
		return self::util_get_yahrzeit_date($next_flag, $iyear, $imonth, $iday, $gregorian_ibeforesunset, $erev_start_flag, $gregorian_format);

	}



	function util_get_yahrzeit_date($previous_next_flag , $iyear, $imonth, $iday, $gregorian_ibeforesunset, $erev_start_flag, $gregorian_format){

		//print "<br>Inside util_get_yahrzeit_date";
		// print " PARMS: flag: ".$previous_next_flag." iyear: ".$iyear." imonth: ".$imonth." iday: ".$iday;

		$defaultmsg = "Cannot determine yahrzeit date";
		if($iyear == ''  ){
			return $defaultmsg;
		}
		 
		if($imonth == ''  ){
			return $defaultmsg;
		}

		if($iday == ''  ){
			return $defaultmsg;
		}

		if($gregorian_ibeforesunset == ''){
			return $defaultmsg;
		}


		// date_default_timezone_set('America/Chicago');

		$heb_date_is_adjusted = "";
		$heb_format_tmp = 'mm/dd/yy';
		$deathdate_hebrew = self::util_convert2hebrew_date($iyear, $imonth, $iday, $gregorian_ibeforesunset, $heb_format_tmp );

		//print "<br>Hebrew death date: ".$deathdate_hebrew;

		list($hebrewdeathMonth, $hebrewdeathDay, $hebrewdeathYear) = split('/',$deathdate_hebrew);

		$heb_year_format = "yy";
		$current_hebrew_year = self::util_convert_today2hebrew_date($heb_year_format);

		// print "<br>cur Hebrew year: ".$current_hebrew_year;

		# Get yahrzeit date for the current Hebrew year.
		$tmpformat = 'yyyy-mm-dd';
		$purpose = "yahrzeit";
		//$current_year_yahrzetit  = self::util_convert_hebrew2gregorian_date($current_hebrew_year, $hebrewdeathMonth, $hebrewdeathDay, $erev_start_flag, $tmpformat);

		

		//if($current_year_yahrzetit ==  "Date requested does not exist."  ){
		$current_year_yahrzetit = self::util_adjust_hebrew_date($hebrewdeathYear, $current_hebrew_year, $hebrewdeathMonth, $hebrewdeathDay, $erev_start_flag, $tmpformat, $purpose);
		//	$heb_date_is_adjusted = " (adjusted)";
		
		//}else if ($current_year_yahrzetit == "Cannot determine Hebrew date" ){
		
			
		if ($current_year_yahrzetit == "Cannot determine Hebrew date" ){
			return "Cannot determine Hebrew date for current year yahrzeit";
		}

		$correct_yarzheit = '';
		$yesterday =  date( mktime(0, 0, 0, date("m") , date("d") - 1, date("Y")));
		$yesterday_formatted = date('M j Y',  $yesterday);

		
		//print "<br> ready to check flag";
		if( $previous_next_flag == 'next'){
			if( strtotime($current_year_yahrzetit)  >= $yesterday  ){
				// Current year yarhzeit: $current_year_yahrzetit  is equal to or later than yesterday: $yesterday.
				$correct_yarzheit = $current_year_yahrzetit;

			}else{
				// Current year yarhzeit has already past the date: $yesterday. Advance hebrew year by 1.
				$next_hebrew_year = $current_hebrew_year + 1;
				$full_format = 'MM dd, yy sunset';
				$heb_date_is_adjusted = "";
				//$next_year_yahrzetit  = self::util_convert_hebrew2gregorian_date($next_hebrew_year, $hebrewdeathMonth, $hebrewdeathDay, $erev_start_flag, $tmpformat);
				//if($next_year_yahrzetit ==  "Date requested does not exist."  ){
						
						
				$next_year_yahrzetit = self::util_adjust_hebrew_date($hebrewdeathYear , $next_hebrew_year, $hebrewdeathMonth, $hebrewdeathDay, $erev_start_flag, $tmpformat, $purpose);
					// $heb_date_is_adjusted = " (adjusted)";
				//}


				$correct_yarzheit = $next_year_yahrzetit;
			}
		}else{
			
	  // get previous yahrzeit.
			if( strtotime($current_year_yahrzetit)  < $yesterday  ){
				// Current year yarhzeit: $current_year_yahrzetit  is before yesterday: $yesterday.
				$correct_yarzheit = $current_year_yahrzetit;

			}else{
				// Current hebrew year yarhzeit in the future:  Subtract 1 from hebrew year.
				$prev_hebrew_year = $current_hebrew_year - 1;
				$full_format = 'MM dd, yy sunset';
				$heb_date_is_adjusted = "";
				//$prev_year_yahrzetit  = self::util_convert_hebrew2gregorian_date($prev_hebrew_year, $hebrewdeathMonth, $hebrewdeathDay, $erev_start_flag, $tmpformat);

				
				//if($prev_year_yahrzetit ==  "Date requested does not exist."  ){
					 
						
				$prev_year_yahrzetit = self::util_adjust_hebrew_date($hebrewdeathYear , $prev_hebrew_year, $hebrewdeathMonth, $hebrewdeathDay, $erev_start_flag, $tmpformat, $purpose);
					//$heb_date_is_adjusted = " (adjusted)";
				//}


				$correct_yarzheit = $prev_year_yahrzetit;
			}


		}

		// print "<br><br><b>correct yahrzeit to return : </b> ".$correct_yarzheit;

		$tmp_date = date_create($correct_yarzheit);

		if($tmp_date == ""){
			return "Cannot determine yahrzeit";

		}

		$gregorianMonthName = $tmp_date->format('F');
		$gregorianDay = $tmp_date->format('j');
		$gregorianYear =$tmp_date->format('Y');


		'yyyy-mm-dd';
		if( $gregorian_format == "MM dd, yyyy"){
			$gregorianMonthName = $tmp_date->format('F');
			$gregorianDay = $tmp_date->format('j');
			$gregorianYear =$tmp_date->format('Y');
			$yahrzeit_date_formatted  = "$gregorianMonthName $gregorianDay, $gregorianYear starting at sunset $heb_date_is_adjusted";

			return $yahrzeit_date_formatted ;
		}else if( $gregorian_format == "dd MM yyyy" ){

			$gregorianMonthName = $tmp_date->format('F');
			$gregorianDay = $tmp_date->format('j');
			$gregorianYear =$tmp_date->format('Y');
			$yahrzeit_date_formatted  = "$gregorianDay $gregorianMonthName $gregorianYear starting at sunset $heb_date_is_adjusted";

			return $yahrzeit_date_formatted ;


		} else if($gregorian_format == "yyyy-mm-dd"){
			$gregorianMonth = $tmp_date->format('m');
			$gregorianDay = $tmp_date->format('d');
			$gregorianYear =$tmp_date->format('Y');
			$yahrzeit_date_formatted  ="$gregorianYear-$gregorianMonth-$gregorianDay";
			return $yahrzeit_date_formatted ;

		}else{
			return "unrecognized gregorian format: $gregorian_format";
		}


	}

	/******************************************************************
	 *
	 *
	 *
	 *********************************************************************/
	function verify_hebrew_date($hebyear , $hebmonth, $hebday){
		/* This function verifies if the Hebrew date exists in reality. For example, 3 Hebrew months  */
		/* are variable length months. Adar, Heshvan, and Kieslev have either 29 or 30 days depending on */
		/* the year. The conversion from Julian to Hebrew ALWAYS produces a legit Hebrew date. The conversion from Hebrew to */
		/* Julian is not always accurate. By going both ways and verifying the results match, we can be certain */
		/* the Hebrew date is valid.
		 /* If valid date, return 1. Else return 0  */


		if($hebyear == '' || $hebmonth == '' || $hebday == ''){
			//  error:  year, month and day are all required.
			return 0;
		}
		$julian_datetmp =  cal_to_jd ( CAL_JEWISH  ,  $hebmonth , $hebday , $hebyear  );

		$tmp_gregorian = jdtogregorian( $julian_datetmp );
		
		
		$tmp_greg_arr = explode("/", $tmp_gregorian  );
		$tmp_greg_month = $tmp_greg_arr[0];
		$tmp_greg_day = $tmp_greg_arr[1];
		$tmp_greg_year = $tmp_greg_arr[2];
		
		
	//	CRM_Core_Error::debug("Inside verify part 1: Gregorian date: ", $tmp_gregorian." year: ".$tmp_greg_year." month: ".$tmp_greg_month." day: ".$tmp_greg_day ); 
		
		$jd_day_count_from_greg = cal_to_jd ( CAL_GREGORIAN , $tmp_greg_month , $tmp_greg_day , $tmp_greg_year );
		
	//	CRM_Core_Error::debug("Inside Verify for Greg. date $tmp_gregorian:", "Julian day count from Hebrew: ".$julian_datetmp." ANd Julian Day Count from Gregorian: ".$jd_day_count_from_greg);
		$hebrewDate_tmp = jdtojewish($julian_datetmp);
		

		
		list($hebrewMonth_tmp, $hebrewDay_tmp, $hebrewYear_tmp) = split('/',$hebrewDate_tmp);
		
		$tmp_heb_test = cal_from_jd($julian_datetmp, CAL_JEWISH  );
 	CRM_Core_Error::debug($hebyear."-".$hebmonth."-".$hebday.": inside verify function : ", $hebrewDate_tmp." Test heb: ".$tmp_heb_test['date']);
		
		
		// Hebrew date before: $hebmonth-$hebday-$hebyear / after round trip (mm-dd-yyyy): $hebrewMonth_tmp-$hebrewDay_tmp-$hebrewYear_tmp

		if( $hebrewMonth_tmp == $hebmonth && $hebrewDay_tmp == $hebday && $hebrewYear_tmp == $hebyear){
			return 1;

		}else{
			return 0;
		}


	}

	/******************************************************************
	 *
	 *
	 *
	 *********************************************************************/
	function util_convert2hebrew_date(&$iyear, &$imonth, &$iday, &$ibeforesunset, &$hebrewformat){
		 
		$defaultmsg = "Cannot determine Hebrew date";
		if($iyear == ''  ){
			return $defaultmsg." because year is blank";
		}
		 
		if($imonth == ''  ){
			return $defaultmsg." because month is blank";
		}

		if($iday == ''  ){
			return $defaultmsg." because day is blank";
		}


		if($ibeforesunset == ''  ){
			return $defaultmsg." because before sunset flag is blank";
		}




		# date_default_timezone_set('Europe/London');
		$idate_tmp = new DateTime("$iyear-$imonth-$iday");

		$idate_str = $idate_tmp->format('F j, Y');
		// Date provided: $idate_str

		$sunset_info_formated = '';
		if($ibeforesunset == "0"){
			$tmpdate_unix = mktime(0, 0, 0,  $idate_tmp->format('m')  ,  $idate_tmp->format('d')+1,  $idate_tmp->format('Y'));
			$tmpdate_array = getdate($tmpdate_unix );


			$gregorianMonth = $tmpdate_array['mon'];
			$gregorianDay = $tmpdate_array['mday'];
			$gregorianYear = $tmpdate_array['year'];
			// After sunset, so added 1 day to Gregorian date.

			$sunset_info_formated = '';

		}else if($ibeforesunset == "1"){
			$gregorianMonth = $idate_tmp->format('n');
			$gregorianDay = $idate_tmp->format('j');
			$gregorianYear = $idate_tmp->format('Y');

			$sunset_info_formated = ' until sunset';
			// Before sunset, so no change to Gregorian date.

		}else{
			return "Cannot determine Hebrew date because ibeforesunset is not 1 or 0.";

		}

		// Date to convert to Hebrew date( mm-dd-yyyy) :  $gregorianMonth - $gregorianDay - $gregorianYear

		$jdDate = gregoriantojd($gregorianMonth,$gregorianDay,$gregorianYear);


		if($hebrewformat == 'mm/dd/yy'){
			$hebrewDate = jdtojewish($jdDate);
			list($hebrewMonth, $hebrewDay, $hebrewYear) = split('/',$hebrewDate);
			$hebrew_date_formated = "$hebrewMonth/$hebrewDay/$hebrewYear";
		}else if($hebrewformat == 'dd MM yy sunset'){
			$hebrewDate = jdtojewish($jdDate);
			list($hebrewMonth, $hebrewDay, $hebrewYear) = split('/',$hebrewDate);
			$hebrewMonthName = self::util_get_hebrew_month_name($jdDate, $hebrewDate);
			$hebrew_date_formated = "$hebrewDay  $hebrewMonthName  $hebrewYear $sunset_info_formated";
		}else if($hebrewformat == 'dd MM yy' || $hebrewformat == 'dd_MM_yy'){
			$hebrewDate = jdtojewish($jdDate);
			list($hebrewMonth, $hebrewDay, $hebrewYear) = split('/',$hebrewDate);
			$hebrewMonthName = self::util_get_hebrew_month_name($jdDate, $hebrewDate);
			$hebrew_date_formated = "$hebrewDay $hebrewMonthName $hebrewYear";
		}else if($hebrewformat == 'dd MM' || $hebrewformat == 'dd_MM'  ){
			$hebrewDate = jdtojewish($jdDate);
			list($hebrewMonth, $hebrewDay, $hebrewYear) = split('/',$hebrewDate);
			$hebrewMonthName = self::util_get_hebrew_month_name($jdDate, $hebrewDate);
			$hebrew_date_formated = "$hebrewDay $hebrewMonthName";

		}else if($hebrewformat == 'yy'){
			$hebrewDate = jdtojewish($jdDate);
			list($hebrewMonth, $hebrewDay, $hebrewYear) = split('/',$hebrewDate);
			$hebrew_date_formated  = "$hebrewYear";
		}else if($hebrewformat == 'hebrew'){
			$hebrew_date_formated =  mb_convert_encoding( jdtojewish( $jdDate, true ), "UTF-8", "ISO-8859-8");
		}else{
			$hebrew_date_formated = "Unrecognized Hebrew date format: $hebrewformat";
		}



		// Hebrew Date formatted: $hebrew_date_formated
		return $hebrew_date_formated;

	}

	/******************************************************************
	 * Input parm is the Hebrew date and desired format. The Gregorian
	 * date is returned as a formated string.
	 *
	 *
	 *******************************************************************/
	function util_convert_hebrew2gregorian_date($iyear, $imonth, $iday, $erev_start_flag, $date_format){

		if($imonth==''){

			return "Month is required";
		}else if($iday=='' ){
			return "Day is required";

		}else if($iyear==''){

			return "year is required";
		}else{

			//CRM_Core_Error::debug("Debug: Inside convert_hebrew2gregorian: Hebrew date provided: ",  $iyear."-".$imonth."-".$iday);
			
			if( $imonth == HebrewCalendar::HEBREW_MONTH_HESHVAN  ||  $imonth == HebrewCalendar::HEBREW_MONTH_KISLEV){ 
				// if Chesvan or Kislev, these are the only 2 months where we do not know if the length is 29 or 30. 
				if( $iday == "30"){
					$valid_hebrew_date  = self::verify_hebrew_date($iyear , $imonth, $iday);
		
					if( $valid_hebrew_date == 0){
						//CRM_Core_Error::debug("----Error: Inside convert_hebrew2gregorian: Invalid date: Hebrew date provided: ".$iyear."-".$imonth."-".$iday, "<hr>");
						return "Date requested does not exist.";
					}
				}
			}
			
			
			

			$julian_date =  cal_to_jd ( CAL_JEWISH  ,  $imonth , $iday ,$iyear  );
			$gregorian_date = cal_from_jd( $julian_date, CAL_GREGORIAN);


			$oDay = $gregorian_date['day'];
			$oYear = $gregorian_date['year'];
			$oMonthName = $gregorian_date['monthname'];
			$oMonth = $gregorian_date['month'];

			if( $erev_start_flag == '0'){
				$sunset_str = "until sunset";
				// wanted ending date, no change needed to Gregorian date.
			}else if($erev_start_flag == '1' ){

				$sunset_str = "starting at sunset";
				$tmpdate_unix = mktime(0, 0, 0,  $oMonth  ,  $oDay - 1,  $oYear);
				$tmpdate_array = getdate($tmpdate_unix );


				$oMonthName = $tmpdate_array['month'];
				$oMonth = $tmpdate_array['mon'];
				$oDay = $tmpdate_array['mday'];
				$oYear = $tmpdate_array['year'];
				// wanted Erev ( ie starting date), so subtracted 1 day from Gregorian date.

				
				
			}else{
				return "Unknown erev_start_flag, must be either '1' or '0' ";
			}



			$formatted_date_str = '';
			if($date_format == 'yyyy-mm-dd'){
				$dash = "-";
				$formatted_date_str= $oYear.$dash.$oMonth.$dash.$oDay;
				// Is Hebrew date valid?  $valid_str
			}else if($date_format == 'dd-mm-yyyy'){
				$slash = '-';
				$formatted_date_str= $oDay.$slash.$oMonth.$slash.$oYear;

			}else if($date_format == 'MM dd, yy sunset'){
				// numeric month: $oMonth

				$formatted_date_str = "$oMonthName $oDay, $oYear $sunset_str"  ;
			}else if($date_format == 'crm_api'){
				
				 $formatted_date_str = date( 'Y-m-d', $tmpdate_unix);
				 
			}else{
				$formatted_date_str = "Unknown date_format.";
			}
			// function util_convert_hebrew2gregorian_date about to return: $formatted_date_str
			// print "<br>".$formatted_date_str."<br>";
			return $formatted_date_str;
		}

	}



	public function XXXget_sql_table_name(){


		$yahrzeit_table_name = 'tests';

		 
		$tmp_table_name = $yahrzeit_table_name;

		return $tmp_table_name;
		/*
		  
		// check if table with this key already exists.
		//$table_missing = true;
		 
		$cur_schema_name = self::getSQLschema();
		 
		$table_sql = "SELECT table_name FROM information_schema.tables
		WHERE
		table_schema = '$cur_schema_name'
		AND table_name = '$tmp_table_name'"  ;


		//  print "<Br>sql: ".$table_sql;
		$table_dao =& CRM_Core_DAO::executeQuery( $table_sql ,   CRM_Core_DAO::$_nullArray ) ;

		//   print "<br>sql: ".$yahrzeit_sql;
		if( $table_dao->fetch( ) ) {
		// print "<br>Table already exists.";
		// $table_missing = false;
		}else{
		// print "<br>Table does NOT exist.";
		self::buildTempTable($tmp_table_name);
		}
		 
		 
		$table_dao->free();
		 
		$temp_table_data_needs_refresh  = true;

		// print "<br>Check if data is stale";
		// If data is stale, rebuild it. Also remove old records.
		if(self::ALWAYS_CLEAR_TEMP_TABLE){
		// this is typically only set in development environments.
		$temp_table_data_needs_refresh  = true;
		 
		}else{
		//      print "<br>About to call freshness function";
		$temp_table_data_needs_refresh  =  self::CheckFreshnessTempTable($yahrzeit_table_name );
		//	print "<br> done with freshness function";

		}
		if( $temp_table_data_needs_refresh ){
		//print "<br>Data needs refresh, refill temp table: ".$tmp_table_name;
		self::fillTempTable($tmp_table_name, false);
		}else{
		//print "<br>Data is okay, use existing records.";
		}
		 
		return $tmp_table_name;
		*/
	}


	public static function getSQLschema(){

		$tmp_schema_name = '';
		$sql = "SELECT SCHEMA() as tmp_sname from civicrm_contact limit 0, 1" ;
		$dao =& CRM_Core_DAO::executeQuery( $sql ,   CRM_Core_DAO::$_nullArray ) ;
		 
		if($dao->fetch( )){
			$tmp_schema_name = $dao->tmp_sname;
		}else{
			print "<br><br>Pogstone message: Cound NOT find schema using query: ".$sql;
		}
		 
		$dao->free();
		// print "<br>About to return schema name: ".$tmp_schema_name;
		return $tmp_schema_name;
	}

	private static function XXXCheckFreshnessTempTable($yahrzeit_table_name ){

		/*
		 // Check if its been more than x minutes since last data load.
		 $tmp_minutes = self::YAHRZEIT_CACHE_TIMEOUT;
		  


		 $sql_str = "SELECT min(TIMEDIFF(now(),created_date)) as time_diff from $yahrzeit_table_name
		 having time_diff > '00:$tmp_minutes:00'
		 order by time_diff ";

		 // print "<br>sql: ".$sql_str;
		 $record_found = false;
		 $return_needs_refresh = false;
		 $dao =& CRM_Core_DAO::executeQuery(  $sql_str ,   CRM_Core_DAO::$_nullArray ) ;

		 // print "<br>sql: ".$yahrzeit_sql;
		 if( $dao->fetch( ) ) {
		 $tmp_min_time = $dao->time_diff;
		 $record_found = true;
		 // print "<br>Found time diff: ".$tmp_min_time;

		 }
		  
		 $dao->free( );

		 // check for empty table
		 $sql_count = "Select count(*) as count from $yahrzeit_table_name";
		 $dao_count =& CRM_Core_DAO::executeQuery(  $sql_count ,   CRM_Core_DAO::$_nullArray ) ;

		 	
		 if( $dao_count->fetch( ) ) {
		 $tmp_count = $dao_count->count;
		 //print "<Br>Num records in temp table: ".$tmp_count;
		 if( $tmp_count == 0){
		 self::fillTempTable($yahrzeit_table_name, false);
		 }
		  
		 }
		 $dao_count->free();

		 if($record_found){
		 // print "<br>Time diff more than limit. Need to remove old records.";
		 $return_needs_refresh = true;
		 self::removeStaleRecords($yahrzeit_table_name, $tmp_minutes);
		  
		 }

		 return $return_needs_refresh;

		 */

	}


	private static function XXXremoveStaleRecords($yahrzeit_table_name, $tmp_limit){

		//  $sql_str = "DELETE from $yahrzeit_table_name where TIMEDIFF(now(),created_date) > '00:$tmp_limit:00'";
		//  $dao =& CRM_Core_DAO::executeQuery(  $sql_str ,   CRM_Core_DAO::$_nullArray ) ;

		// self::fillTempTable($yahrzeit_table_name, false);

	}










	function getYahrzeitDateEnglishObservance(&$deceased_year, &$deceased_month, &$deceased_day, &$previous_next_flag ){

		$tmp_return = '';
		$cur_year = date('Y');


		if(strlen($deceased_year) > 0 && strlen($deceased_month) > 0 && strlen($deceased_day) > 0){


			$tmp_yahrzeit_date_observe_english = new DateTime($cur_year.'-'.$deceased_month.'-'.$deceased_day);
			// Since this function is expected to return the evening of the yahrzeit, need to subtract 1 day.
			$tmp_yahrzeit_date_observe_english = $tmp_yahrzeit_date_observe_english->sub(new DateInterval('P1D')) ;

			if( $previous_next_flag == 'next'){
				if( $tmp_yahrzeit_date_observe_english < new DateTime()){
			  // add a year.
					$tmp_yahrzeit_date_observe_english->add(new DateInterval('P1Y')) ;
				}
			}else if($previous_next_flag == 'prev'){
				// print "<br>Need prev. English yahrzeit date. ";
				if( $tmp_yahrzeit_date_observe_english >= new DateTime()){
			  // subtract a year.
					$tmp_yahrzeit_date_observe_english->sub(new DateInterval('P1Y')) ;
				}


			}else{
				$tmp_return = "Unknown previous/next flag";

			}


			$tmp_return = $tmp_yahrzeit_date_observe_english->format('Y-m-d');
		}else{
			$tmp_return = "Unknown date";
		}

		// print "<h2>English observed yahrzeit: ".$tmp_yahrzeit_date_observe_english_sql."</h2>";


		return $tmp_return;


	}


	function getYahrzeitDateEnglishObservanceFormated(&$deceased_year, &$deceased_month, &$deceased_day, &$previous_next_flag){
		$tmp_return = '';
		$cur_year = date('Y');

		if( strlen($deceased_month) == 0){
	  return "Cannot determine yahrzeit date";

		}

		if( strlen($deceased_day) == 0){
	  return "Cannot determine yahrzeit date";

		}

		$tmp_yahrzeit_date_observe_english = new DateTime($cur_year.'-'.$deceased_month.'-'.$deceased_day);

		if($previous_next_flag == 'next'){
			if( $tmp_yahrzeit_date_observe_english < new DateTime()){
				// add a year.
				$tmp_yahrzeit_date_observe_english->add(new DateInterval('P1Y')) ;
			}
		}else if($previous_next_flag == 'prev'){
			if( $tmp_yahrzeit_date_observe_english >= new DateTime()){
				// subtract a year.
				$tmp_yahrzeit_date_observe_english->sub(new DateInterval('P1Y')) ;
			}

		}else{
			print "<br><br>Inside function: getYahrzeitDateEnglishObservanceFormated, unknown previous_next_flag: ".$previous_next_flag;

		}
		$tmp_return = $tmp_yahrzeit_date_observe_english->format('F d, Y');

		return $tmp_return;



	}



}



?>