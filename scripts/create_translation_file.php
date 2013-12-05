<?php

#################
#  Setting up env
#################

require 'autoload.php';

$params = new ezcConsoleInput();

$helpOption = new ezcConsoleOption( 'h', 'help' );
$helpOption->mandatory = false;
$helpOption->shorthelp = "Show help information";
$params->registerOption( $helpOption );

$targetOption = new ezcConsoleOption( 't', 'target', ezcConsoleInput::TYPE_STRING );
$targetOption->mandatory = true;
$targetOption->shorthelp = "The target extensions, comma separated list";
$params->registerOption( $targetOption );

$default_translation = new ezcConsoleOption( 'd', 'default_translation', ezcConsoleInput::TYPE_STRING );
$default_translation->mandatory = false;
$default_translation->shorthelp = "Set a default translation for all strings.";
$params->registerOption( $default_translation );

// Process console parameters
try
{
    $params->process();
}
catch ( ezcConsoleOptionException $e )
{
    print( $e->getMessage(). "\n" );
    print( "\n" );

    echo $params->getHelpText( 'TS file generator.' ) . "\n";

    echo "\n";
    exit();
}

####################
# Script process
####################

$extensions      = explode( ',', $targetOption->value );
$file_extensions = array( '.tpl', '.php' );

// extract strings from files and store them in $results
$result = array();
foreach( $extensions as $extension )
{
	foreach( $file_extensions as $file_extension )
	{
		$files = Create_Translation_File_Handler::list_extension_files( $extension, array( $file_extension ) );

		foreach( $files as $file )
		{
			switch( $file_extension )
			{
				case '.tpl':
				{
					$i18n_instances = get_i18n_strings( $file );
				}
				break;

				case '.php':
				{
					$i18n_instances = get_i18n_strings_in_php( $file );
				}
				break;
			}

			foreach( $i18n_instances as $instance )
			{
				if( !translation_exits( $instance[ 'context' ], $instance[ 'source' ] ) )
				{
					$result[ $instance[ 'context' ] ][ md5( $instance[ 'source' ] ) ] = $instance;
				}
			}
		}
	}
}

// Sort strings per context
#foreach( $result as &$context )
#{
#	sort( $context );
#}

build_ts_file( $result, $default_translation->value );


########################
# Functions
########################

function get_i18n_strings_in_php( $file )
{
	$return = array();

	$content = file_get_contents( $file );
	
	preg_match_all( '#(?:ezpI18n::tr|ezi18n)\( *[\'|"](.*?)[\'|"] *, *[\'|"](.*?)[\'|"] *[,|\)]#is', $content, $instances, PREG_OFFSET_CAPTURE );

	if( !empty( $instances[ 0 ] ) )
	{
		foreach( $instances[ 0 ] as $index => $values )
		{
			$return[] = array(
				'file'    => $file,
				'offset'  => $values[ 1 ],
				'source'  => $instances[ 2 ][ $index ][ 0 ],
				'context' => $instances[ 1 ][ $index ][ 0 ]
			);
		}
	}
	
	return $return;
}

/**
 * 
 * @param type $file
 * @return array
 */
function get_i18n_strings( $file )
{
	$return = array();
	
	$content = file_get_contents( $file );
	
	preg_match_all( '#{[\'|"]([^{]+)[\'|"]\|i18n\([ |]*[\'|"](.*?)[\'|"][ |]*[,|\)]#', $content, $instances, PREG_OFFSET_CAPTURE );

	if( !empty( $instances[ 0 ] ) )
	{
		foreach( $instances[ 0 ] as $index => $values )
		{
			$return[] = array(
				'file'    => $file,
				'offset'  => $values[ 1 ],
				'source'  => $instances[ 1 ][ $index ][ 0 ],
				'context' => $instances[ 2 ][ $index ][ 0 ]
			);
		}
	}

	return $return;
}

function build_ts_file( $result, $default_translation = '' )
{
	echo "<!DOCTYPE TS><TS>\n";
	
	foreach( $result as $context => $entries )
	{
		echo "\t<context>\n";
		echo "\t<name>$context</name>\n";

		foreach( $entries as $entry )
		{
			// Encode the ampersands and less than / greater than since this is XML
			$source = htmlspecialchars( $entry[ 'source' ], ENT_NOQUOTES );
			$source = str_replace('&', '&amp;', $source );
			
			echo "\t\t<message>\n";
			
				if( $entry[ 'file' ] )
				{
					echo "\t\t\t<location filename=\"{$entry[ 'file' ]}\" line=\"{$entry[ 'offset' ]}\" />\n";
				}
				echo "\t\t\t<source>$source</source>\n";
				echo "\t\t\t".'<translation type="unfinished">' . htmlentities( $default_translation ) . '</translation>'."\n";

			echo "\t\t</message>\n";
		}
		echo "\t</context>\n";
	}
	echo "</TS>\n";
}

/**
 * 
 * @param string $context
 * @param string $source
 * @return boolean
 */
function translation_exits( $context, $source )
{
	$man = eZTranslatorManager::instance();
	$trans = $man->translate( $context, $source );

	return $trans !== null;
}

class Create_Translation_File_Handler
{

	static function list_extension_files( $extension_name, $file_extensions )
	{
		$files = array();
		
		$dir_handle = @opendir( 'extension/' . $extension_name ) or die( "Unable to open $path" );
		
		$files = self::recursion_list( $dir_handle, 'extension/' . $extension_name, $file_extensions );
	
		closedir( $dir_handle );
		
		return $files;
	}

	static function recursion_list( $dir_handle, $path, $file_extensions )
	{
		$return = array();
		//running the while loop
		while (false !== ( $file = readdir($dir_handle) ) )
		{
			$dir = $path.'/'.$file;
	
			if( is_dir( $dir ) && $file != '.' && $file !='..' && $file != '.svn' )
			{
				$handle = @opendir($dir) or die("undable to open file $file");
				//echo "D: $file\n";
				
				$return = array_merge( self::recursion_list( $handle, $dir, $file_extensions), $return );
			}
			elseif( in_array( substr( $file, -4 ), $file_extensions ) )
			{
				//TODO: still takes ini files
				//echo "F: $file\n";
				$return[] = $dir;
			}
		}
		
		return $return;
	}
}
?>