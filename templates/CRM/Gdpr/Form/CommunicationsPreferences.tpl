{crmScope extensionKey='uk.co.vedaconsulting.gdpr'}
{* Template for the Communications Preferences Settings form. *}

<div>
   <div class="help">
   {ts}Configure the display of the <a href="/civicrm/gdpr/comms-prefs/update" target="blank">Communications Preferences page</a>.{/ts}
   </div>
  <div class="crm-block crm-form-block crm-gdpr-comms-prefs-form-block">
  {foreach from=$page_elements item=elementName}
    <div class="crm-section">
      <div class="label">{$form.$elementName.label}</div>
      <div class="content">{$form.$elementName.html}
      {if $descriptions.$elementName} 
        <div class="description">{$descriptions.$elementName}</div>
      {/if}
      </div>
      <div class="clear"></div>
    </div>
  {/foreach}
  </div>{* end page block *}
  <h3> {ts}Channels{/ts} </h3>
   <div class="help">
   {ts}Configure which channels to show on the page.{/ts}
   </div>
  {* Channels block *}
  <div class="crm-block crm-form-block crm-gdpr-comms-prefs-form-block">
    <div class="crm-section">
      <div class="label">{ $form.enable_channels.label }</div>
      <div class="content">{$form.enable_channels.html} 
      </div>
      <div class="clear"></div>
    </div>
  <fieldset class="channels-wrapper toggle-target">
  {foreach from=$channels_elements item=elementName}
    <div class="crm-section">
      <div class="label">{$form.$elementName.label}</div>
      <div class="content">{$form.$elementName.html}</div>
      <div class="clear"></div>
    </div>
  {/foreach}
   </fieldset>
  </div>{* end Channels block *}
  <h3> {ts}Subscriptions to Groups{/ts} </h3>
   <div class="help">
   {ts}Configure which public groups the user can join on the page. You can optionally alter the group title and description. (For example to add more details on the content, message frequency etc.).{/ts}
   </div>
  {* Groups block *}
  <div class="crm-block crm-form-block crm-gdpr-comms-prefs-form-block">
    <div class="crm-section">
      <div class="label">{$form.enable_groups.label}</div>
      <div class="content">{$form.enable_groups.html}</div>
      <div class="clear"></div>
    </div>
   <fieldset class="groups-wrapper toggle-target">
  {foreach from=$groups_elements item=elementName}
    <div class="crm-section">
      <div class="label">{$form.$elementName.label}</div>
      <div class="content">{$form.$elementName.html}</div>
      <div class="clear"></div>
    </div>
  {/foreach}
  {if $group_containers}
   <table>
   <tr>
   <th>{ts}Group{/ts}</th><th>{ts}Add{/ts}</th><th>{ts}Title{/ts}</th><th>{ts}Description{/ts}</th><th>{ts}Channel{/ts}</th>
   </tr>
   {foreach from=$group_containers item=containerName}
     <tr>
     <td>
     <strong>
     {$form.$containerName.label}
     </strong>
     </td>
      <td>
       {$form.$containerName.group_enable.html}
      </td>
      <td>
       {$form.$containerName.group_title.html}
      </td>
      <td>
       {$form.$containerName.group_description.html}
      </td>
      <td class="group-channels">
        {$form.$containerName.email.html}
        {$form.$containerName.phone.html}
        {$form.$containerName.post.html}
        {$form.$containerName.sms.html}
      </td>

     </tr>
   {/foreach}

   </table>
   {/if}
   </fieldset>
  </div>{* end Groups block *}
  <h3> {ts}Completion{/ts} </h3>
  <div class="crm-block crm-form-block crm-gdpr-comms-prefs-form-block">
    <div class="crm-section">
      <div class="label">{$form.completion_redirect.label}</div>
      <div class="content">{$form.completion_redirect.html}
        <div class="description"> </div>
      </div>
    </div>
    <div class="clear"></div>
    <div class="crm-section completion-message">
      <div class="label">{$form.completion_message.label}</div>
      <div class="content">{$form.completion_message.html}
        <div class="description">{$descriptions.completion_message}</div>
      </div>
    </div>
    <div class="clear"></div>
    <div class="crm-section completion-url">
      <div class="label">{$form.completion_url.label}</div>
      <div class="content">{$form.completion_url.html}
        <div class="description">{$descriptions.completion_url}</div>
      </div>
    </div>
    <div class="clear"></div>
  </div> {* end Completion block *}
 </div>{* end form *}

{* FOOTER *}
<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
{literal}
<script>
(function($) {
  // General toggle
  $('input.toggle-control').on('change', function(){
    var toggleTarget = $(this).data('toggle');
    if (toggleTarget) {
      $(toggleTarget).toggle($(this).is(':checked'));
    }
  }).trigger('change');

  // Toggle completion setting elements.
  var completionRadioSelector = 'input[name="completion_redirect"]',
    completionRadio = $(completionRadioSelector),
    completionUrl = $('.completion-url');
    completionMessage = $('.completion-message'),
    isOn = $(completionRadioSelector + ":checked").val() == 1; 
  completionUrl.toggle(isOn);
  completionMessage.toggle(!isOn);
  completionRadio.on('change', function(){
    var isOn = (true == $(this).val());
    completionUrl.toggle(isOn);
    completionMessage.toggle(!isOn);
  });
  controlGroupChannels();
  function controlGroupChannels() {
    var channels = ['email', 'phone', 'post', 'sms'];
    var channelChk = $('input.enable-channel');
    var groupChannels = $('.group-channels input[type="checkbox"]');
    // Disable group channels if channel is disabled.
    function checkGroupChannels(controller) {
        var channel = $(controller).attr('id').replace('channels_enable_', '');
        if (channels.indexOf(channel) === false) {
          return;
        }
        var isChecked = $(controller).is(':checked');
        // get group channels
        groupChannels.filter('input[id$="' + channel + '"]').each(function(){
            if (!isChecked) {
              $(this).attr('checked', false);
            }
            $(this).attr('disabled', !isChecked);
          });
    }
    channelChk.each(function() { 
      checkGroupChannels(this)
    });
    channelChk.on('change', function() {
      checkGroupChannels(this);
    });
  }
}(cj));
</script>
{/literal}
{/crmScope}
