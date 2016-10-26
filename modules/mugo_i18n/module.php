<?php

$Module = array( 'name' => 'Main module'
               , 'variable_params' => true );

$ViewList = array();

$ViewList[ 'main' ] = array(
    'params'     => array( 'extension', 'locale' )
  , 'functions'  => array( 'editor' )
  , 'script'     => 'main.php'
  , 'ui_context' => 'administration'
  , 'default_navigation_part' => 'ezsetupnavigationpart'
);

$ViewList[ 'save' ] = array(
    'functions'  => array( 'editor' )
  , 'script'     => 'save.php'
  , 'ui_context' => 'administration'
);

$ViewList[ 'csv' ] = array(
    'params'     => array( 'extension', 'locale' )
  , 'functions'  => array( 'editor' )
  , 'script'     => 'csv.php'
  , 'ui_context' => 'administration'
);

$FunctionList = array();
$FunctionList[ 'editor' ] = array();

?>