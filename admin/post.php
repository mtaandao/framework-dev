<?php
/**
 * Edit post administration panel.
 *
 * Manage Post actions: post, edit, delete, etc.
 *
 * @package Mtaandao
 * @subpackage Administration
 */

/** Mtaandao Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

$parent_file = 'edit.php';
$submenu_file = 'edit.php';

mn_reset_vars( array( 'action' ) );

if ( isset( $_GET['post'] ) )
 	$post_id = $post_ID = (int) $_GET['post'];
elseif ( isset( $_POST['post_ID'] ) )
 	$post_id = $post_ID = (int) $_POST['post_ID'];
else
 	$post_id = $post_ID = 0;

/**
 * @global string  $post_type
 * @global object  $post_type_object
 * @global MN_Post $post
 */
global $post_type, $post_type_object, $post;

if ( $post_id )
	$post = get_post( $post_id );

if ( $post ) {
	$post_type = $post->post_type;
	$post_type_object = get_post_type_object( $post_type );
}

if ( isset( $_POST['deletepost'] ) )
	$action = 'delete';
elseif ( isset($_POST['mn-preview']) && 'dopreview' == $_POST['mn-preview'] )
	$action = 'preview';

$sendback = mn_get_referer();
if ( ! $sendback ||
     strpos( $sendback, 'post.php' ) !== false ||
     strpos( $sendback, 'post-new.php' ) !== false ) {
	if ( 'attachment' == $post_type ) {
		$sendback = admin_url( 'upload.php' );
	} else {
		$sendback = admin_url( 'edit.php' );
		if ( ! empty( $post_type ) ) {
			$sendback = add_query_arg( 'post_type', $post_type, $sendback );
		}
	}
} else {
	$sendback = remove_query_arg( array('trashed', 'untrashed', 'deleted', 'ids'), $sendback );
}

switch($action) {
case 'post-quickdraft-save':
	// Check nonce and capabilities
	$nonce = $_REQUEST['_mnnonce'];
	$error_msg = false;

	// For output of the quickdraft dashboard widget
	require_once ABSPATH . 'admin/includes/dashboard.php';

	if ( ! mn_verify_nonce( $nonce, 'add-post' ) )
		$error_msg = __( 'Unable to submit this form, please refresh and try again.' );

	if ( ! current_user_can( get_post_type_object( 'post' )->cap->create_posts ) ) {
		exit;
	}

	if ( $error_msg )
		return mn_dashboard_quick_press( $error_msg );

	$post = get_post( $_REQUEST['post_ID'] );
	check_admin_referer( 'add-' . $post->post_type );

	$_POST['comment_status'] = get_default_comment_status( $post->post_type );
	$_POST['ping_status']    = get_default_comment_status( $post->post_type, 'pingback' );

	edit_post();
	mn_dashboard_quick_press();
	exit;

case 'postajaxpost':
case 'post':
	check_admin_referer( 'add-' . $post_type );
	$post_id = 'postajaxpost' == $action ? edit_post() : write_post();
	redirect_post( $post_id );
	exit();

case 'edit':
	$editing = true;

	if ( empty( $post_id ) ) {
		mn_redirect( admin_url('post.php') );
		exit();
	}

	if ( ! $post )
		mn_die( __( 'You attempted to edit an item that doesn&#8217;t exist. Perhaps it was deleted?' ) );

	if ( ! $post_type_object )
		mn_die( __( 'Unknown post type.' ) );

	if ( ! in_array( $typenow, get_post_types( array( 'show_ui' => true ) ) ) ) {
		mn_die( __( 'Sorry, you are not allowed to edit posts in this post type.' ) );
	}

	if ( ! current_user_can( 'edit_post', $post_id ) )
		mn_die( __( 'Sorry, you are not allowed to edit this item.' ) );

	if ( 'trash' == $post->post_status )
		mn_die( __( 'You can&#8217;t edit this item because it is in the Trash. Please restore it and try again.' ) );

	if ( ! empty( $_GET['get-post-lock'] ) ) {
		check_admin_referer( 'lock-post_' . $post_id );
		mn_set_post_lock( $post_id );
		mn_redirect( get_edit_post_link( $post_id, 'url' ) );
		exit();
	}

	$post_type = $post->post_type;
	if ( 'post' == $post_type ) {
		$parent_file = "edit.php";
		$submenu_file = "edit.php";
		$post_new_file = "post-new.php";
	} elseif ( 'attachment' == $post_type ) {
		$parent_file = 'upload.php';
		$submenu_file = 'upload.php';
		$post_new_file = 'media-new.php';
	} else {
		if ( isset( $post_type_object ) && $post_type_object->show_in_menu && $post_type_object->show_in_menu !== true )
			$parent_file = $post_type_object->show_in_menu;
		else
			$parent_file = "edit.php?post_type=$post_type";
		$submenu_file = "edit.php?post_type=$post_type";
		$post_new_file = "post-new.php?post_type=$post_type";
	}

	if ( ! mn_check_post_lock( $post->ID ) ) {
		$active_post_lock = mn_set_post_lock( $post->ID );

		if ( 'attachment' !== $post_type )
			mn_enqueue_script('autosave');
	}

	if ( is_multisite() ) {
		add_action( 'admin_footer', '_admin_notice_post_locked' );
	} else {
		$check_users = get_users( array( 'fields' => 'ID', 'number' => 2 ) );

		if ( count( $check_users ) > 1 )
			add_action( 'admin_footer', '_admin_notice_post_locked' );

		unset( $check_users );
	}

	$title = $post_type_object->labels->edit_item;
	$post = get_post($post_id, OBJECT, 'edit');

	if ( post_type_supports($post_type, 'comments') ) {
		mn_enqueue_script('admin-comments');
		enqueue_comment_hotkeys_js();
	}

	include( ABSPATH . 'admin/edit-form-advanced.php' );

	break;

case 'editattachment':
	check_admin_referer('update-post_' . $post_id);

	// Don't let these be changed
	unset($_POST['guid']);
	$_POST['post_type'] = 'attachment';

	// Update the thumbnail filename
	$newmeta = mn_get_attachment_metadata( $post_id, true );
	$newmeta['thumb'] = $_POST['thumb'];

	mn_update_attachment_metadata( $post_id, $newmeta );

case 'editpost':
	check_admin_referer('update-post_' . $post_id);

	$post_id = edit_post();

	// Session cookie flag that the post was saved
	if ( isset( $_COOKIE['mn-saving-post'] ) && $_COOKIE['mn-saving-post'] === $post_id . '-check' ) {
		setcookie( 'mn-saving-post', $post_id . '-saved', time() + DAY_IN_SECONDS, ADMIN_COOKIE_PATH, COOKIE_DOMAIN, is_ssl() );
	}

	redirect_post($post_id); // Send user on their way while we keep working

	exit();

case 'trash':
	check_admin_referer('trash-post_' . $post_id);

	if ( ! $post )
		mn_die( __( 'The item you are trying to move to the Trash no longer exists.' ) );

	if ( ! $post_type_object )
		mn_die( __( 'Unknown post type.' ) );

	if ( ! current_user_can( 'delete_post', $post_id ) )
		mn_die( __( 'Sorry, you are not allowed to move this item to the Trash.' ) );

	if ( $user_id = mn_check_post_lock( $post_id ) ) {
		$user = get_userdata( $user_id );
		mn_die( sprintf( __( 'You cannot move this item to the Trash. %s is currently editing.' ), $user->display_name ) );
	}

	if ( ! mn_trash_post( $post_id ) )
		mn_die( __( 'Error in moving to Trash.' ) );

	mn_redirect( add_query_arg( array('trashed' => 1, 'ids' => $post_id), $sendback ) );
	exit();

case 'untrash':
	check_admin_referer('untrash-post_' . $post_id);

	if ( ! $post )
		mn_die( __( 'The item you are trying to restore from the Trash no longer exists.' ) );

	if ( ! $post_type_object )
		mn_die( __( 'Unknown post type.' ) );

	if ( ! current_user_can( 'delete_post', $post_id ) )
		mn_die( __( 'Sorry, you are not allowed to restore this item from the Trash.' ) );

	if ( ! mn_untrash_post( $post_id ) )
		mn_die( __( 'Error in restoring from Trash.' ) );

	mn_redirect( add_query_arg('untrashed', 1, $sendback) );
	exit();

case 'delete':
	check_admin_referer('delete-post_' . $post_id);

	if ( ! $post )
		mn_die( __( 'This item has already been deleted.' ) );

	if ( ! $post_type_object )
		mn_die( __( 'Unknown post type.' ) );

	if ( ! current_user_can( 'delete_post', $post_id ) )
		mn_die( __( 'Sorry, you are not allowed to delete this item.' ) );

	if ( $post->post_type == 'attachment' ) {
		$force = ( ! MEDIA_TRASH );
		if ( ! mn_delete_attachment( $post_id, $force ) )
			mn_die( __( 'Error in deleting.' ) );
	} else {
		if ( ! mn_delete_post( $post_id, true ) )
			mn_die( __( 'Error in deleting.' ) );
	}

	mn_redirect( add_query_arg('deleted', 1, $sendback) );
	exit();

case 'preview':
	check_admin_referer( 'update-post_' . $post_id );

	$url = post_preview();

	mn_redirect($url);
	exit();

default:
	/**
	 * Fires for a given custom post action request.
	 *
	 * The dynamic portion of the hook name, `$action`, refers to the custom post action.
	 *
	 * @since 4.6.0
	 *
	 * @param int $post_id Post ID sent with the request.
	 */
	do_action( "post_action_{$action}", $post_id );

	mn_redirect( admin_url('edit.php') );
	exit();
} // end switch
include( ABSPATH . 'admin/admin-footer.php' );
