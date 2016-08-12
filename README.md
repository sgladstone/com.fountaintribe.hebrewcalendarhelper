# Hebrew Calendar Helper CiviCRM extension

Has a variety of functionality related to the Hebrew calendar. This includes mail merge tokens, demographic information, Hebrew date of birth, yahrzeits, APIs and more.
  
# Features added by this extension

User documentation is at: https://github.com/sgladstone/com.fountaintribe.hebrewcalendarhelper/wiki

## Custom Tokens
- aa
- bb
- cc

## Custom Searches

"Yahrzeit Search"

# CiviCRM Configurations created by this extension

## Custom Data Sets
- "Extended Date Information"
- "Yahrzeit Preferences"
- "Yahrzeit Dates (Calculated Automatically)"
- "Hebrew Birth Dates (Calculated Automatically)"

## Custom Contact Types

"Deceased" based on Individual

## Custom Relationship Types

"Yahrzeit observed by"  --- "Yahrzeit observed in memory of"

## CiviCRM Scheduled Jobs

"Call AllHebrewDates.Calculate API"   --- Recalculates all yahrzeits, Hebrew birthdays, and other observances tied to the Hebrew calendar.

########################################
## WARNING
########################################

DO NOT remove or move any of the custom fields, or this extension will NOT WORK. Changing the labels for the custom fields, contact types, and relationship types has NOT been tested.  In any case changing labels is NOT RECOMMENED as your environment will no longer match the user documentation/videos. 
  
