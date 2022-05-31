<?php 

/**
 * Handles parsing eZ templates and PHP files for translations strings
 * as well as building eZ translation files
 */
class MugoI18nTranslationFileHandler
{
    public $extendedPatterns;
    public $overridePatterns;

    public function __construct()
    {
        $this->extendedPatterns = array
        (
            '.tpl'   => array()
            , '.php' => array()
        );
        $this->overridePatterns = array
        (
            '.tpl'   => array()
            , '.php' => array()
        );
    }

    /**
     * Returns a list of translatable strings with their context
     * 
     * @param  array $extensions Extensions to check
     * @param  array $contexts   Contexts to check
     * 
     * @return array             Translateable strings by context
     */
    public function getTranslationStrings( $extensions, $contexts = array() )
    {
        $result         = array();
        $fileExtensions = array( '.tpl', '.php' );

        foreach( $extensions as $extension )
        {
            foreach( $fileExtensions as $fileExtension )
            {
                $files = self::listExtensionFiles( $extension, array( $fileExtension ) );
                foreach( $files as $file )
                {
                    switch( $fileExtension )
                    {
                        case '.tpl':
                        {
                            $extendPatterns   = ( $this->extendedPatterns[ $fileExtension ] )? $this->extendedPatterns[ $fileExtension ] : array();
                            $overridePatterns = ( $this->overridePatterns[ $fileExtension ] )? $this->overridePatterns[ $fileExtension ] : array();
                            $instances        = self::getTPLStrings( $file, $extendPatterns, $overridePatterns );
                        }
                        break;

                        case '.php':
                        {
                            $extendPatterns   = ( $this->extendedPatterns[ $fileExtension ] )? $this->extendedPatterns[ $fileExtension ] : array();
                            $overridePatterns = ( $this->overridePatterns[ $fileExtension ] )? $this->overridePatterns[ $fileExtension ] : array();
                            $instances        = self::getPHPStrings( $file, $extendPatterns, $overridePatterns );
                        }
                        break;
                    }

                    foreach( $instances as $instance )
                    {
                        if( !self::translationExits( $instance[ 'context' ], $instance[ 'source' ] ) )
                        {
                            if( empty( $contexts ) || in_array( $instance[ 'context' ], $contexts, false ) )
                            {
                                $result[ $instance[ 'context' ] ][ md5( $instance[ 'source' ] ) ] = $instance;
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Returns a list of files found in the given extension based on their file extension
     * 
     * @param  string $extensionName  Extension to search
     * @param  array  $fileExtension  Extension of files to find and return
     * 
     * @return array                  List of files
     */
    private static function listExtensionFiles( $extensionName, $fileExtension )
    {
        $files           = array();
        $directoryHandle = @opendir( 'extension/' . $extensionName ) or die( "Unable to open extension/{$extensionName}" );
        $files           = self::recursionList( $directoryHandle, 'extension/' . $extensionName, $fileExtension );

        closedir( $directoryHandle );

        return $files;
    }

    /**
     * Returns a list of files found in the given directory/path recursively based on their file extension
     * 
     * @param  handle $directoryHandle Directory handle
     * @param  string $path            Directory path
     * @param  array  $fileExtension   Extension of files to find and return
     * 
     * @return array                   List of files found
     */
    private static function recursionList( $directoryHandle, $path, $fileExtension )
    {
        $return = array();

        while( false !== ( $file = readdir( $directoryHandle ) ) )
        {
            $dir = $path . '/' . $file;

            if( is_dir( $dir ) && $file != '.' && $file !='..' && $file != '.svn' && $file != '.git' )
            {
                $handle = @opendir( $dir ) or die( "unable to open file $file" );
                $return = array_merge( self::recursionList( $handle, $dir, $fileExtension ), $return );
            }
            elseif( in_array( substr( $file, -4 ), $fileExtension ) )
            {
                $return[] = $dir;
            }
        }

        return $return;
    }

    /**
     * Searches given file for strings marked with the ezpI18n::tr or ezi18n translation functions
     * 
     * @param  string $file             File to check for string marked for translation
     * @param  array  $extendPatterns   Array of regex patterns to use in addition to the defaults
     * @param  array  $overridePatterns Array of regex patterns to use instead of the defaults
     * 
     * @return array                    Matches strings
     */
    public static function getPHPStrings( $file, $extendPatterns = array(), $overridePatterns = array() )
    {
        $return  = array();
        $content = file_get_contents( $file );
        
        $patterns = array
        (
            '#(?:ezpI18n::tr|ezi18n)\( *[\'|"](.*?)[\'|"] *, *[\'|"](.*?)[\'|"] *[,|\)]#is'
        );

        if ( $extendPatterns )
        {
            $patterns = array_merge( $patterns, $extendPatterns );
        }
        elseif ( $overridePatterns )
        {
            $patterns = $overridePatterns;
        }

        $matches = array();
        foreach( $patterns as $index => $pattern )
        {
            preg_match_all( $pattern, $content, $matches[ $index ], PREG_OFFSET_CAPTURE );
        }

        foreach( $matches as $pattern_matches )
        {
            if( !empty( $pattern_matches[ 0 ] ) )
            {
                foreach( $pattern_matches[ 0 ] as $index => $values )
                {
                    $return[ $values[ 1 ] ] = array
                    (
                        'file'    => $file
                      , 'offset'  => $values[ 1 ]
                      , 'source'  => $pattern_matches[ 2 ][ $index ][ 0 ]
                      , 'context' => $pattern_matches[ 1 ][ $index ][ 0 ]
                    );
                }
            }
        }

        // sort by key (offset) so that results are in the order found in the file regardless of pattern
        ksort( $return );

        return $return;
    }

    /**
     * Searches given file for strings marked with the i18n template operator
     * 
     * @param  string $file             File to check for string marked for translation
     * @param  array  $extendPatterns   Array of regex patterns to use in addition to the defaults
     * @param  array  $overridePatterns Array of regex patterns to use instead of the defaults
     * 
     * @return array                    Matches strings
     */
    public static function getTPLStrings( $file, $extendPatterns = array(), $overridePatterns = array() )
    {
        $return  = array();
        $content = file_get_contents( $file );

        // updated regex to match more variations of translation markers e.g. in arrays or hashes
        // split into two regex to handle double-quoted string with single quotes and single-quoted string
        // with double quotes.
        $patterns = array
        (
            // double-quoted strings
            '#["]([^"]+)["]\|i18n\([ |]*[\'|"](.*?)[\'|"][ |]*[,|\)]#'
            // single-quoted string
            , '#[\']([^\']+)[\']\|i18n\([ |]*[\'|"](.*?)[\'|"][ |]*[,|\)]#'
        );

        if ( $extendPatterns )
        {
            $patterns = array_merge( $patterns, $extendPatterns );
        }
        elseif ( $overridePatterns )
        {
            $patterns = $overridePatterns;
        }

        $matches = array();
        foreach( $patterns as $index => $pattern )
        {
            preg_match_all( $pattern, $content, $matches[ $index ], PREG_OFFSET_CAPTURE );
        }

        foreach( $matches as $pattern_matches )
        {
            if( !empty( $pattern_matches[ 0 ] ) )
            {
                foreach( $pattern_matches[ 0 ] as $index => $values )
                {
                    $return[ $values[ 1 ] ] = array
                    (
                        'file'    => $file
                      , 'offset'  => $values[ 1 ]
                      , 'source'  => $pattern_matches[ 1 ][ $index ][ 0 ]
                      , 'context' => $pattern_matches[ 2 ][ $index ][ 0 ]
                    );
                }
            }
        }

        // sort by key (offset) so that results are in the order found in the file regardless of pattern
        ksort( $return );

        return $return;
    }

    /**
     * Builds and outputs the translation file XML
     * 
     * @param  array  $translationStrings String marked for translation by context
     * @param  string $defaultTranslation Default translation used for sources without translation
     * @param  string $existingTSFile     An existing translation file to check and use for translations when creating the new translations
     */
    public static function buildTranslationFile( $translationStrings, $defaultTranslation = '', $existingTSFile = '' )
    {
        $implementation    = new DOMImplementation();
        $dtd               = $implementation->createDocumentType( 'TS' );
        $doc               = $implementation->createDocument( null, 'TS', $dtd );
        $doc->encoding     = 'utf-8';
        $doc->formatOutput = true;
        
        $tsNode = $doc->childNodes->item( 1 );

        $existingTSDoc = ( $existingTSFile )? self::loadExistingTranslationFile( $existingTSFile ) : false;

        // build contexts
        foreach( $translationStrings as $context => $entries )
        {
            $contextNode = $doc->createElement( 'context' );
            $nameNode    = $doc->createElement( 'name' );
            $nameNode->appendChild( $doc->createTextNode( $context ) );
            $contextNode->appendChild( $nameNode );

            // build messages
            foreach( $entries as $entry )
            {
                $messageNode     = $doc->createElement( 'message' );
                $sourceNode      = $doc->createElement( 'source' );
                $translationNode = $doc->createElement( 'translation' );

                $sourceNode->appendChild( $doc->createTextNode( $entry[ 'source' ] ) );

                $existingTranslation = '';
                if ( $existingTSDoc )
                {
                    $existingTranslation = self::findExistingTranslation( $existingTSDoc, $context, $entry[ 'source' ] );
                }

                if ( $existingTranslation )
                {
                    $translationNode->appendChild( $doc->createTextNode(  $existingTranslation ) );
                    $translationNode->setAttribute( 'type', 'finished' );
                }
                else
                {
                    $translationNode->appendChild( $doc->createTextNode( $defaultTranslation ) );
                    $translationNode->setAttribute( 'type', 'unfinished' );
                }
                
                if( $entry[ 'file' ] )
                {
                    $locationNode = $doc->createElement( 'location' );
                    $locationNode->setAttribute( 'filename', $entry[ 'file' ] );
                    $locationNode->setAttribute( 'line', $entry[ 'offset' ] );

                    $messageNode->appendChild( $locationNode );
                }

                $messageNode->appendChild( $sourceNode );
                $messageNode->appendChild( $translationNode );

                $contextNode->appendChild( $messageNode );
            }

            $tsNode->appendChild( $contextNode );
        }

        echo $doc->saveXML();
    }

    /**
     * Loads a translations file into a DOMDocument
     * 
     * @param  string $filepath Path to an existing translations file
     * 
     * @return object           DOMDocument of existing translations file
     */
    private static function loadExistingTranslationFile( $filename )
    {
        $doc                     = new DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput       = true;

        $xml = file_get_contents( $filename );
        // replacing single quotes in name or source tags, because XPath 1.0 does not support
        // escaping quotes, but we need to find sources via XPath queries that may contain them.
        // 
        // no support for non-capturing groups, but we want to keep the replacements specific,
        // so multiple groups it is ...
        $xml = preg_replace( "/(<(name|source)>.*)(['])(.*<\/(name|source)>)/im", '$1#SQ#$4', $xml );

        // check structure
        libxml_use_internal_errors( true );
        $simplexml = simplexml_load_string( $xml );

        if( $simplexml === false )
        {
            $errors = libxml_get_errors();
            foreach( $errors as $error )
            {
                echo "Bad XML in existing TS file: " . trim( $error->message ) . "\n";
            }

            return false;
        }

        $doc->loadXML( $xml );

        return $doc;
    }

    /**
     * Run a single XPath query against a DOMDocument returning a single result
     * 
     * @param  object $doc   DOMDocument to query
     * @param  string $query XPath query to run against the DOMDocument
     * 
     * @return string        XPath query result
     */
    private static function findExistingTranslation( $doc, $context, $source )
    {
        $result         = '';
        $quoteMarker    = '#SQ#';
        $escapedContext = str_replace( "'", $quoteMarker, $context );
        $escapedSource  = str_replace( "'", $quoteMarker, $source );

        $xpath       = new DOMXPath( $doc );
        $query       = "//TS/context/name[text() = '{$escapedContext}']//following-sibling::message/source[text() = '{$escapedSource}']//following-sibling::translation[normalize-space(text()) != '']";
        $queryResult = $xpath->query( $query );

        if( $queryResult && $queryResult->length )
        {
            $result = $queryResult->item( 0 )->nodeValue;
            $result = str_replace( $quoteMarker, "'", $result );
        }

        return $result;
    }

    /**
     * Checks if the eZTranslationManager has an existing translation for the given source
     * 
     * @param  string $context Translation context
     * @param  string $source  Translation source
     * 
     * @return boolean         Translation check result
     */
    public static function translationExits( $context, $source )
    {
        $translationManager = eZTranslatorManager::instance();
        $translation        = $translationManager->translate( $context, $source );

        return $translation !== null;
    }
}

?>