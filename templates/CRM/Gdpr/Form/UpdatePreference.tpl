<div class="crm-communications-preferences-form-block crm-public">
	<div class="comm-pref-block channel-block">
		<!-- Page Intro Text from Settings -->
		{if $page_intro}
		<div class="section-description">
				{ts}{$page_intro}{/ts}
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
      <div class="section-description">
        {ts}{$groups_intro}{/ts}
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
					    		{assign var=groupChannel value=$channelName|replace:$containerPrefix:''}
					    		{if $commPrefGroupsetting.$elementName.$groupChannel}
					    		<span class="group-channel-matrix">
					    			{$groupChannel|ucwords}
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
        <div class="label">
          <label><span class="crm-marker" title="This field is required.">*</span></label>
        </div>
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
		var containerPrefix = "{/literal}{$containerPrefix}{literal}";
		
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

    	var mismatchedChannels = [];
    	if (isChecked) {
	    	$(groupDiv).find('.group-channel-matrix').each(function(){
	    		var groupChannel = $.trim($(this).text().toLowerCase());
	    		var currentChannelValue = $('#' + containerPrefix + groupChannel).val();
	    		if (currentChannelValue != 'YES') {
	    			mismatchedChannels.push(groupChannel);
	    		}
	    	});

	    	if (mismatchedChannels.length !== 0) {
	    		var mismatchedChannelTxt = mismatchedChannels.join(', ');
          CRM.confirm({
            title: ts('Group Channels'),
            message: ts('We may communicate with you by %1 since this is used by a group you have selected.',  {1: '<em>' + mismatchedChannelTxt + '</em>'})
          })
          .on('crmConfirm:yes', function() {
            $(mismatchedChannels).each(function(index, value){
            	$('#'+containerPrefix + value).val('YES');
            });
          });	    		
	    	}
    	}
    }
	});
</script>
{/literal}
