<?php
function mtaa_brand_update_db() {

	set_time_limit( 0 );

	// This sets the original db version. Multisite was introduced later, so it starts at 4.
	if ( is_multisite() && is_main_site() ) {
		if ( ! get_site_option( 'mtaa_brand_db' ) ) {
			$current_db_ver = '4';
		} else {
			$current_db_ver = get_site_option( 'mtaa_brand_db' );
		}
	} else {
		if ( ! get_option( 'mtaa_brand_db' ) ) {
			$current_db_ver = '1';
		} else {
			$current_db_ver = get_option( 'mtaa_brand_db' );
		}
	}

	$target_db_ver = MTAA_BRAND_DB;

	while ( $current_db_ver < $target_db_ver ) {

		$current_db_ver ++;

		$function = "mtaa_brand_update_{$current_db_ver}";
		if ( function_exists( $function ) ) {
			call_user_func( $function );
		}

		if ( is_multisite() && is_main_site() ) {
			update_site_option( 'mtaa_brand_db', $current_db_ver );
		} else {
			update_option( 'mtaa_brand_db', $current_db_ver );
		}
	}

}

function mtaa_brand_update_2() {
	global $mtaa_brand_settings;
	if ( is_array( $mtaa_brand_settings ) ) {
		date_default_timezone_set( 'America/Los_Angeles' );
		$date = date( 'Y-m-d H:i:s' );

		$mtaa_brand_settings['licenseDate'] = $date;
		$mtaa_brand_settings['contentHideScreenOptions'] = '';

		if ( is_multisite() && is_main_site() ) {
			update_site_option( 'mtaa_brand_settings', $mtaa_brand_settings );
		} else {
			update_option( 'mtaa_brand_settings', $mtaa_brand_settings );

		}
	}
}

function mtaa_brand_update_3() {
	global $mtaa_brand_settings;
	if ( is_array( $mtaa_brand_settings ) ) {

		$mtaa_brand_settings['loginLinkTitle'] = '';
		$mtaa_brand_settings['loginLinkUrl'] = '';

		if ( isset( $mtaa_brand_settings['adminMenu'] ) ) {
			foreach ( $mtaa_brand_settings['adminMenu'] as $menuOrder => $menuItem ) {
				foreach ( $menuItem as $menuTitle => $menuSlugArray ) {
					foreach ( $menuSlugArray as $menuSlug => $menuHide ) {
						if ( '0' !== $menuHide ) {
							$theMenuItem = base64_encode( serialize( array(
								'Sort'  => esc_attr( $menuOrder ),
								'Title' => esc_attr( $menuTitle ),
								'Slug'  => esc_attr( $menuSlug )
							) ) );
							$theMenu[ $theMenuItem ] = 'on';
						}
					}
				}
			}
			$mtaa_brand_settings['adminMenu'] = '';
			if ( isset( $theMenu ) ) {
				$mtaa_brand_settings['adminMenu'] = $theMenu;
			}
		}

		if ( is_multisite() && is_main_site() ) {
			update_site_option( 'mtaa_brand_settings', $mtaa_brand_settings );
		} else {
			update_option( 'mtaa_brand_settings', $mtaa_brand_settings );
		}
	}
}

function mtaa_brand_update_4() {
	global $mtaa_brand_settings;
	if ( is_array( $mtaa_brand_settings ) ) {

		$mtaa_brand_settings['loginLogoHide'] = '';

		if ( is_multisite() && is_main_site() ) {
			update_site_option( 'mtaa_brand_settings', $mtaa_brand_settings );
		} else {
			update_option( 'mtaa_brand_settings', $mtaa_brand_settings );
		}
	}
}

function mtaa_brand_update_5() {
	global $mtaa_brand_settings;
	if ( is_array( $mtaa_brand_settings ) ) {

		$mtaa_brand_settings['noticeHideAllUpdates'] = '';

		if ( is_multisite() && is_main_site() ) {
			update_site_option( 'mtaa_brand_settings', $mtaa_brand_settings );
		} else {
			update_option( 'mtaa_brand_settings', $mtaa_brand_settings );
		}
	}
}

function mtaa_brand_update_6() {
	global $mtaa_brand_settings;
	if ( is_array( $mtaa_brand_settings ) ) {

		$mtaa_brand_settings['customLogin'] = '';
		$mtaa_brand_settings['customLoginURL'] = '';

		if ( is_multisite() && is_main_site() ) {
			update_site_option( 'mtaa_brand_settings', $mtaa_brand_settings );
		} else {
			update_option( 'mtaa_brand_settings', $mtaa_brand_settings );
		}
	}
}

function mtaa_brand_update_7() {
	global $mtaa_brand_settings;
	if ( is_array( $mtaa_brand_settings ) ) {

		$mtaa_brand_settings['contentHideMNTitle'] = '';

		if ( is_multisite() && is_main_site() ) {
			update_site_option( 'mtaa_brand_settings', $mtaa_brand_settings );
		} else {
			update_option( 'mtaa_brand_settings', $mtaa_brand_settings );
		}
	}
}

function mtaa_brand_update_8() {
	if ( is_multisite() && is_main_site() ) {
		add_site_option( 'mtaa_brand_version', '1.1' );
	} else {
		add_option( 'mtaa_brand_version', '1.1' );
	}
}