<?php
// Creating the widget 
class ese_widget extends MN_Widget {
	function __construct() {
		parent::__construct(

			// Base ID of your widget
			'ese_widget', 
			// Widget name will appear in UI
			__('MDL Dropdown', 'ese'), 
			// Widget description
			array( 
				'description' 	=> __( 'Footer Dropdown links', 'ese' ),
				
			) 
		);
	}			   

	// Creating widget front-end
	// This is where the action happens
	public function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters( 'widget_title', $instance['title'] );
		$text = apply_filters('widget_text', $instance['text']);

		// before and after widget arguments are defined by themes
		echo $before_widget;

		echo '<input class="mdl-mega-footer__heading-checkbox" type="checkbox" checked>';

			if ( ! empty( $title ) )
				echo $before_title . $title . $after_title;


			echo '<ul class="mdl-mega-footer__link-list">';
			echo $text;
			echo '</ul>';

		echo $after_widget;
	}

	// Widget Backend 
	public function form( $instance ) {
		$instance = mn_parse_args( (array) $instance, array(
			'title'		=> __('Links','ese' ),
			'text' 		=> ''
			
		) );

		$title = esc_attr($instance['title']);
		$text = $instance['text'];

		// Widget admin form
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'ese' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<p>
            <label for="<?php echo $this->get_field_id('text'); ?>"><?php _e('Content', 'ese'); ?>:</label>
            <textarea class="widefat" rows="16" cols="20" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>"><?php echo $text; ?></textarea>
        </p>
		</p>

	<?php }

	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['text'] = mn_kses_post($new_instance['text']);
		return $instance;
	}
} // Class ese_widget ends here


// Register and load the widget
function ese_load_widget() {
	register_widget( 'ese_widget' );
}
add_action( 'widgets_init', 'ese_load_widget' );