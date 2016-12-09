<?php
/**
 * Bootstrap file for setting the ABSPATH constant
 * and loading the db.php file. The db.php
 * file will then load the settings.php file, which
 * will then set up the Mtaandao environment.
 *
 * If the db.php file is not found then an error
 * will be displayed asking the visitor to set up the
 * db.php file.
 *
 * Will also search for db.php in Mtaandao' parent
 * directory to allow the Mtaandao directory to remain
 * untouched.
 *
 * @package Mtaandao
 */

/** Define ABSPATH as this file's directory */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );

/*
 * If db.php exists in the Mtaandao root, or if it exists in the root and settings.php
 * doesn't, load db.php. The secondary check for settings.php has the added benefit
 * of avoiding cases where the current directory is a nested installation, e.g. / is Mtaandao(a)
 * and /blog/ is Mtaandao(b).
 *
 * If neither set of conditions is true, initiate loading the setup process.
 */
if ( file_exists( ABSPATH . 'admin/config/db.php') ) {

	/** The config file resides in ABSPATH */
	require_once( ABSPATH . 'admin/config/db.php' );

} elseif ( @file_exists( dirname( ABSPATH ) . '/install' . '/config' . '/db.php' ) && ! @file_exists( dirname( ABSPATH ) . '/settings.php' ) ) {

	/** The config file resides one level above ABSPATH but is not part of another install */
	require_once( dirname( ABSPATH ) . '/install' . '/config' . '/db.php' );

} else {

	// A config file doesn't exist

	define( 'RES', 'res' );
	require_once( ABSPATH . RES . '/load.php' );

	// Standardize $_SERVER variables across setups.
	mn_fix_server_vars();

	require_once( ABSPATH . RES . '/functions.php' );

	$path = mn_guess_url() . '/admin/setup-config.php';

	/*
	 * We're going to redirect to setup-config.php. While this shouldn't result
	 * in an infinite loop, that's a silly thing to assume, don't you think? If
	 * we're traveling in circles, our last-ditch effort is "Need more help?"
	 */
	if ( false === strpos( $_SERVER['REQUEST_URI'], 'setup-config' ) ) {
		header( 'Location: ' . $path );
		exit;
	}

	define( 'MAIN_DIR', ABSPATH . 'main' );
	require_once( ABSPATH . RES . '/version.php' );

	mn_check_php_mysql_versions();
	mn_load_translations_early();

	// Die with an error message
	$die  = sprintf(
		/* translators: %s: db.php */
		__( "There doesn't seem to be a %s file. I need this before we can get started." ),
		'<code>db.php</code>'
	) . '</p>';
	$die .= '<p>' . sprintf(
		/* translators: %s: Codex URL */
		__( "Need more help? <a href='%s'>We got it</a>." ),
		__( 'https://mtaandao.github.io/Editing_db.php' )
	) . '</p>';
	$die .= '<p>' . sprintf(
		/* translators: %s: db.php */
		__( "You can create a %s file through a web interface, but this doesn't work for all server setups. The safest way is to manually create the file." ),
		'<code>db.php</code>'
	) . '</p>';
	$die .= '<p><a href="' . $path . '" class="button button-large">' . __( "Create a Configuration File" ) . '</a>';

	mn_die( $die, __( 'Mtaandao &rsaquo; Error' ) );
}
