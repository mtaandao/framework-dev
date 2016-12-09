<?php
/**
 * Used to set up and fix common variables and include
 * the Mtaandao procedural and class library.
 *
 * Allows for some configuration in db.php (see default-constants.php)
 *
 * @package Mtaandao
 */

/**
 * Stores the location of the Mtaandao directory of functions, classes, and core content.
 *
 * @since 1.0.0
 */
define( 'RES', 'res' );

// Include files required for initialization.
require( ABSPATH . RES . '/load.php' );
require( ABSPATH . RES . '/default-constants.php' );
require_once( ABSPATH . RES . '/plugin.php' );

/*
 * These can't be directly globalized in version.php. When updating,
 * we're including version.php from another install and don't want
 * these values to be overridden if already set.
 */
global $mn_version, $mn_db_version, $tinymce_version, $required_php_version, $required_mysql_version, $mn_local_package;
require( ABSPATH . RES . '/version.php' );

/**
 * If not already configured, `$blog_id` will default to 1 in a single site
 * configuration. In multisite, it will be overridden by default in ms-settings.php.
 *
 * @global int $blog_id
 * @since 2.0.0
 */
global $blog_id;

// Set initial default constants including MN_MEMORY_LIMIT, MN_MAX_MEMORY_LIMIT, MN_DEBUG, SCRIPT_DEBUG, MAIN_DIR and MN_CACHE.
mn_initial_constants();

// Check for the required PHP version and for the MySQL extension or a database drop-in.
mn_check_php_mysql_versions();

// Disable magic quotes at runtime. Magic quotes are added using mndb later in settings.php.
@ini_set( 'magic_quotes_runtime', 0 );
@ini_set( 'magic_quotes_sybase',  0 );

// Mtaandao calculates offsets from UTC.
date_default_timezone_set( 'UTC' );

// Turn register_globals off.
mn_unregister_GLOBALS();

// Standardize $_SERVER variables across setups.
mn_fix_server_vars();

// Check if we have received a request due to missing favicon.ico
mn_favicon_request();

// Check if we're in maintenance mode.
mn_maintenance();

// Start loading timer.
timer_start();

// Check if we're in MN_DEBUG mode.
mn_debug_mode();

/**
 * Filters whether to enable loading of the advanced-cache.php drop-in.
 *
 * This filter runs before it can be used by plugins. It is designed for non-web
 * run-times. If false is returned, advanced-cache.php will never be loaded.
 *
 * @since 4.6.0
 *
 * @param bool $enable_advanced_cache Whether to enable loading advanced-cache.php (if present).
 *                                    Default true.
 */
if ( MN_CACHE && apply_filters( 'enable_loading_advanced_cache_dropin', true ) ) {
	// For an advanced caching plugin to use. Uses a static drop-in because you would only want one.
	MN_DEBUG ? include( MAIN_DIR . '/advanced-cache.php' ) : @include( MAIN_DIR . '/advanced-cache.php' );

	// Re-initialize any hooks added manually by advanced-cache.php
	if ( $mn_filter ) {
		$mn_filter = MN_Hook::build_preinitialized_hooks( $mn_filter );
	}
}

// Define MN_LANG_DIR if not set.
mn_set_lang_dir();

// Load early Mtaandao files.
require( ABSPATH . RES . '/compat.php' );
require( ABSPATH . RES . '/class-mn-list-util.php' );
require( ABSPATH . RES . '/functions.php' );
require( ABSPATH . RES . '/class-mn-matchesmapregex.php' );
require( ABSPATH . RES . '/class-mn.php' );
require( ABSPATH . RES . '/class-mn-error.php' );
require( ABSPATH . RES . '/pomo/mo.php' );
require( ABSPATH . RES . '/class-phpass.php' );

// Include the mndb class and, if present, a db.php database drop-in.
global $mndb;
require_mn_db();

// Set the database table prefix and the format specifiers for database table columns.
$GLOBALS['table_prefix'] = $table_prefix;
mn_set_mndb_vars();

// Start the Mtaandao object cache, or an external object cache if the drop-in is present.
mn_start_object_cache();

// Attach the default filters.
require( ABSPATH . RES . '/default-filters.php' );

// Initialize multisite if enabled.
if ( is_multisite() ) {
	require( ABSPATH . RES . '/class-mn-site-query.php' );
	require( ABSPATH . RES . '/class-mn-network-query.php' );
	require( ABSPATH . RES . '/ms-blogs.php' );
	require( ABSPATH . RES . '/ms-settings.php' );
} elseif ( ! defined( 'MULTISITE' ) ) {
	define( 'MULTISITE', false );
}

register_shutdown_function( 'shutdown_action_hook' );

// Stop most of Mtaandao from being loaded if we just want the basics.
if ( SHORTINIT )
	return false;

// Load the L10n library.
require_once( ABSPATH . RES . '/l10n.php' );
require_once( ABSPATH . RES . '/class-mn-locale.php' );
require_once( ABSPATH . RES . '/class-mn-locale-switcher.php' );

// Run the installer if Mtaandao is not installed.
mn_not_installed();

// Load most of Mtaandao.
require( ABSPATH . RES . '/class-mn-walker.php' );
require( ABSPATH . RES . '/class-mn-ajax-response.php' );
require( ABSPATH . RES . '/formatting.php' );
require( ABSPATH . RES . '/capabilities.php' );
require( ABSPATH . RES . '/class-mn-roles.php' );
require( ABSPATH . RES . '/class-mn-role.php' );
require( ABSPATH . RES . '/class-mn-user.php' );
require( ABSPATH . RES . '/class-mn-query.php' );
require( ABSPATH . RES . '/query.php' );
require( ABSPATH . RES . '/date.php' );
require( ABSPATH . RES . '/theme.php' );
require( ABSPATH . RES . '/class-mn-theme.php' );
require( ABSPATH . RES . '/template.php' );
require( ABSPATH . RES . '/user.php' );
require( ABSPATH . RES . '/class-mn-user-query.php' );
require( ABSPATH . RES . '/class-mn-session-tokens.php' );
require( ABSPATH . RES . '/class-mn-user-meta-session-tokens.php' );
require( ABSPATH . RES . '/meta.php' );
require( ABSPATH . RES . '/class-mn-meta-query.php' );
require( ABSPATH . RES . '/class-mn-metadata-lazyloader.php' );
require( ABSPATH . RES . '/general-template.php' );
require( ABSPATH . RES . '/link-template.php' );
require( ABSPATH . RES . '/author-template.php' );
require( ABSPATH . RES . '/post.php' );
require( ABSPATH . RES . '/class-walker-page.php' );
require( ABSPATH . RES . '/class-walker-page-dropdown.php' );
require( ABSPATH . RES . '/class-mn-post-type.php' );
require( ABSPATH . RES . '/class-mn-post.php' );
require( ABSPATH . RES . '/post-template.php' );
require( ABSPATH . RES . '/revision.php' );
require( ABSPATH . RES . '/post-formats.php' );
require( ABSPATH . RES . '/post-thumbnail-template.php' );
require( ABSPATH . RES . '/category.php' );
require( ABSPATH . RES . '/class-walker-category.php' );
require( ABSPATH . RES . '/class-walker-category-dropdown.php' );
require( ABSPATH . RES . '/category-template.php' );
require( ABSPATH . RES . '/comment.php' );
require( ABSPATH . RES . '/class-mn-comment.php' );
require( ABSPATH . RES . '/class-mn-comment-query.php' );
require( ABSPATH . RES . '/class-walker-comment.php' );
require( ABSPATH . RES . '/comment-template.php' );
require( ABSPATH . RES . '/rewrite.php' );
require( ABSPATH . RES . '/class-mn-rewrite.php' );
require( ABSPATH . RES . '/feed.php' );
require( ABSPATH . RES . '/bookmark.php' );
require( ABSPATH . RES . '/bookmark-template.php' );
require( ABSPATH . RES . '/kses.php' );
require( ABSPATH . RES . '/cron.php' );
require( ABSPATH . RES . '/deprecated.php' );
require( ABSPATH . RES . '/script-loader.php' );
require( ABSPATH . RES . '/taxonomy.php' );
require( ABSPATH . RES . '/class-mn-taxonomy.php' );
require( ABSPATH . RES . '/class-mn-term.php' );
require( ABSPATH . RES . '/class-mn-term-query.php' );
require( ABSPATH . RES . '/class-mn-tax-query.php' );
require( ABSPATH . RES . '/update.php' );
require( ABSPATH . RES . '/canonical.php' );
require( ABSPATH . RES . '/shortcodes.php' );
require( ABSPATH . RES . '/embed.php' );
require( ABSPATH . RES . '/class-mn-embed.php' );
require( ABSPATH . RES . '/class-oembed.php' );
require( ABSPATH . RES . '/class-mn-oembed-controller.php' );
require( ABSPATH . RES . '/media.php' );
require( ABSPATH . RES . '/http.php' );
require( ABSPATH . RES . '/class-http.php' );
require( ABSPATH . RES . '/class-mn-http-streams.php' );
require( ABSPATH . RES . '/class-mn-http-curl.php' );
require( ABSPATH . RES . '/class-mn-http-proxy.php' );
require( ABSPATH . RES . '/class-mn-http-cookie.php' );
require( ABSPATH . RES . '/class-mn-http-encoding.php' );
require( ABSPATH . RES . '/class-mn-http-response.php' );
require( ABSPATH . RES . '/class-mn-http-requests-response.php' );
require( ABSPATH . RES . '/class-mn-http-requests-hooks.php' );
require( ABSPATH . RES . '/widgets.php' );
require( ABSPATH . RES . '/class-mn-widget.php' );
require( ABSPATH . RES . '/class-mn-widget-factory.php' );
require( ABSPATH . RES . '/nav-menu.php' );
require( ABSPATH . RES . '/nav-menu-template.php' );
require( ABSPATH . RES . '/admin-bar.php' );
require( ABSPATH . RES . '/rest-api.php' );
require( ABSPATH . RES . '/rest-api/class-mn-rest-server.php' );
require( ABSPATH . RES . '/rest-api/class-mn-rest-response.php' );
require( ABSPATH . RES . '/rest-api/class-mn-rest-request.php' );
require( ABSPATH . RES . '/rest-api/endpoints/class-mn-rest-controller.php' );
require( ABSPATH . RES . '/rest-api/endpoints/class-mn-rest-posts-controller.php' );
require( ABSPATH . RES . '/rest-api/endpoints/class-mn-rest-attachments-controller.php' );
require( ABSPATH . RES . '/rest-api/endpoints/class-mn-rest-post-types-controller.php' );
require( ABSPATH . RES . '/rest-api/endpoints/class-mn-rest-post-statuses-controller.php' );
require( ABSPATH . RES . '/rest-api/endpoints/class-mn-rest-revisions-controller.php' );
require( ABSPATH . RES . '/rest-api/endpoints/class-mn-rest-taxonomies-controller.php' );
require( ABSPATH . RES . '/rest-api/endpoints/class-mn-rest-terms-controller.php' );
require( ABSPATH . RES . '/rest-api/endpoints/class-mn-rest-users-controller.php' );
require( ABSPATH . RES . '/rest-api/endpoints/class-mn-rest-comments-controller.php' );
require( ABSPATH . RES . '/rest-api/endpoints/class-mn-rest-settings-controller.php' );
require( ABSPATH . RES . '/rest-api/fields/class-mn-rest-meta-fields.php' );
require( ABSPATH . RES . '/rest-api/fields/class-mn-rest-comment-meta-fields.php' );
require( ABSPATH . RES . '/rest-api/fields/class-mn-rest-post-meta-fields.php' );
require( ABSPATH . RES . '/rest-api/fields/class-mn-rest-term-meta-fields.php' );
require( ABSPATH . RES . '/rest-api/fields/class-mn-rest-user-meta-fields.php' );

$GLOBALS['mn_embed'] = new MN_Embed();

// Load multisite-specific files.
if ( is_multisite() ) {
	require( ABSPATH . RES . '/ms-functions.php' );
	require( ABSPATH . RES . '/ms-default-filters.php' );
	require( ABSPATH . RES . '/ms-deprecated.php' );
}

// Define constants that rely on the API to obtain the default value.
// Define must-use plugin directory constants, which may be overridden in the sunrise.php drop-in.
mn_plugin_directory_constants();

$GLOBALS['mn_plugin_paths'] = array();

// Load must-use plugins.
foreach ( mn_get_mu_plugins() as $mu_plugin ) {
	include_once( $mu_plugin );
}
unset( $mu_plugin );

// Load network activated plugins.
if ( is_multisite() ) {
	foreach ( mn_get_active_network_plugins() as $network_plugin ) {
		mn_register_plugin_realpath( $network_plugin );
		include_once( $network_plugin );
	}
	unset( $network_plugin );
}

/**
 * Fires once all must-use and network-activated plugins have loaded.
 *
 * @since 2.8.0
 */
do_action( 'muplugins_loaded' );

if ( is_multisite() )
	ms_cookie_constants(  );

// Define constants after multisite is loaded.
mn_cookie_constants();

// Define and enforce our SSL constants
mn_ssl_constants();

// Create common globals.
require( ABSPATH . RES . '/vars.php' );

// Make taxonomies and posts available to plugins and themes.
// @plugin authors: warning: these get registered again on the init hook.
create_initial_taxonomies();
create_initial_post_types();

// Register the default theme directory root
register_theme_directory( get_theme_root() );

// Load active plugins.
foreach ( mn_get_active_and_valid_plugins() as $plugin ) {
	mn_register_plugin_realpath( $plugin );
	include_once( $plugin );
}
unset( $plugin );

// Load pluggable functions.
require( ABSPATH . RES . '/pluggable.php' );
require( ABSPATH . RES . '/pluggable-deprecated.php' );

// Set internal encoding.
mn_set_internal_encoding();

// Run mn_cache_postload() if object cache is enabled and the function exists.
if ( MN_CACHE && function_exists( 'mn_cache_postload' ) )
	mn_cache_postload();

/**
 * Fires once activated plugins have loaded.
 *
 * Pluggable functions are also available at this point in the loading order.
 *
 * @since 1.5.0
 */
do_action( 'plugins_loaded' );

// Define constants which affect functionality if not already defined.
mn_functionality_constants();

// Add magic quotes and set up $_REQUEST ( $_GET + $_POST )
mn_magic_quotes();

/**
 * Fires when comment cookies are sanitized.
 *
 * @since 2.0.11
 */
do_action( 'sanitize_comment_cookies' );

/**
 * Mtaandao Query object
 * @global MN_Query $mn_the_query
 * @since 2.0.0
 */
$GLOBALS['mn_the_query'] = new MN_Query();

/**
 * Holds the reference to @see $mn_the_query
 * Use this global for Mtaandao queries
 * @global MN_Query $mn_query
 * @since 1.5.0
 */
$GLOBALS['mn_query'] = $GLOBALS['mn_the_query'];

/**
 * Holds the Mtaandao Rewrite object for creating pretty URLs
 * @global MN_Rewrite $mn_rewrite
 * @since 1.5.0
 */
$GLOBALS['mn_rewrite'] = new MN_Rewrite();

/**
 * Mtaandao Object
 * @global MN $mn
 * @since 2.0.0
 */
$GLOBALS['mn'] = new MN();

/**
 * Mtaandao Widget Factory Object
 * @global MN_Widget_Factory $mn_widget_factory
 * @since 2.8.0
 */
$GLOBALS['mn_widget_factory'] = new MN_Widget_Factory();

/**
 * Mtaandao User Roles
 * @global MN_Roles $mn_roles
 * @since 2.0.0
 */
$GLOBALS['mn_roles'] = new MN_Roles();

/**
 * Fires before the theme is loaded.
 *
 * @since 2.6.0
 */
do_action( 'setup_theme' );

// Define the template related constants.
mn_templating_constants(  );

// Load the default text localization domain.
load_default_textdomain();

$locale = get_locale();
$locale_file = MN_LANG_DIR . "/$locale.php";
if ( ( 0 === validate_file( $locale ) ) && is_readable( $locale_file ) )
	require( $locale_file );
unset( $locale_file );

/**
 * Mtaandao Locale object for loading locale domain date and various strings.
 * @global MN_Locale $mn_locale
 * @since 2.1.0
 */
$GLOBALS['mn_locale'] = new MN_Locale();

/**
 *  Mtaandao Locale Switcher object for switching locales.
 *
 * @since 4.7.0
 *
 * @global MN_Locale_Switcher $mn_locale_switcher Mtaandao locale switcher object.
 */
$GLOBALS['mn_locale_switcher'] = new MN_Locale_Switcher();
$GLOBALS['mn_locale_switcher']->init();

// Load the functions for the active theme, for both parent and child theme if applicable.
if ( ! mn_installing() || 'activate.php' === $pagenow ) {
	if ( TEMPLATEPATH !== STYLESHEETPATH && file_exists( STYLESHEETPATH . '/functions.php' ) )
		include( STYLESHEETPATH . '/functions.php' );
	if ( file_exists( TEMPLATEPATH . '/functions.php' ) )
		include( TEMPLATEPATH . '/functions.php' );
}

/**
 * Fires after the theme is loaded.
 *
 * @since 3.0.0
 */
do_action( 'after_setup_theme' );

// Set up current user.
$GLOBALS['mn']->init();

/**
 * Fires after Mtaandao has finished loading but before any headers are sent.
 *
 * Most of MN is loaded at this stage, and the user is authenticated. MN continues
 * to load on the {@see 'init'} hook that follows (e.g. widgets), and many plugins instantiate
 * themselves on it for all sorts of reasons (e.g. they need a user, a taxonomy, etc.).
 *
 * If you wish to plug an action once MN is loaded, use the {@see 'mn_loaded'} hook below.
 *
 * @since 1.5.0
 */
do_action( 'init' );

// Check site status
if ( is_multisite() ) {
	if ( true !== ( $file = ms_site_check() ) ) {
		require( $file );
		die();
	}
	unset($file);
}

/**
 * This hook is fired once MN, all plugins, and the theme are fully loaded and instantiated.
 *
 * Ajax requests should use admin/admin-ajax.php. admin-ajax.php can handle requests for
 * users not logged in.
 *
 * @link https://mtaandao.github.io/AJAX_in_Plugins
 *
 * @since 3.0.0
 */
do_action( 'mn_loaded' );
