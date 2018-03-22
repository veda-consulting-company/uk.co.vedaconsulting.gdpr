
<div class="crm-form-block">
	<div class="comm-pref-block channel-block">
		{if $page_intro}
		<div class="help">
			<span>
				{ts}{$page_intro}{/ts}
			</span>
		</div>
		{/if}

		<fieldset>
			<legend>{$channels_intro}</legend>
			{foreach from=$channelEleNames item=elementName}
			  <div class="crm-section">
			    <div class="label">{$form.$elementName.label}</div>
			    <div class="content">{$form.$elementName.html}</div>
			    <div class="clear"></div>
			  </div>
			{/foreach}
		</fieldset>
	</div>

	<div class="comm-pref-block groups-block">
		{if $groups_intro}
		<div class="help">
			<span>
			{ts}{$groups_intro}{/ts}
			</span>
		</div>	
		{/if}

		<fieldset class="groups-fieldset">
			<legend>{$groups_heading}</legend>
			{foreach from=$groupEleNames item=elementName}
			  <div class="crm-section">
			    <div class="content">{$form.$elementName.html}</div>
			    <div class="clear"></div>
			  </div>
			{/foreach}
		</fieldset>
		{if $isContactDueAcceptance}
		<div class="crm-section">
			{$form.$tcFieldName.html}	
			<label for="{$tcFieldName}">{$tcFieldlabel}</label>
			<div class="clear"></div>
		</div>
		{/if}
	</div>

	<div class="crm-submit-buttons">
	{include file="CRM/common/formButtons.tpl" location="bottom"}
	</div>

</div>

{literal}
<style type="text/css">
	.comm-pref-block .crm-form-select {
		width: 15%;
	}
	.groups-fieldset .content label{
		margin-left: 15px;
		margin-bottom: 15px;
	}
</style>
{/literal}