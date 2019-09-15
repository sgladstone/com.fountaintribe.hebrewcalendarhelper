{literal}
  <script language='javascript'>

function set_female_bmitzvah(){
  var e = document.getElementById("female_bmitzvah");
  var age_str = e.options[e.selectedIndex].value;

  CRM.api3('Setting', 'create', { "hebrewcalendarhelper_bmitzvah_age_female": age_str}).then(function(result) {
//  window.alert("API result: " + JSON.stringify(result, null , 4) );
  var tmp_age = result.values[1]['hebrewcalendarhelper_bmitzvah_age_female'];
  document.getElementById("bmitzvah_age_female").innerHTML = tmp_age;


  }, function(error) {
  window.alert("ERROR. Could not change setting. API ERROR: " +  error);
 });

}

</script>
{/literal}

<form>
<p>Age for earliest possible bat mitzvah date - only impacts female contact records</p>
<strong>Setting for bat mitzvah age: <span id='bmitzvah_age_female'>
{crmAPI var='result' entity='Setting' action='get' return="hebrewcalendarhelper_bmitzvah_age_female"}
{foreach from=$result.values item=setting}
  {$setting.hebrewcalendarhelper_bmitzvah_age_female}
{/foreach}</span></strong>
<br><br>
<select id='female_bmitzvah' name='female_bmitzvah' onChange='set_female_bmitzvah()'>
<option value="">-- select different age --</option>
<option value="12">12 years old</option>
<option value="13">13 years old</option>
</select>
<br>
<hr><br><br>
<p><strong>Default age: 13 years old</strong>.This is used for remaining records, including records where gender is empty, or not female. The default is also used when this setting has never been set.</p>

<h2>Extra Notes about B'nai Mitzvah</h2>
<p>In some organizations all individuals must wait until 13 years old. The age does not depend on gender.</p>

<p>While in other organizations, girls can become a bat mitzvah as early as age 12.</p>

<p> The age of the individual is calculated according to the Hebrew calendar. This requires their English date of birth and "birth before/after sunset" fields to be filled in.<p>
