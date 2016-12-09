<?php
/**
 * The template for displaying the footer
 *
 * Contains footer content and the closing of the #main and #page div elements.
 */
?>

<footer id="colophon" class="site-footer" role="contentinfo">

    <?php if ( is_active_sidebar( 'footer_1' ) ||  is_active_sidebar( 'footer_2'  )  ||  is_active_sidebar( 'footer_3'  ) ) : ?>
        <div class="footer-widgets widgets">

            <?php if ( is_active_sidebar( 'footer_1'  ) ) { ?>
                <div class="column">
                    <?php dynamic_sidebar('footer_1'); ?>
                </div><!-- .column -->
            <?php } ?>

            <?php if ( is_active_sidebar( 'footer_2'  ) ) { ?>
                <div class="column">
                    <?php dynamic_sidebar('footer_2'); ?>
                </div><!-- .column -->
            <?php } ?>

            <?php if ( is_active_sidebar( 'footer_3'  ) ) { ?>
                <div class="column">
                    <?php dynamic_sidebar('footer_3'); ?>
                </div><!-- .column -->
            <?php } ?>

         </div><!-- .footer-widgets -->

        <hr class="site-footer-separator">
    <?php endif; ?>

    <div class="site-info">
        <p class="copyright">
            &copy; <?php echo date( 'Y' ); ?> &mdash; <?php bloginfo('name'); ?>. <?php _e( 'All Rights Reserved', 'mtaa' ); ?>
        </p>
        <p class="designed-by">
            <?php printf( __( 'Powered by %s', 'mtaa' ), '<a href="http://www.mtaandao.co.ke/" target="_blank" rel="designer">Mtaandao</a>' ); ?>
        </p>
    </div><!-- .site-info -->
</footer><!-- #colophon -->

<?php mn_footer(); ?>

</body>
</html>