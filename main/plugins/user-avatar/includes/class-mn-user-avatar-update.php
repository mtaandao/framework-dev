<?php
/**
 * Updates for legacy settings.
 *
 * @package User Avatar
 * @version 1.9.13
 */

class MN_User_Avatar_Update {
  /**
   * Constructor
   * @since 1.8
   * @uses bool $mnua_default_avatar_updated
   * @uses bool $mnua_media_updated
   * @uses bool $mnua_users_updated
   * @uses add_action()
   */
  public function __construct() {
    global $mnua_default_avatar_updated, $mnua_media_updated, $mnua_users_updated;
    if(empty($mnua_default_avatar_updated)) {
      add_action('admin_init', array($this, 'mnua_default_avatar'));
    }
    if(empty($mnua_users_updated)) {
      add_action('admin_init', array($this, 'mnua_user_meta'));
    }
    if(empty($mnua_media_updated)) {
      add_action('admin_init', array($this, 'mnua_media_state'));
    }
  }

  /**
   * Update default avatar to new format
   * @since 1.4
   * @uses string $avatar_default
   * @uses string $mustache_original
   * @uses int $mnua_avatar_default
   * @uses update_option()
   * @uses mn_get_attachment_image_src()
   */
  public function mnua_default_avatar() {
    global $avatar_default, $mustache_original, $mnua_avatar_default;
    // If default avatar is the old mustache URL, update it
    if($avatar_default == $mustache_original) {
      update_option('avatar_default', 'mn_user_avatar');
    }
    // If user had an image URL as the default avatar, replace with ID instead
    if(!empty($mnua_avatar_default)) {
      $mnua_avatar_default_image = mn_get_attachment_image_src($mnua_avatar_default, 'medium');
      if($avatar_default == $mnua_avatar_default_image[0]) {
        update_option('avatar_default', 'mn_user_avatar');
      }
    }
    update_option('mn_user_avatar_default_avatar_updated', '1');
  }

  /**
   * Rename user meta to match database settings
   * @since 1.4
   * @uses int $blog_id
   * @uses object $mndb
   * @uses delete_user_meta()
   * @uses get_blog_prefix()
   * @uses get_user_meta()
   * @uses get_users()
   * @uses update_option()
   * @uses update_user_meta()
   */
  public function mnua_user_meta() {
    global $blog_id, $mndb;
    $mnua_metakey = $mndb->get_blog_prefix($blog_id).'user_avatar';
    // If database tables start with something other than mn_
    if($mnua_metakey != 'mn_user_avatar') {
      $users = get_users();
      // Move current user metakeys to new metakeys
      foreach($users as $user) {
        $mnua = get_user_meta($user->ID, 'mn_user_avatar', true);
        if(!empty($mnua)) {
          update_user_meta($user->ID, $mnua_metakey, $mnua);
          delete_user_meta($user->ID, 'mn_user_avatar');
        }
      }
    }
    update_option('mn_user_avatar_users_updated', '1'); 
  }

  /**
   * Add media state to existing avatars
   * @since 1.4
   * @uses int $blog_id
   * @uses object $mndb
   * @uses add_post_meta()
   * @uses get_blog_prefix()
   * @uses get_results()
   * @uses update_option()
   */
  public function mnua_media_state() {
    global $blog_id, $mndb;
    // Find all users with MNUA
    $mnua_metakey = $mndb->get_blog_prefix($blog_id).'user_avatar';
    $mnuas = $mndb->get_results($mndb->prepare("SELECT * FROM $mndb->usermeta WHERE meta_key = %s AND meta_value != %d AND meta_value != %d", $mnua_metakey, 0, ""));
    foreach($mnuas as $usermeta) {
      add_post_meta($usermeta->meta_value, '_mn_attachment_mn_user_avatar', $usermeta->user_id);
    }
    update_option('mn_user_avatar_media_updated', '1');
  }
}

/**
 * Initialize
 * @since 1.9.2
 */
function mnua_update_init() {
  global $mnua_update;
  $mnua_update = new MN_User_Avatar_Update();
}
add_action('init', 'mnua_update_init');
