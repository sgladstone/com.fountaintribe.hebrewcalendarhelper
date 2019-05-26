<?php

use CRM_Hebrewcalendarhelper_ExtensionUtil as E;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * FIXME - Add test description.
 *
 * Tips:
 *  - With HookInterface, you may implement CiviCRM hooks directly in the test class.
 *    Simply create corresponding functions (e.g. "hook_civicrm_post(...)" or similar).
 *  - With TransactionalInterface, any data changes made by setUp() or test****() functions will
 *    rollback automatically -- as long as you don't manipulate schema or truncate tables.
 *    If this test needs to manipulate schema or truncate tables, then either:
 *       a. Do all that using setupHeadless() and Civi\Test.
 *       b. Disable TransactionalInterface, and handle all setup/teardown yourself.
 *
 * @group headless
 */
class CRM_Hebrewcalendarhelper_HebrewcalendarhelperTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {
  use \Civi\Test\Api3TestTrait;

  protected $contacts = [];
  protected $tmp = [];

  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://github.com/civicrm/org.civicrm.testapalooza/blob/master/civi-test.md
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function setUp() {
    parent::setUp();

    // This field must be set for the calculations to work.
    require_once 'utils/HebrewCalendar.php';

    $bday_sunset_field_id = civicrm_api3('CustomField', 'getsingle', [
      'custom_group_id' => HebrewCalendar::EXTENDED_DATE_CUSTOM_FIELD_GROUP_NAME,
      'name' => HebrewCalendar::EXTENDED_DATE_CUSTOM_FIELD_BIRTH_NAME,
      'return' => ['id'],
    ])['id'];

    $this->contacts['c1'] = $this->callAPISuccess('Contact', 'create', ['first_name' => 'Jane', 'last_doe' => 'Doe', 'contact_type' => 'Individual', 'birth_date' => '2007-05-17', 'gender_id' => 1, 'custom_' . $bday_sunset_field_id => 1])['id'];
    $this->contacts['c2'] = $this->callAPISuccess('Contact', 'create', ['first_name' => 'John', 'last_doe' => 'Doe', 'contact_type' => 'Individual', 'birth_date' => '2007-05-17', 'gender_id' => 2, 'custom_' . $bday_sunset_field_id => 1])['id'];
  }

  public function tearDown() {
    parent::tearDown();

    foreach ($this->contacts as $cid) {
      $this->callAPISuccess('Contact', 'delete', ['id' => $cid]);
    }
  }

  /**
   * Test Bat Mitzvah calculation (bday + 12 years).
   */
  public function testBatMitzvah() {
    $bm_date = CRM_Hebrewcalendarhelper_BAO_Contact::getBarBatMitzvahDate($this->contacts['c1']);
    $this->assertEquals('2019-06-02 00:00:00', $bm_date);
  }

  /**
   * Test Bar Mitzvah calculation (bday + 13 years).
   */
  public function testBarMitzvah() {
    $bm_date = CRM_Hebrewcalendarhelper_BAO_Contact::getBarBatMitzvahDate($this->contacts['c2']);
    $this->assertEquals('2020-05-22 00:00:00', $bm_date);
  }

  /**
   * Example: Test that we're using a fake CMS.
   */
  public function testWellFormedUF() {
    $this->assertEquals('UnitTests', CIVICRM_UF);
  }

}
