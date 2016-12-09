<?php
/**
 * Upgrade API: MN_Upgrader class
 *
 * Requires skin classes and MN_Upgrader subclasses for backward compatibility.
 *
 * @package Mtaandao
 * @subpackage Upgrader
 * @since 2.8.0
 */

/** MN_Upgrader_Skin class */
require_once ABSPATH . 'admin/includes/class-mn-upgrader-skin.php';

/** Plugin_Upgrader_Skin class */
require_once ABSPATH . 'admin/includes/class-plugin-upgrader-skin.php';

/** Theme_Upgrader_Skin class */
require_once ABSPATH . 'admin/includes/class-theme-upgrader-skin.php';

/** Bulk_Upgrader_Skin class */
require_once ABSPATH . 'admin/includes/class-bulk-upgrader-skin.php';

/** Bulk_Plugin_Upgrader_Skin class */
require_once ABSPATH . 'admin/includes/class-bulk-plugin-upgrader-skin.php';

/** Bulk_Theme_Upgrader_Skin class */
require_once ABSPATH . 'admin/includes/class-bulk-theme-upgrader-skin.php';

/** Plugin_Installer_Skin class */
require_once ABSPATH . 'admin/includes/class-plugin-installer-skin.php';

/** Theme_Installer_Skin class */
require_once ABSPATH . 'admin/includes/class-theme-installer-skin.php';

/** Language_Pack_Upgrader_Skin class */
require_once ABSPATH . 'admin/includes/class-language-pack-upgrader-skin.php';

/** Automatic_Upgrader_Skin class */
require_once ABSPATH . 'admin/includes/class-automatic-upgrader-skin.php';

/** MN_Ajax_Upgrader_Skin class */
require_once ABSPATH . 'admin/includes/class-mn-ajax-upgrader-skin.php';

/**
 * Core class used for upgrading/installing a local set of files via
 * the Filesystem Abstraction classes from a Zip file.
 *
 * @since 2.8.0
 */
class MN_Upgrader {

	/**
	 * The error/notification strings used to update the user on the progress.
	 *
	 * @since 2.8.0
	 * @access public
	 * @var array $strings
	 */
	public $strings = array();

	/**
	 * The upgrader skin being used.
	 *
	 * @since 2.8.0
	 * @access public
	 * @var Automatic_Upgrader_Skin|MN_Upgrader_Skin $skin
	 */
	public $skin = null;

	/**
	 * The result of the installation.
	 *
	 * This is set by MN_Upgrader::install_package(), only when the package is installed
	 * successfully. It will then be an array, unless a MN_Error is returned by the
	 * {@see 'upgrader_post_install'} filter. In that case, the MN_Error will be assigned to
	 * it.
	 *
	 * @since 2.8.0
	 * @access public
	 *
	 * @var MN_Error|array $result {
	 *      @type string $source             The full path to the source the files were installed from.
	 *      @type string $source_files       List of all the files in the source directory.
	 *      @type string $destination        The full path to the install destination folder.
	 *      @type string $destination_name   The name of the destination folder, or empty if `$destination`
	 *                                       and `$local_destination` are the same.
	 *      @type string $local_destination  The full local path to the destination folder. This is usually
	 *                                       the same as `$destination`.
	 *      @type string $remote_destination The full remote path to the destination folder
	 *                                       (i.e., from `$mn_filesystem`).
	 *      @type bool   $clear_destination  Whether the destination folder was cleared.
	 * }
	 */
	public $result = array();

	/**
	 * The total number of updates being performed.
	 *
	 * Set by the bulk update methods.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var int $update_count
	 */
	public $update_count = 0;

	/**
	 * The current update if multiple updates are being performed.
	 *
	 * Used by the bulk update methods, and incremented for each update.
	 *
	 * @since 3.0.0
	 * @access public
	 * @var int
	 */
	public $update_current = 0;

	/**
	 * Construct the upgrader with a skin.
	 *
	 * @since 2.8.0
	 * @access public
	 *
	 * @param MN_Upgrader_Skin $skin The upgrader skin to use. Default is a MN_Upgrader_Skin.
	 *                               instance.
	 */
	public function __construct( $skin = null ) {
		if ( null == $skin )
			$this->skin = new MN_Upgrader_Skin();
		else
			$this->skin = $skin;
	}

	/**
	 * Initialize the upgrader.
	 *
	 * This will set the relationship between the skin being used and this upgrader,
	 * and also add the generic strings to `MN_Upgrader::$strings`.
	 *
	 * @since 2.8.0
	 * @access public
	 */
	public function init() {
		$this->skin->set_upgrader($this);
		$this->generic_strings();
	}

	/**
	 * Add the generic strings to MN_Upgrader::$strings.
	 *
	 * @since 2.8.0
	 * @access public
	 */
	public function generic_strings() {
		$this->strings['bad_request'] = __('Invalid data provided.');
		$this->strings['fs_unavailable'] = __('Could not access filesystem.');
		$this->strings['fs_error'] = __('Filesystem error.');
		$this->strings['fs_no_root_dir'] = __('Unable to locate Mtaandao root directory.');
		$this->strings['fs_no_content_dir'] = __('Unable to locate Mtaandao content directory (main).');
		$this->strings['fs_no_plugins_dir'] = __('Unable to locate Mtaandao plugin directory.');
		$this->strings['fs_no_themes_dir'] = __('Unable to locate Mtaandao theme directory.');
		/* translators: %s: directory name */
		$this->strings['fs_no_folder'] = __('Unable to locate needed folder (%s).');

		$this->strings['download_failed'] = __('Download failed.');
		$this->strings['installing_package'] = __('Installing the latest version&#8230;');
		$this->strings['no_files'] = __('The package contains no files.');
		$this->strings['folder_exists'] = __('Destination folder already exists.');
		$this->strings['mkdir_failed'] = __('Could not create directory.');
		$this->strings['incompatible_archive'] = __('The package could not be installed.');
		$this->strings['files_not_writable'] = __( 'The update cannot be installed because we will be unable to copy some files. This is usually due to inconsistent file permissions.' );

		$this->strings['maintenance_start'] = __('Enabling Maintenance mode&#8230;');
		$this->strings['maintenance_end'] = __('Disabling Maintenance mode&#8230;');
	}

	/**
	 * Connect to the filesystem.
	 *
	 * @since 2.8.0
	 * @access public
	 *
	 * @global MN_Filesystem_Base $mn_filesystem Subclass
	 *
	 * @param array $directories                  Optional. A list of directories. If any of these do
	 *                                            not exist, a MN_Error object will be returned.
	 *                                            Default empty array.
	 * @param bool  $allow_relaxed_file_ownership Whether to allow relaxed file ownership.
	 *                                            Default false.
	 * @return bool|MN_Error True if able to connect, false or a MN_Error otherwise.
	 */
	public function fs_connect( $directories = array(), $allow_relaxed_file_ownership = false ) {
		global $mn_filesystem;

		if ( false === ( $credentials = $this->skin->request_filesystem_credentials( false, $directories[0], $allow_relaxed_file_ownership ) ) ) {
			return false;
		}

		if ( ! MN_Filesystem( $credentials, $directories[0], $allow_relaxed_file_ownership ) ) {
			$error = true;
			if ( is_object($mn_filesystem) && $mn_filesystem->errors->get_error_code() )
				$error = $mn_filesystem->errors;
			// Failed to connect, Error and request again
			$this->skin->request_filesystem_credentials( $error, $directories[0], $allow_relaxed_file_ownership );
			return false;
		}

		if ( ! is_object($mn_filesystem) )
			return new MN_Error('fs_unavailable', $this->strings['fs_unavailable'] );

		if ( is_mn_error($mn_filesystem->errors) && $mn_filesystem->errors->get_error_code() )
			return new MN_Error('fs_error', $this->strings['fs_error'], $mn_filesystem->errors);

		foreach ( (array)$directories as $dir ) {
			switch ( $dir ) {
				case ABSPATH:
					if ( ! $mn_filesystem->abspath() )
						return new MN_Error('fs_no_root_dir', $this->strings['fs_no_root_dir']);
					break;
				case MAIN_DIR:
					if ( ! $mn_filesystem->mn_content_dir() )
						return new MN_Error('fs_no_content_dir', $this->strings['fs_no_content_dir']);
					break;
				case PLUGIN_DIR:
					if ( ! $mn_filesystem->mn_plugins_dir() )
						return new MN_Error('fs_no_plugins_dir', $this->strings['fs_no_plugins_dir']);
					break;
				case get_theme_root():
					if ( ! $mn_filesystem->mn_themes_dir() )
						return new MN_Error('fs_no_themes_dir', $this->strings['fs_no_themes_dir']);
					break;
				default:
					if ( ! $mn_filesystem->find_folder($dir) )
						return new MN_Error( 'fs_no_folder', sprintf( $this->strings['fs_no_folder'], esc_html( basename( $dir ) ) ) );
					break;
			}
		}
		return true;
	} //end fs_connect();

	/**
	 * Download a package.
	 *
	 * @since 2.8.0
	 * @access public
	 *
	 * @param string $package The URI of the package. If this is the full path to an
	 *                        existing local file, it will be returned untouched.
	 * @return string|MN_Error The full path to the downloaded package file, or a MN_Error object.
	 */
	public function download_package( $package ) {

		/**
		 * Filters whether to return the package.
		 *
		 * @since 3.7.0
		 * @access public
		 *
		 * @param bool        $reply   Whether to bail without returning the package.
		 *                             Default false.
		 * @param string      $package The package file name.
		 * @param MN_Upgrader $this    The MN_Upgrader instance.
		 */
		$reply = apply_filters( 'upgrader_pre_download', false, $package, $this );
		if ( false !== $reply )
			return $reply;

		if ( ! preg_match('!^(http|https|ftp)://!i', $package) && file_exists($package) ) //Local file or remote?
			return $package; //must be a local file..

		if ( empty($package) )
			return new MN_Error('no_package', $this->strings['no_package']);

		$this->skin->feedback('downloading_package', $package);

		$download_file = download_url($package);

		if ( is_mn_error($download_file) )
			return new MN_Error('download_failed', $this->strings['download_failed'], $download_file->get_error_message());

		return $download_file;
	}

	/**
	 * Unpack a compressed package file.
	 *
	 * @since 2.8.0
	 * @access public
	 *
	 * @global MN_Filesystem_Base $mn_filesystem Subclass
	 *
	 * @param string $package        Full path to the package file.
	 * @param bool   $delete_package Optional. Whether to delete the package file after attempting
	 *                               to unpack it. Default true.
	 * @return string|MN_Error The path to the unpacked contents, or a MN_Error on failure.
	 */
	public function unpack_package( $package, $delete_package = true ) {
		global $mn_filesystem;

		$this->skin->feedback('unpack_package');

		$upgrade_folder = $mn_filesystem->mn_content_dir() . 'upgrade/';

		//Clean up contents of upgrade directory beforehand.
		$upgrade_files = $mn_filesystem->dirlist($upgrade_folder);
		if ( !empty($upgrade_files) ) {
			foreach ( $upgrade_files as $file )
				$mn_filesystem->delete($upgrade_folder . $file['name'], true);
		}

		// We need a working directory - Strip off any .tmp or .zip suffixes
		$working_dir = $upgrade_folder . basename( basename( $package, '.tmp' ), '.zip' );

		// Clean up working directory
		if ( $mn_filesystem->is_dir($working_dir) )
			$mn_filesystem->delete($working_dir, true);

		// Unzip package to working directory
		$result = unzip_file( $package, $working_dir );

		// Once extracted, delete the package if required.
		if ( $delete_package )
			unlink($package);

		if ( is_mn_error($result) ) {
			$mn_filesystem->delete($working_dir, true);
			if ( 'incompatible_archive' == $result->get_error_code() ) {
				return new MN_Error( 'incompatible_archive', $this->strings['incompatible_archive'], $result->get_error_data() );
			}
			return $result;
		}

		return $working_dir;
	}

	/**
	 * Clears the directory where this item is going to be installed into.
	 *
	 * @since 4.3.0
	 * @access public
	 *
	 * @global MN_Filesystem_Base $mn_filesystem Subclass
	 *
	 * @param string $remote_destination The location on the remote filesystem to be cleared
	 * @return bool|MN_Error True upon success, MN_Error on failure.
	 */
	public function clear_destination( $remote_destination ) {
		global $mn_filesystem;

		if ( ! $mn_filesystem->exists( $remote_destination ) ) {
			return true;
		}

		// Check all files are writable before attempting to clear the destination.
		$unwritable_files = array();

		$_files = $mn_filesystem->dirlist( $remote_destination, true, true );

		// Flatten the resulting array, iterate using each as we append to the array during iteration.
		while ( $f = each( $_files ) ) {
			$file = $f['value'];
			$name = $f['key'];

			if ( ! isset( $file['files'] ) ) {
				continue;
			}

			foreach ( $file['files'] as $filename => $details ) {
				$_files[ $name . '/' . $filename ] = $details;
			}
		}

		// Check writability.
		foreach ( $_files as $filename => $file_details ) {
			if ( ! $mn_filesystem->is_writable( $remote_destination . $filename ) ) {

				// Attempt to alter permissions to allow writes and try again.
				$mn_filesystem->chmod( $remote_destination . $filename, ( 'd' == $file_details['type'] ? FS_CHMOD_DIR : FS_CHMOD_FILE ) );
				if ( ! $mn_filesystem->is_writable( $remote_destination . $filename ) ) {
					$unwritable_files[] = $filename;
				}
			}
		}

		if ( ! empty( $unwritable_files ) ) {
			return new MN_Error( 'files_not_writable', $this->strings['files_not_writable'], implode( ', ', $unwritable_files ) );
		}

		if ( ! $mn_filesystem->delete( $remote_destination, true ) ) {
			return new MN_Error( 'remove_old_failed', $this->strings['remove_old_failed'] );
		}

		return true;
	}

	/**
	 * Install a package.
	 *
	 * Copies the contents of a package form a source directory, and installs them in
	 * a destination directory. Optionally removes the source. It can also optionally
	 * clear out the destination folder if it already exists.
	 *
	 * @since 2.8.0
	 * @access public
	 *
	 * @global MN_Filesystem_Base $mn_filesystem Subclass
	 * @global array              $mn_theme_directories
	 *
	 * @param array|string $args {
	 *     Optional. Array or string of arguments for installing a package. Default empty array.
	 *
	 *     @type string $source                      Required path to the package source. Default empty.
	 *     @type string $destination                 Required path to a folder to install the package in.
	 *                                               Default empty.
	 *     @type bool   $clear_destination           Whether to delete any files already in the destination
	 *                                               folder. Default false.
	 *     @type bool   $clear_working               Whether to delete the files form the working directory
	 *                                               after copying to the destination. Default false.
	 *     @type bool   $abort_if_destination_exists Whether to abort the installation if
	 *                                               the destination folder already exists. Default true.
	 *     @type array  $hook_extra                  Extra arguments to pass to the filter hooks called by
	 *                                               MN_Upgrader::install_package(). Default empty array.
	 * }
	 *
	 * @return array|MN_Error The result (also stored in `MN_Upgrader::$result`), or a MN_Error on failure.
	 */
	public function install_package( $args = array() ) {
		global $mn_filesystem, $mn_theme_directories;

		$defaults = array(
			'source' => '', // Please always pass this
			'destination' => '', // and this
			'clear_destination' => false,
			'clear_working' => false,
			'abort_if_destination_exists' => true,
			'hook_extra' => array()
		);

		$args = mn_parse_args($args, $defaults);

		// These were previously extract()'d.
		$source = $args['source'];
		$destination = $args['destination'];
		$clear_destination = $args['clear_destination'];

		@set_time_limit( 300 );

		if ( empty( $source ) || empty( $destination ) ) {
			return new MN_Error( 'bad_request', $this->strings['bad_request'] );
		}
		$this->skin->feedback( 'installing_package' );

		/**
		 * Filters the install response before the installation has started.
		 *
		 * Returning a truthy value, or one that could be evaluated as a MN_Error
		 * will effectively short-circuit the installation, returning that value
		 * instead.
		 *
		 * @since 2.8.0
		 *
		 * @param bool|MN_Error $response   Response.
		 * @param array         $hook_extra Extra arguments passed to hooked filters.
		 */
		$res = apply_filters( 'upgrader_pre_install', true, $args['hook_extra'] );

		if ( is_mn_error( $res ) ) {
			return $res;
		}

		//Retain the Original source and destinations
		$remote_source = $args['source'];
		$local_destination = $destination;

		$source_files = array_keys( $mn_filesystem->dirlist( $remote_source ) );
		$remote_destination = $mn_filesystem->find_folder( $local_destination );

		//Locate which directory to copy to the new folder, This is based on the actual folder holding the files.
		if ( 1 == count( $source_files ) && $mn_filesystem->is_dir( trailingslashit( $args['source'] ) . $source_files[0] . '/' ) ) { //Only one folder? Then we want its contents.
			$source = trailingslashit( $args['source'] ) . trailingslashit( $source_files[0] );
		} elseif ( count( $source_files ) == 0 ) {
			return new MN_Error( 'incompatible_archive_empty', $this->strings['incompatible_archive'], $this->strings['no_files'] ); // There are no files?
		} else { // It's only a single file, the upgrader will use the folder name of this file as the destination folder. Folder name is based on zip filename.
			$source = trailingslashit( $args['source'] );
		}

		/**
		 * Filters the source file location for the upgrade package.
		 *
		 * @since 2.8.0
		 * @since 4.4.0 The $hook_extra parameter became available.
		 *
		 * @param string      $source        File source location.
		 * @param string      $remote_source Remote file source location.
		 * @param MN_Upgrader $this          MN_Upgrader instance.
		 * @param array       $hook_extra    Extra arguments passed to hooked filters.
		 */
		$source = apply_filters( 'upgrader_source_selection', $source, $remote_source, $this, $args['hook_extra'] );

		if ( is_mn_error( $source ) ) {
			return $source;
		}

		// Has the source location changed? If so, we need a new source_files list.
		if ( $source !== $remote_source ) {
			$source_files = array_keys( $mn_filesystem->dirlist( $source ) );
		}

		/*
		 * Protection against deleting files in any important base directories.
		 * Theme_Upgrader & Plugin_Upgrader also trigger this, as they pass the
		 * destination directory (PLUGIN_DIR / main/themes) intending
		 * to copy the directory into the directory, whilst they pass the source
		 * as the actual files to copy.
		 */
		$protected_directories = array( ABSPATH, MAIN_DIR, PLUGIN_DIR, MAIN_DIR . '/themes' );

		if ( is_array( $mn_theme_directories ) ) {
			$protected_directories = array_merge( $protected_directories, $mn_theme_directories );
		}

		if ( in_array( $destination, $protected_directories ) ) {
			$remote_destination = trailingslashit( $remote_destination ) . trailingslashit( basename( $source ) );
			$destination = trailingslashit( $destination ) . trailingslashit( basename( $source ) );
		}

		if ( $clear_destination ) {
			// We're going to clear the destination if there's something there.
			$this->skin->feedback('remove_old');

			$removed = $this->clear_destination( $remote_destination );

			/**
			 * Filters whether the upgrader cleared the destination.
			 *
			 * @since 2.8.0
			 *
			 * @param mixed  $removed            Whether the destination was cleared. true on success, MN_Error on failure
			 * @param string $local_destination  The local package destination.
			 * @param string $remote_destination The remote package destination.
			 * @param array  $hook_extra         Extra arguments passed to hooked filters.
			 */
			$removed = apply_filters( 'upgrader_clear_destination', $removed, $local_destination, $remote_destination, $args['hook_extra'] );

			if ( is_mn_error( $removed ) ) {
				return $removed;
			}
		} elseif ( $args['abort_if_destination_exists'] && $mn_filesystem->exists($remote_destination) ) {
			//If we're not clearing the destination folder and something exists there already, Bail.
			//But first check to see if there are actually any files in the folder.
			$_files = $mn_filesystem->dirlist($remote_destination);
			if ( ! empty($_files) ) {
				$mn_filesystem->delete($remote_source, true); //Clear out the source files.
				return new MN_Error('folder_exists', $this->strings['folder_exists'], $remote_destination );
			}
		}

		//Create destination if needed
		if ( ! $mn_filesystem->exists( $remote_destination ) ) {
			if ( ! $mn_filesystem->mkdir( $remote_destination, FS_CHMOD_DIR ) ) {
				return new MN_Error( 'mkdir_failed_destination', $this->strings['mkdir_failed'], $remote_destination );
			}
		}
		// Copy new version of item into place.
		$result = copy_dir($source, $remote_destination);
		if ( is_mn_error($result) ) {
			if ( $args['clear_working'] ) {
				$mn_filesystem->delete( $remote_source, true );
			}
			return $result;
		}

		//Clear the Working folder?
		if ( $args['clear_working'] ) {
			$mn_filesystem->delete( $remote_source, true );
		}

		$destination_name = basename( str_replace($local_destination, '', $destination) );
		if ( '.' == $destination_name ) {
			$destination_name = '';
		}

		$this->result = compact( 'source', 'source_files', 'destination', 'destination_name', 'local_destination', 'remote_destination', 'clear_destination' );

		/**
		 * Filters the install response after the installation has finished.
		 *
		 * @since 2.8.0
		 *
		 * @param bool  $response   Install response.
		 * @param array $hook_extra Extra arguments passed to hooked filters.
		 * @param array $result     Installation result data.
		 */
		$res = apply_filters( 'upgrader_post_install', true, $args['hook_extra'], $this->result );

		if ( is_mn_error($res) ) {
			$this->result = $res;
			return $res;
		}

		//Bombard the calling function will all the info which we've just used.
		return $this->result;
	}

	/**
	 * Run an upgrade/install.
	 *
	 * Attempts to download the package (if it is not a local file), unpack it, and
	 * install it in the destination folder.
	 *
	 * @since 2.8.0
	 * @access public
	 *
	 * @param array $options {
	 *     Array or string of arguments for upgrading/installing a package.
	 *
	 *     @type string $package                     The full path or URI of the package to install.
	 *                                               Default empty.
	 *     @type string $destination                 The full path to the destination folder.
	 *                                               Default empty.
	 *     @type bool   $clear_destination           Whether to delete any files already in the
	 *                                               destination folder. Default false.
	 *     @type bool   $clear_working               Whether to delete the files form the working
	 *                                               directory after copying to the destination.
	 *                                               Default false.
	 *     @type bool   $abort_if_destination_exists Whether to abort the installation if the destination
	 *                                               folder already exists. When true, `$clear_destination`
	 *                                               should be false. Default true.
	 *     @type bool   $is_multi                    Whether this run is one of multiple upgrade/install
	 *                                               actions being performed in bulk. When true, the skin
	 *                                               MN_Upgrader::header() and MN_Upgrader::footer()
	 *                                               aren't called. Default false.
	 *     @type array  $hook_extra                  Extra arguments to pass to the filter hooks called by
	 *                                               MN_Upgrader::run().
	 * }
	 * @return array|false|MN_error The result from self::install_package() on success, otherwise a MN_Error,
	 *                              or false if unable to connect to the filesystem.
	 */
	public function run( $options ) {

		$defaults = array(
			'package' => '', // Please always pass this.
			'destination' => '', // And this
			'clear_destination' => false,
			'abort_if_destination_exists' => true, // Abort if the Destination directory exists, Pass clear_destination as false please
			'clear_working' => true,
			'is_multi' => false,
			'hook_extra' => array() // Pass any extra $hook_extra args here, this will be passed to any hooked filters.
		);

		$options = mn_parse_args( $options, $defaults );

		/**
		 * Filters the package options before running an update.
		 *
		 * See also {@see 'upgrader_process_complete'}.
		 *
		 * @since 4.3.0
		 *
		 * @param array $options {
		 *     Options used by the upgrader.
		 *
		 *     @type string $package                     Package for update.
		 *     @type string $destination                 Update location.
		 *     @type bool   $clear_destination           Clear the destination resource.
		 *     @type bool   $clear_working               Clear the working resource.
		 *     @type bool   $abort_if_destination_exists Abort if the Destination directory exists.
		 *     @type bool   $is_multi                    Whether the upgrader is running multiple times.
		 *     @type array  $hook_extra {
		 *         Extra hook arguments.
		 *
		 *         @type string $action               Type of action. Default 'update'.
		 *         @type string $type                 Type of update process. Accepts 'plugin', 'theme', or 'core'.
		 *         @type bool   $bulk                 Whether the update process is a bulk update. Default true.
		 *         @type string $plugin               The base plugin path from the plugins directory.
		 *         @type string $theme                The stylesheet or template name of the theme.
		 *         @type string $language_update_type The language pack update type. Accepts 'plugin', 'theme',
		 *                                            or 'core'.
		 *         @type object $language_update      The language pack update offer.
		 *     }
		 * }
		 */
		$options = apply_filters( 'upgrader_package_options', $options );

		if ( ! $options['is_multi'] ) { // call $this->header separately if running multiple times
			$this->skin->header();
		}

		// Connect to the Filesystem first.
		$res = $this->fs_connect( array( MAIN_DIR, $options['destination'] ) );
		// Mainly for non-connected filesystem.
		if ( ! $res ) {
			if ( ! $options['is_multi'] ) {
				$this->skin->footer();
			}
			return false;
		}

		$this->skin->before();

		if ( is_mn_error($res) ) {
			$this->skin->error($res);
			$this->skin->after();
			if ( ! $options['is_multi'] ) {
				$this->skin->footer();
			}
			return $res;
		}

		/*
		 * Download the package (Note, This just returns the filename
		 * of the file if the package is a local file)
		 */
		$download = $this->download_package( $options['package'] );
		if ( is_mn_error($download) ) {
			$this->skin->error($download);
			$this->skin->after();
			if ( ! $options['is_multi'] ) {
				$this->skin->footer();
			}
			return $download;
		}

		$delete_package = ( $download != $options['package'] ); // Do not delete a "local" file

		// Unzips the file into a temporary directory.
		$working_dir = $this->unpack_package( $download, $delete_package );
		if ( is_mn_error($working_dir) ) {
			$this->skin->error($working_dir);
			$this->skin->after();
			if ( ! $options['is_multi'] ) {
				$this->skin->footer();
			}
			return $working_dir;
		}

		// With the given options, this installs it to the destination directory.
		$result = $this->install_package( array(
			'source' => $working_dir,
			'destination' => $options['destination'],
			'clear_destination' => $options['clear_destination'],
			'abort_if_destination_exists' => $options['abort_if_destination_exists'],
			'clear_working' => $options['clear_working'],
			'hook_extra' => $options['hook_extra']
		) );

		$this->skin->set_result($result);
		if ( is_mn_error($result) ) {
			$this->skin->error($result);
			$this->skin->feedback('process_failed');
		} else {
			// Install succeeded.
			$this->skin->feedback('process_success');
		}

		$this->skin->after();

		if ( ! $options['is_multi'] ) {

			/**
			 * Fires when the upgrader process is complete.
			 *
			 * See also {@see 'upgrader_package_options'}.
			 *
			 * @since 3.6.0
			 * @since 3.7.0 Added to MN_Upgrader::run().
			 * @since 4.6.0 `$translations` was added as a possible argument to `$hook_extra`.
			 *
			 * @param MN_Upgrader $this MN_Upgrader instance. In other contexts, $this, might be a
			 *                          Theme_Upgrader, Plugin_Upgrader, Core_Upgrade, or Language_Pack_Upgrader instance.
			 * @param array       $hook_extra {
			 *     Array of bulk item update data.
			 *
			 *     @type string $action       Type of action. Default 'update'.
			 *     @type string $type         Type of update process. Accepts 'plugin', 'theme', 'translation', or 'core'.
			 *     @type bool   $bulk         Whether the update process is a bulk update. Default true.
			 *     @type array  $plugins      Array of the basename paths of the plugins' main files.
			 *     @type array  $themes       The theme slugs.
			 *     @type array  $translations {
			 *         Array of translations update data.
			 *
			 *         @type string $language The locale the translation is for.
			 *         @type string $type     Type of translation. Accepts 'plugin', 'theme', or 'core'.
			 *         @type string $slug     Text domain the translation is for. The slug of a theme/plugin or
			 *                                'default' for core translations.
			 *         @type string $version  The version of a theme, plugin, or core.
			 *     }
			 * }
			 */
			do_action( 'upgrader_process_complete', $this, $options['hook_extra'] );

			$this->skin->footer();
		}

		return $result;
	}

	/**
	 * Toggle maintenance mode for the site.
	 *
	 * Creates/deletes the maintenance file to enable/disable maintenance mode.
	 *
	 * @since 2.8.0
	 * @access public
	 *
	 * @global MN_Filesystem_Base $mn_filesystem Subclass
	 *
	 * @param bool $enable True to enable maintenance mode, false to disable.
	 */
	public function maintenance_mode( $enable = false ) {
		global $mn_filesystem;
		$file = $mn_filesystem->abspath() . '.maintenance';
		if ( $enable ) {
			$this->skin->feedback('maintenance_start');
			// Create maintenance file to signal that we are upgrading
			$maintenance_string = '<?php $upgrading = ' . time() . '; ?>';
			$mn_filesystem->delete($file);
			$mn_filesystem->put_contents($file, $maintenance_string, FS_CHMOD_FILE);
		} elseif ( ! $enable && $mn_filesystem->exists( $file ) ) {
			$this->skin->feedback('maintenance_end');
			$mn_filesystem->delete($file);
		}
	}

	/**
 	 * Creates a lock using Mtaandao options.
 	 *
 	 * @since 4.5.0
 	 * @access public
 	 * @static
 	 *
 	 * @param string $lock_name       The name of this unique lock.
 	 * @param int    $release_timeout Optional. The duration in seconds to respect an existing lock.
	 *                                Default: 1 hour.
 	 * @return bool False if a lock couldn't be created or if the lock is still valid. True otherwise.
 	 */
	public static function create_lock( $lock_name, $release_timeout = null ) {
		global $mndb;
		if ( ! $release_timeout ) {
			$release_timeout = HOUR_IN_SECONDS;
		}
		$lock_option = $lock_name . '.lock';

		// Try to lock.
		$lock_result = $mndb->query( $mndb->prepare( "INSERT IGNORE INTO `$mndb->options` ( `option_name`, `option_value`, `autoload` ) VALUES (%s, %s, 'no') /* LOCK */", $lock_option, time() ) );

		if ( ! $lock_result ) {
			$lock_result = get_option( $lock_option );

			// If a lock couldn't be created, and there isn't a lock, bail.
			if ( ! $lock_result ) {
				return false;
			}

			// Check to see if the lock is still valid. If it is, bail.
			if ( $lock_result > ( time() - $release_timeout ) ) {
				return false;
			}

			// There must exist an expired lock, clear it and re-gain it.
			MN_Upgrader::release_lock( $lock_name );

			return MN_Upgrader::create_lock( $lock_name, $release_timeout );
		}

		// Update the lock, as by this point we've definitely got a lock, just need to fire the actions.
		update_option( $lock_option, time() );

		return true;
	}

	/**
 	 * Releases an upgrader lock.
 	 *
 	 * @since 4.5.0
 	 * @access public
 	 * @static
	 *
	 * @see MN_Upgrader::create_lock()
 	 *
 	 * @param string $lock_name The name of this unique lock.
	 * @return bool True if the lock was successfully released. False on failure.
 	 */
	public static function release_lock( $lock_name ) {
		return delete_option( $lock_name . '.lock' );
	}

}

/** Plugin_Upgrader class */
require_once ABSPATH . 'admin/includes/class-plugin-upgrader.php';

/** Theme_Upgrader class */
require_once ABSPATH . 'admin/includes/class-theme-upgrader.php';

/** Language_Pack_Upgrader class */
require_once ABSPATH . 'admin/includes/class-language-pack-upgrader.php';

/** Core_Upgrader class */
require_once ABSPATH . 'admin/includes/class-core-upgrader.php';

/** File_Upload_Upgrader class */
require_once ABSPATH . 'admin/includes/class-file-upload-upgrader.php';

/** MN_Automatic_Updater class */
require_once ABSPATH . 'admin/includes/class-mn-automatic-updater.php';
