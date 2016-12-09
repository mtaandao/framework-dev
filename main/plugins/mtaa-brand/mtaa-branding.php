<?php
/*
Plugin Name: Mtaa Branding
Description: Mtaa Branding is a powerful Mtaandao admin theme plugin that reimagines Mtaandao with a clean and simplified design. White label your Mtaandao install with custom colors, a custom login screen, custom admin branding, and more.
Version: 1.1.1
*/

if ( ! defined( 'MTAA_BRAND_VERSION' ) ) {
	define( 'MTAA_BRAND_VERSION', '1.1.1' );
}
if ( ! defined( 'MTAA_BRAND_DB' ) ) {
	define( 'MTAA_BRAND_DB', '8' );
}

// Import
if ( is_admin() && isset( $GLOBALS['_GET']['page'] ) && 'mtaa_brand_import_export' == $GLOBALS['_GET']['page'] ) {

	if ( isset( $_POST['mtaa_brand_import'] ) ) {

		global $mtaa_brand_import_success;

		$import = esc_sql( @unserialize( stripslashes( $_POST['mtaa_brand_import_settings'] ) ) );

		if ( false !== $import && is_array( $import ) ) {
			if ( is_multisite() && is_main_site() ) {
				update_site_option( 'mtaa_brand_settings', $import );
			} else {
				update_option( 'mtaa_brand_settings', $import );
			}
			$mtaa_brand_import_success = true;
		} else {
			$mtaa_brand_import_success = false;
		}
	}
}

// Global Settings
if ( is_admin() || mtaa_brand_is_login_page() ) {
	$mtaa_brand_settings = mtaa_brand_get_settings();
}
function mtaa_brand_get_settings() {
	if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
		require_once( ABSPATH . '/admin/includes/plugin.php' );
	}

	if ( is_multisite() && is_plugin_active_for_network( 'mtaa-branding/mtaa-branding.php' ) ) {
		return $mtaa_brand_settings = get_site_option( 'mtaa_brand_settings' );
	} else {
		return $mtaa_brand_settings = get_option( 'mtaa_brand_settings' );
	}
}
function mtaa_brand_is_login_page() {
	if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
		require_once( ABSPATH . '/admin/includes/plugin.php' );
	}

	if ( is_multisite() && is_plugin_active_for_network( 'mtaa-branding/mtaa-branding.php' ) ) {

		$mtaa_brand_settings = get_site_option( 'mtaa_brand_settings' );

		if ( 'on' === $mtaa_brand_settings['customLogin'] ) {
			if ( preg_match( $mtaa_brand_settings['customLoginURL'] . '/', $GLOBALS['path'] ) ) {
				return true;
			}
		} else {
			if ( preg_match( '/login.php/', $GLOBALS['path'] ) || preg_match( '/mn-register.php/', $GLOBALS['path'] ) ) {
				return true;
			}
		}
	} else {

		$mtaa_brand_settings = get_option( 'mtaa_brand_settings' );

		if ( 'on' === $mtaa_brand_settings['customLogin'] ) {
			if ( $mtaa_brand_settings['customLoginURL'] === $_SERVER['REQUEST_URI'] || $mtaa_brand_settings['customLoginURL'] . '?loggedout=true' === $_SERVER['REQUEST_URI'] || $mtaa_brand_settings['customLoginURL'] . '/?loggedout=true' === $_SERVER['REQUEST_URI'] ) {
				return true;
			}
		} else {
			return in_array( $GLOBALS['pagenow'], array( 'login.php', 'mn-register.php' ) );
		}
	}
}

// Setup the Settings Menu and Page
if ( is_admin() ) {
	if ( is_multisite() && is_plugin_active_for_network( 'mtaa-branding/mtaa-branding.php' ) ) {
		add_action( 'network_admin_menu', 'mtaa_brand_plugin_menu' );
	} else {
		add_action( 'admin_menu', 'mtaa_brand_plugin_menu' );
	}
}

function mtaa_brand_plugin_menu() {
	add_menu_page(
		'Mtaa Branding Settings',
		'Branding',
		'manage_options',
		'mtaa_brand_color_schemes',
		'mtaa_brand_color_schemes',
		'dashicons-admin-appearance',
		'98.2481'
	);
	add_submenu_page(
		'mtaa_brand_color_schemes',
		'Color Schemes',
		'Color Schemes',
		'manage_options',
		'mtaa_brand_color_schemes',
		'mtaa_brand_color_schemes'
	);
	add_submenu_page(
		'mtaa_brand_color_schemes',
		'Custom Branding',
		'Custom Branding',
		'manage_options',
		'mtaa_brand_branding',
		'mtaa_brand_branding'
	);
	add_submenu_page(
		'mtaa_brand_color_schemes',
		'Dashboard',
		'Dashboard',
		'manage_options',
		'mtaa_brand_dashboard',
		'mtaa_brand_dashboard'
	);
	if ( is_multisite() && is_main_site() ) {

	} else {
		add_submenu_page(
			'mtaa_brand_color_schemes',
			'Admin Menu',
			'Admin Menu',
			'manage_options',
			'mtaa_brand_admin_menu',
			'mtaa_brand_admin_menu'
		);
	}
	add_submenu_page(
		'mtaa_brand_color_schemes',
		'Admin Bar &amp; Footer',
		'Admin Bar &amp; Footer',
		'manage_options',
		'mtaa_brand_admin_bar_footer',
		'mtaa_brand_admin_bar_footer'
	);

	add_submenu_page(
		'mtaa_brand_color_schemes',
		'Content &amp; Notices',
		'Content &amp; Notices',
		'manage_options',
		'mtaa_brand_content_notices',
		'mtaa_brand_content_notices'
	);
	add_submenu_page(
		'mtaa_brand_color_schemes',
		'Permissions',
		'Permissions',
		'manage_options',
		'mtaa_brand_permissions',
		'mtaa_brand_permissions'
	);
	add_submenu_page(
		'mtaa_brand_color_schemes',
		'Settings',
		'Settings',
		'manage_options',
		'mtaa_brand_settings',
		'mtaa_brand_settings'
	);
}

// admin_init
add_action( 'admin_init', 'mtaa_brand_admin_init' );
function mtaa_brand_admin_init() {

	if ( is_multisite() && is_plugin_active_for_network( 'mtaa-branding/mtaa-branding.php' ) ) {
		add_action( 'network_admin_edit_mtaa_brand_network', 'mtaa_brand_save_settings_network', 10, 0 );
	} else {
		register_setting(
			'mtaa_brand_settings',
			'mtaa_brand_settings',
			'mtaa_brand_sanitize'
		);
	}

}

// Add Settings Link on Plugin Page
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'mtaa_brand_plugin_link' );
function mtaa_brand_plugin_link( $links ) {
	$settings_link = '<a href="admin.php?page=mtaa_brand_color_schemes">Settings</a>';
	array_push( $links, $settings_link );
	return $links;
}

// DB Updates
function mtaa_brand_check_db() {
	if ( is_multisite() && is_main_site() ) {
		if ( get_site_option( 'mtaa_brand_db' ) >= MTAA_BRAND_DB ) {
			return;
		}
	} else {
		if ( get_option( 'mtaa_brand_db' ) >= MTAA_BRAND_DB ) {
			return;
		}
	}

	require_once( __DIR__ . '/inc/update_db.php' );
	mtaa_brand_update_db();
}

// Version Check
function mtaa_brand_check_version() {
	if ( is_multisite() && is_main_site() ) {
		if ( get_site_option( 'mtaa_brand_version' ) >= MTAA_BRAND_VERSION ) {
			return;
		} else {
			update_site_option( 'mtaa_brand_version', MTAA_BRAND_VERSION );
		}
	} else {
		if ( get_option( 'mtaa_brand_version' ) >= MTAA_BRAND_VERSION ) {
			return;
		} else {
			update_option( 'mtaa_brand_version', MTAA_BRAND_VERSION );
		}
	}

	// Update info on License server
	mtaa_brand_initial_license();
}


// plugins_loaded
add_action( 'plugins_loaded', 'mtaa_brand_plugins_loaded' );
function mtaa_brand_plugins_loaded() {
	global $mtaa_brand_settings;

	// Translations
	load_plugin_textdomain( 'mtaa-brand', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	// Update DB
	mtaa_brand_check_db();

	// Update Version
	mtaa_brand_check_version();

	// Mtaa Branding Plugin Permissions
	$plugin_permission = mtaa_brand_get_user_permission();
	if ( ! empty( $plugin_permission ) ) {
		if ( ! empty( $mtaa_brand_settings['userPermissions'][ $plugin_permission ] ) && ( 'on' === $mtaa_brand_settings['userPermissions'][ $plugin_permission ] ) ) {
			add_action( 'admin_menu', 'mtaa_brand_hide_plugin_menu' );
			add_action( 'admin_head', 'mtaa_brand_hide_plugin' );
		}
	}
}

// admin_enqueue_scripts
add_action( 'admin_enqueue_scripts', 'mtaa_brand_admin_enqueue' );
function mtaa_brand_admin_enqueue( $page ) {
	global $mtaa_brand_settings;

	mn_enqueue_style( 'mtaa-branding', plugins_url( 'css/mtaa_brand.css', __FILE__ ) );
	mn_enqueue_script( 'mtaa-brand', plugins_url( 'js/mtaa_brand.js', __FILE__ ), array( 'jquery' ), MTAA_BRAND_VERSION );

	// Branding Page
	if ( 'mtaa-brand_page_mtaa_brand_branding' === $page ) {
		mn_enqueue_media();
	}

	// Color Schemes Page
	if ( 'toplevel_page_mtaa_brand_color_schemes' === $page ) {
		mn_enqueue_style( 'spectrum-css', plugins_url( 'css/spectrum.css', __FILE__ ) );
		mn_enqueue_script( 'spectrum-js', plugins_url( 'js/spectrum.js', __FILE__ ), array( 'jquery' ), MTAA_BRAND_VERSION );
	}

	// Admin Logo Present
	if ( $adminLogo = $mtaa_brand_settings['adminLogo'] ) {
		mn_localize_script( 'mtaa-brand', 'slate_adminLogo', esc_url( $mtaa_brand_settings['adminLogo'] ) );
	}

	// Hide User Profile Colors
	if ( 'on' === $mtaa_brand_settings['colorsHideUserProfileColors'] ) {
		mn_localize_script( 'mtaa-brand', 'slate_colorsHideUserProfileColors', esc_attr( $mtaa_brand_settings['colorsHideUserProfileColors'] ) );
	}

}

// login_enqueue_scripts
add_action( 'login_enqueue_scripts', 'mtaa_brand_login_enqueue' );
function mtaa_brand_login_enqueue() {
	mn_enqueue_style( 'mtaa-branding', plugins_url( 'css/mtaa_brand.css', __FILE__ ) );
}

// mn_head
// Add Admin Bar styles to front end
add_action( 'mn_head', 'mtaa_brand_mn_head' );
function mtaa_brand_mn_head() {
	if ( is_admin_bar_showing() ) {
		$mtaa_brand_settings = mtaa_brand_get_settings();

		// Color Schemes and Options
		include( __DIR__ . '/css/dynamic_css_adminbar.php' );

		// Hide Admin Bar
		if ( 'on' === $mtaa_brand_settings['adminBarHide'] ) { ?>
			<style type="text/css" media="screen">
				/* Admin Bar */
				#mnadminbar {
					display: none;
				}

				#mnbody,
				.folded #mnbody {
					padding-top: 0;
				}

				@media only screen and (max-width: 782px) {
					#mnadminbar {
						display: block;
						visibility: hidden;
					}

					#admin-bar-menu-toggle {
						visibility: visible;
					}

					#mnadminbar #adminbarsearch:before, #mnadminbar .ab-icon:before, #mnadminbar .ab-item:before, #mnadminbar a.ab-item, #mnadminbar > #mn-toolbar span.ab-label, #mnadminbar > #mn-toolbar span.noticon {
						color: #333;
					}

					.mn-responsive-open #mnadminbar #admin-bar-menu-toggle a {
						background: #fff;
					}

					#mnbody,
					.folded #mnbody {
						padding-top: 46px;
					}
				}
			</style>
		<?php }

		// Hide the MN Logo from the Admin Bar
		if ( 'on' === $mtaa_brand_settings['adminBarHideMN'] ) { ?>
			<style type="text/css" media="screen">
				/* Admin Bar Mtaandao Logo */
				#mnadminbar li#admin-bar-mn-logo {
					display: none;
				}
			</style>
		<?php }
	}
}
// admin_head
add_action( 'admin_head', 'mtaa_brand_admin_head' );
function mtaa_brand_admin_head() {
	global $mtaa_brand_settings;

	// Color Schemes and Options
	include( __DIR__ . '/css/dynamic_css.php' );
	include( __DIR__ . '/css/dynamic_css_adminbar.php' );

	// Favicon
	if ( $adminFavicon = $mtaa_brand_settings['adminFavicon'] ) {
		echo '<link rel="shortcut icon" href="' . esc_url( $adminFavicon ) . '">';
	}

	// Admin Menu
	if ( '' !== $mtaa_brand_settings['adminLogo'] ) { ?>
		<style type="text/css" media="screen">
			/* Admin Bar Mtaandao Logo */
			#adminmenu {
				margin: 0 !important;
			}
		</style>
	<?php }
	if ( '' !== $mtaa_brand_settings['adminLogoFolded'] ) { ?>
		<style type="text/css" media="screen">
			/* Admin Bar Mtaandao Logo */
			#adminmenu .folded {
				margin: 0 0 12px 0 !important;
			}
		</style>
	<?php }

	// Hide User Profile Colors
	if ( 'on' === $mtaa_brand_settings['colorsHideUserProfileColors'] ) { ?>
		<style type="text/css" media="screen">
			/* User Profile Color Options */
			.profile-php #color-picker {
				display: none;
			}
		</style>
	<?php }

	// Hide Admin Bar
	if ( 'on' === $mtaa_brand_settings['adminBarHide'] ) { ?>
		<style type="text/css" media="screen">
			/* Admin Bar */
			#mnadminbar {
				display: none;
			}

			#mnbody,
			.folded #mnbody {
				padding-top: 0;
			}

			@media only screen and (max-width: 782px) {
				#mnadminbar {
					display: block;
					visibility: hidden;
				}

				#admin-bar-menu-toggle {
					visibility: visible;
				}

				#mnadminbar #adminbarsearch:before, #mnadminbar .ab-icon:before, #mnadminbar .ab-item:before, #mnadminbar a.ab-item, #mnadminbar > #mn-toolbar span.ab-label, #mnadminbar > #mn-toolbar span.noticon {
					color: #333;
				}

				.mn-responsive-open #mnadminbar #admin-bar-menu-toggle a {
					background: #fff;
				}

				#mnbody,
				.folded #mnbody {
					padding-top: 46px;
				}
			}
		</style>
	<?php }

	// Hide the MN Logo from the Admin Bar
	if ( 'on' === $mtaa_brand_settings['adminBarHideMN'] ) { ?>
		<style type="text/css" media="screen">
			/* Admin Bar Mtaandao Logo */
			#mnadminbar li#admin-bar-mn-logo {
				display: none;
			}
		</style>
	<?php }

	// Hide Footer
	if ( 'on' === $mtaa_brand_settings['footerHide'] ) { ?>
		<style type="text/css" media="screen">
			/* Footer */
			#mnfooter {
				display: none;
			}
		</style>
	<?php }

	// Hide Help Tab
	if ( 'on' === $mtaa_brand_settings['contentHideHelp'] ) { ?>
		<style type="text/css" media="screen">
			/* Help Tab */
			#contextual-help-link-wrap {
				display: none;
			}
		</style>
	<?php }

	// Hide Screen Options Tab
	if ( 'on' === $mtaa_brand_settings['contentHideScreenOptions'] ) { ?>
		<style type="text/css" media="screen">
			/* Screen Options Tab */
			#screen-options-link-wrap {
				display: none;
			}
		</style>
	<?php }

	// Hide Updates
	if ( 'on' === $mtaa_brand_settings['noticeHideAllUpdates'] ) { ?>
		<style type="text/css" media="screen">
			#admin-bar-updates,
			.theme-update,
			.update-message,
			.update-nag,
			.update-plugins,
			#menu-update {
				display: none !important;
			}
		</style>
	<?php }
}

// admin_title
add_filter( 'admin_title', 'mtaa_brand_admin_title', 10, 2 );
function mtaa_brand_admin_title( $admin_title, $title ) {
	global $mtaa_brand_settings;

	if ( 'on' === $mtaa_brand_settings['contentHideMNTitle'] ) {
		return $title . ' &lsaquo; ' . get_bloginfo( 'name' );
	} else {
		return $admin_title;
	}
}

// login_head
add_action( 'login_head', 'mtaa_brand_login_head' );
function mtaa_brand_login_head() {
	global $mtaa_brand_settings;

	// Color Schemes and Options
	include( __DIR__ . '/css/dynamic_css.php' );

	// Favicon
	if ( '' !== $mtaa_brand_settings['adminFavicon'] ) {
		echo '<link rel="shortcut icon" href="' . esc_url( $mtaa_brand_settings['adminFavicon'] ) . '">';
	}

	// Login Logo
	if ( '' !== $mtaa_brand_settings['loginLogo'] ) { ?>
		<style type="text/css" media="screen">
			/* Login Logo */
			body.login div#login h1 a {
				background-image: url('<?php echo esc_url( $mtaa_brand_settings['loginLogo'] ); ?>');
				background-size: contain;
				width: 100%;
			}
		</style>
	<?php }

	// Hide Login Logo
	if ( '' !== $mtaa_brand_settings['loginLogoHide'] ) { ?>
		<style type="text/css" media="screen">
			/* Login Logo */
			body.login div#login h1 {
				display: none;
			}
		</style>
	<?php }

	// Login Background
	if ( '' !== $mtaa_brand_settings['loginBgImage'] ) { ?>
		<style type="text/css" media="screen">
			/* Login Background Image */
			body.login {
				background-image: url('<?php echo esc_url( $mtaa_brand_settings['loginBgImage'] ); ?>');
				background-position: <?php echo esc_attr( $mtaa_brand_settings['loginBgPosition'] ); ?>;
				background-repeat: <?php echo esc_attr( $mtaa_brand_settings['loginBgRepeat'] ); ?>;
				width: 100%;
			<?php if ( 'on' === $mtaa_brand_settings['loginBgFull' ] ) { ?> background-attachment: fixed;
				background-size: cover;
			<?php } ?>
			}
		</style>
	<?php }
}

// Login Link Text and Address
add_filter( 'login_headerurl', 'mtaa_brand_login_url' );
function mtaa_brand_login_url() {
	global $mtaa_brand_settings;

	$loginUrl = $mtaa_brand_settings['loginLinkUrl'];

	return $loginUrl;
}
add_filter( 'login_headertitle', 'mtaa_brand_login_title' );
function mtaa_brand_login_title() {
	global $mtaa_brand_settings;

	$loginTitle = $mtaa_brand_settings['loginLinkTitle'];

	return $loginTitle;
}

// Get Current User Admin Color
function mtaa_brand_get_user_admin_color() {
	$user_id = get_current_user_id();
	$user_info = get_userdata( $user_id );
	if ( ! ( $user_info instanceof MN_User ) ) {
		return;
	}
	$user_admin_color = $user_info->admin_color;

	return $user_admin_color;
}

add_action( 'admin_menu', 'mtaa_brand_footer_hide_ver' );
function mtaa_brand_footer_hide_ver() {
	global $mtaa_brand_settings;

	if ( 'on' === $mtaa_brand_settings['footerVersionHide'] ) {
		remove_filter( 'update_footer', 'core_update_footer' );
	}
}

// Admin Menu
function mtaa_brand_admin_menus() {
	global $menu;

	$i = 1;
	foreach ( $menu as $menuOrder => $menuItem ) {
		if ( 'Admin Logo' !== $menuItem[0] && 'Admin Logo Folded' !== $menuItem[0] ) {
			if ( ! empty( $menuItem[0] ) ) {
				$getJustName = explode( ' ', $menuItem[0] );
				if ( ( 'Plugins' == $getJustName[0] ) || ( 'Comments' == $getJustName[0] ) || ( 'Themes' === $getJustName[0] ) || ( 'Updates' === $getJustName[0] ) ) {
					$menuTitle = $getJustName[0];
				} else {
					$menuTitle = $menuItem[0];
				}
			} else {
				$menuTitle = 'Menu Separator ' . $i;
				$i ++;
			}
			$theMenu[] = array(
				'Sort'  => $menuOrder,
				'Title' => $menuTitle,
				'Slug'  => $menuItem[2],
				'Hide'  => '0',
			);
		}
	}

	return $theMenu;
}

function mtaa_brand_hide_admin_menus() {
	global $mtaa_brand_settings;
	if ( ! isset( $mtaa_brand_settings['adminMenu'] ) ) {
		return;
	} else {
		foreach ( $mtaa_brand_settings['adminMenu'] as $menuItem => $menuHide ) {

			$menuItem = unserialize( base64_decode( $menuItem ) );

			if ( 'on' === $menuHide ) {
				remove_menu_page( $menuItem['Slug'] );
			}
		}
	}
}

// User Permissions
function mtaa_brand_get_user_permission() {
	$user_id = get_current_user_id();
	$user_info = get_userdata( $user_id );
	if ( ! ( $user_info instanceof MN_User ) ) {
		return;
	}
	$username = $user_info->user_login;

	return $username;
}

function mtaa_brand_hide_plugin_menu() {
	remove_menu_page( 'mtaa_brand_color_schemes' );
}

function mtaa_brand_hide_plugin() { ?>
	<style type="text/css" media="screen">
		/* Admin Bar */
		#mtaa-branding {
			display: none;
		}
	</style>
<?php }

// Dashboard
// Display Custom Widget
add_action( 'mn_dashboard_setup', 'mtaa_brand_dashboard_setup' );
function mtaa_brand_dashboard_setup() {
	global $mn_meta_boxes;
	global $mtaa_brand_settings;

	// Dashboard Welcome Message
	if ( 'on' === $mtaa_brand_settings['dashboardHideWelcome'] ) {
		remove_action( 'welcome_panel', 'mn_welcome_panel' );
	}

	if ( 'on' === $mtaa_brand_settings['dashboardCustomWidget'] ) {
		$widgetTitle = $mtaa_brand_settings['dashboardCustomWidgetTitle'];
		mn_add_dashboard_widget( 'mtaa_brand_dashboard_widget', esc_attr( $widgetTitle ), 'mtaa_brand_dashboard_widget_display' );

		// Move custom widget to top
		$normal_dashboard = $mn_meta_boxes['dashboard']['normal']['core'];
		$example_widget_backup = array( 'mtaa_brand_dashboard_widget' => $normal_dashboard['mtaa_brand_dashboard_widget'] );
		unset( $normal_dashboard['mtaa_brand_dashboard_widget'] );
		$sorted_dashboard = array_merge( $example_widget_backup, $normal_dashboard );
		$mn_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
	}
}
function mtaa_brand_dashboard_widget_display() {
	global $mtaa_brand_settings;
	$widgetText = $mtaa_brand_settings['dashboardCustomWidgetText'];
	echo '<div class="slate__customWidget">' . mn_kses_post( force_balance_tags( $widgetText ) ) . '</div>';
}

// Disabled Dashboard Widgets
add_action( 'admin_init', 'mtaa_brand_disabled_widgets' );
function mtaa_brand_disabled_widgets() {
	global $mtaa_brand_settings;

	if ( isset( $mtaa_brand_settings['dashboardWidgets'] ) ) {
		foreach ( $mtaa_brand_settings['dashboardWidgets'] as $key => $value ) {
			if ( 'on' === $value ) {
				add_action( 'mn_dashboard_setup', 'mtaa_brand_' . $key );
			}
		}
	}
}

function mtaa_brand_dashboardHideActivity() {
	global $mn_meta_boxes;
	unset( $mn_meta_boxes['dashboard']['normal']['core']['dashboard_activity'] );
}

function mtaa_brand_dashboardHideNews() {
	global $mn_meta_boxes;
	unset( $mn_meta_boxes['dashboard']['side']['core']['dashboard_primary'] );
	unset( $mn_meta_boxes['dashboard']['side']['core']['dashboard_secondary'] );
}

function mtaa_brand_dashboardRightNow() {
	global $mn_meta_boxes;
	unset( $mn_meta_boxes['dashboard']['normal']['core']['dashboard_right_now'] );
}

function mtaa_brand_dashboardRecentComments() {
	global $mn_meta_boxes;
	unset( $mn_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments'] );
}

function mtaa_brand_dashboardQuickPress() {
	global $mn_meta_boxes;
	unset( $mn_meta_boxes['dashboard']['side']['core']['dashboard_quick_press'] );
}

function mtaa_brand_dashboardRecentDrafts() {
	global $mn_meta_boxes;
	unset( $mn_meta_boxes['dashboard']['side']['core']['dashboard_recent_drafts'] );
}

function mtaa_brand_dashboardIncomingLinks() {
	global $mn_meta_boxes;
	unset( $mn_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links'] );
}

function mtaa_brand_dashboardPlugins() {
	global $mn_meta_boxes;
	unset( $mn_meta_boxes['dashboard']['normal']['core']['dashboard_plugins'] );
}

// Content
// Remove the hyphen before the post state
add_filter( 'display_post_states', 'mtaa_brand_post_state' );
function mtaa_brand_post_state( $post_states ) {
	if ( ! empty( $post_states ) ) {
		$state_count = count( $post_states );
		$i = 0;
		foreach ( $post_states as $state ) {
			++ $i;
			( $i === $state_count ) ? $sep = '' : $sep = '';
			echo '<span class="post-state">' . esc_attr( $state ) . esc_attr( $sep ) . '</span>';
		}
	}
}

// Notices
add_action( 'after_setup_theme', 'mtaa_brand_mn_notices' );
function mtaa_brand_mn_notices() {
	global $mtaa_brand_settings;

	$mtaa_brand_remove_notices = function ( $a ) {
		global $mn_version;
		return (object) array(
			'last_checked' => time(),
			'version_checked' => $mn_version,
			);
	};

	// Disable Core Updates
	if ( 'on' === $mtaa_brand_settings['noticeMNUpdate'] ) {
		add_filter( 'pre_site_transient_update_core', $mtaa_brand_remove_notices );
	}

	// Disable Theme Updates
	if ( 'on' === $mtaa_brand_settings['noticeThemeUpdate'] ) {
		add_filter( 'pre_site_transient_update_themes', $mtaa_brand_remove_notices );
	}

	// Disable Plugin Updates
	if ( 'on' === $mtaa_brand_settings['noticePluginUpdate'] ) {
		add_filter( 'pre_site_transient_update_plugins', $mtaa_brand_remove_notices );
	}
}
// Hide All Updates (Alternative to Disabling)
add_action( 'admin_menu', 'mtaa_brand_hide_all_updates' );
function mtaa_brand_hide_all_updates() {
	global $mtaa_brand_settings;
	global $menu;
	global $submenu;

	if ( 'on' === $mtaa_brand_settings['noticeHideAllUpdates'] ) {
		if ( is_multisite() && is_main_site() ) {
			remove_action( 'network_admin_notices', 'update_nag', 3 );
			remove_filter( 'update_footer', 'core_update_footer' );
			$menu[65][0] = 'Plugins';
			$submenu['index.php'][10][0] = 'Updates';
		} else {
			remove_action( 'admin_notices', 'update_nag', 3 );
			remove_filter( 'update_footer', 'core_update_footer' );
			$menu[65][0] = 'Plugins';
			$submenu['index.php'][10][0] = 'Updates';
		}
	}
}

// After Setup
add_action( 'after_setup_theme', 'mtaa_brand_add_editor_styles' );
function mtaa_brand_add_editor_styles() {
	add_editor_style( plugins_url( 'css/editor-style.css', __FILE__ ) );
}

// Activate Plugin
register_activation_hook( __FILE__, 'mtaa_brand_activate' );
register_activation_hook( __FILE__, 'mtaa_brand_initial_license' );
register_activation_hook( __FILE__, 'mtaa_brand_check_db' );
register_activation_hook( __FILE__, 'mtaa_brand_check_version' );
function mtaa_brand_activate() {

	date_default_timezone_set( 'America/Los_Angeles' );
	$date = date( 'Y-m-d H:i:s' );

	if ( is_multisite() ) {
		global $blog_id;
		$current_blog_details = get_blog_details( array( 'blog_id' => $blog_id ) );
		$loginLinkTitle = $current_blog_details->blogname;
		$loginLinkUrl = $current_blog_details->siteurl;
	} else {
		$loginLinkTitle = get_bloginfo( 'name' );
		$loginLinkUrl = get_bloginfo( 'url' );
	}

	$default_option = array(
		// Color Schemes
		'colorScheme'                 => 'default',
		'colorSchemeCustomColors'     => array(
			'loginBgColor'                                => '#444343',
			'loginFormBgColor'                            => '#eeecec',
			'loginFormTextColor'                          => '#777777',
			'loginFormInputBgColor'                       => '#fbfbfb',
			'loginFormInputTextColor'                     => '#333333',
			'loginFormInputFocusColor'                    => '#5b9dd9',
			'loginButtonBgColor'                          => '#2ea2cc',
			'loginButtonTextColor'                        => '#ffffff',
			'loginButtonHoverBgColor'                     => '#ffffff',
			'loginButtonHoverTextColor'                   => '#2ea2cc',
			'loginFormLinkColor'                          => '#eeebeb',
			'loginFormLinkHoverColor'                     => '#ffffff',
			'adminMenuBgColor'                            => '#302d2d',
			'adminMenuDividerColor'                       => '#262323',
			'adminNoticeColor'                            => '#ffffff',
			'adminNoticeBgColor'                          => '#d54e21',
			'adminTopLevelTextColor'                      => '#888888',
			'adminTopLevelTextHoverColor'                 => '#2ea2cc',
			'adminTopLevelSelectedTextColor'              => '#ea5340',
			'adminFloatingSubmenuBgColor'                 => '#2ea2cc',
			'adminFloatingSubmenuTextColor'               => '#ffffff',
			'adminFloatingSubmenuTextHoverColor'          => '#b9ecff',
			'adminOpenSubmenuTextColor'                   => '#bbbbbb',
			'adminOpenSubmenuTextHoverColor'              => '#ffffff',
			'adminOpenSubmenuTextSelectedColor'           => '#ffffff',
			'adminTopLevelSelectedFoldedBg'               => '#ea5340',
			'adminTopLevelFoldedTextColor'                => '#ffffff',
			'adminTopLevelSelectedFoldedTextColor'        => '#ffffff',
			'adminTopLevelSelectedFoldedIconColor'        => '#ffffff',
			'adminFoldedFloatingSubmenuTextColor'         => '#ffffff',
			'adminFoldedFloatingSubmenuTextHoverColor'    => '#ffd8d3',
			'adminFoldedFloatingSubmenuSelectedTextColor' => '#ffffff',
			'adminBarBgColor'                             => '#444343',
			'adminBarBgHoverColor'                        => '#333333',
			'adminBarTopLevelColor'                       => '#888888',
			'adminBarTopLevelHoverColor'                  => '#2ea2cc',
			'adminBarSubmenuTextColor'                    => '#eeeeee',
			'adminBarSubmenuTextHoverColor'               => '#2ea2cc',
			'footerBgColor'                               => '#444343',
			'footerTextColor'                             => '#999999',
			'footerLinkColor'                             => '#ffffff',
			'footerLinkHoverColor'                        => '#ffffff',
			'contentTextColor'                            => '#555555',
			'contentHeadingTextColor'                     => '#222222',
			'contentLinkColor'                            => '#0074a2',
			'contentLinkHoverColor'                       => '#2ea2cc',
			'contentTableRowBgHoverColor'                 => '#eeecec',
			'contentDividerColor'                         => '#eeecec',
			'contentPrimaryButtonBgColor'                 => '#2ea2cc',
			'contentPrimaryButtonTextColor'               => '#ffffff',
			'contentPrimaryButtonBgHoverColor'            => '#1e8cbe',
			'contentPrimaryButtonTextHoverColor'          => '#ffffff',
			'contentStandardButtonBgColor'                => '#dcd7d7',
			'contentStandardButtonTextColor'              => '#555555',
			'contentStandardButtonBgHoverColor'           => '#7d7878',
			'contentStandardButtonTextHoverColor'         => '#ffffff',
			'contentMetaBgColor'                          => '#eeecec',
			'contentMetaTextColor'                        => '#777777',
			'contentMetaBgHoverColor'                     => '#eeecec',
			'contentMetaTextHoverColor'                   => '#333333',
			'sidebarBgColor'                              => '#eeecec',
			'sidebarTextColor'                            => '#555555',
			'sidebarHeadingColor'                         => '#222222',
			'sidebarLinkColor'                            => '#0074a2',
			'sidebarLinkHoverColor'                       => '#2ea2cc',
			'sidebarIconColor'                            => '#555555',
			'sidebarDividerColor'                         => '#dad8d8',
			'sidebarPrimaryButtonBgColor'                 => '#2ea2cc',
			'sidebarPrimaryButtonTextColor'               => '#ffffff',
			'sidebarPrimaryButtonBgHoverColor'            => '#1e8cbe',
			'sidebarPrimaryButtonTextHoverColor'          => '#ffffff',
			'sidebarStandardButtonBgColor'                => '#dcd7d7',
			'sidebarStandardButtonTextColor'              => '#555555',
			'sidebarStandardButtonBgHoverColor'           => '#7d7878',
			'sidebarStandardButtonTextHoverColor'         => '#ffffff',
		),
		'colorsHideUserProfileColors' => '',
		'colorsHideShadows'           => '',
		// Login Page
		'loginLinkTitle'              => $loginLinkTitle,
		'loginLinkUrl'                => $loginLinkUrl,
		'loginLogoHide'               => '',
		'loginLogo'                   => plugins_url( '/images/mtaa_brand_login_logo.png', __FILE__ ),
		'loginBgImage'                => plugins_url( '/images/mtaa_brand_background.jpg', __FILE__ ),
		'loginBgPosition'             => 'center top',
		'loginBgRepeat'               => 'no-repeat',
		'loginBgFull'                 => 'on',
		// Admin Branding
		'adminLogo'                   => plugins_url( '/images/mtaa_brand_admin_logo.png', __FILE__ ),
		'adminLogoFolded'             => plugins_url( '/images/mtaa_brand_admin_logo_folded.png', __FILE__ ),
		'adminFavicon'                => plugins_url( '/images/mtaa_brand_favicon.png', __FILE__ ),
		// Admin Menu
		'adminMenu'                   => array(),
		'adminMenuPermissions'        => array(),
		// Admin Bar
		'adminBarHide'                => '',
		'adminBarHideMN'              => '',
		// Footer
		'footerTextShow'              => '',
		'footerVersionHide'           => '',
		'footerText'                  => '',
		'footerHide'                  => '',
		// Dashboard
		'dashboardHideWelcome'        => '',
		'dashboardWidgets'            => array(
			'dashboardHideActivity'   => '0',
			'dashboardHideNews'       => '0',
			'dashboardRightNow'       => '0',
			'dashboardRecentComments' => '0',
			'dashboardQuickPress'     => '0',
			'dashboardRecentDrafts'   => '0',
			'dashboardIncomingLinks'  => '0',
			'dashboardPlugins'        => '0',
		),
		'dashboardCustomWidget'       => '',
		'dashboardCustomWidgetTitle'  => '',
		'dashboardCustomWidgetText'   => '',
		// Content and Notices
		'noticeMNUpdate'              => '',
		'noticeThemeUpdate'           => '',
		'noticePluginUpdate'          => '',
		'noticeHideAllUpdates'        => '',
		'contentHideHelp'             => '',
		'contentHideScreenOptions'    => '',
		'contentHideMNTitle'          => '',
		// Permissions
		'userPermissions'             => array(),
		// Settings
		'customLogin'                 => '',
		'customLoginURL'              => '',
		// License
		'licenseDate'                 => $date,
	);

	if ( is_multisite() && is_main_site() ) {
		add_site_option( 'mtaa_brand_settings', $default_option );
	} else {
		add_option( 'mtaa_brand_settings', $default_option );
	}

	$license_options = array(
		'licenseKey'    => '',
		'licenseStatus' => '',
	);
	if ( is_multisite() && is_main_site() ) {
		add_site_option( 'mtaa_brand_license', $license_options );
	} else {
		add_option( 'mtaa_brand_license', $license_options );
	}

	if ( is_multisite() && is_main_site() ) {
		add_site_option( 'mtaa_brand_db', MTAA_BRAND_DB );
	} else {
		add_option( 'mtaa_brand_db', MTAA_BRAND_DB );
	}

	if ( is_multisite() && is_main_site() ) {
		add_site_option( 'mtaa_brand_version', MTAA_BRAND_VERSION );
	} else {
		add_option( 'mtaa_brand_version', MTAA_BRAND_VERSION );
	}
}

// Deactivate Plugin
register_deactivation_hook( __FILE__, 'mtaa_brand_deactivate' );
function mtaa_brand_deactivate() {

	if ( is_multisite() && is_main_site() ) {
		delete_site_option( 'mtaa_brand_settings' );
		delete_site_option( 'mtaa_brand_license' );
		delete_site_option( 'mtaa_brand_version' );
		delete_site_option( 'mtaa_brand_db' );
	} else {
		delete_option( 'mtaa_brand_settings' );
		delete_option( 'mtaa_brand_license' );
		delete_option( 'mtaa_brand_version' );
		delete_option( 'mtaa_brand_db' );
	}
}

// Sanitization
function mtaa_brand_save_settings_network() {
	$option = mtaa_brand_sanitize( $_POST['mtaa_brand_settings'] );

	if ( ! empty( $option ) ) {
		update_site_option( 'mtaa_brand_settings', $option );
	}

	mn_redirect( esc_url_raw( add_query_arg( array(
		'page'    => $option['currentPage'],
		'updated' => 'true',
		), network_admin_url( 'admin.php' ) ) ) );
	exit();
}
function mtaa_brand_sanitize( $input ) {

	// Color Schemes
	$input['colorScheme'] = ( empty( $input['colorScheme'] ) ) ? '' : esc_attr( $input['colorScheme'] );
	$input['colorsHideUserProfileColors'] = ( empty( $input['colorsHideUserProfileColors'] ) ) ? '' : 'on';
	$input['colorsHideShadows'] = ( empty( $input['colorsHideShadows'] ) ) ? '' : 'on';
	foreach ( $input['colorSchemeCustomColors'] as $key => $value ) {
		$input['colorSchemeCustomColors'][ $key ] = ( empty( $input['colorSchemeCustomColors'][ $key ] ) ) ? '' : mtaa_brand_sanitize_hex( $input['colorSchemeCustomColors'][ $key ] );
	}

	// Login Page
	$input['loginLinkTitle'] = ( empty( $input['loginLinkTitle'] ) ) ? '' : esc_attr( $input['loginLinkTitle'] );
	$input['loginLinkUrl'] = ( empty( $input['loginLinkUrl'] ) ) ? '' : esc_url( $input['loginLinkUrl'] );
	$input['loginLogo'] = ( empty( $input['loginLogo'] ) ) ? '' : esc_url( $input['loginLogo'] );
	$input['loginLogoHide'] = ( empty( $input['loginLogoHide'] ) ) ? '' : 'on';
	$input['loginBgPosition'] = ( empty( $input['loginBgPosition'] ) ) ? '' : esc_attr( $input['loginBgPosition'] );
	$input['loginBgRepeat'] = ( empty( $input['loginBgRepeat'] ) ) ? '' : esc_attr( $input['loginBgRepeat'] );
	$input['loginBgImage'] = ( empty( $input['loginBgImage'] ) ) ? '' : esc_url( $input['loginBgImage'] );
	$input['loginBgFull'] = ( empty( $input['loginBgFull'] ) ) ? '' : 'on';

	// Admin Branding
	$input['adminLogo'] = ( empty( $input['adminLogo'] ) ) ? '' : esc_url( $input['adminLogo'] );
	$input['adminLogoFolded'] = ( empty( $input['adminLogoFolded'] ) ) ? '' : esc_url( $input['adminLogoFolded'] );
	$input['adminFavicon'] = ( empty( $input['adminFavicon'] ) ) ? '' : esc_url( $input['adminFavicon'] );

	// Dashboard
	$input['dashboardHideWelcome'] = ( empty( $input['dashboardHideWelcome'] ) ) ? '' : 'on';
	$input['dashboardCustomWidget'] = ( empty( $input['dashboardCustomWidget'] ) ) ? '' : 'on';
	$input['dashboardCustomWidgetTitle'] = ( empty( $input['dashboardCustomWidgetTitle'] ) ) ? '' : esc_attr( $input['dashboardCustomWidgetTitle'] );
	$input['dashboardCustomWidgetText'] = ( empty( $input['dashboardCustomWidgetText'] ) ) ? '' : mn_kses_post( force_balance_tags( $input['dashboardCustomWidgetText'] ) );
	foreach ( $input['dashboardWidgets'] as $key => $value ) {
		$input['dashboardWidgets'][ $key ] = ( '0' == $input['dashboardWidgets'][ $key ] ) ? '' : 'on';
	}

	// Footer Settings
	$input['footerTextShow'] = ( empty( $input['footerTextShow'] ) ) ? '' : 'on';
	$input['footerVersionHide'] = ( empty( $input['footerVersionHide'] ) ) ? '' : 'on';
	$input['footerText'] = ( empty( $input['footerText'] ) ) ? '' : mn_kses_post( force_balance_tags( $input['footerText'] ) );
	$input['footerHide'] = ( empty( $input['footerHide'] ) ) ? '' : 'on';

	// Admin Bar Settings
	$input['adminBarHide'] = ( empty( $input['adminBarHide'] ) ) ? '' : 'on';
	$input['adminBarHideMN'] = ( empty( $input['adminBarHideMN'] ) ) ? '' : 'on';

	// Permission Settings
	foreach ( $input['userPermissions'] as $key => $value ) {
		$input['userPermissions'][ $key ] = ( '0' == $input['userPermissions'][ $key ] ) ? '' : 'on';
	}

	// Admin Menu
	if ( isset( $input['adminMenu'] ) ) {
		foreach ( $input['adminMenu'] as $menuItem => $menuHide ) {
			$menuHide = ( '0' === $value ) ? '' : 'on';
			$menuItem = unserialize( base64_decode( $menuItem ) );
			foreach ( $menuItem as $key => $value ) {
				$key = ( empty( $key ) ) ? '' : esc_attr( $key );
				$value = ( empty( $value ) ) ? '' : esc_attr( $value );
			}
		}
	}

	foreach ( $input['adminMenuPermissions'] as $key => $value ) {
		$input['adminMenuPermissions'][ $key ] = ( '0' === $input['adminMenuPermissions'][ $key ] ) ? '' : 'on';
	}

	// Notices
	$input['noticeMNUpdate'] = ( empty( $input['noticeMNUpdate'] ) ) ? '' : 'on';
	$input['noticeThemeUpdate'] = ( empty( $input['noticeThemeUpdate'] ) ) ? '' : 'on';
	$input['noticePluginUpdate'] = ( empty( $input['noticePluginUpdate'] ) ) ? '' : 'on';
	$input['noticeHideAllUpdates'] = ( empty( $input['noticeHideAllUpdates'] ) ) ? '' : 'on';
	$input['contentHideHelp'] = ( empty( $input['contentHideHelp'] ) ) ? '' : 'on';
	$input['contentHideScreenOptions'] = ( empty( $input['contentHideScreenOptions'] ) ) ? '' : 'on';
	$input['contentHideMNTitle'] = ( empty( $input['contentHideMNTitle'] ) ) ? '' : 'on';

	// Settings
	$input['customLogin'] = ( empty( $input['customLogin'] ) ) ? '' : 'on';
	$input['customLoginURL'] = ( empty( $input['customLoginURL'] ) ) ? '' : esc_url( $input['customLoginURL'] );

	// Hidden Inputs
	$input['licenseDate'] = ( empty( $input['licenseDate'] ) ) ? '' : esc_attr( $input['licenseDate'] );
	$input['currentPage'] = ( empty( $input['currentPage'] ) ) ? '' : esc_attr( $input['currentPage'] );

	return $input;

}

// Sanitize Hex Colors
function mtaa_brand_sanitize_hex( $color ) {
	if ( '' === $color ) {
		return '';
	}

	if ( preg_match( '|^#([A-Fa-f0-9]{3}){1,2}$|', $color ) ) {
		return $color;
	}

	return null;
}

// Settings Pages
function mtaa_brand_color_schemes() {
	if ( ! current_user_can( 'manage_options' ) ) {
		mn_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'mtaa-brand' ) );
	}

	$page = 'mtaa_brand_color_schemes';
	include( __DIR__ . '/inc/content.php' );

}

function mtaa_brand_branding() {
	if ( ! current_user_can( 'manage_options' ) ) {
		mn_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'mtaa-brand' ) );
	}

	$page = 'mtaa_brand_branding';
	include( __DIR__ . '/inc/content.php' );

}

function mtaa_brand_dashboard() {
	if ( ! current_user_can( 'manage_options' ) ) {
		mn_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'mtaa-brand' ) );
	}

	$page = 'mtaa_brand_dashboard';
	include( __DIR__ . '/inc/content.php' );
}

function mtaa_brand_admin_menu() {
	if ( ! current_user_can( 'manage_options' ) ) {
		mn_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'mtaa-brand' ) );
	}

	$page = 'mtaa_brand_admin_menu';
	include( __DIR__ . '/inc/content.php' );
}

function mtaa_brand_admin_bar_footer() {
	if ( ! current_user_can( 'manage_options' ) ) {
		mn_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'mtaa-brand' ) );
	}

	$page = 'mtaa_brand_admin_bar_footer';
	include( __DIR__ . '/inc/content.php' );
}

function mtaa_brand_content_notices() {
	if ( ! current_user_can( 'manage_options' ) ) {
		mn_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'mtaa-brand' ) );
	}

	$page = 'mtaa_brand_content_notices';
	include( __DIR__ . '/inc/content.php' );
}

function mtaa_brand_permissions() {
	if ( ! current_user_can( 'manage_options' ) ) {
		mn_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'mtaa-brand' ) );
	}

	$page = 'mtaa_brand_permissions';
	include( __DIR__ . '/inc/content.php' );
}

function mtaa_brand_settings() {
	if ( ! current_user_can( 'manage_options' ) ) {
		mn_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'mtaa-brand' ) );
	}

	$page = 'mtaa_brand_settings';
	include( __DIR__ . '/inc/content.php' );
}

function mtaa_brand_about() {
	if ( ! current_user_can( 'manage_options' ) ) {
		mn_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'mtaa-brand' ) );
	}

	$page = 'mtaa_brand_about';
	include( __DIR__ . '/inc/content.php' );
}

function mtaa_brand_license() {
	if ( ! current_user_can( 'manage_options' ) ) {
		mn_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'mtaa-brand' ) );
	}

	$page = 'mtaa_brand_license';
	include( __DIR__ . '/inc/content.php' );
}

function mtaa_brand_import_export() {
	if ( ! current_user_can( 'manage_options' ) ) {
		mn_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'mtaa-brand' ) );
	}

	$page = 'mtaa_brand_import_export';
	include( __DIR__ . '/inc/content.php' );
}