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
}
?>
<style type="text/css" media="screen">

	/* *********************** */
	/* Admin Bar */
	/* *********************** */
	#mnadminbar {
		background-color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminBarBgColor'] ); ?>;
	}
	/* Admin Bar Hover Bg Color */
	#mnadminbar .ab-top-menu>li.hover>.ab-item,
	#mnadminbar .ab-top-menu>li:hover>.ab-item,
	#mnadminbar .ab-top-menu>li>.ab-item:focus,
	#mnadminbar.nojq .quicklinks .ab-top-menu>li>.ab-item:focus,
	#mnadminbar .menupop .ab-sub-wrapper,
	#mnadminbar .quicklinks .menupop ul.ab-sub-secondary,
	#mnadminbar .quicklinks .menupop ul.ab-sub-secondary .ab-submenu,
	.mn-responsive-open #mnadminbar #admin-bar-menu-toggle a,
	#mnadminbar:not(.mobile) .ab-top-menu>li:hover>.ab-item,
	#mnadminbar:not(.mobile) .ab-top-menu>li>.ab-item:focus {
		background: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminBarBgHoverColor'] ); ?>;
	}
	/* Top Level Tex Color */
	#mnadminbar #adminbarsearch:before,
	#mnadminbar .ab-icon:before,
	#mnadminbar .ab-item:before,
	#mnadminbar a.ab-item,
	#mnadminbar > #mn-toolbar span.ab-label,
	#mnadminbar > #mn-toolbar span.noticon,
	.mn-responsive-open #mnadminbar #admin-bar-menu-toggle .ab-icon:before {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminBarTopLevelColor'] ); ?>;
	}
	/* Top Level Text Color Hover */
	#mnadminbar li .ab-item:focus:before,
	#mnadminbar li a:focus .ab-icon:before,
	#mnadminbar li.hover .ab-icon:before,
	#mnadminbar li.hover .ab-item:before,
	#mnadminbar li:hover #adminbarsearch:before,
	#mnadminbar li:hover .ab-icon:before,
	#mnadminbar li:hover .ab-item:before,
	#mnadminbar > #mn-toolbar a:focus span.ab-label,
	#mnadminbar > #mn-toolbar li.hover span.ab-label,
	#mnadminbar > #mn-toolbar li:hover span.ab-label,
	#mnadminbar .ab-top-menu>li.hover>.ab-item,
	#mnadminbar .ab-top-menu>li:hover>.ab-item,
	#mnadminbar .ab-top-menu>li.hover>.ab-item,
	#mnadminbar.nojq .quicklinks .ab-top-menu>li>.ab-item:focus,
	#mnadminbar:not(.mobile) .ab-top-menu>li:hover>.ab-item,
	#mnadminbar:not(.mobile) .ab-top-menu>li>.ab-item:focus,
	#mnadminbar:not(.mobile)>#mn-toolbar a:focus span.ab-label,
	#mnadminbar:not(.mobile)>#mn-toolbar li:hover span.ab-label,
	#mnadminbar>#mn-toolbar li.hover span.ab-label {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminBarTopLevelHoverColor'] ); ?>;
	}
	/* Admin Bar Submenu Text Color */
	#mnadminbar .ab-submenu .ab-item,
	#mnadminbar .quicklinks .menupop ul li a,
	#mnadminbar .quicklinks .menupop ul li a strong,
	#mnadminbar .quicklinks .menupop.hover ul li a,
	#mnadminbar.nojs .quicklinks .menupop:hover ul li a {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminBarSubmenuTextColor'] ); ?>;
	}
	/* Admin Bar Submenu Text Hover Color */
	#mnadminbar .quicklinks .menupop ul li a:focus,
	#mnadminbar .quicklinks .menupop ul li a:focus strong,
	#mnadminbar .quicklinks .menupop ul li a:hover,
	#mnadminbar .quicklinks .menupop ul li a:hover strong,
	#mnadminbar .quicklinks .menupop.hover ul li a:focus,
	#mnadminbar .quicklinks .menupop.hover ul li a:hover,
	#mnadminbar.nojs .quicklinks .menupop:hover ul li a:focus,
	#mnadminbar.nojs .quicklinks .menupop:hover ul li a:hover {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminBarSubmenuTextHoverColor'] ); ?>;
	}

	/* Custom Admin Bar Settings for Specific Plugins */
	#mncontent #admin-bar-root-default #admin-bar-MNML_ALS {
		color: <?php echo mtaa_brand_sanitize_hex( $colorSelected['adminBarTopLevelColor'] ); ?>;
		padding: 14px 10px 15px 10px;
	}
	.folded #mncontent #admin-bar-root-default #admin-bar-MNML_ALS {
		padding: 2px 7px 2px 7px;
	}
	@media screen and (max-width: 960px) {
		#mncontent #admin-bar-root-default #admin-bar-MNML_ALS {
			padding: 2px 7px 2px 7px;
		}
	}
	@media screen and (max-width: 782px) {
		#mncontent #admin-bar-root-default #admin-bar-MNML_ALS,
		.folded #mncontent #admin-bar-root-default #admin-bar-MNML_ALS {
			padding: 0;
		}
	}

</style>