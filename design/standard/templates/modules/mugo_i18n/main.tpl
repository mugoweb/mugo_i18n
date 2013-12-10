{*
    INPUT
	
	locale
	extensions
	content
*}

{ezscript_require( 'jquery.mugo_i18n.js' )}
{ezcss_require( 'mugo_i18n.css' )}

<div id="mugo_i18n">
	<h1>Translations</h1>

	<div class="controlbar">
		<fieldset>
			<legend>Select File:</legend>
			{if $extensions}
				<label>Extension:</label>
				<select id="extensionlist">
					<option>---</option>
					{foreach $extensions as $iExtension}
						<option value="{$iExtension}" {if eq( $iExtension, $extension )}selected="selected"{/if}>
							{$iExtension|wash()}
						</option>
					{/foreach}
				</select>
			{/if}

			{if $translations|count()}
				<label>Locale:</label>
				<select id="localelist">
					<option>---</option>
					{foreach $translations as $translation}
						<option value="{$translation.locale}" {if eq( $locale, $translation.locale )}selected="selected"{/if}>
							{$translation.name|wash()}
						</option>
					{/foreach}
				</select>
			{/if}
		</fieldset>
		
		<fieldset>
			<legend>Filter:</legend>
			Context:
			<select id="contextlist">
				<option value="">All</option>
			</select>
			Hide finished translations: <input id="hidefinished" type="checkbox" />
		</fieldset>
		
		<div style="text-align: right">
			<input class="button" id="save" value="Save" />
		</div>
	</div>
		
	<div class="content">
		{$content}
	</div>
</div>
	
<script>
$(function()
{ldelim}
	$( '#mugo_i18n' ).mugo_i18n(
	{ldelim}
		main_service_url : {'mugo_i18n/main'|ezurl()},
		save_service_url : {'mugo_i18n/save'|ezurl()}
	{rdelim});
{rdelim});
</script>
