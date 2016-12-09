<?php
/**
 * Mtaa Framework Integration
 */

require_once Mtaa_INC . "/functions.php";
require_once Mtaa_INC . "/mtaa.php";
require_once Mtaa_INC . "/components/option.php";

/* Initialize Mtaa Framework */
Mtaa::init();

/* Only Mtaandao dashboard needs these files */
if (is_admin()) {
    require_once Mtaa_INC . "/components/medialib-uploader.php";
    require_once Mtaa_INC . "/components/admin/admin.php";
    require_once Mtaa_INC . "/components/admin/settings-fields.php";
    require_once Mtaa_INC . "/components/admin/settings-interface.php";
    require_once Mtaa_INC . "/components/admin/settings-page.php";
    require_once Mtaa_INC . "/components/admin/settings-sanitization.php";
    require_once Mtaa_INC . "/components/dashboard/dashboard.php";

    require_once Mtaa_INC . "/components/updater/updater.php";
    require_once Mtaa_INC . "/components/updater/framework-updater.php";
    require_once Mtaa_INC . "/components/updater/theme-updater.php";
}

/* Video API */
require_once Mtaa_INC . "/components/video-api.php";
if (is_admin()) {
    require_once Mtaa_INC . "/components/video-thumb.php";
}

/* Load get the image file only when it's not installed as a plugin */
if (!function_exists('get_the_image')) {
    require_once Mtaa_INC . "/components/get-the-image.php";
}

/* Require shortcodes */
if (option::is_on('framework_shortcodes_enable')) {
    require_once Mtaa_INC . "/components/shortcodes/shortcodes.php";
    require_once Mtaa_INC . "/components/shortcodes/init.php";
}

/* wzslider */
if (option::is_on('framework_wzslider_enable')) {
    require_once Mtaa_INC . "/components/shortcodes/wzslider.php";
}

require_once Mtaa_INC . "/components/theme/ui.php";

if (!is_admin()) {
    require_once Mtaa_INC . "/components/theme/theme.php";
    Mtaa_Theme::init();
}

/**
 * Delay `mtaa_load_components` function to run after `functions.php` file is
 * executed. This is needed because we load components code iff somewhere is
 * stated that this theme supports a mtaa component via `add_theme_support`.
 *
 * In `functions.php` and other code loaded directly by this file we need to
 * wrap access to option values (option::get) into functions that run in
 * `after_setup_theme` action with priority higher than 10, otherwise it will
 * get wrong value on first load.
 */
add_action( 'after_setup_theme', 'mtaa_load_components', 5 );

function mtaa_load_components() {
    if ( current_theme_supports( 'mtaa-portfolio' ) ) {
        require_once Mtaa_INC . "/components/portfolio/portfolio.php";
        new Mtaa_Portfolio;
    }

    if ( current_theme_supports( 'mtaa-post-slider' ) ) {
        require_once Mtaa_INC . "/components/post-slider/post-slider.php";
        new Mtaa_Post_Slider( get_theme_support( 'mtaa-post-slider' ) );
    }
}
