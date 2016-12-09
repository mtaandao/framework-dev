<?php

/**
 * Enqueue mn-avatar js file to user-edit and profile page only.
 *
 * @param void
 *
 * @return void.
 */
function mn_avatar_enqueue_scripts() {
	global $pagenow;

	if ( 'profile.php' == $pagenow || 'user-edit.php' == $pagenow )
		mn_enqueue_script( 'mn-avatar.js', plugin_dir_url( __FILE__ ) . '/js/mn-avatar.js' );
}
add_action( 'admin_enqueue_scripts', 'mn_avatar_enqueue_scripts' );

/**
 * Add sub menu page to the Users or Profile menu.
 *
 * @param void
 *
 * @return string The resulting page.
 */
function mn_avatar_users_menu(){
	add_users_page( 'Social Avatar', 'Social Avatar', 'activate_plugins', 'mn-avatar', 'mn_avatar_admin' );
}
add_action( 'admin_menu', 'mn_avatar_users_menu' );

function mn_avatar_admin() {
	// Get the MN avatar capability value
	$mn_avatar_capability = get_option( 'mn_avatar_capability', 'read' );

	// MN Avatar settings section.
	$html  = '';
	$html .= '<form id="mn-avatar-settings" method="post" action="">';
	$html .= '<h3>Social Avatar Settings</h3>';
	$html .= '<table class="form-table">';
	$html .= '<tr><th><label for="mn-avatar-capabilty">Minimum Role Required</label></th>';
	$html .= '<td><select id="mn-avatar-capability" name="mn-avatar-capability">';
	$html .= '<option value="read"' . selected( $mn_avatar_capability, 'read', false ) . '>Subscriber</option>';
	$html .= '<option value="edit_posts"' . selected( $mn_avatar_capability, 'edit_posts', false ) . '>Contributor</option>';
	$html .= '<option value="edit_published_posts"' . selected( $mn_avatar_capability, 'edit_published_posts', false ) . '>Author</option>';
	$html .= '<option value="moderate_comments"' . selected( $mn_avatar_capability, 'moderate_comments', false ) . '>Editor</option>';
	$html .= '<option value="activate_plugins"' . selected( $mn_avatar_capability, 'activate_plugins', false ) . '>Administrator</option>';
	$html .= '</select></td></tr>';
	$html .= '</table>';
	$html .= '<p class="submit"><input type="submit" class="button button-primary" id="submit" value="Save Changes"></p>';
	$html .= '</form>';
	$html .= '<h3>To change your avatar to a social media profile picture (Facebook or Google+) go to the bottom of your profile page and <br>enter your Facebook ID and then "Use Facebook profile picture as avatar."</h3>';

	echo $html;
}

// Saving the MN Avatar settings details.
if ( isset( $_POST['mn-avatar-capability'] ) )
	update_option( 'mn_avatar_capability', $_POST['mn-avatar-capability'] );

/**
 * Adds the MN Avatar section in the user profile page.
 *
 * @param object $profileuser Contains the details of the current profile user
 *
 * @return string $html MN Avatar section in the user profile page
 */
function mn_avatar_add_extra_profile_fields( $profileuser ) {
	// Get the MN avatar capability value
	$mn_avatar_capability = get_option( 'mn_avatar_capability', 'read' );

	if ( ! current_user_can( $mn_avatar_capability ) )
		return;

	// Getting the usermeta
	$mn_avatar_profile = get_user_meta( $profileuser->ID, 'mn_avatar_profile', true );
	$mn_fb_profile     = get_user_meta( $profileuser->ID, 'mn_fb_profile', true );
	$mn_gplus_profile  = get_user_meta( $profileuser->ID, 'mn_gplus_profile', true );

	// MN Avatar section html in the user profile page.
	$html  = '';
	$html .= '<h3>' . apply_filters( 'mn_social_avatar_heading', 'Social Avatar Options' ) . '</h3>';
	$html .= '<table class="form-table">';
	$html .= '<tr><th><label for="facebook-profile">Facebook User ID(numeric)</label></th>';
	$html .= '<td><input type="text" name="fb-profile" id="fb-profile" value="' . $mn_fb_profile . '" class="regular-text" />&nbsp;&nbsp;';
	$html .= '<span><a href="http://findmyfacebookid.com/" target="_blank">Find your facebook id here</a></span></td>';
	$html .= '<tr><th><label for="use-fb-profile">Use Facebook Profile as Avatar</label></th>';
	$html .= '<td><input type="checkbox" name="mn-avatar-profile" value="mn-facebook" ' . checked( $mn_avatar_profile, 'mn-facebook', false ) . '></td></tr>';
	$html .= '<tr><th><label for="gplus-profile">Google+ id</label></th>';
	$html .= '<td><input type="text" name="gplus-profile" id="gplus-profile" value="' . $mn_gplus_profile . '" class="regular-text" /></td></tr>';
	$html .= '<tr><th><label for="use-gplus-profile">Use Google+ Profile as Avatar</label></th>';
	$html .= '<td><input type="checkbox" name="mn-avatar-profile" value="mn-gplus"' . checked( $mn_avatar_profile, 'mn-gplus', false ) . '></td></tr>';
	$html .= '<tr><th><label for="gplus-clear-cache">Clear Google+ Cache</label></th>';
	$html .= '<td><input type="button" name="mn-gplus-clear" value="Clear Cache" user="' . $profileuser->ID . '"><span id="msg"></span></td></tr>';
	$html .= '</table>';

	echo $html;
}
add_action( 'show_user_profile', 'mn_avatar_add_extra_profile_fields' );
add_action( 'edit_user_profile', 'mn_avatar_add_extra_profile_fields' );

/**
 * Saving the MN Avatar details in the mn usermeta table.
 *
 * @param int $user_id id of the current user.
 *
 * @return void
 */
function mn_avatar_save_extra_profile_fields( $user_id ) {
	// Saving the MN Avatar details.
	update_user_meta( $user_id, 'mn_fb_profile', trim( $_POST['fb-profile'] ) );
	update_user_meta( $user_id, 'mn_gplus_profile', trim( $_POST['gplus-profile'] ) );
	update_user_meta( $user_id, 'mn_avatar_profile', $_POST['mn-avatar-profile'] );
}
add_action( 'personal_options_update', 'mn_avatar_save_extra_profile_fields' );
add_action( 'edit_user_profile_update', 'mn_avatar_save_extra_profile_fields' );

/**
 * Replaces the default engravatar with the Facebook profile picture.
 *
 * @param string $avatar The default avatar
 *
 * @param int $id_or_email The user id
 *
 * @param int $size The size of the avatar
 *
 * @param string $default The url of the Wordpress default avatar
 *
 * @param string $alt Alternate text for the avatar.
 *
 * @return string $avatar The modified avatar
 */
function mn_fb_avatar( $avatar, $id_or_email, $size, $default, $alt ) {
	// Getting the user id.
	if ( is_int( $id_or_email ) )
		$user_id = $id_or_email;

	if ( is_object( $id_or_email ) )
		$user_id = $id_or_email->user_id;

	if ( is_string( $id_or_email ) ) {
		$user = get_user_by( 'email', $id_or_email );
		if ( $user )
			$user_id = $user->ID;
		else
			$user_id = $id_or_email;
	}

	// Getting the user details
	$mn_avatar_profile    = get_user_meta( $user_id, 'mn_avatar_profile', true );
	$mn_fb_profile        = get_user_meta( $user_id, 'mn_fb_profile', true );
	$mn_avatar_capability = get_option( 'mn_avatar_capability', 'read' );

	if ( user_can( $user_id, $mn_avatar_capability ) ) {
		if ( 'mn-facebook' == $mn_avatar_profile && ! empty( $mn_fb_profile ) ) {

			$fb     = 'https://graph.facebook.com/' . $mn_fb_profile . '/picture?width='. $size . '&height=' . $size;
			$avatar = "<img alt='facebook-profile-picture' src='{$fb}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";

			return $avatar;
		} else {
			return $avatar;
		}
	} else {
		return $avatar;
	}
}
add_filter( 'get_avatar', 'mn_fb_avatar', 10, 5 );

/**
 * Replaces the default engravatar with the Twitter profile picture
 *
 * @param string $avatar The default avatar
 *
 * @param int $id_or_email The user id
 *
 * @param int $size The size of the avatar
 *
 * @param string $default The url of the Wordpress default avatar
 *
 * @param string $alt Alternate text for the avatar.
 *
 * @return string $avatar The modified avatar
 */
function mn_gplus_avatar( $avatar, $id_or_email, $size, $default, $alt ){
	// Getting the user id.
	if ( is_int( $id_or_email ) )
		$user_id = $id_or_email;

	if ( is_object( $id_or_email ) )
		$user_id = $id_or_email->user_id;

	if ( is_string( $id_or_email ) ) {
		$user = get_user_by( 'email', $id_or_email );
		if ( $user )
			$user_id = $user->ID;
		else
			$user_id = $id_or_email;
	}

	// Getting the user details
	$mn_avatar_profile    = get_user_meta( $user_id, 'mn_avatar_profile', true );
	$mn_gplus_profile     = get_user_meta( $user_id, 'mn_gplus_profile', true );
	$mn_avatar_capability = get_option( 'mn_avatar_capability', 'read' );

	if ( user_can( $user_id, $mn_avatar_capability ) ) {
		if ( 'mn-gplus' == $mn_avatar_profile && ! empty( $mn_gplus_profile ) ) {
			if ( false === ( $gplus = get_transient( "mn_social_avatar_gplus_{$user_id}" ) ) ) {
				$url = 'https://www.googleapis.com/plus/v1/people/' . $mn_gplus_profile . '?fields=image&key=AIzaSyBrLkua-XeZh637G1T1J8DoNHK3Oqw81ao';
				// Fetching the Gplus profile image.
				$results = mn_remote_get( $url, array( 'timeout' => -1 ) );

				// Checking for MN Errors
				if ( ! is_mn_error( $results ) ) {
					if ( 200 == $results['response']['code'] ) {
						$gplusdetails = json_decode( $results['body'] );
						$gplus        = $gplusdetails->image->url;

						// Setting Gplus url for 48 Hours
						set_transient( "mn_social_avatar_gplus_{$user_id}", $gplus, 48 * HOUR_IN_SECONDS );

						// Replacing it with the required size
						$gplus = str_replace( 'sz=50', "sz={$size}", $gplus );

						$avatar = "<img alt='gplus-profile-picture' src='{$gplus}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
					}
				}
			} else {
				// Replacing Gplus url with the required size
				$gplus = str_replace( 'sz=50', "sz={$size}", $gplus );

				$avatar = "<img alt='gplus-profile-picture' src='{$gplus}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
			}
			return $avatar;
		} else {
			return $avatar;
		}
	} else {
		return $avatar;
	}
}
add_filter( 'get_avatar', 'mn_gplus_avatar', 10, 5 );

/**
 * Deletes the transient for a Google Plus for the respective user
 *
 * @param void
 *
 * @return boolean $delete_transient True if the transients gets deleted
 */
function mn_social_avatar_gplus_clear_cache() {
	// Fetch the current user id
	$user_id = sanitize_text_field( $_POST['user_id'] );

	// Delete transient for the particular user
	$delete_transient = delete_transient( "mn_social_avatar_gplus_{$user_id}" );

	echo $delete_transient;
	die();
}
add_action( 'mn_ajax_mn_social_avatar_gplus_clear_cache', 'mn_social_avatar_gplus_clear_cache' );
add_action( 'mn_ajax_nopriv_mn_social_avatar_gplus_clear_cache', 'mn_social_avatar_gplus_clear_cache' );
