
{*  If you want a different layout, clone and customize this file and point to new file using
   templateFile() function.*}
<div class="crm-block crm-form-block crm-contact-custom-search-form-block">
<div class="crm-accordion-wrapper crm-custom_search_form-accordion {if $rows}collapsed{/if}">
    <div class="crm-accordion-header crm-master-accordion-header">
      {ts}Edit Yahrzeit Search Criteria{/ts}
    </div><!-- /.crm-accordion-header -->
    <div class="crm-accordion-body">
        <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
       
       
       <fieldset> 
     <div >
      <div style=" display: inline-block;">  <label>{$form.date_range_ui.label}
      </label>{$form.date_range_ui.html} {$form.date_to_filter.html}</div>
       </div>
       <br>
       
       <div id='relative_from_today_interval_help'> Example: if you choose '7' and 'Day', then you will see yahrzeits that occur EXACTLY on the 7th day after today.</div>
       
       <div id='relative_from_today_interval' >
           <label>
            Interval from today:</label>
             {$form.relative_time_interval_count.html}
           
            {$form.relative_time_interval_type.html}
       </div>
       
       
       <div id="relative_from_today_common">
             {$form.relative_time.html}
       </div>
       
       <div id="specific_dates"> <label>Specific Dates: (Start/End)</label>
        {include file="CRM/common/jcalendar.tpl" elementName='start_date'}{include file="CRM/common/jcalendar.tpl" elementName='end_date'}</div>
        
       <div id="hebrew_month_year"> <label>Hebrew Calendar: (Year/Month)</label>
            {$form.hebrew_year_choice.html} {$form.hebrew_month_choice.html}
            </div>
        </fieldset>  
          <br><br>
       <hr>
       {*
       <div><label>{$form.date_to_filter.label}
      </label>{$form.date_to_filter.html}</div>
       *}
         
        {*
        <!--
        <table border=0 cellspacing=0 cellpadding=0> 
        <tr class="crm-contact-custom-search-form-row-relative_time">
            <td><label>{$form.date_range_ui.label}</label></td><td>{$form.date_range_ui.html}</td>
            <td>{$form.date_to_filter.html}</td>
            </tr>
            
        <tr id='relative_from_today_interval_help' class="relative_time_interval">
            <td colspan=3>Example: if you choose '7' and 'Day', then you will see yahrzeits that occur EXACTLY on the 7th day after today. </td>
        </tr>
        
        <tr id='relative_from_today_interval' class="relative_time_interval">
            <td><label>
            Interval from today:</label></td>
            <td> {$form.relative_time_interval_count.html}</td>
           <td>        
            {$form.relative_time_interval_type.html}
            </td></tr>
            
        <tr id="relative_from_today_common" class="relative_time_common">
        <td><label>{$form.relative_time.label}</label></td>
            <td> {$form.relative_time.html} </td>
            <td></td>
        </tr>    
           
        <tr id="specific_dates" class="specific_dates">
        <td><label>Specific Dates: (Start/End)</label></td>
        <td>{include file="CRM/common/jcalendar.tpl" elementName='start_date'}</td><td>{include file="CRM/common/jcalendar.tpl" elementName='end_date'}</td></tr>    
            
            
            
            <tr id="hebrew_month_year" class="relative_time_hebrewyearmonth">
                <td><label>Hebrew Calendar: (Year/Month)</label></td>
            <td> {$form.hebrew_year_choice.html} </td><td>{$form.hebrew_month_choice.html} </td></tr>
            
            </table>
            -->
      *}
      
       {literal}
     <script>
         function hide_all_date_choices(){
            
            // 
            // relative_time_interval
           // relative_time_common
             // specific_dates
             // hebrewyearmonth
           // var rows2hide = [];
            //rows2hide[0] = document.getElementById("relative_time_interval_help");
             // visibility:hidden
               var trid = document.getElementById("relative_from_today_interval_help");
             if (trid != null) { trid.style.display = 'none'; }
             
             var trid = document.getElementById("relative_from_today_interval");
             if (trid != null) { trid.style.display = 'none'; }
             
               var trid = document.getElementById("relative_from_today_common");
             if (trid != null) { trid.style.display = 'none'; }
             
               var trid = document.getElementById("specific_dates");
             if (trid != null) { trid.style.display = 'none'; }
              
             var trid = document.getElementById("hebrew_month_year");
             if (trid != null) { trid.style.display = 'none'; }
           
         }
         
         function show_datechoice_ui(){
              hide_all_date_choices();
             //  now show just the item the user wants.
             
             var e = document.getElementById("date_range_ui");
             var value = e.options[e.selectedIndex].value;
             
             
              var trid = document.getElementById(value);
             if (trid != null) { 
                 trid.style.display = 'block'; }
                 
             var trid = document.getElementById(value.concat('_help'));
             if (trid != null) { 
                 trid.style.display = 'block'; }
             
         }
         
         //hide_all_date_choices();
         show_datechoice_ui();
         
         var date_range_ui= document.getElementById("date_range_ui");
         date_range_ui.onchange = function(){ show_datechoice_ui()};
         
     </script>  
     {/literal}      
            
        <table class="form-layout-compressed">
            {* Loop through all defined search criteria fields (defined in the buildForm() function). *}
            
           
            
            {foreach from=$elements item=element}
              
              {if $form.$element.name neq 'relative_time_interval_type' and $form.$element.name neq 'relative_time_interval_count'
              and $form.$element.name neq 'start_date' 
              and $form.$element.name neq 'end_date'
              and $form.$element.name neq 'hebrew_month_choice'
              and $form.$element.name neq 'hebrew_year_choice'
              and $form.$element.name neq 'date_range_ui'
              and $form.$element.name neq 'date_to_filter' 
              and $form.$element.name neq 'relative_time'}
            
            
                <tr class="crm-contact-custom-search-form-row-{$element}">
                    <td class="label">{$form.$element.label}</td>
                    {if $element|strstr:'_date'}
                        <td>{include file="CRM/common/jcalendar.tpl" elementName=$element}</td>
                    {else}
                        <td>{$form.$element.html}</td>
                    {/if}
                </tr>
                {/if}
            {/foreach}
        </table>
        <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
    </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->
</div><!-- /.crm-form-block -->

{if $rowsEmpty || $rows}
<div class="crm-content-block">
{if $rowsEmpty}
    {include file="CRM/Contact/Form/Search/Custom/EmptyResults.tpl"}
{/if}

{if $summary}
    {$summary.summary}: {$summary.total}
{/if}

{if $rows}
  <div class="crm-results-block">
    {* Search request has returned 1 or more matching rows. Display results and collapse the search criteria fieldset. *}
        {* This section handles form elements for action task select and submit *}
       <div class="crm-search-tasks">
        {include file="CRM/Contact/Form/Search/ResultTasks.tpl"}
    </div>
        {* This section displays the rows along and includes the paging controls *}
      <div class="crm-search-results">

        {include file="CRM/common/pager.tpl" location="top"}

        {* Include alpha pager if defined. *}
        {if $atoZ}
            {include file="CRM/common/pagerAToZ.tpl"}
        {/if}

        {strip}
        <table class="selector row-highlight" summary="{ts}Search results listings.{/ts}">
            <thead class="sticky">
                <tr>
                <th scope="col" title="Select All Rows">{$form.toggleSelect.html}</th>
                {foreach from=$columnHeaders item=header}
                    <th scope="col">
                        {if $header.sort}
                            {assign var='key' value=$header.sort}
                            {$sort->_response.$key.link}
                        {else}
                            {$header.name}
                        {/if}
                    </th>
                {/foreach}
                <th>&nbsp;</th>
                </tr>
            </thead>

            {counter start=0 skip=1 print=false}
            {foreach from=$rows item=row}
                <tr id='rowid{$row.contact_id}' class="{cycle values="odd-row,even-row"}">
                    {assign var=cbName value=$row.checkbox}
                    <td>{$form.$cbName.html}</td>
                    {foreach from=$columnHeaders item=header}
                        {assign var=fName value=$header.sort}
                        {if $fName eq 'sort_name'}
                            <td><a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$row.contact_id`&key=`$qfKey`&context=custom"}">{$row.sort_name}</a></td>
                        
                        {else}
                            <td>{$row.$fName}</td>
                        {/if}
                    {/foreach}
                    <td>{$row.action}</td>
                </tr>
            {/foreach}
        </table>
        {/strip}

        {include file="CRM/common/pager.tpl" location="bottom"}

        </p>
    {* END Actions/Results section *}
    </div>
    </div>
{/if}



</div>
{/if}
