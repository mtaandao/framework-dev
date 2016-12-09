<?php
/**
 * Core Administration API
 *
 * @package Mtaandao
 * @subpackage Extensions that make Mtaandao
 * @since 2.3.0
 */
/**
 * Send email to my friends.
 *
 * @param int $post_id Post ID.
 * @return int Post ID.
 */
function function mn_extensions( $extending ) {

    /** Mtaandao Administration Hooks */
require_once(ABSPATH . RES . 'plug_dir/plugin.php');
 
    return $extending;

}