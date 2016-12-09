<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

    <?php if ( has_post_thumbnail() ) {
        get_the_image( array( 'size' => 'loop', 'width' => option::get('thumb_width'), 'height' => option::get('thumb_height'), 'before' => '<div class="post-thumb">', 'after' => '</div>' ) );
    } ?>

    <section class="entry-body">
        <header class="entry-header">
            <?php the_title( sprintf( '<h1 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h1>' ); ?>
        </header>

        <div class="entry-content">
            <?php if (option::get('display_content') == 'Full Content') {
                the_content(''.__('Read More', 'mtaa').'');
            }
            if (option::get('display_content') == 'Excerpt')  {
                the_excerpt();
            } ?>
        </div>
    </section>

    <aside class="entry-meta">
        <?php if ( option::is_on( 'display_author' ) )   { print( '<p class="entry-author">' ); the_author_posts_link(); print('</p>'); } ?>
        <?php if ( option::is_on( 'display_date' ) )     printf( '<p class="entry-date"><time class="entry-date" datetime="%1$s">%2$s</time></p>', esc_attr( get_the_date( 'c' ) ), esc_html( get_the_date() ) ); ?>
        <?php if ( option::is_on( 'display_category' ) ) printf( '<p class="cat-links">%s</p>', get_the_category_list( ', ' ) ); ?>
        <?php if ( option::is_on( 'display_comments' ) ) { ?><?php comments_popup_link( __('0 comments', 'mtaa'), __('1 comment', 'mtaa'), __('% comments', 'mtaa'), '', __('Comments are Disabled', 'mtaa')); } ?>


        <?php edit_post_link( __( 'Edit', 'mtaa' ), '<p class="edit-link">', '</p>' ); ?>
    </aside>


    <div class="clearfix"></div>
</article><!-- #post-## -->