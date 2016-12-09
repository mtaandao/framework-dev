<?php
/**
 * Mtaandao Ajax Process Execution
 *
 * @package Mtaandao
 * @subpackage Administration
 *
 * @link https://mtaandao.github.io/AJAX_in_Plugins
 */

/**
 * Executing Ajax process.
 *
 * @since 2.1.0
 */
define( 'DOING_AJAX', true );
if ( ! defined( 'ADMIN' ) ) {
	define( 'ADMIN', true );
}

/** Load Mtaandao Bootstrap */
require_once( dirname( dirname( __FILE__ ) ) . '/load.php' );

/** Allow for cross-domain requests (from the front end). */
send_origin_headers();

// Require an action parameter
if ( empty( $_REQUEST['action'] ) )
	die( '0' );

/** Load Mtaandao Administration APIs */
require_once( ABSPATH . 'admin/includes/admin.php' );

/** Load Ajax Handlers for Mtaandao Core */
require_once( ABSPATH . 'admin/includes/ajax-actions.php' );

@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
@header( 'X-Robots-Tag: noindex' );

send_nosniff_header();
nocache_headers();

/** This action is documented in admin/admin.php */
do_action( 'admin_init' );

$core_actions_get = array(
	'fetch-list', 'ajax-tag-search', 'mn-compression-test', 'imgedit-preview', 'oembed-cache',
	'autocomplete-user', 'dashboard-widgets', 'logged-in',
);

$core_actions_post = array(
	'oembed-cache', 'image-editor', 'delete-comment', 'delete-tag', 'delete-link',
	'delete-meta', 'delete-post', 'trash-post', 'untrash-post', 'delete-page', 'dim-comment',
	'add-link-category', 'add-tag', 'get-tagcloud', 'get-comments', 'replyto-comment',
	'edit-comment', 'add-menu-item', 'add-meta', 'add-user', 'closed-postboxes',
	'hidden-columns', 'update-welcome-panel', 'menu-get-metabox', 'mn-link-ajax',
	'menu-locations-save', 'menu-quick-search', 'meta-box-order', 'get-permalink',
	'sample-permalink', 'inline-save', 'inline-save-tax', 'find_posts', 'widgets-order',
	'save-widget', 'delete-inactive-widgets', 'set-post-thumbnail', 'date_format', 'time_format',
	'mn-remove-post-lock', 'dismiss-mn-pointer', 'upload-attachment', 'get-attachment',
	'query-attachments', 'save-attachment', 'save-attachment-compat', 'send-link-to-editor',
	'send-attachment-to-editor', 'save-attachment-order', 'heartbeat', 'get-revision-diffs',
	'save-user-color-scheme', 'update-widget', 'query-themes', 'parse-embed', 'set-attachment-thumbnail',
	'parse-media-shortcode', 'destroy-sessions', 'install-plugin', 'update-plugin', 'press-this-save-post',
	'press-this-add-category', 'crop-image', 'generate-password', 'save-mnorg-username', 'delete-plugin',
	'search-plugins', 'search-install-plugins', 'activate-plugin', 'update-theme', 'delete-theme',
	'install-theme', 'get-post-thumbnail-html',
);

// Deprecated
$core_actions_post[] = 'mn-fullscreen-save-post';

// Register core Ajax calls.
if ( ! empty( $_GET['action'] ) && in_array( $_GET['action'], $core_actions_get ) )
	add_action( 'mn_ajax_' . $_GET['action'], 'mn_ajax_' . str_replace( '-', '_', $_GET['action'] ), 1 );

if ( ! empty( $_POST['action'] ) && in_array( $_POST['action'], $core_actions_post ) )
	add_action( 'mn_ajax_' . $_POST['action'], 'mn_ajax_' . str_replace( '-', '_', $_POST['action'] ), 1 );

add_action( 'mn_ajax_nopriv_heartbeat', 'mn_ajax_nopriv_heartbeat', 1 );

if ( is_user_logged_in() ) {
	/**
	 * Fires authenticated Ajax actions for logged-in users.
	 *
	 * The dynamic portion of the hook name, `$_REQUEST['action']`,
	 * refers to the name of the Ajax action callback being fired.
	 *
	 * @since 2.1.0
	 */
	do_action( 'mn_ajax_' . $_REQUEST['action'] );
} else {
	/**
	 * Fires non-authenticated Ajax actions for logged-out users.
	 *
	 * The dynamic portion of the hook name, `$_REQUEST['action']`,
	 * refers to the name of the Ajax action callback being fired.
	 *
	 * @since 2.8.0
	 */
	do_action( 'mn_ajax_nopriv_' . $_REQUEST['action'] );
}
// Default status
die( '0' );
