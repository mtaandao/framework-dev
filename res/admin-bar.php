<?php
/**
 * Toolbar API: Top-level Toolbar functionality
 *
 * @package Mtaandao
 * @subpackage Toolbar
 * @since 3.1.0
 */

/**
 * Instantiate the admin bar object and set it up as a global for access elsewhere.
 *
 * UNHOOKING THIS FUNCTION WILL NOT PROPERLY REMOVE THE ADMIN BAR.
 * For that, use show_admin_bar(false) or the {@see 'show_admin_bar'} filter.
 *
 * @since 3.1.0
 * @access private
 *
 * @global Admin_Bar $admin_bar
 *
 * @return bool Whether the admin bar was successfully initialized.
 */
function _admin_bar_init() {
	global $admin_bar;

	if ( ! is_admin_bar_showing() )
		return false;

	/* Load the admin bar class code ready for instantiation */
	require_once( ABSPATH . RES . '/class-admin-bar.php' );
	require_once( ABSPATH . RES . '/pluggable.php' );

	/* Instantiate the admin bar */

	/**
	 * Filters the admin bar class to instantiate.
	 *
	 * @since 3.1.0
	 *
	 * @param string $admin_bar_class Admin bar class to use. Default 'Admin_Bar'.
	 */
	$admin_bar_class = apply_filters( 'admin_bar_class', 'Admin_Bar' );
	if ( class_exists( $admin_bar_class ) )
		$admin_bar = new $admin_bar_class;
	else
		return false;

	$admin_bar->initialize();
	$admin_bar->add_menus();

	return true;
}

/**
 * Renders the admin bar to the page based on the $admin_bar->menu member var.
 *
 * This is called very late on the footer actions so that it will render after
 * anything else being added to the footer.
 *
 * It includes the {@see 'admin_bar_menu'} action which should be used to hook in and
 * add new menus to the admin bar. That way you can be sure that you are adding at most
 * optimal point, right before the admin bar is rendered. This also gives you access to
 * the `$post` global, among others.
 *
 * @since 3.1.0
 *
 * @global Admin_Bar $admin_bar
 */
function admin_bar_render() {
	global $admin_bar;

	if ( ! is_admin_bar_showing() || ! is_object( $admin_bar ) )
		return;

	/**
	 * Load all necessary admin bar items.
	 *
	 * This is the hook used to add, remove, or manipulate admin bar items.
	 *
	 * @since 3.1.0
	 *
	 * @param Admin_Bar $admin_bar Admin_Bar instance, passed by reference
	 */
	do_action_ref_array( 'admin_bar_menu', array( &$admin_bar ) );

	/**
	 * Fires before the admin bar is rendered.
	 *
	 * @since 3.1.0
	 */
	do_action( 'mn_before_admin_bar_render' );

	$admin_bar->render();

	/**
	 * Fires after the admin bar is rendered.
	 *
	 * @since 3.1.0
	 */
	do_action( 'mn_after_admin_bar_render' );
}

/**
 * Add the Mtaandao logo menu.
 *
 * @since 3.3.0
 *
 * @param Admin_Bar $admin_bar
 */
function admin_bar_mn_menu( $admin_bar ) {
	if ( current_user_can( 'read' ) ) {
		$about_url = self_admin_url( 'about.php' );
	} elseif ( is_multisite() ) {
		$about_url = get_dashboard_url( get_current_user_id(), 'about.php' );
	} else {
		$about_url = false;
	}

	$mn_logo_menu_args = array(
		'id'    => 'mn-logo',
		'title' => '<span class="ab-icon"></span><span class="screen-reader-text">' . __( 'About Mtaandao' ) . '</span>',
		'href'  => $about_url,
	);

	// Set tabindex="0" to make sub menus accessible when no URL is available.
	if ( ! $about_url ) {
		$mn_logo_menu_args['meta'] = array(
			'tabindex' => 0,
		);
	}

	$admin_bar->add_menu( $mn_logo_menu_args );

	if ( $about_url ) {
		// Add "About Mtaandao" link
		$admin_bar->add_menu( array(
			'parent' => 'mn-logo',
			'id'     => 'about',
			'title'  => __('About Mtaandao'),
			'href'   => $about_url,
		) );
	}

	// Add Mtaandao.org link
	$admin_bar->add_menu( array(
		'parent'    => 'mn-logo-external',
		'id'        => 'mnorg',
		'title'     => __('Mtaandao.org'),
		'href'      => __('https://mtaandao.co.ke/'),
	) );

	// Add codex link
	$admin_bar->add_menu( array(
		'parent'    => 'mn-logo-external',
		'id'        => 'documentation',
		'title'     => __('Documentation'),
		'href'      => __('https://mtaandao.github.io/'),
	) );

	// Add forums link
	$admin_bar->add_menu( array(
		'parent'    => 'mn-logo-external',
		'id'        => 'support-forums',
		'title'     => __('Support Forums'),
		'href'      => __('https://mtaandao.co.ke/support/'),
	) );

	// Add feedback link
	$admin_bar->add_menu( array(
		'parent'    => 'mn-logo-external',
		'id'        => 'feedback',
		'title'     => __('Feedback'),
		'href'      => __('https://mtaandao.co.ke/support/forum/requests-and-feedback'),
	) );
}

/**
 * Add the sidebar toggle button.
 *
 * @since 3.8.0
 *
 * @param Admin_Bar $admin_bar
 */
function admin_bar_sidebar_toggle( $admin_bar ) {
	if ( is_admin() ) {
		$admin_bar->add_menu( array(
			'id'    => 'menu-toggle',
			'title' => '<span class="ab-icon"></span><span class="screen-reader-text">' . __( 'Menu' ) . '</span>',
			'href'  => '#',
		) );
	}
}

/**
 * Add the "My Account" item.
 *
 * @since 3.3.0
 *
 * @param Admin_Bar $admin_bar
 */
function admin_bar_my_account_item( $admin_bar ) {
	$user_id      = get_current_user_id();
	$current_user = mn_get_current_user();

	if ( ! $user_id )
		return;

	if ( current_user_can( 'read' ) ) {
		$profile_url = get_edit_profile_url( $user_id );
	} elseif ( is_multisite() ) {
		$profile_url = get_dashboard_url( $user_id, 'profile.php' );
	} else {
		$profile_url = false;
	}

	$avatar = get_avatar( $user_id, 26 );
	/* translators: %s: current user's display name */
	$howdy  = sprintf( __( 'Howdy, %s' ), $current_user->display_name );
	$class  = empty( $avatar ) ? '' : 'with-avatar';

	$admin_bar->add_menu( array(
		'id'        => 'my-account',
		'parent'    => 'top-secondary',
		'title'     => $howdy . $avatar,
		'href'      => $profile_url,
		'meta'      => array(
			'class'     => $class,
		),
	) );
}

/**
 * Add the "My Account" submenu items.
 *
 * @since 3.1.0
 *
 * @param Admin_Bar $admin_bar
 */
function admin_bar_my_account_menu( $admin_bar ) {
	$user_id      = get_current_user_id();
	$current_user = mn_get_current_user();

	if ( ! $user_id )
		return;

	if ( current_user_can( 'read' ) ) {
		$profile_url = get_edit_profile_url( $user_id );
	} elseif ( is_multisite() ) {
		$profile_url = get_dashboard_url( $user_id, 'profile.php' );
	} else {
		$profile_url = false;
	}

	$admin_bar->add_group( array(
		'parent' => 'my-account',
		'id'     => 'user-actions',
	) );

	$user_info  = get_avatar( $user_id, 64 );
	$user_info .= "<span class='display-name'>{$current_user->display_name}</span>";

	if ( $current_user->display_name !== $current_user->user_login )
		$user_info .= "<span class='username'>{$current_user->user_login}</span>";

	$admin_bar->add_menu( array(
		'parent' => 'user-actions',
		'id'     => 'user-info',
		'title'  => $user_info,
		'href'   => $profile_url,
		'meta'   => array(
			'tabindex' => -1,
		),
	) );

	if ( false !== $profile_url ) {
		$admin_bar->add_menu( array(
			'parent' => 'user-actions',
			'id'     => 'edit-profile',
			'title'  => __( 'Edit My Profile' ),
			'href'   => $profile_url,
		) );
	}

	$admin_bar->add_menu( array(
		'parent' => 'user-actions',
		'id'     => 'logout',
		'title'  => __( 'Log Out' ),
		'href'   => mn_logout_url(),
	) );
}

/**
 * Add the "Site Name" menu.
 *
 * @since 3.3.0
 *
 * @param Admin_Bar $admin_bar
 */
function admin_bar_site_menu( $admin_bar ) {
	// Don't show for logged out users.
	if ( ! is_user_logged_in() )
		return;

	// Show only when the user is a member of this site, or they're a super admin.
	if ( ! is_user_member_of_blog() && ! is_super_admin() )
		return;

	$blogname = get_bloginfo('name');

	if ( ! $blogname ) {
		$blogname = preg_replace( '#^(https?://)?(www.)?#', '', get_home_url() );
	}

	if ( is_network_admin() ) {
		/* translators: %s: site name */
		$blogname = sprintf( __( 'Network Admin: %s' ), esc_html( get_network()->site_name ) );
	} elseif ( is_user_admin() ) {
		/* translators: %s: site name */
		$blogname = sprintf( __( 'User Dashboard: %s' ), esc_html( get_network()->site_name ) );
	}

	$title = mn_html_excerpt( $blogname, 40, '&hellip;' );

	$admin_bar->add_menu( array(
		'id'    => 'site-name',
		'title' => $title,
		'href'  => ( is_admin() || ! current_user_can( 'read' ) ) ? home_url( '/' ) : admin_url(),
	) );

	// Create submenu items.

	if ( is_admin() ) {
		// Add an option to visit the site.
		$admin_bar->add_menu( array(
			'parent' => 'site-name',
			'id'     => 'view-site',
			'title'  => __( 'Visit Site' ),
			'href'   => home_url( '/' ),
		) );

		if ( is_blog_admin() && is_multisite() && current_user_can( 'manage_sites' ) ) {
			$admin_bar->add_menu( array(
				'parent' => 'site-name',
				'id'     => 'edit-site',
				'title'  => __( 'Edit Site' ),
				'href'   => network_admin_url( 'site-info.php?id=' . get_current_blog_id() ),
			) );
		}

	} else if ( current_user_can( 'read' ) ) {
		// We're on the front end, link to the Dashboard.
		$admin_bar->add_menu( array(
			'parent' => 'site-name',
			'id'     => 'dashboard',
			'title'  => __( 'Dashboard' ),
			'href'   => admin_url(),
		) );

		// Add the appearance submenu items.
		admin_bar_appearance_menu( $admin_bar );
	}
}

/**
 * Adds the "Customize" link to the Toolbar.
 *
 * @since 4.3.0
 *
 * @param Admin_Bar $admin_bar Admin_Bar instance.
 * @global MN_Customize_Manager $mn_customize
 */
function admin_bar_customize_menu( $admin_bar ) {
	global $mn_customize;

	// Don't show for users who can't access the customizer or when in the admin.
	if ( ! current_user_can( 'customize' ) || is_admin() ) {
		return;
	}

	// Don't show if the user cannot edit a given customize_changeset post currently being previewed.
	if ( is_customize_preview() && $mn_customize->changeset_post_id() && ! current_user_can( get_post_type_object( 'customize_changeset' )->cap->edit_post, $mn_customize->changeset_post_id() ) ) {
		return;
	}

	$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	if ( is_customize_preview() && $mn_customize->changeset_uuid() ) {
		$current_url = remove_query_arg( 'customize_changeset_uuid', $current_url );
	}

	$customize_url = add_query_arg( 'url', urlencode( $current_url ), mn_customize_url() );
	if ( is_customize_preview() ) {
		$customize_url = add_query_arg( array( 'changeset_uuid' => $mn_customize->changeset_uuid() ), $customize_url );
	}

	$admin_bar->add_menu( array(
		'id'     => 'customize',
		'title'  => __( 'Customize' ),
		'href'   => $customize_url,
		'meta'   => array(
			'class' => 'hide-if-no-customize',
		),
	) );
	add_action( 'mn_before_admin_bar_render', 'mn_customize_support_script' );
}

/**
 * Add the "My Sites/[Site Name]" menu and all submenus.
 *
 * @since 3.1.0
 *
 * @param Admin_Bar $admin_bar
 */
function admin_bar_my_sites_menu( $admin_bar ) {
	// Don't show for logged out users or single site mode.
	if ( ! is_user_logged_in() || ! is_multisite() )
		return;

	// Show only when the user has at least one site, or they're a super admin.
	if ( count( $admin_bar->user->blogs ) < 1 && ! is_super_admin() )
		return;

	if ( $admin_bar->user->active_blog ) {
		$my_sites_url = get_admin_url( $admin_bar->user->active_blog->blog_id, 'my-sites.php' );
	} else {
		$my_sites_url = admin_url( 'my-sites.php' );
	}

	$admin_bar->add_menu( array(
		'id'    => 'my-sites',
		'title' => __( 'My Sites' ),
		'href'  => $my_sites_url,
	) );

	if ( is_super_admin() ) {
		$admin_bar->add_group( array(
			'parent' => 'my-sites',
			'id'     => 'my-sites-super-admin',
		) );

		$admin_bar->add_menu( array(
			'parent' => 'my-sites-super-admin',
			'id'     => 'network-admin',
			'title'  => __('Network Admin'),
			'href'   => network_admin_url(),
		) );

		$admin_bar->add_menu( array(
			'parent' => 'network-admin',
			'id'     => 'network-admin-d',
			'title'  => __( 'Dashboard' ),
			'href'   => network_admin_url(),
		) );
		$admin_bar->add_menu( array(
			'parent' => 'network-admin',
			'id'     => 'network-admin-s',
			'title'  => __( 'Sites' ),
			'href'   => network_admin_url( 'sites.php' ),
		) );
		$admin_bar->add_menu( array(
			'parent' => 'network-admin',
			'id'     => 'network-admin-u',
			'title'  => __( 'Users' ),
			'href'   => network_admin_url( 'users.php' ),
		) );
		$admin_bar->add_menu( array(
			'parent' => 'network-admin',
			'id'     => 'network-admin-t',
			'title'  => __( 'Themes' ),
			'href'   => network_admin_url( 'themes.php' ),
		) );
		$admin_bar->add_menu( array(
			'parent' => 'network-admin',
			'id'     => 'network-admin-p',
			'title'  => __( 'Plugins' ),
			'href'   => network_admin_url( 'plugins.php' ),
		) );
		$admin_bar->add_menu( array(
			'parent' => 'network-admin',
			'id'     => 'network-admin-o',
			'title'  => __( 'Settings' ),
			'href'   => network_admin_url( 'settings.php' ),
		) );
	}

	// Add site links
	$admin_bar->add_group( array(
		'parent' => 'my-sites',
		'id'     => 'my-sites-list',
		'meta'   => array(
			'class' => is_super_admin() ? 'ab-sub-secondary' : '',
		),
	) );

	foreach ( (array) $admin_bar->user->blogs as $blog ) {
		switch_to_blog( $blog->userblog_id );

		$blavatar = '<div class="blavatar"></div>';

		$blogname = $blog->blogname;

		if ( ! $blogname ) {
			$blogname = preg_replace( '#^(https?://)?(www.)?#', '', get_home_url() );
		}

		$menu_id  = 'blog-' . $blog->userblog_id;

		$admin_bar->add_menu( array(
			'parent'    => 'my-sites-list',
			'id'        => $menu_id,
			'title'     => $blavatar . $blogname,
			'href'      => admin_url(),
		) );

		$admin_bar->add_menu( array(
			'parent' => $menu_id,
			'id'     => $menu_id . '-d',
			'title'  => __( 'Dashboard' ),
			'href'   => admin_url(),
		) );

		if ( current_user_can( get_post_type_object( 'post' )->cap->create_posts ) ) {
			$admin_bar->add_menu( array(
				'parent' => $menu_id,
				'id'     => $menu_id . '-n',
				'title'  => __( 'New Post' ),
				'href'   => admin_url( 'post-new.php' ),
			) );
		}

		if ( current_user_can( 'edit_posts' ) ) {
			$admin_bar->add_menu( array(
				'parent' => $menu_id,
				'id'     => $menu_id . '-c',
				'title'  => __( 'Manage Comments' ),
				'href'   => admin_url( 'edit-comments.php' ),
			) );
		}

		$admin_bar->add_menu( array(
			'parent' => $menu_id,
			'id'     => $menu_id . '-v',
			'title'  => __( 'Visit Site' ),
			'href'   => home_url( '/' ),
		) );

		restore_current_blog();
	}
}

/**
 * Provide a shortlink.
 *
 * @since 3.1.0
 *
 * @param Admin_Bar $admin_bar
 */
function admin_bar_shortlink_menu( $admin_bar ) {
	$short = mn_get_shortlink( 0, 'query' );
	$id = 'get-shortlink';

	if ( empty( $short ) )
		return;

	$html = '<input class="shortlink-input" type="text" readonly="readonly" value="' . esc_attr( $short ) . '" />';

	$admin_bar->add_menu( array(
		'id' => $id,
		'title' => __( 'Shortlink' ),
		'href' => $short,
		'meta' => array( 'html' => $html ),
	) );
}

/**
 * Provide an edit link for posts and terms.
 *
 * @since 3.1.0
 *
 * @global MN_Term  $tag
 * @global MN_Query $mn_the_query
 *
 * @param Admin_Bar $admin_bar
 */
function admin_bar_edit_menu( $admin_bar ) {
	global $tag, $mn_the_query;

	if ( is_admin() ) {
		$current_screen = get_current_screen();
		$post = get_post();

		if ( 'post' == $current_screen->base
			&& 'add' != $current_screen->action
			&& ( $post_type_object = get_post_type_object( $post->post_type ) )
			&& current_user_can( 'read_post', $post->ID )
			&& ( $post_type_object->public )
			&& ( $post_type_object->show_in_admin_bar ) )
		{
			if ( 'draft' == $post->post_status ) {
				$preview_link = get_preview_post_link( $post );
				$admin_bar->add_menu( array(
					'id' => 'preview',
					'title' => $post_type_object->labels->view_item,
					'href' => esc_url( $preview_link ),
					'meta' => array( 'target' => 'mn-preview-' . $post->ID ),
				) );
			} else {
				$admin_bar->add_menu( array(
					'id' => 'view',
					'title' => $post_type_object->labels->view_item,
					'href' => get_permalink( $post->ID )
				) );
			}
		} elseif ( 'edit' == $current_screen->base
 			&& ( $post_type_object = get_post_type_object( $current_screen->post_type ) )
 			&& ( $post_type_object->public )
 			&& ( $post_type_object->show_in_admin_bar )
 			&& ( get_post_type_archive_link( $post_type_object->name ) )
			&& ! ( 'post' === $post_type_object->name && 'posts' === get_option( 'show_on_front' ) ) )
 		{
 			$admin_bar->add_node( array(
 				'id' => 'archive',
 				'title' => $post_type_object->labels->view_items,
 				'href' => get_post_type_archive_link( $current_screen->post_type )
 			) );
		} elseif ( 'term' == $current_screen->base
			&& isset( $tag ) && is_object( $tag ) && ! is_mn_error( $tag )
			&& ( $tax = get_taxonomy( $tag->taxonomy ) )
			&& $tax->public )
		{
			$admin_bar->add_menu( array(
				'id' => 'view',
				'title' => $tax->labels->view_item,
				'href' => get_term_link( $tag )
			) );
		}
	} else {
		$current_object = $mn_the_query->get_queried_object();

		if ( empty( $current_object ) )
			return;

		if ( ! empty( $current_object->post_type )
			&& ( $post_type_object = get_post_type_object( $current_object->post_type ) )
			&& current_user_can( 'edit_post', $current_object->ID )
			&& $post_type_object->show_in_admin_bar
			&& $edit_post_link = get_edit_post_link( $current_object->ID ) )
		{
			$admin_bar->add_menu( array(
				'id' => 'edit',
				'title' => $post_type_object->labels->edit_item,
				'href' => $edit_post_link
			) );
		} elseif ( ! empty( $current_object->taxonomy )
			&& ( $tax = get_taxonomy( $current_object->taxonomy ) )
			&& current_user_can( 'edit_term', $current_object->term_id )
			&& $edit_term_link = get_edit_term_link( $current_object->term_id, $current_object->taxonomy ) )
		{
			$admin_bar->add_menu( array(
				'id' => 'edit',
				'title' => $tax->labels->edit_item,
				'href' => $edit_term_link
			) );
		}
	}
}

/**
 * Add "Add New" menu.
 *
 * @since 3.1.0
 *
 * @param Admin_Bar $admin_bar
 */
function admin_bar_new_content_menu( $admin_bar ) {
	$actions = array();

	$cpts = (array) get_post_types( array( 'show_in_admin_bar' => true ), 'objects' );

	if ( isset( $cpts['post'] ) && current_user_can( $cpts['post']->cap->create_posts ) )
		$actions[ 'post-new.php' ] = array( $cpts['post']->labels->name_admin_bar, 'new-post' );

	if ( isset( $cpts['attachment'] ) && current_user_can( 'upload_files' ) )
		$actions[ 'media-new.php' ] = array( $cpts['attachment']->labels->name_admin_bar, 'new-media' );

	if ( current_user_can( 'manage_links' ) )
		$actions[ 'link-add.php' ] = array( _x( 'Link', 'add new from admin bar' ), 'new-link' );

	if ( isset( $cpts['page'] ) && current_user_can( $cpts['page']->cap->create_posts ) )
		$actions[ 'post-new.php?post_type=page' ] = array( $cpts['page']->labels->name_admin_bar, 'new-page' );

	unset( $cpts['post'], $cpts['page'], $cpts['attachment'] );

	// Add any additional custom post types.
	foreach ( $cpts as $cpt ) {
		if ( ! current_user_can( $cpt->cap->create_posts ) )
			continue;

		$key = 'post-new.php?post_type=' . $cpt->name;
		$actions[ $key ] = array( $cpt->labels->name_admin_bar, 'new-' . $cpt->name );
	}
	// Avoid clash with parent node and a 'content' post type.
	if ( isset( $actions['post-new.php?post_type=content'] ) )
		$actions['post-new.php?post_type=content'][1] = 'add-new-content';

	if ( current_user_can( 'create_users' ) || current_user_can( 'promote_users' ) )
		$actions[ 'user-new.php' ] = array( _x( 'User', 'add new from admin bar' ), 'new-user' );

	if ( ! $actions )
		return;

	$title = '<span class="ab-icon"></span><span class="ab-label">' . _x( 'New', 'admin bar menu group label' ) . '</span>';

	$admin_bar->add_menu( array(
		'id'    => 'new-content',
		'title' => $title,
		'href'  => admin_url( current( array_keys( $actions ) ) ),
	) );

	foreach ( $actions as $link => $action ) {
		list( $title, $id ) = $action;

		$admin_bar->add_menu( array(
			'parent'    => 'new-content',
			'id'        => $id,
			'title'     => $title,
			'href'      => admin_url( $link )
		) );
	}
}

/**
 * Add edit comments link with awaiting moderation count bubble.
 *
 * @since 3.1.0
 *
 * @param Admin_Bar $admin_bar
 */
function admin_bar_comments_menu( $admin_bar ) {
	if ( !current_user_can('edit_posts') )
		return;

	$awaiting_mod = mn_count_comments();
	$awaiting_mod = $awaiting_mod->moderated;
	$awaiting_text = sprintf( _n( '%s comment awaiting moderation', '%s comments awaiting moderation', $awaiting_mod ), number_format_i18n( $awaiting_mod ) );

	$icon  = '<span class="ab-icon"></span>';
	$title = '<span class="ab-label awaiting-mod pending-count count-' . $awaiting_mod . '" aria-hidden="true">' . number_format_i18n( $awaiting_mod ) . '</span>';
	$title .= '<span class="screen-reader-text">' . $awaiting_text . '</span>';

	$admin_bar->add_menu( array(
		'id'    => 'comments',
		'title' => $icon . $title,
		'href'  => admin_url('edit-comments.php'),
	) );
}

/**
 * Add appearance submenu items to the "Site Name" menu.
 *
 * @since 3.1.0
 *
 * @param Admin_Bar $admin_bar
 */
function admin_bar_appearance_menu( $admin_bar ) {
	$admin_bar->add_group( array( 'parent' => 'site-name', 'id' => 'appearance' ) );

	if ( current_user_can( 'switch_themes' ) ) {
		$admin_bar->add_menu( array(
			'parent' => 'appearance',
			'id'     => 'themes',
			'title'  => __( 'Themes' ),
			'href'   => admin_url( 'themes.php' ),
		) );
	}

	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	if ( current_theme_supports( 'widgets' )  ) {
		$admin_bar->add_menu( array(
			'parent' => 'appearance',
			'id'     => 'widgets',
			'title'  => __( 'Widgets' ),
			'href'   => admin_url( 'widgets.php' ),
		) );
	}

	if ( current_theme_supports( 'menus' ) || current_theme_supports( 'widgets' ) )
		$admin_bar->add_menu( array( 'parent' => 'appearance', 'id' => 'menus', 'title' => __('Menus'), 'href' => admin_url('nav-menus.php') ) );

	if ( current_theme_supports( 'custom-background' ) ) {
		$admin_bar->add_menu( array(
			'parent' => 'appearance',
			'id'     => 'background',
			'title'  => __( 'Background' ),
			'href'   => admin_url( 'themes.php?page=custom-background' ),
			'meta'   => array(
				'class' => 'hide-if-customize',
			),
		) );
	}

	if ( current_theme_supports( 'custom-header' ) ) {
		$admin_bar->add_menu( array(
			'parent' => 'appearance',
			'id'     => 'header',
			'title'  => __( 'Header' ),
			'href'   => admin_url( 'themes.php?page=custom-header' ),
			'meta'   => array(
				'class' => 'hide-if-customize',
			),
		) );
	}

}

/**
 * Provide an update link if theme/plugin/core updates are available.
 *
 * @since 3.1.0
 *
 * @param Admin_Bar $admin_bar
 */
function admin_bar_updates_menu( $admin_bar ) {

	$update_data = mn_get_update_data();

	if ( !$update_data['counts']['total'] )
		return;

	$title = '<span class="ab-icon"></span><span class="ab-label">' . number_format_i18n( $update_data['counts']['total'] ) . '</span>';
	$title .= '<span class="screen-reader-text">' . $update_data['title'] . '</span>';

	$admin_bar->add_menu( array(
		'id'    => 'updates',
		'title' => $title,
		'href'  => network_admin_url( 'update-core.php' ),
		'meta'  => array(
			'title' => $update_data['title'],
		),
	) );
}

/**
 * Add search form.
 *
 * @since 3.3.0
 *
 * @param Admin_Bar $admin_bar
 */
function admin_bar_search_menu( $admin_bar ) {
	if ( is_admin() )
		return;

	$form  = '<form action="' . esc_url( home_url( '/' ) ) . '" method="get" id="adminbarsearch">';
	$form .= '<input class="adminbar-input" name="s" id="adminbar-search" type="text" value="" maxlength="150" />';
	$form .= '<label for="adminbar-search" class="screen-reader-text">' . __( 'Search' ) . '</label>';
	$form .= '<input type="submit" class="adminbar-button" value="' . __('Search') . '"/>';
	$form .= '</form>';

	$admin_bar->add_menu( array(
		'parent' => 'top-secondary',
		'id'     => 'search',
		'title'  => $form,
		'meta'   => array(
			'class'    => 'admin-bar-search',
			'tabindex' => -1,
		)
	) );
}

/**
 * Add secondary menus.
 *
 * @since 3.3.0
 *
 * @param Admin_Bar $admin_bar
 */
function admin_bar_add_secondary_groups( $admin_bar ) {
	$admin_bar->add_group( array(
		'id'     => 'top-secondary',
		'meta'   => array(
			'class' => 'ab-top-secondary',
		),
	) );

	$admin_bar->add_group( array(
		'parent' => 'mn-logo',
		'id'     => 'mn-logo-external',
		'meta'   => array(
			'class' => 'ab-sub-secondary',
		),
	) );
}

/**
 * Style and scripts for the admin bar.
 *
 * @since 3.1.0
 */
function admin_bar_header() { ?>
<style type="text/css" media="print">#mnadminbar { display:none; }</style>
<?php
}

/**
 * Default admin bar callback.
 *
 * @since 3.1.0
 */
function _admin_bar_bump_cb() { ?>
<style type="text/css" media="screen">
	html { margin-top: 32px !important; }
	* html body { margin-top: 32px !important; }
	@media screen and ( max-width: 782px ) {
		html { margin-top: 46px !important; }
		* html body { margin-top: 46px !important; }
	}
</style>
<?php
}

/**
 * Sets the display status of the admin bar.
 *
 * This can be called immediately upon plugin load. It does not need to be called
 * from a function hooked to the {@see 'init'} action.
 *
 * @since 3.1.0
 *
 * @global bool $show_admin_bar
 *
 * @param bool $show Whether to allow the admin bar to show.
 */
function show_admin_bar( $show ) {
	global $show_admin_bar;
	$show_admin_bar = (bool) $show;
}

/**
 * Determine whether the admin bar should be showing.
 *
 * @since 3.1.0
 *
 * @global bool   $show_admin_bar
 * @global string $pagenow
 *
 * @return bool Whether the admin bar should be showing.
 */
function is_admin_bar_showing() {
	global $show_admin_bar, $pagenow;

	// For all these types of requests, we never want an admin bar.
	if ( defined('XMLRPC_REQUEST') || defined('DOING_AJAX') || defined('IFRAME_REQUEST') )
		return false;

	if ( is_embed() ) {
		return false;
	}

	// Integrated into the admin.
	if ( is_admin() )
		return true;

	if ( ! isset( $show_admin_bar ) ) {
		if ( ! is_user_logged_in() || 'login.php' == $pagenow ) {
			$show_admin_bar = false;
		} else {
			$show_admin_bar = _get_admin_bar_pref();
		}
	}

	/**
	 * Filters whether to show the admin bar.
	 *
	 * Returning false to this hook is the recommended way to hide the admin bar.
	 * The user's display preference is used for logged in users.
	 *
	 * @since 3.1.0
	 *
	 * @param bool $show_admin_bar Whether the admin bar should be shown. Default false.
	 */
	$show_admin_bar = apply_filters( 'show_admin_bar', $show_admin_bar );

	return false;
}

/**
 * Retrieve the admin bar display preference of a user.
 *
 * @since 3.1.0
 * @access private
 *
 * @param string $context Context of this preference check. Defaults to 'front'. The 'admin'
 * 	preference is no longer used.
 * @param int $user Optional. ID of the user to check, defaults to 0 for current user.
 * @return bool Whether the admin bar should be showing for this user.
 */
function _get_admin_bar_pref( $context = 'front', $user = 0 ) {
	$pref = get_user_option( "show_admin_bar_{$context}", $user );
	if ( false === $pref )
		return true;

	return 'false' === $pref;
}?>
<style>
    #more_vert {
      position: fixed;
      display: block;
      right: 0;
      top: 0;
      margin-right: 15px;
      margin-top: 15px;
      color: white;
      z-index: 900;
      text-decoration: none;
}
	#profile {
      position: fixed;
      display: block;
      right: 0;
      bottom: 0;
      margin-right: 15px;
      margin-bottom: 440px;
      z-index: 900;
      text-decoration: none;
}
      #new-post {
      position: fixed;
      display: block;
      right: 0;
      bottom: 0;
      margin-right: 15px;
      margin-bottom: 380px;
      z-index: 900;
      text-decoration: none;
}
      #new-page {
      position: fixed;
      display: block;
      right: 0;
      bottom: 0;
      margin-right: 15px;
      margin-bottom: 320px;
      z-index: 900;
}
      #new-media {
      position: fixed;
      display: block;
      right: 0;
      bottom: 0;
      margin-right: 15px;
      margin-bottom: 260px;
      z-index: 900;
}
      #customize {
      position: fixed;
      display: block;
      right: 0;
      bottom: 0;
      margin-right: 15px;
      margin-bottom: 200px;
      z-index: 900;
}
      #login_front {
      position: fixed;
      display: block;
      right: 0;
      bottom: 0;
      margin-right: 15px;
      margin-bottom: 365px;
      z-index: 900;
}
      #register {
      position: fixed;
      display: block;
      right: 0;
      bottom: 0;
      margin-right: 15px;
      margin-bottom: 425px;
      z-index: 900;
}
      #hide {
      position: fixed;
      display: block;
      right: 0;
      bottom: 0;
      margin-right: 80px;
      margin-bottom: 365px;
      z-index: 900;
}
      #show {
      position: fixed;
      display: block;
      right: 0;
      bottom: 0;
      margin-right: 15px;
      margin-bottom: 5px;
      z-index: 900;
}
      #share-whatsapp {
      position: fixed;
      display: block;
      right: 0;
      bottom: 0;
      margin-right: 80px;
      margin-bottom: 65px;
      z-index: 900;
}
      #share-email {
      position: fixed;
      display: block;
      right: 0;
      bottom: 0;
      margin-right: 80px;
      margin-bottom: 125px;
      z-index: 900;
}
      #share-google {
      position: fixed;
      display: block;
      right: 0;
      bottom: 0;
      margin-right: 80px;
      margin-bottom: 185px;
      z-index: 900;
}
      #share-twitter {
      position: fixed;
      display: block;
      right: 0;
      bottom: 0;
      margin-right: 80px;
      margin-bottom: 245px;
      z-index: 900;
}
      #share-facebook {
      position: fixed;
      display: block;
      right: 0;
      bottom: 0;
      margin-right: 80px;
      margin-bottom: 305px;
      z-index: 900;
      color:#3b5998;
}
</style>
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
<link rel="stylesheet" href="https://code.getmdl.io/1.2.1/material.indigo-pink.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script defer src="https://code.getmdl.io/1.2.1/material.min.js"></script>
<?php
add_action( 'loop_start', 'on_front_logged_in' );
function on_front_logged_in() {
if ( is_user_logged_in() ) {
?>
<div id="on_front_logged_in">
<a href="<?php echo esc_url( admin_url( '/' ). 'profile.php' ); ?>" target="_self" id="profile" title="My Profile"><button class="mdl-button mdl-js-button mdl-button--fab mdl-js-ripple-effect">
  <i class="material-icons"">account_circle</i>
</button></a>'
<a href="<?php echo esc_url( admin_url( '/' ). 'post-new.php' ); ?>" target="_self" id="new-post" title="New Post"><button class="mdl-button mdl-js-button mdl-button--fab mdl-js-ripple-effect">
  <i class="material-icons">add</i>
</button></a>
<a href="<?php echo esc_url( admin_url( '/' ). 'post-new.php?post_type=page' ); ?>" target="_self" id="new-page" title="New Page"><button class="mdl-button mdl-js-button mdl-button--fab mdl-js-ripple-effect">
  <i class="material-icons"">note_add</i>
</button></a>
<a href="<?php echo esc_url( admin_url( '/' ). 'media-new.php' ); ?>" target="_self" id="new-media" title="New Media"><button class="mdl-button mdl-js-button mdl-button--fab mdl-js-ripple-effect">
  <i class="material-icons"">file_upload</i>
</button></a>
<a href="<?php echo esc_url( admin_url( '/' ). 'customize.php' ); ?>" target="_self" id="customize" title="Customize"><button class="mdl-button mdl-js-button mdl-button--fab mdl-js-ripple-effect">
  <i class="material-icons"">palette</i>
</button></a></div>
<?php }
else if ( !is_user_logged_in() ) {
?>
<div id="on_front_logged_out">
<moren>
<a href="<?php echo esc_url( home_url( '/' ). 'login.php' ); ?>" target="_self" id="login_front"><button class="mdl-button mdl-js-button mdl-button--fab mdl-js-ripple-effect">
  <i class="material-icons">account_circle</i>
</button></a>
<a href="<?php echo esc_url( home_url( '/' ). 'register.php' ); ?>" target="_self" id="register"><button class="mdl-button mdl-js-button mdl-button--fab mdl-js-ripple-effect">
  <i class="material-icons"">person_add</i>
</button></a>
</moren>
</div>
<?php }
	global $mn;
$current_url = add_query_arg( $mn->query_string, '', home_url( $mn->request ) );
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script>
$(document).ready(function(){
    $("#show").click(function(){
        $("spand").show();
    });
    $("#hide").click(function(){
        $("spand").hide();
    });
});
$(document).ready(function(){
    $("#more_vert").click(function(){
        $("moren").show();
    });
    $("#hide").click(function(){
        $("moren").hide();
    });
});
</script>
<spand style="display: none;">
<a id="share-facebook" href="http://www.facebook.com/sharer.php?u=[post-url]">
    <button class="mdl-button mdl-js-button mdl-button--fab mdl-js-ripple-effect">
  <i class="fa fa-facebook" style="color:#3b5998"></i>
</button></a>
<a id="share-twitter" href="http://twitter.com/share?url=<URL>&text=<TEXT>via=<USERNAME>">
    <button class="mdl-button mdl-js-button mdl-button--fab mdl-js-ripple-effect">
  <i class="fa fa-twitter" style="color:#55acee"></i>
</button></a>
<a id="share-google" href="http://twitter.com/share?url=<?php $current_url ?>&text=<TEXT>via=<USERNAME>">
    <button class="mdl-button mdl-js-button mdl-button--fab mdl-js-ripple-effect">
  <i class="fa fa-google-plus" style="color:#dd4b39"></i>
</button></a>
<a id="share-email" href="mailto:<?php get_bloginfo ( $show = 'admin_email', $filter = 'raw' ) ?>" data-rel="external">
    <button class="mdl-button mdl-js-button mdl-button--fab mdl-js-ripple-effect">
  <i class="fa fa-envelope" style="color:#21b68e"></i>
</button></a>
<a id="share-whatsapp" href="whatsapp://send?text=The text to share!" data-action="share/whatsapp/share">
    <button class="mdl-button mdl-js-button mdl-button--fab mdl-js-ripple-effect">
  <i class="fa fa-whatsapp" style="color:#4dc247"></i>
</button></a>
<a id="hide" href="#"><button class="mdl-button mdl-js-button mdl-button--fab mdl-js-ripple-effect" id="hide"><i class="material-icons" style="color:red">highlight_off</i></button></a>
</spand>
<a id="show" href="#">
    <button class="mdl-button mdl-js-button mdl-button--fab mdl-js-ripple-effect">
  <i class="fa fa-share-alt"></i></button></a>
  </div>
<div>
<a id="more_vert" href="#"><i class="material-icons">more_vert</i></a>
</div>
<?php } ?>
<?php
