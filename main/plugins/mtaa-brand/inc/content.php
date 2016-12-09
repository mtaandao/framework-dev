<?php
include( __DIR__ . '/variables.php' );
include( __DIR__ . '/colors.php' );
?>

<div class="wrap slate-settings">

	<?php
	if ( is_multisite() && is_plugin_active_for_network( 'mtaa-branding/mtaa-branding.php' ) ) {
		$mtaa_brand_license = get_site_option( 'mtaa_brand_license' );
	} else {
		$mtaa_brand_license = get_option( 'mtaa_brand_license' );
	}
	$statuses = array( '', 'removed', 'failed', 'used', 'invalid', 'oops' );
	if ( in_array( $mtaa_brand_license['licenseStatus'], $statuses ) ) {
		date_default_timezone_set( 'America/Los_Angeles' );
		$expire_date = time() - ( 60 * 60 * 24 * 3 );
		if ( $expire_date > strtotime( $mtaa_brand_settings['licenseDate'] ) ) { ?>
		<div class="slate-error">
			<h4><?php _e( 'It looks like you’re using', 'mtaa-brand' ); ?> Mtaa Branding <?php _e( 'without a license.', 'mtaa-brand' ); ?></h4>
			<p><?php _e( 'If you like', 'mtaa-brand' ); ?> Mtaa Branding <?php _e( 'please consider', 'mtaa-brand' ); ?> <a href="admin.php?page=mtaa_brand_license"><?php _e( 'entering a license key', 'mtaa-brand' ); ?></a> <?php _e( 'to help support us and continue its development. You’ll also receive free updates and technical support!', 'mtaa-brand' ); ?> <a href="http://mtaandao.co.ke/mtaandao/mtaa-brand/" target="_blank"><?php _e( 'Visit', 'mtaa-brand' ); ?> Seven Bold <?php _e( 'for purchasing information.', 'mtaa-brand' ); ?></a></p>
		</div>
		<?php }
	} ?>

	<?php if ( isset( $_GET['settings-updated'] ) || isset( $_GET['updated'] ) ) { ?>
		<div class="updated">
			<p><strong><?php _e( 'Settings saved.' ) ?></strong></p>
		</div>
		<div class="wrap"><h2 style="display:none;"></h2></div><!-- Mtaandao Hack to show Update Notice -->

	<?php } ?>
	<form method="post" action="<?php if ( is_multisite() && is_plugin_active_for_network( 'mtaa-branding/mtaa-branding.php' ) ) { ?>edit.php?action=mtaa_brand_network<?php } else { ?>options.php<?php } ?>">

		<?php if ( is_multisite() && is_plugin_active_for_network('mtaa-branding/mtaa-branding.php') ) { } else { settings_fields( 'mtaa_brand_settings' ); } ?>

		<div id="slate__colorSchemes" class="pageSection <?php if ( 'mtaa_brand_color_schemes' !== $page ) { echo 'hide'; } ?>">

			<h2><?php _e( 'Color Schemes', 'mtaa-brand' ); ?></h2>

			<section class="premadeColors">
				<div class="colorDefault">
					<label <?php if ( 'default' == $mtaa_brand_settings['colorScheme'] ) { ?> class="selected"<?php } ?>>
						<input type="radio" name="mtaa_brand_settings[colorScheme]" value="default" <?php if ( 'default' == $mtaa_brand_settings['colorScheme'] ) { ?> checked="checked"<?php } ?>> <?php _e( 'Default', 'mtaa-brand' ); ?>
						<div><span style="background:<?php echo mtaa_brand_sanitize_hex( $colorDefault['adminMenuBgColor'] ) ?>;"></span><span style="background: <?php echo mtaa_brand_sanitize_hex( $colorDefault['adminBarBgColor'] ) ?>;"></span><span style="background: <?php echo mtaa_brand_sanitize_hex( $colorDefault['adminTopLevelSelectedTextColor'] ) ?>;"></span><span style="background: <?php echo mtaa_brand_sanitize_hex( $colorDefault['adminTopLevelTextHoverColor'] ) ?>;"></span></div>
					</label>
				</div>
				<div class="colorLight">
					<label <?php if ( 'light' == $mtaa_brand_settings['colorScheme'] ) { ?> class="selected"<?php } ?>>
						<input type="radio" name="mtaa_brand_settings[colorScheme]" value="light" <?php if ( 'light' == $mtaa_brand_settings['colorScheme'] ) { ?> checked="checked"<?php } ?>> <?php _e( 'Light', 'mtaa-brand' ); ?>
						<div><span style="background:<?php echo mtaa_brand_sanitize_hex( $colorLight['adminMenuBgColor'] ) ?>;"></span><span style="background: <?php echo mtaa_brand_sanitize_hex( $colorLight['adminBarBgColor'] ) ?>;"></span><span style="background: <?php echo mtaa_brand_sanitize_hex( $colorLight['adminTopLevelSelectedTextColor'] ) ?>;"></span><span style="background: <?php echo mtaa_brand_sanitize_hex( $colorLight['adminTopLevelTextHoverColor'] ) ?>;"></span></div>
					</label>
				</div>
				<div class="colorBlue">
					<label <?php if ( 'blue' == $mtaa_brand_settings['colorScheme'] ) { ?> class="selected"<?php } ?>>
						<input type="radio" name="mtaa_brand_settings[colorScheme]" value="blue" <?php if ( 'blue' == $mtaa_brand_settings['colorScheme'] ) { ?> checked="checked"<?php } ?>> <?php _e( 'Blue', 'mtaa-brand' ); ?>
						<div><span style="background:<?php echo mtaa_brand_sanitize_hex( $colorBlue['adminMenuBgColor'] ) ?>;"></span><span style="background: <?php echo mtaa_brand_sanitize_hex( $colorBlue['adminBarBgColor'] ) ?>;"></span><span style="background: <?php echo mtaa_brand_sanitize_hex( $colorBlue['adminTopLevelSelectedTextColor'] ) ?>;"></span><span style="background: <?php echo mtaa_brand_sanitize_hex( $colorBlue['adminTopLevelTextHoverColor'] ) ?>;"></span></div>
					</label>
				</div>
				<div class="colorCoffee">
					<label <?php if ( 'coffee' == $mtaa_brand_settings['colorScheme'] ) { ?> class="selected"<?php } ?>>
						<input type="radio" name="mtaa_brand_settings[colorScheme]" value="coffee" <?php if ( 'coffee' == $mtaa_brand_settings['colorScheme'] ) { ?> checked="checked"<?php } ?>> <?php _e( 'Coffee', 'mtaa-brand' ); ?>
						<div><span style="background:<?php echo mtaa_brand_sanitize_hex( $colorCoffee['adminMenuBgColor'] ) ?>;"></span><span style="background: <?php echo mtaa_brand_sanitize_hex( $colorCoffee['adminBarBgColor'] ) ?>;"></span><span style="background: <?php echo mtaa_brand_sanitize_hex( $colorCoffee['adminTopLevelSelectedTextColor'] ) ?>;"></span><span style="background: <?php echo mtaa_brand_sanitize_hex( $colorCoffee['adminTopLevelTextHoverColor'] ) ?>;"></span></div>
					</label>
				</div>
				<div class="colorEctoplasm">
					<label <?php if ( 'ectoplasm' == $mtaa_brand_settings['colorScheme'] ) { ?> class="selected"<?php } ?>>
						<input type="radio" name="mtaa_brand_settings[colorScheme]" value="ectoplasm" <?php if ( 'ectoplasm' == $mtaa_brand_settings['colorScheme'] ) { ?> checked="checked"<?php } ?>> <?php _e( 'Ectoplasm', 'mtaa-brand' ); ?>
						<div><span style="background:<?php echo mtaa_brand_sanitize_hex( $colorEctoplasm['adminMenuBgColor'] ) ?>;"></span><span style="background: <?php echo mtaa_brand_sanitize_hex( $colorEctoplasm['adminBarBgColor'] ) ?>;"></span><span style="background: <?php echo mtaa_brand_sanitize_hex( $colorEctoplasm['adminTopLevelSelectedTextColor'] ) ?>;"></span><span style="background: <?php echo mtaa_brand_sanitize_hex( $colorEctoplasm['adminTopLevelTextHoverColor'] ) ?>;"></span></div>
					</label>
				</div>
				<div class="colorMidnight">
					<label <?php if ( 'midnight' == $mtaa_brand_settings['colorScheme'] ) { ?> class="selected"<?php } ?>>
						<input type="radio" name="mtaa_brand_settings[colorScheme]" value="midnight" <?php if ( 'midnight' == $mtaa_brand_settings['colorScheme'] ) { ?> checked="checked"<?php } ?>> <?php _e( 'Midnight', 'mtaa-brand' ); ?>
						<div><span style="background:<?php echo mtaa_brand_sanitize_hex( $colorMidnight['adminMenuBgColor'] ) ?>;"></span><span style="background: <?php echo mtaa_brand_sanitize_hex( $colorMidnight['adminBarBgColor'] ) ?>;"></span><span style="background: <?php echo mtaa_brand_sanitize_hex( $colorMidnight['adminTopLevelSelectedTextColor'] ) ?>;"></span><span style="background: <?php echo mtaa_brand_sanitize_hex( $colorMidnight['adminTopLevelTextHoverColor'] ) ?>;"></span></div>
					</label>
				</div>
				<div class="colorOcean">
					<label <?php if ( 'ocean' == $mtaa_brand_settings['colorScheme'] ) { ?> class="selected"<?php } ?>>
						<input type="radio" name="mtaa_brand_settings[colorScheme]" value="ocean" <?php if ( 'ocean' == $mtaa_brand_settings['colorScheme'] ) { ?> checked="checked"<?php } ?>> <?php _e( 'Ocean', 'mtaa-brand' ); ?>
						<div><span style="background:<?php echo mtaa_brand_sanitize_hex( $colorOcean['adminMenuBgColor'] ) ?>;"></span><span style="background: <?php echo mtaa_brand_sanitize_hex( $colorOcean['adminBarBgColor'] ) ?>;"></span><span style="background: <?php echo mtaa_brand_sanitize_hex( $colorOcean['adminTopLevelSelectedTextColor'] ) ?>;"></span><span style="background: <?php echo mtaa_brand_sanitize_hex( $colorOcean['adminTopLevelTextHoverColor'] ) ?>;"></span></div>
					</label>
				</div>
				<div class="colorSunrise">
					<label <?php if ( 'sunrise' == $mtaa_brand_settings['colorScheme'] ) { ?> class="selected"<?php } ?>>
						<input type="radio" name="mtaa_brand_settings[colorScheme]" value="sunrise" <?php if ( 'sunrise' == $mtaa_brand_settings['colorScheme'] ) { ?> checked="checked"<?php } ?>> <?php _e( 'Sunrise', 'mtaa-brand' ); ?>
						<div><span style="background:<?php echo mtaa_brand_sanitize_hex( $colorSunrise['adminMenuBgColor'] ) ?>;"></span><span style="background: <?php echo mtaa_brand_sanitize_hex( $colorSunrise['adminBarBgColor'] ) ?>;"></span><span style="background: <?php echo mtaa_brand_sanitize_hex( $colorSunrise['adminTopLevelSelectedTextColor'] ) ?>;"></span><span style="background: <?php echo mtaa_brand_sanitize_hex( $colorSunrise['adminTopLevelTextHoverColor'] ) ?>;"></span></div>
					</label>
				</div>
				<div class="colorCustom">
					<label <?php if ( 'custom' == $mtaa_brand_settings['colorScheme'] ) { ?> class="selected"<?php } ?>>
						<input type="radio" name="mtaa_brand_settings[colorScheme]" value="custom" <?php if ( 'custom' == $mtaa_brand_settings['colorScheme'] ) { ?> checked="checked"<?php } ?>> <?php _e( 'Custom', 'mtaa-brand' ); ?>
						<div><span style="background:<?php echo mtaa_brand_sanitize_hex( $colorCustom['adminMenuBgColor'] ) ?>;"></span><span style="background: <?php echo mtaa_brand_sanitize_hex( $colorCustom['adminBarBgColor'] ) ?>;"></span><span style="background: <?php echo mtaa_brand_sanitize_hex( $colorCustom['adminTopLevelSelectedTextColor'] ) ?>;"></span><span style="background: <?php echo mtaa_brand_sanitize_hex( $colorCustom['adminTopLevelTextHoverColor'] ) ?>;"></span></div>
					</label>
				</div>
			</section>

			<!-- Color Nav -->
			<div class="colorNav">
				<h3><?php _e( 'Custom Color Options', 'mtaa-brand' ); ?></h3>
				<ul>
					<li class="loginPageColors"><a class="nav-tab selected" href="#"><?php _e( 'Login Page', 'mtaa-brand' ); ?></a></li>
					<li class="adminMenuColors"><a class="nav-tab" href="#"><?php _e( 'Admin Menu', 'mtaa-brand' ); ?></a></li>
					<li class="adminBarColors"><a class="nav-tab" href="#"><?php _e( 'Admin Bar', 'mtaa-brand' ); ?></a></li>
					<li class="adminFooterColors"><a class="nav-tab" href="#"><?php _e( 'Admin Footer', 'mtaa-brand' ); ?></a></li>
					<li class="adminContentColors"><a class="nav-tab" href="#"><?php _e( 'Content', 'mtaa-brand' ); ?></a></li>
					<li class="adminSidebarColors"><a class="nav-tab" href="#"><?php _e( 'Sidebar/Sortables', 'mtaa-brand' ); ?></a></li>
				</ul>
			</div>
			<!-- Login Page -->
			<section class="colorSection loginPageColors" <?php if ( 'custom' == $mtaa_brand_settings['colorScheme'] ) { ?> style="display: block;"<?php } ?>>
				<?php colorSection( $colorSectionLoginPage, $colorCustom ) ?>
			</section>

			<!-- Admin Menu -->
			<section class="colorSection adminMenuColors">
				<?php colorSection( $colorSectionAdminMenu, $colorCustom ) ?>
			</section>

			<!-- Admin Bar -->
			<section class="colorSection adminBarColors">
				<?php colorSection( $colorSectionAdminBar, $colorCustom ) ?>
			</section>

			<!-- Footer -->
			<section class="colorSection adminFooterColors">
				<?php colorSection( $colorSectionAdminFooter, $colorCustom ) ?>
			</section>

			<!-- Content -->
			<section class="colorSection adminContentColors">
				<?php colorSection( $colorSectionContent, $colorCustom ) ?>
			</section>

			<!-- Sidebar -->
			<section class="colorSection adminSidebarColors">
				<?php colorSection( $colorSectionSidebar, $colorCustom ) ?>
			</section>

			<section>
				<ul>
					<li>
						<label>
							<input name="mtaa_brand_settings[colorsHideUserProfileColors]" type="checkbox" <?php checked( esc_attr( $mtaa_brand_settings['colorsHideUserProfileColors'] ), 'on' ); ?>>
							<?php _e( 'Hide “Admin Color Scheme” Options on User Profile Pages', 'mtaa-brand' ); ?>
						</label>
					</li>
					<li>
						<label>
							<input name="mtaa_brand_settings[colorsHideShadows]" type="checkbox" <?php checked( esc_attr( $mtaa_brand_settings['colorsHideShadows'] ), 'on' ); ?>>
							<?php _e( 'Hide Admin Menu and Sidebar Shadows', 'mtaa-brand' ); ?>
						</label>
					</li>
				</ul>
			</section>

			<?php submit_button(); ?>

		</div>

		<div id="slate__branding" class="pageSection <?php if ( 'mtaa_brand_branding' !== $page ) { echo 'hide'; } ?>">
			<h2><?php _e( 'Branding', 'mtaa-brand' ); ?></h2>

			<section>
				<h3><?php _e( 'Login Page Logo Link Title and Address', 'mtaa-brand' ); ?></h3>
				<ul>
					<li>
						<label><?php _e( 'Link Title', 'mtaa-brand' ); ?> <input type="text" name="mtaa_brand_settings[loginLinkTitle]" value="<?php echo esc_attr( $loginLinkTitle ); ?>"></label>
					</li>
					<li>
						<label><?php _e( 'Link Address', 'mtaa-brand' ); ?> <input type="text" name="mtaa_brand_settings[loginLinkUrl]" value="<?php echo esc_attr( $loginLinkUrl ); ?>"></label>
					</li>
				</ul>
			</section>

			<section>
				<h3><?php _e( 'Login Page Logo', 'mtaa-brand' ); ?></h3>
				<ul>
					<li>
						<div id="slate__loginLogoImage" class="imageContainer">
							<?php echo mn_kses_post( $loginLogo ); ?>
						</div>
						<input type="text" class="imageValue" id="slate__loginLogo" name="mtaa_brand_settings[loginLogo]" value="<?php echo esc_url( $mtaa_brand_settings['loginLogo'] ); ?>" placeholder="Image Address" />
					</li>
					<li class="slate__selectLoginLogo">
						<a href="#" class="button imageSelect"><?php _e( 'Select Image', 'mtaa-brand' ); ?></a>
						<a href="#" class="imageDelete" <?php echo mn_kses_post( $loginLogoDelete ); ?>><?php _e( 'Delete Image', 'mtaa-brand' ); ?></a>
					</li>
					<li class="slate__description">
						<?php _e( 'Make sure the image is no greater than 320 pixels wide by 80 pixels high.', 'mtaa-brand' ); ?>
					</li>
					<li>
						<label>
							<input name="mtaa_brand_settings[loginLogoHide]" type="checkbox" <?php checked( esc_attr( $mtaa_brand_settings['loginLogoHide'] ), 'on' ); ?>>
							<?php _e( 'Hide the Login Logo', 'mtaa-brand' ); ?>
						</label>
					</li>
				</ul>
			</section>

			<section>
				<h3><?php _e( 'Login Page Background Image', 'mtaa-brand' ); ?></h3>
				<ul>
					<li>
						<div id="slate__loginBgImage" class="imageContainer">
							<?php echo mn_kses_post( $loginBgImage ); ?>
						</div>
						<input type="text" class="imageValue" id="slate__loginBg" name="mtaa_brand_settings[loginBgImage]" value="<?php echo esc_url( $mtaa_brand_settings['loginBgImage'] ); ?>" placeholder="Image Address" />
					</li>
					<li class="slate__selectLoginBg">
						<a href="#" class="button imageSelect"><?php _e( 'Select Image', 'mtaa-brand' ); ?></a>
						<a href="#" class="imageDelete" <?php echo mn_kses_post( $loginBgImageDelete ); ?>><?php _e( 'Delete Image', 'mtaa-brand' ); ?></a>
					</li>
					<li>
						<!-- <p class="slate__description"><?php _e( 'Your logo should be no larger than 320px by 80px or else it will be resized on the login screen.', 'mtaa-brand' ); ?></p> -->
					</li>
					<li>
						<label>
							<select name="mtaa_brand_settings[loginBgPosition]">
								<?php
								$lbp = array(
									'left top' => 'Left Top',
									'left center' => 'Left Center',
									'left bottom' => 'Left Bottom',
									'center top' => 'Center Top',
									'center center' => 'Center Center',
									'center bottom' => 'Center Bottom',
									'right top' => 'Right Top',
									'right center' => 'Right Center',
									'right bottom' => 'Right Bottom',
									);
								foreach ( $lbp as $key => $value ) {
									?>
									<option value="<?php echo esc_attr( $key ); ?>"<?php if ( ( $loginBgPosition ) == $key ) { ?> selected="selected"<?php } ?>><?php echo esc_attr( $value ); ?></option>
									<?php
								}
								?>
							</select>
							<?php _e( 'Background Position', 'mtaa-brand' ); ?>
						</label>
					</li>
					<li>
						<label>
							<select name="mtaa_brand_settings[loginBgRepeat]">
								<?php
								$lbr = array(
									'no-repeat' => 'No Repeat',
									'repeat' => 'Repeat',
									'repeat-x' => 'Repeat Only Horizontally',
									'repeat-y' => 'Repeat Only Vertically',
									);
								foreach ( $lbr as $key => $value ) {
									?>
									<option value="<?php echo esc_attr( $key ); ?>"<?php if ( $loginBgRepeat == $key ) { ?> selected="selected"<?php } ?>><?php echo esc_attr( $value ); ?></option>
									<?php
								}
								?>
							</select>
							<?php _e( 'Background Repeat', 'mtaa-brand' ); ?>
						</label>
					</li>
					<li>
						<label>
							<input name="mtaa_brand_settings[loginBgFull]" type="checkbox" <?php checked( esc_attr( $mtaa_brand_settings['loginBgFull'] ), 'on' ); ?>>
							<?php _e( 'Make the Background Image Fill the Page', 'mtaa-brand' ); ?>
						</label>
					</li>
				</ul>
			</section>

			<section>
				<h3><?php _e( 'Full Width Menu Logo', 'mtaa-brand' ); ?></h3>
				<ul>
					<li>
						<div id="slate__adminLogoImage" class="imageContainer">
							<?php echo mn_kses_post( $adminLogo ); ?>
						</div>
						<input type="text" class="imageValue" id="slate__adminLogo" name="mtaa_brand_settings[adminLogo]" value="<?php echo esc_url( $mtaa_brand_settings['adminLogo'] ); ?>" placeholder="Image Address" />
					</li>
					<li class="slate__selectAdminLogo">
						<a href="#" class="button imageSelect"><?php _e( 'Select Image', 'mtaa-brand' ); ?></a>
						<a href="#" class="imageDelete" <?php echo mn_kses_post( $adminLogoDelete ); ?>><?php _e( 'Delete Image', 'mtaa-brand' ); ?></a>
					</li>
					<li class="slate__description">
						<?php _e( 'Make sure the image is no wider than 200 pixels. Double it for high resolution.', 'mtaa-brand' ); ?>
					</li>
				</ul>
			</section>

			<section>
				<h3><?php _e( 'Collapsed Menu Logo', 'mtaa-brand' ); ?></h3>
				<ul>
					<li>
						<div id="slate__adminLogoFoldedImage" class="imageContainer">
							<?php echo mn_kses_post( $adminLogoFolded ); ?>
						</div>
						<input type="text" class="imageValue" id="slate__adminLogoFolded" name="mtaa_brand_settings[adminLogoFolded]" value="<?php echo esc_url( $mtaa_brand_settings['adminLogoFolded'] ); ?>" placeholder="Image Address" />
					</li>
					<li class="slate__selectAdminLogoFolded">
						<a href="#" class="button imageSelect"><?php _e( 'Select Image', 'mtaa-brand' ); ?></a>
						<a href="#" class="imageDelete" <?php echo mn_kses_post( $adminLogoFoldedDelete ); ?>><?php _e( 'Delete Image', 'mtaa-brand' ); ?></a>
					</li>
					<li class="slate__description">
						<?php _e( 'Make sure the image is no wider than 36 pixels. Double it for high resolution.', 'mtaa-brand' ); ?>
					</li>
				</ul>
			</section>

			<section>
				<h3><?php _e( 'Favicon', 'mtaa-brand' ); ?></h3>
				<ul>
					<li>
						<div id="slate__adminFaviconImage" class="imageContainer">
							<?php echo mn_kses_post( $adminFavicon ); ?>
						</div>
						<input type="hidden" class="imageValue" id="slate__adminFavicon" name="mtaa_brand_settings[adminFavicon]" value="<?php echo esc_url( $mtaa_brand_settings['adminFavicon'] ); ?>" placeholder="Image Address" />
					</li>
					<li class="slate__selectAdminFavicon">
						<a href="#" class="button imageSelect"><?php _e( 'Select Image', 'mtaa-brand' ); ?></a>
						<a href="#" class="imageDelete" <?php echo mn_kses_post( $adminFaviconDelete ); ?>><?php _e( 'Delete Image', 'mtaa-brand' ); ?></a>
					</li>
					<li class="slate__description">
						<?php _e( 'Make sure the image exactly 16 pixels high and 16 pixels wide.', 'mtaa-brand' ); ?>
					</li>
				</ul>
			</section>
			<?php submit_button(); ?>

		</div>

		<div id="slate__dashboard" class="pageSection <?php if ( 'mtaa_brand_dashboard' !== $page ) { echo 'hide'; } ?>">

			<h2><?php _e( 'Dashboard', 'mtaa-brand' ); ?></h2>

			<section>
				<h3><?php _e( 'Welcome Message', 'mtaa-brand' ); ?></h3>
				<ul>
					<li>
						<label>
							<input name="mtaa_brand_settings[dashboardHideWelcome]" type="checkbox" <?php checked( esc_attr( $mtaa_brand_settings['dashboardHideWelcome'] ), 'on' ); ?>>
							<?php _e( 'Hide the Dashboard Welcome Message', 'mtaa-brand' ); ?>
						</label>
					</li>
				</ul>
			</section>

			<section>
				<h3><?php _e( 'Custom Widget', 'mtaa-brand' ); ?></h3>
				<ul>
					<li>
						<label>
							<input name="mtaa_brand_settings[dashboardCustomWidget]" type="checkbox" <?php checked( esc_attr( $mtaa_brand_settings['dashboardCustomWidget'] ), 'on' ); ?>>
							<?php _e( 'Show a Custom Widget on the Dashboard', 'mtaa-brand' ); ?>
						</label>
					</li>
					<li>
						<label><?php _e( 'Widget Title', 'mtaa-brand' ); ?> <input type="text" name="mtaa_brand_settings[dashboardCustomWidgetTitle]" value="<?php echo esc_attr( $dashboardCustomWidgetTitle ); ?>"></label>
					</li>
					<li>
						<label><?php _e( 'Widget Content (HTML Allowed)', 'mtaa-brand' ); ?>
							<textarea name="mtaa_brand_settings[dashboardCustomWidgetText]"><?php echo mn_kses_post( force_balance_tags( $dashboardCustomWidgetText ) ); ?></textarea>
						</label>
					</li>
				</ul>
			</section>

			<section>
				<h3><?php _e( 'Hide Widgets', 'mtaa-brand' ); ?> <span class="slate__select"><a href="#" class="slate__selectAll"><?php _e( 'Select All', 'mtaa-brand' ); ?></a> / <a href="#" class="slate__selectNone"><?php _e( 'Select None', 'mtaa-brand' ); ?></a></span></h3>
				<ul>
					<li>
						<label>
							<input type="hidden" name="mtaa_brand_settings[dashboardWidgets][dashboardHideActivity]" value="0">
							<input name="mtaa_brand_settings[dashboardWidgets][dashboardHideActivity]" type="checkbox" <?php checked( esc_attr( $mtaa_brand_settings['dashboardWidgets']['dashboardHideActivity'] ), 'on' ); ?>>
							<?php _e( 'Activity', 'mtaa-brand' ); ?>
						</label>
					</li>
					<li>
						<label>
							<input type="hidden" name="mtaa_brand_settings[dashboardWidgets][dashboardHideNews]" value="0">
							<input name="mtaa_brand_settings[dashboardWidgets][dashboardHideNews]" type="checkbox" <?php checked( esc_attr( $mtaa_brand_settings['dashboardWidgets']['dashboardHideNews'] ), 'on' ); ?>>
							<?php _e( 'Mtaandao News', 'mtaa-brand' ); ?>
						</label>
					</li>
					<li>
						<label>
							<input type="hidden" name="mtaa_brand_settings[dashboardWidgets][dashboardRightNow]" value="0">
							<input name="mtaa_brand_settings[dashboardWidgets][dashboardRightNow]" type="checkbox" <?php checked( esc_attr( $mtaa_brand_settings['dashboardWidgets']['dashboardRightNow'] ), 'on' ); ?>>
							<?php _e( 'At a Glance', 'mtaa-brand' ); ?>
						</label>
					</li>
					<li>
						<label>
							<input type="hidden" name="mtaa_brand_settings[dashboardWidgets][dashboardRecentComments]" value="0">
							<input name="mtaa_brand_settings[dashboardWidgets][dashboardRecentComments]" type="checkbox" <?php checked( esc_attr( $mtaa_brand_settings['dashboardWidgets']['dashboardRecentComments'] ), 'on' ); ?>>
							<?php _e( 'Recent Comments', 'mtaa-brand' ); ?>
						</label>
					</li>
					<li>
						<label>
							<input type="hidden" name="mtaa_brand_settings[dashboardWidgets][dashboardQuickPress]" value="0">
							<input name="mtaa_brand_settings[dashboardWidgets][dashboardQuickPress]" type="checkbox" <?php checked( esc_attr( $mtaa_brand_settings['dashboardWidgets']['dashboardQuickPress'] ), 'on' ); ?>>
							<?php _e( 'Quick Press', 'mtaa-brand' ); ?>
						</label>
					</li>
					<li>
						<label>
							<input type="hidden" name="mtaa_brand_settings[dashboardWidgets][dashboardRecentDrafts]" value="0">
							<input name="mtaa_brand_settings[dashboardWidgets][dashboardRecentDrafts]" type="checkbox" <?php checked( esc_attr( $mtaa_brand_settings['dashboardWidgets']['dashboardRecentDrafts'] ), 'on' ); ?>>
							<?php _e( 'Recent Drafts', 'mtaa-brand' ); ?>
						</label>
					</li>
					<li>
						<label>
							<input type="hidden" name="mtaa_brand_settings[dashboardWidgets][dashboardIncomingLinks]" value="0">
							<input name="mtaa_brand_settings[dashboardWidgets][dashboardIncomingLinks]" type="checkbox" <?php checked( esc_attr( $mtaa_brand_settings['dashboardWidgets']['dashboardIncomingLinks'] ), 'on' ); ?>>
							<?php _e( 'Incoming Links', 'mtaa-brand' ); ?>
						</label>
					</li>
					<li>
						<label>
							<input type="hidden" name="mtaa_brand_settings[dashboardWidgets][dashboardPlugins]" value="0">
							<input name="mtaa_brand_settings[dashboardWidgets][dashboardPlugins]" type="checkbox" <?php checked( esc_attr( $mtaa_brand_settings['dashboardWidgets']['dashboardPlugins'] ), 'on' ); ?>>
							<?php _e( 'Plugins', 'mtaa-brand' ); ?>
						</label>
					</li>
				</ul>
			</section>

			<?php submit_button(); ?>

		</div>

		<div id="slate__adminMenu" class="pageSection <?php if ( 'mtaa_brand_admin_menu' !== $page ) { echo 'hide'; } ?>">

			<h2><?php _e( 'Admin Menu', 'mtaa-brand' ); ?></h2>

			<section>
				<h3><?php _e( 'Hide the following Menu Items', 'mtaa-brand' ); ?> <span class="slate__select"><a href="#" class="slate__selectAll"><?php _e( 'Select All', 'mtaa-brand' ); ?></a> / <a href="#" class="slate__selectNone"><?php _e( 'Select None', 'mtaa-brand' ); ?></a></span></h3>

				<ul>
					<?php

					$theMenu = mtaa_brand_admin_menus();

					if ( isset( $mtaa_brand_settings['adminMenu'] ) && '' !== $mtaa_brand_settings['adminMenu'] ) {
						foreach ( $mtaa_brand_settings['adminMenu'] as $menuItem => $menuHide ) {

							$menuItem = unserialize( base64_decode( $menuItem ) );

							if ( 'on' == $menuHide ) {
								$savedMenu[] = array(
									'Sort' => $menuItem['Sort'],
									'Title' => $menuItem['Title'],
									'Slug' => $menuItem['Slug'],
									);
							}
						}
					}
					if ( ! isset( $savedMenu ) ) {
						$savedMenu = array();
					}

					foreach ( $mtaa_brand_settings['adminMenuPermissions'] as $userName => $userHide ) {
						if ( 'on' == $userHide ) {
							$adminMenuActive = true;
						}
					}

					if ( isset( $adminMenuActive ) && true == $adminMenuActive ) {
						$theMenu = array_merge( $theMenu, $savedMenu );

						function compare_sort( $a, $b ) {
							if ( $a['Sort'] == $b['Sort'] ) {
								return 0;
							}

							return ( $a['Sort'] < $b['Sort'] ) ? - 1 : 1;
						}

						usort( $theMenu, 'compare_sort' );
					}

					foreach ( $theMenu as $key => $menuItem ) {
						$theMenuItem = base64_encode( serialize( array(
							'Sort' => esc_attr( $menuItem['Sort'] ),
							'Title' => esc_attr( $menuItem['Title'] ),
							'Slug' => esc_attr( $menuItem['Slug'] )
							) ) ); ?>
						<li>
							<label>
								<input name="mtaa_brand_settings[adminMenu][<?php echo $theMenuItem; ?>]" type="checkbox" <?php if ( isset( $mtaa_brand_settings['adminMenu'][ $theMenuItem ] ) ) { checked( esc_attr( $mtaa_brand_settings['adminMenu'][ $theMenuItem ] ), 'on' ); } ?>> <?php echo esc_attr( $menuItem['Title'] ); ?>
							</label>
						</li> <?php
					} ?>
				</ul>

			</section>

			<section>
				<h3><?php _e( 'Apply to the following Users', 'mtaa-brand' ); ?> <span class="slate__select"><a href="#" class="slate__selectAll"><?php _e( 'Select All', 'mtaa-brand' ); ?></a> / <a href="#" class="slate__selectNone"><?php _e( 'Select None', 'mtaa-brand' ); ?></a></span></h3>
				<?php $users = get_users();
				if ( ! ( $users[0] instanceof MN_User) ) {
					return;
				} ?>
				<ul>
					<?php foreach ( $users as $key => $value ) {
						$user_role = $users[$key]->roles;
						$user_id = $users[$key]->ID;
						$username = $users[$key]->user_login;
						$user_first_name = $users[$key]->first_name;
						$user_last_name = $users[$key]->last_name;
						if ( user_can( $user_id, 'edit_posts' ) ) { ?>
						<li>
							<label>
								<input type="hidden" name="mtaa_brand_settings[adminMenuPermissions][<?php echo $username ?>]" value="0">
								<input name="mtaa_brand_settings[adminMenuPermissions][<?php echo $username ?>]" type="checkbox" <?php if ( isset($mtaa_brand_settings['adminMenuPermissions'][ $username ] ) ) { checked( esc_attr( $mtaa_brand_settings['adminMenuPermissions'][ $username ] ), 'on' ); } ?>> <?php echo $user_first_name ?> <?php echo $user_last_name ?> <?php if ( !empty( $user_first_name ) || !empty( $user_last_name ) ) { ?>(<?php } ?><?php echo $username ?><?php if ( !empty( $user_first_name ) || !empty( $user_last_name ) ) { ?>)<?php } ?>
							</label>
						</li> <?php	}
					} ?>
				</ul>
			</section>

			<?php submit_button(); ?>

		</div>

		<div id="slate__adminBar" class="pageSection <?php if ( $page !== 'mtaa_brand_admin_bar_footer' ) { echo 'hide'; } ?>">

			<h2><?php _e( 'Admin Bar &amp; Footer', 'mtaa-brand' ); ?></h2>

			<section>
				<h3><?php _e( 'Admin Bar', 'mtaa-brand' ); ?></h3>
				<ul>
					<li>
						<label>
							<input name="mtaa_brand_settings[adminBarHideMN]" type="checkbox" <?php checked( esc_attr( $mtaa_brand_settings['adminBarHideMN'] ), 'on' ); ?>>
							<?php _e( 'Hide the Mtaandao Logo', 'mtaa-brand' ); ?>
						</label>
					</li>
					<li>
						<label>
							<input name="mtaa_brand_settings[adminBarHide]" type="checkbox" <?php checked( esc_attr( $mtaa_brand_settings['adminBarHide'] ), 'on' ); ?>>
							<?php _e( 'Hide the Admin Bar', 'mtaa-brand' ); ?>
						</label>
					</li>
				</ul>
			</section>

			<section>
				<h3><?php _e( 'Admin Footer', 'mtaa-brand' ); ?></h3>
				<ul>
					<li>
						<label>
							<input name="mtaa_brand_settings[footerTextShow]" type="checkbox" <?php checked( esc_attr( $mtaa_brand_settings['footerTextShow'] ), 'on' ); ?>>
							<?php _e( 'Display Custom Footer Text', 'mtaa-brand' ); ?>
						</label>
					</li>
					<li>
						<label><?php _e( 'Footer Text (HTML Allowed)', 'mtaa-brand' ); ?>
							<textarea class="customFooterText" name="mtaa_brand_settings[footerText]"><?php echo mn_kses_post( force_balance_tags( $footerText ) ); ?></textarea>
						</label>
					</li>
					<li>
						<label>
							<input name="mtaa_brand_settings[footerVersionHide]" type="checkbox" <?php checked( esc_attr( $mtaa_brand_settings['footerVersionHide'] ), 'on' ); ?>>
							<?php _e( 'Hide Version Number', 'mtaa-brand' ); ?>
						</label>
					</li>
					<li>
						<label>
							<input name="mtaa_brand_settings[footerHide]" type="checkbox" <?php checked( esc_attr( $mtaa_brand_settings['footerHide'] ), 'on' ); ?>>
							<?php _e( 'Hide the Admin Footer', 'mtaa-brand' ); ?>
						</label>
					</li>
				</ul>
			</section>

			<?php submit_button(); ?>

		</div>

		<div id="slate__contentNotices" class="pageSection <?php if ( $page !== 'mtaa_brand_content_notices' ) { echo 'hide'; } ?>">

			<h2><?php _e( 'Content &amp; Notices', 'mtaa-brand' ); ?></h2>

			<section>
				<h3><?php _e( 'Title', 'mtaa-brand' ); ?></h3>
				<ul>
					<li>
						<label>
							<input name="mtaa_brand_settings[contentHideMNTitle]" type="checkbox" <?php checked( esc_attr( $mtaa_brand_settings['contentHideMNTitle'] ), 'on' ); ?>>
							<?php _e( 'Hide "Mtaandao" in Page Titles', 'mtaa-brand' ); ?>
						</label>
					</li>
				</ul>
			</section>

			<section>
				<h3><?php _e( 'Tabs', 'mtaa-brand' ); ?></h3>
				<ul>
					<li>
						<label>
							<input name="mtaa_brand_settings[contentHideHelp]" type="checkbox" <?php checked( esc_attr( $mtaa_brand_settings['contentHideHelp'] ), 'on' ); ?>>
							<?php _e( 'Hide the Help Tab', 'mtaa-brand' ); ?>
						</label>
					</li>
					<li>
						<label>
							<input name="mtaa_brand_settings[contentHideScreenOptions]" type="checkbox" <?php checked( esc_attr( $mtaa_brand_settings['contentHideScreenOptions'] ), 'on' ); ?>>
							<?php _e( 'Hide the Screen Options Tab', 'mtaa-brand' ); ?>
						</label>
					</li>
				</ul>
			</section>

			<section>
				<h3><?php _e( 'Disable Notices', 'mtaa-brand' ); ?></h3>
				<p><?php _e( 'Depending on the number of themes and plugins you have installed, the options below may slow down the Mtaandao admin. If you’re concerned about speed, see the “Hide Notices” option below.', 'mtaa-brand' ); ?></p>
				<ul>
					<li>
						<label>
							<input name="mtaa_brand_settings[noticeMNUpdate]" type="checkbox" <?php checked( esc_attr( $mtaa_brand_settings['noticeMNUpdate'] ), 'on' ); ?>>
							<?php _e( 'Disable Mtaandao Core Update Notices', 'mtaa-brand' ); ?>
						</label>
					</li>
					<li>
						<label>
							<input name="mtaa_brand_settings[noticeThemeUpdate]" type="checkbox" <?php checked( esc_attr( $mtaa_brand_settings['noticeThemeUpdate'] ), 'on' ); ?>>
							<?php _e( 'Disable Mtaandao Theme Update Notices', 'mtaa-brand' ); ?>
						</label>
					</li>
					<li>
						<label>
							<input name="mtaa_brand_settings[noticePluginUpdate]" type="checkbox" <?php checked( esc_attr( $mtaa_brand_settings['noticePluginUpdate'] ), 'on' ); ?>>
							<?php _e( 'Disable Mtaandao Plugin Update Notices', 'mtaa-brand' ); ?>
						</label>
					</li>
				</ul>
			</section>

			<section>
				<h3><?php _e( 'Hide Notices', 'mtaa-brand' ); ?></h3>
				<p><?php _e( 'This is an alternative to completely disabling the notices. This option won’t slow down the admin, but if the user has access to the following pages, they may still see updates are available: /admin/update-core.php, /admin/themes.php, and /admin/plugins.php.', 'mtaa-brand' ); ?></p>
				<ul>
					<li>
						<label>
							<input name="mtaa_brand_settings[noticeHideAllUpdates]" type="checkbox" <?php checked( esc_attr( $mtaa_brand_settings['noticeHideAllUpdates'] ), 'on' ); ?>>
							<?php _e( 'Hide All Mtaandao Update Notices', 'mtaa-brand' ); ?>
						</label>
					</li>
				</ul>
			</section>

			<?php submit_button(); ?>

		</div>

		<div id="slate__permissions" class="pageSection <?php if ( $page !== 'mtaa_brand_permissions' ) { echo 'hide'; } ?>">

			<h2><?php _e( 'Permissions', 'mtaa-brand' ); ?></h2>
			<section>
				<p><?php _e( 'Below you can choose which users will see the', 'mtaa-brand' ); ?> Mtaa Branding <?php _e( 'plugin. Keep in mind that if you', 'mtaa-brand' ); ?> <span style='color: #c00;'><?php _e( 'check your own name, you’ll no longer be able to access these settings', 'mtaa-brand' ); ?></span>.<?php _e( 'If that happens, you’ll need to', 'mtaa-brand' ); ?> <a href="admin.php?page=mtaa_brand_permissions"><?php _e( 'bookmark this page', 'mtaa-brand' ); ?></a> <?php _e( 'or deactivate and reactivate the plugin. Make sure to keep an up-to-date', 'mtaa-brand' ); ?> <a href="admin.php?page=mtaa_brand_import_export"><?php _e( 'backup of your settings', 'mtaa-brand' ); ?></a>. <?php _e( 'Only users who already have permission to access plugins are shown below.', 'mtaa-brand' ); ?></p>
			</section>
			<?php
			$users = get_users();
			if ( !($users[0] instanceof MN_User) ) {
				return;
			} ?>
			<section>
				<h3><?php _e( 'Hide', 'mtaa-brand' ); ?> Mtaa Branding <?php _e( 'from the following Users', 'mtaa-brand' ); ?></h3>
				<ul>
					<?php foreach ($users as $key => $value) {
						$user_role = $users[$key]->roles;
						$user_id = $users[$key]->ID;
						$username = $users[$key]->user_login;
						$user_first_name = $users[$key]->first_name;
						$user_last_name = $users[$key]->last_name;
						if ( user_can( $user_id, 'activate_plugins' ) ) { ?>
						<li>
							<label>
								<input type="hidden" name="mtaa_brand_settings[userPermissions][<?php echo $username ?>]" value="0">
								<input name="mtaa_brand_settings[userPermissions][<?php echo $username ?>]" type="checkbox" <?php if ( isset($mtaa_brand_settings['userPermissions'][$username] ) ) { checked( esc_attr( $mtaa_brand_settings['userPermissions'][$username] ), 'on' ); } ?>> <?php echo $user_first_name ?> <?php echo $user_last_name ?> <?php if ( !empty( $user_first_name ) || !empty( $user_last_name ) ) { ?>(<?php } ?><?php echo $username ?><?php if ( !empty( $user_first_name ) || !empty( $user_last_name ) ) { ?>)<?php } ?>
							</label>
						</li>
						<?php	}
					} ?>
				</ul>
			</section>

			<?php submit_button(); ?>

		</div>

		<div id="slate__settings" class="pageSection <?php if ( $page !== 'mtaa_brand_settings' ) { echo 'hide'; } ?>">

			<h2><?php _e( 'Settings', 'mtaa-brand' ); ?></h2>

			<section>
				<h3><?php _e( 'Custom Login Address', 'mtaa-brand' ); ?></h3>

				<p><?php _e( 'If you use a third party plugin that changes the Mtaandao Login page from /login.php to something else, you may need to enter your custom login page address below so that Mtaa Branding can work on the Login page.', 'mtaa-brand' ); ?></p>

				<p><?php _e( 'The Login Page Address should be what comes after your domain name. If your login page was at http://yourdomain.com/mycustomlogin, you would enter "/mycustomlogin". If it was at http://yourdomain.com/subdirectory/mycustomlogin, you would enter "/subdirectory/mycustomLogincustomlogin".', 'mtaa-brand' ); ?></p>

				<ul>
					<li>
						<label>
							<input name="mtaa_brand_settings[customLogin]" type="checkbox" <?php checked( esc_attr( $mtaa_brand_settings['customLogin'] ), 'on' ); ?>>
							<?php _e( 'Enable Mtaa Branding on a Custom Login Page', 'mtaa-brand' ); ?>
						</label>
					</li>
					<li>
						<label><?php _e( 'Login Page Address', 'mtaa-brand' ); ?> <input type="text" name="mtaa_brand_settings[customLoginURL]" value="<?php echo esc_attr( $customLoginURL ); ?>"></label>
					</li>
				</ul>
			</section>

			<?php submit_button(); ?>

		</div>

		<!-- Misc Hidden Inputs -->
		<input type="hidden" name="mtaa_brand_settings[licenseDate]" value="<?php echo esc_attr( $mtaa_brand_settings['licenseDate'] ); ?>" />
		<input type="hidden" name="mtaa_brand_settings[currentPage]" value="<?php echo $page; ?>" />

	</form>

	<div id="slate__importExport" class="pageSection <?php if ( $page !== 'mtaa_brand_import_export' ) { echo 'hide'; } ?>">
		<form action="" method="post">

			<h2><?php _e( 'Import / Export', 'mtaa-brand' ); ?></h2>

			<section>
				<h3><?php _e( 'Import', 'mtaa-brand' ); ?></h3>
				<?php
				global $mtaa_brand_import_success;
				if ( isset( $mtaa_brand_import_success ) && true == $mtaa_brand_import_success ) { ?>
				<!-- <script type="text/javascript">location.reload();</script> -->
				<div class="importSuccess"><?php _e( 'The Import was Successful!', 'mtaa-brand' ); ?></div>
				<?php } else if ( isset( $mtaa_brand_import_success ) && false == $mtaa_brand_import_success ) { ?>
				<div class="importFail"><?php _e( 'Oops, the import didn’t work.', 'mtaa-brand' ); ?></div>
				<?php } ?>
				<textarea class="slateProImportExport" name="mtaa_brand_import_settings"></textarea>
				<p class="slate__description">
					<?php _e( 'Paste your settings above and click “Save Changes” to import. It should look like the text in the Export field below.', 'mtaa-brand' ); ?>
				</p>

				<input type="submit" name="mtaa_brand_import" class="button button-primary" value="Import Settings">
			</section>


			<section>
				<h3><?php _e( 'Export', 'mtaa-brand' ); ?></h3>
				<textarea class="slateProImportExport"><?php echo serialize($mtaa_brand_settings); ?></textarea>
				<p class="slate__description">
					<?php _e( 'Copy and save the text above to backup your', 'mtaa-brand' ); ?> Mtaa Branding <?php _e( 'settings.', 'mtaa-brand' ); ?>
				</p>
			</section>


		</form>
	</div>

	<div id="slate__about" class="pageSection <?php if ( $page !== 'mtaa_brand_about' ) { echo 'hide'; } ?>">

		<h2><?php _e( 'About', 'mtaa-brand' ); ?> Slate Pro</h2>
		<section>
			<p><?php _e( 'If you need product support, please leave a comment on the', 'mtaa-brand' ); ?> <a href="http://codecanyon.net/item/mtaa-brand-a-white-label-mtaandao-admin-theme/9722528?ref=sevenbold" target="_blank"><?php _e( 'CodeCanyon product page', 'mtaa-brand' ); ?></a>. <?php _e( 'Remember that we’re not able to support third party plugins that might conflict with', 'mtaa-brand' ); ?> Slate Pro.</p>
			<p>Mtaa Branding <?php _e( 'was made by', 'mtaa-brand' ); ?> <a href="http://mtaandao.co.ke" target="_blank">Seven Bold</a>. <?php _e( 'If you’re interested in customizing', 'mtaa-brand' ); ?> Mtaa Branding <?php _e( 'or any other web design and development projects, please contact us through the', 'mtaa-brand' ); ?> <a href="http://mtaandao.co.ke" target="_blank">Seven Bold <?php _e( 'website.', 'mtaa-brand' ); ?></a></p>
		</section>
		<section>
			<h3><?php _e( 'Email Opt-in', 'mtaa-brand' ); ?></h3>
			<ul>
				<li><p><?php _e( 'Stay up to date regarding', 'mtaa-brand' ); ?> Mtaa Branding <?php _e( 'and other', 'mtaa-brand' ); ?> Seven Bold <?php _e( 'products.', 'mtaa-brand' ); ?></p></li>
				<li>
					<!-- Begin MailChimp Signup Form -->
					<div id="mc_embed_signup">
						<form action="//sevenbold.us9.list-manage.com/subscribe/post?u=fb6f314c3674cc509e4e1fcf5&amp;id=e2a990023d" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>

							<div class="wrapper">
								<input type="email" value="" name="EMAIL" class="required email" id="mce-EMAIL" placeholder="Enter Your Email Address">
								<!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
								<div style="position: absolute; left: -5000px;"><input type="text" name="b_fb6f314c3674cc509e4e1fcf5_e2a990023d" tabindex="-1" value=""></div>
								<input type="submit" value="Sign Up" name="subscribe" id="mc-embedded-subscribe" class="button">
							</div>

						</form>
					</div>
					<!--End mc_embed_signup-->
				</li>
			</ul>
		</section>

	</div>

	<div id="slate__license" class="pageSection <?php if ( $page !== 'mtaa_brand_license' ) { echo 'hide'; } ?>">
		<?php
		if ( isset( $_POST['mtaa_brand_license']['licenseKey'] ) && !(empty ( $_POST['mtaa_brand_license']['licenseKey'] ) ) ) {
			$licenseKey = esc_attr( $_POST['mtaa_brand_license']['licenseKey'] );

			if ( isset( $_POST['licenseKeyActivate'] ) ) {
				$licenseReply = mtaa_brand_licensing( esc_attr( $licenseKey ), '0' );
				$licenseReply = $licenseReply['body'];
				if ( is_multisite() && is_plugin_active_for_network('mtaa-branding/mtaa-branding.php') ) {
					update_site_option( 'mtaa_brand_license', array(
						'licenseKey' => esc_attr( $licenseKey ),
						'licenseStatus' => esc_attr( $licenseReply )
						)
					);
				} else {
					update_option( 'mtaa_brand_license', array(
						'licenseKey' => esc_attr( $licenseKey ),
						'licenseStatus' => esc_attr( $licenseReply )
						)
					);
				}
			} else if ( isset( $_POST['licenseKeyDeactivate'] )  ) {
				$licenseReply = mtaa_brand_licensing( esc_attr( $licenseKey ), '1' );
				$licenseReply = $licenseReply['body'];
				if ( is_multisite() && is_plugin_active_for_network('mtaa-branding/mtaa-branding.php') ) {
					update_site_option( 'mtaa_brand_license', array(
						'licenseKey' => esc_attr( $licenseKey ),
						'licenseStatus' => esc_attr( $licenseReply )
						)
					);
				} else {
					update_option( 'mtaa_brand_license', array(
						'licenseKey' => esc_attr( $licenseKey ),
						'licenseStatus' => esc_attr( $licenseReply )
						)
					);
				}
			}
		} else {
			$licenseReply = '';
		}

		if ( is_multisite() && is_plugin_active_for_network('mtaa-branding/mtaa-branding.php') ) {
			$mtaa_brand_license = get_site_option( 'mtaa_brand_license' );
		} else {
			$mtaa_brand_license = get_option( 'mtaa_brand_license' );
		}
		$licenseStatus = esc_attr( $mtaa_brand_license['licenseStatus'] );
		if ('success' == $licenseStatus || 'active' == $licenseStatus ) {
			$licenseState = 'active';
		} else {
			$licenseState = 'inactive';
		}
		?>
		<form action="" method="post">

			<h2><?php _e( 'License', 'mtaa-brand' ); ?></h2>
			<p><?php _e( 'Enter your license key to qualify for premium customer support, receive updates via the Mtaandao Plugins page, access future updates, and other added benefits. Your license key is the same as the “Purchase Code” you received in your CodeCanyon.net purchase receipt email.', 'mtaa-brand' ); ?></p>
			<?php if ( isset($expire_date) && $expire_date > strtotime( $mtaa_brand_settings['licenseDate'] ) ) { ?>
			<p><?php _e( 'If you need a license key,', 'mtaa-brand' ); ?> <a href="http://mtaandao.co.ke/mtaandao/mtaa-brand/" target="_blank"><?php _e( 'please visit', 'mtaa-brand' ); ?> Seven Bold <?php _e( 'for info on how to buy', 'mtaa-brand' ); ?> Slate Pro</a>.</p>
			<?php } ?>
			<section>
				<ul>
					<li>
						<label><?php _e( 'License Key', 'mtaa-brand' ); ?> <input type="text" name="mtaa_brand_license[licenseKey]" value="<?php if ( 'active' == $licenseState ) { echo esc_attr( $mtaa_brand_license['licenseKey'] ); } ?>" <?php if ( 'active' == $licenseState ) {?> readonly="readonly"<?php } ?>></label>
						<div class="slate__licenseStatus">
							<?php if ( 'success' == $licenseReply ) { ?>
							<span class="slate__licenseSuccess"><?php _e( 'Your License Key was successfully activated!', 'mtaa-brand' ); ?></span>
							<?php	} else if ( 'current' == $licenseReply ) { ?>
							<span class="slate__licenseSuccess"><?php _e( 'Awesome, your license is valid and active!', 'mtaa-brand' ); ?></span>
							<?php	} else if ( 'invalid' == $licenseReply ) { ?>
							<span class="slate__licenseInvalid"><?php _e( 'Shoot, it looks like your License Key isn’t valid.', 'mtaa-brand' ); ?></span>
							<?php	} else if ( 'used' == $licenseReply ) { ?>
							<span class="slate__licenseActive"><?php _e( 'Looks like this key is already activated. It needs to be deactivated before you can use it.', 'mtaa-brand' ); ?></span>
							<?php	} else if ( 'removed' == $licenseReply ) { ?>
							<span class="slate__licenseRemoved"><?php _e( 'Your license key was successfully removed.', 'mtaa-brand' ); ?></span>
							<?php	} else if ( 'failed' == $licenseReply ) { ?>
							<span class="slate__licenseFailed"><?php _e( 'The license key you’re deactivating doesn’t match the website its activated on. Please deactivate from the proper website.', 'mtaa-brand' ); ?></span>
							<?php	} else if ( 'server' == $licenseReply ) { ?>
							<span class="slate__licenseServer"><?php _e( 'Oops, we couldn’t connect to the licensing server.', 'mtaa-brand' ); ?></span>
							<?php } ?>
						</div>
					</li>
					<?php if ( 'active' !== $licenseState ) { ?>
					<li>
						<input type="submit" name="licenseKeyActivate" id="licenseKeyActivate" class="button button-primary" value="Activate License Key">
					</li>
					<?php } else { ?>
					<li>
						<input type="submit" name="licenseKeyDeactivate" id="licenseKeyDeactivate" class="button" value="Deactivate License Key">
					</li>
					<?php } ?>
				</ul>
			</section>

		</form>

	</div>

</div>

<?php
// Setup each section of colors
function colorSection( $theSection, $colorCustom ) { ?>
<?php foreach ( $theSection as $section => $names ) { ?>
<h4><?php _e( $section, 'mtaa-brand' ); ?></h4>
<ul>
	<?php foreach ( $names as $field => $name ) { ?>
	<?php if ( is_array( $name ) ) { ?>
	<li><h5><?php _e( $field, 'mtaa-brand' ); ?></h5></li>
	<?php foreach ( $name as $field => $name ) { ?>
	<li>
		<label class="colorpickerToggle">
			<input type="text" class="slate__colorpicker" value="<?php echo mtaa_brand_sanitize_hex( $colorCustom[$field] ) ?>">
			<?php _e( $name, 'mtaa-brand' ); ?>
		</label>
		<input class="customColorsInput" type="hidden" name="mtaa_brand_settings[colorSchemeCustomColors][<?php echo $field;?>]" value="<?php echo mtaa_brand_sanitize_hex( $colorCustom[$field] ) ?>">
	</li>
	<?php } ?>
	<?php } else { ?>
	<li>
		<label class="colorpickerToggle">
			<input type="text" class="slate__colorpicker" value="<?php echo mtaa_brand_sanitize_hex( $colorCustom[$field] ) ?>">
			<?php _e( $name, 'mtaa-brand' ); ?>
		</label>
		<input class="customColorsInput" type="hidden" name="mtaa_brand_settings[colorSchemeCustomColors][<?php echo $field;?>]" value="<?php echo mtaa_brand_sanitize_hex( $colorCustom[$field] ) ?>">
	</li>
	<?php } ?>
	<?php } ?>
</ul>
<?php }
}