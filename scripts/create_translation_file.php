<?php

require_once( 'autoload.php' );

$parameters = new ezcConsoleInput();

$helpOption            = new ezcConsoleOption( 'h', 'help' );
$helpOption->mandatory = false;
$helpOption->shorthelp = "Show help information";
$parameters->registerOption( $helpOption );

$targetOption            = new ezcConsoleOption( 't', 'target', ezcConsoleInput::TYPE_STRING );
$targetOption->mandatory = true;
$targetOption->shorthelp = "The target extensions, comma separated list";
$parameters->registerOption( $targetOption );

$targetContextsOption            = new ezcConsoleOption( 'c', 'context', ezcConsoleInput::TYPE_STRING );
$targetContextsOption->mandatory = false;
$targetContextsOption->default   = '';
$targetContextsOption->shorthelp = "All contexts are returned by default.  Use this option to limit the output to the comma separated list provided.";
$parameters->registerOption( $targetContextsOption );

$defaultTranslationOption            = new ezcConsoleOption( 'd', 'default-translation', ezcConsoleInput::TYPE_STRING );
$defaultTranslationOption->mandatory = false;
$defaultTranslationOption->default   = '';
$defaultTranslationOption->shorthelp = "Set a default translation for all strings.";
$parameters->registerOption( $defaultTranslationOption );

$existingTranslationsFileOption            = new ezcConsoleOption( 'e', 'existing-translations-file', ezcConsoleInput::TYPE_STRING );
$existingTranslationsFileOption->mandatory = false;
$existingTranslationsFileOption->default   = '';
$existingTranslationsFileOption->shorthelp = "An existing translations file to check for translations.";
$parameters->registerOption( $existingTranslationsFileOption );

$extendPatternsTPLOption            = new ezcConsoleOption( 'x', 'extend-patterns-tpl', ezcConsoleInput::TYPE_STRING );
$extendPatternsTPLOption->mandatory = false;
$extendPatternsTPLOption->multiple  = true;
$extendPatternsTPLOption->default   = array();
$extendPatternsTPLOption->shorthelp = "A regex pattern of template operator calls to search for, extending the existing list of patterns; (multi-option)";
$parameters->registerOption( $extendPatternsTPLOption );

$extendPatternsPHPOption            = new ezcConsoleOption( 'X', 'extend-patterns-php', ezcConsoleInput::TYPE_STRING );
$extendPatternsPHPOption->mandatory = false;
$extendPatternsPHPOption->multiple  = true;
$extendPatternsPHPOption->default   = array();
$extendPatternsPHPOption->shorthelp = "A regex pattern of PHP function calls to search for, extending the existing list of patterns; (multi-option)";
$parameters->registerOption( $extendPatternsPHPOption );

$overridePatternsTPLOption            = new ezcConsoleOption( 'o', 'override-patterns-tpl', ezcConsoleInput::TYPE_STRING );
$overridePatternsTPLOption->mandatory = false;
$overridePatternsTPLOption->multiple  = true;
$overridePatternsTPLOption->default   = array();
$overridePatternsTPLOption->shorthelp = "A regex pattern of template operator calls to search for, overriding the existing list of patterns; (multi-option)";
$parameters->registerOption( $overridePatternsTPLOption );

$overridePatternsPHPOption            = new ezcConsoleOption( 'O', 'override-patterns-php', ezcConsoleInput::TYPE_STRING );
$overridePatternsPHPOption->mandatory = false;
$overridePatternsPHPOption->multiple  = true;
$overridePatternsPHPOption->default   = array();
$overridePatternsPHPOption->shorthelp = "A regex pattern of PHP function calls to search for, overriding the existing list of patterns; (multi-option)";
$parameters->registerOption( $overridePatternsPHPOption );

// Process console parameters
try
{
    $parameters->process();
}
catch ( ezcConsoleOptionException $e )
{
    echo $e->getMessage() . "\n\n";
    echo $parameters->getHelpText( 'TS file generator.' ) . "\n\n";

    exit();
}

$translationFileHandler = new MugoI18nTranslationFileHandler();

$extensions = explode( ',', $targetOption->value );
$contexts   = ( $targetContextsOption->value )? explode( ',', $targetContextsOption->value ) : array();

// Set extended patterns
if ( $extendPatternsTPLOption->value ) { $translationFileHandler->extendedPatterns[ '.tpl' ] = $extendPatternsTPLOption->value; }
if ( $extendPatternsPHPOption->value ) { $translationFileHandler->extendedPatterns[ '.php' ] = $extendPatternsPHPOption->value; }

// Set override patterns
if ( $overridePatternsTPLOption->value ){ $translationFileHandler->overridePatterns[ '.tpl' ] = $overridePatternsTPLOption->value; }
if ( $overridePatternsPHPOption->value ){ $translationFileHandler->overridePatterns[ '.php' ] = $overridePatternsPHPOption->value; }

// Find translations strings
$translationStrings = $translationFileHandler->getTranslationStrings( $extensions, $contexts );

// Return translation file contents or failure message.
if ( $translationStrings )
{
    MugoI18nTranslationFileHandler::buildTranslationFile( $translationStrings, $defaultTranslationOption->value, $existingTranslationsFileOption->value );
}
else
{
    echo "No translation strings found.\n";
}

?>
