{* HEADER *}

<div>

<h3>Data Protection Officer</h3>

<div class="crm-block crm-form-block crm-gdpr-settings-form-block">

<div id="help">
	{ts}Set your organisation's data protection officer.{/ts}
</div>

<div class="crm-section">
	<div class="label">{$form.data_officer.label}</div>
	<div class="content">
		{$form.data_officer.html}
		<br />
         <span class="description"><i>{ts}Under the GDPR, you must appoint a data protection officer (DPO).{/ts} <a href='https://ico.org.uk/for-organisations/data-protection-reform/overview-of-the-gdpr/accountability-and-governance/#dpo' target='_blank'>More info</a></i></span>
	</div>
	<div class="clear"></div>
</div>

</div>

<h3>Activity types</h3>

<div class="crm-block crm-form-block crm-gdpr-settings-form-block">

<div id="help">
	{ts}Set activity types to check for contacts that have not had any activity for a set period.{/ts}
</div>

<div class="crm-section">
	<div class="label">{$form.contact_type.label}</div>
	<div class="content">
		{$form.contact_type.html}
		<br />
        <span class="description"><i>{ts}Check only these contact types who have not had any activity.{/ts}</i></span>
	</div>
	<div class="clear"></div>
</div>

<div class="crm-section">
	<div class="label">{$form.activity_type.label}</div>
	<div class="content">
		{$form.activity_type.html}
		<br />
        <span class="description"><i>{ts}Check for contacts who have not had any activity of these types.{/ts}</i></span>
	</div>
	<div class="clear"></div>
</div>

<div class="crm-section">
	<div class="label">{$form.activity_period.label}</div>
	<div class="content">
		{$form.activity_period.html} (days)
	</div>
	<div class="clear"></div>
</div>

</div>

<!-- Forget Me settings -->
<h3>Forget me</h3>

<div class="crm-block crm-form-block crm-gdpr-settings-form-block">
	<div id="help">
		{ts}Settings related to 'Forget me' process.{/ts}
	</div>

	<div class="crm-section">
		<div class="label">{$form.forgetme_name.label}</div>
		<div class="content">
			{$form.forgetme_name.html}
			<br />
	        <span class="description"><i>{ts}Name to be used for contacts that have been made anonymous.{/ts}</i></span>
		</div>
		<div class="clear"></div>
	</div>
</div>
<!-- /Forget me settings -->

{* FOOTER *}
<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>

</div>
