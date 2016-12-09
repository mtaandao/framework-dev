<?php
$articleClass = ( ! has_post_thumbnail() ) ? 'no-thumbnail ' : '';

$portfolios = mn_get_post_terms( get_the_ID(), 'portfolio' );
if ( is_array( $portfolios ) ) {
    foreach ( $portfolios as $portfolio ) {
        $articleClass .= 'portfolio_' . $portfolio->term_id . '_item ';
    }
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( $articleClass ); ?>>
    <div class="entry-thumbnail-popover">
        <div class="entry-thumbnail-popover-content popover-content--animated">
            <?php the_title( sprintf( '<h2 class="portfolio_item-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>

            <?php if ( option::is_on( 'portfolio_excerpt' ) ) : ?>

                <?php the_excerpt(); ?>

            <?php endif; ?>

            <a class="btn" href="<?php echo esc_url( get_permalink() ); ?>" title="<?php echo esc_attr( get_the_title() ); ?>">
                <?php _e( 'View More', 'mtaa' ); ?>
            </a>
        </div>
    </div>

    <?php if ( has_post_thumbnail() )  : ?>

        <a class="link_to_post" href="<?php echo esc_url( get_permalink() ); ?>" title="<?php echo esc_attr( get_the_title() ); ?>"><?php the_post_thumbnail( 'portfolio_item-thumbnail' ); ?></a>

    <?php else: ?>

        <img width="600" height="400" src="<?php echo get_template_directory_uri() . '/images/portfolio_item-placeholder.gif'; ?>">

    <?php endif; ?>

</article><!-- #post-## -->