<?php

/*------------------------------------------*/
/* Mtaa: Featured WooCommerce Products    */
/*------------------------------------------*/

class Mtaa_Featured_Products extends MN_Widget {

	/* Widget setup. */
	function Mtaa_Featured_Products() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'mtaa-featured-products', 'description' => __('Custom Mtaa widget that displays featured WooCommerce products', 'mtaa') );

		/* Widget control settings. */
		$control_ops = array( 'id_base' => 'mtaa-featured-products' );

		/* Create the widget. */
		$this->MN_Widget( 'mtaa-featured-products', __('Mtaa: Featured WooCommerce Products', 'mtaa'), $widget_ops, $control_ops );
	}

	/* How to display the widget on the screen. */
	function widget( $args, $instance ) {
		extract( $args );


		/* User-selected settings. */
		$title 			= apply_filters('widget_title', $instance['title'] );
 		$show_count 	= $instance['show_count'];

  		/* Before widget (defined by themes). */
		echo $before_widget;

 		/* Title of widget (before and after defined by themes). */
		if ( $title )
			echo $before_title . $title . $after_title;

		?>


		<ul class="featured-products">
			<?php
				$woo_args = array( 'post_type' => 'product', 'posts_per_page' => $show_count, 'meta_key' => '_featured', 'meta_value' => 'yes' );
				$woo_loop = new MN_Query( $woo_args );
 				while ( $woo_loop->have_posts() ) : $woo_loop->the_post(); $_product;
				if ( function_exists( 'get_product' ) ) {
					$_product = get_product( $woo_loop->post->ID );
				} else {
					$_product = new WC_Product( $woo_loop->post->ID );
				}
			?>
			<li>

				<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">

					<div class="shop_item_details">

	  					<h3><?php the_title(); ?></h3>

						<span class="price"><?php echo $_product->get_price_html(); ?></span>

					</div>

					<?php get_the_image( array( 'size' => 'woo-featured', 'width' => 280, 'height' => 280, 'before' => '<div class="post-thumb">', 'after' => '</div>', 'link_to_post' => false ) ); ?>

				</a>

			</li>
			<?php endwhile; ?>
		</ul>


		<?php
		/* After widget (defined by themes). */
		echo $after_widget;

	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags (if needed) and update the widget settings. */
		$instance['title'] = strip_tags( $new_instance['title'] );
 		$instance['show_count'] = $new_instance['show_count'];

		return $instance;
	}

	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => 'Featured Products', 'show_count' => 4 );
		$instance = mn_parse_args( (array) $instance, $defaults ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label><br />
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" type="text" class="widefat" />
		</p>


		<p>
			<label for="<?php echo $this->get_field_id( 'show_count' ); ?>">Show:</label>
			<input id="<?php echo $this->get_field_id( 'show_count' ); ?>" name="<?php echo $this->get_field_name( 'show_count' ); ?>" value="<?php echo $instance['show_count']; ?>" type="text" size="2" /> products
		</p>



		<?php
	}
}

function mtaa_register_fpw_widget() {
	register_widget('Mtaa_Featured_Products');
}
add_action('widgets_init', 'mtaa_register_fpw_widget');
?>