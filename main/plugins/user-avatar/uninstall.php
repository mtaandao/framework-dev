<?php
/**
 * Remove user metadata and options on plugin delete.
 *
 * @package User Avatar
 * @version 1.9.13
 */

/**
 * @since 1.4
 * @uses int $blog_id
 * @uses object $mndb
 * @uses delete_option()
 * @uses delete_post_meta_by_key()
 * @uses delete_user_meta()
 * @uses get_users()
 * @uses get_blog_prefix()
 * @uses is_multisite()
 * @uses switch_to_blog()
 * @uses update_option()
 * @uses mn_get_sites()
 */

if(!defined('MN_UNINSTALL_PLUGIN')) {
  die('You are not allowed to call this page directly.');
}

global $blog_id, $mndb;
$users = get_users();

// Remove settings for all sites in multisite
if(is_multisite()) {
  $blogs = mn_get_sites();
  foreach($users as $user) {
    foreach($blogs as $blog) {
      delete_user_meta($user->ID, $mndb->get_blog_prefix($blog->blog_id).'user_avatar');
    }
  }
  foreach($blogs as $blog) {
    switch_to_blog($blog->blog_id);
    delete_option('avatar_default_mn_user_avatar');
    delete_option('mn_user_avatar_allow_upload');
    delete_option('mn_user_avatar_disable_gravatar');
    delete_option('mn_user_avatar_edit_avatar');
    delete_option('mn_user_avatar_load_scripts');
    delete_option('mn_user_avatar_resize_crop');
    delete_option('mn_user_avatar_resize_h');
    delete_option('mn_user_avatar_resize_upload');
    delete_option('mn_user_avatar_resize_w');
    delete_option('mn_user_avatar_tinymce');
    delete_option('mn_user_avatar_upload_size_limit');
    delete_option('mn_user_avatar_default_avatar_updated');
    delete_option('mn_user_avatar_media_updated');
    delete_option('mn_user_avatar_users_updated');
	delete_option('mnua_has_gravatar');
  }
} else {
  foreach($users as $user) {
    delete_user_meta($user->ID, $mndb->get_blog_prefix($blog_id).'user_avatar');
  }
  delete_option('avatar_default_mn_user_avatar');
  delete_option('mn_user_avatar_allow_upload');
  delete_option('mn_user_avatar_disable_gravatar');
  delete_option('mn_user_avatar_edit_avatar');
  delete_option('mn_user_avatar_load_scripts');
  delete_option('mn_user_avatar_resize_crop');
  delete_option('mn_user_avatar_resize_h');
  delete_option('mn_user_avatar_resize_upload');
  delete_option('mn_user_avatar_resize_w');
  delete_option('mn_user_avatar_tinymce');
  delete_option('mn_user_avatar_upload_size_limit');
  delete_option('mn_user_avatar_default_avatar_updated');
  delete_option('mn_user_avatar_media_updated');
  delete_option('mn_user_avatar_users_updated');
  delete_option('mnua_has_gravatar');
}

// Delete post meta
delete_post_meta_by_key('_mn_attachment_mn_user_avatar');

// Reset all default avatars to Mystery Man
update_option('avatar_default', 'mystery');
