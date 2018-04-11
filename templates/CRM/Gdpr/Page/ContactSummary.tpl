<div id="last_acceptance_date" class="crm-summary-row">
  <div class="crm-label">{ts}Gdpr Status{/ts}</div>
  <div class="crm-content crm-contact-gdpr_status">
		{if $lastAcceptanceDate} 
			Own preferences submitted on {$lastAcceptanceDate}
		{else}
			{ts} Not Updated {/ts}
		{/if}
	</div>
</div>
{literal}
<script type="text/javascript">
	CRM.$(function($){
		$('#last_acceptance_date').insertAfter(
			$('#crm-communication-pref-content').find('.crm-contact-privacy_values').parent('.crm-summary-row')
		);
	});
</script>
{/literal}