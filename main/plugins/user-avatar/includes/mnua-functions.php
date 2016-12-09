<?php
/**
 * Public user functions.
 * 
 * @package User Avatar
 * @version 1.9.13
 */

/**
 * Returns true if user has mn_user_avatar
 * @since 1.8
 * @param int|string $id_or_email
 * @param bool $has_mnua
 * @param object $user
 * @param int $user_id
 * @uses object $mnua_functions
 * @return object has_mn_user_avatar()
 */
function has_mn_user_avatar($id_or_email="", $has_mnua="", $user="", $user_id="") {
  global $mnua_functions;
  return $mnua_functions->has_mn_user_avatar($id_or_email, $has_mnua, $user, $user_id);
}

/**
 * Find MNUA, show get_avatar if empty
 * @since 1.8
 * @param int|string $id_or_email
 * @param int|string $size
 * @param string $align
 * @param string $alt
 * @uses object $mnua_functions
 * @return object get_mn_user_avatar()
 */
function get_mn_user_avatar($id_or_email="", $size="", $align="", $alt="") {
  global $mnua_functions;
  return $mnua_functions->get_mn_user_avatar($id_or_email, $size, $align, $alt);
}

/**
 * Return just the image src
 * @since 1.8
 * @param int|string $id_or_email
 * @param int|string $size
 * @param string $align
 * @uses object $mnua_functions
 * @return object get_mn_user_avatar_src()
 */
function get_mn_user_avatar_src($id_or_email="", $size="", $align="") {
  global $mnua_functions;
  return $mnua_functions->get_mn_user_avatar_src($id_or_email, $size, $align);
}

/**
 * Before wrapper for profile
 * @since 1.6
 * @uses do_action()
 */
function mnua_before_avatar() {
  do_action('mnua_before_avatar');
}

/**
 * After wrapper for profile
 * @since 1.6
 * @uses do_action()
 */
function mnua_after_avatar() {
  do_action('mnua_after_avatar');
}

/**
 * Before avatar container
 * @since 1.6
 * @uses apply_filters()
 * @uses bbp_is_edit()
 * @uses mnuf_has_shortcode()
 */
function mnua_do_before_avatar() {
  $mnua_profile_title = '<h3>'.__('Avatar','mn-user-avatar').'</h3>';
  /**
   * Filter profile title
   * @since 1.9.4
   * @param string $mnua_profile_title
   */
  $mnua_profile_title = apply_filters('mnua_profile_title', $mnua_profile_title);
?>
  <?php if(class_exists('bbPress') && bbp_is_edit()) : // Add to bbPress profile with same style ?>
    <h2 class="entry-title"><?php _e('Local Avatar','mn-user-avatar'); ?></h2>
    <fieldset class="bbp-form">
      <legend><?php _e('Image','mn-user-avatar'); ?></legend>
  <?php elseif(class_exists('MNUF_Main') && mnuf_has_shortcode('mnuf_editprofile')) : // Add to MN User Frontend profile with same style ?>
    <fieldset>
      <legend><?php _e('Local Avatar','mn-user-avatar') ?></legend>
      <table class="mnuf-table">
        <tr>
          <th><label for="mn_user_avatar"><?php _e('Image','mn-user-avatar'); ?></label></th>
          <td>
  <?php else : // Add to profile without table ?>
    <div class="mnua-edit-container">
      <?php echo $mnua_profile_title; ?>
  <?php endif; ?>
  <?php
}
add_action('mnua_before_avatar', 'mnua_do_before_avatar');

/**
 * After avatar container
 * @since 1.6
 * @uses bbp_is_edit()
 * @uses mnuf_has_shortcode()
 */
function mnua_do_after_avatar() {
?>
  <?php if(class_exists('bbPress') && bbp_is_edit()) : // Add to bbPress profile with same style ?>
    </fieldset>
  <?php elseif(class_exists('MNUF_Main') && mnuf_has_shortcode('mnuf_editprofile')) : // Add to MN User Frontend profile with same style ?>
          </td>
        </tr>
      </table>
    </fieldset>
  <?php else : // Add to profile without table ?>
    </div>
  <?php endif; ?>
  <?php
}
add_action('mnua_after_avatar', 'mnua_do_after_avatar');

/**
 * Before wrapper for profile in admin section
 * @since 1.9.4
 * @uses do_action()
 */
function mnua_before_avatar_admin() {
  do_action('mnua_before_avatar_admin');
}

/**
 * After wrapper for profile in admin section
 * @since 1.9.4
 * @uses do_action()
 */
function mnua_after_avatar_admin() {
  do_action('mnua_after_avatar_admin');
}

/**
 * Before avatar container in admin section
 * @since 1.9.4
 */
function mnua_do_before_avatar_admin() {
?>
  <h3><?php _e('Avatar') ?></h3>
  <table class="form-table">
    <tr>
      <th><label for="mn_user_avatar"><?php _e('Image','mn-user-avatar'); ?></label></th>
      <td>
  <?php
}
add_action('mnua_before_avatar_admin', 'mnua_do_before_avatar_admin');

/**
 * After avatar container in admin section
 * @since 1.9.4
 */
function mnua_do_after_avatar_admin() {
?>
      </td>
    </tr>
  </table>
  <?php
}
add_action('mnua_after_avatar_admin', 'mnua_do_after_avatar_admin');

/**
 * Filter for the inevitable complaints about the donation message :(
 * @since 1.6.6
 * @uses do_action()
 */
function mnua_donation_message() {
  do_action('mnua_donation_message');
}

/**
 * Donation message
 * @since 1.6.6
 */
function mnua_do_donation_message() { ?>
 <?php 
}
//add_action('mnua_donation_message', 'mnua_do_donation_message');

/**
 * Register widget
 * @since 1.9.4
 * @uses register_widget()
 */
function mnua_widgets_init() {
  register_widget('MN_User_Avatar_Profile_Widget');
}
add_action('widgets_init', 'mnua_widgets_init');
