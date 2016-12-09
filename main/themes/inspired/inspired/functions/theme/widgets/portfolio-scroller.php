<?php

/*------------------------------------------*/
/* Mtaa: Portfolio Scroller               */
/*------------------------------------------*/

class Wpmtaa_Portfolio_Scroller extends MN_Widget {

	function Wpmtaa_Portfolio_Scroller() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'mtaa-portfolio-scroller', 'description' => 'Your portfolio posts in an attractive horizontal scroller.' );

		/* Widget control settings. */
		$control_ops = array( 'id_base' => 'mtaa-portfolio-scroller' );

		/* Create the widget. */
		$this->MN_Widget( 'mtaa-portfolio-scroller', 'Mtaa: Portfolio Scroller', $widget_ops, $control_ops );
	}

	function widget( $args, $instance ) {

		global $mn_query;

		extract( $args );

		/* User-selected settings. */
		$title = apply_filters( 'widget_title', $instance['title'] );
		$category = absint( $instance['category'] );
		$items_num = absint( $instance['items_num'] );
		$items_height = 0 < ( $height = absint( $instance['items_height'] ) ) ? $height : 560;
		$hide_title = $instance['hide_title'] == true;
		$show_date = $instance['show_date'] == true;
		$direction = $instance['direction'] == 'left' ? 'left' : 'right';
		$auto_scroll = $instance['auto_scroll'] == true;
		$scroll_speed = absint( $instance['scroll_speed'] );
		$hover_pause = $instance['hover_pause'] == true;

		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Title of widget (before and after defined by themes). */
		if ( $title )
			echo $before_title . $title . $after_title;

		?>


		<div id="loading">
		    <div class="spinner">
		        <div class="rect1"></div> <div class="rect2"></div> <div class="rect3"></div> <div class="rect4"></div> <div class="rect5"></div>
		    </div>
		</div>

		<div class="portfolio-scroller">


		<?php $query_opts = apply_filters('mtaa_query', array(
			'posts_per_page' => $items_num,
			'post_type' => 'portfolio_item'
		));
		if ( $category > 0 ) $query_opts['tax_query'] = array(
			array(
				'taxonomy' => 'portfolio',
				'terms' => $category
			)
		);
		query_posts($query_opts);

		if ( have_posts() ) : while ( have_posts() ) : the_post();

		$articleClass = ( ! has_post_thumbnail() ) ? 'no-thumbnail ' : '';

		?>


		<article id="post-<?php the_ID(); ?>" <?php post_class( $articleClass ); ?>>
		    <div class="entry-thumbnail-popover">
		        <div class="entry-thumbnail-popover-content popover-content--animated">

		            <?php the_title( sprintf( '<h2 class="portfolio_item-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>

		            <?php the_excerpt(); ?>

		            <a class="btn" href="<?php echo esc_url( get_permalink() ); ?>" title="<?php echo esc_attr( get_the_title() ); ?>">
		                <?php _e( 'View More', 'mtaa' ); ?>
		            </a>
		        </div>
		    </div>

		    <?php get_the_image( array( 'size' => 'portfolio-scroller-widget', 'height' => $items_height ) );  ?>

		</article><!-- #post-## -->




		<?php endwhile; else:

			echo '<p>' . __( 'Nothing to display&hellip;', 'mtaa' ) . '</p>';

		endif;

 		echo '</div>';

		?><script type="text/javascript">

		jQuery(function($) {

			var $c = $('.widget#<?php echo $widget_id; ?> .portfolio-scroller');

			var _direction = '<?php echo $direction; ?>';

			$c.imagesLoaded( function(){

				$('.portfolio-scroller').show();
			 	$('#loading').hide();

				$c.carouFredSel({
	 				direction: _direction,
	 				width: '100%',
	 				auto: <?php echo $auto_scroll === true ? 'true' : 'false'; ?>,
	 				height: <?php echo $items_height; ?>,
	    				items: {
						visible: '+1',
						width: 'variable',
						height: <?php echo $items_height; ?>
					},
	 				scroll: {
						items: 1,
						duration: <?php echo $scroll_speed; ?>,
						timeoutDuration: 0,
						easing: 'linear',
						pauseOnHover: '<?php echo $hover_pause === true ? 'immediate' : 'false'; ?>'
					}
				});

			});


		});


		</script><?php

		//Reset query_posts
		mn_reset_query();

		/* After widget (defined by themes). */
		echo $after_widget;
	}


	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags (if needed) and update the widget settings. */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['category'] = $new_instance['category'];
		$instance['items_num'] = absint( $new_instance['items_num'] );
		$instance['items_height'] = absint( $new_instance['items_height'] );
		$instance['hide_title'] = $new_instance['hide_title'] == 'on';
		$instance['show_date'] = $new_instance['show_date'] == 'on';
		$instance['direction'] = $new_instance['direction'] == 'left' ? 'left' : 'right';
		$instance['auto_scroll'] = $new_instance['auto_scroll'] == 'on';
		$instance['scroll_speed'] =abs( $new_instance['scroll_speed'] );
		$instance['hover_pause'] = $new_instance['hover_pause'] == 'on';

		return $instance;
	}

	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => '', 'category' => 0, 'items_num' => 5, 'items_height' => 560, 'hide_title' => false, 'show_date' => true, 'direction' => 'left', 'auto_scroll' => true, 'scroll_speed' => 7000, 'hover_pause' => true );
		$instance = mn_parse_args( (array) $instance, $defaults ); ?>

		<p>
			<label>
				<?php _e( 'Title:', 'mtaa' ); ?>
				<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" type="text" class="widefat" />
			</label>
		</p>

		<p>
			<label>
				<?php _e( 'Category:', 'mtaa' ); ?>
				<select id="<?php echo $this->get_field_id( 'category' ); ?>" name="<?php echo $this->get_field_name( 'category' ); ?>" class="widefat">
					<option value="0" <?php if ( !$instance['category'] ) echo 'selected="selected"'; ?>>All</option>
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
			</label>
		</p>

		<p>
			<label>
				<?php _e( 'Visible Items:', 'mtaa' ); ?>
				<input id="<?php echo $this->get_field_id( 'items_num' ); ?>" name="<?php echo $this->get_field_name( 'items_num' ); ?>" value="<?php echo absint( $instance['items_num'] ); ?>" type="number" size="4" />
			</label>
			<span class="howto"><?php _e( 'Number of portfolio itmes to show at one time', 'mtaa' ); ?></span>
		</p>

		<p>
			<label>
				<?php _e( 'Item Height:', 'mtaa' ); ?>
				<input id="<?php echo $this->get_field_id( 'items_height' ); ?>" name="<?php echo $this->get_field_name( 'items_height' ); ?>" value="<?php echo absint( $instance['items_height'] ); ?>" type="number" size="4" />
			</label>
			<span class="howto"><?php _e( 'The height of the items in the scroller', 'mtaa' ); ?></span>
		</p>

		<p>
			<label>
				<input class="checkbox" type="checkbox" <?php checked( $instance['hide_title'] ); ?> id="<?php echo $this->get_field_id( 'hide_title' ); ?>" name="<?php echo $this->get_field_name( 'hide_title' ); ?>" />
				<?php _e( 'Hide post title', 'mtaa' ); ?>
			</label>
		</p>

		<p>
			<label>
				<input class="checkbox" type="checkbox" <?php checked( $instance['show_date'] ); ?> id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" />
				<?php _e( 'Display post date', 'mtaa' ); ?>
			</label>
		</p>

		<p>
			<?php _e( 'Direction:', 'mtaa' ); ?>
			<label><input id="<?php echo $this->get_field_id( 'direction' ); ?>" name="<?php echo $this->get_field_name( 'direction' ); ?>" value="left" type="radio" <?php checked( $instance['direction'], 'left' ); ?> /> <?php _e( 'Backward', 'mtaa' ); ?></label>
			<label><input id="<?php echo $this->get_field_id( 'direction' ); ?>" name="<?php echo $this->get_field_name( 'direction' ); ?>" value="right" type="radio" <?php checked( $instance['direction'], 'right' ); ?> /> <?php _e( 'Forward', 'mtaa' ); ?></label>
			<span class="howto"><?php _e( 'The direction the scroller will go', 'mtaa' ); ?></span>
		</p>

		<p>
			<label>
				<input class="checkbox" type="checkbox" <?php checked( $instance['auto_scroll'] ); ?> id="<?php echo $this->get_field_id( 'auto_scroll' ); ?>" name="<?php echo $this->get_field_name( 'auto_scroll' ); ?>" />
				<?php _e( 'Auto-Scroll', 'mtaa' ); ?>
			</label>
			<span class="howto"><?php _e( 'Automatically scroll through the portfolio items', 'mtaa' ); ?></span>
		</p>

		<p>
			<label>
				<?php _e( 'Auto-Scroll Speed:', 'mtaa' ); ?>
				<input id="<?php echo $this->get_field_id( 'scroll_speed' ); ?>" name="<?php echo $this->get_field_name( 'scroll_speed' ); ?>" value="<?php echo absint( $instance['scroll_speed'] ); ?>" type="number" size="4" /> ms
			</label>
			<span class="howto"><?php _e( 'The speed of the scroller in milliseconds', 'mtaa' ); ?></span>
		</p>


		<p>
			<label>
				<input class="checkbox" type="checkbox" <?php checked( $instance['hover_pause'] ); ?> id="<?php echo $this->get_field_id( 'hover_pause' ); ?>" name="<?php echo $this->get_field_name( 'hover_pause' ); ?>" />
				<?php _e( 'Pause on Hover', 'mtaa' ); ?>
			</label>
			<span class="howto"><?php _e( 'Pause the scroller when the user hovers their mouse over it', 'mtaa' ); ?></span>
		</p><?php
	}
}

function mtaa_register_ps_widget() {
	register_widget('Wpmtaa_Portfolio_Scroller');
}
add_action('widgets_init', 'mtaa_register_ps_widget');