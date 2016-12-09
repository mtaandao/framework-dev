<?php
/**
 * Twenty Seventeen: Customizer
 *
 * @package Mtaandao
 * @subpackage Twenty_Seventeen
 * @since 1.0
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param MN_Customize_Manager $mn_customize Theme Customizer object.
 */
function twentyseventeen_customize_register( $mn_customize ) {
	$mn_customize->get_setting( 'blogname' )->transport          = 'postMessage';
	$mn_customize->get_setting( 'blogdescription' )->transport   = 'postMessage';
	$mn_customize->get_setting( 'header_textcolor' )->transport  = 'postMessage';

	$mn_customize->selective_refresh->add_partial( 'blogname', array(
		'selector' => '.site-title a',
		'render_callback' => 'twentyseventeen_customize_partial_blogname',
	) );
	$mn_customize->selective_refresh->add_partial( 'blogdescription', array(
		'selector' => '.site-description',
		'render_callback' => 'twentyseventeen_customize_partial_blogdescription',
	) );

	/**
	 * Custom colors.
	 */
	$mn_customize->add_setting( 'colorscheme', array(
		'default'           => 'light',
		'transport'         => 'postMessage',
		'sanitize_callback' => 'twentyseventeen_sanitize_colorscheme',
	) );

	$mn_customize->add_setting( 'colorscheme_hue', array(
		'default'           => 250,
		'transport'         => 'postMessage',
		'sanitize_callback' => 'absint', // The hue is stored as a positive integer.
	) );

	$mn_customize->add_control( 'colorscheme', array(
		'type'    => 'radio',
		'label'    => __( 'Color Scheme', 'twentyseventeen' ),
		'choices'  => array(
			'light'  => __( 'Light', 'twentyseventeen' ),
			'dark'   => __( 'Dark', 'twentyseventeen' ),
			'custom' => __( 'Custom', 'twentyseventeen' ),
		),
		'section'  => 'colors',
		'priority' => 5,
	) );

	$mn_customize->add_control( new MN_Customize_Color_Control( $mn_customize, 'colorscheme_hue', array(
		'mode' => 'hue',
		'section'  => 'colors',
		'priority' => 6,
	) ) );

	/**
	 * Theme options.
	 */
	$mn_customize->add_section( 'theme_options', array(
		'title'    => __( 'Theme Options', 'twentyseventeen' ),
		'priority' => 130, // Before Additional CSS.
	) );

	$mn_customize->add_setting( 'page_layout', array(
		'default'           => 'two-column',
		'sanitize_callback' => 'twentyseventeen_sanitize_page_layout',
		'transport'         => 'postMessage',
	) );

	$mn_customize->add_control( 'page_layout', array(
		'label'       => __( 'Page Layout', 'twentyseventeen' ),
		'section'     => 'theme_options',
		'type'        => 'radio',
		'description' => __( 'When the two column layout is assigned, the page title is in one column and content is in the other.', 'twentyseventeen' ),
		'choices'     => array(
			'one-column' => __( 'One Column', 'twentyseventeen' ),
			'two-column' => __( 'Two Column', 'twentyseventeen' ),
		),
		'active_callback' => 'twentyseventeen_is_view_with_layout_option',
	) );

	/**
	 * Filter number of front page sections in Twenty Seventeen.
	 *
	 * @since Twenty Seventeen 1.0
	 *
	 * @param $num_sections integer
	 */
	$num_sections = apply_filters( 'twentyseventeen_front_page_sections', 4 );

	// Create a setting and control for each of the sections available in the theme.
	for ( $i = 1; $i < ( 1 + $num_sections ); $i++ ) {
		$mn_customize->add_setting( 'panel_' . $i, array(
			'default'           => false,
			'sanitize_callback' => 'absint',
			'transport'         => 'postMessage',
		) );

		$mn_customize->add_control( 'panel_' . $i, array(
			/* translators: %d is the front page section number */
			'label'          => sprintf( __( 'Front Page Section %d Content', 'twentyseventeen' ), $i ),
			'description'    => ( 1 !== $i ? '' : __( 'Select pages to feature in each area from the dropdowns. Add an image to a section by setting a featured image in the page editor. Empty sections will not be displayed.', 'twentyseventeen' ) ),
			'section'        => 'theme_options',
			'type'           => 'dropdown-pages',
			'allow_addition' => true,
			'active_callback' => 'twentyseventeen_is_static_front_page',
		) );

		$mn_customize->selective_refresh->add_partial( 'panel_' . $i, array(
			'selector'            => '#panel' . $i,
			'render_callback'     => 'twentyseventeen_front_page_section',
			'container_inclusive' => true,
		) );
	}
}
add_action( 'customize_register', 'twentyseventeen_customize_register' );

/**
 * Sanitize the page layout options.
 */
function twentyseventeen_sanitize_page_layout( $input ) {
	$valid = array(
		'one-column' => __( 'One Column', 'twentyseventeen' ),
		'two-column' => __( 'Two Column', 'twentyseventeen' ),
	);

	if ( array_key_exists( $input, $valid ) ) {
		return $input;
	}

	return '';
}

/**
 * Sanitize the colorscheme.
 */
function twentyseventeen_sanitize_colorscheme( $input ) {
	$valid = array( 'light', 'dark', 'custom' );

	if ( in_array( $input, $valid ) ) {
		return $input;
	}

	return 'light';
}

/**
 * Render the site title for the selective refresh partial.
 *
 * @since Twenty Seventeen 1.0
 * @see twentyseventeen_customize_register()
 *
 * @return void
 */
function twentyseventeen_customize_partial_blogname() {
	bloginfo( 'name' );
}

/**
 * Render the site tagline for the selective refresh partial.
 *
 * @since Twenty Seventeen 1.0
 * @see twentyseventeen_customize_register()
 *
 * @return void
 */
function twentyseventeen_customize_partial_blogdescription() {
	bloginfo( 'description' );
}

/**
 * Return whether we're previewing the front page and it's a static page.
 */
function twentyseventeen_is_static_front_page() {
	return ( is_front_page() && ! is_home() );
}

/**
 * Return whether we're on a view that supports a one or two column layout.
 */
function twentyseventeen_is_view_with_layout_option() {
	// This option is available on all pages. It's also available on archives when there isn't a sidebar.
	return ( is_page() || ( is_archive() && ! is_active_sidebar( 'sidebar-1' ) ) );
}

/**
 * Bind JS handlers to instantly live-preview changes.
 */
function twentyseventeen_customize_preview_js() {
	mn_enqueue_script( 'twentyseventeen-customize-preview', get_theme_file_uri( '/assets/js/customize-preview.js' ), array( 'customize-preview' ), '1.0', true );
}
add_action( 'customize_preview_init', 'twentyseventeen_customize_preview_js' );

/**
 * Load dynamic logic for the customizer controls area.
 */
function twentyseventeen_panels_js() {
	mn_enqueue_script( 'twentyseventeen-customize-controls', get_theme_file_uri( '/assets/js/customize-controls.js' ), array(), '1.0', true );
}
add_action( 'customize_controls_enqueue_scripts', 'twentyseventeen_panels_js' );
