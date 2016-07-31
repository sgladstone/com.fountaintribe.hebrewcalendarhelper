<?php
// This file declares a managed database record of type "Job".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return array (
  0 => 
  array (
    'name' => 'Cron:AllHebrewDates.Calculate',
    'entity' => 'Job',
    'params' => 
    array (
      'version' => 3,
      'name' => 'Call AllHebrewDates.Calculate API',
      'description' => 'Recalculate all yahrzeits, Hebrew birthdays, and other observances tied to the Hebrew calendar.',
      'run_frequency' => 'Hourly',
      'api_entity' => 'AllHebrewDates',
      'api_action' => 'Calculate',
      'parameters' => '',
    ),
  ),
);