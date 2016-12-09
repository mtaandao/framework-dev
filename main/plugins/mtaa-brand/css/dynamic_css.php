<?php
include( __DIR__ . '/../inc/colors.php' );

// Check to see if the user selected an admin color in their profile.
if ( 'fresh' == mtaa_brand_get_user_admin_color() || '' == mtaa_brand_get_user_admin_color() ) {
	if ( 'default' == $mtaa_brand_settings['colorScheme'] ) {
		$colorSelected = $colorDefault;
	} else if ( 'light' == $mtaa_brand_settings['colorScheme'] ) {
		$colorSelected = $colorLight;
	} else if ( 'blue' == $mtaa_brand_settings['colorScheme'] ) {
		$colorSelected = $colorBlue;
	} else if ( 'coffee' == $mtaa_brand_settings['colorScheme'] ) {
		$colorSelected = $colorCoffee;
	} else if ( 'ectoplasm' == $mtaa_brand_settings['colorScheme'] ) {
		$colorSelected = $colorEctoplasm;
	} else if ( 'midnight' == $mtaa_brand_settings['colorScheme'] ) {
		$colorSelected = $colorMidnight;
	} else if ( 'ocean' == $mtaa_brand_settings['colorScheme'] ) {
		$colorSelected = $colorOcean;
	} else if ( 'sunrise' == $mtaa_brand_settings['colorScheme'] ) {
		$colorSelected = $colorSunrise;
	} else if ( 'custom' == $mtaa_brand_settings['colorScheme'] ) {
		$colorSelected = $colorCustom;
	} else {
		$colorSelected = $colorDefault;
	}
} else if ( 'light' == mtaa_brand_get_user_admin_color() ) {
	$colorSelected = $colorLight;
} else if ( 'blue' == mtaa_brand_get_user_admin_color() ) {
	$colorSelected = $colorBlue;
} else if ( 'coffee' == mtaa_brand_get_user_admin_color() ) {
	$colorSelected = $colorCoffee;
} else if ( 'ectoplasm' == mtaa_brand_get_user_admin_color() ) {
	$colorSelected = $colorEctoplasm;
} else if ( 'midnight' == mtaa_brand_get_user_admin_color() ) {
	$colorSelected = $colorMidnight;
} else if ( 'ocean' == mtaa_brand_get_user_admin_color() ) {
	$colorSelected = $colorOcean;
} else if ( 'sunrise' == mtaa_brand_get_user_admin_color() ) {
	$colorSelected = $colorSunrise;
} else if ( 'custom' == mtaa_brand_get_user_admin_color() ) {
	$colorSelected = $colorCustom;
} else {
	$colorSelected = $colorDefault;
}
?>

<style type="text/css" media="screen">

	/* *********************** */
	/* Mtaa Branding */
	/* *********************** */
	#slate__colorSchemes .premadeColors label {
		border-color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentDividerColor'] ); ?>;
	}
	#slate__colorSchemes .premadeColors label.selected,
	#slate__colorSchemes .premadeColors label:hover {
		background: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentDividerColor'] ); ?>;
		border-color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentDividerColor'] ); ?>;
	}


	/* *********************** */
	/* Login Page */
	/* *********************** */
	body.login {
		background-color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['loginBgColor'] ); ?>;
	}
	#loginform {
		background-color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['loginFormBgColor'] ); ?>;
	}
	.login h1 a {
		<?php if ( $colorDefault == $colorSelected || $colorCoffee == $colorSelected || $colorEctoplasm == $colorSelected || $colorMidnight == $colorSelected || $colorSunrise == $colorSelected ) { ?>
			background-image: url(<?php echo admin_url(); ?>/images/w-logo-white.png?ver=20131202);
			background-image: none,url(<?php echo admin_url(); ?>/images/mtaandao-logo-white.svg?ver=20131107);
		<?php	} ?>
	}
	#loginform label {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['loginFormTextColor'] ); ?>;
	}
	#loginform input {
		background-color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['loginFormInputBgColor'] ); ?>;
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['loginFormInputTextColor'] ); ?>;
	}
	#loginform input:focus {
		border: 1px solid <?php echo mtaa_brand_sanitize_hex( $colorSelected['loginFormInputFocusColor'] ); ?>;
	}
	#loginform input[type="checkbox"]:checked::before {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['loginFormInputTextColor'] ); ?>;
	}
	.login #backtoblog a, 
	.login #nav a {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['loginFormLinkColor'] ); ?>;
	}
	.login #backtoblog a:hover, 
	.login #nav a:hover {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['loginFormLinkHoverColor'] ); ?>;
	}
	#loginform .button-primary {
		border-color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['loginButtonBgColor'] ); ?>;
		background-color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['loginButtonBgColor'] ); ?>;
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['loginButtonTextColor'] ); ?>;
	}
	#loginform .button-primary:hover {
		border-color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['loginButtonHoverBgColor'] ); ?>;
		background-color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['loginButtonHoverBgColor'] ); ?>;
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['loginButtonHoverTextColor'] ); ?>;
	}

	/* *********************** */
	/* Admin Menu */
	/* *********************** */
	/* Background */
	#adminmenuback {
		background-color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminMenuBgColor'] ); ?>;
		<?php if ('on' == $mtaa_brand_settings['colorsHideShadows']) { ?>
			background-image: none;
		<?php	} ?>
	}
	@media only screen and (max-width: 782px) {
		#adminmenuwrap {
			background-color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminMenuBgColor'] ); ?>;
		}
	}
	/* Divider Line */
	#adminmenu li.mn-menu-separator,
	#adminmenu #collapse-menu {
		border-top-color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminMenuDividerColor'] ); ?>;
	}
	/* Top Level Menu Color */
	#adminmenu a,
	#adminmenu div.mn-menu-image:before,
	#collapse-menu, 
	#collapse-menu #collapse-button div:after {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminTopLevelTextColor'] ); ?>;
	}
	/* Top Level Menu Hover */
	#adminmenu .mn-submenu a:focus, 
	#adminmenu .mn-submenu a:hover, 
	#adminmenu a:hover, 
	#adminmenu li.menu-top>a:focus,
	#adminmenu li.opensub>a.menu-top,
	#adminmenu li:hover div.mn-menu-image:before,
	#adminmenu li.opensub a,
	#collapse-menu:hover, 
	#collapse-menu:hover #collapse-button div:after,
	#adminmenu li a:focus div.mn-menu-image:before,
	#adminmenu li.opensub div.mn-menu-image:before,
	#adminmenu li:hover div.mn-menu-image:before {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminTopLevelTextHoverColor'] ); ?>;
	}
	/* Selected Top Level Menu Color */
	#adminmenu li.current a.menu-top,
	#adminmenu li.mn-has-current-submenu a.mn-has-current-submenu,
	#adminmenu .current div.mn-menu-image:before, 
	#adminmenu .mn-has-current-submenu div.mn-menu-image:before, 
	#adminmenu a.current:hover div.mn-menu-image:before, 
	#adminmenu a.mn-has-current-submenu:hover div.mn-menu-image:before, 
	#adminmenu li.mn-has-current-submenu:hover div.mn-menu-image:before {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminTopLevelSelectedTextColor'] ); ?>;
	}
	/* Folded Top Menu Text Color */
	#adminmenu .mn-submenu .mn-submenu-head,
	.folded #adminmenu .mn-submenu .mn-submenu-head {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminTopLevelFoldedTextColor'] ); ?>;
	}
	#adminmenu .mn-has-current-submenu.opensub .mn-submenu .mn-submenu-head,
	.folded #adminmenu .mn-has-current-submenu .mn-submenu .mn-submenu-head {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminTopLevelSelectedFoldedTextColor'] ); ?>;
	}
	/* Folded Top Menu and Submenu Background Color */
	.folded #adminmenu li.mn-has-current-submenu a.mn-has-current-submenu {
		background: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminTopLevelSelectedFoldedBg'] ); ?>;
	}
	@media only screen and (max-width: 960px) {
		.auto-fold #adminmenu li.mn-has-current-submenu a.mn-has-current-submenu,
		#adminmenu .mn-has-current-submenu.opensub .mn-submenu {
			background: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminTopLevelSelectedFoldedBg'] ); ?>;
		}
	}

	/* Folded Top Menu Icon Color */
	.folded #adminmenu .current div.mn-menu-image:before, 
	.folded #adminmenu .mn-has-current-submenu div.mn-menu-image:before, 
	.folded #adminmenu a.current:hover div.mn-menu-image:before, 
	.folded #adminmenu a.mn-has-current-submenu:hover div.mn-menu-image:before, 
	.folded #adminmenu li.mn-has-current-submenu:hover div.mn-menu-image:before {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminTopLevelSelectedFoldedIconColor'] ); ?>;
	}
	@media only screen and (max-width: 960px) {
		.auto-fold #adminmenu .current div.mn-menu-image:before, 
		.auto-fold #adminmenu .mn-has-current-submenu div.mn-menu-image:before, 
		.auto-fold #adminmenu a.current:hover div.mn-menu-image:before, 
		.auto-fold #adminmenu a.mn-has-current-submenu:hover div.mn-menu-image:before, 
		.auto-fold #adminmenu li.mn-has-current-submenu:hover div.mn-menu-image:before {
			color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminTopLevelSelectedFoldedIconColor'] ); ?>;
		}
	}
	@media only screen and (max-width: 782px) {
		.auto-fold #adminmenu .current div.mn-menu-image:before, 
		.auto-fold #adminmenu .mn-has-current-submenu div.mn-menu-image:before, 
		.auto-fold #adminmenu a.current:hover div.mn-menu-image:before, 
		.auto-fold #adminmenu a.mn-has-current-submenu:hover div.mn-menu-image:before, 
		.auto-fold #adminmenu li.mn-has-current-submenu:hover div.mn-menu-image:before {
			color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminTopLevelSelectedTextColor'] ); ?>;
		}
	}
	/* Open Submenu Color */
	#adminmenu .mn-submenu a,
	#adminmenu .mn-has-current-submenu .mn-submenu a,
	#adminmenu .mn-has-current-submenu.opensub .mn-submenu a {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminOpenSubmenuTextColor'] ); ?>;
	}
	/* Open Submenu Hover Color */
	#adminmenu .mn-submenu a:hover,
	#adminmenu .mn-has-current-submenu .mn-submenu a:hover,
	#adminmenu .mn-has-current-submenu.opensub .mn-submenu a:hover {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminOpenSubmenuTextHoverColor'] ); ?>;
	}
	/* Open Selected Submenu Color */
	#adminmenu .opensub .mn-submenu li.current a, 
	#adminmenu .mn-submenu li.current, 
	#adminmenu .mn-submenu li.current a, 
	#adminmenu .mn-submenu li.current a:focus, 
	#adminmenu .mn-submenu li.current a:hover, 
	#adminmenu a.mn-has-current-submenu:focus+.mn-submenu li.current a {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminOpenSubmenuTextSelectedColor'] ); ?>;
	}
	/* Floating Submenu Background */
	#adminmenu .mn-submenu {
		background: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminFloatingSubmenuBgColor'] ); ?>;
	}
	/* Submenu Arrow Color */
	#adminmenu li.mn-has-submenu.mn-not-current-submenu.opensub:hover:after {
		border-right-color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminFloatingSubmenuBgColor'] ); ?>;
	}
	/* Floating Submenu Text Color */
	#adminmenu .opensub .mn-submenu a {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminFloatingSubmenuTextColor'] ); ?>;
	}
	/* Floating Submenu Text Hover Color */
	#adminmenu .opensub .mn-submenu a:hover {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminFloatingSubmenuTextHoverColor'] ); ?>;
	}
	/* Folded Floating Submenu Hover Color */
	.folded #adminmenu .mn-has-current-submenu a:hover,
	.folded #adminmenu .mn-has-current-submenu.opensub .mn-submenu a:hover {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminFoldedFloatingSubmenuTextHoverColor'] ); ?>;
	}
	/* Folded Floating Submenu Text Color */
	.folded.sticky-menu #adminmenu .mn-has-current-submenu.opensub .mn-submenu a:hover {
			color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminFoldedFloatingSubmenuTextHoverColor'] ); ?>;
		}
	@media only screen and (max-width: 960px) {
		.sticky-menu #adminmenu .mn-has-current-submenu.opensub .mn-submenu a:hover {
			color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminFoldedFloatingSubmenuTextHoverColor'] ); ?>;
		}
	}
	/* Folded Selected Floating Unselected Submenu Color */
	.folded #adminmenu .mn-has-current-submenu a,
	.folded #adminmenu .mn-has-current-submenu.opensub .mn-submenu a {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminFoldedFloatingSubmenuTextColor'] ); ?>;
	}
	.folded.sticky-menu #adminmenu .mn-has-current-submenu.opensub .mn-submenu a {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminFoldedFloatingSubmenuTextColor'] ); ?>;
	}
	@media only screen and (max-width: 960px) {
		.sticky-menu #adminmenu .mn-has-current-submenu.opensub .mn-submenu a {
			color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminFoldedFloatingSubmenuTextColor'] ); ?>;
		}
	}
	@media only screen and (max-width: 782px) {
		.folded #adminmenu .mn-has-current-submenu a,
		.folded #adminmenu .mn-has-current-submenu.opensub .mn-submenu a {
			color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminOpenSubmenuTextColor'] ); ?>;
		}
		.folded #adminmenu .mn-has-current-submenu a:hover,
		.folded #adminmenu .mn-has-current-submenu.opensub .mn-submenu a:hover {
			color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminOpenSubmenuTextHoverColor'] ); ?>;
		}
	}
	/* Folded Selected Floating Selected Submenu Color */
	.folded #adminmenu .mn-submenu li.current, 
	.folded #adminmenu .mn-submenu li.current a,
	.folded #adminmenu .opensub .mn-submenu li.current, 
	.folded #adminmenu .opensub .mn-submenu li.current a, 
	.folded #adminmenu .opensub .mn-submenu li.current a:focus, 
	.folded #adminmenu .opensub .mn-submenu li.current a:hover {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminFoldedFloatingSubmenuSelectedTextColor'] ); ?>;
	}
	@media only screen and (max-width: 960px) {
		#adminmenu .mn-submenu li.current a,
		#adminmenu .opensub .mn-submenu li.current,
		#adminmenu .opensub .mn-submenu li.current a, 
		#adminmenu .opensub .mn-submenu li.current a:focus, 
		#adminmenu .opensub .mn-submenu li.current a:hover {
			color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminOpenSubmenuTextSelectedColor'] ); ?>;
		}
		.sticky-menu #adminmenu .mn-submenu li.current a,
		.sticky-menu #adminmenu .opensub .mn-submenu li.current,
		.sticky-menu #adminmenu .opensub .mn-submenu li.current a, 
		.sticky-menu #adminmenu .opensub .mn-submenu li.current a:focus, 
		.sticky-menu #adminmenu .opensub .mn-submenu li.current a:hover {
			color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminFoldedFloatingSubmenuSelectedTextColor'] ); ?>;
		}
	}
	/* Folded Floating Submenu Background and Color */
	/* Auto-collapsed */
	@media only screen and (max-width: 960px) {
		.sticky-menu #adminmenu .mn-has-current-submenu.opensub.mn-menu-open .mn-submenu,
		.sticky-menu #adminmenu .mn-menu-open.opensub .mn-submenu,
		.sticky-menu #adminmenu a.mn-has-current-submenu.mn-menu-open:focus+.mn-submenu, 
		.sticky-menu .no-js li.mn-has-current-submenu.mn-menu-open:hover .mn-submenu {
			background: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminTopLevelSelectedFoldedBg'] ); ?>;
		}
	}
	.folded #adminmenu .mn-has-current-submenu .mn-submenu,
	.folded #adminmenu .mn-has-current-submenu.opensub .mn-submenu,
	.folded #adminmenu a.mn-has-current-submenu:focus+.mn-submenu,
	.folded #adminmenu a.mn-has-current-submenu.opensub:focus+.mn-submenu,
	.folded #adminmenu .mn-has-current-submenu .mn-submenu .mn-submenu-head, 
	.folded #adminmenu .mn-has-current-submenu.opensub .mn-submenu .mn-submenu-head, 
	.folded #adminmenu .mn-menu-arrow, 
	.folded #adminmenu .mn-menu-arrow div, 
	.folded #adminmenu li.current a.menu-top, 
	.folded #adminmenu li.mn-has-current-submenu a.mn-has-current-submenu, 
	.folded #adminmenu li.current.menu-top, 
	.folded #adminmenu li.mn-has-current-submenu {
		background: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminTopLevelSelectedFoldedBg'] ); ?>;
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminTopLevelSelectedFoldedTextColor'] ); ?>;
	}
	.folded #adminmenu .mn-has-current-submenu .mn-submenu .mn-submenu-head, 
	.folded #adminmenu .mn-has-current-submenu.opensub .mn-submenu .mn-submenu-head {
		background: none;
	}
	@media only screen and (max-width: 782px) {
		.folded #adminmenu .mn-has-current-submenu .mn-submenu,
		.folded #adminmenu .mn-has-current-submenu.opensub .mn-submenu,
		.folded #adminmenu a.mn-has-current-submenu:focus+.mn-submenu,
		.folded #adminmenu a.mn-has-current-submenu.opensub:focus+.mn-submenu,
		.folded #adminmenu .mn-has-current-submenu .mn-submenu .mn-submenu-head, 
		.folded #adminmenu .mn-has-current-submenu.opensub .mn-submenu .mn-submenu-head,
		.folded #adminmenu .mn-menu-arrow, 
		.folded #adminmenu .mn-menu-arrow div, 
		.folded #adminmenu li.current a.menu-top, 
		.folded #adminmenu li.mn-has-current-submenu a.mn-has-current-submenu, 
		.folded #adminmenu li.current.menu-top, 
		.folded #adminmenu li.mn-has-current-submenu {
			background: none;
			color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminTopLevelSelectedTextColor'] ); ?>;
		}
	}
	/* Folded Floating Top Level Menu Background and Color */
	@media only screen and (max-width: 960px) {
		#adminmenu .mn-has-current-submenu .mn-submenu .mn-submenu-head, 
		#adminmenu .mn-menu-arrow, 
		#adminmenu .mn-menu-arrow div, 
		#adminmenu li.current a.menu-top, 
		.folded #adminmenu li.current.menu-top, 
		.folded #adminmenu li.mn-has-current-submenu {
			background: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminTopLevelSelectedFoldedBg'] ); ?>;
			color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminTopLevelSelectedFoldedTextColor'] ); ?>;
		}
	}
	@media only screen and (max-width: 782px) {
		#adminmenu .mn-has-current-submenu .mn-submenu .mn-submenu-head, 
		#adminmenu .mn-menu-arrow, 
		#adminmenu .mn-menu-arrow div, 
		#adminmenu li.current a.menu-top,
		.mn-responsive-open #adminmenu li.current a.menu-top,
		.folded #adminmenu .mn-has-current-submenu .mn-submenu,
		.folded #adminmenu a.mn-has-current-submenu:focus+.mn-submenu,
		.folded #adminmenu .mn-has-current-submenu .mn-submenu .mn-submenu-head,
		.folded #adminmenu li.current.menu-top, 
		.folded #adminmenu li.mn-has-current-submenu {
			background: none;
			color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminTopLevelSelectedTextColor'] ); ?>;
		}
	}
	/* Update Notices */
	#adminmenu .awaiting-mod, 
	#adminmenu .update-plugins, 
	#sidemenu li a span.update-plugins,
	#adminmenu li a.mn-has-current-submenu .update-plugins, 
	#adminmenu li.current a .awaiting-mod {
		background: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminNoticeBgColor'] ); ?>;
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminNoticeColor'] ); ?>;
	}

	/* *********************** */
	/* Footer */
	/* *********************** */
	#mnfooter {
		background-color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['footerBgColor'] ); ?>;
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['footerTextColor'] ); ?>;
	}
	#mnfooter a {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['footerLinkColor'] ); ?>;
	}
	#mnfooter a:hover {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['footerLinkHoverColor'] ); ?>;
	}

	/* *********************** */
	/* Content Colors */
	/* *********************** */

	/*sidebarTextColor*/
	/* Primary Link Color */
	a,
	.view-switch a.current:before {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentLinkColor'] ); ?>;
	}
	a:hover {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentLinkHoverColor'] ); ?>;
	}
	/* Text Color */
	#poststuff #post-body.columns-2 #side-sortables, 
	.comment-php #submitdiv,
	#postbox-container-2,
	.howto,
	.ac_match, 
	.subsubsub a.current {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentTextColor'] ); ?>;
	}
	/* Icon Colors */ 
	span.mn-media-buttons-icon:before,
	.post-format-icon:before, 
	.post-state-format:before,
	input[type=radio]:checked+label:before,
	input[type=checkbox]:checked:before {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentTextColor'] ); ?>;
	}
	.insert-media.add_media:hover span.mn-media-buttons-icon:before {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentStandardButtonTextHoverColor'] ); ?>;
	}
	input[type=radio]:checked:before {
		background: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentTextColor'] ); ?>;
	}
	/* Arrow Color */
	.accordion-section-title:after, 
	.handlediv, 
	.item-edit, 
	.sidebar-name-arrow, 
	.widget-action {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentTextColor'] ); ?>;
	}
	/* Heading Color */
	.wrap h2,
	#poststuff h3,
	.welcome-panel-content h3,
	#dashboard-widgets-wrap h3,
	.widefat tfoot tr th, .widefat thead tr th,
	th.manage-column a, 
	th.sortable a:active, 
	th.sortable a:focus, 
	th.sortable a:hover {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentHeadingTextColor'] ); ?>;
	}
	/* Form Focus Color */
	input[type=checkbox]:focus, 
	input[type=color]:focus, 
	input[type=date]:focus, 
	input[type=datetime-local]:focus, 
	input[type=datetime]:focus, 
	input[type=email]:focus, 
	input[type=month]:focus, 
	input[type=number]:focus, 
	input[type=password]:focus, 
	input[type=radio]:focus, 
	input[type=search]:focus, 
	input[type=tel]:focus, 
	input[type=text]:focus, 
	input[type=time]:focus, 
	input[type=url]:focus, 
	input[type=week]:focus, 
	select:focus, 
	textarea:focus {
		border-color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentLinkHoverColor'] ); ?>;
	}
	/* Table Nav */
	.tablenav .tablenav-pages a:focus, 
	.tablenav .tablenav-pages a:hover {
		background: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentLinkHoverColor'] ); ?>;
		color: #fff;
	}

	/* Sidebar, accent, etc */
	#side-sortablesback, 
	.comment-php #submitdiv-back,
	#poststuff #post-body.columns-2 #side-sortables, 
	.comment-php #submitdiv,
	#normal-sortables .postbox,
	#dashboard-widgets-wrap #normal-sortables .postbox,
	#dashboard-widgets-wrap #side-sortables .postbox,
	#dashboard-widgets-wrap #column3-sortables .postbox, 
	#dashboard-widgets-wrap #column4-sortables .postbox, 
	#dashboard-widgets-wrap #column5-sortables .postbox,
	#contextual-help-link-wrap, 
	#screen-options-link-wrap,
	.edit-tags-php #col-left,
	#col-leftback {
		background-color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['sidebarBgColor'] ); ?>;
		<?php if ('on' == $mtaa_brand_settings['colorsHideShadows']) { ?>
			background-image: none;
		<?php	} ?>
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['sidebarTextColor'] ); ?>;
	}
	@media only screen and (max-width: 850px) {
		.postbox {
			background-color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['sidebarBgColor'] ); ?>;
			<?php if ('on' == $mtaa_brand_settings['colorsHideShadows']) { ?>
				background-image: none;
			<?php	} ?>
			color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['sidebarTextColor'] ); ?>;
		}
	}
	/* Tables */
	.mn-list-table tr:hover,
	table.comments tr:hover,
	.edit-tags-php #col-left,
	#col-leftback {
		background-color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentTableRowBgHoverColor'] ); ?>;
	}
	#poststuff h3 {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['sidebarHeadingColor'] ); ?>;
	}
	/* Icon Colors */
	.postbox #misc-publishing-actions label[for=post_status]:before,
	#post-body .postbox #visibility:before, 
	#post-body .postbox .misc-pub-revisions:before,
	.postbox .curtime #timestamp:before {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['sidebarIconColor'] ); ?>;
	}
	.postbox .howto,
	.postbox input[type=radio]:checked:before,
	.postbox input[type=checkbox]:checked:before {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['sidebarTextColor'] ); ?>;
	}
	/* Links */
	.postbox a {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['sidebarLinkColor'] ); ?>;
	}
	.postbox a:hover {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['sidebarLinkHoverColor'] ); ?>;
	}
	/* Inputs */
	.postbox input[type=checkbox]:focus, 
	.postbox input[type=color]:focus, 
	.postbox input[type=date]:focus, 
	.postbox input[type=datetime-local]:focus, 
	.postbox input[type=datetime]:focus, 
	.postbox input[type=email]:focus, 
	.postbox input[type=month]:focus, 
	.postbox input[type=number]:focus, 
	.postbox input[type=password]:focus, 
	.postbox input[type=radio]:focus, 
	.postbox input[type=search]:focus, 
	.postbox input[type=tel]:focus, 
	.postbox input[type=text]:focus, 
	.postbox input[type=time]:focus, 
	.postbox input[type=url]:focus, 
	.postbox input[type=week]:focus, 
	.postbox select:focus, 
	.postbox textarea:focus {
		border-color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['sidebarLinkColor'] ); ?>;
	}
	/* Arrow Color */
	.postbox .accordion-section-title:after, 
	.postbox .handlediv, 
	.postbox .item-edit, 
	.postbox .sidebar-name-arrow, 
	.postbox .widget-action {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['sidebarHeadingColor'] ); ?>;
	}
	.postbox .accordion-section-title:after:hover, 
	.postbox .handlediv:hover, 
	.postbox .item-edit:hover, 
	.postbox .sidebar-name-arrow:hover, 
	.postbox .widget-action:hover {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['sidebarLinkHoverColor'] ); ?>;
	}
	/* Divider */
	.postbox {
		border-color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['sidebarDividerColor'] ); ?>;
	}

	/* Content Buttons */
	.mn-core-ui .button.button-primary {
		background: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentPrimaryButtonBgColor'] ); ?>;
		border-color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentPrimaryButtonBgColor'] ); ?>;
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentPrimaryButtonTextColor'] ); ?>;
	}
	.mn-core-ui .button.button-primary:hover {
		background: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentPrimaryButtonBgHoverColor'] ); ?>;
		border-color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentPrimaryButtonBgHoverColor'] ); ?>;
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentPrimaryButtonTextHoverColor'] ); ?>;
	}
	.mn-core-ui .button, 
	.mn-core-ui .button-secondary,
	.comment-php #minor-publishing .button {
		background: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentStandardButtonBgColor'] ); ?>;
		border-color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentStandardButtonBgColor'] ); ?>;
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentStandardButtonTextColor'] ); ?>;
	}
	.comment-php #minor-publishing .button:hover,
	.mn-core-ui .button-secondary:focus, 
	.mn-core-ui .button-secondary:hover, 
	.mn-core-ui .button.focus, 
	.mn-core-ui .button.hover, 
	.mn-core-ui .button:focus, 
	.mn-core-ui .button:hover {
		background: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentStandardButtonBgHoverColor'] ); ?>;
		border-color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentStandardButtonBgHoverColor'] ); ?>;
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentStandardButtonTextHoverColor'] ); ?>;
	}
	/* TinyMCE Tabs */
	.mn-switch-editor.switch-html,
	.mn-switch-editor:hover {
		background: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentDividerColor'] ); ?> !important;
	}
	.html-active .switch-html, 
	.tmce-active .switch-tmce {
		background: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentStandardButtonBgColor'] ); ?> !important;
	}
	/* Add New Button */
	.wrap .add-new-h2, 
	.wrap .add-new-h2:active {
		background: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentPrimaryButtonBgColor'] ); ?>;
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentPrimaryButtonTextColor'] ); ?>;
	}
	.wrap .add-new-h2:hover {
		background: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentPrimaryButtonBgHoverColor'] ); ?>;
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentPrimaryButtonTextHoverColor'] ); ?>;
	}

	/* Sidebar Buttons */
	.mn-core-ui .postbox .button.button-primary {
		background: <?php echo mtaa_brand_sanitize_hex( $colorSelected['sidebarPrimaryButtonBgColor'] ); ?>;
		border-color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['sidebarPrimaryButtonBgColor'] ); ?>;
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['sidebarPrimaryButtonTextColor'] ); ?>;
	} 
	.mn-core-ui .postbox .button.button-primary:hover {
		background: <?php echo mtaa_brand_sanitize_hex( $colorSelected['sidebarPrimaryButtonBgHoverColor'] ); ?>;
		border-color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['sidebarPrimaryButtonBgHoverColor'] ); ?>;
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['sidebarPrimaryButtonTextHoverColor'] ); ?>;
	}
	.mn-core-ui .postbox .button, 
	.mn-core-ui .postbox .button-secondary, 
	.comment-php .postbox #minor-publishing .button {
		background: <?php echo mtaa_brand_sanitize_hex( $colorSelected['sidebarStandardButtonBgColor'] ); ?>;
		border-color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['sidebarStandardButtonBgColor'] ); ?>;
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['sidebarStandardButtonTextColor'] ); ?>;
	}
	.mn-core-ui .postbox .button:hover, 
	.comment-php .postbox #minor-publishing .button:hover,
	.mn-core-ui .postbox .button-secondary:focus, 
	.mn-core-ui .postbox .button-secondary:hover, 
	.mn-core-ui .postbox .button.focus, 
	.mn-core-ui .postbox .button.hover, 
	.mn-core-ui .postbox .button:focus, 
	.mn-core-ui .postbox .button:hover {
		background: <?php echo mtaa_brand_sanitize_hex( $colorSelected['sidebarStandardButtonBgHoverColor'] ); ?>;
		border-color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['sidebarStandardButtonBgHoverColor'] ); ?>;
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['sidebarStandardButtonTextHoverColor'] ); ?>;
	}

	/* Meta Link Tabs */
	#screen-meta-links a,
	#screen-meta-links a.show-settings {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentMetaTextColor'] ); ?>;
		background: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentMetaBgColor'] ); ?> !important;
	}
	#screen-meta-links a:hover,
	#screen-meta-links a.show-settings:hover {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentMetaTextHoverColor'] ); ?>;
		background: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentMetaBgHoverColor'] ); ?> !important;
	}
	#screen-meta-links a:after {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentMetaTextColor'] ); ?>;
	}
	#screen-meta-links a:hover:after {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentMetaTextHoverColor'] ); ?>;
	}

	/* Dividing Line Colors */
	.wrap h2,
	#post-body-content > h2,
	.widefat thead th,
	.widefat tfoot th,
	.slate-settings .pageSection section,
	#slate__colorSchemes .colorNav ul,
	#welcome-panel,
	#main-editor-tools,
	.mn-editor-expand #main-editor-tools {
		border-color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentDividerColor'] ); ?>;
	}

	/* Nav Tabs */
	.nav-tab {
		background: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentDividerColor'] ); ?>;
		border-color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentDividerColor'] ); ?>;
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentTextColor'] ); ?>;
	}
	.nav-tab:hover {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['contentTextColor'] ); ?>;
	}

</style>