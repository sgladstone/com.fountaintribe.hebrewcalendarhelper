<?php

use CRM_Hebrewcalendarhelper_ExtensionUtil as E;

return [
  'hebrewcalendarhelper_bat_mitzvah_age' => [
    'name' => 'hebrewcalendarhelper_bat_mitzvah_age',
    'type' => 'Int',
    'html_type' => 'text',
    'html_attributes' => [
      'size' => 4,
    ],
    'default' => 12,
    'add' => '1.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => '',
    'title' =>  E::ts('Bat Mitzvah Age'),
    'help_text' => '',
    'settings_pages' => [
      'hebrewcalendar' => [
        'weight' => 1,
      ]
    ],
  ],
];
