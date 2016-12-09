<?php
/**
 * Core Administration API
 *
 * @package Mtaandao
 * @subpackage Administration
 * @since 2.3.0
 */

if ( ! defined('ADMIN') ) {
	/*
	 * This file is being included from a file other than admin/admin.php, so
	 * some setup was skipped. Make sure the admin message catalog is loaded since
	 * load_default_textdomain() will not have done so in this context.
	 */
	load_textdomain( 'default', MN_LANG_DIR . '/admin-' . get_locale() . '.mo' );
}

/** Mtaandao Administration Hooks */
require_once(ABSPATH . 'admin/includes/admin-filters.php');

/** Mtaandao Bookmark Administration API */
require_once(ABSPATH . 'admin/includes/bookmark.php');

/** Mtaandao Comment Administration API */
require_once(ABSPATH . 'admin/includes/comment.php');

/** Mtaandao Administration File API */
require_once(ABSPATH . 'admin/includes/file.php');

/** Mtaandao Image Administration API */
require_once(ABSPATH . 'admin/includes/image.php');

/** Mtaandao Media Administration API */
require_once(ABSPATH . 'admin/includes/media.php');

/** Mtaandao Import Administration API */
require_once(ABSPATH . 'admin/includes/import.php');

/** Mtaandao Misc Administration API */
require_once(ABSPATH . 'admin/includes/misc.php');

/** Mtaandao Options Administration API */
require_once(ABSPATH . 'admin/includes/options.php');

/** Mtaandao Plugin Administration API */
require_once(ABSPATH . 'admin/includes/plugin.php');

/** Mtaandao Post Administration API */
require_once(ABSPATH . 'admin/includes/post.php');

/** Mtaandao Administration Screen API */
require_once(ABSPATH . 'admin/includes/class-mn-screen.php');
require_once(ABSPATH . 'admin/includes/screen.php');

/** Mtaandao Taxonomy Administration API */
require_once(ABSPATH . 'admin/includes/taxonomy.php');

/** Mtaandao Template Administration API */
require_once(ABSPATH . 'admin/includes/template.php');

/** Mtaandao List Table Administration API and base class */
require_once(ABSPATH . 'admin/includes/class-mn-list-table.php');
require_once(ABSPATH . 'admin/includes/class-mn-list-table-compat.php');
require_once(ABSPATH . 'admin/includes/list-table.php');

/** Mtaandao Theme Administration API */
require_once(ABSPATH . 'admin/includes/theme.php');

/** Mtaandao User Administration API */
require_once(ABSPATH . 'admin/includes/user.php');

/** Mtaandao Site Icon API */
require_once(ABSPATH . 'admin/includes/class-mn-site-icon.php');

/** Mtaandao Update Administration API */
require_once(ABSPATH . 'admin/includes/update.php');

/** Mtaandao Deprecated Administration API */
require_once(ABSPATH . 'admin/includes/deprecated.php');

/** Mtaandao Multisite support API */
if ( is_multisite() ) {
	require_once(ABSPATH . 'admin/includes/ms-admin-filters.php');
	require_once(ABSPATH . 'admin/includes/ms.php');
	require_once(ABSPATH . 'admin/includes/ms-deprecated.php');
}
