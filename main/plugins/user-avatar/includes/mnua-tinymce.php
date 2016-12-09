<?php
/**
 * TinyMCE button for Visual Editor.
 *
 * @package User Avatar
 * @version 1.9.13
 */

/**
 * Add TinyMCE button
 * @since 1.9.5
 * @uses add_filter()
 * @uses get_user_option()
 */
function mnua_add_buttons() {
  // Add only in Rich Editor mode
  if(get_user_option('rich_editing') == 'true') {
    add_filter('mce_external_plugins', 'mnua_add_tinymce_plugin');
    add_filter('mce_buttons', 'mnua_register_button');
  }
}
add_action('init', 'mnua_add_buttons');

/**
 * Register TinyMCE button
 * @since 1.9.5
 * @param array $buttons
 * @return array
 */
function mnua_register_button($buttons) {
  array_push($buttons, 'separator', 'mnUserAvatar');
  return $buttons;
}

/**
 * Load TinyMCE plugin
 * @since 1.9.5
 * @param array $plugin_array
 * @return array
 */
function mnua_add_tinymce_plugin($plugins) {
  $plugins['mnUserAvatar'] = MNUA_INC_URL.'tinymce/editor_plugin.js';
  return $plugins;
}

/**
 * Call TinyMCE window content via admin-ajax
 * @since 1.4
 */
function mnua_ajax_tinymce() {
  include_once(MNUA_INC.'tinymce/window.php');
  die();
}
add_action('mn_ajax_mn_user_avatar_tinymce', 'mnua_ajax_tinymce');
