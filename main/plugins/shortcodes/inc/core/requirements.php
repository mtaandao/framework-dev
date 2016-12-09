<?php
class Su_Requirements {

	static $config = array(
		'php' => '5.1',
		'mn'  => '3.5'
	);

	/**
	 * Constructor
	 */
	function __construct() {
		add_action( 'su/activation', array( __CLASS__, 'php' ) );
		add_action( 'su/activation', array( __CLASS__, 'mn' ) );
	}

	/**
	 * Check PHP version
	 */
	public static function php() {
		$php = phpversion();
		load_plugin_textdomain( 'su', false, dirname( plugin_basename( SU_PLUGIN_FILE ) ), '/languages/' );
		$msg = sprintf( __( '<h1>Oops! Plugin not activated&hellip;</h1> <p>Shortcodes Ultimate is not fully compatible with your PHP version (%s).<br />Reccomended PHP version &ndash; %s (or higher).</p><a href="%s">&larr; Return to the plugins screen</a> <a href="%s"%s>Continue and activate anyway &rarr;</a>', 'shortcodes-ultimate' ), $php, self::$config['php'], network_admin_url( 'plugins.php?deactivate=true' ), $_SERVER['REQUEST_URI'] . '&continue=true', ' style="float:right;font-weight:bold"' );
		// Check Forced activation
		if ( isset( $_GET['continue'] ) ) return;
		// PHP version is too low
		elseif ( version_compare( self::$config['php'], $php, '>' ) ) {
			deactivate_plugins( plugin_basename( SU_PLUGIN_FILE ) );
			mn_die( $msg );
		}
	}

	/**
	 * Check Mtaandao version
	 */
	public static function mn() {
		$mn = get_bloginfo( 'version' );
		load_plugin_textdomain( 'su', false, dirname( plugin_basename( SU_PLUGIN_FILE ) ), '/languages/' );
		$msg = sprintf( __( '<h1>Oops! Plugin not activated&hellip;</h1> <p>Shortcodes Ultimate is not fully compatible with your version of Mtaandao (%s).<br />Reccomended Mtaandao version &ndash; %s (or higher).</p><a href="%s">&larr; Return to the plugins screen</a> <a href="%s"%s>Continue and activate anyway &rarr;</a>', 'shortcodes-ultimate' ), $mn, self::$config['mn'], network_admin_url( 'plugins.php?deactivate=true' ), $_SERVER['REQUEST_URI'] . '&continue=true', ' style="float:right;font-weight:bold"' );
		// Check Forced activation
		if ( isset( $_GET['continue'] ) ) return;
		// PHP version is too low
		elseif ( version_compare( self::$config['mn'], $mn, '>' ) ) {
			deactivate_plugins( plugin_basename( SU_PLUGIN_FILE ) );
			mn_die( $msg );
		}
	}

}

new Su_Requirements;
