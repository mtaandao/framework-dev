<?php
/**
 * Press This Display and Handler.
 *
 * @package Mtaandao
 * @subpackage Press_This
 */

define('IFRAME_REQUEST' , true);

/** Mtaandao Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

if ( ! current_user_can( 'edit_posts' ) || ! current_user_can( get_post_type_object( 'post' )->cap->create_posts ) ) {
	mn_die(
		'<h1>' . __( 'Cheatin&#8217; uh?' ) . '</h1>' .
		'<p>' . __( 'Sorry, you are not allowed to create posts as this user.' ) . '</p>',
		403
	);
}

include( ABSPATH . 'admin/includes/class-mn-press-this.php' ); 
$mn_press_this = new MN_Press_This();
$mn_press_this->html();
