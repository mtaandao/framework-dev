<?php
/**
 * Mtaa_Theme_Updater Class
 *
 * @package Mtaa
 * @subpackage Theme_Updater
 */

class Mtaa_Theme_Updater {
    /**
     * Returns local theme version
     *
     * @return string
     */
    public static function get_local_version() {
        return Mtaa::$themeVersion;
    }

    /**
     * Returns current theme version pulled from Mtaa server.
     *
     * @return string
     */
    public static function get_remote_version() {
        global $mn_version;

        $url  = 'http://mnloy.mtaandao.co.ke/changelog/' . Mtaa::$theme_raw_name;

        $options = array(
            'timeout'    => 3,
            'user-agent' => 'Mtaandao/' . $mn_version . '; ' . home_url( '/' )
        );

        $response = mn_remote_get($url, $options);

        if (is_mn_error($response) || 200 != mn_remote_retrieve_response_code($response)) {
            return 'Can\'t contact Mtaa server. Please try again later.';
        }

        $changelog = trim(mn_remote_retrieve_body($response));
        $changelog = maybe_unserialize($changelog);

        $changelog = preg_split("/(\r\n|\n|\r)/", $changelog);

        foreach ($changelog as $line) {
            if (preg_match("/((?:\d+(?!\.\*)\.)+)(\d+)?(\.\*)?/i", $line, $matches)) {
                $version = $matches[0];
                break;
            }
        }

        return $version;
    }

    /**
     * Checks if new theme version is available
     *
     * @return bool true if new version if remote version is higher than local
     */
    public static function has_update() {
        $remoteVersion = self::get_remote_version();
        $localVersion  = self::get_local_version();

        if (preg_match('/[0-9]*\.?[0-9]+/', $remoteVersion)) {
            if (version_compare($localVersion, $remoteVersion, '<')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Adds notifications if there are new theme version available.
     * Runs on time a day
     *
     * @return void
     */
    public static function check_update() {
        $lastChecked = (int) option::get('theme_last_checked');
        $temp_version = get_transient('mtaa_temp_theme_version');

        // force a check if we think theme was updated
        if (!$temp_version) {
            set_transient('mtaa_temp_theme_version', Mtaa::$themeVersion);
        } else {
            if (version_compare($temp_version, Mtaa::$themeVersion, '!=')) {
                $lastChecked = 0;
                set_transient('mtaa_temp_theme_version', Mtaa::$themeVersion);
            }
        }

        if ($lastChecked == 0 || ($lastChecked + 60 * 60 * 24) < time()) {
            if (self::has_update()) {
                option::set('theme_status', 'needs_update');
            } else {
                option::delete('theme_status');
            }
            option::set('theme_last_checked', time());
        }

        if (option::get('theme_status') == 'needs_update') {
            add_action('admin_notices', array(__CLASS__, 'notification'));
        }
    }

    /**
     * admin global notification about new theme version release
     *
     * @return void
     */
    public static function notification() {
        if (isset(Mtaa::$config['tf_url'])) {
            $update_url = Mtaa::$config['tf_url'];
        } else {
            $update_url = 'http://themes.mtaandao.co.ke/' . Mtaa::$theme_raw_name;
        }

        echo '<div class="mtaafw-theme update-nag">';
        echo 'A new version of <a href="' . $update_url . '">' . Mtaa::$themeName . '</a> theme is available. ';
        echo '<u><a href="http://mnloy.mtaandao.co.ke/changelog/' . Mtaa::$theme_raw_name . '?TB_iframe=true" class="thickbox thickbox-preview">Check out what\'s new</a></u> or visit our tutorial on <u><a href="http://www.mtaandao.co.ke/tutorial/how-to-update-a-mtaa-theme/">updating themes</a></u>.';
        echo ' <input type="button" class="close button" value="Hide" /></div>';
    }
}
