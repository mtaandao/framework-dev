<?php
/*
Plugin Name: Mtaa Framework
Plugin URI: http://www.mtaandao.co.ke/framework-tour
Description: Mtaa Framework is a platform which comes packaged with every Mtaa Theme.
Version: 1.4.6
Author: Mtaa
Author URI: http://www.mtaandao.co.ke
Text Domain: mtaa
License: GPLv3 or later
*/

/**
 * Mtaa Framework Core & Heart
 *
 * @package Mtaa
 */
class Mtaa {
    public static $mtaaVersion = '1.4.6';
    public static $mtaaPath;

    public static $assetsPath;

    public static $theme_raw_name;

    public static $themeName;
    public static $themePath;
    public static $themeVersion;

    public static $config;
    public static $themeData;

    public static $tf;

    /**
     * Initializes Mtaa framework
     *
     * @return void
     */
    public static function init() {
        self::load_theme_data();
        option::init(); // 1

        add_action('after_setup_theme', array('option', 'init'), 10); // 2
        add_action('after_setup_theme', array(__CLASS__, 'locale'));

        add_action('admin_bar_menu', array(__CLASS__, 'add_node_to_admin_bar'), 1000);
    }

    /**
     * Mtaandao localization
     *
     * @return void
     */
    public static function locale() {
        load_theme_textdomain('mtaa', get_template_directory() . '/languages');

        $locale     = get_locale();
        $localeFile = get_template_directory() . "/languages/$locale.php";

        if (is_readable($localeFile)) {
            require_once($localeFile);
        }
    }

    /**
     * Load and run theme config file
     *
     * @return boolean
     */
    public static function get_config() {
        return require_once(FUNC_INC . "/theme/config.php");
    }

    public static function get_mtaa_root() {
        return dirname(__FILE__);
    }

    public static function get_root_uri() {
        return get_template_directory_uri() . '/functions/mtaa';
    }

    public static function get_assets_uri() {
        return self::get_root_uri() . '/assets';
    }

    /**
     * Loads theme data and configs
     *
     * @return void
     */
    private static function load_theme_data() {
        self::$config = self::get_config();

        /*
         * Mtaandao 3.4 deprecated `get_theme_data()` so we must use
         * `mn_get_theme()` which returns an instance of `MN_Theme`
         */
        if (function_exists('mn_get_theme')) {
            self::$themeData    = mn_get_theme();
            self::$themeVersion = self::$themeData->version;
            self::$themeName    = self::$themeData->name;
        } else {
            self::$themeData    = get_theme_data(get_template_directory() . '/style.css');
            self::$themeVersion = self::$themeData['Version'];
            self::$themeName    = self::$config['name'];
        }

        self::$theme_raw_name = basename(get_template_directory());
        self::$themePath      = get_template_directory_uri();
        self::$mtaaPath     = self::$themePath . "/functions/mtaa";

        self::$assetsPath = Mtaa::$mtaaPath . '/assets';

        self::$tf = isset(self::$config['tf_url']);
    }

    /**
     * Retrieve metadata from a file.
     *
     * Searches for metadata in the first 8kiB of a file, such as a plugin or theme.
     * Each piece of metadata must be on its own line. Fields can not span multiple
     * lines, the value will get cut at the end of the first line.
     *
     * If the file data is not within that first 8kiB, then the author should correct
     * their plugin file and move the data headers to the top.
     *
     * @see http://codex.mtaandao.org/File_Header
     *
     * @since Mtaandao 2.9.0
     * @param string $file Path to the file
     * @param array $default_headers List of headers, in the format array('HeaderKey' => 'Header Name')
     * @param string $context If specified adds filter hook "extra_{$context}_headers"
     */
    public static function get_file_data( $file, $default_headers, $context = '' ) {
        // We don't need to write to the file, so just open for reading.
        $fp = fopen( $file, 'r' );

        // Pull only the first 8kiB of the file in.
        $file_data = fread( $fp, 8192 );

        // PHP will close file handle, but we are good citizens.
        fclose( $fp );

        // Make sure we catch CR-only line endings.
        $file_data = str_replace( "\r", "\n", $file_data );

        if ( $context && $extra_headers = apply_filters( "extra_{$context}_headers", array() ) ) {
            $extra_headers = array_combine( $extra_headers, $extra_headers ); // keys equal values
            $all_headers = array_merge( $extra_headers, (array) $default_headers );
        } else {
            $all_headers = $default_headers;
        }

        foreach ( $all_headers as $field => $regex ) {
            if ( preg_match( '/^[ \t\/*#@]*' . preg_quote( $regex, '/' ) . ':(.*)$/mi', $file_data, $match ) && $match[1] )
                $all_headers[ $field ] = _cleanup_header_comment( $match[1] );
            else
                $all_headers[ $field ] = '';
        }

        return $all_headers;
    }


    /**
     * Add Theme Options to Admin Bar
     */
    public static function add_node_to_admin_bar($admin_bar) {
        if (!is_super_admin() || !is_admin_bar_showing()) return;

        $admin_bar->add_menu(array('id' => 'mtaa', 'title' => __( 'Mtaa', 'mtaa' ), 'href' => admin_url('admin.php?page=mtaa_options')));
        $admin_bar->add_menu(array('id' => 'mtaa-theme-options', 'parent' => 'mtaa', 'title' => __( 'Theme Options', 'mtaa' ), 'href' => admin_url('admin.php?page=mtaa_options')));

        if (option::is_on('framework_update_enable')) {
            $admin_bar->add_menu(array('id' => 'mtaa-framework-update', 'parent' => 'mtaa', 'title' => __( 'Framework Update', 'mtaa' ), 'href' => admin_url('admin.php?page=mtaa_update')));
        }
    }
}
