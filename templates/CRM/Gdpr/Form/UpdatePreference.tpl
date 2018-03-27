<div class="crm-form-block">
	<div class="comm-pref-block channel-block">
		<!-- Page Intro Text from Settings -->
		{if $page_intro}
		<div class="help">
			<span>
				{ts}{$page_intro}{/ts}
			</span>
		</div>
		{/if}

		<!-- if any profile has configured -->
		{if !empty($custom_pre)}
		<fieldset>
		    <div class="crm-public-form-item crm-group custom_pre_profile-group">
		    {include file="CRM/UF/Form/Block.tpl" fields=$custom_pre}
		    </div>
		</fieldset>
		{/if}

		<!-- Channels fieldset section -->
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

	<!-- Groups from settings -->
	<div class="comm-pref-block groups-block">

		<!-- Groups Fieldset -->
		<fieldset class="groups-fieldset">
			<legend>{$groups_heading}</legend>
      {if $groups_intro}
      <div class="help">
        <span>
        {ts}{$groups_intro}{/ts}
        </span>
      </div>
		{/if}
			{foreach from=$groupEleNames item=elementName}
			  <div class="crm-section">
			    <div class="content">
			    	{$form.$elementName.html}
			    	{$form.$elementName.label}
			    	{if $commPrefGroupsetting.$elementName.group_description}
				    	<br>
				    	<span class="group-description">
				    		{$commPrefGroupsetting.$elementName.group_description}
				    		<br>
					    	{foreach from=$channelEleNames item=channelName}
					    		{if $commPrefGroupsetting.$elementName.$channelName}
					    		<span class="group-channel-matrix">
					    			<small>{$channelName|ucwords} </small>
					    		</span>
					    		{/if}
					    	{/foreach}
				    	</span>
			    	{/if}
			  	</div>
			    <div class="clear"></div>
			  </div>
			{/foreach}
		</fieldset>

	<div class="clear"></div>
		<!-- GDPR Terms and conditions url link and checkbox -->
    <fieldset class="data-policy-fieldset">
		{if $isContactDueAcceptance}
      <div class="crm-section data-policy">
        <div class="content">
          {$form.$tcFieldName.html}
          <label for="{$tcFieldName}">{$tcFieldlabel}</label>
        </div>
        <div class="clear"></div>
      </div>
      {else}
      <div class="crm-section">
        <div class="content">
          <span>{$tcFieldlabel}</span>
          <div class="clear"></div>
        </div>
      </div>
      {/if}
    </div>
  </fieldset>

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
	.group-description {
		display: inline-block;
		margin: 1em 1em 1em 2.3em;
	}
	.group-channel-matrix {
		display: inline-block;
		padding-top: 10px;
		padding-right: 10px;
	}
	.groups-fieldset .crm-error{
		display: none;
	}
	.groups-fieldset .crm-error-label{
		display: unset;
	}
</style>
{/literal}
