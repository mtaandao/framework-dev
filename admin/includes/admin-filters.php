<?php
/**
 * Administration API: Default admin hooks
 *
 * @package Mtaandao
 * @subpackage Administration
 * @since 4.3.0
 */

// Bookmark hooks.
add_action( 'admin_page_access_denied', 'mn_link_manager_disabled_message' );

// Dashboard hooks.
add_action( 'activity_box_end', 'mn_dashboard_quota' );

// Media hooks.
add_action( 'attachment_submitbox_misc_actions', 'attachment_submitbox_metadata' );

add_action( 'media_upload_image', 'mn_media_upload_handler' );
add_action( 'media_upload_audio', 'mn_media_upload_handler' );
add_action( 'media_upload_video', 'mn_media_upload_handler' );
add_action( 'media_upload_file',  'mn_media_upload_handler' );

add_action( 'post-plupload-upload-ui', 'media_upload_flash_bypass' );

add_action( 'post-html-upload-ui', 'media_upload_html_bypass'  );

add_filter( 'async_upload_image', 'get_media_item', 10, 2 );
add_filter( 'async_upload_audio', 'get_media_item', 10, 2 );
add_filter( 'async_upload_video', 'get_media_item', 10, 2 );
add_filter( 'async_upload_file',  'get_media_item', 10, 2 );

add_filter( 'attachment_fields_to_save', 'image_attachment_fields_to_save', 10, 2 );

add_filter( 'media_upload_gallery', 'media_upload_gallery' );
add_filter( 'media_upload_library', 'media_upload_library' );

add_filter( 'media_upload_tabs', 'update_gallery_tab' );

// Misc hooks.
add_action( 'admin_head', 'admin_canonical_url'   );
add_action( 'admin_head', 'mn_color_scheme_settings' );
add_action( 'admin_head', 'mn_site_icon'             );
add_action( 'admin_head', '_ipad_meta'               );

// Prerendering.
if ( ! is_customize_preview() ) {
	add_filter( 'admin_print_styles', 'mn_resource_hints', 1 );
}

add_action( 'admin_print_scripts-post.php',     'mn_page_reload_on_back_button_js' );
add_action( 'admin_print_scripts-post-new.php', 'mn_page_reload_on_back_button_js' );

add_action( 'update_option_home',          'update_home_siteurl', 10, 2 );
add_action( 'update_option_siteurl',       'update_home_siteurl', 10, 2 );
add_action( 'update_option_page_on_front', 'update_home_siteurl', 10, 2 );

add_filter( 'heartbeat_received', 'mn_check_locked_posts',  10,  3 );
add_filter( 'heartbeat_received', 'mn_refresh_post_lock',   10,  3 );
add_filter( 'mn_refresh_nonces', 'mn_refresh_post_nonces', 10,  3 );
add_filter( 'heartbeat_received', 'heartbeat_autosave',     500, 2 );

add_filter( 'heartbeat_settings', 'mn_heartbeat_set_suspension' );

// Nav Menu hooks.
add_action( 'admin_head-nav-menus.php', '_mn_delete_orphaned_draft_menu_items' );

// Plugin hooks.
add_filter( 'whitelist_options', 'option_update_filter' );

// Plugin Install hooks.
add_action( 'install_plugins_featured',               'install_dashboard' );
add_action( 'install_plugins_upload',                 'install_plugins_upload' );
add_action( 'install_plugins_search',                 'display_plugins_table' );
add_action( 'install_plugins_popular',                'display_plugins_table' );
add_action( 'install_plugins_recommended',            'display_plugins_table' );
add_action( 'install_plugins_new',                    'display_plugins_table' );
add_action( 'install_plugins_beta',                   'display_plugins_table' );
add_action( 'install_plugins_favorites',              'display_plugins_table' );
add_action( 'install_plugins_pre_plugin-information', 'install_plugin_information' );

// Template hooks.
add_action( 'admin_enqueue_scripts', array( 'MN_Internal_Pointers', 'enqueue_scripts'                ) );
add_action( 'user_register',         array( 'MN_Internal_Pointers', 'dismiss_pointers_for_new_users' ) );

// Theme hooks.
add_action( 'customize_controls_print_footer_scripts', 'customize_themes_print_templates' );

// Theme Install hooks.
// add_action('install_themes_dashboard', 'install_themes_dashboard');
// add_action('install_themes_upload', 'install_themes_upload', 10, 0);
// add_action('install_themes_search', 'display_themes');
// add_action('install_themes_featured', 'display_themes');
// add_action('install_themes_new', 'display_themes');
// add_action('install_themes_updated', 'display_themes');
add_action( 'install_themes_pre_theme-information', 'install_theme_information' );

// User hooks.
add_action( 'admin_init', 'default_password_nag_handler' );

add_action( 'admin_notices', 'default_password_nag' );

add_action( 'profile_update', 'default_password_nag_edit_user', 10, 2 );

// Update hooks.
add_action( 'load-plugins.php', 'mn_plugin_update_rows', 20 ); // After mn_update_plugins() is called.
add_action( 'load-themes.php', 'mn_theme_update_rows', 20 ); // After mn_update_themes() is called.

add_action( 'admin_notices', 'update_nag',      3  );
add_action( 'admin_notices', 'maintenance_nag', 10 );

add_filter( 'update_footer', 'core_update_footer' );

// Update Core hooks.
add_action( '_core_updated_successfully', '_redirect_to_about_mtaandao' );

// Upgrade hooks.
add_action( 'upgrader_process_complete', array( 'Language_Pack_Upgrader', 'async_upgrade' ), 20 );
add_action( 'upgrader_process_complete', 'mn_version_check', 10, 0 );
add_action( 'upgrader_process_complete', 'mn_update_plugins', 10, 0 );
add_action( 'upgrader_process_complete', 'mn_update_themes', 10, 0 );
