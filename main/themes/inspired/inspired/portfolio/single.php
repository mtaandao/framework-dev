<?php get_header(); ?>

<?php
$slides = get_post_meta( get_the_ID(), 'mtaa_slider', true );
$hasSlider = is_array( $slides ) && count( $slides ) > 0;
?>

<main id="main" class="site-main container-fluid" role="main">

    <?php while ( have_posts() ) : the_post(); ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class( ( has_post_thumbnail() || $hasSlider ) ? ' has-post-cover' : '' ); ?>>
            <div class="entry-cover">
                <?php $entryCoverBackground = get_the_image( array( 'size' => 'entry-cover', 'format' => 'array' ) ); ?>

                <?php if ( $hasSlider ) :  ?>

                   <div id="slider">
                        <ul class="slides">

                            <?php foreach ( $slides as $slide ) : ?>

                                <?php if ( $slide['slideType'] == 'image' ) : ?>

                                    <?php $img = inspired_get_slide_image( $slide );

                                    $style = ' style="background-image:url(\'' . $img['src'] . '\')" data-bigimg="' . $img['src'] . '"' ;
                                    ?>

                                    <li<?php echo $style; ?>>
                                        <div class="slide-background-overlay"></div>
                                        <div class="li-wrap">

                                            <?php if ( ! empty( $img['caption'] ) || ! empty( $img['description'] ) ) { ?>

                                                <?php if (! empty( $img['caption'] ) ) { ?>
                                                    <h3><?php echo esc_html( $img['caption'] ); ?></h3>
                                                <?php } ?>

                                                <?php if (! empty( $img['description'] )) { ?>
                                                    <div class="excerpt"><?php echo esc_html( $img['description'] ); ?></div>
                                                <?php } ?>

                                            <?php } ?>

                                        </div>
                                    </li>

                                <?php endif; ?>

                            <?php endforeach; ?>

                        </ul>

                        <div id="scroll-to-content" title="<?php esc_attr_e( 'Scroll to Content', 'mtaa' ); ?>">
                            <?php _e( 'Scroll to Content', 'mtaa' ); ?>
                        </div>

                    </div>

                <?php elseif( isset( $entryCoverBackground['src'] ) ) : ?>

                    <div class="entry-cover-image" style="background-image: url('<?php echo $entryCoverBackground['src'] ?>');"></div>

                <?php endif; ?>

                <header class="entry-header">
                    <div class="entry-info">

                        <div class="entry-meta">

                            <?php if ( option::is_on( 'portfolio_category' ) ) : ?>

                                <?php if ( is_array( $tax_menu_items = get_the_terms( get_the_ID(), 'portfolio' ) ) ) : ?>
                                    <?php foreach ( $tax_menu_items as $tax_menu_item ) : ?>
                                        <a href="<?php echo get_term_link( $tax_menu_item, $tax_menu_item->taxonomy ); ?>"><?php echo $tax_menu_item->name; ?></a>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                            <?php endif; ?>

                        </div>


                        <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

                        <div class="entry-meta">
                            <?php if ( option::is_on( 'portfolio_date' ) ) printf( '<p class="entry-date"><time class="entry-date" datetime="%1$s">%2$s</time></p>', esc_attr( get_the_date( 'c' ) ), esc_html( get_the_date() ) ); ?>
                        </div>
                    </div>
                </header><!-- .entry-header -->
            </div><!-- .entry-cover -->

            <div class="entry-content">

                <?php the_content(); ?>

            </div><!-- .entry-content -->


            <footer class="entry-footer">

                <?php if ( option::is_on( 'portfolio_share' ) ) : ?>

                    <div class="share">

                        <h4 class="section-title"><?php _e( 'Share', 'mtaa' ); ?></h4>

                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode( get_permalink() ); ?>&text=<?php echo urlencode( get_the_title() ); ?>" target="_blank" title="<?php esc_attr_e( 'Tweet this on Twitter', 'mtaa' ); ?>" class="twitter"><?php echo option::get( 'portfolio_share_label_twitter' ); ?></a>

                        <a href="https://facebook.com/sharer.php?u=<?php echo urlencode( get_permalink() ); ?>&t=<?php echo urlencode( get_the_title() ); ?>" target="_blank" title="<?php esc_attr_e( 'Share this on Facebook', 'mtaa' ); ?>" class="facebook"><?php echo option::get( 'portfolio_share_label_facebook' ); ?></a>

                        <a href="https://plus.google.com/share?url=<?php echo urlencode( get_permalink() ); ?>" target="_blank" title="<?php esc_attr_e( 'Post this to Google+', 'mtaa' ); ?>" class="gplus"><?php echo option::get( 'portfolio_share_label_gplus' ); ?></a>

                    </div>

                <?php endif; ?>

                <?php if ( option::is_on( 'portfolio_author' ) ) : ?>

                    <div class="post_author">

                        <?php echo get_avatar( get_the_author_meta( 'ID' ) , 65 ); ?>

                        <span><?php _e( 'Written by', 'mtaa' ); ?></span>

                        <?php the_author_posts_link(); ?>

                    </div>

                <?php endif; ?>


                <?php edit_post_link( __( 'Edit', 'mtaa' ), '<span class="edit-link">', '</span>' ); ?>
            </footer><!-- .entry-footer -->
        </article><!-- #post-## -->

        <?php if ( option::is_on( 'portfolio_comments' ) ) : ?>

            <?php comments_template(); ?>

        <?php endif; ?>

    <?php endwhile; // end of the loop. ?>

</main><!-- #main -->

<?php get_footer(); ?>