<?php
/**
 * Defines all profile and upload settings.
 *
 * @package User Avatar
 * @version 1.9.13
 */

class MN_User_Avatar {
  /**
   * Constructor
   * @since 1.8
   * @uses string $pagenow
   * @uses bool $show_avatars
   * @uses object $mnua_admin
   * @uses bool $mnua_allow_upload
   * @uses add_action()
   * @uses add_filterI]()
   * @uses is_admin()
   * @uses is_user_logged_in()
   * @uses mnua_is_author_or_above()
   * @uses mnua_is_menu_page()
   */
  public function __construct() {
    global $pagenow, $show_avatars, $mnua_admin, $mnua_allow_upload;
    // Add MNUA to profile for users with permission
    if($this->mnua_is_author_or_above() || ((bool) $mnua_allow_upload == 1 && is_user_logged_in())) {
      // Profile functions and scripts
      add_action('show_user_profile', array('mn_user_avatar', 'mnua_action_show_user_profile'));
      add_action('personal_options_update', array($this, 'mnua_action_process_option_update'));
      add_action('edit_user_profile', array('mn_user_avatar', 'mnua_action_show_user_profile'));
      add_action('edit_user_profile_update', array($this, 'mnua_action_process_option_update'));
      add_action('user_new_form', array($this, 'mnua_action_show_user_profile'));
      add_action('user_register', array($this, 'mnua_action_process_option_update'));
      // Admin scripts
      $pages = array('profile.php', 'options-discussion.php', 'user-edit.php', 'user-new.php');
      if(in_array($pagenow, $pages) || $mnua_admin->mnua_is_menu_page()) {
        add_action('admin_enqueue_scripts', array($this, 'mnua_media_upload_scripts'));
      }
      // Front pages
      if(!is_admin()) {
        add_action('show_user_profile', array('mn_user_avatar', 'mnua_media_upload_scripts'));
        add_action('edit_user_profile', array('mn_user_avatar', 'mnua_media_upload_scripts'));
      }
      if(!$this->mnua_is_author_or_above()) {
        // Upload errors
        add_action('user_profile_update_errors', array($this, 'mnua_upload_errors'), 10, 3);
        // Prefilter upload size
        add_filter('mn_handle_upload_prefilter', array($this, 'mnua_handle_upload_prefilter'));
      }
    }
    add_filter('media_view_settings', array($this, 'mnua_media_view_settings'), 10, 1);
  }

  /**
   * Avatars have no parent posts
   * @since 1.8.4
   * @param array $settings
   * @uses object $post
   * @uses bool $mnua_is_profile
   * @uses is_admin()
   * array $settings
   */
  public function mnua_media_view_settings($settings) {
    global $post, $mnua_is_profile;
    // Get post ID so not to interfere with media uploads
    $post_id = is_object($post) ? $post->ID : 0;
    // Don't use post ID on front pages if there's a MNUA uploader
    $settings['post']['id'] = (!is_admin() && $mnua_is_profile == 1) ? 0 : $post_id;
    return $settings;
  }

  /**
   * Media Uploader
   * @since 1.4
   * @param object $user
   * @uses object $current_user
   * @uses string $mustache_admin
   * @uses string $pagenow
   * @uses object $post
   * @uses bool $show_avatars
   * @uses object $mn_user_avatar
   * @uses object $mnua_admin
   * @uses object $mnua_functions
   * @uses bool $mnua_is_profile
   * @uses int $mnua_upload_size_limit
   * @uses get_user_by()
   * @uses mn_enqueue_script()
   * @uses mn_enqueue_media()
   * @uses mn_enqueue_style()
   * @uses mn_localize_script()
   * @uses mn_max_upload_size()
   * @uses mnua_get_avatar_original()
   * @uses mnua_is_author_or_above()
   * @uses mnua_is_menu_page()
   */
  public static function mnua_media_upload_scripts($user="") {
    global $current_user, $mustache_admin, $pagenow, $post, $show_avatars, $mn_user_avatar, $mnua_admin, $mnua_functions, $mnua_is_profile, $mnua_upload_size_limit;
    // This is a profile page
    $mnua_is_profile = 1;
    $user = ($pagenow == 'user-edit.php' && isset($_GET['user_id'])) ? get_user_by('id', $_GET['user_id']) : $current_user;
    mn_enqueue_style('mn-user-avatar', MNUA_URL.'css/mn-user-avatar.css', "", MNUA_VERSION);
    mn_enqueue_script('jquery');
    if($mn_user_avatar->mnua_is_author_or_above()) {
      mn_enqueue_script('admin-bar');
      mn_enqueue_media(array('post' => $post));
      mn_enqueue_script('mn-user-avatar', MNUA_URL.'js/mn-user-avatar.js', array('jquery', 'media-editor'), MNUA_VERSION, true);
    } else {
      mn_enqueue_script('mn-user-avatar', MNUA_URL.'js/mn-user-avatar-user.js', array('jquery'), MNUA_VERSION, true);
    }
    // Admin scripts
    if($pagenow == 'options-discussion.php' || $mnua_admin->mnua_is_menu_page()) {
      // Size limit slider
      mn_enqueue_script('jquery-ui-slider');
      mn_enqueue_style('mn-user-avatar-jqueryui', MNUA_URL.'css/jquery.ui.slider.css', "", null);
      // Default avatar
      mn_localize_script('mn-user-avatar', 'mnua_custom', array('avatar_thumb' => $mustache_admin));
      // Settings control
      mn_enqueue_script('mn-user-avatar-admin', MNUA_URL.'js/mn-user-avatar-admin.js', array('mn-user-avatar'), MNUA_VERSION, true);
      mn_localize_script('mn-user-avatar-admin', 'mnua_admin', array('upload_size_limit' => $mnua_upload_size_limit, 'max_upload_size' => mn_max_upload_size()));
    } else {
      // Original user avatar
      $avatar_medium_src = (bool) $show_avatars == 1 ? $mnua_functions->mnua_get_avatar_original($user->user_email, 'medium') : includes_url().'images/blank.gif';
      mn_localize_script('mn-user-avatar', 'mnua_custom', array('avatar_thumb' => $avatar_medium_src));
    }
  }

  /**
   * Add to edit user profile
   * @since 1.4
   * @param object $user
   * @uses int $blog_id
   * @uses object $current_user
   * @uses bool $show_avatars
   * @uses object $mndb
   * @uses object $mn_user_avatar
   * @uses bool $mnua_allow_upload
   * @uses bool $mnua_edit_avatar
   * @uses object $mnua_functions
   * @uses string $mnua_upload_size_limit_with_units
   * @uses add_query_arg()
   * @uses admin_url()
   * @uses do_action()
   * @uses get_blog_prefix()
   * @uses get_user_meta()
   * @uses get_mn_user_avatar_src()
   * @uses has_mn_user_avatar()
   * @uses is_admin()
   * @uses mnua_author()
   * @uses mnua_get_avatar_original()
   * @uses mnua_is_author_or_above()
   */
  public static function mnua_action_show_user_profile($user) {
    global $blog_id, $current_user, $show_avatars, $mndb, $mn_user_avatar, $mnua_allow_upload, $mnua_edit_avatar, $mnua_functions, $mnua_upload_size_limit_with_units;
	  
    $has_mn_user_avatar = has_mn_user_avatar(@$user->ID);
    // Get MNUA attachment ID
    $mnua = get_user_meta(@$user->ID, $mndb->get_blog_prefix($blog_id).'user_avatar', true);
    // Show remove button if MNUA is set
    $hide_remove = !$has_mn_user_avatar ? 'mnua-hide' : "";
    // Hide image tags if show avatars is off
    $hide_images = !$has_mn_user_avatar && (bool) $show_avatars == 0 ? 'mnua-no-avatars' : "";
    // If avatars are enabled, get original avatar image or show blank
    $avatar_medium_src = (bool) $show_avatars == 1 ? $mnua_functions->mnua_get_avatar_original(@$user->user_email, 'medium') : includes_url().'images/blank.gif';
    // Check if user has mn_user_avatar, if not show image from above
    $avatar_medium = $has_mn_user_avatar ? get_mn_user_avatar_src($user->ID, 'medium') : $avatar_medium_src;
    // Check if user has mn_user_avatar, if not show image from above
    $avatar_thumbnail = $has_mn_user_avatar ? get_mn_user_avatar_src($user->ID, 96) : $avatar_medium_src;
    $edit_attachment_link = esc_url(add_query_arg(array('post' => $mnua, 'action' => 'edit'), admin_url('post.php')));
    // Chck if admin page
    $is_admin = is_admin() ? '_admin' : "";
  ?>
    <?php do_action('mnua_before_avatar'.$is_admin); ?>
    <input type="hidden" name="mn-user-avatar" id="<?php echo ($user=='add-new-user') ? 'mn-user-avatar' : 'mn-user-avatar-existing'?>" value="<?php echo $mnua; ?>" />
    <?php if($mn_user_avatar->mnua_is_author_or_above()) : // Button to launch Media Uploader ?>
      <p id="<?php echo ($user=='add-new-user') ? 'mnua-add-button' : 'mnua-add-button-existing'?>"><button type="button" class="button" id="<?php echo ($user=='add-new-user') ? 'mnua-add' : 'mnua-add-existing'?>" name="<?php echo ($user=='add-new-user') ? 'mnua-add' : 'mnua-add-existing'?>" data-title="<?php _e('Choose Image','mn-user-avatar'); ?>: <?php echo $user->display_name; ?>"><?php _e('Choose Image','mn-user-avatar'); ?></button></p>
    <?php elseif(!$mn_user_avatar->mnua_is_author_or_above()) : // Upload button ?>
      <p id="<?php echo ($user=='add-new-user') ? 'mnua-upload-button' : 'mnua-upload-button-existing'?>">
        <input name="mnua-file" id="<?php echo ($user=='add-new-user') ? 'mnua-file' : 'mnua-file-existing'?>" type="file" />
        <button type="submit" class="button" id="<?php echo ($user=='add-new-user') ? 'mnua-upload' : 'mnua-upload-existing'?>" name="submit" value="<?php _e('Upload','mn-user-avatar'); ?>"><?php _e('Upload','mn-user-avatar'); ?></button>
      </p>
      <p id="<?php echo ($user=='add-new-user') ? 'mnua-upload-messages' : 'mnua-upload-messages-existing'?>">
        <span id="<?php echo ($user=='add-new-user') ? 'mnua-max-upload' : 'mnua-max-upload-existing'?>" class="description"><?php printf(__('Maximum upload file size: %d%s.','mn-user-avatar'), esc_html($mnua_upload_size_limit_with_units), esc_html('KB')); ?></span>
        <span id="<?php echo ($user=='add-new-user') ? 'mnua-allowed-files' : 'mnua-allowed-files-existing'?>" class="description"><?php _e('Allowed Files','mn-user-avatar'); ?>: <?php _e('<code>jpg jpeg png gif</code>','mn-user-avatar'); ?></span>
      </p>
    <?php endif; ?>
    <div id="<?php echo ($user=='add-new-user') ? 'mnua-images' : 'mnua-images-existing'?>" class="<?php echo $hide_images; ?>">
      <p id="<?php echo ($user=='add-new-user') ? 'mnua-preview' : 'mnua-preview-existing'?>">
        <img src="<?php echo $avatar_medium; ?>" alt="" />
        <span class="description"><?php _e('Original Size','mn-user-avatar'); ?></span>
      </p>
      <p id="<?php echo ($user=='add-new-user') ? 'mnua-thumbnail' : 'mnua-thumbnail-existing'?>">
        <img src="<?php echo $avatar_thumbnail; ?>" alt="" />
        <span class="description"><?php _e('Thumbnail','mn-user-avatar'); ?></span>
      </p>
      <p id="<?php echo ($user=='add-new-user') ? 'mnua-remove-button' : 'mnua-remove-button-existing'?>" class="<?php echo $hide_remove; ?>">
        <button type="button" class="button" id="<?php echo ($user=='add-new-user') ? 'mnua-remove' : 'mnua-remove-existing'?>" name="mnua-remove"><?php _e('Remove Image','mn-user-avatar'); ?></button>
        <?php if((bool) $mnua_edit_avatar == 1 && !$mn_user_avatar->mnua_is_author_or_above() && has_mn_user_avatar($current_user->ID) && $mn_user_avatar->mnua_author($mnua, $current_user->ID)) : // Edit button ?>
          <span id="<?php echo ($user=='add-new-user') ? 'mnua-edit-attachment' : 'mnua-edit-attachment-existing'?>"><a href="<?php echo $edit_attachment_link; ?>" class="edit-attachment" target="_blank"><?php _e('Edit Image','mn-user-avatar'); ?></a></span>
        <?php endif; ?>
      </p>
      <p id="<?php echo ($user=='add-new-user') ? 'mnua-undo-button' : 'mnua-undo-button-existing'?>"><button type="button" class="button" id="<?php echo ($user=='add-new-user') ? 'mnua-undo' : 'mnua-undo-existing'?>" name="mnua-undo"><?php _e('Undo','mn-user-avatar'); ?></button></p>
    </div>
    <?php do_action('mnua_after_avatar'.$is_admin); ?>
  <?php
  }

  /**
   * Add upload error messages
   * @since 1.7.1
   * @param array $errors
   * @param bool $update
   * @param object $user
   * @uses int $mnua_upload_size_limit
   * @uses add()
   * @uses mn_upload_dir()
   */
  public static function mnua_upload_errors($errors, $update, $user) {
    global $mnua_upload_size_limit;
    if($update && !empty($_FILES['mnua-file'])) {
      $size = $_FILES['mnua-file']['size'];
      $type = $_FILES['mnua-file']['type'];
      $upload_dir = mn_upload_dir();
      // Allow only JPG, GIF, PNG
      if(!empty($type) && !preg_match('/(jpe?g|gif|png)$/i', $type)) {
        $errors->add('mnua_file_type', __('This file is not an image. Please try another.','mn-user-avatar'));
      }
      // Upload size limit
      if(!empty($size) && $size > $mnua_upload_size_limit) {
        $errors->add('mnua_file_size', __('Memory exceeded. Please try another smaller file.','mn-user-avatar'));
      }
      // Check if directory is writeable
      if(!is_writeable($upload_dir['path'])) {
        $errors->add('mnua_file_directory', sprintf(__('Unable to create directory %s. Is its parent directory writable by the server?','mn-user-avatar'), $upload_dir['path']));
      }
    }
  }

  /**
   * Set upload size limit
   * @since 1.5
   * @param object $file
   * @uses int $mnua_upload_size_limit
   * @uses add_action()
   * @return object $file
   */
  public function mnua_handle_upload_prefilter($file) {
    global $mnua_upload_size_limit;
    $size = $file['size'];
    if(!empty($size) && $size > $mnua_upload_size_limit) {
      /**
       * Error handling that only appears on front pages
       * @since 1.7
       */
      function mnua_file_size_error($errors, $update, $user) {
        $errors->add('mnua_file_size', __('Memory exceeded. Please try another smaller file.','mn-user-avatar'));
      }
      add_action('user_profile_update_errors', 'mnua_file_size_error', 10, 3);
      return;
    }
    return $file;
  }

  /**
   * Update user meta
   * @since 1.4
   * @param int $user_id
   * @uses int $blog_id
   * @uses object $post
   * @uses object $mndb
   * @uses object $mn_user_avatar
   * @uses bool $mnua_resize_crop
   * @uses int $mnua_resize_h
   * @uses bool $mnua_resize_upload
   * @uses int $mnua_resize_w
   * @uses add_post_meta()
   * @uses delete_metadata()
   * @uses get_blog_prefix()
   * @uses is_mn_error()
   * @uses update_post_meta()
   * @uses update_user_meta()
   * @uses mn_delete_attachment()
   * @uses mn_generate_attachment_metadata()
   * @uses mn_get_image_editor()
   * @uses mn_handle_upload()
   * @uses mn_insert_attachment()
   * @uses MN_Query()
   * @uses mn_read_image_metadata()
   * @uses mn_reset_query()
   * @uses mn_update_attachment_metadata()
   * @uses mn_upload_dir()
   * @uses mnua_is_author_or_above()
   * @uses object $mnua_admin
   * @uses mnua_has_gravatar()
   */
  public static function mnua_action_process_option_update($user_id) {
    global $blog_id, $post, $mndb, $mn_user_avatar, $mnua_resize_crop, $mnua_resize_h, $mnua_resize_upload, $mnua_resize_w, $mnua_admin;
    // Check if user has publish_posts capability
    if($mn_user_avatar->mnua_is_author_or_above()) {
      $mnua_id = isset($_POST['mn-user-avatar']) ? strip_tags($_POST['mn-user-avatar']) : "";
      // Remove old attachment postmeta
      delete_metadata('post', null, '_mn_attachment_mn_user_avatar', $user_id, true);
      // Create new attachment postmeta
      add_post_meta($mnua_id, '_mn_attachment_mn_user_avatar', $user_id);
      // Update usermeta
      update_user_meta($user_id, $mndb->get_blog_prefix($blog_id).'user_avatar', $mnua_id);
    } else {
      // Remove attachment info if avatar is blank
      if(isset($_POST['mn-user-avatar']) && empty($_POST['mn-user-avatar'])) {
        // Delete other uploads by user
        $q = array(
          'author' => $user_id,
          'post_type' => 'attachment',
          'post_status' => 'inherit',
          'posts_per_page' => '-1',
          'meta_query' => array(
            array(
              'key' => '_mn_attachment_mn_user_avatar',
              'value' => "",
              'compare' => '!='
            )
          )
        );
        $avatars_mn_query = new MN_Query($q);
        while($avatars_mn_query->have_posts()) : $avatars_mn_query->the_post();
          mn_delete_attachment($post->ID);
        endwhile;
        mn_reset_query();
        // Remove attachment postmeta
        delete_metadata('post', null, '_mn_attachment_mn_user_avatar', $user_id, true);
        // Remove usermeta
        update_user_meta($user_id, $mndb->get_blog_prefix($blog_id).'user_avatar', "");
      }
      // Create attachment from upload
      if(isset($_POST['submit']) && $_POST['submit'] && !empty($_FILES['mnua-file'])) {
        $name = $_FILES['mnua-file']['name'];
        $file = mn_handle_upload($_FILES['mnua-file'], array('test_form' => false));
        $type = $_FILES['mnua-file']['type'];
        $upload_dir = mn_upload_dir();
        if(is_writeable($upload_dir['path'])) {
          if(!empty($type) && preg_match('/(jpe?g|gif|png)$/i', $type)) {
            // Resize uploaded image
            if((bool) $mnua_resize_upload == 1) {
              // Original image
              $uploaded_image = mn_get_image_editor($file['file']);
              // Check for errors
              if(!is_mn_error($uploaded_image)) {
                // Resize image
                $uploaded_image->resize($mnua_resize_w, $mnua_resize_h, $mnua_resize_crop);
                // Save image
                $resized_image = $uploaded_image->save($file['file']);
              }
            }
            // Break out file info
            $name_parts = pathinfo($name);
            $name = trim(substr($name, 0, -(1 + strlen($name_parts['extension']))));
            $url = $file['url'];
            $file = $file['file'];
            $title = $name;
            // Use image exif/iptc data for title if possible
            if($image_meta = @mn_read_image_metadata($file)) {
              if(trim($image_meta['title']) && !is_numeric(sanitize_title($image_meta['title']))) {
                $title = $image_meta['title'];
              }
            }
            // Construct the attachment array
            $attachment = array(
              'guid'           => $url,
              'post_mime_type' => $type,
              'post_title'     => $title,
              'post_content'   => ""
            );
            // This should never be set as it would then overwrite an existing attachment
            if(isset($attachment['ID'])) {
              unset($attachment['ID']);
            }
            // Save the attachment metadata
            $attachment_id = mn_insert_attachment($attachment, $file);
            if(!is_mn_error($attachment_id)) {
              // Delete other uploads by user
              $q = array(
                'author' => $user_id,
                'post_type' => 'attachment',
                'post_status' => 'inherit',
                'posts_per_page' => '-1',
                'meta_query' => array(
                  array(
                    'key' => '_mn_attachment_mn_user_avatar',
                    'value' => "",
                    'compare' => '!='
                  )
                )
              );
              $avatars_mn_query = new MN_Query($q);
              while($avatars_mn_query->have_posts()) : $avatars_mn_query->the_post();
                mn_delete_attachment($post->ID);
              endwhile;
              mn_reset_query();
              mn_update_attachment_metadata($attachment_id, mn_generate_attachment_metadata($attachment_id, $file));
              // Remove old attachment postmeta
              delete_metadata('post', null, '_mn_attachment_mn_user_avatar', $user_id, true);
              // Create new attachment postmeta
              update_post_meta($attachment_id, '_mn_attachment_mn_user_avatar', $user_id);
              // Update usermeta
              update_user_meta($user_id, $mndb->get_blog_prefix($blog_id).'user_avatar', $attachment_id);
            }
          }
        }
      }
    }
	
  }

  /**
   * Check attachment is owned by user
   * @since 1.4
   * @param int $attachment_id
   * @param int $user_id
   * @param bool $mnua_author
   * @uses get_post()
   * @return bool 
   */
  private function mnua_author($attachment_id, $user_id, $mnua_author=0) {
    $attachment = get_post($attachment_id);
    if(!empty($attachment) && $attachment->post_author == $user_id) {
      $mnua_author = true;
    }
    return (bool) $mnua_author;
  }

  /**
   * Check if current user has at least Author privileges
   * @since 1.8.5
   * @uses current_user_can()
   * @uses apply_filters()
   * @return bool
   */
  public function mnua_is_author_or_above() {
    $is_author_or_above = (current_user_can('edit_published_posts') && current_user_can('upload_files') && current_user_can('publish_posts') && current_user_can('delete_published_posts')) ? true : false;
    /**
     * Filter Author privilege check
     * @since 1.9.2
     * @param bool $is_author_or_above
     */
    return (bool) apply_filters('mnua_is_author_or_above', $is_author_or_above);
  }
}

/**
 * Initialize MN_User_Avatar
 * @since 1.8
 */
function mnua_init() {
  global $mn_user_avatar;
  $mn_user_avatar = new MN_User_Avatar();
}
add_action('init', 'mnua_init');
