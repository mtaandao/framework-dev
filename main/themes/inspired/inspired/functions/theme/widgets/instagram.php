<?php
/*
	Instagram-Widget-for-Mtaandao
	http://davidmregister.com/instagram-widget-for-mtaandao
	Description: This widget get a users recent images, up to 10, and displays them in a Mtaandao Widget.
	Version: 1.3.1
	Author: David Register, modified by Mtaa
	Author URI: http://davidmregister.com
	License: GPL2
*/

/**
 * Instagrm_Feed_Widget Class
 */
class Instagrm_Feed_Widget extends MN_Widget {
	/** constructor */
	function __construct() {
		parent::MN_Widget(
			/* Base ID */
			'mtaa-instagram',
			/* Name */
			'Mtaa: Instagram', array( 'description' => 'A widget to display a user\'s Instagram feed' ) );
	}

	/* MN_Widget::widget */
	function widget( $args, $instance ) {
		extract( $args );
		//get widget information to display on page
		$title = apply_filters( 'widget_title', $instance['title'] );

		$user_id = apply_filters( 'widget_title', $instance['user_id'] );
		$access_token = apply_filters( 'widget_title', $instance['access_token'] );

		$picture_number = apply_filters( 'widget_title', $instance['picture_number'] );
		$picture_size = 'thumbnail';
		$link_images = apply_filters( 'widget_title', $instance['link_images'] );

 		echo $before_widget;

		if ( $title )
			echo $before_title . $title . $after_title;


		$results = $this->get_recent_data($user_id,$access_token);
		$i=1;
		echo "<ul class='instagram_widget'>";
		if(!empty($results->data)){
			foreach($results->data as $item){
				if($picture_number == 0){
					echo "<strong>".__('Please set the Number of images to show within the widget', 'mtaa')."</strong>";
					break;
				}

				echo "<li>";
				if(!empty($link_images)){
					echo "<a href='".$item->link."' target='_blank'><img src='".$item->images->$picture_size->url."' alt='".$title." image'/></a>";
				}else{
					echo "<img src='".$item->images->$picture_size->url."' alt=''/>";
				}

				echo "</li>";
				if($i == $picture_number){
					echo "</ul>";
					break;
				}else{
					$i++;
				}
			}
		} else {
			echo "<strong>".__('The user currently does not have any images...', 'mtaa')."</strong>";
		}
		echo $after_widget;
	}

	/* MN_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		//update setting with information form widget form
		$instance['title'] = strip_tags($new_instance['title']);

		$instance['access_token'] = strip_tags($new_instance['access_token']);
		$instance['user_id'] = strip_tags($new_instance['user_id']);

		$instance['picture_number'] = strip_tags($new_instance['picture_number']);

		$instance['link_images'] = strip_tags($new_instance['link_images']);

		delete_transient( 'mtaa_w_ig_recent_' . $new_instance['user_id'] );

		return $instance;
	}

	/* MN_Widget::form */
	function form( $instance ) {

		$defaults = array( 'title' => 'Instagram', 'access_token' => '', 'user_id' => '', 'picture_number' => 9, 'link_images' => true );
		$instance = mn_parse_args( (array) $instance, $defaults );

		$title = esc_attr( $instance[ 'title' ] );
		$access_token = esc_attr( $instance[ 'access_token' ] );
		$user_id = esc_attr( $instance[ 'user_id' ] );
		$picture_number = esc_attr( $instance[ 'picture_number' ] );
		$link_images = esc_attr( $instance[ 'link_images' ] );

		?>

		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'mtaa'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('user_id'); ?>"><?php _e('User ID (not username):', 'mtaa'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('user_id'); ?>" name="<?php echo $this->get_field_name('user_id'); ?>" type="text" value="<?php echo $user_id; ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('access_token'); ?>"><?php _e('Access Token:', 'mtaa'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('access_token'); ?>" name="<?php echo $this->get_field_name('access_token'); ?>" type="text" value="<?php echo $access_token; ?>" />
		</p>

		<p class="description">You can find your ID and Access Token on this <a href="http://www.mtaandao.co.ke/instagram/" target="_blank">address</a>.</p>


		<p>
			<label for="<?php echo $this->get_field_id('picture_number'); ?>"><?php _e('Number of Images:', 'mtaa'); ?></label>
			<select id="<?php echo $this->get_field_id('picture_number'); ?>" name="<?php echo $this->get_field_name('picture_number'); ?>">
					<option value="0">Select</option>
				<?php for($i=1;$i<11;$i++):?>
					<option value="<?php echo $i;?>" <?php if($i == $picture_number){echo 'selected="selected"';};?>><?php echo $i;?></option>
				<?php endfor;?>
			</select>
		</p>


		<p>
			<input class="checkbox" id="<?php echo $this->get_field_id('link_images'); ?>" name="<?php echo $this->get_field_name('link_images'); ?>" type="checkbox" <?php echo (($link_images)? "CHECKED":''); ?> />
 			<label for="<?php echo $this->get_field_id('link_images'); ?>"><?php _e('Link images to full image', 'mtaa'); ?></label>
 		</p>


 		<?php
	}

	function get_recent_data($user_id, $access_token) {
		$result = get_transient( 'mtaa_w_ig_recent_' . $user_id );

		if ( $result === false ) {
			$ch = curl_init();

			curl_setopt( $ch, CURLOPT_URL, 'https://api.instagram.com/v1/users/' . $user_id . '/media/recent/?access_token=' . $access_token );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );

			$result = curl_exec( $ch );

			curl_close( $ch );

			$timeout = $result ? 30 : 1;

			set_transient( 'mtaa_w_ig_recent_' . $user_id, $result, $timeout * MINUTE_IN_SECONDS );
		}

		return json_decode ( $result );
	}

} // class Instagrm_Feed_Widget

// register Instagrm widget
add_action( 'widgets_init', create_function( '', 'register_widget("Instagrm_Feed_Widget");' ) );

?>