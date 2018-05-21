{if $comm_pref_url}
	<div id="comm_pref_url" class="comm_pref_url_div">
		<div class="header-dark"> Communication Preferences </div>
		{if $link_intro}
		<div id="comm_pref_intro">
			<span>{$link_intro}</span>
		</div>
		{/if}	
		<div id="comm_pref_link">			
			<span><a href="{$comm_pref_url}">{$link_label}</a></span>
		</div>
	</div>


	{literal}
	<script type="text/javascript">
		(function($) {
			var entity = "{/literal}{$entity}{literal}";
			if (entity == 'Event') {
				$('#comm_pref_url').appendTo($('.event_info-group .display-block'));
			}
			else{
				$('#comm_pref_url').appendTo($('.amount_display-group .display-block'));
			}
		}(cj))
	</script>
	{/literal}
{/if}