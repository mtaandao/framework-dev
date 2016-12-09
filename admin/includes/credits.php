<?php
/**
 * Mtaandao Credits Administration API.
 *
 * @package Mtaandao
 * @subpackage Administration
 * @since 4.4.0
 */

/**
 * Retrieve the contributor credits.
 *
 * @since 3.2.0
 *
 * @return array|false A list of all of the contributors, or false on error.
 */
function mn_credits() {
	$mn_version = get_bloginfo( 'version' );
	$locale = get_user_locale();

	$results = get_site_transient( 'mtaandao_credits_' . $locale );

	if ( ! is_array( $results )
		|| false !== strpos( $mn_version, '-' )
		|| ( isset( $results['data']['version'] ) && strpos( $mn_version, $results['data']['version'] ) !== 0 )
	) {
		$response = mn_remote_get( "http://api.mtaandao.co.ke/core/credits/1.1/?version={$mn_version}&locale={$locale}" );

		if ( is_mn_error( $response ) || 200 != mn_remote_retrieve_response_code( $response ) )
			return false;

		$results = json_decode( mn_remote_retrieve_body( $response ), true );

		if ( ! is_array( $results ) )
			return false;

		set_site_transient( 'mtaandao_credits_' . $locale, $results, DAY_IN_SECONDS );
	}

	return $results;
}

/**
 * Retrieve the link to a contributor's Mtaandao.org profile page.
 *
 * @access private
 * @since 3.2.0
 *
 * @param string $display_name  The contributor's display name, passed by reference.
 * @param string $username      The contributor's username.
 * @param string $profiles      URL to the contributor's Mtaandao.org profile page.
 */
function _mn_credits_add_profile_link( &$display_name, $username, $profiles ) {
	$display_name = '<a href="' . esc_url( sprintf( $profiles, $username ) ) . '">' . esc_html( $display_name ) . '</a>';
}

/**
 * Retrieve the link to an external library used in Mtaandao.
 *
 * @access private
 * @since 3.2.0
 *
 * @param string $data External library data, passed by reference.
 */
function _mn_credits_build_object_link( &$data ) {
	$data = '<a href="' . esc_url( $data[1] ) . '">' . esc_html( $data[0] ) . '</a>';
}
