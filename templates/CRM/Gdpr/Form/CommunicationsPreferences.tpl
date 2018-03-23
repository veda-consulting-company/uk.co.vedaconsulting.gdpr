{* Template for the Communications Preferences Settings form. *}

<div>
   <div class="help">
   Configure the display of the Communications Preferences page.
   </div>
  <div class="crm-block crm-form-block crm-gdpr-comms-prefs-form-block">
  {foreach from=$page_elements item=elementName}
    <div class="crm-section">
      <div class="label">{$form.$elementName.label}</div>
      <div class="content">{$form.$elementName.html}</div>
      <div class="clear"></div>
    </div>
  {/foreach}
  </div>{* end page block *}
  <h3> Channels </h3>
   <div class="help">
   Configure which channels to show on the page.
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
  <h3> Subscriptions to Groups </h3>
   <div class="help">
   Configure which public groups the user can join on the page. You can optionally alter the group title and description. (For example to add more details on the content, message frequency etc.). 
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
   <th>Group</th><th>Enable</th><th>Title</th><th>Description</th><th>Uses Channel</th>
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
      <td>
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

 </div>{* end form *}

{* FOOTER *}
<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
{literal}
<script>
(function($) {
  $('input.toggle-control').on('change', function(){
    var toggleTarget = $(this).data('toggle');
    if (toggleTarget) {
      $(toggleTarget).toggle($(this).is(':checked'));
    }
  }).trigger('change');
}(cj));
</script>
{/literal}
