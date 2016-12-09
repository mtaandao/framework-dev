<?php
/**
 * Upgrade API: Core_Upgrader class
 *
 * @package Mtaandao
 * @subpackage Upgrader
 * @since 4.6.0
 */

/**
 * Core class used for updating core.
 *
 * It allows for Mtaandao to upgrade itself in combination with
 * the admin/includes/update-core.php file.
 *
 * @since 2.8.0
 * @since 4.6.0 Moved to its own file from admin/includes/class-mn-upgrader.php.
 *
 * @see MN_Upgrader
 */
class Core_Upgrader extends MN_Upgrader {

	/**
	 * Initialize the upgrade strings.
	 *
	 * @since 2.8.0
	 * @access public
	 */
	public function upgrade_strings() {
		$this->strings['up_to_date'] = __('Mtaandao is at the latest version.');
		$this->strings['locked'] = __('Another update is currently in progress.');
		$this->strings['no_package'] = __('Update package not available.');
		$this->strings['downloading_package'] = __('Downloading update from <span class="code">%s</span>&#8230;');
		$this->strings['unpack_package'] = __('Unpacking the update&#8230;');
		$this->strings['copy_failed'] = __('Could not copy files.');
		$this->strings['copy_failed_space'] = __('Could not copy files. You may have run out of disk space.' );
		$this->strings['start_rollback'] = __( 'Attempting to roll back to previous version.' );
		$this->strings['rollback_was_required'] = __( 'Due to an error during updating, Mtaandao has rolled back to your previous version.' );
	}

	/**
	 * Upgrade Mtaandao core.
	 *
	 * @since 2.8.0
	 * @access public
	 *
	 * @global MN_Filesystem_Base $mn_filesystem Subclass
	 * @global callable           $_mn_filesystem_direct_method
	 *
	 * @param object $current Response object for whether Mtaandao is current.
	 * @param array  $args {
	 *        Optional. Arguments for upgrading Mtaandao core. Default empty array.
	 *
	 *        @type bool $pre_check_md5    Whether to check the file checksums before
	 *                                     attempting the upgrade. Default true.
	 *        @type bool $attempt_rollback Whether to attempt to rollback the chances if
	 *                                     there is a problem. Default false.
	 *        @type bool $do_rollback      Whether to perform this "upgrade" as a rollback.
	 *                                     Default false.
	 * }
	 * @return null|false|MN_Error False or MN_Error on failure, null on success.
	 */
	public function upgrade( $current, $args = array() ) {
		global $mn_filesystem;

		include( ABSPATH . RES . '/version.php' ); // $mn_version;

		$start_time = time();

		$defaults = array(
			'pre_check_md5'    => true,
			'attempt_rollback' => false,
			'do_rollback'      => false,
			'allow_relaxed_file_ownership' => false,
		);
		$parsed_args = mn_parse_args( $args, $defaults );

		$this->init();
		$this->upgrade_strings();

		// Is an update available?
		if ( !isset( $current->response ) || $current->response == 'latest' )
			return new MN_Error('up_to_date', $this->strings['up_to_date']);

		$res = $this->fs_connect( array( ABSPATH, MAIN_DIR ), $parsed_args['allow_relaxed_file_ownership'] );
		if ( ! $res || is_mn_error( $res ) ) {
			return $res;
		}

		$mn_dir = trailingslashit($mn_filesystem->abspath());

		$partial = true;
		if ( $parsed_args['do_rollback'] )
			$partial = false;
		elseif ( $parsed_args['pre_check_md5'] && ! $this->check_files() )
			$partial = false;

		/*
		 * If partial update is returned from the API, use that, unless we're doing
		 * a reinstall. If we cross the new_bundled version number, then use
		 * the new_bundled zip. Don't though if the constant is set to skip bundled items.
		 * If the API returns a no_content zip, go with it. Finally, default to the full zip.
		 */
		if ( $parsed_args['do_rollback'] && $current->packages->rollback )
			$to_download = 'rollback';
		elseif ( $current->packages->partial && 'reinstall' != $current->response && $mn_version == $current->partial_version && $partial )
			$to_download = 'partial';
		elseif ( $current->packages->new_bundled && version_compare( $mn_version, $current->new_bundled, '<' )
			&& ( ! defined( 'CORE_UPGRADE_SKIP_NEW_BUNDLED' ) || ! CORE_UPGRADE_SKIP_NEW_BUNDLED ) )
			$to_download = 'new_bundled';
		elseif ( $current->packages->no_content )
			$to_download = 'no_content';
		else
			$to_download = 'full';

		// Lock to prevent multiple Core Updates occurring
		$lock = MN_Upgrader::create_lock( 'core_updater', 15 * MINUTE_IN_SECONDS );
		if ( ! $lock ) {
			return new MN_Error( 'locked', $this->strings['locked'] );
		}

		$download = $this->download_package( $current->packages->$to_download );
		if ( is_mn_error( $download ) ) {
			MN_Upgrader::release_lock( 'core_updater' );
			return $download;
		}

		$working_dir = $this->unpack_package( $download );
		if ( is_mn_error( $working_dir ) ) {
			MN_Upgrader::release_lock( 'core_updater' );
			return $working_dir;
		}

		// Copy update-core.php from the new version into place.
		if ( !$mn_filesystem->copy($working_dir . '/mtaandao/admin/includes/update-core.php', $mn_dir . 'admin/includes/update-core.php', true) ) {
			$mn_filesystem->delete($working_dir, true);
			MN_Upgrader::release_lock( 'core_updater' );
			return new MN_Error( 'copy_failed_for_update_core_file', __( 'The update cannot be installed because we will be unable to copy some files. This is usually due to inconsistent file permissions.' ), 'admin/includes/update-core.php' );
		}
		$mn_filesystem->chmod($mn_dir . 'admin/includes/update-core.php', FS_CHMOD_FILE);

		require_once( ABSPATH . 'admin/includes/update-core.php' );

		if ( ! function_exists( 'update_core' ) ) {
			MN_Upgrader::release_lock( 'core_updater' );
			return new MN_Error( 'copy_failed_space', $this->strings['copy_failed_space'] );
		}

		$result = update_core( $working_dir, $mn_dir );

		// In the event of an issue, we may be able to roll back.
		if ( $parsed_args['attempt_rollback'] && $current->packages->rollback && ! $parsed_args['do_rollback'] ) {
			$try_rollback = false;
			if ( is_mn_error( $result ) ) {
				$error_code = $result->get_error_code();
				/*
				 * Not all errors are equal. These codes are critical: copy_failed__copy_dir,
				 * mkdir_failed__copy_dir, copy_failed__copy_dir_retry, and disk_full.
				 * do_rollback allows for update_core() to trigger a rollback if needed.
				 */
				if ( false !== strpos( $error_code, 'do_rollback' ) )
					$try_rollback = true;
				elseif ( false !== strpos( $error_code, '__copy_dir' ) )
					$try_rollback = true;
				elseif ( 'disk_full' === $error_code )
					$try_rollback = true;
			}

			if ( $try_rollback ) {
				/** This filter is documented in admin/includes/update-core.php */
				apply_filters( 'update_feedback', $result );

				/** This filter is documented in admin/includes/update-core.php */
				apply_filters( 'update_feedback', $this->strings['start_rollback'] );

				$rollback_result = $this->upgrade( $current, array_merge( $parsed_args, array( 'do_rollback' => true ) ) );

				$original_result = $result;
				$result = new MN_Error( 'rollback_was_required', $this->strings['rollback_was_required'], (object) array( 'update' => $original_result, 'rollback' => $rollback_result ) );
			}
		}

		/** This action is documented in admin/includes/class-mn-upgrader.php */
		do_action( 'upgrader_process_complete', $this, array( 'action' => 'update', 'type' => 'core' ) );

		// Clear the current updates
		delete_site_transient( 'update_core' );

		if ( ! $parsed_args['do_rollback'] ) {
			$stats = array(
				'update_type'      => $current->response,
				'success'          => true,
				'fs_method'        => $mn_filesystem->method,
				'fs_method_forced' => defined( 'FS_METHOD' ) || has_filter( 'filesystem_method' ),
				'fs_method_direct' => !empty( $GLOBALS['_mn_filesystem_direct_method'] ) ? $GLOBALS['_mn_filesystem_direct_method'] : '',
				'time_taken'       => time() - $start_time,
				'reported'         => $mn_version,
				'attempted'        => $current->version,
			);

			if ( is_mn_error( $result ) ) {
				$stats['success'] = false;
				// Did a rollback occur?
				if ( ! empty( $try_rollback ) ) {
					$stats['error_code'] = $original_result->get_error_code();
					$stats['error_data'] = $original_result->get_error_data();
					// Was the rollback successful? If not, collect its error too.
					$stats['rollback'] = ! is_mn_error( $rollback_result );
					if ( is_mn_error( $rollback_result ) ) {
						$stats['rollback_code'] = $rollback_result->get_error_code();
						$stats['rollback_data'] = $rollback_result->get_error_data();
					}
				} else {
					$stats['error_code'] = $result->get_error_code();
					$stats['error_data'] = $result->get_error_data();
				}
			}

			mn_version_check( $stats );
		}

		MN_Upgrader::release_lock( 'core_updater' );

		return $result;
	}

	/**
	 * Determines if this Mtaandao Core version should update to an offered version or not.
	 *
	 * @since 3.7.0
	 * @access public
	 *
	 * @static
	 *
	 * @param string $offered_ver The offered version, of the format x.y.z.
	 * @return bool True if we should update to the offered version, otherwise false.
	 */
	public static function should_update_to_version( $offered_ver ) {
		include( ABSPATH . RES . '/version.php' ); // $mn_version; // x.y.z

		$current_branch = implode( '.', array_slice( preg_split( '/[.-]/', $mn_version  ), 0, 2 ) ); // x.y
		$new_branch     = implode( '.', array_slice( preg_split( '/[.-]/', $offered_ver ), 0, 2 ) ); // x.y
		$current_is_development_version = (bool) strpos( $mn_version, '-' );

		// Defaults:
		$upgrade_dev   = true;
		$upgrade_minor = true;
		$upgrade_major = false;

		// MN_AUTO_UPDATE_CORE = true (all), 'minor', false.
		if ( defined( 'MN_AUTO_UPDATE_CORE' ) ) {
			if ( false === MN_AUTO_UPDATE_CORE ) {
				// Defaults to turned off, unless a filter allows it
				$upgrade_dev = $upgrade_minor = $upgrade_major = false;
			} elseif ( true === MN_AUTO_UPDATE_CORE ) {
				// ALL updates for core
				$upgrade_dev = $upgrade_minor = $upgrade_major = true;
			} elseif ( 'minor' === MN_AUTO_UPDATE_CORE ) {
				// Only minor updates for core
				$upgrade_dev = $upgrade_major = false;
				$upgrade_minor = true;
			}
		}

		// 1: If we're already on that version, not much point in updating?
		if ( $offered_ver == $mn_version )
			return false;

		// 2: If we're running a newer version, that's a nope
		if ( version_compare( $mn_version, $offered_ver, '>' ) )
			return false;

		$failure_data = get_site_option( 'auto_core_update_failed' );
		if ( $failure_data ) {
			// If this was a critical update failure, cannot update.
			if ( ! empty( $failure_data['critical'] ) )
				return false;

			// Don't claim we can update on update-core.php if we have a non-critical failure logged.
			if ( $mn_version == $failure_data['current'] && false !== strpos( $offered_ver, '.1.next.minor' ) )
				return false;

			// Cannot update if we're retrying the same A to B update that caused a non-critical failure.
			// Some non-critical failures do allow retries, like download_failed.
			// 3.7.1 => 3.7.2 resulted in files_not_writable, if we are still on 3.7.1 and still trying to update to 3.7.2.
			if ( empty( $failure_data['retry'] ) && $mn_version == $failure_data['current'] && $offered_ver == $failure_data['attempted'] )
				return false;
		}

		// 3: 3.7-alpha-25000 -> 3.7-alpha-25678 -> 3.7-beta1 -> 3.7-beta2
		if ( $current_is_development_version ) {

			/**
			 * Filters whether to enable automatic core updates for development versions.
			 *
			 * @since 3.7.0
			 *
			 * @param bool $upgrade_dev Whether to enable automatic updates for
			 *                          development versions.
			 */
			if ( ! apply_filters( 'allow_dev_auto_core_updates', $upgrade_dev ) )
				return false;
			// Else fall through to minor + major branches below.
		}

		// 4: Minor In-branch updates (3.7.0 -> 3.7.1 -> 3.7.2 -> 3.7.4)
		if ( $current_branch == $new_branch ) {

			/**
			 * Filters whether to enable minor automatic core updates.
			 *
			 * @since 3.7.0
			 *
			 * @param bool $upgrade_minor Whether to enable minor automatic core updates.
			 */
			return apply_filters( 'allow_minor_auto_core_updates', $upgrade_minor );
		}

		// 5: Major version updates (3.7.0 -> 3.8.0 -> 3.9.1)
		if ( version_compare( $new_branch, $current_branch, '>' ) ) {

			/**
			 * Filters whether to enable major automatic core updates.
			 *
			 * @since 3.7.0
			 *
			 * @param bool $upgrade_major Whether to enable major automatic core updates.
			 */
			return apply_filters( 'allow_major_auto_core_updates', $upgrade_major );
		}

		// If we're not sure, we don't want it
		return false;
	}

	/**
	 * Compare the disk file checksums against the expected checksums.
	 *
	 * @since 3.7.0
	 * @access public
	 *
	 * @global string $mn_version
	 * @global string $mn_local_package
	 *
	 * @return bool True if the checksums match, otherwise false.
	 */
	public function check_files() {
		global $mn_version, $mn_local_package;

		$checksums = get_core_checksums( $mn_version, isset( $mn_local_package ) ? $mn_local_package : 'en_US' );

		if ( ! is_array( $checksums ) )
			return false;

		foreach ( $checksums as $file => $checksum ) {
			// Skip files which get updated
			if ( 'main' == substr( $file, 0, 10 ) )
				continue;
			if ( ! file_exists( ABSPATH . $file ) || md5_file( ABSPATH . $file ) !== $checksum )
				return false;
		}

		return true;
	}
}
