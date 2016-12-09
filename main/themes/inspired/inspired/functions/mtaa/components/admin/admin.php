<?php
/**
 * Mtaa_Admin
 *
 * @package Mtaa
 * @subpackage Admin
 */

new Mtaa_Admin();

class Mtaa_Admin {

    /**
     * Initialize admin options page
     */
    public function __construct() {
        if (isset($_GET['activated']) && $_GET['activated'] == 'true') {
            header('Location: admin.php?page=mtaa_options');
        }

        if (isset($_GET['page']) && $_GET['page'] == 'mtaa_options') {
            add_action('init', array('Mtaa_Admin_Settings_Page', 'init'));
        }

        add_action('admin_menu', array($this, 'register_admin_pages'));
        add_action('admin_footer', array($this, 'activate'));

        add_action('mn_ajax_zoom_ajax_post',       array('Mtaa_Admin_Settings_Page', 'ajax_options'));
        add_action('mn_ajax_zoom_widgets_default', array('Mtaa_Admin_Settings_Page', 'ajax_widgets_default'));

        add_action('admin_print_scripts-widgets.php', array($this, 'widgets_styling_script'));
        add_action('admin_print_scripts-widgets.php', array($this, 'widgets_styling_css'));

        add_action('admin_enqueue_scripts', array($this, 'mnadmin_script'));
        add_action('admin_enqueue_scripts',  array($this, 'mnadmin_css'));
    }

    public function widgets_styling_script() {
        mn_enqueue_script('mtaa_widgets_styling', Mtaa::$assetsPath . '/js/widgets-styling.js', array('jquery'));
    }

    public function widgets_styling_css() {
        mn_enqueue_style('mtaa_widgets_styling', Mtaa::$assetsPath . '/css/widgets-styling.css');
    }

    public function mnadmin_script() {
        mn_enqueue_script('mtaa-admin', Mtaa::$assetsPath . '/js/admin.js', array('jquery'), Mtaa::$mtaaVersion);
        mn_localize_script('mtaa-admin', 'mtaaFramework', array(
            'rootUri'   => Mtaa::get_root_uri(),
            'assetsUri' => Mtaa::get_assets_uri()
        ));
    }

    public function mnadmin_css() {
        mn_enqueue_style('mtaa-admin', Mtaa::get_assets_uri() . '/css/admin.css', array(), Mtaa::$mtaaVersion);
    }

    public function activate() {
        if (option::get('mtaa_activated') != 'yes') {
            option::set('mtaa_activated', 'yes');
            option::set('mtaa_activated_time', time());
        } else {
            $activated_time = option::get('mtaa_activated_time');
            if ((time() - $activated_time) < 2592000) {
                return;
            }
        }

        option::set('mtaa_activated_time', time());
    }

    public function admin() {
        require_once(Mtaa_INC . '/pages/admin.php');
    }

    public function themes() {
        require_once(Mtaa_INC . '/pages/themes.php');
    }

    public function update() {
        require_once(Mtaa_INC . '/pages/update.php');
    }

    /**
     * Mtaa custom menu for admin
     */
    public function register_admin_pages() {
        add_object_page ( 'Page Title', 'Mtaa', 'manage_options','mtaa_options', array($this, 'admin'), 'none');

        add_submenu_page('mtaa_options', 'Mtaa', 'Theme Options', 'manage_options', 'mtaa_options', array($this, 'admin'));

        if (option::is_on('framework_update_enable')) {
            add_submenu_page('mtaa_options', 'Update Framework', 'Update Framework', 'update_themes', 'mtaa_update', array($this, 'update'));
        }

        if (option::is_on('framework_newthemes_enable') && !mtaa::$tf) {
            add_submenu_page('mtaa_options', 'New Themes', 'New Themes', 'manage_options', 'mtaa_themes', array($this, 'themes'));
        }
    }
}
