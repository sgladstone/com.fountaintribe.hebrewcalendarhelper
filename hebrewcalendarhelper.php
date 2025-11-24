<?php

require_once 'hebrewcalendarhelper.civix.php';

/**
 * Implements hook_civicrm_post().
 */
function hebrewcalendarhelper_civicrm_post($op, $objectName, $objectId, &$objectRef) {
  // if an individual is being created or edited, rebuild yahrzeit data, hebrew birthday info.
  if ($objectName == 'Individual' && ($op == 'create' || $op == 'edit' || $op == 'restore')) {
    // If there is a date of birth or date of death, then calculate Hebrew dates.
    // Note that this works with InlineEdit, as long as the section modified has relevant info.
    if (!empty($objectRef->birth_date) || !empty($objectRef->death_date)) {
      // Calculate Hebrew demographic dates, such as next yahrzeit date, next hebrew birthday date for this contact.
      civicrm_api3('AllHebrewDates', 'calculate', [
        'sequential' => 1,
        'contact_ids' => $objectId,
      ]);
    }
  }
}

/**
 * Implements hook_civicrm_tokens().
 */
function hebrewcalendarhelper_civicrm_tokens(&$tokens) {
  //$dates_category_label = " :: Today";
  $dates_category_label = " :: Dates";

  $tokens['hebrewcalendar']['hebrewcalendar.today___hebrew_trans'] = 'Today (Hebrew transliterated)'.$dates_category_label;
  $tokens['hebrewcalendar']['hebrewcalendar.today___hebrew'] = 'Today (Hebrew)'.$dates_category_label;

  // Next 2 are now available as read-only custom fields, which means CiviCRM core makes them available as tokens.
  //	$tokens['dates']['dates.birth_date_hebrew_trans'] = 'Birth Date (Hebrew - transliterated)'.$dates_category_label ;
  //	$tokens['dates']['dates.birth_date_hebrew'] = 'Birth Date (Hebrew)'.$dates_category_label ;

  $token_category_label  = " :: Yahrzeits for this Mourner ";

  $tokens['yahrzeit'] = array(
    //  'yahrzeit.all' => "All Yahrzeits".$token_category_label,
  );

  // 'communitynews.upcomingevents___day_7' =>   'Events in the next 7 days :: Events',

  $partial_tokens_for_each_date = array(
    'deceased_name' => 'Name of Deceased',
    'english_date' => 'English Yarzeit Date (evening)',
    'morning_format_english' => 'English Yahrzeit Date (morning)',
    'hebrew_date' => 'Hebrew Yahrzeit Date',
    'hebrew_date_hebrew' => 'Hebrew Yahrzeit Date (Hebrew letters)',
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

  $partial_date_choices = [
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
  ];

  foreach ($partial_date_choices as $cur_date_choice => $date_label) {
    foreach ($partial_tokens_for_each_date as $cur_partial_token => $partial_label) {
      $tmp_full_token = "yahrzeit.".$cur_partial_token."___".$cur_date_choice;
      $tmp_full_label = $partial_label." ".$date_label." ".$token_category_label;
      $tokens['yahrzeit'][$tmp_full_token] = $tmp_full_label;
    }
  }
}

/**
 * Implements hook_civicrm_tokenValues().
 */
function hebrewcalendarhelper_civicrm_tokenValues(&$values, &$contactIDs, $job = null, $tokens = array(), $context = null) {
  // When running from Scheduled Jobs, we only receive a single contactId,
  // not an array. CiviCRM also expects us to return a flat array.
  $is_scheduled_job = FALSE;

  if (!is_array($contactIDs)) {
    $contactIDs = [$contactIDs];
    $is_scheduled_job = TRUE;
  }

  if (!empty($tokens['hebrewcalendar'])) {
    require_once 'utils/HebrewCalendar.php';
    $hebrew_format = 'dd MM yy';

    $tmpHebCal = new HebrewCalendar();
    $today_hebrew = $tmpHebCal->util_convert_today2hebrew_date($hebrew_format );

    $tmp_hebrew_format = 'hebrew';
    $today_hebrew_hebrew = $tmpHebCal->util_convert_today2hebrew_date($tmp_hebrew_format );

    foreach ($contactIDs as $cid ) {
      $values[$cid]['hebrewcalendar.today___hebrew_trans'] = $today_hebrew;
      $values[$cid]['hebrewcalendar.today___hebrew'] = $today_hebrew_hebrew;
    }
  }

  if (!empty($tokens['yahrzeit'])) {
    // Since we are going to fill in all possible yahrzeit tokens, even if the user did not selet them
    // we need to make sure that the unused tokens are not empty strings.
    // All the token data is in the  database table, and were do not want to query it for each token.
    // We will query it once for all yahrzeit tokens.

    // Hebrew dates are generally written in English letters (ie transliterated), unless otherwise noted.
    $token_yahrzeits_all = 'yahrzeit.all'; // all yahrzeits for this mourner (entire year)
    $token_yah_dec_name  = 'yahrzeit.deceased_name';
    $token_yah_english_date = 'yahrzeit.english_date'; // English date of yahrzeit (evening when a candle should be lit)
    $token_yah_hebrew_date = 'yahrzeit.hebrew_date'; // yahrzeit Hebrew date, example: 23 Elul 5776

    $token_yah_hebrew_date_hebrew = 'yahrzeit.hebrew_date_hebrew';  // yahrzeit Hebrew date, written in Hebrew letters.
    $token_yah_dec_death_english_date = 'yahrzeit.dec_death_english_date'; // English date of death, example: August 15, 1980
    $token_yah_dec_death_hebrew_date = 'yahrzeit.dec_death_hebrew_date';  // Hebrew date of death, example: 23 Elul 5765
    $token_yah_relationship_name = 'yahrzeit.relationship_name';

    $token_yah_erev_shabbat_before = 'yahrzeit.erev_shabbat_before';
    $token_yah_shabbat_morning_before = 'yahrzeit.shabbat_morning_before';
    $token_yah_erev_shabbat_after = 'yahrzeit.erev_shabbat_after';
    $token_yah_shabbat_morning_after = 'yahrzeit.shabbat_morning_after';

    $token_yah_shabbat_parashat_before = 'yahrzeit.parashat_shabbat_before';
    $token_yah_shabbat_parashat_after = 'yahrzeit.parashat_shabbat_after';

    $token_yah_english_date_morning = 'yahrzeit.morning_format_english'; // English date of yahrzeit (morning after candle is lit)

    // make sure the value array has a key for each contact.
    // CiviCRM is buggy here, if token is being used in CiviMail, we need to use the key
    // as the token. Otherwise (PDF Letter, one-off email, etc) we
    // need to use the value.
    while ($cur_token_raw = current($tokens['yahrzeit'])){
      $tmp_key = key($tokens['yahrzeit']);
      $cur_token = '';

      if (is_numeric($tmp_key)) {
        $cur_token = $cur_token_raw;
      }
      else {
        // Its being used by CiviMail.
        $cur_token = $tmp_key;
      }

      $token_to_fill = 'yahrzeit.'.$cur_token;
      $token_as_array = explode("___", $cur_token);
      $partial_token = $token_as_array[0];

      if (isset($token_as_array[1]) && strlen($token_as_array[1]) > 0) {
        $token_date_portion =  $token_as_array[1];
      }

      if ($partial_token == 'deceased_name') {
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

    require_once 'utils/HebrewCalendar.php';
    $tmpHebCal = new HebrewCalendar();

    $tmpHebCal->process_yahrzeit_tokens($values, $contactIDs,
        $token_yahrzeits_all,
        $token_yah_dec_name, $token_yah_english_date,
        $token_yah_hebrew_date,
        $token_yah_dec_death_english_date,
        $token_yah_dec_death_hebrew_date,
        $token_yah_relationship_name,
        $token_yah_erev_shabbat_before,
        $token_yah_shabbat_morning_before,
        $token_yah_erev_shabbat_after,
        $token_yah_shabbat_morning_after,
        $token_yah_english_date_morning,
        $token_yah_shabbat_parashat_before,
        $token_yah_shabbat_parashat_after,
        $token_date_portion,
        $token_yah_hebrew_date_hebrew);

    // debug_helper($contactIDs, $is_scheduled_job, $values);

    if ($is_scheduled_job) {
      foreach ($contactIDs as $cid) {
        foreach ($values[$cid] as $key => $val) {
          $values[$key] = $val;
        }
      }
    }
  }
}

function debug_helper($contactIDs, $is_scheduled_job, $values) {
   $debug_contacts = '';

   if (is_array($contactIDs)) {
     $debug_contacts_str = implode(" , ",$contactIDs);
     $debug_contact_arr = $contactIDs;
   }
   else {
     $debug_contacts_str = $contactIDs;
     $debug_contact_arr = [$contactIDs];
   }

  $date = date('Ymd');

  $pdir = $_SERVER["DOCUMENT_ROOT"];
  $debug_filename = $pdir."/sites/default/files/civicrm/ConfigAndLog/yah_token_testing___".$date.".txt";
  $debug_scheduled_job = "";

  if ($is_scheduled_job){
     $debug_scheduled_job = "Scheduled job is TRUE\n";
  }

  $debug_message =  "\n------------------------------------------------------------------------------------------------------------\n";
  $debug_message = $debug_message."Yahrzeit Token contact ids: ".$debug_contacts_str."\n\nscheduled?: ".$debug_scheduled_job ;
  $debug_message = $debug_message."\n-------------------\n";

//       $myfile = fopen($pdir."/sites/default/files/civicrm/ConfigAndLog/yah_token_testing.txt", "w+") or die("Unable to open file!");
//       fwrite($myfile, "Yahrzeit token values:\n\n".$debug_values."\n\n\n\nToken contact ids: ".$debug_contacts."\n\nJob: ".$job."\n\nContext: ".$context."\n\n\n");

  file_put_contents($debug_filename, $debug_message, FILE_APPEND | LOCK_EX);
  foreach ($debug_contact_arr as $cid) {
    file_put_contents($debug_filename, "\n\ncontact id: ".$cid , FILE_APPEND | LOCK_EX);
    foreach ($values[$cid] as $key => $val) {
      $tmp = "\nkey: ".$key." ---- value: ".$val;
      file_put_contents($debug_filename, $tmp, FILE_APPEND | LOCK_EX);
    }
  }
}

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function hebrewcalendarhelper_civicrm_config(&$config) {
  _hebrewcalendarhelper_civix_civicrm_config($config);
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
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function hebrewcalendarhelper_civicrm_enable() {
  _hebrewcalendarhelper_civix_civicrm_enable();

  require_once 'utils/HebrewCalendar.php';
  $tmp_cal = new HebrewCalendar();
  $tmp_cal->createExtensionConfigs();  // this function does not do anything if custom data/configs already exist.
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function hebrewcalendarhelper_civicrm_disable() {

  // This only removes temp stuff, ie stuff that is safely re-created when the extension is re-enabled.
  // Things that the organization is using to store their data is NOT removed.
  // ie extension-created CiviCRM custom data sets, relationship types, etc are left in place.
  require_once 'utils/HebrewCalendar.php';
  $tmp_cal = new HebrewCalendar();
  $tmp_cal->removeExtensionConfigs();

}
