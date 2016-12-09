<?php
/**
 * Admin page to change plugin options.
 *
 * @package User Avatar
 * @version 1.9.13
 */

/**
 * @since 1.4
 * @uses bool $show_avatars
 * @uses string $upload_size_limit_with_units
 * @uses object $mnua_admin
 * @uses bool $mnua_allow_upload
 * @uses bool $mnua_disable_gravatar
 * @uses bool $mnua_edit_avatar
 * @uses bool $mnua_resize_crop
 * @uses int int $mnua_resize_h
 * @uses bool $mnua_resize_upload
 * @uses int $mnua_resize_w
 * @uses object $mnua_subscriber
 * @uses bool $mnua_tinymce
 * @uses int $mnua_upload_size_limit
 * @uses string $mnua_upload_size_limit_with_units
 * @uses admin_url()
 * @uses apply_filters()
 * @uses checked()
 * @uses do_action()
 * @uses do_settings_fields()
 * @uses get_option()
 * @uses settings_fields()
 * @uses submit_button()
 * @uses mnua_add_default_avatar()
 */

global $show_avatars, $upload_size_limit_with_units, $mnua_admin, $mnua_allow_upload, $mnua_disable_gravatar, $mnua_edit_avatar, $mnua_resize_crop, $mnua_resize_h, $mnua_resize_upload, $mnua_resize_w, $mnua_subscriber, $mnua_tinymce, $mnua_upload_size_limit, $mnua_upload_size_limit_with_units;
$updated = false;
if(isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true') {
  $updated = true;
}
$hide_size = (bool) $mnua_allow_upload != 1 ? ' style="display:none;"' : "";
$hide_resize = (bool) $mnua_resize_upload != 1 ? ' style="display:none;"' : "";
$mnua_options_page_title = __('User Avatar', 'mn-user-avatar');
/**
 * Filter admin page title
 * @since 1.9
 * @param string $mnua_options_page_title
 */
$mnua_options_page_title = apply_filters('mnua_options_page_title', $mnua_options_page_title);
?>

<div class="wrap">
  <h2><?php echo $mnua_options_page_title; ?></h2>
  <table><tr valign="top">
    <td align="top">
  <form method="post" action="<?php echo admin_url('options.php'); ?>">
    <?php settings_fields('mnua-settings-group'); ?>
    <?php do_settings_fields('mnua-settings-group', ""); ?>
    <?php do_action('mnua_donation_message'); ?>
    <table class="form-table">
      <?php
        // Format settings in table rows
        $mnua_before_settings = array();
        /**
         * Filter settings at beginning of table
         * @since 1.9
         * @param array $mnua_before_settings
         */
        $mnua_before_settings = apply_filters('mnua_before_settings', $mnua_before_settings);
        echo implode("", $mnua_before_settings);
      ?>
      <tr valign="top">
        <th scope="row"><?php _e('Settings'); ?></th>
        <td>
          <?php
            // Format settings in fieldsets
            $mnua_settings = array();
            $mnua_settings['tinymce'] = '<fieldset>
              <label for="mn_user_avatar_tinymce">
                <input name="mn_user_avatar_tinymce" type="checkbox" id="mn_user_avatar_tinymce" value="1" '.checked($mnua_tinymce, 1, 0).' />'
                .__('Add avatar button to Visual Editor', 'mn-user-avatar').'
              </label>
            </fieldset>';
            $mnua_settings['upload'] ='<fieldset>
              <label for="mn_user_avatar_allow_upload">
                <input name="mn_user_avatar_allow_upload" type="checkbox" id="mn_user_avatar_allow_upload" value="1" '.checked($mnua_allow_upload, 1, 0).' />'
                .__('Allow Contributors & Subscribers to upload avatars', 'mn-user-avatar').'
              </label>
            </fieldset>';
            $mnua_settings['gravatar'] ='<fieldset>
              <label for="mn_user_avatar_disable_gravatar">
                <input name="mn_user_avatar_disable_gravatar" type="checkbox" id="mn_user_avatar_disable_gravatar" value="1" '.checked($mnua_disable_gravatar, 1, 0).' />'
                .__('Disable Gravatar and use only local avatars', 'mn-user-avatar').'
              </label>
            </fieldset>';
            /**
             * Filter main settings
             * @since 1.9
             * @param array $mnua_settings
             */
            $mnua_settings = apply_filters('mnua_settings', $mnua_settings);
            echo implode("", $mnua_settings);
          ?>
        </td>
      </tr>
    </table>
    <?php
      // Format settings in table
      $mnua_subscriber_settings = array();
      $mnua_subscriber_settings['subscriber-settings'] = '<div id="mnua-contributors-subscribers"'.$hide_size.'>
        <table class="form-table">
          <tr valign="top">
            <th scope="row">
              <label for="mn_user_avatar_upload_size_limit">'
                .__('Upload Size Limit', 'mn-user-avatar').' '.__('(only for Contributors & Subscribers)', 'mn-user-avatar').'
              </label>
            </th>
            <td>
              <fieldset>
                <legend class="screen-reader-text"><span>'.__('Upload Size Limit', 'mn-user-avatar').' '. __('(only for Contributors & Subscribers)', 'mn-user-avatar').'</span></legend>
                <input name="mn_user_avatar_upload_size_limit" type="text" id="mn_user_avatar_upload_size_limit" value="'.$mnua_upload_size_limit.'" class="regular-text" />
                <span id="mnua-readable-size">'.$mnua_upload_size_limit_with_units.'</span>
                <span id="mnua-readable-size-error">'.sprintf(__('%s exceeds the maximum upload size for this site.','mn-user-avatar'), "").'</span>
                <div id="mnua-slider"></div>
                <span class="description">'.sprintf(__('Maximum upload file size: %d%s.','mn-user-avatar'), esc_html(mn_max_upload_size()), esc_html(' bytes ('.$upload_size_limit_with_units.')')).'</span>
              </fieldset>
              <fieldset>
                <label for="mn_user_avatar_edit_avatar">
                  <input name="mn_user_avatar_edit_avatar" type="checkbox" id="mn_user_avatar_edit_avatar" value="1" '.checked($mnua_edit_avatar, 1, 0).' />'
                  .__('Allow users to edit avatars', 'mn-user-avatar').'
                </label>
              </fieldset>
              <fieldset>
                <label for="mn_user_avatar_resize_upload">
                  <input name="mn_user_avatar_resize_upload" type="checkbox" id="mn_user_avatar_resize_upload" value="1" '.checked($mnua_resize_upload, 1, 0).' />'
                  .__('Resize avatars on upload', 'mn-user-avatar').'
                </label>
              </fieldset>
              <fieldset id="mnua-resize-sizes"'.$hide_resize.'>
                <label for="mn_user_avatar_resize_w">'.__('Width','mn-user-avatar').'</label>
                <input name="mn_user_avatar_resize_w" type="number" step="1" min="0" id="mn_user_avatar_resize_w" value="'.get_option('mn_user_avatar_resize_w').'" class="small-text" />
                <label for="mn_user_avatar_resize_h">'.__('Height','mn-user-avatar').'</label>
                <input name="mn_user_avatar_resize_h" type="number" step="1" min="0" id="mn_user_avatar_resize_h" value="'.get_option('mn_user_avatar_resize_h').'" class="small-text" />
                <br />
                <input name="mn_user_avatar_resize_crop" type="checkbox" id="mn_user_avatar_resize_crop" value="1" '.checked('1', $mnua_resize_crop, 0).' />
                <label for="mn_user_avatar_resize_crop">'.__('Crop avatars to exact dimensions', 'mn-user-avatar').'</label>
              </fieldset>
            </td>
          </tr>
        </table>
      </div>';
      /**
       * Filter Subscriber settings
       * @since 1.9
       * @param array $mnua_subscriber_settings
       */
      $mnua_subscriber_settings = apply_filters('mnua_subscriber_settings', $mnua_subscriber_settings);
      echo implode("", $mnua_subscriber_settings);
    ?>
    <table class="form-table">
      <tr valign="top">
      <th scope="row"><?php _e('Avatar Display','mn-user-avatar'); ?></th>
      <td>
        <fieldset>
          <legend class="screen-reader-text"><span><?php _e('Avatar Display','mn-user-avatar'); ?></span></legend>
          <label for="show_avatars">
          <input type="checkbox" id="show_avatars" name="show_avatars" value="1" <?php checked($show_avatars, 1); ?> />
          <?php _e('Show Avatars','mn-user-avatar'); ?>
          </label>
        </fieldset>
        </td>
      </tr>
        <tr valign="top" id="avatar-rating" <?php echo ((bool) $mnua_disable_gravatar == 1) ? 'style="display:none"' : ''?>>
          <th scope="row"><?php _e('Maximum Rating','mn-user-avatar'); ?></th>
          <td>
            <fieldset>
              <legend class="screen-reader-text"><span><?php _e('Maximum Rating','mn-user-avatar'); ?></span></legend>
              <?php
                $ratings = array(
                  'G' => __('G &#8212; Suitable for all audiences','mn-user-avatar'),
                  'PG' => __('PG &#8212; Possibly offensive, usually for audiences 13 and above','mn-user-avatar'),
                  'R' => __('R &#8212; Intended for adult audiences above 17','mn-user-avatar'),
                  'X' => __('X &#8212; Even more mature than above','mn-user-avatar')
                );
                foreach ($ratings as $key => $rating) :
                  $selected = (get_option('avatar_rating') == $key) ? 'checked="checked"' : "";
                  echo "\n\t<label><input type='radio' name='avatar_rating' value='".esc_attr($key)."' $selected/> $rating</label><br />";
                endforeach;
              ?>
            </fieldset>
          </td>
        </tr>
      <tr valign="top">
        <th scope="row"><?php _e('Default Avatar','mn-user-avatar') ?></th>
        <td class="defaultavatarpicker">
          <fieldset>
            <legend class="screen-reader-text"><span><?php _e('Default Avatar','mn-user-avatar'); ?></span></legend>
            <?php _e('For users without a custom avatar of their own, you can either display a generic logo or a generated one based on their e-mail address.','mn-user-avatar'); ?><br />
            <?php echo $mnua_admin->mnua_add_default_avatar(); ?>
          </fieldset>
        </td>
      </tr>
    </table>
    <?php submit_button(); ?>
  </form>
</td>
  </tr></table>
</div>
