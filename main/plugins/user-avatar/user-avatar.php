<?php
/**
 * @package User Avatar
 * @version 2.0.7
 */

/*
Plugin Name: User Avatar
Description: Use any image from your Mtaandao Media Library as a custom user avatar. Add your own Default Avatar.
Version: 2.0.7
Text Domain: mn-user-avatar
Domain Path: /lang/
*/

if(!defined('ABSPATH')) {
  die('You are not allowed to call this page directly.');
}

/**
 * Let's get started!
 */
class MN_User_Avatar_Setup {
  /**
   * Constructor
   * @since 1.9.2
   */
  public function __construct() {
    $this->_define_constants();
    $this->_load_mn_includes();
    $this->_load_mnua();
  }

  /**
   * Define paths
   * @since 1.9.2
   */
  private function _define_constants() {
    define('MNUA_VERSION', '2.0.7');
    define('MNUA_FOLDER', basename(dirname(__FILE__)));
    define('MNUA_DIR', plugin_dir_path(__FILE__));
    define('MNUA_INC', MNUA_DIR.'includes'.'/');
    define('MNUA_URL', plugin_dir_url(MNUA_FOLDER).MNUA_FOLDER.'/');
    define('MNUA_INC_URL', MNUA_URL.'includes'.'/');
  }

  /**
   * Mtaandao includes used in plugin
   * @since 1.9.2
   * @uses is_admin()
   */
  private function _load_mn_includes() {
    if(!is_admin()) {
      // mn_handle_upload
      require_once(ABSPATH.'admin/includes/file.php');
      // mn_generate_attachment_metadata
      require_once(ABSPATH.'admin/includes/image.php');
      // image_add_caption
      require_once(ABSPATH.'admin/includes/media.php');
      // submit_button
      require_once(ABSPATH.'admin/includes/template.php');
    }
    // add_screen_option
    require_once(ABSPATH.'admin/includes/screen.php');
  }

  /**
   * Load User Avatar
   * @since 1.9.2
   * @uses bool $mnua_tinymce
   * @uses is_admin()
   */
  private function _load_mnua() {
    global $mnua_tinymce;
    require_once(MNUA_DIR.'social-avatar.php');
    require_once(MNUA_INC.'mnua-globals.php');
    require_once(MNUA_INC.'mnua-functions.php');
    require_once(MNUA_INC.'class-mn-user-avatar-admin.php');
    require_once(MNUA_INC.'class-mn-user-avatar.php');
    require_once(MNUA_INC.'class-mn-user-avatar-functions.php');
    require_once(MNUA_INC.'class-mn-user-avatar-shortcode.php');
    require_once(MNUA_INC.'class-mn-user-avatar-subscriber.php');
    require_once(MNUA_INC.'class-mn-user-avatar-update.php');
    require_once(MNUA_INC.'class-mn-user-avatar-widget.php');
    // Load TinyMCE only if enabled
    if((bool) $mnua_tinymce == 1) {
      require_once(MNUA_INC.'mnua-tinymce.php');
    }

  }
}

/**
 * Initialize
 */
new MN_User_Avatar_Setup();
