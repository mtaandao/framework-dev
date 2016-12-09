<article id="post-<?php the_ID(); ?>" <?php post_class( (has_post_thumbnail() ? ' has-post-cover' : '') ); ?>>
    <div class="entry-cover">
        <?php $entryCoverBackground = get_the_image( array( 'size' => 'entry-cover', 'format' => 'array' ) ); ?>
        <?php if ( isset( $entryCoverBackground['src'] ) ) : ?>

            <div class="entry-cover-image" style="background-image: url('<?php echo $entryCoverBackground['src'] ?>');"></div>

        <?php endif; ?>

        <header class="entry-header">
            <div class="entry-info">
                <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

                <div class="entry-meta">
                    <?php if ( option::is_on( 'post_category' ) ) : ?><span class="entry-category"><?php _e( 'in', 'mtaa' ); ?> <?php the_category( ', ' ); ?></span><?php endif; ?>
                    <?php if ( option::is_on( 'post_date' ) )     : ?><p class="entry-date"><?php _e( 'on', 'mtaa' ); ?> <?php printf( '<time class="entry-date" datetime="%1$s">%2$s</time> ', esc_attr( get_the_date( 'c' ) ), esc_html( get_the_date() ) ); ?></p> <?php endif; ?>
                </div>
            </div>
        </header><!-- .entry-header -->
    </div><!-- .entry-cover -->

    <div class="entry-content">
        <?php the_content(); ?>
    </div><!-- .entry-content -->


    <footer class="entry-footer">

        <?php
        mn_link_pages( array(
            'before' => '<div class="page-links">' . __( 'Pages:', 'mtaa' ),
            'after'  => '</div>',
        ) );
        ?>


        <?php if ( option::is_on( 'post_tags' ) ) : ?>

            <?php
            the_tags(
                '<div class="tag_list"><h4 class="section-title">' . __( 'Tags', 'mtaa' ). '</h4>',
                '<span class="separator">,</span>',
                '</div>'
            );
            ?>

        <?php endif; ?>



        <div class="prevnext">

            <?php
            $previous_post = get_previous_post();
            $next_post = get_next_post();
            ?>

            <?php if ($previous_post != NULL) { ?>
            <div class="previous">
                <span>&larr; <?php _e('previous', 'mtaa'); ?></span>
                <a href="<?php echo get_permalink($previous_post->ID); ?>" title="<?php echo $previous_post->post_title; ?>"><?php echo $previous_post->post_title; ?></a>
            </div>
            <?php } ?>


            <?php if ($next_post != NULL) { ?>
            <div class="next">
                <span><?php _e('next', 'mtaa'); ?> &rarr;</span>
                <a href="<?php echo get_permalink($next_post->ID); ?>" title="<?php echo $next_post->post_title; ?>"><?php echo $next_post->post_title; ?></a>
            </div>
            <?php } ?>

        </div><!-- /.nextprev -->
        <div class="clear"></div>



        <?php if ( option::is_on( 'post_share' ) ) : ?>

            <div class="share">

                <h4 class="section-title"><?php _e( 'Share', 'mtaa' ); ?></h4>

                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode( get_permalink() ); ?>&text=<?php echo urlencode( get_the_title() ); ?>" target="_blank" title="<?php esc_attr_e( 'Tweet this on Twitter', 'mtaa' ); ?>" class="twitter"><?php echo option::get( 'post_share_label_twitter' ); ?></a>

                <a href="https://facebook.com/sharer.php?u=<?php echo urlencode( get_permalink() ); ?>&t=<?php echo urlencode( get_the_title() ); ?>" target="_blank" title="<?php esc_attr_e( 'Share this on Facebook', 'mtaa' ); ?>" class="facebook"><?php echo option::get( 'post_share_label_facebook' ); ?></a>

                <a href="https://plus.google.com/share?url=<?php echo urlencode( get_permalink() ); ?>" target="_blank" title="<?php esc_attr_e( 'Post this to Google+', 'mtaa' ); ?>" class="gplus"><?php echo option::get( 'post_share_label_gplus' ); ?></a>

            </div>

        <?php endif; ?>


        <?php if ( option::is_on( 'post_author' ) ) : ?>

            <div class="post_author">

                <?php echo get_avatar( get_the_author_meta( 'ID' ) , 65 ); ?>

                <span><?php _e( 'Written by', 'mtaa' ); ?></span>

                <?php the_author_posts_link(); ?>

            </div>

        <?php endif; ?>


        <?php edit_post_link( __( 'Edit', 'mtaa' ), '<span class="edit-link">', '</span>' ); ?>

    </footer><!-- .entry-footer -->
</article><!-- #post-## -->
