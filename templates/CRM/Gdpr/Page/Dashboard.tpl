<h3>Contacts who have not had any activity for {$settings.activity_period} days</h3>
<div class="crm-block crm-form-block crm-gdpr-dashboard-activities-list-form-block">
    <div>
      <table class="selector row-highlight" id="ContactSummaryListTable">
        <thead class="sticky">
        <tr>
          <th scope="col" width="60%">{ts}Activity Types{/ts}</th>
          <th scope="col">{ts}No. of Contacts{/ts}</th>
        </tr>
        </thead>
        <tbody>
        <tr>
          <td>{$gdprActTypes}
          <br />
          <span class="description"><i>{ts}(Excluding contacts who clicked through links in emails){/ts}</i></span>
          </td>
          <td><a href='{crmURL p="civicrm/gdpr/activitycontact" q="reset=1"}'>{$count}</a></td>
        </tr>
        <tr>
          <td>Click-throughs
          <br />
          <span class="description"><i>{ts}(Contacts who have not had any activity, but clicked through links in emails){/ts}</i></span>
          </td>
          <td>{$clickThroughCount}</td>
        </tr>
        </tbody>
      </table>
    </div>
</div>

{if $gsCsDetails}
<h3>Search</h3>
<div class="crm-block crm-form-block crm-gdpr-dashboard-search-form-block">
{capture assign=customSearchUrl}{crmURL p="civicrm/contact/search/custom" q="reset=1&csid=`$gsCsDetails.id`"}{/capture}
    <a href="{$customSearchUrl}">{$gsCsDetails.label}</a>
</div>
{/if}

{if call_user_func(array('CRM_Core_Permission','check'), 'administer CiviCRM')}
<h3>GDPR Settings</h3>
<div class="crm-block crm-form-block crm-gdpr-dashboard-settings-form-block">
    <div>
        <div id="help">
          Click <a href="{crmURL p="civicrm/gdpr/settings" q="reset=1"}">here</a> to update GDPR settings.
        </div>
    </div>
</div>
{/if}
