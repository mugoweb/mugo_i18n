<?php

$module = $Params[ 'Module' ];

if( !empty( $_REQUEST[ 'ids' ] ) && $_REQUEST[ 'locale' ] && $_REQUEST[ 'extension' ] )
{
    // Checking given extension value
    $extension = '';
    $activeExtensions = \eZExtension::activeExtensions();
    if( in_array( $_REQUEST[ 'extension' ], $activeExtensions ) )
    {
        $extension = $_REQUEST[ 'extension' ];
    }

    // Checking locale value
    $locale = $_REQUEST[ 'locale' ];
    preg_replace( '#[^a-zA-Z\-]#', '', $locale );
    
    $file = 'extension/'. $extension .'/translations/'. $locale .'/translation.ts';

    if( file_exists( $file ) )
    {
        $doc = new DOMDocument();
        $doc->load( $file );
        $xpath = new DOMXPath( $doc );

        foreach( $_REQUEST[ 'ids' ] as $index => $id )
        {
            $idParts = explode( '/', $id );
            $line = array_pop( $idParts );
            $filename = implode( '/', $idParts );

            $result = $xpath->query( '//message[location/@line = "'. $line .'" and location/@filename = "'. $filename .'"]/translation' );

            if( $result->length )
            {
                $translationTag = $result->item( 0 );
                $translationTag->setAttribute( 'type', 'finished' );
                $translationTag->nodeValue = $_REQUEST[ 'values' ][ $index ];
            }
        }

        echo $doc->save( $file );
    }
}

eZExecution::cleanExit();
?>