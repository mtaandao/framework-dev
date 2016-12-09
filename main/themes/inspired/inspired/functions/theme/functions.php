<?php
/**
 * Theme functions and definitions
 *
 * Set up the theme and provides some helper functions, which are used in the
 * theme as custom template tags. Others are attached to action and filter
 * hooks in Mtaandao to change core functionality.
 *
 * When using a child theme you can override certain functions (those wrapped
 * in a function_exists() call) by defining them first in your child theme's
 * functions.php file. The child theme's functions.php file is included before
 * the parent theme's file, so the child theme functions would be used.
 */

if ( ! function_exists( 'inspired_setup' ) ) :
/**
 * Theme setup.
 *
 * Set up theme defaults and registers support for various Mtaandao features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support post thumbnails.
 */
function inspired_setup() {
    // This theme styles the visual editor to resemble the theme style.
    add_editor_style( array( 'css/editor-style.css' ) );

    /* Homepage Slider */
    add_image_size( 'featured', 1600 );
    add_image_size( 'featured-small', 800 );

    add_image_size( 'recent-thumbnail', 360, 200, true );
    add_image_size( 'woo-featured', 280, 280, true );
    add_image_size( 'entry-cover', 1200 );
    add_image_size( 'portfolio_item-thumbnail', 600, 400, true );
    add_image_size( 'portfolio-scroller-widget', 9999, 560 );
    add_image_size( 'loop', option::get( 'thumb_width' ), option::get( 'thumb_height' ), true );


    /*
     * Switch default core markup for search form, comment form, and comments
     * to output valid HTML5.
     */
    add_theme_support( 'html5', array(
        'search-form', 'comment-form', 'comment-list', 'gallery', 'caption'
    ) );

    // Register nav menus
    register_nav_menus( array(
        'primary' => __( 'Main Menu', 'mtaa' )
    ) );
}
endif;
add_action( 'after_setup_theme', 'inspired_setup' );


/* Portfolio Module @ Mtaa Framewowk
================================== */

add_theme_support( 'mtaa-portfolio' );

function inspired_filter_portfolio( $query ) {
    if ( $query->is_main_query() && $query->is_tax( 'portfolio' ) ) {
        $query->set( 'posts_per_page', option::get( 'portfolio_posts' ) );
    }

    return $query;
}
add_action( 'pre_get_posts', 'inspired_filter_portfolio' );



/* Slider Metabox for Portfolio @ Mtaa Framewowk
================================== */

add_theme_support( 'mtaa-post-slider', array(
    'portfolio_item' => array(
        'video' => false
    )
) );


/* Get page slide image
================================== */

function inspired_get_slide_image( $slide ) {
    if ( $slide['slideType'] !== 'image' ) return;

    if ( is_numeric( $slide['imageId'] ) ) {
        $image = mn_get_attachment_image_src( $slide['imageId'], 'featured' );
        $height = $image[2];
        $image = $image[0];
    } else {
        $image = $slide['imageId'];
        $height = false !== ($size = @getimagesize( $image )) ? $size[1] : 1600;
        unset($size);
    }

    $caption = '';
    if ( isset($slide['caption']) ) $caption = trim( $slide['caption'] );

    $description = '';
    if ( isset($slide['description']) ) $description = trim( $slide['description'] );

    return array(
        'src' => $image,
        'caption' => $caption,
        'description' => $description,
        'height' => $height,
        'width' => 1600
    );
}



function is_blog() {
    global $post;
    $posttype = get_post_type( $post );
    return ( ( $posttype == 'post' ) && ( is_home() || is_single() || is_archive() || is_category() || is_tag() || is_author() ) ) ? true : false;
}

function fix_blog_link_on_cpt( $classes, $item, $args ) {
    if( !is_blog() ) {
        $blog_page_id = intval( get_option('page_for_posts') );
        if( $blog_page_id != 0 && $item->object_id == $blog_page_id )
            unset($classes[array_search('current_page_parent', $classes)]);
    }
    return $classes;
}
add_filter( 'nav_menu_css_class', 'fix_blog_link_on_cpt', 10, 3 );



/*  Add support for Custom Background
==================================== */

add_theme_support( 'custom-background' );


/*  WooCommerce Support
==================================== */

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    add_theme_support( 'woocommerce' );
}


/*  WooCommerce Extra Features
 *
 * Change number of related products on product page
 * ==================================== */

if ( !function_exists( 'diamond_wc_menu_cartitem' ) ) :
    /**
     * Generates list item for WooCommerce cart to be used in nav menu.
     */
    function diamond_wc_menu_cartitem() {
        global $woocommerce;

        if ( !current_theme_supports( 'woocommerce' ) ) return;
        if ( !option::is_on( 'cart_icon' ) ) return;

        return '<li class="menu-item"><a href="' . $woocommerce->cart->get_cart_url() . '" title="' . __( 'View your shopping cart', 'mtaa' ) . '" class="cart-button">' . '<span>' . sprintf( _n( '%d item &ndash; ', '%d items &ndash; ', $woocommerce->cart->get_cart_contents_count(), 'mtaa' ), $woocommerce->cart->get_cart_contents_count() ) . $woocommerce->cart->get_cart_total() . '</span></a></li>';
    }
endif;

if ( !function_exists( 'woo_related_products_limit' ) ) :
    function woo_related_products_limit() {
        global $product;

        $args = array(
            'post_type' => 'product',
            'no_found_rows' => 1,
            'posts_per_page' => 4,
            'ignore_sticky_posts' => 1,
            'post__not_in' => array( $product->id )
        );

        return $args;
    }
endif;
add_filter( 'woocommerce_related_products_args', 'woo_related_products_limit' );

add_filter( 'add_to_cart_fragments', 'woocommerce_header_add_to_cart_fragment' );

function woocommerce_header_add_to_cart_fragment( $fragments ) {
    global $woocommerce;

    ob_start();

    ?>
    <a class="cart-button" href="<?php echo $woocommerce->cart->get_cart_url(); ?>"
       title="<?php _e( 'View your shopping cart', 'mtaa' ); ?>"><?php echo sprintf( _n( '%d item', '%d items', $woocommerce->cart->cart_contents_count, 'mtaa' ), $woocommerce->cart->cart_contents_count ); ?> &ndash; <?php echo $woocommerce->cart->get_cart_total(); ?></a>
    <?php

    $fragments['a.cart-button'] = ob_get_clean();

    return $fragments;

}

/*  Add Support for Shortcodes in Excerpt
========================================== */

add_filter( 'the_excerpt', 'shortcode_unautop' );
add_filter( 'the_excerpt', 'do_shortcode' );

add_filter( 'widget_text', 'shortcode_unautop' );
add_filter( 'widget_text', 'do_shortcode' );


/*  Custom Excerpt Length
==================================== */

function new_excerpt_length( $length ) {
    return (int) option::get( "excerpt_length" ) ? (int)option::get( "excerpt_length" ) : 50;
}

add_filter( 'excerpt_length', 'new_excerpt_length' );



/* Replaces the excerpt "more" text by a link
=========================================== */

function new_excerpt_more( $more ) {
    global $post;

    return ' […] <div class="clear"></div><a class="more_link clearfix" href="' . get_permalink( $post->ID ) . '" rel="nofollow">' . __( 'Read More', 'mtaa' ) . '</a>';
}

add_filter( 'excerpt_more', 'new_excerpt_more' );



/* Enable Excerpts for Pages
==================================== */

add_action( 'init', 'mtaa_excerpts_to_pages' );
function mtaa_excerpts_to_pages() {
    add_post_type_support( 'page', 'excerpt' );
}


/* Jetpack's Infinite Scroll support
=========================================== */

function mtaa_infinite_scroll_init() {
    add_theme_support( 'infinite-scroll', array(
        'container' => 'main',
        'wrapper' => '.recent-posts'
    ) );
}

add_action( 'after_setup_theme', 'mtaa_infinite_scroll_init' );


/*  Maximum width for images in posts
=========================================== */

if ( ! isset( $content_width ) ) $content_width = 950;


/* Email validation
==================================== */

function simple_email_check( $email ) {
    // First, we check that there's one @ symbol, and that the lengths are right
    if ( !preg_match( "/^[^@]{1,64}@[^@]{1,255}$/", $email ) ) {
        // Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
        return false;
    }

    return true;
}


/* Returns true if there is a chance that current post has cover
==================================== */

function inspired_maybeWithCover() {
    return is_single() && has_post_thumbnail( get_queried_object_id() );
}


/* Comments Custom Template
==================================== */

function mtaa_comment( $comment, $args, $depth ) {
    $GLOBALS['comment'] = $comment;
    switch ( $comment->comment_type ) :
        case '' :
            ?>
            <li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
            <div id="comment-<?php comment_ID(); ?>">
                <div class="comment-author vcard">
                    <?php echo get_avatar( $comment, 50 ); ?>
                    <?php printf( '<cite class="fn">%s</cite>', get_comment_author_link() ); ?>

                    <div class="comment-meta commentmetadata"><a
                            href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
                            <?php printf( __( '%s @ %s', 'mtaa' ), get_comment_date(), get_comment_time() ); ?></a>
                            <?php comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'], 'reply_text' => 'Reply', 'before' => '&nbsp;·&nbsp;&nbsp;' ) ) ); ?>
                            <?php edit_comment_link( __( 'Edit', 'mtaa' ), '&nbsp;·&nbsp;&nbsp;' ); ?>

                    </div>
                    <!-- .comment-meta .commentmetadata -->

                </div>
                <!-- .comment-author .vcard -->
                <?php if ( $comment->comment_approved == '0' ) : ?>
                    <em class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'mtaa' ); ?></em>
                    <br/>
                <?php endif; ?>

                <div class="comment-body"><?php comment_text(); ?></div>

            </div><!-- #comment-##  -->

            <?php
            break;
        case 'pingback'  :
        case 'trackback' :
            ?>
            <li class="post pingback">
            <p><?php _e( 'Pingback:', 'mtaa' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __( '(Edit)', 'mtaa' ), ' ' ); ?></p>
            <?php
            break;
    endswitch;
}




/* Register custom shortcodes
==================================== */

function mnz_shortcode_fullscreen( $atts, $content = null ) {
    extract( shortcode_atts( array(
      'title' => __( 'Fullscreen Image', 'mtaa' ),
    ), $atts ) );

    return '<div class="fullimg">'  . do_shortcode( $content ) . '</div>' . "\n";
}
add_shortcode( 'fullscreen', 'mnz_shortcode_fullscreen' );



function add_fullscreen_buttons() {
    if ( !current_user_can('edit_posts') && ! current_user_can('edit_pages') ) {
        return;
    }

    if ( get_user_option('rich_editing') == 'true' ) {
        add_filter( 'mce_external_plugins', 'add_fullscreen_tinymce_plugin' );
        add_filter( 'mce_buttons', 'register_fullscreen_buttons' );
    }
}
add_action( 'init', 'add_fullscreen_buttons' );

function register_fullscreen_buttons( $buttons ) {
    array_push( $buttons, "|", "fullscreen" );
    return $buttons;
}
function add_fullscreen_tinymce_plugin( $plugin_array ) {
    $plugin_array['fullscreen_btn'] = get_template_directory_uri() . '/functions/theme/assets/js/fullscreen_buttons.js';
    return $plugin_array;
}

function fullscreen_refresh_mce( $ver ) {
    $ver += 3;
    return $ver;
}
add_filter( 'tiny_mce_version', 'fullscreen_refresh_mce' );




/* Enqueue scripts and styles for the front end.
=========================================== */

function inspired_scripts() {
    // Load our main stylesheet.
    mn_enqueue_style( 'inspired-style', get_stylesheet_uri() );

    mn_enqueue_style( 'media-queries', get_template_directory_uri() . '/css/media-queries.css', array(), Mtaa::$themeVersion );

    mn_enqueue_style( 'dashicons' );

    mn_enqueue_script( 'flexslider', get_template_directory_uri() . '/js/flexslider.min.js', array( 'jquery' ), Mtaa::$themeVersion, true );

    mn_enqueue_script( 'caroufredsel', get_template_directory_uri() . '/js/caroufredsel.min.js', array( 'jquery' ), Mtaa::$themeVersion, true );

    mn_enqueue_script( 'fitvids', get_template_directory_uri() . '/js/fitvids.min.js', array( 'jquery' ), Mtaa::$themeVersion, true );

    mn_enqueue_script( 'imagesLoaded', get_template_directory_uri() . '/js/imagesLoaded.min.js', array(), Mtaa::$themeVersion, true );

    mn_enqueue_script( 'superfish', get_template_directory_uri() . '/js/superfish.min.js', array( 'jquery' ), Mtaa::$themeVersion, true );

    // Enqueue Isotope when Portfolio Isotope template is used.
    if ( is_page_template( 'portfolio/archive-isotope.php' ) ) {
        mn_enqueue_script( 'isotope', get_template_directory_uri() . '/js/isotope.pkgd.min.js', array( ), Mtaa::$themeVersion, true );
    }

    $themeJsOptions = option::getJsOptions();

    mn_enqueue_script( 'inspired-script', get_template_directory_uri() . '/js/functions.js', array( 'jquery' ), Mtaa::$themeVersion, true );
    mn_localize_script( 'inspired-script', 'mtaaOptions', $themeJsOptions );
}

add_action( 'mn_enqueue_scripts', 'inspired_scripts' );
