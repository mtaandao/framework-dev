<?php

/*------------------------------------------*/
/* Mtaa: Recent Posts           */
/*------------------------------------------*/

class Wpmtaa_Portfolio_Showcase extends MN_Widget {

    function Wpmtaa_Portfolio_Showcase() {
        /* Widget settings. */
        $widget_ops = array( 'classname' => 'portfolio-showcase', 'description' => 'Portfolio posts shown as a gallery.' );

        /* Widget control settings. */
        $control_ops = array( 'id_base' => 'mtaa-portfolio-showcase' );

        /* Create the widget. */
        $this->MN_Widget( 'mtaa-portfolio-showcase', 'Mtaa: Portfolio Showcase', $widget_ops, $control_ops );
    }

    function widget( $args, $instance ) {
        extract( $args );

        /* User-selected settings. */
        $title = apply_filters( 'widget_title', $instance['title'] );
        $category = $instance['category'];
        $show_count = $instance['show_count'];
        $show_excerpt = $instance['show_excerpt'] == true;
        $view_all_enabled = $instance['view_all_enabled'] == true;
        $view_all_text = $instance['view_all_text'];
        $view_all_link = $instance['view_all_link'];

        /* Before widget (defined by themes). */
        echo $before_widget;

        /* Title of widget (before and after defined by themes). */
        if ( $title )
            echo $before_title . $title . $after_title;

        $args = array(
            'post_type' => 'portfolio_item',
            'posts_per_page' => $show_count,
        );

        if ( $category ) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'portfolio',
                    'terms' => $category,
                    'field' => 'term_id',
                )
            );
        }

        $mn_query = new MN_Query( $args );
        ?>

        <?php if ( $mn_query->have_posts() ) : ?>

            <div class="portfolio-grid">

                <?php while ( $mn_query->have_posts() ) : $mn_query->the_post(); ?>

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

                                <?php the_title( sprintf( '<h3 class="portfolio_item-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h3>' ); ?>

                                <?php if ( $show_excerpt == true) {
                                    the_excerpt();
                                 } ?>

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

                <?php endwhile; ?>

                <?php if ( $view_all_enabled ) : ?>

                    <article class="portfolio-view_all-link portfolio_item type-portfolio_item hentry no-thumbnail">
                        <div class="entry-thumbnail-popover">
                            <div class="entry-thumbnail-popover-content popover-content--animated">
                                <a class="btn" href="<?php echo esc_url( $view_all_link ); ?>" title="<?php echo esc_attr( $view_all_text ); ?>">
                                    <?php echo esc_html( $view_all_text ); ?>
                                </a>
                            </div>
                        </div>

                        <img width="600" height="400" src="<?php echo get_template_directory_uri() . '/images/portfolio_item-placeholder.gif'; ?>">

                    </article><!-- .ortfolio-view_all-link -->

                <?php endif; ?>

            </div>

        <?php endif; ?>

        <?php
        /* After widget (defined by themes). */
        echo $after_widget;
    }


    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;

        /* Strip tags (if needed) and update the widget settings. */
        $instance['title'] = strip_tags( $new_instance['title'] );
        $instance['category'] = $new_instance['category'];
        $instance['show_count'] = $new_instance['show_count'];
        $instance['show_excerpt'] = $new_instance['show_excerpt'] == 'on';
        $instance['view_all_enabled'] = $new_instance['view_all_enabled'] == 'on';
        $instance['view_all_text'] = $new_instance['view_all_text'];
        $instance['view_all_link'] = $new_instance['view_all_link'];

        return $instance;
    }

    function form( $instance ) {

        /* Set up some default widget settings. */
        $defaults = array( 'title' => '', 'category' => 0, 'show_count' => 5, 'show_excerpt' => true, 'view_all_enabled' => true, 'view_all_text' => 'View All', 'view_all_link' => '#' );
        $instance = mn_parse_args( (array) $instance, $defaults ); ?>

        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label><br />
            <input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" type="text" class="widefat" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'category' ); ?>">Category:</label>
            <select id="<?php echo $this->get_field_id( 'category' ); ?>" name="<?php echo $this->get_field_name( 'category' ); ?>">
                <option value="0" <?php if ( ! $instance['category'] ) echo 'selected="selected"'; ?>>All</option>
                <?php
                $categories = get_categories( array( 'taxonomy' => 'portfolio' ) );

                foreach( $categories as $cat ) {
                    echo '<option value="' . $cat->cat_ID . '"';

                    if ( $cat->cat_ID == $instance['category'] ) echo  ' selected="selected"';

                    echo '>' . $cat->cat_name . ' (' . $cat->category_count . ')';

                    echo '</option>';
                }
                ?>
            </select>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'show_count' ); ?>">Show:</label>
            <input id="<?php echo $this->get_field_id( 'show_count' ); ?>" name="<?php echo $this->get_field_name( 'show_count' ); ?>" value="<?php echo $instance['show_count']; ?>" type="text" size="2" /> entries
        </p>

        <p>
            <label>
                <input class="checkbox" type="checkbox" <?php checked( $instance['show_excerpt'] ); ?> id="<?php echo $this->get_field_id( 'show_excerpt' ); ?>" name="<?php echo $this->get_field_name( 'show_excerpt' ); ?>" />
                <?php _e( 'Display excerpts', 'mtaa' ); ?>
            </label>
        </p>

        <p>
            <p>
                <input class="checkbox" type="checkbox" <?php checked( $instance['view_all_enabled'] ); ?> id="<?php echo $this->get_field_id( 'view_all_enabled' ); ?>" name="<?php echo $this->get_field_name( 'view_all_enabled' ); ?>" />
                <label for="<?php echo $this->get_field_id( 'view_all_enabled' ); ?>">Display view more thumb</label>
            </p>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'view_all_text' ); ?>">Text for view more thumb:</label><br />
            <input id="<?php echo $this->get_field_id( 'view_all_text' ); ?>" name="<?php echo $this->get_field_name( 'view_all_text' ); ?>" value="<?php echo $instance['view_all_text']; ?>" type="text" class="widefat" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'view_all_link' ); ?>">Link for view more thumb:</label><br />
            <input id="<?php echo $this->get_field_id( 'view_all_link' ); ?>" name="<?php echo $this->get_field_name( 'view_all_link' ); ?>" value="<?php echo $instance['view_all_link']; ?>" type="text" class="widefat" />
        </p>

        <?php
    }
}

function mtaa_register_psc_widget() {
    register_widget('Wpmtaa_Portfolio_Showcase');
}
add_action('widgets_init', 'mtaa_register_psc_widget');