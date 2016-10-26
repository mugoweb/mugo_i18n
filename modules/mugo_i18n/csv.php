<?php

namespace MugoI18nModules;

$extension = isset( $Params[ 'extension' ] ) ? $Params[ 'extension' ] : '';
$locale = isset( $Params[ 'locale' ] ) ? $Params[ 'locale' ] : '';

header( "Content-Type: text/csv" );
header( "Content-Disposition: attachment; filename=translation_export.csv" );
header( "Content-Description: csv File" );
header( "Pragma: no-cache" );
header( "Expires: 0" );

if( $extension && $locale )
{
    $content = get_csv_content( $extension, $locale );
}
else
{
    die( 'Please select an extension and a locale.' );
}


\eZExecution::cleanExit();

/*
 * Functions
 */

function get_csv_content( $extension, $locale )
{
    $file = 'extension/'. $extension .'/translations/'. $locale .'/translation.ts';

    if( file_exists( $file ) )
    {
        $fp = fopen( 'php://output', 'w' );        

        $doc = new \DOMDocument();
        $doc->load( $file );

        $xpath = new \DOMXPath( $doc );
        $result = $xpath->query( '//message' );

        echo 'context,source,translation' . "\n";
        foreach( $result as $message )
        {
            $context     = $xpath->query( $message->getNodePath() . '/../name' )->item(0)->nodeValue;
            $source      = $xpath->query( $message->getNodePath() . '/source' )->item(0)->nodeValue;
            $translation = $xpath->query( $message->getNodePath() . '/translation' )->item(0)->nodeValue;
            
            fputcsv( $fp, array( $context, $source, $translation ) );
        }
    }
    else
    {
        die( 'Could not find a translation file at: "'. $file . '"' );
    }

    return true;
}

?>