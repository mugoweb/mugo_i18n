<?php

$Module = array( 'name' => 'Main module',
                 'variable_params' => true );

$ViewList = array();

$ViewList[ 'main' ] = array(
	'params'     => array( 'locale' ),
	'functions'  => array( 'editor' ),
	'script'     => 'main.php',
	'ui_context' => 'administration'
);

$ViewList[ 'save' ] = array(
	'functions' => array( 'editor' ),
	'script' => 'save.php',
	'ui_context' => 'administration'
);

$FunctionList = array();
$FunctionList[ 'editor' ] = array();

?>