<?php
/*----------------------------------------------------------------------------------*/
/*  Mtaa: Twitter Widget
/*----------------------------------------------------------------------------------*/

require_once dirname(__FILE__) . '/twitteroauth/twitteroauth.php';

class mtaa_Twitter extends MN_Widget {
	private $connection;

	private $consumer_key;
	private $consumer_secret;
	private $access_token;
	private $access_token_secret;

	function mtaa_Twitter() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'twitter', 'description' => 'A list of latest tweets' );

		/* Widget control settings. */
		$control_ops = array( 'id_base' => 'mtaa-twitter' );

		/* Create the widget. */
		$this->MN_Widget( 'mtaa-twitter', 'Mtaa: Twitter', $widget_ops, $control_ops );
	}

	function widget( $args, $instance ) {
		extract( $args );

		/* User-selected settings. */
		$title = apply_filters('widget_title', $instance['title'] );
		$username = $instance['username'];
		$show_count = $instance['show_count'];
		$hide_timestamp = isset( $instance['hide_timestamp'] ) ? $instance['hide_timestamp'] : false;
		$linked = $instance['hide_url'] ? false : '#';
		$show_follow = isset( $instance['show_follow'] ) ? $instance['show_follow'] : false;
		$show_followers = isset( $instance['show_followers'] ) ? $instance['show_followers'] : false;

		$this->consumer_key = isset( $instance['consumer_key'] ) ? $instance['consumer_key'] : '';
		$this->consumer_secret = isset( $instance['consumer_secret'] ) ? $instance['consumer_secret'] : '';
		$this->access_token = isset( $instance['access_token'] ) ? $instance['access_token'] : '';
		$this->access_token_secret = isset( $instance['access_token_secret'] ) ? $instance['access_token_secret'] : '';


		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Title of widget (before and after defined by themes). */
		if ( $title )
			echo $before_title . $title . $after_title;

		if ($this->pre_validate_keys() === true) {
			$this->messages($username, $show_count, true, !$hide_timestamp, $linked);
		} else {
			echo '<p>Twitter Widget not configured. <a href="http://www.mtaandao.co.ke/docs/twitter-widget-with-api-version-1-1-setup-instructions/">Read instructions</a>.</p>';
		}


		if ($show_follow) {
			echo '<div class="follow-user"><a href="https://twitter.com/' . $username . '" class="twitter-follow-button"';

			if ($show_followers) {
				echo 'data-show-count="false"';
			}

			echo '>Follow @' . $username . '</a><script src="//platform.twitter.com/widgets.js" type="text/javascript"></script></div>';
		}

		echo '<div class="clear"></div>';

		/* After widget (defined by themes). */
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags (if needed) and update the widget settings. */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['username'] = $new_instance['username'];
		$instance['show_count'] = $new_instance['show_count'];
		$instance['hide_timestamp'] = $new_instance['hide_timestamp'];
		$instance['hide_url'] = $new_instance['hide_url'];
		$instance['show_follow'] = $new_instance['show_follow'];
		$instance['show_followers'] = $new_instance['show_followers'];
		$instance['consumer_key'] = $new_instance['consumer_key'];
		$instance['consumer_secret'] = $new_instance['consumer_secret'];
		$instance['access_token'] = $new_instance['access_token'];
		$instance['access_token_secret'] = $new_instance['access_token_secret'];

		delete_transient( 'mtaa_tw_msg_' . $new_instance['username'] );

		return $instance;
	}

	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => 'Latest Tweets', 'username' => '', 'show_count' => 3, 'hide_timestamp' => false, 'hide_url' => false, 'show_follow' => true, 'show_followers' => true,
						   'consumer_key' => '', 'consumer_secret' => '', 'access_token' => '', 'access_token_secret' => '' );
		$instance = mn_parse_args( (array) $instance, $defaults );
		?>

		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title', 'mtaa'); ?>:</label><br />
		<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'username' ); ?>"><?php _e('Twitter Username', 'mtaa'); ?>:</label>
		<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'username' ); ?>" name="<?php echo $this->get_field_name( 'username' ); ?>" value="<?php echo $instance['username']; ?>"   />
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'show_count' ); ?>"><?php _e('Show', 'mtaa'); ?>:</label>
		<input  type="text" id="<?php echo $this->get_field_id( 'show_count' ); ?>" name="<?php echo $this->get_field_name( 'show_count' ); ?>" value="<?php echo $instance['show_count']; ?>" size="3" /> <?php _e('tweets', 'mtaa'); ?>
		</p>

		<p>
		<input class="checkbox" type="checkbox" <?php checked( $instance['hide_timestamp'], 'on' ); ?> id="<?php echo $this->get_field_id( 'hide_timestamp' ); ?>" name="<?php echo $this->get_field_name( 'hide_timestamp' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'hide_timestamp' ); ?>"><?php _e('Hide Timestamp', 'mtaa'); ?></label>
		</p>

		<p>
		<input class="checkbox" type="checkbox" <?php checked( $instance['hide_url'], 'on' ); ?> id="<?php echo $this->get_field_id( 'hide_url' ); ?>" name="<?php echo $this->get_field_name( 'hide_url' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'hide_url' ); ?>"><?php _e('Hide Tweet URL', 'mtaa'); ?></label>
		</p>

		<p>
		<input class="checkbox" type="checkbox" <?php checked( $instance['show_follow'], 'on' ); ?> id="<?php echo $this->get_field_id( 'show_follow' ); ?>" name="<?php echo $this->get_field_name( 'show_follow' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_follow' ); ?>"><?php _e('Display Follow me button', 'mtaa'); ?></label>
		</p>

		<p>
		<input class="checkbox" type="checkbox" <?php checked( $instance['show_followers'], 'on' ); ?> id="<?php echo $this->get_field_id( 'show_followers' ); ?>" name="<?php echo $this->get_field_name( 'show_followers' ); ?>" />
		<label for="<?php echo $this->get_field_id( 'show_followers' ); ?>"><?php _e('Hide follower count?', 'mtaa'); ?>?</label>
		</p>
		<br/>
		<p><strong>Your Twitter API 1.1 data</strong><em> (required)</em>

		<small><a href="http://www.mtaandao.co.ke/docs/twitter-widget-with-api-version-1-1-setup-instructions/" target="_blank">Instructions getting your Twitter API keys</a></small>
		</p>


		<p>
		<label for="<?php echo $this->get_field_id( 'consumer_key' ); ?>"><?php _e('Consumer key', 'mtaa'); ?>:</label><br />
		<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'consumer_key' ); ?>" name="<?php echo $this->get_field_name( 'consumer_key' ); ?>" value="<?php echo $instance['consumer_key']; ?>" />
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'consumer_secret' ); ?>"><?php _e('Consumer secret', 'mtaa'); ?>:</label><br />
		<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'consumer_secret' ); ?>" name="<?php echo $this->get_field_name( 'consumer_secret' ); ?>" value="<?php echo $instance['consumer_secret']; ?>" />
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'access_token' ); ?>"><?php _e('Access token', 'mtaa'); ?>:</label><br />
		<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'access_token' ); ?>" name="<?php echo $this->get_field_name( 'access_token' ); ?>" value="<?php echo $instance['access_token']; ?>" />
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'access_token_secret' ); ?>"><?php _e('Access token secret', 'mtaa'); ?>:</label><br />
		<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'access_token_secret' ); ?>" name="<?php echo $this->get_field_name( 'access_token_secret' ); ?>" value="<?php echo $instance['access_token_secret']; ?>" />
		</p>

		<?php
	}

	function messages($username = '', $num = 1, $list = false, $update = true, $linked  = '#') {
		$messages = get_transient( 'mtaa_tw_msg_' . $username );
		if ($messages === false) {
			$this->connection = new Mtaa_TwitterOAuth( $this->consumer_key, $this->consumer_secret, $this->access_token, $this->access_token_secret );
			$this->connection->get('account/verify_credentials');

			if ( $this->connection->http_code !== 200 ) {
				echo "Can't query Twitter API. Wrong API credentials or Twitter API Limit exceeded.";
				set_transient( 'mtaa_tw_msg_' . $username, array(), 360 );
				return;
			}

			$params = array(
				'screen_name' => $username,
				'count' => $num,
				'trim_user' => true,
				'contributor_details' => false,
				'include_entities' => false
			);
			$messages = $this->connection->get( 'statuses/user_timeline', $params );

			set_transient( 'mtaa_tw_msg_' . $username, $messages, 360 );
		}

		if ($list) echo '<ul class="twitter-list">';

		if ($username == '' || ( $this->connection && $this->connection->http_code == 404) ) {
			if ($list) echo '<li>';
			echo 'Widget not configured or user does not exist.';
			if ($list) echo '</li>';
		} else {
			if ( !$messages ) {
				if ($list) echo '<li>';
				echo 'No Twitter messages.';
				if ($list) echo '</li>';
			} else {
				$i = 0;
				foreach ( $messages as $message ) {
					$msg = $message->text;
					$link = 'http://twitter.com/'. $username .'/status/'. $message->id_str;

					if ($list) echo '<li class="twitter-item">'; elseif ($num != 1) echo '<p class="twitter-message">';

					$msg = $this->hyperlinks($msg);
					$msg = $this->twitter_users($msg);

					echo $msg;

					if($update) {
						$time = strtotime($message->created_at);

						if ( ( abs( time() - $time) ) < 86400 )
							$h_time = sprintf( __('%s ago', 'mtaa'), human_time_diff( $time ) );
						else
							$h_time = date('M j, Y', $time);

						if ($linked != '' | $linked != false) {
							echo sprintf( __('%s', 'mtaa'),' <div class="clear"></div><a href="'.$link.'" class="twitter-link twitter-timestamp">' . $h_time . '</a>' );
						} else {
							echo sprintf( __('%s', 'mtaa'),' <div class="clear"></div><em class="twitter-timestamp">' . $h_time . '</em>' );
						}
					}

					if ($list) echo '</li>'; elseif ($num != 1) echo '</p>';

					$i++;
					if ( $i >= $num ) break;
				}
			}
		}
		if ($list) echo '</ul>';
	}

	function hyperlinks($text) {
		$text = preg_replace('/\b([a-zA-Z]+:\/\/[\w_.\-]+\.[a-zA-Z]{2,6}[\/\w\-~.?=&%#+$*!]*)\b/i',"<a href=\"$1\" class=\"twitter-link\">$1</a>", $text);
		$text = preg_replace('/\b(?<!:\/\/)(www\.[\w_.\-]+\.[a-zA-Z]{2,6}[\/\w\-~.?=&%#+$*!]*)\b/i',"<a href=\"http://$1\" class=\"twitter-link\">$1</a>", $text);
		$text = preg_replace("/\b([a-zA-Z][a-zA-Z0-9\_\.\-]*[a-zA-Z]*\@[a-zA-Z][a-zA-Z0-9\_\.\-]*[a-zA-Z]{2,6})\b/i","<a href=\"mailto://$1\" class=\"twitter-link\">$1</a>", $text);
		$text = preg_replace('/([\.|\,|\:|\°|\ø|\>|\{|\(]?)#{1}(\w*)([\.|\,|\:|\!|\?|\>|\}|\)]?)\s/i', "$1<a href=\"http://twitter.com/#search?q=$2\" class=\"twitter-link\">#$2</a>$3 ", $text);
		return $text;
	}

	function twitter_users($text) {
		$text = preg_replace('/([\.|\,|\:|\°|\ø|\>|\{|\(]?)@{1}(\w*)([\.|\,|\:|\!|\?|\>|\}|\)]?)\s/i', "$1<a href=\"http://twitter.com/$2\" class=\"twitter-user\">@$2</a>$3 ", $text);
		return $text;
	}

	function pre_validate_keys() {
		if ( ! $this->consumer_key        ) return false;
		if ( ! $this->consumer_secret     ) return false;
		if ( ! $this->access_token        ) return false;
		if ( ! $this->access_token_secret ) return false;

		return true;
	}
}


function mtaa_register_tw_widget() {
	register_widget('mtaa_Twitter');
}

add_action('widgets_init', 'mtaa_register_tw_widget');