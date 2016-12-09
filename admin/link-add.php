<?php
/**
 * Add Link Administration Screen.
 *
 * @package Mtaandao
 * @subpackage Administration
 */

/** Load Mtaandao Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

if ( ! current_user_can('manage_links') )
	mn_die(__('Sorry, you are not allowed to add links to this site.'));

$title = __('Add New Link');
$parent_file = 'link-manager.php';

mn_reset_vars( array('action', 'cat_id', 'link_id' ) );

mn_enqueue_script('link');
mn_enqueue_script('xfn');

if ( mn_is_mobile() )
	mn_enqueue_script( 'jquery-touch-punch' );

$link = get_default_link_to_edit();
include( ABSPATH . 'admin/edit-link-form.php' );

require( ABSPATH . 'admin/admin-footer.php' );
