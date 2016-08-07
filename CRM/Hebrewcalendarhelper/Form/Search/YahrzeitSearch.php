<?php

/**
 * A custom contact search
 */
class CRM_Hebrewcalendarhelper_Form_Search_YahrzeitSearch extends 
CRM_Contact_Form_Search_Custom_Base implements CRM_Contact_Form_Search_Interface {
 

  protected $_formValues;
  //protected $_tableName = null;
  protected $_localHebrewCalendar = null;
   
  protected $_systemDateFormat = "";
  
  function __construct( &$formValues ) {
  	
  	//parent::__construct($formValues);
  	
  	require_once( 'utils/HebrewCalendar.php');
  	$this->_formValues = $formValues;
  
  	/**
  	 * Define the columns for search result rows
  	 */
  	$this->_columns = array(
  			ts('Mourner Name') => 'sort_name',
  			// ts('Mourner Membership Type') => 'membership_type_name',
  			// ts('Mourner Mem. Status') =>  'membership_status_name',
  			ts('Deceased Name (sortable)'      )   => 'deceased_name',
  			ts('Deceased Name (formatted)'      )   => 'deceased_display_name',
  			ts('Deceased Nickname') => 'nick_name',
  			ts('Deceased Hebrew Name') => 'hebrew_name',
  			ts('Date of Death') => 'deceased_date',
  			ts('Before Sunset?') => 'd_before_sunset',
  			ts('Hebrew Date of Death') => 'hebrew_deceased_date',
  			ts('Yahrzeit Date (evening, sortable)')  => 'yahrzeit_date_sort',
  			ts('Yahrzeit Date (evening, formatted)') => 'yahrzeit_date_display',
  			ts('Yahrzeit Date (morning, formatted)')  => 'yahrzeit_morning_format_english',
  			ts('Relationship to Mourner') => 'relationship_name_formatted',
  			ts('Hebrew Yahrzeit Date (Hebrew format)') => 'yahrzeit_hebrew_date_format_hebrew',
  			ts('Hebrew Yahrzeit Date') => 'yahrzeit_hebrew_date_format_english',
  			//  ts('Obervance type') => 'yahrzeit_type',
  			ts('Mourner Preference') => 'mourner_observance_preference',
  			ts('Plaque?') => 'has_plaque',
  			ts('Plaque Location') => 'plaque_location',
  			ts('') => 'deceased_contact_id',
  			ts('Email') => 'email',
  			ts('Phone') => 'phone',
  			ts('Street Address') => 'street_address',
  			ts('Supplemental Address') => 'supplemental_address_1',
  			ts('City') => 'city',
  			ts('State/Province') => 'abbreviation',
  			ts('Postal Code') => 'postal_code',
  			ts('Friday Night Before Yahrzeit') => 'yah_erev_shabbat_before',
  			ts('Saturday Morning Before Yahrzeit ' ) => 'yah_shabbat_morning_before',
  			ts('Friday Night After Yahrzeit') => 'yah_erev_shabbat_after',
  			ts('Saturday Morning After Yahrzeit ' ) => 'yah_shabbat_morning_after',
  			ts('Mourner Display Name' ) => 'mourner_display_name',
  			ts('Mourner First Name' ) => 'mourner_first_name',
  			ts('Mourner Last Name' ) => 'mourner_last_name',
  			ts('Mourner Household') => 'household_display_name',
  			ts('Mourner Household ID') => 'household_id',
  			ts('Relationship Description') => 'relationship_description',
  			ts('Relationship Note') => 'relationship_note',
  	);
  
  	$tmpHebCal = new HebrewCalendar();
  
  
  	$this->_localHebrewCalendar = $tmpHebCal;
  
  	$need_email = false;
  
  }
  
  
  
  function alterRow(&$row){
  
  	 
  
  	$row['deceased_name'] = "<a href='/civicrm/contact/view?reset=1&cid=".$row['deceased_contact_id']."'>".$row['deceased_name']."</a>";
  	 
  }
  
  
  
  
  function buildForm( &$form ) {
  	/**
  	 * You can define a custom title for the search form
  	 */
  	$this->setTitle('Find Yahrzeits');
  
  	 
  	//require_once('utils/Entitlement.php');
  	//$tmpEntitlement = new Entitlement();
  
  
  	$date_options = array(
  			'language'  => 'en',
  			'formatType'    => 'dMY',
  
  	);
  		
  	$form->addDate('start_date', ts('Date From'), false, array( 'formatType' => 'custom' ) );
  
  	$form->addDate('end_date', ts('...Through'), false, array( 'formatType' => 'custom' ) );
  
  	//require_once('utils/CustomSearchTools.php');
  	//$searchTools = new CustomSearchTools();
  	
  
  	$group_ids =   CRM_Core_PseudoConstant::group();
  
  	$relative_times_choices = array( '0' => 'Current Month', '1' => 'Next Month', '2' => '2 Months From Now' , '3' => '3 Months From Now', '4' => '4 Months From Now'
  			, '5' => '5 Months From Now', '6' => '6 Months From Now', '7' => '7 Months From Now', '8' => '8 Months From Now', '9' => '9 Months From Now', '10' => '10 Months From Now'
  			, '11' => '11 Months From Now', '12' => '12 Months From Now'  );
  
  	// TODO: get all mem ids, and org ids
  
  	//$mem_ids = $searchTools->getMembershipsforSelectList();
  
  	//$org_ids = $searchTools->getMembershipOrgsforSelectList();
  	 
  	 
  
  	//  $tmp_in_out_group = array( '' =>  '-- select --', 'IN' => 'In Group(s)', 'NOT IN' => 'Not In Group(s)');
  
  	//   $tmp_in_out_mem = array( '' =>  '-- select --', 'IN' => 'Has Membership Type(s)', 'NOT IN' => 'Does Not Have Membership Type(s)');
  
  	
  
  		$select2style = array(
  				'multiple' => TRUE,
  				'style' => 'width: 100%; max-width: 60em;',
  				'class' => 'crm-select2',
  				'placeholder' => ts('- select -'),
  		);
  		//
  
  		$form->add('select', 'group_of_contact',
  				ts('Mourner group(s)'),
  				$group_ids,
  				FALSE,
  				$select2style
  				);
  
  		$form->add('select', 'membership_org_of_contact',
  				ts('Mourner has Membership In'),
  				$org_ids,
  				FALSE,
  				$select2style
  				);
  
  		$form->add('select', 'membership_type_of_contact',
  				ts('Mourner Membership Type(s)'),
  				$mem_ids,
  				FALSE,
  				$select2style
  				);
  
  		$form->add('select', 'relative_time',
  				ts('Timeframe relative to today'),
  				$relative_times_choices,
  				FALSE,
  				$select2style
  				);
  
  
  
  	
  
  
  
  
  	/*
  	 $form->add('select', 'group_of_contact', ts('Mourner group(s)'), $group_ids, FALSE,
  	 array('id' => 'group_of_contact', 'multiple' => 'multiple', 'title' => ts('-- select --'))
  	 );
  
  	 */
  
  
  
  
  
  	// $form->add('select', 'membership_type_in_notin' , ts('Mourner Has or Not') ,  $tmp_in_out_mem, FALSE, array('id' => 'membership_type_in_notin' , 'title' => ts('-- select --')) ) ;
  
  
  
  	$tmp_yes_no =  array('all' => '-- select --',   'yes' => 'Deceased has Plaque', 'no' => 'Deceased does not have Plaque');
  	$tmp_has_plaque_select = $form->add  ('select', 'deceased_has_plaque', ts('Plaque Filter Choice'),
  			$tmp_yes_no,
  			false);
  	 
  	$tmp_deceased_mourners = array(   'only_living' => 'Only Include Individuals with Living Mourners',
  			'only_deceased' => 'Only Include Individuals with Deceased Mourners',
  			'no_mourner' => 'Only Include Individuals with No Mourners (Does NOT allow creation of PDF letters/email to mourner)',
  			'all' => 'Include any record (living, deceased, or no mourner. Does NOT allow creation of PDF letters/email to mourner)',);
  	$tmp_deceased_mourners_select = $form->add('select', 'living_mourners', ts('Mourner Status Choice'),
  			$tmp_deceased_mourners,
  			false);
  	 
  
  	 
  	$tmp_date_options = array('' => '-- select --',
  			'yahrzeit_date' => 'Yahrzeit Date - Evening (default)',
  			'yahrzeit_date_morning' => 'Yahrzeit Date - Morning',
  			'yahrzeit_erev_shabbat_before' => 'Friday Night Before Yahrzeit',
  			'yahrzeit_erev_shabbat_after' => 'Friday Night After Yahrzeit',
  			'yahrzeit_shabbat_morning_before' => 'Saturday Morning Before Yahrzeit' ,
  			'yahrzeit_shabbat_morning_after' => 'Saturday Morning After Yahrzeit'
  	);
  	$tmp_date_options_select = $form->add('select', 'date_to_filter', ts('Date to Filter'),
  			$tmp_date_options,
  			false);
  	 
  	$gender_options_tmp =  CRM_Contact_BAO_Contact::buildOptions('gender_id');
  
  	$gender_options = array("" => "-- select --");
  	foreach( $gender_options_tmp as $key => $val){
  		$gender_options[$key] = $val;
  
  	}
  
  	$gender_select = $form->add  ('select', 'gender_choice', ts('Mourner Gender'),
  			$gender_options,
  			false);
  	 
  
  // TODO: Get commprefs from API
  //	$comm_prefs =  $searchTools->getCommunicationPreferencesForSelectList();
  
  	$comm_prefs_select = $form->add  ('select', 'comm_prefs', ts('Communication Preference'),
  			$comm_prefs,
  			false);
  
  	 
  	/**
  	 * If you are using the sample template, this array tells the template fields to render
  	 * for the search form.
  	 */
  	$form->assign( 'elements', array(  'group_of_contact',  'membership_org_of_contact',  'membership_type_of_contact',
  			'relative_time' , 'start_date', 'end_date' , 'date_to_filter' ,  'deceased_has_plaque' ,
  			'yahrzeit_type_selection', 'living_mourners', 'gender_choice',  'comm_prefs' ) );
  
  
  }
  
  /**
   * Define the smarty template used to layout the search form and results listings.
   */
  function templateFile( ) {
  		 
  		return 'CRM/Contact/Form/Search/Custom.tpl';
  	
  }
   
  /**
   * Construct the search query
   */
  function all( $offset = 0, $rowcount = 0, $sort = null,
  		$includeContactIDs = false, $onlyIDs = false ) {
  
  			// SELECT clause must include contact_id as an alias for civicrm_contact.id
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
  
  			if($error_msg <> ''){
  
  				print "<br><h2>Configuration Problem: ".$error_msg."</h2>" ;
  				return '';
  			}
  			*/
  			$extended_date_table = "";
  			$extended_birth_date  = "";
  			$extended_death_date  = "";
  			
  			
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
  			// Get SQL table info for table with Hebrew name.
  			$custom_religious_field_group_label = "Religious";
  			$custom_hebrewname_field_label = "Hebrew Name";
  			$customFieldLabels = array($custom_hebrewname_field_label );
  			$extended_religious_table = "";
  			$outCustomColumnNames = array();
  			$error_msg = getCustomTableFieldNames($custom_religious_field_group_label , $customFieldLabels, $extended_religious_table, $outCustomColumnNames ) ;
  
  			$extended_hebrewname  =  $outCustomColumnNames[$custom_hebrewname_field_label];
  			if($error_msg <> ''){
  
  				print "<br><h2>Configuration Problem: ".$error_msg."</h2>" ;
  				return '';
  			}
  			*/
  			
  
  			
  			/*
  			// Get SQL table info for plaque table.
  			$custom_plaque_field_group_label = "Memorial Plaque Info";
  			$custom_plaque_location_field_label = "Plaque Location";
  			$custom_has_plaque_field_label = "Has Plaque";
  			$customFieldLabels = array($custom_plaque_location_field_label, $custom_has_plaque_field_label );
  			$extended_plaque_table = "";
  			$outCustomColumnNames = array();
  			$error_msg = getCustomTableFieldNames($custom_plaque_field_group_label , $customFieldLabels, $extended_plaque_table, $outCustomColumnNames ) ;
  
  			$extended_plaque_location  =  $outCustomColumnNames[$custom_plaque_location_field_label];
  			$extended_has_plaque =  $outCustomColumnNames[$custom_has_plaque_field_label];
  
  			if($error_msg <> ''){
  
  				print "<br><h2>Configuration Problem: ".$error_msg."</h2>" ;
  				return '';
  			}
  			
  			*/
  			/******************************************************************************/
  			// Get data for contacts
  
  			// make sure selected smart groups are cached in the cache table
  			$group_of_contact = $this->_formValues['group_of_contact'];
  
  			// TODO: refesh smart group cache.
  			//require_once('utils/CustomSearchTools.php');
  			//$searchTools = new CustomSearchTools();
  			//$searchTools::verifyGroupCacheTable($group_of_contact ) ;
  
  
  			$where = $this->where( $includeContactIDs );
  
  			$from =  $this->from();
  
  			$nice_date_format = '%M %e, %Y' ;
  
  			$mem_cols = "";
  			//if(count( $this->_formValues['membership_type_of_contact'] ) > 0 ){
  			//	$mem_cols = "mt.name as membership_type_name , mem_status.name  as membership_status_name,  ";
  			//}
  			// Figure out how to format date for this locale
  			$config = CRM_Core_Config::singleton( );
  			$tmp_system_date_format = 	$config->dateInputFormat;
  
  			$this->_systemDateFormat = $tmp_system_date_format;
  
  
  			if($tmp_system_date_format == 'dd/mm/yy'){
  		  $nice_date_format = '%e %M %Y' ;
  
  	  }else if($tmp_system_date_format == 'mm/dd/yy'){
  	  	$nice_date_format = '%M %e, %Y' ;
  	  	 
  	  }else{
  	  	print "<br>Configuration Issue: Unrecognized System date format: ".$tmp_system_date_format;
  	  	 
  	  }
  
  
  
  	  $full_select = " mourner_contact_id as contact_id, mourner_contact_id as id, mourner_name as sort_name,
	deceased_name as deceased_name, deceased_display_name as deceased_display_name, deceased_contact_id,
	mourner_email, date_format(contact_deceased.deceased_date, '".$nice_date_format."' )  as deceased_date , d_before_sunset, yahrzeit_hebrew_date_format_hebrew, yahrzeit_hebrew_date_format_english,
	hebrew_deceased_date, date_format(yahrzeit_date, '%Y-%m-%d' ) as yahrzeit_date_sort  ,
	date_format(yahrzeit_date, '".$nice_date_format."' ) as yahrzeit_date_display, ".$mem_cols."
	relationship_name_formatted, yahrzeit_type, if( mourner_observance_preference, 'English', 'Hebrew') as mourner_observance_preference,
	 contact_deceased.nick_name, ".$extended_hebrewname." as hebrew_name,
	if( mourner_observance_preference, date_format(yahrzeit_date_morning ,'".$nice_date_format."' )   ,
	  date_format( yahrzeit_date_morning, '".$nice_date_format."'   )) as yahrzeit_morning_format_english,
	".$extended_plaque_location." as plaque_location, if(".$extended_has_plaque." OR length(".$extended_plaque_location.") > 0, 'Yes', 'No') as has_plaque,
	civicrm_email.email, civicrm_phone.phone, civicrm_address.street_address,
		 civicrm_address.supplemental_address_1, civicrm_address.city , civicrm_address.postal_code,
		 civicrm_state_province.abbreviation,
		 date_format( yahrzeit_erev_shabbat_before, '".$nice_date_format."' ) as yah_erev_shabbat_before ,
		 date_format( yahrzeit_shabbat_morning_before, '".$nice_date_format."' ) as yah_shabbat_morning_before,
		 date_format( yahrzeit_erev_shabbat_after, '".$nice_date_format."' ) as yah_erev_shabbat_after ,
		 date_format( yahrzeit_shabbat_morning_after, '".$nice_date_format."' ) as yah_shabbat_morning_after,
		 yahrzeit_date as yahrzeit_date_raw,
		 contact_a.display_name as mourner_display_name,
		 contact_a.first_name as mourner_first_name,
		  contact_a.last_name as mourner_last_name,
		  group_concat(distinct hh.display_name) as household_display_name,
		  group_concat(distinct hh.id) as household_id,
		  rd.description as relationship_description,
		  rnote.note as relationship_note
		  ";
  
  
  
  	  if ( $onlyIDs ) {
  	  	$select = "mourner_contact_id as contact_id" ;
  	  }else{
  	  	$select = $full_select;
  
  	  }
  
  
  	  $tmp_group_by = " GROUP BY mourner_contact_id, deceased_contact_id, yahrzeit_date   ";
  
  	  $sql = "SELECT $select
  	  FROM $from
  	  WHERE $where
  	  ".$tmp_group_by ;
  
  	  $downstream_sql = "SELECT $full_select
  	  FROM $from
  	  WHERE $where
  	  ".$tmp_group_by.
  	  " ORDER BY yahrzeit_date, deceased_name ASC ";
  
  
  	   
  
  
  
  	  if ( !($onlyIDs )) {
  	  	//	$downstream_sql  = $sql." ORDER BY yahrzeit_date desc";
  	  	 
  	  	 
  	  }
  
  	  //for only contact ids ignore order.
  	  if ( !$onlyIDs ) {
  	  	// Define ORDER BY for query in $sort, with default value
  	  	//print "<br>sort: ".$sort."<br>";
  	  	if ( ! empty( $sort ) ) {
  	  		if ( is_string( $sort ) ) {
  	  			$sql .= " ORDER BY $sort ";
  	  		} else {
  	  			//print "<br>sort order: ".$sort->orderBy()."<br>";
  	  			$sql .= " ORDER BY " . trim( $sort->orderBy() );
  	  		}
  	  	} else {
  	  		$sql .=   " ORDER BY yahrzeit_date, deceased_name ASC";
  	  	}
  	  }
  
  
  	  // print "<br><br>SQL: ".$sql ;
  	  // print "<br>";
  	  if ( $rowcount > 0 && $offset >= 0 ) {
  	  	$sql .= " LIMIT $offset, $rowcount ";
  	  }
  
  	  /****  Put the sql statemetn in the session so it is avilable to downstream logic   ****/
  	  //  print "<br>downstream sql: $downstream_sql ";
  	  $_SESSION['yahrzeit_sql'] ='';
  	  $_SESSION['yahrzeit_sql'] =  $downstream_sql;
  	  //    $_SESSION['yahrzeit_temp_tablename'] = $this->_tableName ;
  
  	  $yahrzeit_data= $_SESSION['yahrzeit_sql'];
  
  	  //   print "<br><br>sql from session: ".$yahrzeit_data;
  	  /************      *****/
  
  
  	  return $sql;
  }
  
  function from(){
  	 
  	$tmp_cal = $this->_localHebrewCalendar;
  	$tmp_sql_table_name = $tmp_cal::get_sql_table_name() ;
  
  /*
  	// Get SQL table info for table with Hebrew name.
  	$custom_religious_field_group_label = "Religious";
  	$custom_hebrewname_field_label = "Hebrew Name";
  	$customFieldLabels = array($custom_hebrewname_field_label );
  	$extended_religious_table = "";
  	$outCustomColumnNames = array();
  	$error_msg = getCustomTableFieldNames($custom_religious_field_group_label , $customFieldLabels, $extended_religious_table, $outCustomColumnNames ) ;
  
  	$extended_hebrewname  =  $outCustomColumnNames[$custom_hebrewname_field_label];
  */
  
  	/*
  	// Get SQL table info for plaque table.
  	$custom_plaque_field_group_label = "Memorial Plaque Info";
  	$custom_plaque_location_field_label = "Plaque Location";
  	$custom_has_plaque_field_label = "Has Plaque";
  	$customFieldLabels = array($custom_plaque_location_field_label, $custom_has_plaque_field_label );
  	$extended_plaque_table = "";
  	$outCustomColumnNames = array();
  	$error_msg = getCustomTableFieldNames($custom_plaque_field_group_label , $customFieldLabels, $extended_plaque_table, $outCustomColumnNames ) ;
  
  	$extended_plaque_location  =  $outCustomColumnNames[$custom_plaque_location_field_label];
  	$extended_has_plaque =  $outCustomColumnNames[$custom_has_plaque_field_label];
  
  */
  
  	$tmp_group_join = "";
  	if(count( $this->_formValues['group_of_contact'] ) > 0 ){
  		$tmp_group_join = "LEFT JOIN civicrm_group_contact as groups on contact_b.mourner_contact_id = groups.contact_id
  		 LEFT JOIN civicrm_group_contact_cache as groupcache ON contact_b.mourner_contact_id = groupcache.contact_id ";
  		 
  	}
  	 
  	 
  	$tmp_mem_join = "";
  	if( count( $this->_formValues['membership_type_of_contact'] ) > 0 || count( $this->_formValues['membership_org_of_contact'] ) > 0     ){
  		$tmp_mem_join = "LEFT JOIN civicrm_membership as memberships on contact_b.mourner_contact_id = memberships.contact_id
	 	LEFT JOIN civicrm_membership_status as mem_status on memberships.status_id = mem_status.id
	 	LEFT JOIN civicrm_membership_type mt ON memberships.membership_type_id = mt.id
	 	";
  		 
  	}
  
  	// Get household of the mourner, if one exists.
  	$tmp_rel_type_ids = "7, 6";   // Household member of , Head of Household
  	$tmp_from_sql_hh_join = " LEFT JOIN civicrm_relationship rel ON contact_b.mourner_contact_id = rel.contact_id_a AND rel.is_active = 1 AND rel.relationship_type_id IN ( ".$tmp_rel_type_ids." ) ";
  
  
  
  
  	$tmp_from = "$tmp_sql_table_name contact_b
  	LEFT JOIN civicrm_contact contact_a ON contact_a.id =  contact_b.mourner_contact_id
  	LEFT JOIN civicrm_contact contact_deceased on contact_deceased.id = contact_b.deceased_contact_id
  	left JOIN civicrm_relationship rd ON rd.id = contact_b.yahrzeit_relationship_id
  	left join civicrm_note rnote ON rnote.entity_id = contact_b.yahrzeit_relationship_id AND rnote.entity_table = 'civicrm_relationship'
  	$tmp_from_sql_hh_join
  	LEFT JOIN civicrm_contact as hh ON rel.contact_id_b = hh.id AND hh.is_deleted <> 1
  	LEFT JOIN ".$extended_religious_table." extra_religious on contact_deceased.id = extra_religious.entity_id
	 LEFT JOIN ".$extended_plaque_table." extra_plaque on contact_deceased.id = extra_plaque.entity_id
  	 left join civicrm_email on contact_a.id = civicrm_email.contact_id AND civicrm_email.is_primary = 1
  	 left join civicrm_phone on contact_a.id = civicrm_phone.contact_id AND civicrm_phone.is_primary = 1
  	 left join civicrm_address on contact_a.id = civicrm_address.contact_id AND civicrm_address.is_primary = 1
  	 left join civicrm_state_province on civicrm_address.state_province_id = civicrm_state_province.id
  	 $tmp_group_join
  	 $tmp_mem_join";
  	  
  	 //print "<br><br> tmp from: ".$tmp_from ;
  	 return $tmp_from;
  }
  
  function where($includeContactIDs = false){
  	$clauses = array( );
  
  	$clauses[] = "contact_deceased.is_deleted <> 1";
  	$clauses[] = "( contact_a.id is null OR contact_a.is_deleted <> 1 ) ";
  
  
  	// Get SQL table info for plaque table.
  	$custom_plaque_field_group_label = "Memorial Plaque Info";
  	$custom_plaque_location_field_label = "Plaque Location";
  	$custom_has_plaque_field_label = "Has Plaque";
  	$customFieldLabels = array($custom_plaque_location_field_label, $custom_has_plaque_field_label );
  	$extended_plaque_table = "";
  	$outCustomColumnNames = array();
  	$error_msg = getCustomTableFieldNames($custom_plaque_field_group_label , $customFieldLabels, $extended_plaque_table, $outCustomColumnNames ) ;
  
  	$extended_plaque_location  =  $outCustomColumnNames[$custom_plaque_location_field_label];
  	$extended_has_plaque =  $outCustomColumnNames[$custom_has_plaque_field_label];
  
  
  
  	$groups_of_individual = $this->_formValues['group_of_contact'];
  
  	require_once('utils/CustomSearchTools.php');
  	$searchTools = new CustomSearchTools();
  
  
  
  	$comm_prefs = $this->_formValues['comm_prefs'];
  
  	$searchTools->updateWhereClauseForCommPrefs($comm_prefs, $clauses ) ;
  
  
  
  	$tmp_sql_list = $searchTools->getSQLStringFromArray($groups_of_individual);
  
  
  
  	//print "<br> sql list: ".$tmp_sql_list;
  	if(strlen($tmp_sql_list) > 0 ){
  
  
  		$clauses[] = "(   (groups.group_id IN (".$tmp_sql_list.") AND groups.status = 'Added') OR
				( groupcache.group_id IN (".$tmp_sql_list.") )  )";
  
  	}
  
  	$membership_types_of_con = $this->_formValues['membership_type_of_contact'];
  
  	$mem_type_IN_OR_NOT = $this->_formValues['membership_type_in_notin'];
  
  
  
  
  
  	$tmp_membership_sql_list = $searchTools->convertArrayToSqlString( $membership_types_of_con ) ;
  	if(strlen($tmp_membership_sql_list) > 0 ){
  		$in_tmp = "IN";
  		if(strcmp ($mem_type_IN_OR_NOT, "NOT IN" ) == 0){
  			$clauses[] = "( memberships.membership_type_id is NULL OR  memberships.membership_type_id NOT IN (".$tmp_membership_sql_list.")  )" ;
  		}else{
  			$clauses[] = "memberships.membership_type_id IN (".$tmp_membership_sql_list.")" ;
  			$clauses[] = "mem_status.is_current_member = '1'";
  			$clauses[] = "mem_status.is_active = '1'";
  
  		}
  	}
  
  	// 'membership_org_of_contact'
  	$membership_org_of_con = $this->_formValues['membership_org_of_contact'];
  	$tmp_membership_org_sql_list = $searchTools->convertArrayToSqlString( $membership_org_of_con ) ;
  	if(strlen($tmp_membership_org_sql_list) > 0 ){
  		// print "<br>membership orgs: <br>".$tmp_membership_org_sql_list;
  			
  		$clauses[] = "mt.member_of_contact_id IN (".$tmp_membership_org_sql_list.")" ;
  		$clauses[] = "mt.is_active = '1'" ;
  		$clauses[] = "mem_status.is_current_member = '1'";
  		$clauses[] = "mem_status.is_active = '1'";
  		//print_r($clauses);
  	}
  
  
  	$has_plaque = $this->_formValues['deceased_has_plaque'];
  	if(strcmp($has_plaque, 'yes') == 0  ){
  
  		$clauses[] = "(extra_plaque.".$extended_has_plaque." = '1' OR length(extra_plaque.".$extended_plaque_location.") > 0) ";
  	}else if(strcmp($has_plaque, 'no') == 0){
  		$clauses[] = "(( extra_plaque.".$extended_has_plaque." is NULL OR extra_plaque.".$extended_has_plaque." = '0') AND
		 (length(extra_plaque.".$extended_plaque_location.") is NULL OR length(extra_plaque.".$extended_plaque_location.") = 0)) ";
  	}else{
  
  
  	}
  
  	$living_mourners_choice =   $this->_formValues['living_mourners'];
  	if( $living_mourners_choice == "only_living"){
  		$clauses[] = "( contact_a.contact_type IN ( 'Household', 'Individual')  AND contact_a.is_deceased <> 1 )";
  	}else if($living_mourners_choice == "only_deceased"){
  		$clauses[] = "contact_a.is_deceased = 1";
  	}else if( $living_mourners_choice == "no_mourner" ){
  		$clauses[] = "contact_a.id IS NULL";
  	}
  
  
  	$gender_choice =   $this->_formValues['gender_choice'];
  	if( strlen( $gender_choice) > 0 ){
  		$clauses[] = "contact_a.gender_id = $gender_choice";
  
  	}
  	 
  	//  $clauses[] = "contact_b.created_date >= DATE_SUB(CURDATE(), INTERVAL 10  MINUTE)";
  	 
  
  
  
  	 
  	$clauses[] = "(yahrzeit_type = mourner_observance_preference) " ;
  
  
  	$startDate = CRM_Utils_Date::processDate( $this->_formValues['start_date'] );
  
  	$date_to_filter = $this->_formValues['date_to_filter'];
  	 
  	$date_sql_field_name = "";
  	if( strlen($date_to_filter) > 0 ){
  		$date_sql_field_name = $date_to_filter;
  
  	}else{
  		$date_sql_field_name = "yahrzeit_date" ;
  	}
  
  
  	if ( $startDate ) {
  		$clauses[] = $date_sql_field_name." >= $startDate";
  	}
  
  	$endDate = CRM_Utils_Date::processDate( $this->_formValues['end_date'] );
  	if ( $endDate ) {
  		$clauses[] = $date_sql_field_name." <= $endDate";
  	}
  
  
  	$relative_time_array = $this->_formValues['relative_time'];
  
  	if( is_array( $relative_time_array ) && count($relative_time_array) > 0){
  		 
  		$i = 0;
  		foreach( $relative_time_array as $relative_time){
  			if( $i == 0){
  				$rel_time_str = "(";
  			}else if( $i > 0 && strlen($rel_time_str) > 2 ){
  				$rel_time_str = $rel_time_str." OR ";
  			}
  			$rel_time_str = $rel_time_str." ( month($date_sql_field_name) =  MONTH( date_add( now() ,  INTERVAL $relative_time MONTH) )
  			AND year( $date_sql_field_name )  = YEAR ( date_add( now() ,  INTERVAL $relative_time MONTH) ) ) " ;
  			$i = $i + 1;
  
  		}
  	}
  	if( strlen( $rel_time_str) > 0){
  		$rel_time_str = $rel_time_str.")";
  		$clauses[] = $rel_time_str;
  	}
  	 
  	 
  	 
  	/*
  
  	$relative_time = $this->_formValues['relative_time'];
  	if( ($relative_time <> '' ) && is_numeric ($relative_time) ){
  	$clauses[] =  "month( $date_sql_field_name ) = MONTH( date_add( now() ,  INTERVAL $relative_time MONTH) )  " ;
  	$clauses[] =  "year( $date_sql_field_name )  = YEAR ( date_add( now() ,  INTERVAL $relative_time MONTH) )  " ;
  	}
  	*/
  	 
  
  	if ( $includeContactIDs ) {
  		$contactIDs = array( );
  		foreach ( $this->_formValues as $id => $value ) {
  			if ( $value &&
  					substr( $id, 0, CRM_Core_Form::CB_PREFIX_LEN ) == CRM_Core_Form::CB_PREFIX ) {
  						$contactIDs[] = substr( $id, CRM_Core_Form::CB_PREFIX_LEN );
  					}
  		}
  
  		if ( ! empty( $contactIDs ) ) {
  			$contactIDs = implode( ', ', $contactIDs );
  			$clauses[] = "contact_a.id IN ( $contactIDs )";
  		}
  	}
  
  	$tmp_rtn = implode( ' AND ', $clauses );
  
  	//  print "<br>where: ".$tmp_rtn ;
  	return $tmp_rtn;
  }
  
  
  
  
   
  /*
   * Functions below generally don't need to be modified
   */
  function count( ) {
  	$sql = $this->all( );
  	 
  	$dao = CRM_Core_DAO::executeQuery( $sql,
  			CRM_Core_DAO::$_nullArray );
  	return $dao->N;
  }
   
  
  function contactIDs( $offset = 0, $rowcount = 0, $sort = null, $returnSQL = false) {
  	return $this->all( $offset, $rowcount, $sort, false, true );
  }
   
  function &columns( ) {
  	return $this->_columns;
  }
  
  function setTitle( $title ) {
  	if ( $title ) {
  		CRM_Utils_System::setTitle( $title );
  	} else {
  		CRM_Utils_System::setTitle(ts('Search'));
  	}
  }
  
  function summary( ) {
  	return null;
  }
}
