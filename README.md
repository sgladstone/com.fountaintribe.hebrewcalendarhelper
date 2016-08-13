# Hebrew Calendar Helper CiviCRM extension

Has a variety of functionality related to the Hebrew calendar. This includes mail merge tokens, demographic information, Hebrew date of birth, yahrzeits, APIs and more.
  
# Features added by this extension

User documentation is at: https://github.com/sgladstone/com.fountaintribe.hebrewcalendarhelper/wiki

## Custom Tokens

### In the token section "Today"

- "Today (Hebrew transliterated)"
- "Today (Hebrew)"
 
### In the token section "Yahrzeits for this Mourner"

 - "Name of Deceased in exactly X days"
 - "English Yarzeit Date (evening) in exactly X days"
 - "English Yahrzeit Date (morning) in exactly X days"
 - "Hebrew Yahrzeit Date in exactly X days"
 - "English Date of Death in exactly X days"
 - "Hebrew Date of Death in exactly X days"
 - "Relationship of Deceased to Mourner in exactly X days"
 
 If you are planning to use these tokens to create a PDF letter, you need to install "wkhtmltopdf" and configure CiviCRM to use it. If this is not done,
 tokens that use Hebrew letters will show as ????? in the PDF document. 
 

## Custom Searches

"Yahrzeit Search"


---

# CiviCRM Configurations created by this extension

## Custom Data Sets
- "Extended Date Information"
- "Yahrzeit Preferences"
- "Yahrzeit Dates (Calculated Automatically)"
- "Hebrew Birth Dates (Calculated Automatically)"
- "Memorial Plaque Info" used for track information, such as if a deceased person has a memorial plaque, the location of the plaque, etc. This area is also used to integrate with the electronic yahrzeit system from Yahrzeitronix (sales@yahrzeitronix.com) 
- "Religious"  Used to track religious information about individuals, such as their Hebrew name, tribe, etc.

## Custom Contact Types

"Deceased" based on Individual

## Custom Relationship Types

"Yahrzeit observed by"  --- "Yahrzeit observed in memory of"

## CiviCRM Scheduled Jobs

"Call AllHebrewDates.Calculate API"   --- Recalculates all yahrzeits, Hebrew birthdays, and other observances tied to the Hebrew calendar.

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

- a
- b  

########################################
## WARNING
 - DO NOT remove or move any of the custom fields, or this extension will NOT WORK. Changing the labels for the custom fields, contact types, and relationship types has NOT been tested.  
 - Changing labels is NOT RECOMMENED as your environment will no longer match the user documentation/videos. 
  
