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
			    <div class="content group-channel-div">
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
					    			{$channelName|ucwords}
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
<script type="text/javascript">
	CRM.$(function($){
		var groupChk = $('.groups-fieldset input[type="checkbox"]');

    groupChk.each(function() { 
      checkGroupChannels(this)
    });
    groupChk.on('change', function() {
      checkGroupChannels(this);
    });

    function checkGroupChannels(controller) {
    	var groupId 	= $(controller).attr('id')
    	var groupDiv	= $(controller).parent('.group-channel-div');
    	var isChecked = $(controller).is(':checked');

    	if (isChecked) {
	    	$(groupDiv).find('.group-channel-matrix').each(function(){
	    		var groupChannel = $.trim($(this).text().toLowerCase());
	    		$('#' + groupChannel).val('YES');
	    	});
    	}
    }
	});
</script>
{/literal}
