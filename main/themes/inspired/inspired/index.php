<?php
/**
 * The main template file.
 */

get_header(); ?>

<?php if ( option::is_on( 'featured_posts_show' ) && is_front_page() && $paged < 2) : ?>

    <?php get_template_part( 'mtaa-slider' ); ?>

<?php endif; ?>

<main id="main" class="site-main" role="main">

    <section class="recent-posts">

        <h2 class="section-title">
            <?php if ( is_front_page() ) : ?>

                <?php _e( 'Our Blog', 'mtaa' ); ?>

            <?php else: ?>

                <?php echo get_the_title( get_option( 'page_for_posts' ) ); ?>

            <?php endif; ?>
        </h2>

        <?php if ( have_posts() ) : ?>

            <?php while ( have_posts() ) : the_post(); ?>

                <?php

                get_template_part( 'content', get_post_format() );
                ?>

            <?php endwhile; ?>

            <?php get_template_part( 'pagination' ); ?>

        <?php else: ?>

            <?php get_template_part( 'content', 'none' ); ?>

        <?php endif; ?>

    </section><!-- .recent-posts -->

</main><!-- .site-main -->

<?php
get_footer();
