<?php
/**
 * Action handler for Multisite administration panels.
 *
 * @package Mtaandao
 * @subpackage Multisite
 * @since 3.0.0
 */

require_once( dirname( __FILE__ ) . '/admin.php' );

mn_redirect( network_admin_url() );
exit;
