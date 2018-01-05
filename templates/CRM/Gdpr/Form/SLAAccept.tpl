<div>
{$agreement_text}
</div>
{if $tc_url}
<div>
<a href="{$tc_url}" target="blank">Terms and Conditions </a>
</div>
{/if}
<div>
  {$form.accept_tc.html} 
  <span>{$form.accept_tc.label}</span>
  </div>
<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
