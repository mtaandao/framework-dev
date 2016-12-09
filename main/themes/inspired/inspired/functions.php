<?php
/**
 * Mtaa Theme Functions
 *
 * Don't edit this file until you know what you're doing. If you mind to add
 * functions and other hacks please use functions/user/ folder instead and
 * functions/user/functions.php file, those files are intend for that and
 * will never be overwritten in case of a framework update.
 */

/**
 * Paths to Mtaa Theme Functions
 */
define("FUNC_INC", get_template_directory() . "/functions");

define("Mtaa_INC", FUNC_INC . "/mtaa");
define("THEME_INC", FUNC_INC . "/theme");
define("USER_INC", FUNC_INC . "/user");

/** Mtaa Framework Core */
require_once Mtaa_INC . "/init.php";

/** Mtaa Theme */
require_once THEME_INC . "/functions.php";
require_once THEME_INC . "/post-options.php";
require_once THEME_INC . "/custom-post-types.php";
require_once THEME_INC . "/sidebar.php";

/* Theme widgets */
require_once THEME_INC . "/widgets/social.php";
require_once THEME_INC . "/widgets/recentposts.php";
require_once THEME_INC . "/widgets/twitter.php";
require_once THEME_INC . "/widgets/instagram.php";
require_once THEME_INC . "/widgets/single-page.php";
require_once THEME_INC . "/widgets/portfolio-showcase.php";
require_once THEME_INC . "/widgets/portfolio-scroller.php";
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	require_once THEME_INC . "/widgets/featured-products.php";
}

/** User functions */
require_once USER_INC . "/functions.php";

?>