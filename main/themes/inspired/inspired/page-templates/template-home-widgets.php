<?php
/**
 * Template Name: Homepage (Widgetized)
 */

get_header(); ?>

<?php if ( option::is_on( 'featured_posts_show' ) ) : ?>

    <?php get_template_part( 'mtaa-slider' ); ?>

<?php endif; ?>

<div class="widgetized-section">
    <?php if ( ! function_exists( 'dynamic_sidebar' ) || ! dynamic_sidebar( 'home-full' ) ) : ?>

    <?php endif; ?>
</div>

<?php
get_footer();
