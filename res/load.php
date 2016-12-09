<?php
/**
 * These functions are needed to load Mtaandao.
 *
 * @package Mtaandao
 */

/**
 * Return the HTTP protocol sent by the server.
 *
 * @since 4.4.0
 *
 * @return string The HTTP protocol. Default: HTTP/1.0.
 */
function mn_get_server_protocol() {
	$protocol = $_SERVER['SERVER_PROTOCOL'];
	if ( ! in_array( $protocol, array( 'HTTP/1.1', 'HTTP/2', 'HTTP/2.0' ) ) ) {
		$protocol = 'HTTP/1.0';
	}
	return $protocol;
}

/**
 * Turn register globals off.
 *
 * @since 2.1.0
 * @access private
 */
function mn_unregister_GLOBALS() {
	if ( !ini_get( 'register_globals' ) )
		return;

	if ( isset( $_REQUEST['GLOBALS'] ) )
		die( 'GLOBALS overwrite attempt detected' );

	// Variables that shouldn't be unset
	$no_unset = array( 'GLOBALS', '_GET', '_POST', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES', 'table_prefix' );

	$input = array_merge( $_GET, $_POST, $_COOKIE, $_SERVER, $_ENV, $_FILES, isset( $_SESSION ) && is_array( $_SESSION ) ? $_SESSION : array() );
	foreach ( $input as $k => $v )
		if ( !in_array( $k, $no_unset ) && isset( $GLOBALS[$k] ) ) {
			unset( $GLOBALS[$k] );
		}
}

/**
 * Fix `$_SERVER` variables for various setups.
 *
 * @since 3.0.0
 * @access private
 *
 * @global string $PHP_SELF The filename of the currently executing script,
 *                          relative to the document root.
 */
function mn_fix_server_vars() {
	global $PHP_SELF;

	$default_server_values = array(
		'SERVER_SOFTWARE' => '',
		'REQUEST_URI' => '',
	);

	$_SERVER = array_merge( $default_server_values, $_SERVER );

	// Fix for IIS when running with PHP ISAPI
	if ( empty( $_SERVER['REQUEST_URI'] ) || ( PHP_SAPI != 'cgi-fcgi' && preg_match( '/^Microsoft-IIS\//', $_SERVER['SERVER_SOFTWARE'] ) ) ) {

		// IIS Mod-Rewrite
		if ( isset( $_SERVER['HTTP_X_ORIGINAL_URL'] ) ) {
			$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_ORIGINAL_URL'];
		}
		// IIS Isapi_Rewrite
		elseif ( isset( $_SERVER['HTTP_X_REWRITE_URL'] ) ) {
			$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_REWRITE_URL'];
		} else {
			// Use ORIG_PATH_INFO if there is no PATH_INFO
			if ( !isset( $_SERVER['PATH_INFO'] ) && isset( $_SERVER['ORIG_PATH_INFO'] ) )
				$_SERVER['PATH_INFO'] = $_SERVER['ORIG_PATH_INFO'];

			// Some IIS + PHP configurations puts the script-name in the path-info (No need to append it twice)
			if ( isset( $_SERVER['PATH_INFO'] ) ) {
				if ( $_SERVER['PATH_INFO'] == $_SERVER['SCRIPT_NAME'] )
					$_SERVER['REQUEST_URI'] = $_SERVER['PATH_INFO'];
				else
					$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'] . $_SERVER['PATH_INFO'];
			}

			// Append the query string if it exists and isn't null
			if ( ! empty( $_SERVER['QUERY_STRING'] ) ) {
				$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
			}
		}
	}

	// Fix for PHP as CGI hosts that set SCRIPT_FILENAME to something ending in php.cgi for all requests
	if ( isset( $_SERVER['SCRIPT_FILENAME'] ) && ( strpos( $_SERVER['SCRIPT_FILENAME'], 'php.cgi' ) == strlen( $_SERVER['SCRIPT_FILENAME'] ) - 7 ) )
		$_SERVER['SCRIPT_FILENAME'] = $_SERVER['PATH_TRANSLATED'];

	// Fix for Dreamhost and other PHP as CGI hosts
	if ( strpos( $_SERVER['SCRIPT_NAME'], 'php.cgi' ) !== false )
		unset( $_SERVER['PATH_INFO'] );

	// Fix empty PHP_SELF
	$PHP_SELF = $_SERVER['PHP_SELF'];
	if ( empty( $PHP_SELF ) )
		$_SERVER['PHP_SELF'] = $PHP_SELF = preg_replace( '/(\?.*)?$/', '', $_SERVER["REQUEST_URI"] );
}

/**
 * Check for the required PHP version, and the MySQL extension or
 * a database drop-in.
 *
 * Dies if requirements are not met.
 *
 * @since 3.0.0
 * @access private
 *
 * @global string $required_php_version The required PHP version string.
 * @global string $mn_version           The Mtaandao version string.
 */
function mn_check_php_mysql_versions() {
	global $required_php_version, $mn_version;
	$php_version = phpversion();

	if ( version_compare( $required_php_version, $php_version, '>' ) ) {
		mn_load_translations_early();

		$protocol = mn_get_server_protocol();
		header( sprintf( '%s 500 Internal Server Error', $protocol ), true, 500 );
		header( 'Content-Type: text/html; charset=utf-8' );
		/* translators: 1: Current PHP version number, 2: Mtaandao version number, 3: Minimum required PHP version number */
		die( sprintf( __( 'Your server is running PHP version %1$s but Mtaandao %2$s requires at least %3$s.' ), $php_version, $mn_version, $required_php_version ) );
	}

	if ( ! extension_loaded( 'mysql' ) && ! extension_loaded( 'mysqli' ) && ! extension_loaded( 'mysqlnd' ) && ! file_exists( MAIN_DIR . '/db.php' ) ) {
		mn_load_translations_early();

		$protocol = mn_get_server_protocol();
		header( sprintf( '%s 500 Internal Server Error', $protocol ), true, 500 );
		header( 'Content-Type: text/html; charset=utf-8' );
		die( __( 'Your PHP installation appears to be missing the MySQL extension which is required by Mtaandao.' ) );
	}
}

/**
 * Don't load all of Mtaandao when handling a favicon.ico request.
 *
 * Instead, send the headers for a zero-length favicon and bail.
 *
 * @since 3.0.0
 */
function mn_favicon_request() {
	if ( '/favicon.ico' == $_SERVER['REQUEST_URI'] ) {
		header('Content-Type: image/vnd.microsoft.icon');
		exit;
	}
}

/**
 * Die with a maintenance message when conditions are met.
 *
 * Checks for a file in the Mtaandao root directory named ".maintenance".
 * This file will contain the variable $upgrading, set to the time the file
 * was created. If the file was created less than 10 minutes ago, Mtaandao
 * enters maintenance mode and displays a message.
 *
 * The default message can be replaced by using a drop-in (maintenance.php in
 * the main directory).
 *
 * @since 3.0.0
 * @access private
 *
 * @global int $upgrading the unix timestamp marking when upgrading Mtaandao began.
 */
function mn_maintenance() {
	if ( ! file_exists( ABSPATH . '.maintenance' ) || mn_installing() )
		return;

	global $upgrading;

	include( ABSPATH . '.maintenance' );
	// If the $upgrading timestamp is older than 10 minutes, don't die.
	if ( ( time() - $upgrading ) >= 600 )
		return;

	/**
	 * Filters whether to enable maintenance mode.
	 *
	 * This filter runs before it can be used by plugins. It is designed for
	 * non-web runtimes. If this filter returns true, maintenance mode will be
	 * active and the request will end. If false, the request will be allowed to
	 * continue processing even if maintenance mode should be active.
	 *
	 * @since 4.6.0
	 *
	 * @param bool $enable_checks Whether to enable maintenance mode. Default true.
	 * @param int  $upgrading     The timestamp set in the .maintenance file.
	 */
	if ( ! apply_filters( 'enable_maintenance_mode', true, $upgrading ) ) {
		return;
	}

	if ( file_exists( MAIN_DIR . '/maintenance.php' ) ) {
		require_once( MAIN_DIR . '/maintenance.php' );
		die();
	}

	mn_load_translations_early();

	$protocol = mn_get_server_protocol();
	header( "$protocol 503 Service Unavailable", true, 503 );
	header( 'Content-Type: text/html; charset=utf-8' );
	header( 'Retry-After: 600' );
?>
	<!DOCTYPE html>
	<html xmlns="http://www.w3.org/1999/xhtml"<?php if ( is_rtl() ) echo ' dir="rtl"'; ?>>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title><?php _e( 'Maintenance' ); ?></title>

	</head>
	<body>
		<h1><?php _e( 'Briefly unavailable for scheduled maintenance. Check back in a minute.' ); ?></h1>
	</body>
	</html>
<?php
	die();
}

/**
 * Start the Mtaandao micro-timer.
 *
 * @since 0.71
 * @access private
 *
 * @global float $timestart Unix timestamp set at the beginning of the page load.
 * @see timer_stop()
 *
 * @return bool Always returns true.
 */
function timer_start() {
	global $timestart;
	$timestart = microtime( true );
	return true;
}

/**
 * Retrieve or display the time from the page start to when function is called.
 *
 * @since 0.71
 *
 * @global float   $timestart Seconds from when timer_start() is called.
 * @global float   $timeend   Seconds from when function is called.
 *
 * @param int|bool $display   Whether to echo or return the results. Accepts 0|false for return,
 *                            1|true for echo. Default 0|false.
 * @param int      $precision The number of digits from the right of the decimal to display.
 *                            Default 3.
 * @return string The "second.microsecond" finished time calculation. The number is formatted
 *                for human consumption, both localized and rounded.
 */
function timer_stop( $display = 0, $precision = 3 ) {
	global $timestart, $timeend;
	$timeend = microtime( true );
	$timetotal = $timeend - $timestart;
	$r = ( function_exists( 'number_format_i18n' ) ) ? number_format_i18n( $timetotal, $precision ) : number_format( $timetotal, $precision );
	if ( $display )
		echo $r;
	return $r;
}

/**
 * Set PHP error reporting based on Mtaandao debug settings.
 *
 * Uses three constants: `MN_DEBUG`, `MN_DEBUG_DISPLAY`, and `MN_DEBUG_LOG`.
 * All three can be defined in db.php. By default, `MN_DEBUG` and
 * `MN_DEBUG_LOG` are set to false, and `MN_DEBUG_DISPLAY` is set to true.
 *
 * When `MN_DEBUG` is true, all PHP notices are reported. Mtaandao will also
 * display internal notices: when a deprecated Mtaandao function, function
 * argument, or file is used. Deprecated code may be removed from a later
 * version.
 *
 * It is strongly recommended that plugin and theme developers use `MN_DEBUG`
 * in their development environments.
 *
 * `MN_DEBUG_DISPLAY` and `MN_DEBUG_LOG` perform no function unless `MN_DEBUG`
 * is true.
 *
 * When `MN_DEBUG_DISPLAY` is true, Mtaandao will force errors to be displayed.
 * `MN_DEBUG_DISPLAY` defaults to true. Defining it as null prevents Mtaandao
 * from changing the global configuration setting. Defining `MN_DEBUG_DISPLAY`
 * as false will force errors to be hidden.
 *
 * When `MN_DEBUG_LOG` is true, errors will be logged to debug.log in the content
 * directory.
 *
 * Errors are never displayed for XML-RPC, REST, and Ajax requests.
 *
 * @since 3.0.0
 * @access private
 */
function mn_debug_mode() {
	/**
	 * Filters whether to allow the debug mode check to occur.
	 *
	 * This filter runs before it can be used by plugins. It is designed for
	 * non-web run-times. Returning false causes the `MN_DEBUG` and related
	 * constants to not be checked and the default php values for errors
	 * will be used unless you take care to update them yourself.
	 *
	 * @since 4.6.0
	 *
	 * @param bool $enable_debug_mode Whether to enable debug mode checks to occur. Default true.
	 */
	if ( ! apply_filters( 'enable_mn_debug_mode_checks', true ) ){
		return;
	}

	if ( MN_DEBUG ) {
		error_reporting( E_ALL );

		if ( MN_DEBUG_DISPLAY )
			ini_set( 'display_errors', 1 );
		elseif ( null !== MN_DEBUG_DISPLAY )
			ini_set( 'display_errors', 0 );

		if ( MN_DEBUG_LOG ) {
			ini_set( 'log_errors', 1 );
			ini_set( 'error_log', MAIN_DIR . '/debug.log' );
		}
	} else {
		error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );
	}

	if ( defined( 'XMLRPC_REQUEST' ) || defined( 'REST_REQUEST' ) || ( defined( 'MN_INSTALLING' ) && MN_INSTALLING ) || mn_doing_ajax() ) {
		@ini_set( 'display_errors', 0 );
	}
}

/**
 * Set the location of the language directory.
 *
 * To set directory manually, define the `MN_LANG_DIR` constant
 * in db.php.
 *
 * If the language directory exists within `MAIN_DIR`, it
 * is used. Otherwise the language directory is assumed to live
 * in `RES`.
 *
 * @since 3.0.0
 * @access private
 */
function mn_set_lang_dir() {
	if ( !defined( 'MN_LANG_DIR' ) ) {
		if ( file_exists( MAIN_DIR . '/languages' ) && @is_dir( MAIN_DIR . '/languages' ) || !@is_dir(ABSPATH . RES . '/languages') ) {
			/**
			 * Server path of the language directory.
			 *
			 * No leading slash, no trailing slash, full path, not relative to ABSPATH
			 *
			 * @since 2.1.0
			 */
			define( 'MN_LANG_DIR', MAIN_DIR . '/languages' );
			if ( !defined( 'LANGDIR' ) ) {
				// Old static relative path maintained for limited backward compatibility - won't work in some cases.
				define( 'LANGDIR', 'main/languages' );
			}
		} else {
			/**
			 * Server path of the language directory.
			 *
			 * No leading slash, no trailing slash, full path, not relative to `ABSPATH`.
			 *
			 * @since 2.1.0
			 */
			define( 'MN_LANG_DIR', ABSPATH . RES . '/languages' );
			if ( !defined( 'LANGDIR' ) ) {
				// Old relative path maintained for backward compatibility.
				define( 'LANGDIR', RES . '/languages' );
			}
		}
	}
}

/**
 * Load the database class file and instantiate the `$mndb` global.
 *
 * @since 2.5.0
 *
 * @global mndb $mndb The Mtaandao database class.
 */
function require_mn_db() {
	global $mndb;

	require_once( ABSPATH . RES . '/mn-db.php' );
	if ( file_exists( MAIN_DIR . '/db.php' ) )
		require_once( MAIN_DIR . '/db.php' );

	if ( isset( $mndb ) ) {
		return;
	}

	$mndb = new mndb( DB_USER, DB_PASSWORD, DB_NAME, DB_HOST );
}

/**
 * Set the database table prefix and the format specifiers for database
 * table columns.
 *
 * Columns not listed here default to `%s`.
 *
 * @since 3.0.0
 * @access private
 *
 * @global mndb   $mndb         The Mtaandao database class.
 * @global string $table_prefix The database table prefix.
 */
function mn_set_mndb_vars() {
	global $mndb, $table_prefix;
	if ( !empty( $mndb->error ) )
		dead_db();

	$mndb->field_types = array( 'post_author' => '%d', 'post_parent' => '%d', 'menu_order' => '%d', 'term_id' => '%d', 'term_group' => '%d', 'term_taxonomy_id' => '%d',
		'parent' => '%d', 'count' => '%d','object_id' => '%d', 'term_order' => '%d', 'ID' => '%d', 'comment_ID' => '%d', 'comment_post_ID' => '%d', 'comment_parent' => '%d',
		'user_id' => '%d', 'link_id' => '%d', 'link_owner' => '%d', 'link_rating' => '%d', 'option_id' => '%d', 'blog_id' => '%d', 'meta_id' => '%d', 'post_id' => '%d',
		'user_status' => '%d', 'umeta_id' => '%d', 'comment_karma' => '%d', 'comment_count' => '%d',
		// multisite:
		'active' => '%d', 'cat_id' => '%d', 'deleted' => '%d', 'lang_id' => '%d', 'mature' => '%d', 'public' => '%d', 'site_id' => '%d', 'spam' => '%d',
	);

	$prefix = $mndb->set_prefix( $table_prefix );

	if ( is_mn_error( $prefix ) ) {
		mn_load_translations_early();
		mn_die(
			/* translators: 1: $table_prefix 2: db.php */
			sprintf( __( '<strong>ERROR</strong>: %1$s in %2$s can only contain numbers, letters, and underscores.' ),
				'<code>$table_prefix</code>',
				'<code>db.php</code>'
			)
		);
	}
}

/**
 * Toggle `$_mn_using_ext_object_cache` on and off without directly
 * touching global.
 *
 * @since 3.7.0
 *
 * @global bool $_mn_using_ext_object_cache
 *
 * @param bool $using Whether external object cache is being used.
 * @return bool The current 'using' setting.
 */
function mn_using_ext_object_cache( $using = null ) {
	global $_mn_using_ext_object_cache;
	$current_using = $_mn_using_ext_object_cache;
	if ( null !== $using )
		$_mn_using_ext_object_cache = $using;
	return $current_using;
}

/**
 * Start the Mtaandao object cache.
 *
 * If an object-cache.php file exists in the main directory,
 * it uses that drop-in as an external object cache.
 *
 * @since 3.0.0
 * @access private
 */
function mn_start_object_cache() {
	$first_init = false;
 	if ( ! function_exists( 'mn_cache_init' ) ) {
		if ( file_exists( MAIN_DIR . '/object-cache.php' ) ) {
			require_once ( MAIN_DIR . '/object-cache.php' );
			if ( function_exists( 'mn_cache_init' ) ) {
				mn_using_ext_object_cache( true );
			}
		}

		$first_init = true;
	} elseif ( ! mn_using_ext_object_cache() && file_exists( MAIN_DIR . '/object-cache.php' ) ) {
		/*
		 * Sometimes advanced-cache.php can load object-cache.php before
		 * it is loaded here. This breaks the function_exists check above
		 * and can result in `$_mn_using_ext_object_cache` being set
		 * incorrectly. Double check if an external cache exists.
		 */
		mn_using_ext_object_cache( true );
	}

	if ( ! mn_using_ext_object_cache() ) {
		require_once ( ABSPATH . RES . '/cache.php' );
	}

	/*
	 * If cache supports reset, reset instead of init if already
	 * initialized. Reset signals to the cache that global IDs
	 * have changed and it may need to update keys and cleanup caches.
	 */
	if ( ! $first_init && function_exists( 'mn_cache_switch_to_blog' ) ) {
		mn_cache_switch_to_blog( get_current_blog_id() );
	} elseif ( function_exists( 'mn_cache_init' ) ) {
		mn_cache_init();
	}

	if ( function_exists( 'mn_cache_add_global_groups' ) ) {
		mn_cache_add_global_groups( array( 'users', 'userlogins', 'usermeta', 'user_meta', 'useremail', 'userslugs', 'site-transient', 'site-options', 'site-lookup', 'blog-lookup', 'blog-details', 'site-details', 'rss', 'global-posts', 'blog-id-cache', 'networks', 'sites' ) );
		mn_cache_add_non_persistent_groups( array( 'counts', 'plugins' ) );
	}
}

/**
 * Redirect to the installer if Mtaandao is not installed.
 *
 * Dies with an error message when Multisite is enabled.
 *
 * @since 3.0.0
 * @access private
 */
function mn_not_installed() {
	if ( is_multisite() ) {
		if ( ! is_blog_installed() && ! mn_installing() ) {
			nocache_headers();

			mn_die( __( 'The site you have requested is not installed properly. Please contact the system administrator.' ) );
		}
	} elseif ( ! is_blog_installed() && ! mn_installing() ) {
		nocache_headers();

		require( ABSPATH . RES . '/kses.php' );
		require( ABSPATH . RES . '/pluggable.php' );
		require( ABSPATH . RES . '/formatting.php' );

		$link = mn_guess_url() . '/admin/install.php';

		mn_redirect( $link );
		die();
	}
}

/**
 * Retrieve an array of must-use plugin files.
 *
 * The default directory is main/mu-plugins. To change the default
 * directory manually, define `MNMU_PLUGIN_DIR` and `MU_PLUGIN_URL`
 * in db.php.
 *
 * @since 3.0.0
 * @access private
 *
 * @return array Files to include.
 */
function mn_get_mu_plugins() {
	$mu_plugins = array();
	if ( !is_dir( MNMU_PLUGIN_DIR ) )
		return $mu_plugins;
	if ( ! $dh = opendir( MNMU_PLUGIN_DIR ) )
		return $mu_plugins;
	while ( ( $plugin = readdir( $dh ) ) !== false ) {
		if ( substr( $plugin, -4 ) == '.php' )
			$mu_plugins[] = MNMU_PLUGIN_DIR . '/' . $plugin;
	}
	closedir( $dh );
	sort( $mu_plugins );

	return $mu_plugins;
}

/**
 * Retrieve an array of active and valid plugin files.
 *
 * While upgrading or installing Mtaandao, no plugins are returned.
 *
 * The default directory is main/plugins. To change the default
 * directory manually, define `PLUGIN_DIR` and `PLUGIN_URL`
 * in db.php.
 *
 * @since 3.0.0
 * @access private
 *
 * @return array Files.
 */
function mn_get_active_and_valid_plugins() {
	$plugins = array();
	$active_plugins = (array) get_option( 'active_plugins', array() );

	// Check for hacks file if the option is enabled
	if ( get_option( 'hack_file' ) && file_exists( ABSPATH . 'my-hacks.php' ) ) {
		_deprecated_file( 'my-hacks.php', '1.5.0' );
		array_unshift( $plugins, ABSPATH . 'my-hacks.php' );
	}

	if ( empty( $active_plugins ) || mn_installing() )
		return $plugins;

	$network_plugins = is_multisite() ? mn_get_active_network_plugins() : false;

	foreach ( $active_plugins as $plugin ) {
		if ( ! validate_file( $plugin ) // $plugin must validate as file
			&& '.php' == substr( $plugin, -4 ) // $plugin must end with '.php'
			&& file_exists( PLUGIN_DIR . '/' . $plugin ) // $plugin must exist
			// not already included as a network plugin
			&& ( ! $network_plugins || ! in_array( PLUGIN_DIR . '/' . $plugin, $network_plugins ) )
			)
		$plugins[] = PLUGIN_DIR . '/' . $plugin;
	}
	return $plugins;
}

/**
 * Set internal encoding.
 *
 * In most cases the default internal encoding is latin1, which is
 * of no use, since we want to use the `mb_` functions for `utf-8` strings.
 *
 * @since 3.0.0
 * @access private
 */
function mn_set_internal_encoding() {
	if ( function_exists( 'mb_internal_encoding' ) ) {
		$charset = get_option( 'blog_charset' );
		if ( ! $charset || ! @mb_internal_encoding( $charset ) )
			mb_internal_encoding( 'UTF-8' );
	}
}

/**
 * Add magic quotes to `$_GET`, `$_POST`, `$_COOKIE`, and `$_SERVER`.
 *
 * Also forces `$_REQUEST` to be `$_GET + $_POST`. If `$_SERVER`,
 * `$_COOKIE`, or `$_ENV` are needed, use those superglobals directly.
 *
 * @since 3.0.0
 * @access private
 */
function mn_magic_quotes() {
	// If already slashed, strip.
	if ( get_magic_quotes_gpc() ) {
		$_GET    = stripslashes_deep( $_GET    );
		$_POST   = stripslashes_deep( $_POST   );
		$_COOKIE = stripslashes_deep( $_COOKIE );
	}

	// Escape with mndb.
	$_GET    = add_magic_quotes( $_GET    );
	$_POST   = add_magic_quotes( $_POST   );
	$_COOKIE = add_magic_quotes( $_COOKIE );
	$_SERVER = add_magic_quotes( $_SERVER );

	// Force REQUEST to be GET + POST.
	$_REQUEST = array_merge( $_GET, $_POST );
}

/**
 * Runs just before PHP shuts down execution.
 *
 * @since 1.2.0
 * @access private
 */
function shutdown_action_hook() {
	/**
	 * Fires just before PHP shuts down execution.
	 *
	 * @since 1.2.0
	 */
	do_action( 'shutdown' );

	mn_cache_close();
}

/**
 * Copy an object.
 *
 * @since 2.7.0
 * @deprecated 3.2.0
 *
 * @param object $object The object to clone.
 * @return object The cloned object.
 */
function mn_clone( $object ) {
	// Use parens for clone to accommodate PHP 4. See #17880
	return clone( $object );
}

/**
 * Whether the current request is for an administrative interface page.
 *
 * Does not check if the user is an administrator; current_user_can()
 * for checking roles and capabilities.
 *
 * @since 1.5.1
 *
 * @global MN_Screen $current_screen
 *
 * @return bool True if inside Mtaandao administration interface, false otherwise.
 */
function is_admin() {
	if ( isset( $GLOBALS['current_screen'] ) )
		return $GLOBALS['current_screen']->in_admin();
	elseif ( defined( 'ADMIN' ) )
		return ADMIN;

	return false;
}

/**
 * Whether the current request is for a site's admininstrative interface.
 *
 * e.g. `/admin/`
 *
 * Does not check if the user is an administrator; current_user_can()
 * for checking roles and capabilities.
 *
 * @since 3.1.0
 *
 * @global MN_Screen $current_screen
 *
 * @return bool True if inside Mtaandao blog administration pages.
 */
function is_blog_admin() {
	if ( isset( $GLOBALS['current_screen'] ) )
		return $GLOBALS['current_screen']->in_admin( 'site' );
	elseif ( defined( 'MN_BLOG_ADMIN' ) )
		return MN_BLOG_ADMIN;

	return false;
}

/**
 * Whether the current request is for the network administrative interface.
 *
 * e.g. `/admin/network/`
 *
 * Does not check if the user is an administrator; current_user_can()
 * for checking roles and capabilities.
 *
 * @since 3.1.0
 *
 * @global MN_Screen $current_screen
 *
 * @return bool True if inside Mtaandao network administration pages.
 */
function is_network_admin() {
	if ( isset( $GLOBALS['current_screen'] ) )
		return $GLOBALS['current_screen']->in_admin( 'network' );
	elseif ( defined( 'MN_NETWORK_ADMIN' ) )
		return MN_NETWORK_ADMIN;

	return false;
}

/**
 * Whether the current request is for a user admin screen.
 *
 * e.g. `/admin/user/`
 *
 * Does not inform on whether the user is an admin! Use capability
 * checks to tell if the user should be accessing a section or not
 * current_user_can().
 *
 * @since 3.1.0
 *
 * @global MN_Screen $current_screen
 *
 * @return bool True if inside Mtaandao user administration pages.
 */
function is_user_admin() {
	if ( isset( $GLOBALS['current_screen'] ) )
		return $GLOBALS['current_screen']->in_admin( 'user' );
	elseif ( defined( 'MN_USER_ADMIN' ) )
		return MN_USER_ADMIN;

	return false;
}

/**
 * If Multisite is enabled.
 *
 * @since 3.0.0
 *
 * @return bool True if Multisite is enabled, false otherwise.
 */
function is_multisite() {
	if ( defined( 'MULTISITE' ) )
		return MULTISITE;

	if ( defined( 'SUBDOMAIN_INSTALL' ) || defined( 'VHOST' ) || defined( 'SUNRISE' ) )
		return true;

	return false;
}

/**
 * Retrieve the current site ID.
 *
 * @since 3.1.0
 *
 * @global int $blog_id
 *
 * @return int Site ID.
 */
function get_current_blog_id() {
	global $blog_id;
	return absint($blog_id);
}

/**
 * Retrieves the current network ID.
 *
 * @since 4.6.0
 *
 * @return int The ID of the current network.
 */
function get_current_network_id() {
	if ( ! is_multisite() ) {
		return 1;
	}

	$current_network = get_network();

	if ( ! isset( $current_network->id ) ) {
		return get_main_network_id();
	}

	return absint( $current_network->id );
}

/**
 * Attempt an early load of translations.
 *
 * Used for errors encountered during the initial loading process, before
 * the locale has been properly detected and loaded.
 *
 * Designed for unusual load sequences (like setup-config.php) or for when
 * the script will then terminate with an error, otherwise there is a risk
 * that a file can be double-included.
 *
 * @since 3.4.0
 * @access private
 *
 * @global MN_Locale $mn_locale The Mtaandao date and time locale object.
 *
 * @staticvar bool $loaded
 */
function mn_load_translations_early() {
	global $mn_locale;

	static $loaded = false;
	if ( $loaded )
		return;
	$loaded = true;

	if ( function_exists( 'did_action' ) && did_action( 'init' ) )
		return;

	// We need $mn_local_package
	require ABSPATH . RES . '/version.php';

	// Translation and localization
	require_once ABSPATH . RES . '/pomo/mo.php';
	require_once ABSPATH . RES . '/l10n.php';
	require_once ABSPATH . RES . '/class-mn-locale.php';
	require_once ABSPATH . RES . '/class-mn-locale-switcher.php';

	// General libraries
	require_once ABSPATH . RES . '/plugin.php';

	$locales = $locations = array();

	while ( true ) {
		if ( defined( 'MNLANG' ) ) {
			if ( '' == MNLANG )
				break;
			$locales[] = MNLANG;
		}

		if ( isset( $mn_local_package ) )
			$locales[] = $mn_local_package;

		if ( ! $locales )
			break;

		if ( defined( 'MN_LANG_DIR' ) && @is_dir( MN_LANG_DIR ) )
			$locations[] = MN_LANG_DIR;

		if ( defined( 'MAIN_DIR' ) && @is_dir( MAIN_DIR . '/languages' ) )
			$locations[] = MAIN_DIR . '/languages';

		if ( @is_dir( ABSPATH . 'main/languages' ) )
			$locations[] = ABSPATH . 'main/languages';

		if ( @is_dir( ABSPATH . RES . '/languages' ) )
			$locations[] = ABSPATH . RES . '/languages';

		if ( ! $locations )
			break;

		$locations = array_unique( $locations );

		foreach ( $locales as $locale ) {
			foreach ( $locations as $location ) {
				if ( file_exists( $location . '/' . $locale . '.mo' ) ) {
					load_textdomain( 'default', $location . '/' . $locale . '.mo' );
					if ( defined( 'MN_SETUP_CONFIG' ) && file_exists( $location . '/admin-' . $locale . '.mo' ) )
						load_textdomain( 'default', $location . '/admin-' . $locale . '.mo' );
					break 2;
				}
			}
		}

		break;
	}

	$mn_locale = new MN_Locale();
}

/**
 * Check or set whether Mtaandao is in "installation" mode.
 *
 * If the `MN_INSTALLING` constant is defined during the bootstrap, `mn_installing()` will default to `true`.
 *
 * @since 4.4.0
 *
 * @staticvar bool $installing
 *
 * @param bool $is_installing Optional. True to set MN into Installing mode, false to turn Installing mode off.
 *                            Omit this parameter if you only want to fetch the current status.
 * @return bool True if MN is installing, otherwise false. When a `$is_installing` is passed, the function will
 *              report whether MN was in installing mode prior to the change to `$is_installing`.
 */
function mn_installing( $is_installing = null ) {
	static $installing = null;

	// Support for the `MN_INSTALLING` constant, defined before MN is loaded.
	if ( is_null( $installing ) ) {
		$installing = defined( 'MN_INSTALLING' ) && MN_INSTALLING;
	}

	if ( ! is_null( $is_installing ) ) {
		$old_installing = $installing;
		$installing = $is_installing;
		return (bool) $old_installing;
	}

	return (bool) $installing;
}

/**
 * Determines if SSL is used.
 *
 * @since 2.6.0
 * @since 4.6.0 Moved from functions.php to load.php.
 *
 * @return bool True if SSL, otherwise false.
 */
function is_ssl() {
	if ( isset( $_SERVER['HTTPS'] ) ) {
		if ( 'on' == strtolower( $_SERVER['HTTPS'] ) ) {
			return true;
		}

		if ( '1' == $_SERVER['HTTPS'] ) {
			return true;
		}
	} elseif ( isset($_SERVER['SERVER_PORT'] ) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
		return true;
	}
	return false;
}

/**
 * Converts a shorthand byte value to an integer byte value.
 *
 * @since 2.3.0
 * @since 4.6.0 Moved from media.php to load.php.
 *
 * @link https://secure.php.net/manual/en/function.ini-get.php
 * @link https://secure.php.net/manual/en/faq.using.php#faq.using.shorthandbytes
 *
 * @param string $value A (PHP ini) byte value, either shorthand or ordinary.
 * @return int An integer byte value.
 */
function mn_convert_hr_to_bytes( $value ) {
	$value = strtolower( trim( $value ) );
	$bytes = (int) $value;

	if ( false !== strpos( $value, 'g' ) ) {
		$bytes *= GB_IN_BYTES;
	} elseif ( false !== strpos( $value, 'm' ) ) {
		$bytes *= MB_IN_BYTES;
	} elseif ( false !== strpos( $value, 'k' ) ) {
		$bytes *= KB_IN_BYTES;
	}

	// Deal with large (float) values which run into the maximum integer size.
	return min( $bytes, PHP_INT_MAX );
}

/**
 * Determines whether a PHP ini value is changeable at runtime.
 *
 * @since 4.6.0
 *
 * @link https://secure.php.net/manual/en/function.ini-get-all.php
 *
 * @param string $setting The name of the ini setting to check.
 * @return bool True if the value is changeable at runtime. False otherwise.
 */
function mn_is_ini_value_changeable( $setting ) {
	static $ini_all;

	if ( ! isset( $ini_all ) ) {
		$ini_all = false;
		// Sometimes `ini_get_all()` is disabled via the `disable_functions` option for "security purposes".
		if ( function_exists( 'ini_get_all' ) ) {
			$ini_all = ini_get_all();
		}
 	}

	// Bit operator to workaround https://bugs.php.net/bug.php?id=44936 which changes access level to 63 in PHP 5.2.6 - 5.2.17.
	if ( isset( $ini_all[ $setting ]['access'] ) && ( INI_ALL === ( $ini_all[ $setting ]['access'] & 7 ) || INI_USER === ( $ini_all[ $setting ]['access'] & 7 ) ) ) {
		return true;
	}

	// If we were unable to retrieve the details, fail gracefully to assume it's changeable.
	if ( ! is_array( $ini_all ) ) {
		return true;
	}

	return false;
}

/**
 * Determines whether the current request is a Mtaandao Ajax request.
 *
 * @since 4.7.0
 *
 * @return bool True if it's a Mtaandao Ajax request, false otherwise.
 */
function mn_doing_ajax() {
	/**
	 * Filters whether the current request is a Mtaandao Ajax request.
	 *
	 * @since 4.7.0
	 *
	 * @param bool $mn_doing_ajax Whether the current request is a Mtaandao Ajax request.
	 */
	return apply_filters( 'mn_doing_ajax', defined( 'DOING_AJAX' ) && DOING_AJAX );
}

/**
 * Check whether variable is a Mtaandao Error.
 *
 * Returns true if $thing is an object of the MN_Error class.
 *
 * @since 2.1.0
 *
 * @param mixed $thing Check if unknown variable is a MN_Error object.
 * @return bool True, if MN_Error. False, if not MN_Error.
 */
function is_mn_error( $thing ) {
	return ( $thing instanceof MN_Error );
}
