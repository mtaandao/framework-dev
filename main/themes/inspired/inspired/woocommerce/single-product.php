<?php
/**
 * The Template for displaying all single products.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

get_header( 'shop' ); ?>

<main id="main" class="site-main container-fluid" role="main">

    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

        <div class="entry-content">

            <?php do_action('woocommerce_output_content_wrapper'); ?>

            <?php while ( have_posts() ) : the_post(); ?>

                <?php woocommerce_get_template_part( 'content', 'single-product' ); ?>

            <?php endwhile; // end of the loop. ?>

        </div>

        <div class="clearfix"></div>
    </article><!-- #post-## -->

</main><!-- #main -->

<?php get_footer('shop'); ?>