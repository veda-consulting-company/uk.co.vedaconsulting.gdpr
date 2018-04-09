{if $crmPermissions->check('forget contact')}
<div class="action-link">
  {if $isGdprAdmin }
    {capture assign=forgetMeURL}{crmURL p="civicrm/gdpr/forgetme" q="reset=1&cid=`$contactId`"}{/capture}
  <a href="{$forgetMeURL}" class="button small-popup"><span><i class="crm-i fa-chain-broken"></i> {ts}Forget Me{/ts}</span></a>
  {/if}
  <br/><br/>
</div>
{/if}

<h3>Summary</h3>

<div class="crm-block crm-form-block">
    <div>
      <table class="selector row-highlight" id="SummaryTable">
        <thead class="sticky">
        <tr>
         <th scope="col">{ts}Subject{/ts}</th>
         <th scope="col">{ts}Status{/ts}</th>
         <th scope="col">{ts}Date{/ts}</th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$summary item=summaryItem}
        <tr>
          <td>{$summaryItem.title}</td>
          <td>{$summaryItem.details}</td>
          <td>{$summaryItem.date}</td>
        </tr>
        {/foreach}
        </tbody>
      </table>
    </div>
</div>
{if $groupSubscriptions}

<h3>Group Subscription Log</h3>

<div class="crm-block crm-form-block crm-grouo-subscription-list-form-block">
    <div>
      <table class="selector row-highlight" id="GroupSubscriptionListTable">
        <thead class="sticky">
        <tr>
         <th scope="col">{ts}Group{/ts}</th>
         <th scope="col">{ts}Status{/ts}</th>
         <th scope="col">{ts}Date{/ts}</th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$groupSubscriptions key=id item=groupSubscription}
        <tr>
          <td>{$groupSubscription.title}</td>
          <td>{$groupSubscription.status}</td>
          <td>{$groupSubscription.date}</td>
        </tr>
        {/foreach}
        </tbody>
      </table>
    </div>
</div>

{literal}
<script>
cj(document).ready( function() {
  cj('#GroupSubscriptionListTable').DataTable({
    "order": [[ 2, "desc" ]],
  });
});
</script>
{/literal}

{else}

<div class="messages status no-popup">
  <div class="icon inform-icon"></div>
  {ts}No group subscription have been recorded for this contact.{/ts}
</div>

{/if}
