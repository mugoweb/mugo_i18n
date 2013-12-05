<?php

namespace MugoI18nModules;

$module = $Params[ 'Module' ];

$content = $Params[ 'locale' ] ? get_content( $Params[ 'locale' ] ) : '';

// Not sure if that's a good list you might need more than the available translations
$availableTranslations = \eZContentLanguage::fetchList();

$tpl = \eZTemplate::factory();
$tpl->setVariable( 'translations', $availableTranslations);
$tpl->setVariable( 'content', $content );
$tpl->setVariable( 'locale', $Params[ 'locale' ] );

$Result = array();
$Result['content'] = $tpl->fetch( 'design:modules/mugo_i18n/main.tpl' );
$Result[ 'path' ] = array( array( 'text' => \ezpI18n::tr( 'kernel/user', 'Setup' ),
								  'url' => false ),
						   array( 'text' => \ezpI18n::tr( 'kernel/user', 'Mugo i18n' ),
								  'url' => false ) );


/****
 * functions
 ****/
function get_content( $locale )
{
	$return = '';
	
	$file = 'extension/mugo_i18n/translations/'. $locale .'/translation.ts';
	
	if( file_exists( $file ) )
	{
		$xslString =
		'<?xml version="1.0"?>
		<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
			<xsl:output omit-xml-declaration="yes" method="html" indent="yes" />

			<xsl:template match="context">
				<fieldset class="context">
					<legend><xsl:value-of select="name" /></legend>				
					<ul>
						<xsl:apply-templates select="message" />
					</ul>
				</fieldset>
			</xsl:template>

			<xsl:template match="message">
				<li class="{translation/@type}">
					<label>
						<xsl:value-of select="source" />
					</label>
					<input data-id="{location/@filename}/{location/@line}" type="text" value="{translation}" />
				</li>
			</xsl:template>


		</xsl:stylesheet>';

		$doc = new \DOMDocument();
		$doc->load( $file );

		$XSL = new \DOMDocument(); 
		$XSL->loadXML( $xslString ); 

		$xslt = new \XSLTProcessor(); 
		$xslt->importStylesheet( $XSL );

		$return = $xslt->transformToXML( $doc );

		/* some test instances to test the script
		ezi18n( 'context', 'source', $comment, $arguments );
		ezi18n( 'context', 'source', $comment );
		ezi18n( 'context', 'source' );
		ezi18n( 'context', 'source');

		ezpI18n::tr( 'context', 'source', $comment, $arguments );
		ezpI18n::tr( 'context', 'source', $comment );
		ezpI18n::tr( 'context', 'source' );
		ezpI18n::tr( 'context', 'source');
		*/
	}

	return $return;
}

?>