<?php

/*------------------------------------------*/
/* Mtaa: Single Page                      */
/*------------------------------------------*/

class Wpmtaa_Single_Page extends MN_Widget {

	/* Widget setup. */
	function Wpmtaa_Single_Page() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'mtaa-singlepage', 'description' => __('Custom Mtaa widget that displays a single specified static page.', 'mtaa') );

		/* Widget control settings. */
		$control_ops = array( 'id_base' => 'mtaa-single-page' );

		/* Create the widget. */
		$this->MN_Widget( 'mtaa-single-page', __('Mtaa: Single Page', 'mtaa'), $widget_ops, $control_ops );
	}

	/* How to display the widget on the screen. */
	function widget( $args, $instance ) {
		extract( $args );

		/* Our variables from the widget settings. */
		$page_id = (int) $instance['page_id'];
 		$show_image = $instance['show_image'] == true;


		if ( empty( $page_id ) || $page_id < 1 ) return false;
		$page_data = get_page( $page_id );
		$title = apply_filters( 'widget_title', trim($page_data->post_title), $instance, $this->id_base );
		$link_title = (bool) $instance['link_title'];

		if ( !empty( $page_data->post_content ) ) {
			echo $before_widget;


			/* Title of widget (before and after defined by themes). */
			if ( $title ) {
				echo $before_title;

				if ( $link_title ) echo '<a href="' . esc_url( get_permalink($page_data->ID) ) . '">';
				echo $title;
				if ( $link_title ) echo '</a>';

				echo $after_title;
			}

			$page_excerpt = trim( $page_data->post_excerpt );

 			echo '<div class="featured_page_content">';

  			if ( $show_image ) {

				if ($page_excerpt) {

					echo '<div class="post-video"><div class="video_cover">';

						echo apply_filters( 'the_content', trim($page_data->post_excerpt) );

					echo '</div></div>';

				} else {
					get_the_image( array( 'post_id' => $page_data->ID, 'link_to_post' => false, 'size' => 'entry-cover', 'width' => 1170, 'before' => '<div class="post-thumb">', 'after' => '</div>' ) );
				}

			}

				echo '<div class="post-content">';

					if ( false !== ($more_tag_pos = strpos( $page_data->post_content, '<!--more-->' ) ) ){

						echo apply_filters( 'the_content', force_balance_tags(trim(substr($page_data->post_content, 0, $more_tag_pos))));

					} else {
						echo apply_filters( 'the_content', $page_data->post_content);
					}

				echo '</div>';

			echo '</div>';

			echo $after_widget;
		}
	}

		/* Update the widget settings.*/
		function update( $new_instance, $old_instance ) {
			$instance = $old_instance;

			/* Strip tags for title and name to remove HTML (important for text inputs). */
 			$instance['page_id'] = (int) $new_instance['page_id'];
			$instance['link_title'] = $new_instance['link_title'];
 			$instance['show_image'] = $new_instance['show_image'] == 'on';

			return $instance;
		}

		/** Displays the widget settings controls on the widget panel.
		 * Make use of the get_field_id() and get_field_name() function when creating your form elements. This handles the confusing stuff. */
		function form( $instance ) {
			/* Set up some default widget settings. */
			$defaults = array( 'page_id' => 0, 'link_title' => true, 'show_image' => true );
			$instance = mn_parse_args( (array) $instance, $defaults );

			?><p>
				<label for="<?php echo $this->get_field_id('page_id'); ?>"><?php _e('Page to Display:', 'mtaa'); ?></label>
				<?php mn_dropdown_pages( array( 'name' => $this->get_field_name('page_id'), 'id' => $this->get_field_id('page_id'), 'selected' => (int) $instance['page_id'] ) ); ?>
			</p>

			<p>
				<label>
					<input class="checkbox" type="checkbox" <?php checked( $instance['show_image'] ); ?> id="<?php echo $this->get_field_id( 'show_image' ); ?>" name="<?php echo $this->get_field_name( 'show_image' ); ?>" />
					<?php _e( 'Display Image/Video at the Top', 'mtaa' ); ?>
				</label>
			</p>


			<p class="description">
				<?php _e('To display a video in the widget, make sure to insert the <strong>embed code</strong> in the <strong>Excerpt</strong> field of the selected page.', 'mtaa'); ?>
			</p>

			<p>
				<input class="checkbox" type="checkbox" <?php checked( $instance['link_title'], 'on' ); ?> id="<?php echo $this->get_field_id( 'link_title' ); ?>" name="<?php echo $this->get_field_name( 'link_title' ); ?>" />
				<label for="<?php echo $this->get_field_id( 'link_title' ); ?>"><?php _e('Link Page Title to Page', 'mtaa'); ?></label>
			</p>
			 <?php
		}
}

function mtaa_register_sp_widget() {
	register_widget('Wpmtaa_Single_Page');
}
add_action('widgets_init', 'mtaa_register_sp_widget');
?>