<?php

global $mtaa_brand_settings;

// Login Page Variables
$loginLinkTitle = ( $mtaa_brand_settings['loginLinkTitle'] ) ? $mtaa_brand_settings['loginLinkTitle'] : '';
$loginLinkUrl = ( $mtaa_brand_settings['loginLinkUrl'] ) ? $mtaa_brand_settings['loginLinkUrl'] : '';
$loginLogo = ( $mtaa_brand_settings['loginLogo'] ) ? '<img src="' . esc_url( $mtaa_brand_settings['loginLogo'] ) . '" />' : '';
$loginLogoDelete = ( $mtaa_brand_settings['loginLogo'] ) ? '' : 'style="display: none;"';
$loginBgImage = ( $mtaa_brand_settings['loginBgImage'] ) ? '<img src="' . esc_url( $mtaa_brand_settings['loginBgImage'] ) . '" />' : '';
$loginBgImageDelete = ( $mtaa_brand_settings['loginBgImage'] ) ? '' : 'style="display: none;"';
$loginBgPosition = $mtaa_brand_settings['loginBgPosition'];
$loginBgRepeat = $mtaa_brand_settings['loginBgRepeat'];

// Admin Branding Variables
$adminLogo = ( $mtaa_brand_settings['adminLogo'] ) ? '<img src="' . esc_url( $mtaa_brand_settings['adminLogo'] ) . '" />' : '';
$adminLogoFolded = ( $mtaa_brand_settings['adminLogoFolded'] ) ? '<img src="' . esc_url( $mtaa_brand_settings['adminLogoFolded'] ) . '" />' : '';
$adminFavicon = ( $mtaa_brand_settings['adminFavicon'] ) ? '<img src="' . esc_url( $mtaa_brand_settings['adminFavicon'] ) . '" />' : '';
$adminLogoDelete = ( $mtaa_brand_settings['adminLogo'] ) ? '' : 'style="display: none;"';
$adminLogoFoldedDelete = ( $mtaa_brand_settings['adminLogoFolded'] ) ? '' : 'style="display: none;"';
$adminFaviconDelete = ( $mtaa_brand_settings['adminFavicon'] ) ? '' : 'style="display: none;"';

// Dashboard
$dashboardCustomWidgetTitle = ( $mtaa_brand_settings['dashboardCustomWidgetTitle'] ) ? $mtaa_brand_settings['dashboardCustomWidgetTitle'] : '';
$dashboardCustomWidgetText = ( $mtaa_brand_settings['dashboardCustomWidgetText'] ) ? $mtaa_brand_settings['dashboardCustomWidgetText'] : '';

// Footer Settings Variables
$footerText = ( $mtaa_brand_settings['footerText'] ) ? $mtaa_brand_settings['footerText'] : '';

// Settings
$customLoginURL = ( $mtaa_brand_settings['customLoginURL'] ) ? $mtaa_brand_settings['customLoginURL'] : '';