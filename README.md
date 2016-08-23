# Hebrew Calendar Helper CiviCRM extension

Has a variety of functionality related to the Hebrew calendar. This includes mail merge tokens, demographic information, Hebrew date of birth, yahrzeits, APIs and more.
  
# Features added by this extension

Full documentation is at: https://github.com/sgladstone/com.fountaintribe.hebrewcalendarhelper/wiki

## Custom Tokens

### Additional token sections "Today"  and "Yahrzeits for this Mourner"

 Token details documented at: https://github.com/sgladstone/com.fountaintribe.hebrewcalendarhelper/wiki

## Custom Searches

"Yahrzeit Search"


---

# CiviCRM Configurations created by this extension

## Custom Data Sets
- "Extended Date Information" Used to indicate if an individual was born/died before sunset. This can be edited when editing any individual. 
- "Yahrzeit Preferences" Used to indicate track mourner preferences for various yahrzeits observed. This can be edited when editing a yahrzeit relationship.
- "Yahrzeit Dates (Calculated Automatically)" - Nothing in this area is editable.
- "Hebrew Birth Dates (Calculated Automatically)" - Nothing in this area is editable.
- "Memorial Plaque Info" used for track information, such as if a deceased person has a memorial plaque, the location of the plaque, etc. This can be edited when editing any deceased individual. This area is also used to integrate with the electronic yahrzeit system from Yahrzeitronix (sales@yahrzeitronix.com) 
- "Religious"  Used to track religious information about individuals, such as their Hebrew name, Hebrew names of parents, etc. This can be edited when edited any individual.

## Custom Contact Types

"Deceased" based on Individual. This is set automatically. There is no need for the user to edit this.

## Custom Relationship Types

"Yahrzeit observed by"  --- "Yahrzeit observed in memory of"

 **Its strongly recommended** to create additional relationships types manually. See details at: https://github.com/sgladstone/com.fountaintribe.hebrewcalendarhelper/wiki

## CiviCRM Scheduled Jobs

"Call AllHebrewDates.Calculate API"   --- Recalculates all yahrzeits, Hebrew birthdays, and other observances tied to the Hebrew calendar.  This should run at least daily. It can run more frequently as preferred. 


---

## MySQL tables

"civicrm_fountaintribe_yahrzeits_temp"  - This table gets frequently truncated and repopulated. 

# What happens when this extension is disabled?

What is removed:
 - The MySQL table "civicrm_fountaintribe_yahrzeits_temp"
 - The API used by the scheduled job "Call AllHebrewDates.Calculate API"
 - The custom searches described in this README
 - The tokens described in this README
 - The APIs described in this README
 
What is left:
 - The CiviCRM custom field sets, custom fields, and other CiviCRM configurations described in this README. 
  
# What happens when this extension is disabled, then re-enabled? Or uninstalled then re-installed?

During enablement, the extension checks for the existence of the various CiviCRM configurations described in this README. If everything already exists, then nothing is changed. If anything is missing, then it is created.

For example: 
 - The custom relationship type "Yahrzeit observed by" does not exist. Yet all the other configurations exist, such as all the custom fields already exist.
 
 - The relationship type "Yahrzeit observed by" will be created during enablement.   No other configurations are created or changed. 

 - The MySQL temp tables are always recreated. 

########################################
## WARNING
 - DO NOT remove or move any of the custom fields, or this extension will NOT WORK. Changing the labels for the custom fields, contact types, and relationship types has NOT been tested.  
 - Changing labels is NOT RECOMMENDED as your environment will no longer match the user documentation/videos. 
  
