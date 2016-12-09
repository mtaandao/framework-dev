<?php
/**
 * Defines shortcodes.
 *
 * @package User Avatar
 * @version 1.9.13
 */

class MN_User_Avatar_Shortcode {
  /**
   * Constructor
   * @since 1.8
   * @uses object $mn_user_avatar
   * @uses add_action()
   * @uses add_shortcode()
   */
  public function __construct() {
    global $mn_user_avatar;
    add_shortcode('avatar', array($this, 'mnua_shortcode'));
    add_shortcode('avatar_upload', array($this, 'mnua_edit_shortcode'));
    // Add avatar and scripts to avatar_upload
    add_action('mnua_show_profile', array($mn_user_avatar, 'mnua_action_show_user_profile'));
    add_action('mnua_show_profile', array($mn_user_avatar, 'mnua_media_upload_scripts'));
    add_action('mnua_update', array($mn_user_avatar, 'mnua_action_process_option_update'));
    // Add error messages to avatar_upload
    add_action('mnua_update_errors', array($mn_user_avatar, 'mnua_upload_errors'), 10, 3);
  }

  /**
   * Display shortcode
   * @since 1.4
   * @param array $atts
   * @param string $content
   * @uses array $_mn_additional_image_sizes
   * @uses array $all_sizes
   * @uses int $blog_id
   * @uses object $post
   * @uses object $mndb
   * @uses do_shortcode()
   * @uses get_attachment_link()
   * @uses get_blog_prefix()
   * @uses get_option()
   * @uses get_user_by()
   * @uses get_query_var()
   * @uses get_the_author_meta()
   * @uses get_user_meta()
   * @uses get_mn_user_avatar_src()
   * @uses get_mn_user_avatar()
   * @uses image_add_caption()
   * @uses is_author()
   * @uses shortcode_atts()
   * @return string 
   */
  public function mnua_shortcode($atts, $content=null) {
    global $all_sizes, $blog_id, $post, $mndb;
    // Set shortcode attributes
    extract(shortcode_atts(array('user' => "", 'size' => '96', 'align' => "", 'link' => "", 'target' => ""), $atts));
    // Find user by ID, login, slug, or e-mail address
    if(!empty($user)) {
      $user = is_numeric($user) ? get_user_by('id', $user) : get_user_by('login', $user);
      $user = empty($user) ? get_user_by('slug', $user) : $user;
      $user = empty($user) ? get_user_by('email', $user) : $user;
    } else {
      // Find author's name if id_or_email is empty
      $author_name = get_query_var('author_name');
      if(is_author()) {
        // On author page, get user by page slug
        $user = get_user_by('slug', $author_name);
      } else {
        // On post, get user by author meta
        $user_id = get_the_author_meta('ID');
        $user = get_user_by('id', $user_id);
      }
    }
    // Numeric sizes leave as-is
    $get_size = $size;
    // Check for custom image sizes if there are captions
    if(!empty($content)) {
      if(in_array($size, $all_sizes)) {
        if(in_array($size, array('original', 'large', 'medium', 'thumbnail'))) {
          $get_size = ($size == 'original') ? get_option('large_size_w') : get_option($size.'_size_w');
        } else {
          $get_size = $_mn_additional_image_sizes[$size]['width'];
        }
      }
    }
    // Get user ID
    $id_or_email = !empty($user) ? $user->ID : 'unknown@gravatar.com';
    // Check if link is set
    if(!empty($link)) {
      // CSS class is same as link type, except for URL
      $link_class = $link;
      if($link == 'file') {
        // Get image src
        $link = get_mn_user_avatar_src($id_or_email, 'original');
      } elseif($link == 'attachment') {
        // Get attachment URL
        $link = get_attachment_link(get_the_author_meta($mndb->get_blog_prefix($blog_id).'user_avatar', $id_or_email));
      } else {
        // URL
        $link_class = 'custom';
      }
      // Open in new window
      $target_link = !empty($target) ? ' target="'.$target.'"' : "";
      // Wrap the avatar inside the link
      $html = '<a href="'.$link.'" class="mn-user-avatar-link mn-user-avatar-'.$link_class.'"'.$target_link.'>'.get_mn_user_avatar($id_or_email, $get_size, $align).'</a>';
    } else {
      $html = get_mn_user_avatar($id_or_email, $get_size, $align);
    }
    // Check if caption is set
    if(!empty($content)) {
      // Get attachment ID
      $mnua = get_user_meta($id_or_email, $mndb->get_blog_prefix($blog_id).'user_avatar', true);
      // Clean up caption
      $content = trim($content);
      $content = preg_replace('/\r|\n/', "", $content);
      $content = preg_replace('/<\/p><p>/', "", $content, 1);
      $content = preg_replace('/<\/p><p>$/', "", $content);
      $content = str_replace('</p><p>', "<br /><br />", $content);
      $avatar = do_shortcode(image_add_caption($html, $mnua, $content, $title="", $align, $link, $get_size, $alt=""));
    } else {
      $avatar = $html;
    }
    return $avatar;
  }

  /**
   * Update user
   * @since 1.8
   * @param bool $user_id
   * @uses add_query_arg()
   * @uses apply_filters()
   * @uses do_action_ref_array()
   * @uses mn_get_referer()
   * @uses mn_redirect()
   * @uses mn_safe_redirect()
   */
  private function mnua_edit_user($user_id=0) {
    $update = $user_id ? true : false;
    $user = new stdClass;
    $errors = new MN_Error();
    do_action_ref_array('mnua_update_errors', array(&$errors, $update, &$user));
    if($errors->get_error_codes()) {
      // Return with errors
      return $errors;
    }
    if($update) {
      // Redirect with updated variable
      $redirect_url = esc_url_raw(add_query_arg(array('updated' => '1'), mn_get_referer()));
      /**
       * Filter redirect URL
       * @since 1.9.12
       * @param string $redirect_url
       */
      $redirect_url = apply_filters('mnua_edit_user_redirect_url', $redirect_url);
      /**
       * Filter mn_safe_redirect or mn_redirect
       * @since 1.9.12
       * @param bool $safe_redirect
       */
      $safe_redirect = apply_filters('mnua_edit_user_safe_redirect', true);
      $safe_redirect ? mn_safe_redirect($redirect_url) : mn_redirect($redirect_url);
      exit;
    }
  }

  /**
   * Edit shortcode
   * @since 1.8
   * @param array $atts
   * @uses $mn_user_avatar
   * @uses $mnua_allow_upload
   * @uses current_user_can()
   * @uses do_action()
   * @uses get_error_messages()
   * @uses get_user_by()
   * @uses is_user_logged_in()
   * @uses is_mn_error()
   * @uses shortcode_atts()
   * @uses mnua_edit_form()
   * @uses mnua_edit_user()
   * @uses mnua_is_author_or_above()
   * @return string
   */
  public function mnua_edit_shortcode($atts) {
    global $current_user, $errors, $mn_user_avatar, $mnua_allow_upload;
    // Shortcode only works for users with permission
    if($mn_user_avatar->mnua_is_author_or_above() || ((bool) $mnua_allow_upload == 1 && is_user_logged_in())) {
      extract(shortcode_atts(array('user' => ""), $atts));
      // Default user is current user
      $valid_user = $current_user;
      // Find user by ID, login, slug, or e-mail address
      if(!empty($user)) {
        $get_user = is_numeric($user) ? get_user_by('id', $user) : get_user_by('login', $user);
        $get_user = empty($get_user) ? get_user_by('slug', $user) : $get_user;
        $get_user = empty($get_user) ? get_user_by('email', $user) : $get_user;
        // Check if current user can edit this user
        $valid_user = current_user_can('edit_user', $get_user) ? $get_user : null;
      }
      // Show form only for valid user
      if($valid_user) {
        // Save
        if(isset($_POST['submit']) && $_POST['submit'] && $_POST['mnua_action'] == 'update') {
          do_action('mnua_update', $valid_user->ID);
          // Check for errors
          $errors = $this->mnua_edit_user($valid_user->ID);
        }
        // Errors
        if(isset($errors) && is_mn_error($errors)) {
          echo '<div class="error"><p>'.implode("</p>\n<p>", $errors->get_error_messages()).'</p></div>';
        } elseif(isset($_GET['updated']) && $_GET['updated'] == '1') {
          echo '<div class="updated"><p><strong>'.__('Profile updated.','mn-user-avatar').'</strong></p></div>';
        }
        // Edit form
        return $this->mnua_edit_form($valid_user);
      }
    }
  }

  /**
   * Edit form
   * @since 1.8
   * @param object $user
   * @uses do_action()
   * @uses submit_button()
   * @uses mn_nonce_field()
   */
  private function mnua_edit_form($user) {
    ob_start();
  ?>
    <form id="mnua-edit-<?php echo $user->ID; ?>" class="mnua-edit" action="" method="post" enctype="multipart/form-data">
      <?php do_action('mnua_show_profile', $user); ?>
      <input type="hidden" name="mnua_action" value="update" />
      <input type="hidden" name="user_id" id="user_id" value="<?php echo esc_attr($user->ID); ?>" />
      <?php mn_nonce_field('update-user_'.$user->ID); ?>
      <?php submit_button(__('Update Profile','mn-user-avatar')); ?>
    </form>
  <?php
    return ob_get_clean();
  }
}

/**
 * Initialize
 * @since 1.9.2
 */
function mnua_shortcode_init() {
  global $mnua_shortcode;
  $mnua_shortcode = new MN_User_Avatar_Shortcode();
  // Clean output
  ob_start();
}
add_action('init', 'mnua_shortcode_init');
