{*
    INPUT
	
	locale
	content
*}

{ezscript_require( 'jquery.mugo_i18n.js' )}
{ezcss_require( 'mugo_i18n.css' )}

<div id="mugo_i18n">
	<h1>Internationalizations</h1>

	{if $translations|count()}
	Select target locale:
	<select id="localelist">
		<option>---</option>
		{foreach $translations as $translation}
			<option data-locale="{$translation.locale}"
					value={concat( 'mugo_i18n/main/', $translation.locale )|ezurl()}
					{if eq( $locale, $translation.locale )}selected="selected"{/if}
			>
				{$translation.name|wash()}
			</option>
		{/foreach}
	</select>
	{/if}
	
	<button data-active="0" id="hidefinished">Hide finished translations</button>
	<button id="save">Save</button>
	
	<div class="content">
		{$content}
	</div>
</div>
	
<script>
$(function()
{ldelim}
	$( '#mugo_i18n' ).mugo_i18n(
	{ldelim}
		save_service_url : {'mugo_i18n/save'|ezurl()}
	{rdelim});
{rdelim});
</script>
