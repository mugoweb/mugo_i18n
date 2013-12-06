<?php

$module = $Params[ 'Module' ];

if( !empty( $_REQUEST[ 'ids' ] ) && $_REQUEST[ 'locale' ] )
{
	// PEK: security risk here - user a preg_replace to only allow a-z01\-
	$file = 'extension/mugo_i18n/translations/'. $_REQUEST[ 'locale' ] .'/translation.ts';

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
				$translationTag = $result->item(0);
				$translationTag->setAttribute( 'type', 'finished' );
				$translationTag->nodeValue = $_REQUEST[ 'values' ][ $index ];
				
				var_dump( $_REQUEST[ 'values' ][ $index ] );
			}
		}

		echo $doc->save( $file );
	}
}

eZExecution::cleanExit();
?>