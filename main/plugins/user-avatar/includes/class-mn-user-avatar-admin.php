<?php
/**
 * Defines all of administrative, activation, and deactivation settings.
 *
 * @package User Avatar
 * @version 1.9.13
 */

class MN_User_Avatar_Admin {
  /**
   * Constructor
   * @since 1.8
   * @uses bool $show_avatars
   * @uses add_action()
   * @uses add_filter()
   * @uses load_plugin_textdomain()
   * @uses register_activation_hook()
   * @uses register_deactivation_hook()
   */
  public function __construct() {
    global $show_avatars;
    // Initialize default settings
    register_activation_hook(MNUA_DIR.'mn-user-avatar.php', array($this, 'mnua_options'));
    // Settings saved to mn_options
    add_action('admin_init', array($this, 'mnua_options'));
    // Remove subscribers edit_posts capability
    // Translations
    load_plugin_textdomain('mn-user-avatar', "", MNUA_FOLDER.'/lang');
    // Admin menu settings
    add_action('admin_menu', array($this, 'mnua_admin'));
    add_action('admin_init', array($this, 'mnua_register_settings'));
    // Default avatar
    add_filter('default_avatar_select', array($this, 'mnua_add_default_avatar'), 10);
    add_filter('whitelist_options', array($this, 'mnua_whitelist_options'), 10);
    // Additional plugin info
    add_filter('plugin_action_links', array($this, 'mnua_action_links'), 10, 2);
    // Hide column in Users table if default avatars are enabled
    if((bool) $show_avatars == 0) {
      add_filter('manage_users_columns', array($this, 'mnua_add_column'), 10, 1);
      add_filter('manage_users_custom_column', array($this, 'mnua_show_column'), 10, 3);
    }
    // Media states
    add_filter('display_media_states', array($this, 'mnua_add_media_state'), 10, 1);
	
  }

  /**
   * Settings saved to mn_options
   * @since 1.4
   * @uses add_option()
   */
  public function mnua_options() {
    
    add_option('avatar_default_mn_user_avatar', "");
    add_option('mn_user_avatar_allow_upload', '0');
    add_option('mn_user_avatar_disable_gravatar', '0');
    add_option('mn_user_avatar_edit_avatar', '1');
    add_option('mn_user_avatar_resize_crop', '0');
    add_option('mn_user_avatar_resize_h', '96');
    add_option('mn_user_avatar_resize_upload', '0');
    add_option('mn_user_avatar_resize_w', '96');
    add_option('mn_user_avatar_tinymce', '1');
    add_option('mn_user_avatar_upload_size_limit', '0');	

    if(mn_next_scheduled( 'mnua_has_gravatar_cron_hook' )){
      $cron=get_option('cron');
      $new_cron='';
      foreach($cron as $key=>$value)
      {
        if(is_array($value))
        {
        if(array_key_exists('mnua_has_gravatar_cron_hook',$value))
        unset($cron[$key]);
        }
      }
      update_option('cron',$cron);
  }



  }

  /**
   * On deactivation
   * @since 1.4
   * @uses int $blog_id
   * @uses object $mndb
   * @uses get_blog_prefix()
   * @uses get_option()
   * @uses update_option()
   */
  public function mnua_deactivate() {
    global $blog_id, $mndb;
    $mn_user_roles = $mndb->get_blog_prefix($blog_id).'user_roles';
    // Get user roles and capabilities
    $user_roles = get_option($mn_user_roles);
    // Remove subscribers edit_posts capability
    unset($user_roles['subscriber']['capabilities']['edit_posts']);
    update_option($mn_user_roles, $user_roles);
    // Reset all default avatars to Mystery Man
    update_option('avatar_default', 'mystery');
	
  }

  /**
   * Add options page and settings
   * @since 1.4
   * @uses add_menu_page()
   * @uses add_submenu_page()
   */
  public function mnua_admin() {
    add_menu_page(__('User Avatar', 'mn-user-avatar'), __('Custom Avatars', 'mn-user-avatar'), 'manage_options', 'mn-user-avatar', array($this, 'mnua_options_page'), 'dashicons-businessman');
    add_submenu_page('mn-user-avatar', __('Settings' , 'mn-user-avatar'), __('Settings' , 'mn-user-avatar'), 'manage_options', 'mn-user-avatar', array($this, 'mnua_options_page'));
    $hook = add_submenu_page('mn-user-avatar', __('Library','mn-user-avatar'), __('Library', 'mn-user-avatar'), 'manage_options', 'mn-user-avatar-library', array($this, 'mnua_media_page'));
    add_action("load-$hook", array($this, 'mnua_media_screen_option'));
    add_filter('set-screen-option', array($this, 'mnua_set_media_screen_option'), 10, 3);
  }

  /**
   * Checks if current page is settings page
   * @since 1.8.3
   * @uses string $pagenow
   * @return bool
   */
  public function mnua_is_menu_page() {
    global $pagenow;
    $is_menu_page = ($pagenow == 'admin.php' && isset($_GET['page']) && $_GET['page'] == 'mn-user-avatar') ? true : false;
    return (bool) $is_menu_page;
  }

  /**
   * Media page
   * @since 1.8
   */
  public function mnua_media_page() {
    require_once(MNUA_INC.'mnua-media-page.php');
  }

  /**
   * Avatars per page
   * @since 1.8.10
   * @uses add_screen_option()
   */
  public function mnua_media_screen_option() {
    $option = 'per_page';
    $args = array(
      'label' => __('Avatars','mn-user-avatar'),
      'default' => 10,
      'option' => 'upload_per_page'
    );
    add_screen_option($option, $args);
  }

  /**
   * Save per page setting
   * @since 1.8.10
   * @param int $status
   * @param string $option
   * @param int $value
   * @return int $status
   */
  public function mnua_set_media_screen_option($status, $option, $value) {
    $status = ($option == 'upload_per_page') ? $value : $status;
    return $status;
  }

  /**
   * Options page
   * @since 1.4
   */
  public function mnua_options_page() {
    require_once(MNUA_INC.'mnua-options-page.php');
  }

  /**
   * Whitelist settings
   * @since 1.9
   * @uses apply_filters()
   * @uses register_setting()
   * @return array
   */
  public function mnua_register_settings() {
    $settings = array();
    $settings[] = register_setting('mnua-settings-group', 'avatar_rating');
    $settings[] = register_setting('mnua-settings-group', 'avatar_default');
    $settings[] = register_setting('mnua-settings-group', 'avatar_default_mn_user_avatar');
    $settings[] = register_setting('mnua-settings-group', 'show_avatars', 'intval');
    $settings[] = register_setting('mnua-settings-group', 'mn_user_avatar_tinymce', 'intval');
    $settings[] = register_setting('mnua-settings-group', 'mn_user_avatar_allow_upload', 'intval');
    $settings[] = register_setting('mnua-settings-group', 'mn_user_avatar_disable_gravatar', 'intval');
    $settings[] = register_setting('mnua-settings-group', 'mn_user_avatar_edit_avatar', 'intval');
    $settings[] = register_setting('mnua-settings-group', 'mn_user_avatar_resize_crop', 'intval');
    $settings[] = register_setting('mnua-settings-group', 'mn_user_avatar_resize_h', 'intval');
    $settings[] = register_setting('mnua-settings-group', 'mn_user_avatar_resize_upload', 'intval');
    $settings[] = register_setting('mnua-settings-group', 'mn_user_avatar_resize_w', 'intval');
    $settings[] = register_setting('mnua-settings-group', 'mn_user_avatar_upload_size_limit', 'intval');
    /**
     * Filter admin whitelist settings
     * @since 1.9
     * @param array $settings
     */
    return apply_filters('mnua_register_settings', $settings);
  }

  /**
   * Add default avatar
   * @since 1.4
   * @uses string $avatar_default
   * @uses string $mustache_admin
   * @uses string $mustache_medium
   * @uses int $mnua_avatar_default
   * @uses bool $mnua_disable_gravatar
   * @uses object $mnua_functions
   * @uses get_avatar()
   * @uses remove_filter()
   * @uses mnua_attachment_is_image()
   * @uses mnua_get_attachment_image_src()
   * @return string
   */
  public function mnua_add_default_avatar() {
    global $avatar_default, $mustache_admin, $mustache_medium, $mnua_avatar_default, $mnua_disable_gravatar, $mnua_functions;
    // Remove get_avatar filter
    remove_filter('get_avatar', array($mnua_functions, 'mnua_get_avatar_filter'));
    // Set avatar_list variable
    $avatar_list = "";
    // Set avatar defaults
    $avatar_defaults = array(
      'mystery' => __('Mystery Man','mn-user-avatar'),
      'blank' => __('Blank','mn-user-avatar'),
      'gravatar_default' => __('Gravatar Logo','mn-user-avatar'),
      'identicon' => __('Identicon (Generated)','mn-user-avatar'),
      'wavatar' => __('Wavatar (Generated)','mn-user-avatar'),
      'monsterid' => __('MonsterID (Generated)','mn-user-avatar'),
      'retro' => __('Retro (Generated)','mn-user-avatar')
    );
    // No Default Avatar, set to Mystery Man
    if(empty($avatar_default)) {
      $avatar_default = 'mystery';
    }
    // Take avatar_defaults and get examples for unknown@gravatar.com
    foreach($avatar_defaults as $default_key => $default_name) {
      $avatar = get_avatar('unknown@gravatar.com', 32, $default_key);
      $selected = ($avatar_default == $default_key) ? 'checked="checked" ' : "";
      $avatar_list .= "\n\t<label><input type='radio' name='avatar_default' id='avatar_{$default_key}' value='".esc_attr($default_key)."' {$selected}/> ";
      $avatar_list .= preg_replace("/src='(.+?)'/", "src='\$1&amp;forcedefault=1'", $avatar);
      $avatar_list .= ' '.$default_name.'</label>';
      $avatar_list .= '<br />';
    }
    // Show remove link if custom Default Avatar is set
    if(!empty($mnua_avatar_default) && $mnua_functions->mnua_attachment_is_image($mnua_avatar_default)) {
      $avatar_thumb_src = $mnua_functions->mnua_get_attachment_image_src($mnua_avatar_default, array(32,32));
      $avatar_thumb = $avatar_thumb_src[0];
      $hide_remove = "";
    } else {
      $avatar_thumb = $mustache_admin;
      $hide_remove = ' class="mnua-hide"';
    }
    // Default Avatar is mn_user_avatar, check the radio button next to it
    $selected_avatar = ((bool) $mnua_disable_gravatar == 1 || $avatar_default == 'mn_user_avatar') ? ' checked="checked" ' : "";
    // Wrap MNUA in div
    $avatar_thumb_img = '<div id="mnua-preview"><img src="'.$avatar_thumb.'" width="32" /></div>';
    // Add MNUA to list
    $mnua_list = "\n\t<label><input type='radio' name='avatar_default' id='mn_user_avatar_radio' value='mn_user_avatar'$selected_avatar /> ";
    $mnua_list .= preg_replace("/src='(.+?)'/", "src='\$1'", $avatar_thumb_img);
    $mnua_list .= ' '.__('Local Avatar', 'mn-user-avatar').'</label>';
    $mnua_list .= '<p id="mnua-edit"><button type="button" class="button" id="mnua-add" name="mnua-add" data-avatar_default="true" data-title="'._('Choose Image').': '._('Default Avatar').'">'.__('Choose Image','mn-user-avatar').'</button>';
    $mnua_list .= '<span id="mnua-remove-button"'.$hide_remove.'><a href="#" id="mnua-remove">'.__('Remove','mn-user-avatar').'</a></span><span id="mnua-undo-button"><a href="#" id="mnua-undo">'.__('Undo','mn-user-avatar').'</a></span></p>';
    $mnua_list .= '<input type="hidden" id="mn-user-avatar" name="avatar_default_mn_user_avatar" value="'.$mnua_avatar_default.'">';
    $mnua_list .= '<div id="mnua-modal"></div>';
    if((bool) $mnua_disable_gravatar != 1) {
      return $mnua_list.'<div id="mn-avatars">'.$avatar_list.'</div>';
    } else {
      return $mnua_list.'<div id="mn-avatars" style="display:none;">'.$avatar_list.'</div>';
    }
  }

  /**
   * Add default avatar_default to whitelist
   * @since 1.4
   * @param array $options
   * @return array $options
   */
  public function mnua_whitelist_options($options) {
    $options['discussion'][] = 'avatar_default_mn_user_avatar';
    return $options;
  }

  /**
   * Add actions links on plugin page
   * @since 1.6.6
   * @param array $links
   * @param string $file
   * @return array $links
   */
  public function mnua_action_links($links, $file) { 
    if(basename(dirname($file)) == 'mn-user-avatar') {
      $links[] = '<a href="'.esc_url(add_query_arg(array('page' => 'mn-user-avatar'), admin_url('admin.php'))).'">'.__('Settings','mn-user-avatar').'</a>';
    }
    return $links;
  }



  /**
   * Add column to Users table
   * @since 1.4
   * @param array $columns
   * @return array
   */
  public function mnua_add_column($columns) {
    return $columns + array('mn-user-avatar' => __('Local Avatar','mn-user-avatar'));
  }

  /**
   * Show thumbnail in Users table
   * @since 1.4
   * @param string $value
   * @param string $column_name
   * @param int $user_id
   * @uses int $blog_id
   * @uses object $mndb
   * @uses object $mnua_functions
   * @uses get_blog_prefix()
   * @uses get_user_meta()
   * @uses mnua_get_attachment_image()
   * @return string $value
   */
  public function mnua_show_column($value, $column_name, $user_id) {
    global $blog_id, $mndb, $mnua_functions;
    $mnua = get_user_meta($user_id, $mndb->get_blog_prefix($blog_id).'user_avatar', true);
    $mnua_image = $mnua_functions->mnua_get_attachment_image($mnua, array(32,32));
    if($column_name == 'mn-user-avatar') {
      $value = $mnua_image;
    }
    return $value;
  }

  /**
   * Get list table
   * @since 1.8
   * @param string $class
   * @param array $args
   * @return object
   */
  public function _mnua_get_list_table($class, $args = array()) {
    require_once(MNUA_INC.'class-mn-user-avatar-list-table.php');
    $args['screen'] = 'mn-user-avatar';
    return new $class($args);
  }

  /**
   * Add media states
   * @since 1.4
   * @param array $states
   * @uses object $post
   * @uses int $mnua_avatar_default
   * @uses apply_filters()
   * @uses get_post_custom_values()
   * @return array
   */
  public function mnua_add_media_state($states) {
    global $post, $mnua_avatar_default;
    $is_mnua = get_post_custom_values('_mn_attachment_mn_user_avatar', $post->ID);
    if(!empty($is_mnua)) {
      $states[] = __('Avatar','mn-user-avatar');
    }
    if(!empty($mnua_avatar_default) && ($mnua_avatar_default == $post->ID)) {
      $states[] = __('Default Avatar','mn-user-avatar');
    }
    /**
     * Filter media states
     * @since 1.4
     * @param array $states
     */
    return apply_filters('mnua_add_media_state', $states);
  }
  
}

/**
 * Initialize
 * @since 1.9.2
 */
function mnua_admin_init() {
  global $mnua_admin;
  $mnua_admin = new MN_User_Avatar_Admin();
}
add_action('init', 'mnua_admin_init');
