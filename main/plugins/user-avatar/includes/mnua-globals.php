<?php
/**
 * Global variables used in plugin.
 *
 * @package User Avatar
 * @version 1.9.13
 */

/**
 * @since 1.8
 * @uses get_intermediate_image_sizes()
 * @uses get_option()
 * @uses mn_max_upload_size()
 */

// Define global variables
global $avatar_default,
       $show_avatars,
       $mnua_allow_upload,
       $mnua_avatar_default,
       $mnua_disable_gravatar,
       $mnua_edit_avatar,
       $mnua_resize_crop,
       $mnua_resize_h,
       $mnua_resize_upload,
       $mnua_resize_w,
       $mnua_tinymce,
       $mustache_original,
       $mustache_medium,
       $mustache_thumbnail,
       $mustache_avatar,
       $mustache_admin,
       $mnua_default_avatar_updated,
       $mnua_users_updated,
       $mnua_media_updated,
       $upload_size_limit,
       $upload_size_limit_with_units,
       $mnua_user_upload_size_limit,
       $mnua_upload_size_limit,
       $mnua_upload_size_limit_with_units,
       $all_sizes,
       $mnua_hash_gravatar;
//delete_option('mnua_hash_gravatar');
// Store if hash has gravatar
$mnua_hash_gravatar = get_option('mnua_hash_gravatar');
if( $mnua_hash_gravatar != false)
$mnua_hash_gravatar = unserialize(get_option('mnua_hash_gravatar'));

// Default avatar name
$avatar_default = get_option('avatar_default');
// Attachment ID of default avatar
$mnua_avatar_default = get_option('avatar_default_mn_user_avatar');

// Booleans
$show_avatars = get_option('show_avatars');
$mnua_allow_upload = get_option('mn_user_avatar_allow_upload');
$mnua_disable_gravatar = get_option('mn_user_avatar_disable_gravatar');
$mnua_edit_avatar = get_option('mn_user_avatar_edit_avatar');
$mnua_resize_crop = get_option('mn_user_avatar_resize_crop');
$mnua_resize_upload = get_option('mn_user_avatar_resize_upload');
$mnua_tinymce = get_option('mn_user_avatar_tinymce');

// Resize dimensions
$mnua_resize_h = get_option('mn_user_avatar_resize_h');
$mnua_resize_w = get_option('mn_user_avatar_resize_w');

// Default avatar 512x512
$mustache_original = MNUA_URL.'images/mnua.png';
// Default avatar 300x300
$mustache_medium = MNUA_URL.'images/mnua-300x300.png';
// Default avatar 150x150
$mustache_thumbnail = MNUA_URL.'images/mnua-150x150.png';
// Default avatar 96x96
$mustache_avatar = MNUA_URL.'images/mnua-96x96.png';
// Default avatar 32x32
$mustache_admin = MNUA_URL.'images/mnua-32x32.png';

// Check for updates
$mnua_default_avatar_updated = get_option('mn_user_avatar_default_avatar_updated');
$mnua_users_updated = get_option('mn_user_avatar_users_updated');
$mnua_media_updated = get_option('mn_user_avatar_media_updated');

// Server upload size limit
$upload_size_limit = mn_max_upload_size();
// Convert to KB
if($upload_size_limit > 1024) {
  $upload_size_limit /= 1024;
}
$upload_size_limit_with_units = (int) $upload_size_limit.'KB';

// User upload size limit
$mnua_user_upload_size_limit = get_option('mn_user_avatar_upload_size_limit');
if($mnua_user_upload_size_limit == 0 || $mnua_user_upload_size_limit > mn_max_upload_size()) {
  $mnua_user_upload_size_limit = mn_max_upload_size();
}
// Value in bytes
$mnua_upload_size_limit = $mnua_user_upload_size_limit;
// Convert to KB
if($mnua_user_upload_size_limit > 1024) {
  $mnua_user_upload_size_limit /= 1024;
}
$mnua_upload_size_limit_with_units = (int) $mnua_user_upload_size_limit.'KB';

// Check for custom image sizes
$all_sizes = array_merge(get_intermediate_image_sizes(), array('original'));
