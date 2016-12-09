<?php
/**
 * Mtaa_Theme_Updater Class
 *
 * @package Mtaa
 * @subpackage Updater
 */

add_action('admin_init', array('Mtaa_Updater', 'init'));

class Mtaa_Updater {
    public static function init() {
        if (option::is_on('framework_update_enable') && 
            option::is_on('framework_update_notification_enable'))
        {
            add_action('admin_head', array('Mtaa_Framework_Updater', 'update_init'));
            add_action('admin_head', array('Mtaa_Framework_Updater', 'check_update'));
        }

        if (option::is_on('framework_theme_update_notification_enable') && !mtaa::$tf) {
            add_action('admin_head', array('Mtaa_Theme_Updater', 'check_update'));
        }

        add_action('mn_ajax_zoom_updater', array(__CLASS__, 'ajax'));
    }

    public static function ajax() {
        if ($_POST['type'] == 'framework-notification-hide') {
            option::set('framework_last_checked', time() + 60 * 60 * 48);
            option::delete('framework_status');

            die();
        }

        if ($_POST['type'] == 'theme-notification-hide') {
            option::set('theme_last_checked', time() + 60 * 60 * 48);
            option::delete('theme_status');

            die();
        }

        die();
    }
}
