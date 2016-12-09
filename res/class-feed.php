<?php
/**
 * Feed API
 *
 * @package Mtaandao
 * @subpackage Feed
 */

_deprecated_file( basename( __FILE__ ), '4.7.0', 'fetch_feed()' );

if ( ! class_exists( 'SimplePie', false ) ) {
	require_once( ABSPATH . RES . '/class-simplepie.php' );
}

require_once( ABSPATH . RES . '/class-mn-feed-cache.php' );
require_once( ABSPATH . RES . '/class-mn-feed-cache-transient.php' );
require_once( ABSPATH . RES . '/class-mn-simplepie-file.php' );
require_once( ABSPATH . RES . '/class-mn-simplepie-sanitize-kses.php' );