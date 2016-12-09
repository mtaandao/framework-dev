<?php
/*------------------------------------------*/
/* Mtaa: Social widget					*/
/*------------------------------------------*/

$socialProfiles = array('Facebook', 'Twitter', 'Google Plus', 'RSS', 'Email', 'Youtube', 'Vimeo', 'LinkedIn', 'Flickr', 'Pinterest', 'Dribbble', 'Behance', 'Tumblr', 'Instagram');
class mtaa_widget_socialize extends MN_Widget {

/* Widget setup. */
function mtaa_widget_socialize() {
	/* Widget settings. */
	$widget_ops = array( 'classname' => 'mtaa_social', 'description' => __('Custom Mtaa Widget that displays links to social sharing websites.', 'mtaa') );

	/* Widget control settings. */
	$control_ops = array( 'width' => 250, 'height' => 350, 'id_base' => 'mtaa-widget-social' );

	/* Create the widget. */
	$this->MN_Widget( 'mtaa-widget-social', __('Mtaa: Social Widget', 'mtaa'), $widget_ops, $control_ops );

}

/* How to display the widget on the screen. */
function widget( $args, $instance ) {

	extract( $args );
	global $socialProfiles;

	/* Our variables from the widget settings. */
	$title = apply_filters('widget_title', $instance['title'] );

	echo $before_widget;
	echo $before_title . $title . $after_title;
	?>
		<ul class="mtaaSocial">
			<?php
			foreach ($socialProfiles as $item)
			{
				$id = strtolower($item);
				$iditem = $id . "_title";
				if ($instance[$id] && $instance[$id] != '') {
				    if (simple_email_check($instance[$id])) { $instance[$id] = 'mailto:' . $instance[$id]; }

				    echo '<li><a class="'.$id.'" href="'.$instance[$id].'" rel="external,nofollow" title="'.$instance[$iditem].'"><img src="'. get_template_directory_uri() .'/images/icons/'.strtolower($item).'.png" alt="'.$instance[$iditem]. '" /></a></li>';
				}
			}
			?>
  		</ul>
		<div class="cleaner">&nbsp;</div>

	<?php
	echo $after_widget;

	mn_reset_query();
	}

	/* Update the widget settings.*/
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );
		global $socialProfiles;

		foreach ($socialProfiles as $item)
		{
			$id = strtolower($item);
			$idtitle = $id . "_title";
			$instance[$id] = strip_tags( $new_instance[$id] );
			$instance[$idtitle] = strip_tags( $new_instance[$idtitle] );
		}

		return $instance;
	}

	/** Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function when creating your form elements. This handles the confusing stuff. */
	function form( $instance ) {

		global $socialProfiles;

		/* Set up some default widget settings. */
		$defaults = array( 'title' => '');
		$instance = mn_parse_args( (array) $instance, $defaults );
    ?>

 		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Widget Title:', 'mtaa'); ?></label>
			<input type="text" class="wideft" size="27" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>"  />
		</p>
		<br/>
		<?php

		foreach ($socialProfiles as $item)
		{
			$id = strtolower($item);
			$idtitle = $id . "_title";

			?>
 	 		<p>
				<img style="float: left; margin:0 8px 0 0;" src="<?php echo get_template_directory_uri(); ?>/images/icons/<?php echo $id; ?>.png" />
 				<label for="<?php echo $this->get_field_id( $id ); ?>"><strong><?php echo $item; ?> URL</strong></label>
				<input type="text" style="margin-left:40px;" id="<?php echo $this->get_field_id( $id ); ?>" name="<?php echo $this->get_field_name( $id ); ?>" value="<?php echo isset( $instance[$id] ) ? $instance[$id] : ''; ?>" size="33"/>

			</p>
			<p style="margin-left:40px;">

 				<label for="<?php echo $this->get_field_id( $idtitle ); ?>">Label</label>
				<input type="text"  id="<?php echo $this->get_field_id( $idtitle ); ?>" name="<?php echo $this->get_field_name( $idtitle ); ?>" value="<?php echo isset( $instance[$idtitle] ) ? $instance[$idtitle] : ''; ?>" size="33" /><br/>
			</p>

 			<br/>
			<?php
		}

		?>

	<?php
	}
}

function mtaa_register_sw_widget() {
	register_widget('mtaa_widget_socialize');
}
add_action('widgets_init', 'mtaa_register_sw_widget');