<?php
/**
 * Mtaa_Framework_Updater Class
 *
 * @package Mtaa
 * @subpackage Framework_Updater
 */

class Mtaa_Framework_Updater {
    /**
     * Adds notifications if there is any new version available.
     * Runs one time a day.
     */
    public static function check_update() {
        /* force recheck if we spoted manualy modified version */
        if (get_transient('framework_version') !== Mtaa::$mtaaVersion) {
            $lastChecked = 0;
        } else {
            $lastChecked = (int) option::get('framework_last_checked');
        }

        if ($lastChecked == 0 || ($lastChecked + 60 * 60 * 24) < time()) {
            if (self::has_update()) {
                option::set('framework_status', 'needs_update');
            } else {
                option::delete('framework_status');
            }
            option::set('framework_last_checked', time());
            set_transient('framework_version', Mtaa::$mtaaVersion);
        }

        if (option::get('framework_status') == 'needs_update') {
            add_action('admin_notices', array(__CLASS__, 'notification'));
        }

    }

    /**
     * Checks if a new Mtaa Framework version is available.
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
     * Returns local framework version.
     *
     * @return string
     */
    public static function get_local_version() {
        return Mtaa::$mtaaVersion;
    }

    /**
     * Returns latest available Mtaa Framework version.
     *
     * @return string
     */
    public static function get_remote_version() {
        global $mn_version;

        $url  = 'http://framework.mtaandao.co.ke/get-version/';

        $options = array(
            'timeout' => 3,
            'user-agent' => 'Mtaandao/' . $mn_version . '; ' . home_url( '/' )
        );

        $response = mn_remote_get($url, $options);

        if (is_mn_error($response) || 200 != mn_remote_retrieve_response_code($response)) {
            return 'Can\'t contact Mtaa server. Please try again later.';
        }

        $version = trim(mn_remote_retrieve_body($response));
        $version = maybe_unserialize($version);

        return $version;
    }

    /**
     * Returns Framework changelog
     *
     * @return string changelog
     */
    public static function get_changelog() {
        global $mn_version;

        $url = 'http://framework.mtaandao.co.ke/changelog/';

        $options = array(
            'timeout'    => 3,
            'user-agent' => 'Mtaandao/' . $mn_version . '; ' . home_url( '/' )
        );

        $response = mn_remote_get($url, $options);

        if (is_mn_error($response) || 200 != mn_remote_retrieve_response_code($response)) {
            return 'Can\'t contact Mtaa server. Please try again later.';
        }

        $response = trim(mn_remote_retrieve_body($response));
        $response = maybe_unserialize($response);

        return $response;
    }

    public static function notification() {
        $msg = sprintf('You are using an outdated version of Mtaa Framework, please <a href="%s">update now</a>. <input type="button" class="close button" value="Hide" />', admin_url('admin.php?page=mtaa_update'));

        echo '<div class="update-nag mtaafw-core">' . $msg . '</div>';
    }

    /**
     * Checks if we are going to make an update and updates current framework to latest version
     */
    public static function update_init() {
        global $r;

        if (!isset($_GET['page'])) return;

        $requestedPage = strtolower(strip_tags(trim($_REQUEST['page'])));

        if ($requestedPage != 'mtaa_update') return;

        $fsmethod = get_filesystem_method();
        $fs = MN_Filesystem();

        if ($fs == false) {
            function framework_update_filesystem_warning() {
                $method = get_filesystem_method();
                echo "<p>Failed: Filesystem preventing downloads. ($method)</p>";
            }
            add_action( 'admin_notices', 'framework_update_filesystem_warning' );

            return;
        }

        if (isset($_POST['mtaa-update-do'])) {
            $action = strtolower(trim(strip_tags($_POST['mtaa-update-do'])));

            if ($action == 'update') {
                $fwUrl = 'http://framework.mtaandao.co.ke/mtaa-framework.zip';
                $fwFile = download_url($fwUrl);

                if (is_mn_error($fwUrl)) {
                    $error = $fwFile->get_error_code();

                    if ($error == 'http_no_url') {
                        $r = "<p>Failed: Invalid URL Provided</p>";
                    } else {
                        $r = "<p>Failed: Upload - $error</p>";
                    }

                    function framework_update_warning() {
                        global $r;
                        echo "<p>$r</p>";
                    }
                    add_action( 'admin_notices', 'framework_update_warning' );

                    return;
                }
            }

            global $mn_filesystem;
            $to = Mtaa::get_mtaa_root();
            $dounzip = unzip_file($fwFile, $to);

            unlink($fwFile);

            if (is_mn_error($dounzip)) {

                $error = $dounzip->get_error_code();
                $data = $dounzip->get_error_data($error);

                if($error == 'incompatible_archive') {
                    //The source file was not found or is invalid
                    function framework_update_no_archive_warning() {
                        echo "<p>Failed: Incompatible archive</p>";
                    }
                    add_action( 'admin_notices', 'framework_update_no_archive_warning' );
                }
                if($error == 'empty_archive') {
                    function framework_update_empty_archive_warning() {
                        echo "<p>Failed: Empty Archive</p>";
                    }
                    add_action( 'admin_notices', 'framework_update_empty_archive_warning' );
                }
                if($error == 'mkdir_failed') {
                    function framework_update_mkdir_warning() {
                        echo "<p>Failed: mkdir Failure</p>";
                    }
                    add_action( 'admin_notices', 'framework_update_mkdir_warning' );
                }
                if($error == 'copy_failed') {
                    function framework_update_copy_fail_warning() {
                        echo "<p>Failed: Copy Failed</p>";
                    }
                    add_action( 'admin_notices', 'update_copy_fail_warning' );
                }

                return;

            }

            function framework_updated_success() {
                echo '<div class="updated fade"><p>New framework successfully downloaded, extracted and updated.</p></div>';
            }
            add_action('admin_notices', 'framework_updated_success');

            remove_action('admin_notices', array('Mtaa', 'notification'));

            option::delete('framework_status');
            option::set('framework_last_checked', time());

        }
    }
}
