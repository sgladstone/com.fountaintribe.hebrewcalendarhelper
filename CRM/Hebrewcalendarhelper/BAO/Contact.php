<?php

/**
 * @file Helper functions for getting contact information.
 */

class CRM_Hebrewcalendarhelper_BAO_Contact {

  /**
   * Returns the (ealiest) bar/bat mitzvah date for a contact.
   *
   * @param Integer $contact_id
   * @return Date
   */
  public static function getBarBatMitzvahDate($contact_id) {
    $group_id = civicrm_api3('CustomGroup', 'getsingle', [
      'name' => HebrewCalendar::HEB_BIRTH_CUSTOM_FIELD_GROUP_NAME,
      'return' => ['id'],
    ])['id'];

    $field_id = civicrm_api3('CustomField', 'getsingle', [
      'custom_group_id' => $group_id,
      'name' => HebrewCalendar::HEB_EARLIEST_BARBAT_MITZVAH_NAME,
      'return' => ['id'],
    ])['id'];

    $result = civicrm_api3('Contact', 'getsingle', [
      'contact_id' => $contact_id,
      'return' => ['custom_' . $field_id],
    ]);

    return $result['custom_9'];
  }

  /**
   * Sets the value of the "Birthday before sunset (yes/no)" field.
   *
   * @param Integer $contact_id
   * @param Boolean $value
   */
  public static function setBirthdayBeforeSunset($contact_id, $value) {
    $field_id = civicrm_api3('CustomField', 'getsingle', [
      'custom_group_id' => HebrewCalendar::EXTENDED_DATE_CUSTOM_FIELD_GROUP_NAME,
      'name' => HebrewCalendar::EXTENDED_DATE_CUSTOM_FIELD_BIRTH_NAME,
      'return' => ['id'],
    ])['id'];

    civicrm_api3('Contact', 'create', [
      'contact_id' => $contact_id,
      'custom_' . $field_id => $value,
    ]);
  }

}
