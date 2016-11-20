<?php

require_once 'hebrewcalendarhelper.civix.php';

function hebrewcalendarhelper_civicrm_post( $op, $objectName, $objectId, &$objectRef ){
	// if an individual is being created or edited, rebuild yahrzeit data, hebrew birthday info.
	// 
	if( $objectName == 'Individual' && ($op == 'create' || $op == 'edit' || $op == 'restore' ) ){
		 
		// If there is a date of birth or date of death, then calculate Hebrew dates. 
		if( (isset($objectRef->death_date) && strlen($objectRef->death_date) > 0)  || 
			( isset( $objectRef->birth_date )  && strlen($objectRef->birth_date) > 0 )  ){
				// Calculate Hebrew demographic dates, such as next yahrzeit date, next hebrew birthday date for this contact.
				$params = array(
						'version' => 3,
						'sequential' => 1,
						'contact_ids' => $objectId,
				);
				$result = civicrm_api('AllHebrewDates', 'calculate', $params);
		
		}	
	}


}


// This functionality is now handled by read-only custom fields. 
function XXXhebrewcalendarhelper_civicrm_summary( $contactID, &$content, &$contentPlacement ) {
	
	
		// Add Hebrew date of death, Hebrew birthday and add this info 
		// to the back-office summary tab.
		require_once 'utils/HebrewCalendar.php';
		
		$contentPlacement = CRM_Utils_Hook::SUMMARY_BELOW; 
		$content = "";
		$tmpHebCal = new HebrewCalendar();
		$hebrew_data = $tmpHebCal->retrieve_hebrew_demographic_dates( $contactID);

		//if( isset($hebrew_data['error_message'] )){
		//$tmp_error_message  = $hebrew_data['error_message'] ;
		if(  isset($hebrew_data['error_message'])  && strlen( $hebrew_data['error_message']) > 0 ){
				
			$begin_content = contact_summary_determine_beginning_content();
			$middle_content = "<tr><td>Error Occured: ".$hebrew_data['error_message']."</td></tr>";
			$end_content = contact_summary_determine_ending_content();
				
			$content = $begin_content.$middle_content.$end_content;
				
		}else if( isset( $hebrew_data['contact_type']  ) && $hebrew_data['contact_type'] == 'Individual' ){
		
	
			$begin_content = contact_summary_determine_beginning_content();
			$middle_content = contact_summary_determine_middle_content( $hebrew_data  ) ;
			$end_content = contact_summary_determine_ending_content();

			$content = $begin_content.$middle_content.$end_content;
        

		}else{
		
			$content = "";

		 } // end of else
		
	
	
}


function hebrewcalendarhelper_civicrm_alterContent(  &$content, $context, $tplName, &$object ){

	/*

	if( $tplName ==  'CRM/Mailing/Form/Upload.tpl' ){
		
			$extra = "<h3>This area <b>CANNOT</b> be used to send personalized <b>yahrzeit</b> reminders</h3>
	 	     If you want to send personalized yahrzeit reminders using email, then use the 'Send Email to Contacts' action
	 	     from the 'Upcoming Yahrzeits' screen instead. ";

			$content = $extra.$content;

	}
	
	*/
}


function hebrewcalendarhelper_civicrm_tokens( &$tokens ){
	
	

	//$dates_category_label = " :: Today";
	$dates_category_label = " :: Dates";
	
	$tokens['hebrewcalendar']['hebrewcalendar.today___hebrew_trans'] =  'Today (Hebrew transliterated)'.$dates_category_label;
	$tokens['hebrewcalendar']['hebrewcalendar.today___hebrew'] = 'Today (Hebrew)'.$dates_category_label ;
		
		// Next 2 are now available as read-only custom fields, which means CiviCRM core makes them available as tokens. 
	//	$tokens['dates']['dates.birth_date_hebrew_trans'] = 'Birth Date (Hebrew - transliterated)'.$dates_category_label ;
	//	$tokens['dates']['dates.birth_date_hebrew'] = 'Birth Date (Hebrew)'.$dates_category_label ;
		
		
		
	$token_category_label  = " :: Yahrzeits for this Mourner ";
		 
		$tokens['yahrzeit'] = array(
			//	'yahrzeit.all' => "All Yahrzeits".$token_category_label, 
		);
		
		// 'communitynews.upcomingevents___day_7' =>   'Events in the next 7 days :: Events', 
		
	
		
		$partial_tokens_for_each_date = array(
			'deceased_name' => 'Name of Deceased',
			'english_date' => 'English Yarzeit Date (evening)',
			'morning_format_english' => 'English Yahrzeit Date (morning)',
			'hebrew_date' => 'Hebrew Yahrzeit Date',	
			'hebrew_date_hebrew' =>  'Hebrew Yahrzeit Date (Hebrew letters)',	
			'dec_death_english_date' => 'English Date of Death',
			'dec_death_hebrew_date' => 'Hebrew Date of Death',
			'relationship_name'  => 'Relationship of Deceased to Mourner',
			'erev_shabbat_before' => 'Erev (evening) of the Shabbat before yahrzeit',
			'parashat_shabbat_before' => 'Parashat of the Shabbat before yahrzeit',
			'shabbat_morning_before' => 'Morning of the Shabbat before yahrzeit',
			'erev_shabbat_after' => 'Erev (evening) of the Shabbat after yahrzeit',
			'parashat_shabbat_after' => 'Parashat of the Shabbat after yahrzeit',
			'shabbat_morning_after' => 'Morning of the Shabbat after yahrzeit',
				//'yahrzeit.erev_shabbat_before' => 'Yahrzeit: Erev (evening) of the Shabbat Before',
				//'yahrzeit.shabbat_morning_before' => 'Yahrzeit: Morning of the Shabbat Before',
				//'yahrzeit.erev_shabbat_after' => 'Yahrzeit: Erev (evening) of the Shabbat After',
				//'yahrzeit.shabbat_morning_after' => 'Yahrzeit: Morning of the Shabbat After',
		);
		
		$partial_date_choices = array(		
				'day_7' => 'in exactly 7 days',
				'day_10' => 'in exactly 10 days',
				'day_14' => 'in exactly 14 days',
				'day_30' => 'in exactly 30 days',	
				'month_cur' => 'during current month',
				'month_next' => 'during next month',
				'month_2' => '2 months from now',
				'month_3' => '3 months from now',
				'month_4' => '4 months from now',
				'week_cur' => 'during current week',
				'week_next' => 'during next week',
				'week_2'  => '2 weeks from now',
				'week_3'  => '3 weeks from now',
				'week_4'  => '4 weeks from now',
		);

		
		foreach( $partial_date_choices as $cur_date_choice => $date_label){
	    	foreach( $partial_tokens_for_each_date as $cur_partial_token => $partial_label ){    	
	    		
	    		$tmp_full_token = "yahrzeit.".$cur_partial_token."___".$cur_date_choice; 
	    		$tmp_full_label = $partial_label." ".$date_label." ".$token_category_label ;
	    		$tokens['yahrzeit'][$tmp_full_token] = $tmp_full_label;	
	    		
	    	}	    	
	    	
	    }


}
 
function hebrewcalendarhelper_civicrm_tokenValues( &$values, &$contactIDs, $job = null, $tokens = array(), $context = null) {
	
	if(!empty($tokens['hebrewcalendar'])){
		require_once 'utils/HebrewCalendar.php';
		$hebrew_format = 'dd MM yy';

		$tmpHebCal = new HebrewCalendar();
		$today_hebrew = $tmpHebCal->util_convert_today2hebrew_date($hebrew_format );

		$tmp_hebrew_format = 'hebrew';
		$today_hebrew_hebrew = $tmpHebCal->util_convert_today2hebrew_date($tmp_hebrew_format );

		foreach ( $contactIDs as $cid ) {
			$values[$cid]['hebrewcalendar.today___hebrew_trans'] = $today_hebrew;
			$values[$cid]['hebrewcalendar.today___hebrew'] = $today_hebrew_hebrew;
		}

	}

	
	if(!empty($tokens['yahrzeit']) ){
		 
		
		// Since we are going to fill in all possible yahrzeit tokens, even if the user did not selet them
		// we need to make sure that the unused tokens are not empty strings.
		// All the token data is in the  database table, and were do not want to query it for each token.
		// We will query it once for all yahrzeit tokens. 
		
		
		// Hebrew dates are generally written in English letters (ie transliterated), unless otherwise noted.
		$token_yahrzeits_all = 'yahrzeit.all'; // all yahrzeits for this mourner (entire year)
		$token_yah_dec_name  = 'yahrzeit.deceased_name' ;
		$token_yah_english_date = 'yahrzeit.english_date'; // English date of yahrzeit (evening when a candle should be lit) 
		$token_yah_hebrew_date = 'yahrzeit.hebrew_date' ; // yahrzeit Hebrew date, example: 23 Elul 5776
		
		$token_yah_hebrew_date_hebrew = 'yahrzeit.hebrew_date_hebrew' ;  // yahrzeit Hebrew date, written in Hebrew letters.
		$token_yah_dec_death_english_date = 'yahrzeit.dec_death_english_date'; // English date of death, example: August 15, 1980
		$token_yah_dec_death_hebrew_date = 'yahrzeit.dec_death_hebrew_date';  // Hebrew date of death, example: 23 Elul 5765
		$token_yah_relationship_name = 'yahrzeit.relationship_name';
		
		$token_yah_erev_shabbat_before = 'yahrzeit.erev_shabbat_before';
		$token_yah_shabbat_morning_before = 'yahrzeit.shabbat_morning_before';
		$token_yah_erev_shabbat_after = 'yahrzeit.erev_shabbat_after' ;
		$token_yah_shabbat_morning_after = 'yahrzeit.shabbat_morning_after' ;
		
		$token_yah_shabbat_parashat_before = 'yahrzeit.parashat_shabbat_before';
		$token_yah_shabbat_parashat_after = 'yahrzeit.parashat_shabbat_after';
		
		
		$token_yah_english_date_morning = 'yahrzeit.morning_format_english'; // English date of yahrzeit (morning after candle is lit)
		
	

		
		// CiviCRM is buggy here, if token is being used in CiviMail, we need to use the key
		// as the token. Otherwise ( PDF Letter, one-off email, etc) we
		// need to use the value.
		while( $cur_token_raw = current( $tokens['yahrzeit'] )){
			$tmp_key = key($tokens['yahrzeit']);
		
			$cur_token = '';
			if(  is_numeric( $tmp_key)){
				$cur_token = $cur_token_raw;
			}else{
				// Its being used by CiviMail.
				$cur_token = $tmp_key;
			}
		
			$token_to_fill = 'yahrzeit.'.$cur_token;
			//print "<br><br>Token to fill: ".$token_to_fill."<br>";
		
			$token_as_array = explode("___",  $cur_token );
		
			
		
			$partial_token =  $token_as_array[0];
			if( isset( $token_as_array[1] ) && strlen($token_as_array[1]) > 0 ){
				$token_date_portion =  $token_as_array[1];
			}
				
			if( $partial_token ==  'deceased_name' ){
				$token_yah_dec_name = $token_to_fill;
			}else if($partial_token == 'english_date'){
				$token_yah_english_date =  $token_to_fill;
			}else if($partial_token == 'hebrew_date'){
				$token_yah_hebrew_date = $token_to_fill;
			}else if($partial_token == 'hebrew_date_hebrew'){
				$token_yah_hebrew_date_hebrew = $token_to_fill;
			}else if( $partial_token == 'dec_death_english_date'){
				$token_yah_dec_death_english_date = $token_to_fill;
			}else if( $partial_token == 'dec_death_hebrew_date'){
				$token_yah_dec_death_hebrew_date = $token_to_fill;
			}else if( $partial_token == 'relationship_name'){
				$token_yah_relationship_name = $token_to_fill;
			}else if( $partial_token == 'erev_shabbat_before'){
				$token_yah_erev_shabbat_before = $token_to_fill;
			}else if( $partial_token == 'shabbat_morning_before'){
				$token_yah_shabbat_morning_before = $token_to_fill;
			}else if( $partial_token == 'erev_shabbat_after'){
				$token_yah_erev_shabbat_after = $token_to_fill;
			}else if( $partial_token == 'shabbat_morning_after'){
				$token_yah_shabbat_morning_after = $token_to_fill;
			}else if( $partial_token == 'morning_format_english'){
				$token_yah_english_date_morning = $token_to_fill;
			}else if($partial_token == 'parashat_shabbat_before'){
				$token_yah_shabbat_parashat_before = $token_to_fill;
			}else if($partial_token == 'parashat_shabbat_after'){
				$token_yah_shabbat_parashat_after = $token_to_fill;	
			}
			
			next($tokens['yahrzeit']);
		}
		
		
		
		require_once('utils/HebrewCalendar.php');
		$tmpHebCal = new HebrewCalendar();
		$tmpHebCal->process_yahrzeit_tokens( $values, $contactIDs , 
				$token_yahrzeits_all, 
				$token_yah_dec_name, $token_yah_english_date,
				$token_yah_hebrew_date, 
				$token_yah_dec_death_english_date, 
				$token_yah_dec_death_hebrew_date ,  
				$token_yah_relationship_name,
				$token_yah_erev_shabbat_before ,
				$token_yah_shabbat_morning_before ,
				$token_yah_erev_shabbat_after ,
				$token_yah_shabbat_morning_after,
				$token_yah_english_date_morning, 
				$token_yah_shabbat_parashat_before,
				$token_yah_shabbat_parashat_after,
				$token_date_portion,
				$token_yah_hebrew_date_hebrew) ;
		

	}
	 
}
 
function XXXcontact_summary_determine_middle_content( &$hebrew_data ){

	$heb_date_of_birth =  $hebrew_data['hebrew_date_of_birth'];
	if(isset($hebrew_data['bar_bat_mitzvah_label'])){
		$bar_bat_mitzvah_label = $hebrew_data['bar_bat_mitzvah_label'] ;
	}else{
		$bar_bat_mitzvah_label = "Bar/Bat Mitzvah";
	}
	if(isset($hebrew_data['earliest_bar_bat_mitzvah_date'])){
		$earliest_bar_bat_mitzvah_date = $hebrew_data['earliest_bar_bat_mitzvah_date'];
	}else{
		$earliest_bar_bat_mitzvah_date = "";
	}
	
	
	/*
	if(isset($hebrew_data['is_deceased'])){   
		$is_deceased = $hebrew_data['is_deceased'];
	}
	
	if(isset($hebrew_data['hebrew_date_of_death']) ){ 
		$hebrew_date_of_death = $hebrew_data['hebrew_date_of_death'];
		$hebrew_date_of_death_html   = " <tr> <td class='label'>Hebrew Date of Death</td> <td class='html-adjust'>$hebrew_date_of_death</td> </tr> \n  ";
	
	}
	
	
	if( isset($hebrew_data['yahrzeit_date_observe_hebrew'])  && isset($hebrew_data['yahrzeit_date_observe_english'])  ){   
		$yahrzeit_date_observe_hebrew = $hebrew_data['yahrzeit_date_observe_hebrew'];
		$yahrzeit_date_observe_english = $hebrew_data['yahrzeit_date_observe_english'];
		
		$next_yehrzeit_date_html     = " <tr> <td class='label'>Next Hebrew Yahrzeit</td> <td class='html-adjust'>$yahrzeit_date_observe_hebrew</td> </tr> \n
		<tr> <td class='label'>Next English Yahrzeit</td> <td class='html-adjust'>$yahrzeit_date_observe_english</td> </tr> \n ";
	}
	*/
	

	$heb_date_of_birth_html = " <tr> <td class='label'>Hebrew Date of Birth</td> <td class='html-adjust'> $heb_date_of_birth </td>  </tr> \n  ";
	$earliest_bar_bat_date_html  = " <tr> <td class='label'>Earliest Possible $bar_bat_mitzvah_label Date</td><td class='html-adjust'>$earliest_bar_bat_mitzvah_date </td> </tr> \n";
	
	
/*
	if($is_deceased){
		$middle_html = $heb_date_of_birth_html.$hebrew_date_of_death_html.$next_yehrzeit_date_html ;
	}else{
		$middle_html = $heb_date_of_birth_html.$earliest_bar_bat_date_html;
	}
	*/
	
	$middle_html = $heb_date_of_birth_html.$earliest_bar_bat_date_html;
	
	return $middle_html;

}


/*
function XXXcontact_summary_determine_beginning_content(){

	$html_rtn = "   <div id='customFields'>
		                    <div class='contact_panel'>
		                        <div class='contactCardLeft'>
		                                                        <div class='customFieldGroup ui-corner-all'>
		                <table>

		                  <tr>
		                    <td colspan='2' class='grouplabel'>Hebrew Calendar Demographics</td>
		                  </tr> \n
		            ";

	return $html_rtn;


}
*/


/*

function XXXcontact_summary_determine_ending_content(){

	$html_rtn = " </table>
		            </div>
		                        </div><!--contactCardLeft-->

		                        <div class='contactCardRight'>
		                                                                </div>

		                        <div class='clear'></div>
		                    </div>
		                </div>  \n
		   ";

	return $html_rtn;


}

*/


/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function hebrewcalendarhelper_civicrm_config(&$config) {
  _hebrewcalendarhelper_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function hebrewcalendarhelper_civicrm_xmlMenu(&$files) {
  _hebrewcalendarhelper_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function hebrewcalendarhelper_civicrm_install() {
  _hebrewcalendarhelper_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function hebrewcalendarhelper_civicrm_uninstall() {
  _hebrewcalendarhelper_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function hebrewcalendarhelper_civicrm_enable() {
  _hebrewcalendarhelper_civix_civicrm_enable();
  
  require_once( 'utils/HebrewCalendar.php');
  $tmp_cal = new HebrewCalendar();
  $tmp_cal->createExtensionConfigs();  // this function does not do anything if custom data/configs already exist. 
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function hebrewcalendarhelper_civicrm_disable() {
  _hebrewcalendarhelper_civix_civicrm_disable();
  
  
  // This only removes temp stuff, ie stuff that is safely re-created when the extension is re-enabled.
  // Things that the organization is using to store their data is NOT removed.
  // ie extension-created CiviCRM custom data sets, relationship types, etc are left in place.
  require_once( 'utils/HebrewCalendar.php');
  $tmp_cal = new HebrewCalendar();
  $tmp_cal->removeExtensionConfigs();  
  
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function hebrewcalendarhelper_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _hebrewcalendarhelper_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function hebrewcalendarhelper_civicrm_managed(&$entities) {
  _hebrewcalendarhelper_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function hebrewcalendarhelper_civicrm_caseTypes(&$caseTypes) {
  _hebrewcalendarhelper_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function hebrewcalendarhelper_civicrm_angularModules(&$angularModules) {
_hebrewcalendarhelper_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function hebrewcalendarhelper_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _hebrewcalendarhelper_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Functions below this ship commented out. Uncomment as required.
 *

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function hebrewcalendarhelper_civicrm_preProcess($formName, &$form) {

}

*/
