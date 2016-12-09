<?php
/**
 * Custom functions that act independently of the theme templates
 *
 * Eventually, some of the functionality here could be replaced by core features
 *
 * @package Ese
 */

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function ese_body_classes( $classes ) {
	// Adds a class of group-blog to blogs with more than 1 published author.
	if ( is_multi_author() ) {
		$classes[] = 'group-blog';
	}

	$classes[] = 'mdl-color-text--grey-700 mdl-base';

	return $classes;
}
add_filter( 'body_class', 'ese_body_classes' );

if ( version_compare( $GLOBALS['mn_version'], '4.1', '<' ) ) :
	/**
	 * Filters mn_title to print a neat <title> tag based on what is being viewed.
	 *
	 * @param string $title Default title text for current view.
	 * @param string $sep Optional separator.
	 * @return string The filtered title.
	 */
	function ese_mn_title( $title, $sep ) {
		if ( is_feed() ) {
			return $title;
		}

		global $page, $paged;

		// Add the blog name.
		$title .= get_bloginfo( 'name', 'display' );

		// Add the blog description for the home/front page.
		$site_description = get_bloginfo( 'description', 'display' );
		if ( $site_description && ( is_home() || is_front_page() ) ) {
			$title .= " $sep $site_description";
		}

		// Add a page number if necessary.
		if ( ( $paged >= 2 || $page >= 2 ) && ! is_404() ) {
			$title .= " $sep " . sprintf( esc_html__( 'Page %s', 'ese' ), max( $paged, $page ) );
		}

		return $title;
	}
	add_filter( 'mn_title', 'ese_mn_title', 10, 2 );

	/**
	 * Title shim for sites older than Mtaandao 4.1.
	 *
	 * @link https://make.mtaandao.org/core/2014/10/29/title-tags-in-4-1/
	 * @todo Remove this function when Mtaandao 4.3 is released.
	 */
	function ese_render_title() {
		?>
		<title><?php mn_title( '|', true, 'right' ); ?></title>
		<?php
	}
	add_action( 'mn_head', 'ese_render_title' );
endif;

/**
 * Custom Read More Button
 */

function modify_read_more_link() {
	return '<br><br><a class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored mdl-js-ripple-effect" href="' . get_permalink() . '">'. __( 'Read More', 'ese' ). '</a>';
}
add_filter( 'the_content_more_link', 'modify_read_more_link' );
