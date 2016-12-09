<?php
/**
 * Install theme network administration panel.
 *
 * @package Mtaandao
 * @subpackage Multisite
 * @since 3.1.0
 */

if ( isset( $_GET['tab'] ) && ( 'theme-information' == $_GET['tab'] ) )
	define( 'IFRAME_REQUEST', true );

/** Load Mtaandao Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

require( ABSPATH . 'admin/theme-install.php' );
