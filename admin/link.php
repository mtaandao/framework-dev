<?php
/**
 * Manage link administration actions.
 *
 * This page is accessed by the link management pages and handles the forms and
 * Ajax processes for link actions.
 *
 * @package Mtaandao
 * @subpackage Administration
 */

/** Load Mtaandao Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

mn_reset_vars( array( 'action', 'cat_id', 'link_id' ) );

if ( ! current_user_can('manage_links') )
	mn_link_manager_disabled_message();

if ( !empty($_POST['deletebookmarks']) )
	$action = 'deletebookmarks';
if ( !empty($_POST['move']) )
	$action = 'move';
if ( !empty($_POST['linkcheck']) )
	$linkcheck = $_POST['linkcheck'];

$this_file = admin_url('link-manager.php');

switch ($action) {
	case 'deletebookmarks' :
		check_admin_referer('bulk-bookmarks');

		// For each link id (in $linkcheck[]) change category to selected value.
		if (count($linkcheck) == 0) {
			mn_redirect($this_file);
			exit;
		}

		$deleted = 0;
		foreach ($linkcheck as $link_id) {
			$link_id = (int) $link_id;

			if ( mn_delete_link($link_id) )
				$deleted++;
		}

		mn_redirect("$this_file?deleted=$deleted");
		exit;

	case 'move' :
		check_admin_referer('bulk-bookmarks');

		// For each link id (in $linkcheck[]) change category to selected value.
		if (count($linkcheck) == 0) {
			mn_redirect($this_file);
			exit;
		}
		$all_links = join(',', $linkcheck);
		/*
		 * Should now have an array of links we can change:
		 *     $q = $mndb->query("update $mndb->links SET link_category='$category' WHERE link_id IN ($all_links)");
		 */

		mn_redirect($this_file);
		exit;

	case 'add' :
		check_admin_referer('add-bookmark');

		$redir = mn_get_referer();
		if ( add_link() )
			$redir = add_query_arg( 'added', 'true', $redir );

		mn_redirect( $redir );
		exit;

	case 'save' :
		$link_id = (int) $_POST['link_id'];
		check_admin_referer('update-bookmark_' . $link_id);

		edit_link($link_id);

		mn_redirect($this_file);
		exit;

	case 'delete' :
		$link_id = (int) $_GET['link_id'];
		check_admin_referer('delete-bookmark_' . $link_id);

		mn_delete_link($link_id);

		mn_redirect($this_file);
		exit;

	case 'edit' :
		mn_enqueue_script('link');
		mn_enqueue_script('xfn');

		if ( mn_is_mobile() )
			mn_enqueue_script( 'jquery-touch-punch' );

		$parent_file = 'link-manager.php';
		$submenu_file = 'link-manager.php';
		$title = __('Edit Link');

		$link_id = (int) $_GET['link_id'];

		if (!$link = get_link_to_edit($link_id))
			mn_die(__('Link not found.'));

		include( ABSPATH . 'admin/edit-link-form.php' );
		include( ABSPATH . 'admin/admin-footer.php' );
		break;

	default :
		break;
}
