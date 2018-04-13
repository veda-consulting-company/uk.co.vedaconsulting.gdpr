<div id="last_acceptance_date" class="crm-summary-row">
  <div class="crm-label">{ts}GDPR Status{/ts}</div>
  <div class="crm-content crm-contact-gdpr_status">
		{if $lastAcceptanceDate} 
			Communication preferences submitted on {$lastAcceptanceDate}
		{/if}
	</div>
</div>
{literal}
<script type="text/javascript">
	CRM.$(function($){
		$('#last_acceptance_date').prependTo('#crm-communication-pref-content .crm-inline-block-content');
	});
</script>
{/literal}