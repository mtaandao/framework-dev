<?php
/**
 * Settings only for subscribers and contributors.
 *
 * @package User Avatar
 * @version 1.9.13
 */

class MN_User_Avatar_Subscriber {
  /**
   * Constructor
   * @since 1.8
   * @uses object $mn_user_avatar
   * @uses bool $mnua_allow_upload
   * @uses add_action()
   * @uses current_user_can()
   * @uses mnua_is_author_or_above()
   */
  public function __construct() {
    global $mn_user_avatar, $mnua_allow_upload;
    if((bool) $mnua_allow_upload == 1) {
      add_action('user_edit_form_tag', array($this, 'mnua_add_edit_form_multipart_encoding'));
      // Only Subscribers lack delete_posts capability
      if(!current_user_can('delete_posts') && current_user_can('edit_posts') && !$mn_user_avatar->mnua_is_author_or_above()) {
        add_action('admin_menu', array($this, 'mnua_subscriber_remove_menu_pages'));
        add_action('mn_before_admin_bar_render', array($this, 'mnua_subscriber_remove_menu_bar_items'));
        add_action('mn_dashboard_setup', array($this, 'mnua_subscriber_remove_dashboard_widgets'));
        add_action('admin_init', array($this, 'mnua_subscriber_offlimits'));
      }
    }
    add_action('admin_init', array($this, 'mnua_subscriber_capability'));
  }

  /**
   * Allow multipart data in form
   * @since 1.4.1
   */
  public function mnua_add_edit_form_multipart_encoding() {
    echo ' enctype="multipart/form-data"';
  }

  /**
   * Remove menu items
   * @since 1.4
   * @uses remove_menu_page()
   */
  public function mnua_subscriber_remove_menu_pages() {
    remove_menu_page('edit.php');
    remove_menu_page('edit-comments.php');
    remove_menu_page('tools.php');
  }

  /**
   * Remove menu bar items
   * @since 1.5.1
   * @uses object $admin_bar
   * @uses remove_menu()
   */
  public function mnua_subscriber_remove_menu_bar_items() {
    global $admin_bar;
    $admin_bar->remove_menu('comments');
    $admin_bar->remove_menu('new-content');
  }

  /**
   * Remove dashboard items
   * @since 1.4
   * @uses remove_meta_box()
   */
  public function mnua_subscriber_remove_dashboard_widgets() {
    remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
    remove_meta_box('dashboard_recent_drafts', 'dashboard', 'side');
    remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
  }

  /**
   * Restrict access to pages
   * @since 1.4
   * @uses string $pagenow
   * @uses bool $mnua_edit_avatar
   * @uses apply_filters()
   * @uses do_action()
   * @uses mn_die()
   */
  public function mnua_subscriber_offlimits() {
    global $pagenow, $mnua_edit_avatar;
    $offlimits = array('edit.php', 'edit-comments.php', 'post-new.php', 'tools.php');
    if((bool) $mnua_edit_avatar != 1) {
      array_push($offlimits, 'post.php');
    }
    /**
     * Filter restricted pages
     * @since 1.9
     * @param array $offlimits
     */
    $offlimits = apply_filters('mnua_subscriber_offlimits', $offlimits);
    if(in_array($pagenow, $offlimits)) {
      do_action('admin_page_access_denied');
      mn_die(__('You do not have sufficient permissions to access this page.','mn-user-avatar'));
    }
  }

  /**
   * Give subscribers edit_posts capability
   * @since 1.8.3
   * @uses int $blog_id
   * @uses object $mndb
   * @uses bool $mnua_allow_upload
   * @uses bool $mnua_edit_avatar
   * @uses get_blog_prefix()
   * @uses get_option()
   * @uses update_option()
   */
  public function mnua_subscriber_capability() {
    global $blog_id, $mndb, $mnua_allow_upload, $mnua_edit_avatar;
    $mn_user_roles = $mndb->get_blog_prefix($blog_id).'user_roles';
    $user_roles = get_option($mn_user_roles);
    if((bool) $mnua_allow_upload == 1 && (bool) $mnua_edit_avatar == 1) {
      $user_roles['subscriber']['capabilities']['edit_posts'] = true;
    } else {
     if(isset($user_roles['subscriber']['capabilities']['edit_posts'])){
     	unset($user_roles['subscriber']['capabilities']['edit_posts']);
     }
    }
    update_option($mn_user_roles, $user_roles);
  }
}

/**
 * Initialize
 * @since 1.9.5
 */
function mnua_subscriber_init() {
  global $mnua_subscriber;
  $mnua_subscriber = new MN_User_Avatar_Subscriber();
}
add_action('init', 'mnua_subscriber_init');
