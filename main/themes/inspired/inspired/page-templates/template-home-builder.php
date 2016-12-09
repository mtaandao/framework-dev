<?php
/**
 * Template Name: Homepage (Mtaa Builder)
 */

get_header(); ?>

<?php if ( option::is_on( 'featured_posts_show' ) ) : ?>

    <?php get_template_part( 'mtaa-slider' ); ?>

<?php endif; ?>

<main id="main" class="site-main" role="main">

    <?php while ( have_posts() ) : the_post(); ?>

        <?php get_template_part( 'content', 'page' ); ?>

    <?php endwhile; // end of the loop. ?>

</main><!-- #main -->

<?php
get_footer();
