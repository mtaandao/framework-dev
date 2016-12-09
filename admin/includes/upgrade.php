<?php
/**
 * Mtaandao Upgrade API
 *
 * Most of the functions are pluggable and can be overwritten.
 *
 * @package Mtaandao
 * @subpackage Administration
 */

/** Include user install customize script. */
if ( file_exists(MAIN_DIR . '/install.php') )
	require (MAIN_DIR . '/install.php');

/** Mtaandao Administration API */
require_once(ABSPATH . 'admin/includes/admin.php');

/** Mtaandao Schema API */
require_once(ABSPATH . 'admin/includes/schema.php');

if ( !function_exists('mn_install') ) :
/**
 * Installs the site.
 *
 * Runs the required functions to set up and populate the database,
 * including primary admin user and initial options.
 *
 * @since 2.1.0
 *
 * @param string $blog_title    Site title.
 * @param string $user_name     User's username.
 * @param string $user_email    User's email.
 * @param bool   $public        Whether site is public.
 * @param string $deprecated    Optional. Not used.
 * @param string $user_password Optional. User's chosen password. Default empty (random password).
 * @param string $language      Optional. Language chosen. Default empty.
 * @return array Array keys 'url', 'user_id', 'password', and 'password_message'.
 */
function mn_install( $blog_title, $user_name, $user_email, $public, $deprecated = '', $user_password = '', $language = '' ) {
	if ( !empty( $deprecated ) )
		_deprecated_argument( __FUNCTION__, '2.6.0' );

	mn_check_mysql_version();
	mn_cache_flush();
	make_db_current_silent();
	populate_options();
	populate_roles();

	update_option('blogname', $blog_title);
	update_option('admin_email', $user_email);
	update_option('blog_public', $public);

	// Freshness of site - in the future, this could get more specific about actions taken, perhaps.
	update_option( 'fresh_site', 1 );

	if ( $language ) {
		update_option( 'MNLANG', $language );
	}

	$guessurl = mn_guess_url();

	update_option('siteurl', $guessurl);

	// If not a public blog, don't ping.
	if ( ! $public )
		update_option('default_pingback_flag', 0);

	/*
	 * Create default user. If the user already exists, the user tables are
	 * being shared among sites. Just set the role in that case.
	 */
	$user_id = username_exists($user_name);
	$user_password = trim($user_password);
	$email_password = false;
	if ( !$user_id && empty($user_password) ) {
		$user_password = mn_generate_password( 12, false );
		$message = __('<strong><em>Note that password</em></strong> carefully! It is a <em>random</em> password that was generated just for you.');
		$user_id = mn_create_user($user_name, $user_password, $user_email);
		update_user_option($user_id, 'default_password_nag', true, true);
		$email_password = true;
	} elseif ( ! $user_id ) {
		// Password has been provided
		$message = '<em>'.__('Your chosen password.').'</em>';
		$user_id = mn_create_user($user_name, $user_password, $user_email);
	} else {
		$message = __('User already exists. Password inherited.');
	}

	$user = new MN_User($user_id);
	$user->set_role('administrator');

	mn_install_defaults($user_id);

	mn_install_maybe_enable_pretty_permalinks();

	flush_rewrite_rules();

	mn_new_blog_notification($blog_title, $guessurl, $user_id, ($email_password ? $user_password : __('The password you chose during the install.') ) );

	mn_cache_flush();

	/**
	 * Fires after a site is fully installed.
	 *
	 * @since 3.9.0
	 *
	 * @param MN_User $user The site owner.
	 */
	do_action( 'mn_install', $user );

	return array('url' => $guessurl, 'user_id' => $user_id, 'password' => $user_password, 'password_message' => $message);
}
endif;

if ( !function_exists('mn_install_defaults') ) :
/**
 * Creates the initial content for a newly-installed site.
 *
 * Adds the default "Uncategorized" category, the first post (with comment),
 * first page, and default widgets for default theme for the current version.
 *
 * @since 2.1.0
 *
 * @global mndb       $mndb
 * @global MN_Rewrite $mn_rewrite
 * @global string     $table_prefix
 *
 * @param int $user_id User ID.
 */
function mn_install_defaults( $user_id ) {
	global $mndb, $mn_rewrite, $table_prefix;

	// Default category
	$cat_name = __('Uncategorized');
	/* translators: Default category slug */
	$cat_slug = sanitize_title(_x('Uncategorized', 'Default category slug'));

	if ( global_terms_enabled() ) {
		$cat_id = $mndb->get_var( $mndb->prepare( "SELECT cat_ID FROM {$mndb->sitecategories} WHERE category_nicename = %s", $cat_slug ) );
		if ( $cat_id == null ) {
			$mndb->insert( $mndb->sitecategories, array('cat_ID' => 0, 'cat_name' => $cat_name, 'category_nicename' => $cat_slug, 'last_updated' => current_time('mysql', true)) );
			$cat_id = $mndb->insert_id;
		}
		update_option('default_category', $cat_id);
	} else {
		$cat_id = 1;
	}

	$mndb->insert( $mndb->terms, array('term_id' => $cat_id, 'name' => $cat_name, 'slug' => $cat_slug, 'term_group' => 0) );
	$mndb->insert( $mndb->term_taxonomy, array('term_id' => $cat_id, 'taxonomy' => 'category', 'description' => '', 'parent' => 0, 'count' => 1));
	$cat_tt_id = $mndb->insert_id;

	// First post
	$now = current_time( 'mysql' );
	$now_gmt = current_time( 'mysql', 1 );
	$first_post_guid = get_option( 'home' ) . '/?p=1';

	if ( is_multisite() ) {
		$first_post = get_site_option( 'first_post' );

		if ( ! $first_post ) {
			/* translators: %s: site link */
			$first_post = __( 'Welcome to %s. This is your first post. Edit or delete it, then start blogging!' );
		}

		$first_post = sprintf( $first_post,
			sprintf( '<a href="%s">%s</a>', esc_url( network_home_url() ), get_network()->site_name )
		);

		// Back-compat for pre-4.4
		$first_post = str_replace( 'SITE_URL', esc_url( network_home_url() ), $first_post );
		$first_post = str_replace( 'SITE_NAME', get_network()->site_name, $first_post );
	} else {
		$first_post = __( 'Welcome to Mtaandao. This is your first post. Edit or delete it, then start writing!' );
	}

	$mndb->insert( $mndb->posts, array(
		'post_author' => $user_id,
		'post_date' => $now,
		'post_date_gmt' => $now_gmt,
		'post_content' => $first_post,
		'post_excerpt' => '',
		'post_title' => __('Hello world!'),
		/* translators: Default post slug */
		'post_name' => sanitize_title( _x('hello-world', 'Default post slug') ),
		'post_modified' => $now,
		'post_modified_gmt' => $now_gmt,
		'guid' => $first_post_guid,
		'comment_count' => 1,
		'to_ping' => '',
		'pinged' => '',
		'post_content_filtered' => ''
	));
	$mndb->insert( $mndb->term_relationships, array('term_taxonomy_id' => $cat_tt_id, 'object_id' => 1) );

	// Default comment
	$first_comment_author = __( 'A Mtaandao Commenter' );
	$first_comment_email = 'wapuu@mtaandao.example';
	$first_comment_url = 'https://mtaandao.co.ke/';
	$first_comment = __( 'Hi, this is a comment.
To get started with moderating, editing, and deleting comments, please visit the Comments screen in the dashboard.
Commenter avatars come from <a href="https://gravatar.com">Gravatar</a>.' );
	if ( is_multisite() ) {
		$first_comment_author = get_site_option( 'first_comment_author', $first_comment_author );
		$first_comment_email = get_site_option( 'first_comment_email', $first_comment_email );
		$first_comment_url = get_site_option( 'first_comment_url', network_home_url() );
		$first_comment = get_site_option( 'first_comment', $first_comment );
	}
	$mndb->insert( $mndb->comments, array(
		'comment_post_ID' => 1,
		'comment_author' => $first_comment_author,
		'comment_author_email' => $first_comment_email,
		'comment_author_url' => $first_comment_url,
		'comment_date' => $now,
		'comment_date_gmt' => $now_gmt,
		'comment_content' => $first_comment
	));

	// First Page
	$first_page = sprintf( __( "This is an example page. It's different from a blog post because it will stay in one place and will show up in your site navigation (in most themes). Most people start with an About page that introduces them to potential site visitors. It might say something like this:

<blockquote>Hi there! I'm a bike messenger by day, aspiring actor by night, and this is my website. I live in Los Angeles, have a great dog named Jack, and I like pi&#241;a coladas. (And gettin' caught in the rain.)</blockquote>

...or something like this:

<blockquote>The XYZ Doohickey Company was founded in 1971, and has been providing quality doohickeys to the public ever since. Located in Gotham City, XYZ employs over 2,000 people and does all kinds of awesome things for the Gotham community.</blockquote>

As a new Mtaandao user, you should go to <a href=\"%s\">your dashboard</a> to delete this page and create new pages for your content. Have fun!" ), admin_url() );
	if ( is_multisite() )
		$first_page = get_site_option( 'first_page', $first_page );
	$first_post_guid = get_option('home') . '/?page_id=2';
	$mndb->insert( $mndb->posts, array(
		'post_author' => $user_id,
		'post_date' => $now,
		'post_date_gmt' => $now_gmt,
		'post_content' => $first_page,
		'post_excerpt' => '',
		'comment_status' => 'closed',
		'post_title' => __( 'Sample Page' ),
		/* translators: Default page slug */
		'post_name' => __( 'sample-page' ),
		'post_modified' => $now,
		'post_modified_gmt' => $now_gmt,
		'guid' => $first_post_guid,
		'post_type' => 'page',
		'to_ping' => '',
		'pinged' => '',
		'post_content_filtered' => ''
	));
	$mndb->insert( $mndb->postmeta, array( 'post_id' => 2, 'meta_key' => '_mn_page_template', 'meta_value' => 'default' ) );

	// Set up default widgets for default theme.
	update_option( 'widget_search', array ( 2 => array ( 'title' => '' ), '_multiwidget' => 1 ) );
	update_option( 'widget_recent-posts', array ( 2 => array ( 'title' => '', 'number' => 5 ), '_multiwidget' => 1 ) );
	update_option( 'widget_recent-comments', array ( 2 => array ( 'title' => '', 'number' => 5 ), '_multiwidget' => 1 ) );
	update_option( 'widget_archives', array ( 2 => array ( 'title' => '', 'count' => 0, 'dropdown' => 0 ), '_multiwidget' => 1 ) );
	update_option( 'widget_categories', array ( 2 => array ( 'title' => '', 'count' => 0, 'hierarchical' => 0, 'dropdown' => 0 ), '_multiwidget' => 1 ) );
	update_option( 'widget_meta', array ( 2 => array ( 'title' => '' ), '_multiwidget' => 1 ) );
	update_option( 'sidebars_widgets', array( 'mn_inactive_widgets' => array(), 'sidebar-1' => array( 0 => 'search-2', 1 => 'recent-posts-2', 2 => 'recent-comments-2', 3 => 'archives-2', 4 => 'categories-2', 5 => 'meta-2' ), 'sidebar-2' => array(), 'sidebar-3' => array(), 'array_version' => 3 ) );
	if ( ! is_multisite() )
		update_user_meta( $user_id, 'show_welcome_panel', 1 );
	elseif ( ! is_super_admin( $user_id ) && ! metadata_exists( 'user', $user_id, 'show_welcome_panel' ) )
		update_user_meta( $user_id, 'show_welcome_panel', 2 );

	if ( is_multisite() ) {
		// Flush rules to pick up the new page.
		$mn_rewrite->init();
		$mn_rewrite->flush_rules();

		$user = new MN_User($user_id);
		$mndb->update( $mndb->options, array('option_value' => $user->user_email), array('option_name' => 'admin_email') );

		// Remove all perms except for the login user.
		$mndb->query( $mndb->prepare("DELETE FROM $mndb->usermeta WHERE user_id != %d AND meta_key = %s", $user_id, $table_prefix.'user_level') );
		$mndb->query( $mndb->prepare("DELETE FROM $mndb->usermeta WHERE user_id != %d AND meta_key = %s", $user_id, $table_prefix.'capabilities') );

		// Delete any caps that snuck into the previously active blog. (Hardcoded to blog 1 for now.) TODO: Get previous_blog_id.
		if ( !is_super_admin( $user_id ) && $user_id != 1 )
			$mndb->delete( $mndb->usermeta, array( 'user_id' => $user_id , 'meta_key' => $mndb->base_prefix.'1_capabilities' ) );
	}
}
endif;

/**
 * Maybe enable pretty permalinks on install.
 *
 * If after enabling pretty permalinks don't work, fallback to query-string permalinks.
 *
 * @since 4.2.0
 *
 * @global MN_Rewrite $mn_rewrite Mtaandao rewrite component.
 *
 * @return bool Whether pretty permalinks are enabled. False otherwise.
 */
function mn_install_maybe_enable_pretty_permalinks() {
	global $mn_rewrite;

	// Bail if a permalink structure is already enabled.
	if ( get_option( 'permalink_structure' ) ) {
		return true;
	}

	/*
	 * The Permalink structures to attempt.
	 *
	 * The first is designed for mod_rewrite or nginx rewriting.
	 *
	 * The second is PATHINFO-based permalinks for web server configurations
	 * without a true rewrite module enabled.
	 */
	$permalink_structures = array(
		'/%year%/%monthnum%/%day%/%postname%/',
		'/index.php/%year%/%monthnum%/%day%/%postname%/'
	);

	foreach ( (array) $permalink_structures as $permalink_structure ) {
		$mn_rewrite->set_permalink_structure( $permalink_structure );

		/*
	 	 * Flush rules with the hard option to force refresh of the web-server's
	 	 * rewrite config file (e.g. .htaccess or web.config).
	 	 */
		$mn_rewrite->flush_rules( true );

		$test_url = '';

		// Test against a real Mtaandao Post
		$first_post = get_page_by_path( sanitize_title( _x( 'hello-world', 'Default post slug' ) ), OBJECT, 'post' );
		if ( $first_post ) {
			$test_url = get_permalink( $first_post->ID );
		}

		/*
	 	 * Send a request to the site, and check whether
	 	 * the 'x-pingback' header is returned as expected.
	 	 *
	 	 * Uses mn_remote_get() instead of mn_remote_head() because web servers
	 	 * can block head requests.
	 	 */
		$response          = mn_remote_get( $test_url, array( 'timeout' => 5 ) );
		$x_pingback_header = mn_remote_retrieve_header( $response, 'x-pingback' );
		$pretty_permalinks = $x_pingback_header && $x_pingback_header === get_bloginfo( 'pingback_url' );

		if ( $pretty_permalinks ) {
			return true;
		}
	}

	/*
	 * If it makes it this far, pretty permalinks failed.
	 * Fallback to query-string permalinks.
	 */
	$mn_rewrite->set_permalink_structure( '' );
	$mn_rewrite->flush_rules( true );

	return false;
}

if ( !function_exists('mn_new_blog_notification') ) :
/**
 * Notifies the site admin that the setup is complete.
 *
 * Sends an email with mn_mail to the new administrator that the site setup is complete,
 * and provides them with a record of their login credentials.
 *
 * @since 2.1.0
 *
 * @param string $blog_title Site title.
 * @param string $blog_url   Site url.
 * @param int    $user_id    User ID.
 * @param string $password   User's Password.
 */
function mn_new_blog_notification($blog_title, $blog_url, $user_id, $password) {
	$user = new MN_User( $user_id );
	$email = $user->user_email;
	$name = $user->user_login;
	$login_url = mn_login_url();
	/* translators: New site notification email. 1: New site URL, 2: User login, 3: User password or password reset link, 4: Login URL */
	$message = sprintf( __( "Your new Mtaandao site has been successfully set up at:

%1\$s

You can log in to the administrator account with the following information:

Username: %2\$s
Password: %3\$s
Log in here: %4\$s

We hope you enjoy your new site. Thanks!

--The Mtaandao Team
https://mtaandao.co.ke/
"), $blog_url, $name, $password, $login_url );

	@mn_mail($email, __('New Mtaandao Site'), $message);
}
endif;

if ( !function_exists('mn_upgrade') ) :
/**
 * Runs Mtaandao Upgrade functions.
 *
 * Upgrades the database if needed during a site update.
 *
 * @since 2.1.0
 *
 * @global int  $mn_current_db_version
 * @global int  $mn_db_version
 * @global mndb $mndb Mtaandao database abstraction object.
 */
function mn_upgrade() {
	global $mn_current_db_version, $mn_db_version, $mndb;

	$mn_current_db_version = __get_option('db_version');

	// We are up-to-date. Nothing to do.
	if ( $mn_db_version == $mn_current_db_version )
		return;

	if ( ! is_blog_installed() )
		return;

	mn_check_mysql_version();
	mn_cache_flush();
	pre_schema_upgrade();
	make_db_current_silent();
	upgrade_all();
	if ( is_multisite() && is_main_site() )
		upgrade_network();
	mn_cache_flush();

	if ( is_multisite() ) {
		if ( $mndb->get_row( "SELECT blog_id FROM {$mndb->blog_versions} WHERE blog_id = '{$mndb->blogid}'" ) )
			$mndb->query( "UPDATE {$mndb->blog_versions} SET db_version = '{$mn_db_version}' WHERE blog_id = '{$mndb->blogid}'" );
		else
			$mndb->query( "INSERT INTO {$mndb->blog_versions} ( `blog_id` , `db_version` , `last_updated` ) VALUES ( '{$mndb->blogid}', '{$mn_db_version}', NOW());" );
	}

	/**
	 * Fires after a site is fully upgraded.
	 *
	 * @since 3.9.0
	 *
	 * @param int $mn_db_version         The new $mn_db_version.
	 * @param int $mn_current_db_version The old (current) $mn_db_version.
	 */
	do_action( 'mn_upgrade', $mn_db_version, $mn_current_db_version );
}
endif;

/**
 * Functions to be called in install and upgrade scripts.
 *
 * Contains conditional checks to determine which upgrade scripts to run,
 * based on database version and MN version being updated-to.
 *
 * @ignore
 * @since 1.0.1
 *
 * @global int $mn_current_db_version
 * @global int $mn_db_version
 */
function upgrade_all() {
	global $mn_current_db_version, $mn_db_version;
	$mn_current_db_version = __get_option('db_version');

	// We are up-to-date. Nothing to do.
	if ( $mn_db_version == $mn_current_db_version )
		return;

	// If the version is not set in the DB, try to guess the version.
	if ( empty($mn_current_db_version) ) {
		$mn_current_db_version = 0;

		// If the template option exists, we have 1.5.
		$template = __get_option('template');
		if ( !empty($template) )
			$mn_current_db_version = 2541;
	}

	if ( $mn_current_db_version < 6039 )
		upgrade_230_options_table();

	populate_options();

	if ( $mn_current_db_version < 2541 ) {
		upgrade_100();
		upgrade_101();
		upgrade_110();
		upgrade_130();
	}

	if ( $mn_current_db_version < 3308 )
		upgrade_160();

	if ( $mn_current_db_version < 4772 )
		upgrade_210();

	if ( $mn_current_db_version < 4351 )
		upgrade_old_slugs();

	if ( $mn_current_db_version < 5539 )
		upgrade_230();

	if ( $mn_current_db_version < 6124 )
		upgrade_230_old_tables();

	if ( $mn_current_db_version < 7499 )
		upgrade_250();

	if ( $mn_current_db_version < 7935 )
		upgrade_252();

	if ( $mn_current_db_version < 8201 )
		upgrade_260();

	if ( $mn_current_db_version < 8989 )
		upgrade_270();

	if ( $mn_current_db_version < 10360 )
		upgrade_280();

	if ( $mn_current_db_version < 11958 )
		upgrade_290();

	if ( $mn_current_db_version < 15260 )
		upgrade_300();

	if ( $mn_current_db_version < 19389 )
		upgrade_330();

	if ( $mn_current_db_version < 20080 )
		upgrade_340();

	if ( $mn_current_db_version < 22422 )
		upgrade_350();

	if ( $mn_current_db_version < 25824 )
		upgrade_370();

	if ( $mn_current_db_version < 26148 )
		upgrade_372();

	if ( $mn_current_db_version < 26691 )
		upgrade_380();

	if ( $mn_current_db_version < 29630 )
		upgrade_400();

	if ( $mn_current_db_version < 33055 )
		upgrade_430();

	if ( $mn_current_db_version < 33056 )
		upgrade_431();

	if ( $mn_current_db_version < 35700 )
		upgrade_440();

	if ( $mn_current_db_version < 36686 )
		upgrade_450();

	if ( $mn_current_db_version < 37965 )
		upgrade_460();

	maybe_disable_link_manager();

	maybe_disable_automattic_widgets();

	update_option( 'db_version', $mn_db_version );
	update_option( 'db_upgraded', true );
}

/**
 * Execute changes made in Mtaandao 1.0.
 *
 * @ignore
 * @since 1.0.0
 *
 * @global mndb $mndb Mtaandao database abstraction object.
 */
function upgrade_100() {
	global $mndb;

	// Get the title and ID of every post, post_name to check if it already has a value
	$posts = $mndb->get_results("SELECT ID, post_title, post_name FROM $mndb->posts WHERE post_name = ''");
	if ($posts) {
		foreach ($posts as $post) {
			if ('' == $post->post_name) {
				$newtitle = sanitize_title($post->post_title);
				$mndb->query( $mndb->prepare("UPDATE $mndb->posts SET post_name = %s WHERE ID = %d", $newtitle, $post->ID) );
			}
		}
	}

	$categories = $mndb->get_results("SELECT cat_ID, cat_name, category_nicename FROM $mndb->categories");
	foreach ($categories as $category) {
		if ('' == $category->category_nicename) {
			$newtitle = sanitize_title($category->cat_name);
			$mndb->update( $mndb->categories, array('category_nicename' => $newtitle), array('cat_ID' => $category->cat_ID) );
		}
	}

	$sql = "UPDATE $mndb->options
		SET option_value = REPLACE(option_value, 'mn-links/links-images/', 'mn-images/links/')
		WHERE option_name LIKE %s
		AND option_value LIKE %s";
	$mndb->query( $mndb->prepare( $sql, $mndb->esc_like( 'links_rating_image' ) . '%', $mndb->esc_like( 'mn-links/links-images/' ) . '%' ) );

	$done_ids = $mndb->get_results("SELECT DISTINCT post_id FROM $mndb->post2cat");
	if ($done_ids) :
		$done_posts = array();
		foreach ($done_ids as $done_id) :
			$done_posts[] = $done_id->post_id;
		endforeach;
		$catwhere = ' AND ID NOT IN (' . implode(',', $done_posts) . ')';
	else:
		$catwhere = '';
	endif;

	$allposts = $mndb->get_results("SELECT ID, post_category FROM $mndb->posts WHERE post_category != '0' $catwhere");
	if ($allposts) :
		foreach ($allposts as $post) {
			// Check to see if it's already been imported
			$cat = $mndb->get_row( $mndb->prepare("SELECT * FROM $mndb->post2cat WHERE post_id = %d AND category_id = %d", $post->ID, $post->post_category) );
			if (!$cat && 0 != $post->post_category) { // If there's no result
				$mndb->insert( $mndb->post2cat, array('post_id' => $post->ID, 'category_id' => $post->post_category) );
			}
		}
	endif;
}

/**
 * Execute changes made in Mtaandao 1.0.1.
 *
 * @ignore
 * @since 1.0.1
 *
 * @global mndb $mndb Mtaandao database abstraction object.
 */
function upgrade_101() {
	global $mndb;

	// Clean up indices, add a few
	add_clean_index($mndb->posts, 'post_name');
	add_clean_index($mndb->posts, 'post_status');
	add_clean_index($mndb->categories, 'category_nicename');
	add_clean_index($mndb->comments, 'comment_approved');
	add_clean_index($mndb->comments, 'comment_post_ID');
	add_clean_index($mndb->links , 'link_category');
	add_clean_index($mndb->links , 'link_visible');
}

/**
 * Execute changes made in Mtaandao 1.2.
 *
 * @ignore
 * @since 1.2.0
 *
 * @global mndb $mndb Mtaandao database abstraction object.
 */
function upgrade_110() {
	global $mndb;

	// Set user_nicename.
	$users = $mndb->get_results("SELECT ID, user_nickname, user_nicename FROM $mndb->users");
	foreach ($users as $user) {
		if ('' == $user->user_nicename) {
			$newname = sanitize_title($user->user_nickname);
			$mndb->update( $mndb->users, array('user_nicename' => $newname), array('ID' => $user->ID) );
		}
	}

	$users = $mndb->get_results("SELECT ID, user_pass from $mndb->users");
	foreach ($users as $row) {
		if (!preg_match('/^[A-Fa-f0-9]{32}$/', $row->user_pass)) {
			$mndb->update( $mndb->users, array('user_pass' => md5($row->user_pass)), array('ID' => $row->ID) );
		}
	}

	// Get the GMT offset, we'll use that later on
	$all_options = get_alloptions_110();

	$time_difference = $all_options->time_difference;

		$server_time = time()+date('Z');
	$weblogger_time = $server_time + $time_difference * HOUR_IN_SECONDS;
	$gmt_time = time();

	$diff_gmt_server = ($gmt_time - $server_time) / HOUR_IN_SECONDS;
	$diff_weblogger_server = ($weblogger_time - $server_time) / HOUR_IN_SECONDS;
	$diff_gmt_weblogger = $diff_gmt_server - $diff_weblogger_server;
	$gmt_offset = -$diff_gmt_weblogger;

	// Add a gmt_offset option, with value $gmt_offset
	add_option('gmt_offset', $gmt_offset);

	// Check if we already set the GMT fields (if we did, then
	// MAX(post_date_gmt) can't be '0000-00-00 00:00:00'
	// <michel_v> I just slapped myself silly for not thinking about it earlier
	$got_gmt_fields = ! ($mndb->get_var("SELECT MAX(post_date_gmt) FROM $mndb->posts") == '0000-00-00 00:00:00');

	if (!$got_gmt_fields) {

		// Add or subtract time to all dates, to get GMT dates
		$add_hours = intval($diff_gmt_weblogger);
		$add_minutes = intval(60 * ($diff_gmt_weblogger - $add_hours));
		$mndb->query("UPDATE $mndb->posts SET post_date_gmt = DATE_ADD(post_date, INTERVAL '$add_hours:$add_minutes' HOUR_MINUTE)");
		$mndb->query("UPDATE $mndb->posts SET post_modified = post_date");
		$mndb->query("UPDATE $mndb->posts SET post_modified_gmt = DATE_ADD(post_modified, INTERVAL '$add_hours:$add_minutes' HOUR_MINUTE) WHERE post_modified != '0000-00-00 00:00:00'");
		$mndb->query("UPDATE $mndb->comments SET comment_date_gmt = DATE_ADD(comment_date, INTERVAL '$add_hours:$add_minutes' HOUR_MINUTE)");
		$mndb->query("UPDATE $mndb->users SET user_registered = DATE_ADD(user_registered, INTERVAL '$add_hours:$add_minutes' HOUR_MINUTE)");
	}

}

/**
 * Execute changes made in Mtaandao 1.5.
 *
 * @ignore
 * @since 1.5.0
 *
 * @global mndb $mndb Mtaandao database abstraction object.
 */
function upgrade_130() {
	global $mndb;

	// Remove extraneous backslashes.
	$posts = $mndb->get_results("SELECT ID, post_title, post_content, post_excerpt, guid, post_date, post_name, post_status, post_author FROM $mndb->posts");
	if ($posts) {
		foreach ($posts as $post) {
			$post_content = addslashes(deslash($post->post_content));
			$post_title = addslashes(deslash($post->post_title));
			$post_excerpt = addslashes(deslash($post->post_excerpt));
			if ( empty($post->guid) )
				$guid = get_permalink($post->ID);
			else
				$guid = $post->guid;

			$mndb->update( $mndb->posts, compact('post_title', 'post_content', 'post_excerpt', 'guid'), array('ID' => $post->ID) );

		}
	}

	// Remove extraneous backslashes.
	$comments = $mndb->get_results("SELECT comment_ID, comment_author, comment_content FROM $mndb->comments");
	if ($comments) {
		foreach ($comments as $comment) {
			$comment_content = deslash($comment->comment_content);
			$comment_author = deslash($comment->comment_author);

			$mndb->update($mndb->comments, compact('comment_content', 'comment_author'), array('comment_ID' => $comment->comment_ID) );
		}
	}

	// Remove extraneous backslashes.
	$links = $mndb->get_results("SELECT link_id, link_name, link_description FROM $mndb->links");
	if ($links) {
		foreach ($links as $link) {
			$link_name = deslash($link->link_name);
			$link_description = deslash($link->link_description);

			$mndb->update( $mndb->links, compact('link_name', 'link_description'), array('link_id' => $link->link_id) );
		}
	}

	$active_plugins = __get_option('active_plugins');

	/*
	 * If plugins are not stored in an array, they're stored in the old
	 * newline separated format. Convert to new format.
	 */
	if ( !is_array( $active_plugins ) ) {
		$active_plugins = explode("\n", trim($active_plugins));
		update_option('active_plugins', $active_plugins);
	}

	// Obsolete tables
	$mndb->query('DROP TABLE IF EXISTS ' . $mndb->prefix . 'optionvalues');
	$mndb->query('DROP TABLE IF EXISTS ' . $mndb->prefix . 'optiontypes');
	$mndb->query('DROP TABLE IF EXISTS ' . $mndb->prefix . 'optiongroups');
	$mndb->query('DROP TABLE IF EXISTS ' . $mndb->prefix . 'optiongroup_options');

	// Update comments table to use comment_type
	$mndb->query("UPDATE $mndb->comments SET comment_type='trackback', comment_content = REPLACE(comment_content, '<trackback />', '') WHERE comment_content LIKE '<trackback />%'");
	$mndb->query("UPDATE $mndb->comments SET comment_type='pingback', comment_content = REPLACE(comment_content, '<pingback />', '') WHERE comment_content LIKE '<pingback />%'");

	// Some versions have multiple duplicate option_name rows with the same values
	$options = $mndb->get_results("SELECT option_name, COUNT(option_name) AS dupes FROM `$mndb->options` GROUP BY option_name");
	foreach ( $options as $option ) {
		if ( 1 != $option->dupes ) { // Could this be done in the query?
			$limit = $option->dupes - 1;
			$dupe_ids = $mndb->get_col( $mndb->prepare("SELECT option_id FROM $mndb->options WHERE option_name = %s LIMIT %d", $option->option_name, $limit) );
			if ( $dupe_ids ) {
				$dupe_ids = join($dupe_ids, ',');
				$mndb->query("DELETE FROM $mndb->options WHERE option_id IN ($dupe_ids)");
			}
		}
	}

	make_site_theme();
}

/**
 * Execute changes made in Mtaandao 2.0.
 *
 * @ignore
 * @since 2.0.0
 *
 * @global mndb $mndb Mtaandao database abstraction object.
 * @global int  $mn_current_db_version
 */
function upgrade_160() {
	global $mndb, $mn_current_db_version;

	populate_roles_160();

	$users = $mndb->get_results("SELECT * FROM $mndb->users");
	foreach ( $users as $user ) :
		if ( !empty( $user->user_firstname ) )
			update_user_meta( $user->ID, 'first_name', mn_slash($user->user_firstname) );
		if ( !empty( $user->user_lastname ) )
			update_user_meta( $user->ID, 'last_name', mn_slash($user->user_lastname) );
		if ( !empty( $user->user_nickname ) )
			update_user_meta( $user->ID, 'nickname', mn_slash($user->user_nickname) );
		if ( !empty( $user->user_level ) )
			update_user_meta( $user->ID, $mndb->prefix . 'user_level', $user->user_level );
		if ( !empty( $user->user_icq ) )
			update_user_meta( $user->ID, 'icq', mn_slash($user->user_icq) );
		if ( !empty( $user->user_aim ) )
			update_user_meta( $user->ID, 'aim', mn_slash($user->user_aim) );
		if ( !empty( $user->user_msn ) )
			update_user_meta( $user->ID, 'msn', mn_slash($user->user_msn) );
		if ( !empty( $user->user_yim ) )
			update_user_meta( $user->ID, 'yim', mn_slash($user->user_icq) );
		if ( !empty( $user->user_description ) )
			update_user_meta( $user->ID, 'description', mn_slash($user->user_description) );

		if ( isset( $user->user_idmode ) ):
			$idmode = $user->user_idmode;
			if ($idmode == 'nickname') $id = $user->user_nickname;
			if ($idmode == 'login') $id = $user->user_login;
			if ($idmode == 'firstname') $id = $user->user_firstname;
			if ($idmode == 'lastname') $id = $user->user_lastname;
			if ($idmode == 'namefl') $id = $user->user_firstname.' '.$user->user_lastname;
			if ($idmode == 'namelf') $id = $user->user_lastname.' '.$user->user_firstname;
			if (!$idmode) $id = $user->user_nickname;
			$mndb->update( $mndb->users, array('display_name' => $id), array('ID' => $user->ID) );
		endif;

		// FIXME: RESET_CAPS is temporary code to reset roles and caps if flag is set.
		$caps = get_user_meta( $user->ID, $mndb->prefix . 'capabilities');
		if ( empty($caps) || defined('RESET_CAPS') ) {
			$level = get_user_meta($user->ID, $mndb->prefix . 'user_level', true);
			$role = translate_level_to_role($level);
			update_user_meta( $user->ID, $mndb->prefix . 'capabilities', array($role => true) );
		}

	endforeach;
	$old_user_fields = array( 'user_firstname', 'user_lastname', 'user_icq', 'user_aim', 'user_msn', 'user_yim', 'user_idmode', 'user_ip', 'user_domain', 'user_browser', 'user_description', 'user_nickname', 'user_level' );
	$mndb->hide_errors();
	foreach ( $old_user_fields as $old )
		$mndb->query("ALTER TABLE $mndb->users DROP $old");
	$mndb->show_errors();

	// Populate comment_count field of posts table.
	$comments = $mndb->get_results( "SELECT comment_post_ID, COUNT(*) as c FROM $mndb->comments WHERE comment_approved = '1' GROUP BY comment_post_ID" );
	if ( is_array( $comments ) )
		foreach ($comments as $comment)
			$mndb->update( $mndb->posts, array('comment_count' => $comment->c), array('ID' => $comment->comment_post_ID) );

	/*
	 * Some alpha versions used a post status of object instead of attachment
	 * and put the mime type in post_type instead of post_mime_type.
	 */
	if ( $mn_current_db_version > 2541 && $mn_current_db_version <= 3091 ) {
		$objects = $mndb->get_results("SELECT ID, post_type FROM $mndb->posts WHERE post_status = 'object'");
		foreach ($objects as $object) {
			$mndb->update( $mndb->posts, array(	'post_status' => 'attachment',
												'post_mime_type' => $object->post_type,
												'post_type' => ''),
										 array( 'ID' => $object->ID ) );

			$meta = get_post_meta($object->ID, 'imagedata', true);
			if ( ! empty($meta['file']) )
				update_attached_file( $object->ID, $meta['file'] );
		}
	}
}

/**
 * Execute changes made in Mtaandao 2.1.
 *
 * @ignore
 * @since 2.1.0
 *
 * @global mndb $mndb Mtaandao database abstraction object.
 * @global int  $mn_current_db_version
 */
function upgrade_210() {
	global $mndb, $mn_current_db_version;

	if ( $mn_current_db_version < 3506 ) {
		// Update status and type.
		$posts = $mndb->get_results("SELECT ID, post_status FROM $mndb->posts");

		if ( ! empty($posts) ) foreach ($posts as $post) {
			$status = $post->post_status;
			$type = 'post';

			if ( 'static' == $status ) {
				$status = 'publish';
				$type = 'page';
			} elseif ( 'attachment' == $status ) {
				$status = 'inherit';
				$type = 'attachment';
			}

			$mndb->query( $mndb->prepare("UPDATE $mndb->posts SET post_status = %s, post_type = %s WHERE ID = %d", $status, $type, $post->ID) );
		}
	}

	if ( $mn_current_db_version < 3845 ) {
		populate_roles_210();
	}

	if ( $mn_current_db_version < 3531 ) {
		// Give future posts a post_status of future.
		$now = gmdate('Y-m-d H:i:59');
		$mndb->query ("UPDATE $mndb->posts SET post_status = 'future' WHERE post_status = 'publish' AND post_date_gmt > '$now'");

		$posts = $mndb->get_results("SELECT ID, post_date FROM $mndb->posts WHERE post_status ='future'");
		if ( !empty($posts) )
			foreach ( $posts as $post )
				mn_schedule_single_event(mysql2date('U', $post->post_date, false), 'publish_future_post', array($post->ID));
	}
}

/**
 * Execute changes made in Mtaandao 2.3.
 *
 * @ignore
 * @since 2.3.0
 *
 * @global mndb $mndb Mtaandao database abstraction object.
 * @global int  $mn_current_db_version
 */
function upgrade_230() {
	global $mn_current_db_version, $mndb;

	if ( $mn_current_db_version < 5200 ) {
		populate_roles_230();
	}

	// Convert categories to terms.
	$tt_ids = array();
	$have_tags = false;
	$categories = $mndb->get_results("SELECT * FROM $mndb->categories ORDER BY cat_ID");
	foreach ($categories as $category) {
		$term_id = (int) $category->cat_ID;
		$name = $category->cat_name;
		$description = $category->category_description;
		$slug = $category->category_nicename;
		$parent = $category->category_parent;
		$term_group = 0;

		// Associate terms with the same slug in a term group and make slugs unique.
		if ( $exists = $mndb->get_results( $mndb->prepare("SELECT term_id, term_group FROM $mndb->terms WHERE slug = %s", $slug) ) ) {
			$term_group = $exists[0]->term_group;
			$id = $exists[0]->term_id;
			$num = 2;
			do {
				$alt_slug = $slug . "-$num";
				$num++;
				$slug_check = $mndb->get_var( $mndb->prepare("SELECT slug FROM $mndb->terms WHERE slug = %s", $alt_slug) );
			} while ( $slug_check );

			$slug = $alt_slug;

			if ( empty( $term_group ) ) {
				$term_group = $mndb->get_var("SELECT MAX(term_group) FROM $mndb->terms GROUP BY term_group") + 1;
				$mndb->query( $mndb->prepare("UPDATE $mndb->terms SET term_group = %d WHERE term_id = %d", $term_group, $id) );
			}
		}

		$mndb->query( $mndb->prepare("INSERT INTO $mndb->terms (term_id, name, slug, term_group) VALUES
		(%d, %s, %s, %d)", $term_id, $name, $slug, $term_group) );

		$count = 0;
		if ( !empty($category->category_count) ) {
			$count = (int) $category->category_count;
			$taxonomy = 'category';
			$mndb->query( $mndb->prepare("INSERT INTO $mndb->term_taxonomy (term_id, taxonomy, description, parent, count) VALUES ( %d, %s, %s, %d, %d)", $term_id, $taxonomy, $description, $parent, $count) );
			$tt_ids[$term_id][$taxonomy] = (int) $mndb->insert_id;
		}

		if ( !empty($category->link_count) ) {
			$count = (int) $category->link_count;
			$taxonomy = 'link_category';
			$mndb->query( $mndb->prepare("INSERT INTO $mndb->term_taxonomy (term_id, taxonomy, description, parent, count) VALUES ( %d, %s, %s, %d, %d)", $term_id, $taxonomy, $description, $parent, $count) );
			$tt_ids[$term_id][$taxonomy] = (int) $mndb->insert_id;
		}

		if ( !empty($category->tag_count) ) {
			$have_tags = true;
			$count = (int) $category->tag_count;
			$taxonomy = 'post_tag';
			$mndb->insert( $mndb->term_taxonomy, compact('term_id', 'taxonomy', 'description', 'parent', 'count') );
			$tt_ids[$term_id][$taxonomy] = (int) $mndb->insert_id;
		}

		if ( empty($count) ) {
			$count = 0;
			$taxonomy = 'category';
			$mndb->insert( $mndb->term_taxonomy, compact('term_id', 'taxonomy', 'description', 'parent', 'count') );
			$tt_ids[$term_id][$taxonomy] = (int) $mndb->insert_id;
		}
	}

	$select = 'post_id, category_id';
	if ( $have_tags )
		$select .= ', rel_type';

	$posts = $mndb->get_results("SELECT $select FROM $mndb->post2cat GROUP BY post_id, category_id");
	foreach ( $posts as $post ) {
		$post_id = (int) $post->post_id;
		$term_id = (int) $post->category_id;
		$taxonomy = 'category';
		if ( !empty($post->rel_type) && 'tag' == $post->rel_type)
			$taxonomy = 'tag';
		$tt_id = $tt_ids[$term_id][$taxonomy];
		if ( empty($tt_id) )
			continue;

		$mndb->insert( $mndb->term_relationships, array('object_id' => $post_id, 'term_taxonomy_id' => $tt_id) );
	}

	// < 3570 we used linkcategories. >= 3570 we used categories and link2cat.
	if ( $mn_current_db_version < 3570 ) {
		/*
		 * Create link_category terms for link categories. Create a map of link
		 * cat IDs to link_category terms.
		 */
		$link_cat_id_map = array();
		$default_link_cat = 0;
		$tt_ids = array();
		$link_cats = $mndb->get_results("SELECT cat_id, cat_name FROM " . $mndb->prefix . 'linkcategories');
		foreach ( $link_cats as $category) {
			$cat_id = (int) $category->cat_id;
			$term_id = 0;
			$name = mn_slash($category->cat_name);
			$slug = sanitize_title($name);
			$term_group = 0;

			// Associate terms with the same slug in a term group and make slugs unique.
			if ( $exists = $mndb->get_results( $mndb->prepare("SELECT term_id, term_group FROM $mndb->terms WHERE slug = %s", $slug) ) ) {
				$term_group = $exists[0]->term_group;
				$term_id = $exists[0]->term_id;
			}

			if ( empty($term_id) ) {
				$mndb->insert( $mndb->terms, compact('name', 'slug', 'term_group') );
				$term_id = (int) $mndb->insert_id;
			}

			$link_cat_id_map[$cat_id] = $term_id;
			$default_link_cat = $term_id;

			$mndb->insert( $mndb->term_taxonomy, array('term_id' => $term_id, 'taxonomy' => 'link_category', 'description' => '', 'parent' => 0, 'count' => 0) );
			$tt_ids[$term_id] = (int) $mndb->insert_id;
		}

		// Associate links to cats.
		$links = $mndb->get_results("SELECT link_id, link_category FROM $mndb->links");
		if ( !empty($links) ) foreach ( $links as $link ) {
			if ( 0 == $link->link_category )
				continue;
			if ( ! isset($link_cat_id_map[$link->link_category]) )
				continue;
			$term_id = $link_cat_id_map[$link->link_category];
			$tt_id = $tt_ids[$term_id];
			if ( empty($tt_id) )
				continue;

			$mndb->insert( $mndb->term_relationships, array('object_id' => $link->link_id, 'term_taxonomy_id' => $tt_id) );
		}

		// Set default to the last category we grabbed during the upgrade loop.
		update_option('default_link_category', $default_link_cat);
	} else {
		$links = $mndb->get_results("SELECT link_id, category_id FROM $mndb->link2cat GROUP BY link_id, category_id");
		foreach ( $links as $link ) {
			$link_id = (int) $link->link_id;
			$term_id = (int) $link->category_id;
			$taxonomy = 'link_category';
			$tt_id = $tt_ids[$term_id][$taxonomy];
			if ( empty($tt_id) )
				continue;
			$mndb->insert( $mndb->term_relationships, array('object_id' => $link_id, 'term_taxonomy_id' => $tt_id) );
		}
	}

	if ( $mn_current_db_version < 4772 ) {
		// Obsolete linkcategories table
		$mndb->query('DROP TABLE IF EXISTS ' . $mndb->prefix . 'linkcategories');
	}

	// Recalculate all counts
	$terms = $mndb->get_results("SELECT term_taxonomy_id, taxonomy FROM $mndb->term_taxonomy");
	foreach ( (array) $terms as $term ) {
		if ( ('post_tag' == $term->taxonomy) || ('category' == $term->taxonomy) )
			$count = $mndb->get_var( $mndb->prepare("SELECT COUNT(*) FROM $mndb->term_relationships, $mndb->posts WHERE $mndb->posts.ID = $mndb->term_relationships.object_id AND post_status = 'publish' AND post_type = 'post' AND term_taxonomy_id = %d", $term->term_taxonomy_id) );
		else
			$count = $mndb->get_var( $mndb->prepare("SELECT COUNT(*) FROM $mndb->term_relationships WHERE term_taxonomy_id = %d", $term->term_taxonomy_id) );
		$mndb->update( $mndb->term_taxonomy, array('count' => $count), array('term_taxonomy_id' => $term->term_taxonomy_id) );
	}
}

/**
 * Remove old options from the database.
 *
 * @ignore
 * @since 2.3.0
 *
 * @global mndb $mndb Mtaandao database abstraction object.
 */
function upgrade_230_options_table() {
	global $mndb;
	$old_options_fields = array( 'option_can_override', 'option_type', 'option_width', 'option_height', 'option_description', 'option_admin_level' );
	$mndb->hide_errors();
	foreach ( $old_options_fields as $old )
		$mndb->query("ALTER TABLE $mndb->options DROP $old");
	$mndb->show_errors();
}

/**
 * Remove old categories, link2cat, and post2cat database tables.
 *
 * @ignore
 * @since 2.3.0
 *
 * @global mndb $mndb Mtaandao database abstraction object.
 */
function upgrade_230_old_tables() {
	global $mndb;
	$mndb->query('DROP TABLE IF EXISTS ' . $mndb->prefix . 'categories');
	$mndb->query('DROP TABLE IF EXISTS ' . $mndb->prefix . 'link2cat');
	$mndb->query('DROP TABLE IF EXISTS ' . $mndb->prefix . 'post2cat');
}

/**
 * Upgrade old slugs made in version 2.2.
 *
 * @ignore
 * @since 2.2.0
 *
 * @global mndb $mndb Mtaandao database abstraction object.
 */
function upgrade_old_slugs() {
	// Upgrade people who were using the Redirect Old Slugs plugin.
	global $mndb;
	$mndb->query("UPDATE $mndb->postmeta SET meta_key = '_mn_old_slug' WHERE meta_key = 'old_slug'");
}

/**
 * Execute changes made in Mtaandao 2.5.0.
 *
 * @ignore
 * @since 2.5.0
 *
 * @global int $mn_current_db_version
 */
function upgrade_250() {
	global $mn_current_db_version;

	if ( $mn_current_db_version < 6689 ) {
		populate_roles_250();
	}

}

/**
 * Execute changes made in Mtaandao 2.5.2.
 *
 * @ignore
 * @since 2.5.2
 *
 * @global mndb $mndb Mtaandao database abstraction object.
 */
function upgrade_252() {
	global $mndb;

	$mndb->query("UPDATE $mndb->users SET user_activation_key = ''");
}

/**
 * Execute changes made in Mtaandao 2.6.
 *
 * @ignore
 * @since 2.6.0
 *
 * @global int $mn_current_db_version
 */
function upgrade_260() {
	global $mn_current_db_version;

	if ( $mn_current_db_version < 8000 )
		populate_roles_260();
}

/**
 * Execute changes made in Mtaandao 2.7.
 *
 * @ignore
 * @since 2.7.0
 *
 * @global mndb $mndb Mtaandao database abstraction object.
 * @global int  $mn_current_db_version
 */
function upgrade_270() {
	global $mndb, $mn_current_db_version;

	if ( $mn_current_db_version < 8980 )
		populate_roles_270();

	// Update post_date for unpublished posts with empty timestamp
	if ( $mn_current_db_version < 8921 )
		$mndb->query( "UPDATE $mndb->posts SET post_date = post_modified WHERE post_date = '0000-00-00 00:00:00'" );
}

/**
 * Execute changes made in Mtaandao 2.8.
 *
 * @ignore
 * @since 2.8.0
 *
 * @global int  $mn_current_db_version
 * @global mndb $mndb Mtaandao database abstraction object.
 */
function upgrade_280() {
	global $mn_current_db_version, $mndb;

	if ( $mn_current_db_version < 10360 )
		populate_roles_280();
	if ( is_multisite() ) {
		$start = 0;
		while( $rows = $mndb->get_results( "SELECT option_name, option_value FROM $mndb->options ORDER BY option_id LIMIT $start, 20" ) ) {
			foreach ( $rows as $row ) {
				$value = $row->option_value;
				if ( !@unserialize( $value ) )
					$value = stripslashes( $value );
				if ( $value !== $row->option_value ) {
					update_option( $row->option_name, $value );
				}
			}
			$start += 20;
		}
		refresh_blog_details( $mndb->blogid );
	}
}

/**
 * Execute changes made in Mtaandao 2.9.
 *
 * @ignore
 * @since 2.9.0
 *
 * @global int $mn_current_db_version
 */
function upgrade_290() {
	global $mn_current_db_version;

	if ( $mn_current_db_version < 11958 ) {
		// Previously, setting depth to 1 would redundantly disable threading, but now 2 is the minimum depth to avoid confusion
		if ( get_option( 'thread_comments_depth' ) == '1' ) {
			update_option( 'thread_comments_depth', 2 );
			update_option( 'thread_comments', 0 );
		}
	}
}

/**
 * Execute changes made in Mtaandao 3.0.
 *
 * @ignore
 * @since 3.0.0
 *
 * @global int  $mn_current_db_version
 * @global mndb $mndb Mtaandao database abstraction object.
 */
function upgrade_300() {
	global $mn_current_db_version, $mndb;

	if ( $mn_current_db_version < 15093 )
		populate_roles_300();

	if ( $mn_current_db_version < 14139 && is_multisite() && is_main_site() && ! defined( 'MULTISITE' ) && get_site_option( 'siteurl' ) === false )
		add_site_option( 'siteurl', '' );

	// 3.0 screen options key name changes.
	if ( mn_should_upgrade_global_tables() ) {
		$sql = "DELETE FROM $mndb->usermeta
			WHERE meta_key LIKE %s
			OR meta_key LIKE %s
			OR meta_key LIKE %s
			OR meta_key LIKE %s
			OR meta_key LIKE %s
			OR meta_key LIKE %s
			OR meta_key = 'manageedittagscolumnshidden'
			OR meta_key = 'managecategoriescolumnshidden'
			OR meta_key = 'manageedit-tagscolumnshidden'
			OR meta_key = 'manageeditcolumnshidden'
			OR meta_key = 'categories_per_page'
			OR meta_key = 'edit_tags_per_page'";
		$prefix = $mndb->esc_like( $mndb->base_prefix );
		$mndb->query( $mndb->prepare( $sql,
			$prefix . '%' . $mndb->esc_like( 'meta-box-hidden' ) . '%',
			$prefix . '%' . $mndb->esc_like( 'closedpostboxes' ) . '%',
			$prefix . '%' . $mndb->esc_like( 'manage-'	   ) . '%' . $mndb->esc_like( '-columns-hidden' ) . '%',
			$prefix . '%' . $mndb->esc_like( 'meta-box-order'  ) . '%',
			$prefix . '%' . $mndb->esc_like( 'metaboxorder'    ) . '%',
			$prefix . '%' . $mndb->esc_like( 'screen_layout'   ) . '%'
		) );
	}

}

/**
 * Execute changes made in Mtaandao 3.3.
 *
 * @ignore
 * @since 3.3.0
 *
 * @global int   $mn_current_db_version
 * @global mndb  $mndb
 * @global array $mn_registered_widgets
 * @global array $sidebars_widgets
 */
function upgrade_330() {
	global $mn_current_db_version, $mndb, $mn_registered_widgets, $sidebars_widgets;

	if ( $mn_current_db_version < 19061 && mn_should_upgrade_global_tables() ) {
		$mndb->query( "DELETE FROM $mndb->usermeta WHERE meta_key IN ('show_admin_bar_admin', 'plugins_last_view')" );
	}

	if ( $mn_current_db_version >= 11548 )
		return;

	$sidebars_widgets = get_option( 'sidebars_widgets', array() );
	$_sidebars_widgets = array();

	if ( isset($sidebars_widgets['mn_inactive_widgets']) || empty($sidebars_widgets) )
		$sidebars_widgets['array_version'] = 3;
	elseif ( !isset($sidebars_widgets['array_version']) )
		$sidebars_widgets['array_version'] = 1;

	switch ( $sidebars_widgets['array_version'] ) {
		case 1 :
			foreach ( (array) $sidebars_widgets as $index => $sidebar )
			if ( is_array($sidebar) )
			foreach ( (array) $sidebar as $i => $name ) {
				$id = strtolower($name);
				if ( isset($mn_registered_widgets[$id]) ) {
					$_sidebars_widgets[$index][$i] = $id;
					continue;
				}
				$id = sanitize_title($name);
				if ( isset($mn_registered_widgets[$id]) ) {
					$_sidebars_widgets[$index][$i] = $id;
					continue;
				}

				$found = false;

				foreach ( $mn_registered_widgets as $widget_id => $widget ) {
					if ( strtolower($widget['name']) == strtolower($name) ) {
						$_sidebars_widgets[$index][$i] = $widget['id'];
						$found = true;
						break;
					} elseif ( sanitize_title($widget['name']) == sanitize_title($name) ) {
						$_sidebars_widgets[$index][$i] = $widget['id'];
						$found = true;
						break;
					}
				}

				if ( $found )
					continue;

				unset($_sidebars_widgets[$index][$i]);
			}
			$_sidebars_widgets['array_version'] = 2;
			$sidebars_widgets = $_sidebars_widgets;
			unset($_sidebars_widgets);

		case 2 :
			$sidebars_widgets = retrieve_widgets();
			$sidebars_widgets['array_version'] = 3;
			update_option( 'sidebars_widgets', $sidebars_widgets );
	}
}

/**
 * Execute changes made in Mtaandao 3.4.
 *
 * @ignore
 * @since 3.4.0
 *
 * @global int   $mn_current_db_version
 * @global mndb  $mndb
 */
function upgrade_340() {
	global $mn_current_db_version, $mndb;

	if ( $mn_current_db_version < 19798 ) {
		$mndb->hide_errors();
		$mndb->query( "ALTER TABLE $mndb->options DROP COLUMN blog_id" );
		$mndb->show_errors();
	}

	if ( $mn_current_db_version < 19799 ) {
		$mndb->hide_errors();
		$mndb->query("ALTER TABLE $mndb->comments DROP INDEX comment_approved");
		$mndb->show_errors();
	}

	if ( $mn_current_db_version < 20022 && mn_should_upgrade_global_tables() ) {
		$mndb->query( "DELETE FROM $mndb->usermeta WHERE meta_key = 'themes_last_view'" );
	}

	if ( $mn_current_db_version < 20080 ) {
		if ( 'yes' == $mndb->get_var( "SELECT autoload FROM $mndb->options WHERE option_name = 'uninstall_plugins'" ) ) {
			$uninstall_plugins = get_option( 'uninstall_plugins' );
			delete_option( 'uninstall_plugins' );
			add_option( 'uninstall_plugins', $uninstall_plugins, null, 'no' );
		}
	}
}

/**
 * Execute changes made in Mtaandao 3.5.
 *
 * @ignore
 * @since 3.5.0
 *
 * @global int   $mn_current_db_version
 * @global mndb  $mndb
 */
function upgrade_350() {
	global $mn_current_db_version, $mndb;

	if ( $mn_current_db_version < 22006 && $mndb->get_var( "SELECT link_id FROM $mndb->links LIMIT 1" ) )
		update_option( 'link_manager_enabled', 1 ); // Previously set to 0 by populate_options()

	if ( $mn_current_db_version < 21811 && mn_should_upgrade_global_tables() ) {
		$meta_keys = array();
		foreach ( array_merge( get_post_types(), get_taxonomies() ) as $name ) {
			if ( false !== strpos( $name, '-' ) )
			$meta_keys[] = 'edit_' . str_replace( '-', '_', $name ) . '_per_page';
		}
		if ( $meta_keys ) {
			$meta_keys = implode( "', '", $meta_keys );
			$mndb->query( "DELETE FROM $mndb->usermeta WHERE meta_key IN ('$meta_keys')" );
		}
	}

	if ( $mn_current_db_version < 22422 && $term = get_term_by( 'slug', 'post-format-standard', 'post_format' ) )
		mn_delete_term( $term->term_id, 'post_format' );
}

/**
 * Execute changes made in Mtaandao 3.7.
 *
 * @ignore
 * @since 3.7.0
 *
 * @global int $mn_current_db_version
 */
function upgrade_370() {
	global $mn_current_db_version;
	if ( $mn_current_db_version < 25824 )
		mn_clear_scheduled_hook( 'mn_auto_updates_maybe_update' );
}

/**
 * Execute changes made in Mtaandao 3.7.2.
 *
 * @ignore
 * @since 3.7.2
 * @since 3.8.0
 *
 * @global int $mn_current_db_version
 */
function upgrade_372() {
	global $mn_current_db_version;
	if ( $mn_current_db_version < 26148 )
		mn_clear_scheduled_hook( 'mn_maybe_auto_update' );
}

/**
 * Execute changes made in Mtaandao 3.8.0.
 *
 * @ignore
 * @since 3.8.0
 *
 * @global int $mn_current_db_version
 */
function upgrade_380() {
	global $mn_current_db_version;
	if ( $mn_current_db_version < 26691 ) {
		deactivate_plugins( array( 'mp6/mp6.php' ), true );
	}
}

/**
 * Execute changes made in Mtaandao 4.0.0.
 *
 * @ignore
 * @since 4.0.0
 *
 * @global int $mn_current_db_version
 */
function upgrade_400() {
	global $mn_current_db_version;
	if ( $mn_current_db_version < 29630 ) {
		if ( ! is_multisite() && false === get_option( 'MNLANG' ) ) {
			if ( defined( 'MNLANG' ) && ( '' !== MNLANG ) && in_array( MNLANG, get_available_languages() ) ) {
				update_option( 'MNLANG', MNLANG );
			} else {
				update_option( 'MNLANG', '' );
			}
		}
	}
}

/**
 * Execute changes made in Mtaandao 4.2.0.
 *
 * @ignore
 * @since 4.2.0
 *
 * @global int   $mn_current_db_version
 * @global mndb  $mndb
 */
function upgrade_420() {}

/**
 * Executes changes made in Mtaandao 4.3.0.
 *
 * @ignore
 * @since 4.3.0
 *
 * @global int  $mn_current_db_version Current version.
 * @global mndb $mndb                  Mtaandao database abstraction object.
 */
function upgrade_430() {
	global $mn_current_db_version, $mndb;

	if ( $mn_current_db_version < 32364 ) {
		upgrade_430_fix_comments();
	}

	// Shared terms are split in a separate process.
	if ( $mn_current_db_version < 32814 ) {
		update_option( 'finished_splitting_shared_terms', 0 );
		mn_schedule_single_event( time() + ( 1 * MINUTE_IN_SECONDS ), 'mn_split_shared_term_batch' );
	}

	if ( $mn_current_db_version < 33055 && 'utf8mb4' === $mndb->charset ) {
		if ( is_multisite() ) {
			$tables = $mndb->tables( 'blog' );
		} else {
			$tables = $mndb->tables( 'all' );
			if ( ! mn_should_upgrade_global_tables() ) {
				$global_tables = $mndb->tables( 'global' );
				$tables = array_diff_assoc( $tables, $global_tables );
			}
		}

		foreach ( $tables as $table ) {
			maybe_convert_table_to_utf8mb4( $table );
		}
	}
}

/**
 * Executes comments changes made in Mtaandao 4.3.0.
 *
 * @ignore
 * @since 4.3.0
 *
 * @global int  $mn_current_db_version Current version.
 * @global mndb $mndb                  Mtaandao database abstraction object.
 */
function upgrade_430_fix_comments() {
	global $mn_current_db_version, $mndb;

	$content_length = $mndb->get_col_length( $mndb->comments, 'comment_content' );

	if ( is_mn_error( $content_length ) ) {
		return;
	}

	if ( false === $content_length ) {
		$content_length = array(
			'type'   => 'byte',
			'length' => 65535,
		);
	} elseif ( ! is_array( $content_length ) ) {
		$length = (int) $content_length > 0 ? (int) $content_length : 65535;
		$content_length = array(
			'type'	 => 'byte',
			'length' => $length
		);
	}

	if ( 'byte' !== $content_length['type'] || 0 === $content_length['length'] ) {
		// Sites with malformed DB schemas are on their own.
		return;
	}

	$allowed_length = intval( $content_length['length'] ) - 10;

	$comments = $mndb->get_results(
		"SELECT `comment_ID` FROM `{$mndb->comments}`
			WHERE `comment_date_gmt` > '2015-04-26'
			AND LENGTH( `comment_content` ) >= {$allowed_length}
			AND ( `comment_content` LIKE '%<%' OR `comment_content` LIKE '%>%' )"
	);

	foreach ( $comments as $comment ) {
		mn_delete_comment( $comment->comment_ID, true );
	}
}

/**
 * Executes changes made in Mtaandao 4.3.1.
 *
 * @ignore
 * @since 4.3.1
 */
function upgrade_431() {
	// Fix incorrect cron entries for term splitting
	$cron_array = _get_cron_array();
	if ( isset( $cron_array['mn_batch_split_terms'] ) ) {
		unset( $cron_array['mn_batch_split_terms'] );
		_set_cron_array( $cron_array );
	}
}

/**
 * Executes changes made in Mtaandao 4.4.0.
 *
 * @ignore
 * @since 4.4.0
 *
 * @global int  $mn_current_db_version Current version.
 * @global mndb $mndb                  Mtaandao database abstraction object.
 */
function upgrade_440() {
	global $mn_current_db_version, $mndb;

	if ( $mn_current_db_version < 34030 ) {
		$mndb->query( "ALTER TABLE {$mndb->options} MODIFY option_name VARCHAR(191)" );
	}

	// Remove the unused 'add_users' role.
	$roles = mn_roles();
	foreach ( $roles->role_objects as $role ) {
		if ( $role->has_cap( 'add_users' ) ) {
			$role->remove_cap( 'add_users' );
		}
	}
}

/**
 * Executes changes made in Mtaandao 4.5.0.
 *
 * @ignore
 * @since 4.5.0
 *
 * @global int  $mn_current_db_version Current database version.
 * @global mndb $mndb                  Mtaandao database abstraction object.
 */
function upgrade_450() {
	global $mn_current_db_version, $mndb;

	if ( $mn_current_db_version < 36180 ) {
		mn_clear_scheduled_hook( 'mn_maybe_auto_update' );
	}

	// Remove unused email confirmation options, moved to usermeta.
	if ( $mn_current_db_version < 36679 && is_multisite() ) {
		$mndb->query( "DELETE FROM $mndb->options WHERE option_name REGEXP '^[0-9]+_new_email$'" );
	}

	// Remove unused user setting for mnLink.
	delete_user_setting( 'mnlink' );
}

/**
 * Executes changes made in Mtaandao 4.6.0.
 *
 * @ignore
 * @since 4.6.0
 *
 * @global int $mn_current_db_version Current database version.
 */
function upgrade_460() {
	global $mn_current_db_version;

	// Remove unused post meta.
	if ( $mn_current_db_version < 37854 ) {
		delete_post_meta_by_key( '_post_restored_from' );
	}

	// Remove plugins with callback as an array object/method as the uninstall hook, see #13786.
	if ( $mn_current_db_version < 37965 ) {
		$uninstall_plugins = get_option( 'uninstall_plugins', array() );

		if ( ! empty( $uninstall_plugins ) ) {
			foreach ( $uninstall_plugins as $basename => $callback ) {
				if ( is_array( $callback ) && is_object( $callback[0] ) ) {
					unset( $uninstall_plugins[ $basename ] );
				}
			}

			update_option( 'uninstall_plugins', $uninstall_plugins );
		}
	}
}

/**
 * Executes network-level upgrade routines.
 *
 * @since 3.0.0
 *
 * @global int   $mn_current_db_version
 * @global mndb  $mndb
 */
function upgrade_network() {
	global $mn_current_db_version, $mndb;

	// Always.
	if ( is_main_network() ) {
		/*
		 * Deletes all expired transients. The multi-table delete syntax is used
		 * to delete the transient record from table a, and the corresponding
		 * transient_timeout record from table b.
		 */
		$time = time();
		$sql = "DELETE a, b FROM $mndb->sitemeta a, $mndb->sitemeta b
			WHERE a.meta_key LIKE %s
			AND a.meta_key NOT LIKE %s
			AND b.meta_key = CONCAT( '_site_transient_timeout_', SUBSTRING( a.meta_key, 17 ) )
			AND b.meta_value < %d";
		$mndb->query( $mndb->prepare( $sql, $mndb->esc_like( '_site_transient_' ) . '%', $mndb->esc_like ( '_site_transient_timeout_' ) . '%', $time ) );
	}

	// 2.8.
	if ( $mn_current_db_version < 11549 ) {
		$mnmu_sitewide_plugins = get_site_option( 'mnmu_sitewide_plugins' );
		$active_sitewide_plugins = get_site_option( 'active_sitewide_plugins' );
		if ( $mnmu_sitewide_plugins ) {
			if ( !$active_sitewide_plugins )
				$sitewide_plugins = (array) $mnmu_sitewide_plugins;
			else
				$sitewide_plugins = array_merge( (array) $active_sitewide_plugins, (array) $mnmu_sitewide_plugins );

			update_site_option( 'active_sitewide_plugins', $sitewide_plugins );
		}
		delete_site_option( 'mnmu_sitewide_plugins' );
		delete_site_option( 'deactivated_sitewide_plugins' );

		$start = 0;
		while( $rows = $mndb->get_results( "SELECT meta_key, meta_value FROM {$mndb->sitemeta} ORDER BY meta_id LIMIT $start, 20" ) ) {
			foreach ( $rows as $row ) {
				$value = $row->meta_value;
				if ( !@unserialize( $value ) )
					$value = stripslashes( $value );
				if ( $value !== $row->meta_value ) {
					update_site_option( $row->meta_key, $value );
				}
			}
			$start += 20;
		}
	}

	// 3.0
	if ( $mn_current_db_version < 13576 )
		update_site_option( 'global_terms_enabled', '1' );

	// 3.3
	if ( $mn_current_db_version < 19390 )
		update_site_option( 'initial_db_version', $mn_current_db_version );

	if ( $mn_current_db_version < 19470 ) {
		if ( false === get_site_option( 'active_sitewide_plugins' ) )
			update_site_option( 'active_sitewide_plugins', array() );
	}

	// 3.4
	if ( $mn_current_db_version < 20148 ) {
		// 'allowedthemes' keys things by stylesheet. 'allowed_themes' keyed things by name.
		$allowedthemes  = get_site_option( 'allowedthemes'  );
		$allowed_themes = get_site_option( 'allowed_themes' );
		if ( false === $allowedthemes && is_array( $allowed_themes ) && $allowed_themes ) {
			$converted = array();
			$themes = mn_get_themes();
			foreach ( $themes as $stylesheet => $theme_data ) {
				if ( isset( $allowed_themes[ $theme_data->get('Name') ] ) )
					$converted[ $stylesheet ] = true;
			}
			update_site_option( 'allowedthemes', $converted );
			delete_site_option( 'allowed_themes' );
		}
	}

	// 3.5
	if ( $mn_current_db_version < 21823 )
		update_site_option( 'ms_files_rewriting', '1' );

	// 3.5.2
	if ( $mn_current_db_version < 24448 ) {
		$illegal_names = get_site_option( 'illegal_names' );
		if ( is_array( $illegal_names ) && count( $illegal_names ) === 1 ) {
			$illegal_name = reset( $illegal_names );
			$illegal_names = explode( ' ', $illegal_name );
			update_site_option( 'illegal_names', $illegal_names );
		}
	}

	// 4.2
	if ( $mn_current_db_version < 31351 && $mndb->charset === 'utf8mb4' ) {
		if ( mn_should_upgrade_global_tables() ) {
			$mndb->query( "ALTER TABLE $mndb->usermeta DROP INDEX meta_key, ADD INDEX meta_key(meta_key(191))" );
			$mndb->query( "ALTER TABLE $mndb->site DROP INDEX domain, ADD INDEX domain(domain(140),path(51))" );
			$mndb->query( "ALTER TABLE $mndb->sitemeta DROP INDEX meta_key, ADD INDEX meta_key(meta_key(191))" );
			$mndb->query( "ALTER TABLE $mndb->signups DROP INDEX domain_path, ADD INDEX domain_path(domain(140),path(51))" );

			$tables = $mndb->tables( 'global' );

			// sitecategories may not exist.
			if ( ! $mndb->get_var( "SHOW TABLES LIKE '{$tables['sitecategories']}'" ) ) {
				unset( $tables['sitecategories'] );
			}

			foreach ( $tables as $table ) {
				maybe_convert_table_to_utf8mb4( $table );
			}
		}
	}

	// 4.3
	if ( $mn_current_db_version < 33055 && 'utf8mb4' === $mndb->charset ) {
		if ( mn_should_upgrade_global_tables() ) {
			$upgrade = false;
			$indexes = $mndb->get_results( "SHOW INDEXES FROM $mndb->signups" );
			foreach ( $indexes as $index ) {
				if ( 'domain_path' == $index->Key_name && 'domain' == $index->Column_name && 140 != $index->Sub_part ) {
					$upgrade = true;
					break;
				}
			}

			if ( $upgrade ) {
				$mndb->query( "ALTER TABLE $mndb->signups DROP INDEX domain_path, ADD INDEX domain_path(domain(140),path(51))" );
			}

			$tables = $mndb->tables( 'global' );

			// sitecategories may not exist.
			if ( ! $mndb->get_var( "SHOW TABLES LIKE '{$tables['sitecategories']}'" ) ) {
				unset( $tables['sitecategories'] );
			}

			foreach ( $tables as $table ) {
				maybe_convert_table_to_utf8mb4( $table );
			}
		}
	}
}

//
// General functions we use to actually do stuff
//

/**
 * Creates a table in the database if it doesn't already exist.
 *
 * This method checks for an existing database and creates a new one if it's not
 * already present. It doesn't rely on MySQL's "IF NOT EXISTS" statement, but chooses
 * to query all tables first and then run the SQL statement creating the table.
 *
 * @since 1.0.0
 *
 * @global mndb  $mndb
 *
 * @param string $table_name Database table name to create.
 * @param string $create_ddl SQL statement to create table.
 * @return bool If table already exists or was created by function.
 */
function maybe_create_table($table_name, $create_ddl) {
	global $mndb;

	$query = $mndb->prepare( "SHOW TABLES LIKE %s", $mndb->esc_like( $table_name ) );

	if ( $mndb->get_var( $query ) == $table_name ) {
		return true;
	}

	// Didn't find it try to create it..
	$mndb->query($create_ddl);

	// We cannot directly tell that whether this succeeded!
	if ( $mndb->get_var( $query ) == $table_name ) {
		return true;
	}
	return false;
}

/**
 * Drops a specified index from a table.
 *
 * @since 1.0.1
 *
 * @global mndb  $mndb
 *
 * @param string $table Database table name.
 * @param string $index Index name to drop.
 * @return true True, when finished.
 */
function drop_index($table, $index) {
	global $mndb;
	$mndb->hide_errors();
	$mndb->query("ALTER TABLE `$table` DROP INDEX `$index`");
	// Now we need to take out all the extra ones we may have created
	for ($i = 0; $i < 25; $i++) {
		$mndb->query("ALTER TABLE `$table` DROP INDEX `{$index}_$i`");
	}
	$mndb->show_errors();
	return true;
}

/**
 * Adds an index to a specified table.
 *
 * @since 1.0.1
 *
 * @global mndb  $mndb
 *
 * @param string $table Database table name.
 * @param string $index Database table index column.
 * @return true True, when done with execution.
 */
function add_clean_index($table, $index) {
	global $mndb;
	drop_index($table, $index);
	$mndb->query("ALTER TABLE `$table` ADD INDEX ( `$index` )");
	return true;
}

/**
 * Adds column to a database table if it doesn't already exist.
 *
 * @since 1.3.0
 *
 * @global mndb  $mndb
 *
 * @param string $table_name  The table name to modify.
 * @param string $column_name The column name to add to the table.
 * @param string $create_ddl  The SQL statement used to add the column.
 * @return bool True if already exists or on successful completion, false on error.
 */
function maybe_add_column($table_name, $column_name, $create_ddl) {
	global $mndb;
	foreach ($mndb->get_col("DESC $table_name", 0) as $column ) {
		if ($column == $column_name) {
			return true;
		}
	}

	// Didn't find it try to create it.
	$mndb->query($create_ddl);

	// We cannot directly tell that whether this succeeded!
	foreach ($mndb->get_col("DESC $table_name", 0) as $column ) {
		if ($column == $column_name) {
			return true;
		}
	}
	return false;
}

/**
 * If a table only contains utf8 or utf8mb4 columns, convert it to utf8mb4.
 *
 * @since 4.2.0
 *
 * @global mndb  $mndb
 *
 * @param string $table The table to convert.
 * @return bool true if the table was converted, false if it wasn't.
 */
function maybe_convert_table_to_utf8mb4( $table ) {
	global $mndb;

	$results = $mndb->get_results( "SHOW FULL COLUMNS FROM `$table`" );
	if ( ! $results ) {
		return false;
	}

	foreach ( $results as $column ) {
		if ( $column->Collation ) {
			list( $charset ) = explode( '_', $column->Collation );
			$charset = strtolower( $charset );
			if ( 'utf8' !== $charset && 'utf8mb4' !== $charset ) {
				// Don't upgrade tables that have non-utf8 columns.
				return false;
			}
		}
	}

	$table_details = $mndb->get_row( "SHOW TABLE STATUS LIKE '$table'" );
	if ( ! $table_details ) {
		return false;
	}

	list( $table_charset ) = explode( '_', $table_details->Collation );
	$table_charset = strtolower( $table_charset );
	if ( 'utf8mb4' === $table_charset ) {
		return true;
	}

	return $mndb->query( "ALTER TABLE $table CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci" );
}

/**
 * Retrieve all options as it was for 1.2.
 *
 * @since 1.2.0
 *
 * @global mndb  $mndb
 *
 * @return stdClass List of options.
 */
function get_alloptions_110() {
	global $mndb;
	$all_options = new stdClass;
	if ( $options = $mndb->get_results( "SELECT option_name, option_value FROM $mndb->options" ) ) {
		foreach ( $options as $option ) {
			if ( 'siteurl' == $option->option_name || 'home' == $option->option_name || 'category_base' == $option->option_name )
				$option->option_value = untrailingslashit( $option->option_value );
			$all_options->{$option->option_name} = stripslashes( $option->option_value );
		}
	}
	return $all_options;
}

/**
 * Utility version of get_option that is private to install/upgrade.
 *
 * @ignore
 * @since 1.5.1
 * @access private
 *
 * @global mndb  $mndb
 *
 * @param string $setting Option name.
 * @return mixed
 */
function __get_option($setting) {
	global $mndb;

	if ( $setting == 'home' && defined( 'MN_HOME' ) )
		return untrailingslashit( MN_HOME );

	if ( $setting == 'siteurl' && defined( 'MN_SITEURL' ) )
		return untrailingslashit( MN_SITEURL );

	$option = $mndb->get_var( $mndb->prepare("SELECT option_value FROM $mndb->options WHERE option_name = %s", $setting ) );

	if ( 'home' == $setting && '' == $option )
		return __get_option( 'siteurl' );

	if ( 'siteurl' == $setting || 'home' == $setting || 'category_base' == $setting || 'tag_base' == $setting )
		$option = untrailingslashit( $option );

	return maybe_unserialize( $option );
}

/**
 * Filters for content to remove unnecessary slashes.
 *
 * @since 1.5.0
 *
 * @param string $content The content to modify.
 * @return string The de-slashed content.
 */
function deslash($content) {
	// Note: \\\ inside a regex denotes a single backslash.

	/*
	 * Replace one or more backslashes followed by a single quote with
	 * a single quote.
	 */
	$content = preg_replace("/\\\+'/", "'", $content);

	/*
	 * Replace one or more backslashes followed by a double quote with
	 * a double quote.
	 */
	$content = preg_replace('/\\\+"/', '"', $content);

	// Replace one or more backslashes with one backslash.
	$content = preg_replace("/\\\+/", "\\", $content);

	return $content;
}

/**
 * Modifies the database based on specified SQL statements.
 *
 * Useful for creating new tables and updating existing tables to a new structure.
 *
 * @since 1.5.0
 *
 * @global mndb  $mndb
 *
 * @param string|array $queries Optional. The query to run. Can be multiple queries
 *                              in an array, or a string of queries separated by
 *                              semicolons. Default empty.
 * @param bool         $execute Optional. Whether or not to execute the query right away.
 *                              Default true.
 * @return array Strings containing the results of the various update queries.
 */
function dbDelta( $queries = '', $execute = true ) {
	global $mndb;

	if ( in_array( $queries, array( '', 'all', 'blog', 'global', 'ms_global' ), true ) )
	    $queries = mn_get_db_schema( $queries );

	// Separate individual queries into an array
	if ( !is_array($queries) ) {
		$queries = explode( ';', $queries );
		$queries = array_filter( $queries );
	}

	/**
	 * Filters the dbDelta SQL queries.
	 *
	 * @since 3.3.0
	 *
	 * @param array $queries An array of dbDelta SQL queries.
	 */
	$queries = apply_filters( 'dbdelta_queries', $queries );

	$cqueries = array(); // Creation Queries
	$iqueries = array(); // Insertion Queries
	$for_update = array();

	// Create a tablename index for an array ($cqueries) of queries
	foreach ($queries as $qry) {
		if ( preg_match( "|CREATE TABLE ([^ ]*)|", $qry, $matches ) ) {
			$cqueries[ trim( $matches[1], '`' ) ] = $qry;
			$for_update[$matches[1]] = 'Created table '.$matches[1];
		} elseif ( preg_match( "|CREATE DATABASE ([^ ]*)|", $qry, $matches ) ) {
			array_unshift( $cqueries, $qry );
		} elseif ( preg_match( "|INSERT INTO ([^ ]*)|", $qry, $matches ) ) {
			$iqueries[] = $qry;
		} elseif ( preg_match( "|UPDATE ([^ ]*)|", $qry, $matches ) ) {
			$iqueries[] = $qry;
		} else {
			// Unrecognized query type
		}
	}

	/**
	 * Filters the dbDelta SQL queries for creating tables and/or databases.
	 *
	 * Queries filterable via this hook contain "CREATE TABLE" or "CREATE DATABASE".
	 *
	 * @since 3.3.0
	 *
	 * @param array $cqueries An array of dbDelta create SQL queries.
	 */
	$cqueries = apply_filters( 'dbdelta_create_queries', $cqueries );

	/**
	 * Filters the dbDelta SQL queries for inserting or updating.
	 *
	 * Queries filterable via this hook contain "INSERT INTO" or "UPDATE".
	 *
	 * @since 3.3.0
	 *
	 * @param array $iqueries An array of dbDelta insert or update SQL queries.
	 */
	$iqueries = apply_filters( 'dbdelta_insert_queries', $iqueries );

	$text_fields = array( 'tinytext', 'text', 'mediumtext', 'longtext' );
	$blob_fields = array( 'tinyblob', 'blob', 'mediumblob', 'longblob' );

	$global_tables = $mndb->tables( 'global' );
	foreach ( $cqueries as $table => $qry ) {
		// Upgrade global tables only for the main site. Don't upgrade at all if conditions are not optimal.
		if ( in_array( $table, $global_tables ) && ! mn_should_upgrade_global_tables() ) {
			unset( $cqueries[ $table ], $for_update[ $table ] );
			continue;
		}

		// Fetch the table column structure from the database
		$suppress = $mndb->suppress_errors();
		$tablefields = $mndb->get_results("DESCRIBE {$table};");
		$mndb->suppress_errors( $suppress );

		if ( ! $tablefields )
			continue;

		// Clear the field and index arrays.
		$cfields = $indices = array();

		// Get all of the field names in the query from between the parentheses.
		preg_match("|\((.*)\)|ms", $qry, $match2);
		$qryline = trim($match2[1]);

		// Separate field lines into an array.
		$flds = explode("\n", $qryline);

		// For every field line specified in the query.
		foreach ( $flds as $fld ) {
			$fld = trim( $fld, " \t\n\r\0\x0B," ); // Default trim characters, plus ','.

			// Extract the field name.
			preg_match( '|^([^ ]*)|', $fld, $fvals );
			$fieldname = trim( $fvals[1], '`' );
			$fieldname_lowercased = strtolower( $fieldname );

			// Verify the found field name.
			$validfield = true;
			switch ( $fieldname_lowercased ) {
				case '':
				case 'primary':
				case 'index':
				case 'fulltext':
				case 'unique':
				case 'key':
				case 'spatial':
					$validfield = false;

					/*
					 * Normalize the index definition.
					 *
					 * This is done so the definition can be compared against the result of a
					 * `SHOW INDEX FROM $table_name` query which returns the current table
					 * index information.
					 */

					// Extract type, name and columns from the definition.
					preg_match(
						  '/^'
						.   '(?P<index_type>'             // 1) Type of the index.
						.       'PRIMARY\s+KEY|(?:UNIQUE|FULLTEXT|SPATIAL)\s+(?:KEY|INDEX)|KEY|INDEX'
						.   ')'
						.   '\s+'                         // Followed by at least one white space character.
						.   '(?:'                         // Name of the index. Optional if type is PRIMARY KEY.
						.       '`?'                      // Name can be escaped with a backtick.
						.           '(?P<index_name>'     // 2) Name of the index.
						.               '(?:[0-9a-zA-Z$_-]|[\xC2-\xDF][\x80-\xBF])+'
						.           ')'
						.       '`?'                      // Name can be escaped with a backtick.
						.       '\s+'                     // Followed by at least one white space character.
						.   ')*'
						.   '\('                          // Opening bracket for the columns.
						.       '(?P<index_columns>'
						.           '.+?'                 // 3) Column names, index prefixes, and orders.
						.       ')'
						.   '\)'                          // Closing bracket for the columns.
						. '$/im',
						$fld,
						$index_matches
					);

					// Uppercase the index type and normalize space characters.
					$index_type = strtoupper( preg_replace( '/\s+/', ' ', trim( $index_matches['index_type'] ) ) );

					// 'INDEX' is a synonym for 'KEY', standardize on 'KEY'.
					$index_type = str_replace( 'INDEX', 'KEY', $index_type );

					// Escape the index name with backticks. An index for a primary key has no name.
					$index_name = ( 'PRIMARY KEY' === $index_type ) ? '' : '`' . strtolower( $index_matches['index_name'] ) . '`';

					// Parse the columns. Multiple columns are separated by a comma.
					$index_columns = array_map( 'trim', explode( ',', $index_matches['index_columns'] ) );

					// Normalize columns.
					foreach ( $index_columns as &$index_column ) {
						// Extract column name and number of indexed characters (sub_part).
						preg_match(
							  '/'
							.   '`?'                      // Name can be escaped with a backtick.
							.       '(?P<column_name>'    // 1) Name of the column.
							.           '(?:[0-9a-zA-Z$_-]|[\xC2-\xDF][\x80-\xBF])+'
							.       ')'
							.   '`?'                      // Name can be escaped with a backtick.
							.   '(?:'                     // Optional sub part.
							.       '\s*'                 // Optional white space character between name and opening bracket.
							.       '\('                  // Opening bracket for the sub part.
							.           '\s*'             // Optional white space character after opening bracket.
							.           '(?P<sub_part>'
							.               '\d+'         // 2) Number of indexed characters.
							.           ')'
							.           '\s*'             // Optional white space character before closing bracket.
							.        '\)'                 // Closing bracket for the sub part.
							.   ')?'
							. '/',
							$index_column,
							$index_column_matches
						);

						// Escape the column name with backticks.
						$index_column = '`' . $index_column_matches['column_name'] . '`';

						// Append the optional sup part with the number of indexed characters.
						if ( isset( $index_column_matches['sub_part'] ) ) {
							$index_column .= '(' . $index_column_matches['sub_part'] . ')';
						}
					}

					// Build the normalized index definition and add it to the list of indices.
					$indices[] = "{$index_type} {$index_name} (" . implode( ',', $index_columns ) . ")";

					// Destroy no longer needed variables.
					unset( $index_column, $index_column_matches, $index_matches, $index_type, $index_name, $index_columns );

					break;
			}

			// If it's a valid field, add it to the field array.
			if ( $validfield ) {
				$cfields[ $fieldname_lowercased ] = $fld;
			}
		}

		// For every field in the table.
		foreach ( $tablefields as $tablefield ) {
			$tablefield_field_lowercased = strtolower( $tablefield->Field );
			$tablefield_type_lowercased = strtolower( $tablefield->Type );

			// If the table field exists in the field array ...
			if ( array_key_exists( $tablefield_field_lowercased, $cfields ) ) {

				// Get the field type from the query.
				preg_match( '|`?' . $tablefield->Field . '`? ([^ ]*( unsigned)?)|i', $cfields[ $tablefield_field_lowercased ], $matches );
				$fieldtype = $matches[1];
				$fieldtype_lowercased = strtolower( $fieldtype );

				// Is actual field type different from the field type in query?
				if ($tablefield->Type != $fieldtype) {
					$do_change = true;
					if ( in_array( $fieldtype_lowercased, $text_fields ) && in_array( $tablefield_type_lowercased, $text_fields ) ) {
						if ( array_search( $fieldtype_lowercased, $text_fields ) < array_search( $tablefield_type_lowercased, $text_fields ) ) {
							$do_change = false;
						}
					}

					if ( in_array( $fieldtype_lowercased, $blob_fields ) && in_array( $tablefield_type_lowercased, $blob_fields ) ) {
						if ( array_search( $fieldtype_lowercased, $blob_fields ) < array_search( $tablefield_type_lowercased, $blob_fields ) ) {
							$do_change = false;
						}
					}

					if ( $do_change ) {
						// Add a query to change the column type.
						$cqueries[] = "ALTER TABLE {$table} CHANGE COLUMN `{$tablefield->Field}` " . $cfields[ $tablefield_field_lowercased ];
						$for_update[$table.'.'.$tablefield->Field] = "Changed type of {$table}.{$tablefield->Field} from {$tablefield->Type} to {$fieldtype}";
					}
				}

				// Get the default value from the array.
				if ( preg_match( "| DEFAULT '(.*?)'|i", $cfields[ $tablefield_field_lowercased ], $matches ) ) {
					$default_value = $matches[1];
					if ($tablefield->Default != $default_value) {
						// Add a query to change the column's default value
						$cqueries[] = "ALTER TABLE {$table} ALTER COLUMN `{$tablefield->Field}` SET DEFAULT '{$default_value}'";
						$for_update[$table.'.'.$tablefield->Field] = "Changed default value of {$table}.{$tablefield->Field} from {$tablefield->Default} to {$default_value}";
					}
				}

				// Remove the field from the array (so it's not added).
				unset( $cfields[ $tablefield_field_lowercased ] );
			} else {
				// This field exists in the table, but not in the creation queries?
			}
		}

		// For every remaining field specified for the table.
		foreach ($cfields as $fieldname => $fielddef) {
			// Push a query line into $cqueries that adds the field to that table.
			$cqueries[] = "ALTER TABLE {$table} ADD COLUMN $fielddef";
			$for_update[$table.'.'.$fieldname] = 'Added column '.$table.'.'.$fieldname;
		}

		// Index stuff goes here. Fetch the table index structure from the database.
		$tableindices = $mndb->get_results("SHOW INDEX FROM {$table};");

		if ($tableindices) {
			// Clear the index array.
			$index_ary = array();

			// For every index in the table.
			foreach ($tableindices as $tableindex) {

				// Add the index to the index data array.
				$keyname = strtolower( $tableindex->Key_name );
				$index_ary[$keyname]['columns'][] = array('fieldname' => $tableindex->Column_name, 'subpart' => $tableindex->Sub_part);
				$index_ary[$keyname]['unique'] = ($tableindex->Non_unique == 0)?true:false;
				$index_ary[$keyname]['index_type'] = $tableindex->Index_type;
			}

			// For each actual index in the index array.
			foreach ($index_ary as $index_name => $index_data) {

				// Build a create string to compare to the query.
				$index_string = '';
				if ($index_name == 'primary') {
					$index_string .= 'PRIMARY ';
				} elseif ( $index_data['unique'] ) {
					$index_string .= 'UNIQUE ';
				}
				if ( 'FULLTEXT' === strtoupper( $index_data['index_type'] ) ) {
					$index_string .= 'FULLTEXT ';
				}
				if ( 'SPATIAL' === strtoupper( $index_data['index_type'] ) ) {
					$index_string .= 'SPATIAL ';
				}
				$index_string .= 'KEY ';
				if ( 'primary' !== $index_name  ) {
					$index_string .= '`' . $index_name . '`';
				}
				$index_columns = '';

				// For each column in the index.
				foreach ($index_data['columns'] as $column_data) {
					if ( $index_columns != '' ) {
						$index_columns .= ',';
					}

					// Add the field to the column list string.
					$index_columns .= '`' . $column_data['fieldname'] . '`';
					if ($column_data['subpart'] != '') {
						$index_columns .= '('.$column_data['subpart'].')';
					}
				}

				// The alternative index string doesn't care about subparts
				$alt_index_columns = preg_replace( '/\([^)]*\)/', '', $index_columns );

				// Add the column list to the index create string.
				$index_strings = array(
					"$index_string ($index_columns)",
					"$index_string ($alt_index_columns)",
				);

				foreach ( $index_strings as $index_string ) {
					if ( ! ( ( $aindex = array_search( $index_string, $indices ) ) === false ) ) {
						unset( $indices[ $aindex ] );
						break;
					}
				}
			}
		}

		// For every remaining index specified for the table.
		foreach ( (array) $indices as $index ) {
			// Push a query line into $cqueries that adds the index to that table.
			$cqueries[] = "ALTER TABLE {$table} ADD $index";
			$for_update[] = 'Added index ' . $table . ' ' . $index;
		}

		// Remove the original table creation query from processing.
		unset( $cqueries[ $table ], $for_update[ $table ] );
	}

	$allqueries = array_merge($cqueries, $iqueries);
	if ($execute) {
		foreach ($allqueries as $query) {
			$mndb->query($query);
		}
	}

	return $for_update;
}

/**
 * Updates the database tables to a new schema.
 *
 * By default, updates all the tables to use the latest defined schema, but can also
 * be used to update a specific set of tables in mn_get_db_schema().
 *
 * @since 1.5.0
 *
 * @uses dbDelta
 *
 * @param string $tables Optional. Which set of tables to update. Default is 'all'.
 */
function make_db_current( $tables = 'all' ) {
	$alterations = dbDelta( $tables );
	echo "<ol>\n";
	foreach ($alterations as $alteration) echo "<li>$alteration</li>\n";
	echo "</ol>\n";
}

/**
 * Updates the database tables to a new schema, but without displaying results.
 *
 * By default, updates all the tables to use the latest defined schema, but can
 * also be used to update a specific set of tables in mn_get_db_schema().
 *
 * @since 1.5.0
 *
 * @see make_db_current()
 *
 * @param string $tables Optional. Which set of tables to update. Default is 'all'.
 */
function make_db_current_silent( $tables = 'all' ) {
	dbDelta( $tables );
}

/**
 * Creates a site theme from an existing theme.
 *
 * {@internal Missing Long Description}}
 *
 * @since 1.5.0
 *
 * @param string $theme_name The name of the theme.
 * @param string $template   The directory name of the theme.
 * @return bool
 */
function make_site_theme_from_oldschool($theme_name, $template) {
	$home_path = get_home_path();
	$site_dir = MAIN_DIR . "/themes/$template";

	if (! file_exists("$home_path/index.php"))
		return false;

	/*
	 * Copy files from the old locations to the site theme.
	 * TODO: This does not copy arbitrary include dependencies. Only the standard MN files are copied.
	 */
	$files = array('index.php' => 'index.php', 'mn-layout.css' => 'style.css', 'mn-comments.php' => 'comments.php', 'mn-comments-popup.php' => 'comments-popup.php');

	foreach ($files as $oldfile => $newfile) {
		if ($oldfile == 'index.php')
			$oldpath = $home_path;
		else
			$oldpath = ABSPATH;

		// Check to make sure it's not a new index.
		if ($oldfile == 'index.php') {
			$index = implode('', file("$oldpath/$oldfile"));
			if (strpos($index, 'MN_USE_THEMES') !== false) {
				if (! @copy(MAIN_DIR . '/themes/' . DEFAULT_THEME . '/index.php', "$site_dir/$newfile"))
					return false;

				// Don't copy anything.
				continue;
			}
		}

		if (! @copy("$oldpath/$oldfile", "$site_dir/$newfile"))
			return false;

		chmod("$site_dir/$newfile", 0777);

		// Update the blog header include in each file.
		$lines = explode("\n", implode('', file("$site_dir/$newfile")));
		if ($lines) {
			$f = fopen("$site_dir/$newfile", 'w');

			foreach ($lines as $line) {
				if (preg_match('/require.*mn-blog-header/', $line))
					$line = '//' . $line;

				// Update stylesheet references.
				$line = str_replace("<?php echo __get_option('siteurl'); ?>/mn-layout.css", "<?php bloginfo('stylesheet_url'); ?>", $line);

				// Update comments template inclusion.
				$line = str_replace("<?php include(ABSPATH . 'mn-comments.php'); ?>", "<?php comments_template(); ?>", $line);

				fwrite($f, "{$line}\n");
			}
			fclose($f);
		}
	}

	// Add a theme header.
	$header = "/*\nTheme Name: $theme_name\nTheme URI: " . __get_option('siteurl') . "\nDescription: A theme automatically created by the update.\nVersion: 1.0\nAuthor: Moi\n*/\n";

	$stylelines = file_get_contents("$site_dir/style.css");
	if ($stylelines) {
		$f = fopen("$site_dir/style.css", 'w');

		fwrite($f, $header);
		fwrite($f, $stylelines);
		fclose($f);
	}

	return true;
}

/**
 * Creates a site theme from the default theme.
 *
 * {@internal Missing Long Description}}
 *
 * @since 1.5.0
 *
 * @param string $theme_name The name of the theme.
 * @param string $template   The directory name of the theme.
 * @return false|void
 */
function make_site_theme_from_default($theme_name, $template) {
	$site_dir = MAIN_DIR . "/themes/$template";
	$default_dir = MAIN_DIR . '/themes/' . DEFAULT_THEME;

	// Copy files from the default theme to the site theme.
	//$files = array('index.php', 'comments.php', 'comments-popup.php', 'footer.php', 'header.php', 'sidebar.php', 'style.css');

	$theme_dir = @ opendir($default_dir);
	if ($theme_dir) {
		while(($theme_file = readdir( $theme_dir )) !== false) {
			if (is_dir("$default_dir/$theme_file"))
				continue;
			if (! @copy("$default_dir/$theme_file", "$site_dir/$theme_file"))
				return;
			chmod("$site_dir/$theme_file", 0777);
		}
	}
	@closedir($theme_dir);

	// Rewrite the theme header.
	$stylelines = explode("\n", implode('', file("$site_dir/style.css")));
	if ($stylelines) {
		$f = fopen("$site_dir/style.css", 'w');

		foreach ($stylelines as $line) {
			if (strpos($line, 'Theme Name:') !== false) $line = 'Theme Name: ' . $theme_name;
			elseif (strpos($line, 'Theme URI:') !== false) $line = 'Theme URI: ' . __get_option('url');
			elseif (strpos($line, 'Description:') !== false) $line = 'Description: Your theme.';
			elseif (strpos($line, 'Version:') !== false) $line = 'Version: 1';
			elseif (strpos($line, 'Author:') !== false) $line = 'Author: You';
			fwrite($f, $line . "\n");
		}
		fclose($f);
	}

	// Copy the images.
	umask(0);
	if (! mkdir("$site_dir/images", 0777)) {
		return false;
	}

	$images_dir = @ opendir("$default_dir/images");
	if ($images_dir) {
		while(($image = readdir($images_dir)) !== false) {
			if (is_dir("$default_dir/images/$image"))
				continue;
			if (! @copy("$default_dir/images/$image", "$site_dir/images/$image"))
				return;
			chmod("$site_dir/images/$image", 0777);
		}
	}
	@closedir($images_dir);
}

/**
 * Creates a site theme.
 *
 * {@internal Missing Long Description}}
 *
 * @since 1.5.0
 *
 * @return false|string
 */
function make_site_theme() {
	// Name the theme after the blog.
	$theme_name = __get_option('blogname');
	$template = sanitize_title($theme_name);
	$site_dir = MAIN_DIR . "/themes/$template";

	// If the theme already exists, nothing to do.
	if ( is_dir($site_dir)) {
		return false;
	}

	// We must be able to write to the themes dir.
	if (! is_writable(MAIN_DIR . "/themes")) {
		return false;
	}

	umask(0);
	if (! mkdir($site_dir, 0777)) {
		return false;
	}

	if (file_exists(ABSPATH . 'mn-layout.css')) {
		if (! make_site_theme_from_oldschool($theme_name, $template)) {
			// TODO: rm -rf the site theme directory.
			return false;
		}
	} else {
		if (! make_site_theme_from_default($theme_name, $template))
			// TODO: rm -rf the site theme directory.
			return false;
	}

	// Make the new site theme active.
	$current_template = __get_option('template');
	if ($current_template == DEFAULT_THEME) {
		update_option('template', $template);
		update_option('stylesheet', $template);
	}
	return $template;
}

/**
 * Translate user level to user role name.
 *
 * @since 2.0.0
 *
 * @param int $level User level.
 * @return string User role name.
 */
function translate_level_to_role($level) {
	switch ($level) {
	case 10:
	case 9:
	case 8:
		return 'administrator';
	case 7:
	case 6:
	case 5:
		return 'editor';
	case 4:
	case 3:
	case 2:
		return 'author';
	case 1:
		return 'contributor';
	case 0:
		return 'subscriber';
	}
}

/**
 * Checks the version of the installed MySQL binary.
 *
 * @since 2.1.0
 *
 * @global mndb  $mndb
 */
function mn_check_mysql_version() {
	global $mndb;
	$result = $mndb->check_database_version();
	if ( is_mn_error( $result ) )
		die( $result->get_error_message() );
}

/**
 * Disables the Automattic widgets plugin, which was merged into core.
 *
 * @since 2.2.0
 */
function maybe_disable_automattic_widgets() {
	$plugins = __get_option( 'active_plugins' );

	foreach ( (array) $plugins as $plugin ) {
		if ( basename( $plugin ) == 'widgets.php' ) {
			array_splice( $plugins, array_search( $plugin, $plugins ), 1 );
			update_option( 'active_plugins', $plugins );
			break;
		}
	}
}

/**
 * Disables the Link Manager on upgrade if, at the time of upgrade, no links exist in the DB.
 *
 * @since 3.5.0
 *
 * @global int  $mn_current_db_version
 * @global mndb $mndb Mtaandao database abstraction object.
 */
function maybe_disable_link_manager() {
	global $mn_current_db_version, $mndb;

	if ( $mn_current_db_version >= 22006 && get_option( 'link_manager_enabled' ) && ! $mndb->get_var( "SELECT link_id FROM $mndb->links LIMIT 1" ) )
		update_option( 'link_manager_enabled', 0 );
}

/**
 * Runs before the schema is upgraded.
 *
 * @since 2.9.0
 *
 * @global int  $mn_current_db_version
 * @global mndb $mndb Mtaandao database abstraction object.
 */
function pre_schema_upgrade() {
	global $mn_current_db_version, $mndb;

	// Upgrade versions prior to 2.9
	if ( $mn_current_db_version < 11557 ) {
		// Delete duplicate options. Keep the option with the highest option_id.
		$mndb->query("DELETE o1 FROM $mndb->options AS o1 JOIN $mndb->options AS o2 USING (`option_name`) WHERE o2.option_id > o1.option_id");

		// Drop the old primary key and add the new.
		$mndb->query("ALTER TABLE $mndb->options DROP PRIMARY KEY, ADD PRIMARY KEY(option_id)");

		// Drop the old option_name index. dbDelta() doesn't do the drop.
		$mndb->query("ALTER TABLE $mndb->options DROP INDEX option_name");
	}

	// Multisite schema upgrades.
	if ( $mn_current_db_version < 25448 && is_multisite() && mn_should_upgrade_global_tables() ) {

		// Upgrade versions prior to 3.7
		if ( $mn_current_db_version < 25179 ) {
			// New primary key for signups.
			$mndb->query( "ALTER TABLE $mndb->signups ADD signup_id BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST" );
			$mndb->query( "ALTER TABLE $mndb->signups DROP INDEX domain" );
		}

		if ( $mn_current_db_version < 25448 ) {
			// Convert archived from enum to tinyint.
			$mndb->query( "ALTER TABLE $mndb->blogs CHANGE COLUMN archived archived varchar(1) NOT NULL default '0'" );
			$mndb->query( "ALTER TABLE $mndb->blogs CHANGE COLUMN archived archived tinyint(2) NOT NULL default 0" );
		}
	}

	// Upgrade versions prior to 4.2.
	if ( $mn_current_db_version < 31351 ) {
		if ( ! is_multisite() && mn_should_upgrade_global_tables() ) {
			$mndb->query( "ALTER TABLE $mndb->usermeta DROP INDEX meta_key, ADD INDEX meta_key(meta_key(191))" );
		}
		$mndb->query( "ALTER TABLE $mndb->terms DROP INDEX slug, ADD INDEX slug(slug(191))" );
		$mndb->query( "ALTER TABLE $mndb->terms DROP INDEX name, ADD INDEX name(name(191))" );
		$mndb->query( "ALTER TABLE $mndb->commentmeta DROP INDEX meta_key, ADD INDEX meta_key(meta_key(191))" );
		$mndb->query( "ALTER TABLE $mndb->postmeta DROP INDEX meta_key, ADD INDEX meta_key(meta_key(191))" );
		$mndb->query( "ALTER TABLE $mndb->posts DROP INDEX post_name, ADD INDEX post_name(post_name(191))" );
	}

	// Upgrade versions prior to 4.4.
	if ( $mn_current_db_version < 34978 ) {
		// If compatible termmeta table is found, use it, but enforce a proper index and update collation.
		if ( $mndb->get_var( "SHOW TABLES LIKE '{$mndb->termmeta}'" ) && $mndb->get_results( "SHOW INDEX FROM {$mndb->termmeta} WHERE Column_name = 'meta_key'" ) ) {
			$mndb->query( "ALTER TABLE $mndb->termmeta DROP INDEX meta_key, ADD INDEX meta_key(meta_key(191))" );
			maybe_convert_table_to_utf8mb4( $mndb->termmeta );
		}
	}
}

/**
 * Install global terms.
 *
 * @since 3.0.0
 *
 * @global mndb   $mndb
 * @global string $charset_collate
 */
if ( !function_exists( 'install_global_terms' ) ) :
function install_global_terms() {
	global $mndb, $charset_collate;
	$ms_queries = "
CREATE TABLE $mndb->sitecategories (
  cat_ID bigint(20) NOT NULL auto_increment,
  cat_name varchar(55) NOT NULL default '',
  category_nicename varchar(200) NOT NULL default '',
  last_updated timestamp NOT NULL,
  PRIMARY KEY  (cat_ID),
  KEY category_nicename (category_nicename),
  KEY last_updated (last_updated)
) $charset_collate;
";
// now create tables
	dbDelta( $ms_queries );
}
endif;

/**
 * Determine if global tables should be upgraded.
 *
 * This function performs a series of checks to ensure the environment allows
 * for the safe upgrading of global Mtaandao database tables. It is necessary
 * because global tables will commonly grow to millions of rows on large
 * installations, and the ability to control their upgrade routines can be
 * critical to the operation of large networks.
 *
 * In a future iteration, this function may use `mn_is_large_network()` to more-
 * intelligently prevent global table upgrades. Until then, we make sure
 * Mtaandao is on the main site of the main network, to avoid running queries
 * more than once in multi-site or multi-network environments.
 *
 * @since 4.3.0
 *
 * @return bool Whether to run the upgrade routines on global tables.
 */
function mn_should_upgrade_global_tables() {

	// Return false early if explicitly not upgrading
	if ( defined( 'DO_NOT_UPGRADE_GLOBAL_TABLES' ) ) {
		return false;
	}

	// Assume global tables should be upgraded
	$should_upgrade = true;

	// Set to false if not on main network (does not matter if not multi-network)
	if ( ! is_main_network() ) {
		$should_upgrade = false;
	}

	// Set to false if not on main site of current network (does not matter if not multi-site)
	if ( ! is_main_site() ) {
		$should_upgrade = false;
	}

	/**
	 * Filters if upgrade routines should be run on global tables.
	 *
	 * @param bool $should_upgrade Whether to run the upgrade routines on global tables.
	 */
	return apply_filters( 'mn_should_upgrade_global_tables', $should_upgrade );
}