{* Template for terms and conditions field to insert into Event and Contribution forms *}
<fieldset class="crm-gdpr-terms-conditions" id="gdpr-terms-conditions">
  <legend></legend>
  <div class="crm-section">
    <div class="label"><label>Terms &amp; Conditions
    <span class="crm-marker" title="This field is required">*</span>
    </label></div>
    <div class="content">
      <div class="terms-conditions-acceptance-intro">
       {$terms_conditions.intro}
      </div>
    </div>
      {if $terms_conditions.links.global}
      {assign var="link" value=$terms_conditions.links.global}
    <div class="label"><label>
    </label></div>
      <div class="content terms-conditions-item">
        <a href="{$link.url}" class="terms-conditions-link" target="blank">{$link.label}</a>
        <div class="terms-conditions-checkbox">
        {$form.accept_tc.html}
        </div>
      </div>
      {/if}
      {if $terms_conditions.links.event}
      {assign var="link" value=$terms_conditions.links.event}
    <div class="label"><label>
    </label></div>
      <div class="content terms-conditions-item">
          <a href="{$link.url}" class="terms-conditions-link" target="blank">{$link.label}</a>
        <div class="terms-conditions-checkbox">
          {$form.accept_event_tc.html}
        </div>
      </div>
      {/if}
  </div>
  <div class="clear"></div>
</fieldset>
<script type="text/javascript">
{literal}
  (function($) {
    {/literal}
    var field = $('#gdpr-terms-conditions');
    {if $terms_conditions.position == 'formTop'}
    $('form .crm-section:first').prepend(field);
    {elseif $terms_conditions.position == 'customPre'}
    $('form .custom_pre-section:first').after(field);
    {elseif $terms_conditions.position ==  'customPost'}
    $('form .custom_post-section:first').after(field);
    {else}
    $('form #crm-submit-buttons:last').before(field);
    {/if}
    {literal}
  }(cj))
{/literal}
</script>
