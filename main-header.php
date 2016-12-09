<?php
/**
 * Loads the Mtaandao environment and template.
 *
 * @package Mtaandao
 */

if ( !isset($mn_did_header) ) {

	$mn_did_header = true;

	// Load the Mtaandao library.
	require_once( dirname(__FILE__) . '/load.php' );

	// Set up the Mtaandao query.
	mn();

	// Load the theme template.
	require_once( ABSPATH . RES . '/template-loader.php' );

}
