/**
 * Functions for ajaxified updates, deletions and installs inside the Mtaandao admin.
 *
 * @version 4.2.0
 *
 * @package Mtaandao
 * @subpackage Administration
 */

/* global pagenow */

/**
 * @param {jQuery}  $                                   jQuery object.
 * @param {object}  mn                                  MN object.
 * @param {object}  settings                            MN Updates settings.
 * @param {string}  settings.ajax_nonce                 AJAX nonce.
 * @param {object}  settings.l10n                       Translation strings.
 * @param {object=} settings.plugins                    Base names of plugins in their different states.
 * @param {Array}   settings.plugins.all                Base names of all plugins.
 * @param {Array}   settings.plugins.active             Base names of active plugins.
 * @param {Array}   settings.plugins.inactive           Base names of inactive plugins.
 * @param {Array}   settings.plugins.upgrade            Base names of plugins with updates available.
 * @param {Array}   settings.plugins.recently_activated Base names of recently activated plugins.
 * @param {object=} settings.themes                     Plugin/theme status information or null.
 * @param {number}  settings.themes.all                 Amount of all themes.
 * @param {number}  settings.themes.upgrade             Amount of themes with updates available.
 * @param {number}  settings.themes.disabled            Amount of disabled themes.
 * @param {object=} settings.totals                     Combined information for available update counts.
 * @param {number}  settings.totals.count               Holds the amount of available updates.
 */
(function( $, mn, settings ) {
	var $document = $( document );

	mn = mn || {};

	/**
	 * The MN Updates object.
	 *
	 * @since 4.2.0
	 *
	 * @type {object}
	 */
	mn.updates = {};

	/**
	 * User nonce for ajax calls.
	 *
	 * @since 4.2.0
	 *
	 * @type {string}
	 */
	mn.updates.ajaxNonce = settings.ajax_nonce;

	/**
	 * Localized strings.
	 *
	 * @since 4.2.0
	 *
	 * @type {object}
	 */
	mn.updates.l10n = settings.l10n;

	/**
	 * Current search term.
	 *
	 * @since 4.6.0
	 *
	 * @type {string}
	 */
	mn.updates.searchTerm = '';

	/**
	 * Whether filesystem credentials need to be requested from the user.
	 *
	 * @since 4.2.0
	 *
	 * @type {bool}
	 */
	mn.updates.shouldRequestFilesystemCredentials = false;

	/**
	 * Filesystem credentials to be packaged along with the request.
	 *
	 * @since 4.2.0
	 * @since 4.6.0 Added `available` property to indicate whether credentials have been provided.
	 *
	 * @type {object} filesystemCredentials                    Holds filesystem credentials.
	 * @type {object} filesystemCredentials.ftp                Holds FTP credentials.
	 * @type {string} filesystemCredentials.ftp.host           FTP host. Default empty string.
	 * @type {string} filesystemCredentials.ftp.username       FTP user name. Default empty string.
	 * @type {string} filesystemCredentials.ftp.password       FTP password. Default empty string.
	 * @type {string} filesystemCredentials.ftp.connectionType Type of FTP connection. 'ssh', 'ftp', or 'ftps'.
	 *                                                         Default empty string.
	 * @type {object} filesystemCredentials.ssh                Holds SSH credentials.
	 * @type {string} filesystemCredentials.ssh.publicKey      The public key. Default empty string.
	 * @type {string} filesystemCredentials.ssh.privateKey     The private key. Default empty string.
	 * @type {bool}   filesystemCredentials.available          Whether filesystem credentials have been provided.
	 *                                                         Default 'false'.
	 */
	mn.updates.filesystemCredentials = {
		ftp:       {
			host:           '',
			username:       '',
			password:       '',
			connectionType: ''
		},
		ssh:       {
			publicKey:  '',
			privateKey: ''
		},
		available: false
	};

	/**
	 * Whether we're waiting for an Ajax request to complete.
	 *
	 * @since 4.2.0
	 * @since 4.6.0 More accurately named `ajaxLocked`.
	 *
	 * @type {bool}
	 */
	mn.updates.ajaxLocked = false;

	/**
	 * Admin notice template.
	 *
	 * @since 4.6.0
	 *
	 * @type {function} A function that lazily-compiles the template requested.
	 */
	mn.updates.adminNotice = mn.template( 'mn-updates-admin-notice' );

	/**
	 * Update queue.
	 *
	 * If the user tries to update a plugin while an update is
	 * already happening, it can be placed in this queue to perform later.
	 *
	 * @since 4.2.0
	 * @since 4.6.0 More accurately named `queue`.
	 *
	 * @type {Array.object}
	 */
	mn.updates.queue = [];

	/**
	 * Holds a jQuery reference to return focus to when exiting the request credentials modal.
	 *
	 * @since 4.2.0
	 *
	 * @type {jQuery}
	 */
	mn.updates.$elToReturnFocusToFromCredentialsModal = undefined;

	/**
	 * Adds or updates an admin notice.
	 *
	 * @since 4.6.0
	 *
	 * @param {object}  data
	 * @param {*=}      data.selector      Optional. Selector of an element to be replaced with the admin notice.
	 * @param {string=} data.id            Optional. Unique id that will be used as the notice's id attribute.
	 * @param {string=} data.className     Optional. Class names that will be used in the admin notice.
	 * @param {string=} data.message       Optional. The message displayed in the notice.
	 * @param {number=} data.successes     Optional. The amount of successful operations.
	 * @param {number=} data.errors        Optional. The amount of failed operations.
	 * @param {Array=}  data.errorMessages Optional. Error messages of failed operations.
	 *
	 */
	mn.updates.addAdminNotice = function( data ) {
		var $notice = $( data.selector ), $adminNotice;

		delete data.selector;
		$adminNotice = mn.updates.adminNotice( data );

		// Check if this admin notice already exists.
		if ( ! $notice.length ) {
			$notice = $( '#' + data.id );
		}

		if ( $notice.length ) {
			$notice.replaceWith( $adminNotice );
		} else {
			$( '.wrap' ).find( '> h1' ).after( $adminNotice );
		}

		$document.trigger( 'mn-updates-notice-added' );
	};

	/**
	 * Handles Ajax requests to Mtaandao.
	 *
	 * @since 4.6.0
	 *
	 * @param {string} action The type of Ajax request ('update-plugin', 'install-theme', etc).
	 * @param {object} data   Data that needs to be passed to the ajax callback.
	 * @return {$.promise}    A jQuery promise that represents the request,
	 *                        decorated with an abort() method.
	 */
	mn.updates.ajax = function( action, data ) {
		var options = {};

		if ( mn.updates.ajaxLocked ) {
			mn.updates.queue.push( {
				action: action,
				data:   data
			} );

			// Return a Deferred object so callbacks can always be registered.
			return $.Deferred();
		}

		mn.updates.ajaxLocked = true;

		if ( data.success ) {
			options.success = data.success;
			delete data.success;
		}

		if ( data.error ) {
			options.error = data.error;
			delete data.error;
		}

		options.data = _.extend( data, {
			action:          action,
			_ajax_nonce:     mn.updates.ajaxNonce,
			username:        mn.updates.filesystemCredentials.ftp.username,
			password:        mn.updates.filesystemCredentials.ftp.password,
			hostname:        mn.updates.filesystemCredentials.ftp.hostname,
			connection_type: mn.updates.filesystemCredentials.ftp.connectionType,
			public_key:      mn.updates.filesystemCredentials.ssh.publicKey,
			private_key:     mn.updates.filesystemCredentials.ssh.privateKey
		} );

		return mn.ajax.send( options ).always( mn.updates.ajaxAlways );
	};

	/**
	 * Actions performed after every Ajax request.
	 *
	 * @since 4.6.0
	 *
	 * @param {object}  response
	 * @param {array=}  response.debug     Optional. Debug information.
	 * @param {string=} response.errorCode Optional. Error code for an error that occurred.
	 */
	mn.updates.ajaxAlways = function( response ) {
		if ( ! response.errorCode || 'unable_to_connect_to_filesystem' !== response.errorCode ) {
			mn.updates.ajaxLocked = false;
			mn.updates.queueChecker();
		}

		if ( 'undefined' !== typeof response.debug && window.console && window.console.log ) {
			_.map( response.debug, function( message ) {
				window.console.log( $( '<p />' ).html( message ).text() );
			} );
		}
	};

	/**
	 * Refreshes update counts everywhere on the screen.
	 *
	 * @since 4.7.0
	 */
	mn.updates.refreshCount = function() {
		var $adminBarUpdates              = $( '#admin-bar-updates' ),
			$dashboardNavMenuUpdateCount  = $( 'a[href="update-core.php"] .update-plugins' ),
			$pluginsNavMenuUpdateCount    = $( 'a[href="plugins.php"] .update-plugins' ),
			$appearanceNavMenuUpdateCount = $( 'a[href="themes.php"] .update-plugins' ),
			itemCount;

		$adminBarUpdates.find( '.ab-item' ).removeAttr( 'title' );
		$adminBarUpdates.find( '.ab-label' ).text( settings.totals.counts.total );

		// Remove the update count from the toolbar if it's zero.
		if ( 0 === settings.totals.counts.total ) {
			$adminBarUpdates.find( '.ab-label' ).parents( 'li' ).remove();
		}

		// Update the "Updates" menu item.
		$dashboardNavMenuUpdateCount.each( function( index, element ) {
			element.className = element.className.replace( /count-\d+/, 'count-' + settings.totals.counts.total );
		} );
		if ( settings.totals.counts.total > 0 ) {
			$dashboardNavMenuUpdateCount.find( '.update-count' ).text( settings.totals.counts.total );
		} else {
			$dashboardNavMenuUpdateCount.remove();
		}

		// Update the "Plugins" menu item.
		$pluginsNavMenuUpdateCount.each( function( index, element ) {
			element.className = element.className.replace( /count-\d+/, 'count-' + settings.totals.counts.plugins );
		} );
		if ( settings.totals.counts.total > 0 ) {
			$pluginsNavMenuUpdateCount.find( '.plugin-count' ).text( settings.totals.counts.plugins );
		} else {
			$pluginsNavMenuUpdateCount.remove();
		}

		// Update the "Appearance" menu item.
		$appearanceNavMenuUpdateCount.each( function( index, element ) {
			element.className = element.className.replace( /count-\d+/, 'count-' + settings.totals.counts.themes );
		} );
		if ( settings.totals.counts.total > 0 ) {
			$appearanceNavMenuUpdateCount.find( '.theme-count' ).text( settings.totals.counts.themes );
		} else {
			$appearanceNavMenuUpdateCount.remove();
		}

		// Update list table filter navigation.
		if ( 'plugins' === pagenow || 'plugins-network' === pagenow ) {
			itemCount = settings.totals.counts.plugins;
		} else if ( 'themes' === pagenow || 'themes-network' === pagenow ) {
			itemCount = settings.totals.counts.themes;
		}

		if ( itemCount > 0 ) {
			$( '.subsubsub .upgrade .count' ).text( '(' + itemCount + ')' );
		} else {
			$( '.subsubsub .upgrade' ).remove();
		}
	};

	/**
	 * Decrements the update counts throughout the various menus.
	 *
	 * This includes the toolbar, the "Updates" menu item and the menu items
	 * for plugins and themes.
	 *
	 * @since 3.9.0
	 *
	 * @param {string} type The type of item that was updated or deleted.
	 *                      Can be 'plugin', 'theme'.
	 */
	mn.updates.decrementCount = function( type ) {
		settings.totals.counts.total = Math.max( --settings.totals.counts.total, 0 );

		if ( 'plugin' === type ) {
			settings.totals.counts.plugins = Math.max( --settings.totals.counts.plugins, 0 );
		} else if ( 'theme' === type ) {
			settings.totals.counts.themes = Math.max( --settings.totals.counts.themes, 0 );
		}

		mn.updates.refreshCount( type );
	};

	/**
	 * Sends an Ajax request to the server to update a plugin.
	 *
	 * @since 4.2.0
	 * @since 4.6.0 More accurately named `updatePlugin`.
	 *
	 * @param {object}               args         Arguments.
	 * @param {string}               args.plugin  Plugin basename.
	 * @param {string}               args.slug    Plugin slug.
	 * @param {updatePluginSuccess=} args.success Optional. Success callback. Default: mn.updates.updatePluginSuccess
	 * @param {updatePluginError=}   args.error   Optional. Error callback. Default: mn.updates.updatePluginError
	 * @return {$.promise} A jQuery promise that represents the request,
	 *                     decorated with an abort() method.
	 */
	mn.updates.updatePlugin = function( args ) {
		var $updateRow, $card, $message, message;

		args = _.extend( {
			success: mn.updates.updatePluginSuccess,
			error: mn.updates.updatePluginError
		}, args );

		if ( 'plugins' === pagenow || 'plugins-network' === pagenow ) {
			$updateRow = $( 'tr[data-plugin="' + args.plugin + '"]' );
			$message   = $updateRow.find( '.update-message' ).removeClass( 'notice-error' ).addClass( 'updating-message notice-warning' ).find( 'p' );
			message    = mn.updates.l10n.updatingLabel.replace( '%s', $updateRow.find( '.plugin-title strong' ).text() );
		} else if ( 'plugin-install' === pagenow || 'plugin-install-network' === pagenow ) {
			$card    = $( '.plugin-card-' + args.slug );
			$message = $card.find( '.update-now' ).addClass( 'updating-message' );
			message  = mn.updates.l10n.updatingLabel.replace( '%s', $message.data( 'name' ) );

			// Remove previous error messages, if any.
			$card.removeClass( 'plugin-card-update-failed' ).find( '.notice.notice-error' ).remove();
		}

		if ( $message.html() !== mn.updates.l10n.updating ) {
			$message.data( 'originaltext', $message.html() );
		}

		$message
			.attr( 'aria-label', message )
			.text( mn.updates.l10n.updating );

		$document.trigger( 'mn-plugin-updating', args );

		return mn.updates.ajax( 'update-plugin', args );
	};

	/**
	 * Updates the UI appropriately after a successful plugin update.
	 *
	 * @since 4.2.0
	 * @since 4.6.0 More accurately named `updatePluginSuccess`.
	 *
	 * @typedef {object} updatePluginSuccess
	 * @param {object} response            Response from the server.
	 * @param {string} response.slug       Slug of the plugin to be updated.
	 * @param {string} response.plugin     Basename of the plugin to be updated.
	 * @param {string} response.pluginName Name of the plugin to be updated.
	 * @param {string} response.oldVersion Old version of the plugin.
	 * @param {string} response.newVersion New version of the plugin.
	 */
	mn.updates.updatePluginSuccess = function( response ) {
		var $pluginRow, $updateMessage, newText;

		if ( 'plugins' === pagenow || 'plugins-network' === pagenow ) {
			$pluginRow     = $( 'tr[data-plugin="' + response.plugin + '"]' )
				.removeClass( 'update' )
				.addClass( 'updated' );
			$updateMessage = $pluginRow.find( '.update-message' )
				.removeClass( 'updating-message notice-warning' )
				.addClass( 'updated-message notice-success' ).find( 'p' );

			// Update the version number in the row.
			newText = $pluginRow.find( '.plugin-version-author-uri' ).html().replace( response.oldVersion, response.newVersion );
			$pluginRow.find( '.plugin-version-author-uri' ).html( newText );
		} else if ( 'plugin-install' === pagenow || 'plugin-install-network' === pagenow ) {
			$updateMessage = $( '.plugin-card-' + response.slug ).find( '.update-now' )
				.removeClass( 'updating-message' )
				.addClass( 'button-disabled updated-message' );
		}

		$updateMessage
			.attr( 'aria-label', mn.updates.l10n.updatedLabel.replace( '%s', response.pluginName ) )
			.text( mn.updates.l10n.updated );

		mn.a11y.speak( mn.updates.l10n.updatedMsg, 'polite' );

		mn.updates.decrementCount( 'plugin' );

		$document.trigger( 'mn-plugin-update-success', response );
	};

	/**
	 * Updates the UI appropriately after a failed plugin update.
	 *
	 * @since 4.2.0
	 * @since 4.6.0 More accurately named `updatePluginError`.
	 *
	 * @typedef {object} updatePluginError
	 * @param {object}  response              Response from the server.
	 * @param {string}  response.slug         Slug of the plugin to be updated.
	 * @param {string}  response.plugin       Basename of the plugin to be updated.
	 * @param {string=} response.pluginName   Optional. Name of the plugin to be updated.
	 * @param {string}  response.errorCode    Error code for the error that occurred.
	 * @param {string}  response.errorMessage The error that occurred.
	 */
	mn.updates.updatePluginError = function( response ) {
		var $card, $message, errorMessage;

		if ( ! mn.updates.isValidResponse( response, 'update' ) ) {
			return;
		}

		if ( mn.updates.maybeHandleCredentialError( response, 'update-plugin' ) ) {
			return;
		}

		errorMessage = mn.updates.l10n.updateFailed.replace( '%s', response.errorMessage );

		if ( 'plugins' === pagenow || 'plugins-network' === pagenow ) {
			if ( response.plugin ) {
				$message = $( 'tr[data-plugin="' + response.plugin + '"]' ).find( '.update-message' );
			} else {
				$message = $( 'tr[data-slug="' + response.slug + '"]' ).find( '.update-message' );
			}
			$message.removeClass( 'updating-message notice-warning' ).addClass( 'notice-error' ).find( 'p' ).html( errorMessage );

			if ( response.pluginName ) {
				$message.find( 'p' )
					.attr( 'aria-label', mn.updates.l10n.updateFailedLabel.replace( '%s', response.pluginName ) );
			} else {
				$message.find( 'p' ).removeAttr( 'aria-label' );
			}
		} else if ( 'plugin-install' === pagenow || 'plugin-install-network' === pagenow ) {
			$card = $( '.plugin-card-' + response.slug )
				.addClass( 'plugin-card-update-failed' )
				.append( mn.updates.adminNotice( {
					className: 'update-message notice-error notice-alt is-dismissible',
					message:   errorMessage
				} ) );

			$card.find( '.update-now' )
				.text( mn.updates.l10n.updateFailedShort ).removeClass( 'updating-message' );

			if ( response.pluginName ) {
				$card.find( '.update-now' )
					.attr( 'aria-label', mn.updates.l10n.updateFailedLabel.replace( '%s', response.pluginName ) );
			} else {
				$card.find( '.update-now' ).removeAttr( 'aria-label' );
			}

			$card.on( 'click', '.notice.is-dismissible .notice-dismiss', function() {

				// Use same delay as the total duration of the notice fadeTo + slideUp animation.
				setTimeout( function() {
					$card
						.removeClass( 'plugin-card-update-failed' )
						.find( '.column-name a' ).focus();

					$card.find( '.update-now' )
						.attr( 'aria-label', false )
						.text( mn.updates.l10n.updateNow );
				}, 200 );
			} );
		}

		mn.a11y.speak( errorMessage, 'assertive' );

		$document.trigger( 'mn-plugin-update-error', response );
	};

	/**
	 * Sends an Ajax request to the server to install a plugin.
	 *
	 * @since 4.6.0
	 *
	 * @param {object}                args         Arguments.
	 * @param {string}                args.slug    Plugin identifier in the Mtaandao.org Plugin repository.
	 * @param {installPluginSuccess=} args.success Optional. Success callback. Default: mn.updates.installPluginSuccess
	 * @param {installPluginError=}   args.error   Optional. Error callback. Default: mn.updates.installPluginError
	 * @return {$.promise} A jQuery promise that represents the request,
	 *                     decorated with an abort() method.
	 */
	mn.updates.installPlugin = function( args ) {
		var $card    = $( '.plugin-card-' + args.slug ),
			$message = $card.find( '.install-now' );

		args = _.extend( {
			success: mn.updates.installPluginSuccess,
			error: mn.updates.installPluginError
		}, args );

		if ( 'import' === pagenow ) {
			$message = $( '[data-slug="' + args.slug + '"]' );
		}

		if ( $message.html() !== mn.updates.l10n.installing ) {
			$message.data( 'originaltext', $message.html() );
		}

		$message
			.addClass( 'updating-message' )
			.attr( 'aria-label', mn.updates.l10n.pluginInstallingLabel.replace( '%s', $message.data( 'name' ) ) )
			.text( mn.updates.l10n.installing );

		mn.a11y.speak( mn.updates.l10n.installingMsg, 'polite' );

		// Remove previous error messages, if any.
		$card.removeClass( 'plugin-card-install-failed' ).find( '.notice.notice-error' ).remove();

		$document.trigger( 'mn-plugin-installing', args );

		return mn.updates.ajax( 'install-plugin', args );
	};

	/**
	 * Updates the UI appropriately after a successful plugin install.
	 *
	 * @since 4.6.0
	 *
	 * @typedef {object} installPluginSuccess
	 * @param {object} response             Response from the server.
	 * @param {string} response.slug        Slug of the installed plugin.
	 * @param {string} response.pluginName  Name of the installed plugin.
	 * @param {string} response.activateUrl URL to activate the just installed plugin.
	 */
	mn.updates.installPluginSuccess = function( response ) {
		var $message = $( '.plugin-card-' + response.slug ).find( '.install-now' );

		$message
			.removeClass( 'updating-message' )
			.addClass( 'updated-message installed button-disabled' )
			.attr( 'aria-label', mn.updates.l10n.pluginInstalledLabel.replace( '%s', response.pluginName ) )
			.text( mn.updates.l10n.installed );

		mn.a11y.speak( mn.updates.l10n.installedMsg, 'polite' );

		$document.trigger( 'mn-plugin-install-success', response );

		if ( response.activateUrl ) {
			setTimeout( function() {

				// Transform the 'Install' button into an 'Activate' button.
				$message.removeClass( 'install-now installed button-disabled updated-message' ).addClass( 'activate-now button-primary' )
					.attr( 'href', response.activateUrl )
					.attr( 'aria-label', mn.updates.l10n.activatePluginLabel.replace( '%s', response.pluginName ) )
					.text( mn.updates.l10n.activatePlugin );
			}, 1000 );
		}
	};

	/**
	 * Updates the UI appropriately after a failed plugin install.
	 *
	 * @since 4.6.0
	 *
	 * @typedef {object} installPluginError
	 * @param {object}  response              Response from the server.
	 * @param {string}  response.slug         Slug of the plugin to be installed.
	 * @param {string=} response.pluginName   Optional. Name of the plugin to be installed.
	 * @param {string}  response.errorCode    Error code for the error that occurred.
	 * @param {string}  response.errorMessage The error that occurred.
	 */
	mn.updates.installPluginError = function( response ) {
		var $card   = $( '.plugin-card-' + response.slug ),
			$button = $card.find( '.install-now' ),
			errorMessage;

		if ( ! mn.updates.isValidResponse( response, 'install' ) ) {
			return;
		}

		if ( mn.updates.maybeHandleCredentialError( response, 'install-plugin' ) ) {
			return;
		}

		errorMessage = mn.updates.l10n.installFailed.replace( '%s', response.errorMessage );

		$card
			.addClass( 'plugin-card-update-failed' )
			.append( '<div class="notice notice-error notice-alt is-dismissible"><p>' + errorMessage + '</p></div>' );

		$card.on( 'click', '.notice.is-dismissible .notice-dismiss', function() {

			// Use same delay as the total duration of the notice fadeTo + slideUp animation.
			setTimeout( function() {
				$card
					.removeClass( 'plugin-card-update-failed' )
					.find( '.column-name a' ).focus();
			}, 200 );
		} );

		$button
			.removeClass( 'updating-message' ).addClass( 'button-disabled' )
			.attr( 'aria-label', mn.updates.l10n.pluginInstallFailedLabel.replace( '%s', $button.data( 'name' ) ) )
			.text( mn.updates.l10n.installFailedShort );

		mn.a11y.speak( errorMessage, 'assertive' );

		$document.trigger( 'mn-plugin-install-error', response );
	};

	/**
	 * Updates the UI appropriately after a successful importer install.
	 *
	 * @since 4.6.0
	 *
	 * @typedef {object} installImporterSuccess
	 * @param {object} response             Response from the server.
	 * @param {string} response.slug        Slug of the installed plugin.
	 * @param {string} response.pluginName  Name of the installed plugin.
	 * @param {string} response.activateUrl URL to activate the just installed plugin.
	 */
	mn.updates.installImporterSuccess = function( response ) {
		mn.updates.addAdminNotice( {
			id:        'install-success',
			className: 'notice-success is-dismissible',
			message:   mn.updates.l10n.importerInstalledMsg.replace( '%s', response.activateUrl + '&from=import' )
		} );

		$( '[data-slug="' + response.slug + '"]' )
			.removeClass( 'install-now updating-message' )
			.addClass( 'activate-now' )
			.attr({
				'href': response.activateUrl + '&from=import',
				'aria-label': mn.updates.l10n.activateImporterLabel.replace( '%s', response.pluginName )
			})
			.text( mn.updates.l10n.activateImporter );

		mn.a11y.speak( mn.updates.l10n.installedMsg, 'polite' );

		$document.trigger( 'mn-importer-install-success', response );
	};

	/**
	 * Updates the UI appropriately after a failed importer install.
	 *
	 * @since 4.6.0
	 *
	 * @typedef {object} installImporterError
	 * @param {object}  response              Response from the server.
	 * @param {string}  response.slug         Slug of the plugin to be installed.
	 * @param {string=} response.pluginName   Optional. Name of the plugin to be installed.
	 * @param {string}  response.errorCode    Error code for the error that occurred.
	 * @param {string}  response.errorMessage The error that occurred.
	 */
	mn.updates.installImporterError = function( response ) {
		var errorMessage = mn.updates.l10n.installFailed.replace( '%s', response.errorMessage ),
			$installLink = $( '[data-slug="' + response.slug + '"]' ),
			pluginName = $installLink.data( 'name' );

		if ( ! mn.updates.isValidResponse( response, 'install' ) ) {
			return;
		}

		if ( mn.updates.maybeHandleCredentialError( response, 'install-plugin' ) ) {
			return;
		}

		mn.updates.addAdminNotice( {
			id:        response.errorCode,
			className: 'notice-error is-dismissible',
			message:   errorMessage
		} );

		$installLink
			.removeClass( 'updating-message' )
			.text( mn.updates.l10n.installNow )
			.attr( 'aria-label', mn.updates.l10n.installNowLabel.replace( '%s', pluginName ) );

		mn.a11y.speak( errorMessage, 'assertive' );

		$document.trigger( 'mn-importer-install-error', response );
	};

	/**
	 * Sends an Ajax request to the server to delete a plugin.
	 *
	 * @since 4.6.0
	 *
	 * @param {object}               args         Arguments.
	 * @param {string}               args.plugin  Basename of the plugin to be deleted.
	 * @param {string}               args.slug    Slug of the plugin to be deleted.
	 * @param {deletePluginSuccess=} args.success Optional. Success callback. Default: mn.updates.deletePluginSuccess
	 * @param {deletePluginError=}   args.error   Optional. Error callback. Default: mn.updates.deletePluginError
	 * @return {$.promise} A jQuery promise that represents the request,
	 *                     decorated with an abort() method.
	 */
	mn.updates.deletePlugin = function( args ) {
		var $link = $( '[data-plugin="' + args.plugin + '"]' ).find( '.row-actions a.delete' );

		args = _.extend( {
			success: mn.updates.deletePluginSuccess,
			error: mn.updates.deletePluginError
		}, args );

		if ( $link.html() !== mn.updates.l10n.deleting ) {
			$link
				.data( 'originaltext', $link.html() )
				.text( mn.updates.l10n.deleting );
		}

		mn.a11y.speak( mn.updates.l10n.deleting, 'polite' );

		$document.trigger( 'mn-plugin-deleting', args );

		return mn.updates.ajax( 'delete-plugin', args );
	};

	/**
	 * Updates the UI appropriately after a successful plugin deletion.
	 *
	 * @since 4.6.0
	 *
	 * @typedef {object} deletePluginSuccess
	 * @param {object} response            Response from the server.
	 * @param {string} response.slug       Slug of the plugin that was deleted.
	 * @param {string} response.plugin     Base name of the plugin that was deleted.
	 * @param {string} response.pluginName Name of the plugin that was deleted.
	 */
	mn.updates.deletePluginSuccess = function( response ) {

		// Removes the plugin and updates rows.
		$( '[data-plugin="' + response.plugin + '"]' ).css( { backgroundColor: '#faafaa' } ).fadeOut( 350, function() {
			var $form            = $( '#bulk-action-form' ),
				$views           = $( '.subsubsub' ),
				$pluginRow       = $( this ),
				columnCount      = $form.find( 'thead th:not(.hidden), thead td' ).length,
				pluginDeletedRow = mn.template( 'item-deleted-row' ),
				/** @type {object} plugins Base names of plugins in their different states. */
				plugins          = settings.plugins;

			// Add a success message after deleting a plugin.
			if ( ! $pluginRow.hasClass( 'plugin-update-tr' ) ) {
				$pluginRow.after(
					pluginDeletedRow( {
						slug:    response.slug,
						plugin:  response.plugin,
						colspan: columnCount,
						name:    response.pluginName
					} )
				);
			}

			$pluginRow.remove();

			// Remove plugin from update count.
			if ( -1 !== _.indexOf( plugins.upgrade, response.plugin ) ) {
				plugins.upgrade = _.without( plugins.upgrade, response.plugin );
				mn.updates.decrementCount( 'plugin' );
			}

			// Remove from views.
			if ( -1 !== _.indexOf( plugins.inactive, response.plugin ) ) {
				plugins.inactive = _.without( plugins.inactive, response.plugin );
				if ( plugins.inactive.length ) {
					$views.find( '.inactive .count' ).text( '(' + plugins.inactive.length + ')' );
				} else {
					$views.find( '.inactive' ).remove();
				}
			}

			if ( -1 !== _.indexOf( plugins.active, response.plugin ) ) {
				plugins.active = _.without( plugins.active, response.plugin );
				if ( plugins.active.length ) {
					$views.find( '.active .count' ).text( '(' + plugins.active.length + ')' );
				} else {
					$views.find( '.active' ).remove();
				}
			}

			if ( -1 !== _.indexOf( plugins.recently_activated, response.plugin ) ) {
				plugins.recently_activated = _.without( plugins.recently_activated, response.plugin );
				if ( plugins.recently_activated.length ) {
					$views.find( '.recently_activated .count' ).text( '(' + plugins.recently_activated.length + ')' );
				} else {
					$views.find( '.recently_activated' ).remove();
				}
			}

			plugins.all = _.without( plugins.all, response.plugin );

			if ( plugins.all.length ) {
				$views.find( '.all .count' ).text( '(' + plugins.all.length + ')' );
			} else {
				$form.find( '.tablenav' ).css( { visibility: 'hidden' } );
				$views.find( '.all' ).remove();

				if ( ! $form.find( 'tr.no-items' ).length ) {
					$form.find( '#the-list' ).append( '<tr class="no-items"><td class="colspanchange" colspan="' + columnCount + '">' + mn.updates.l10n.noPlugins + '</td></tr>' );
				}
			}
		} );

		mn.a11y.speak( mn.updates.l10n.deleted, 'polite' );

		$document.trigger( 'mn-plugin-delete-success', response );
	};

	/**
	 * Updates the UI appropriately after a failed plugin deletion.
	 *
	 * @since 4.6.0
	 *
	 * @typedef {object} deletePluginError
	 * @param {object}  response              Response from the server.
	 * @param {string}  response.slug         Slug of the plugin to be deleted.
	 * @param {string}  response.plugin       Base name of the plugin to be deleted
	 * @param {string=} response.pluginName   Optional. Name of the plugin to be deleted.
	 * @param {string}  response.errorCode    Error code for the error that occurred.
	 * @param {string}  response.errorMessage The error that occurred.
	 */
	mn.updates.deletePluginError = function( response ) {
		var $plugin, $pluginUpdateRow,
			pluginUpdateRow  = mn.template( 'item-update-row' ),
			noticeContent    = mn.updates.adminNotice( {
				className: 'update-message notice-error notice-alt',
				message:   response.errorMessage
			} );

		if ( response.plugin ) {
			$plugin          = $( 'tr.inactive[data-plugin="' + response.plugin + '"]' );
			$pluginUpdateRow = $plugin.siblings( '[data-plugin="' + response.plugin + '"]' );
		} else {
			$plugin          = $( 'tr.inactive[data-slug="' + response.slug + '"]' );
			$pluginUpdateRow = $plugin.siblings( '[data-slug="' + response.slug + '"]' );
		}

		if ( ! mn.updates.isValidResponse( response, 'delete' ) ) {
			return;
		}

		if ( mn.updates.maybeHandleCredentialError( response, 'delete-plugin' ) ) {
			return;
		}

		// Add a plugin update row if it doesn't exist yet.
		if ( ! $pluginUpdateRow.length ) {
			$plugin.addClass( 'update' ).after(
				pluginUpdateRow( {
					slug:    response.slug,
					plugin:  response.plugin || response.slug,
					colspan: $( '#bulk-action-form' ).find( 'thead th:not(.hidden), thead td' ).length,
					content: noticeContent
				} )
			);
		} else {

			// Remove previous error messages, if any.
			$pluginUpdateRow.find( '.notice-error' ).remove();

			$pluginUpdateRow.find( '.plugin-update' ).append( noticeContent );
		}

		$document.trigger( 'mn-plugin-delete-error', response );
	};

	/**
	 * Sends an Ajax request to the server to update a theme.
	 *
	 * @since 4.6.0
	 *
	 * @param {object}              args         Arguments.
	 * @param {string}              args.slug    Theme stylesheet.
	 * @param {updateThemeSuccess=} args.success Optional. Success callback. Default: mn.updates.updateThemeSuccess
	 * @param {updateThemeError=}   args.error   Optional. Error callback. Default: mn.updates.updateThemeError
	 * @return {$.promise} A jQuery promise that represents the request,
	 *                     decorated with an abort() method.
	 */
	mn.updates.updateTheme = function( args ) {
		var $notice;

		args = _.extend( {
			success: mn.updates.updateThemeSuccess,
			error: mn.updates.updateThemeError
		}, args );

		if ( 'themes-network' === pagenow ) {
			$notice = $( '[data-slug="' + args.slug + '"]' ).find( '.update-message' ).removeClass( 'notice-error' ).addClass( 'updating-message notice-warning' ).find( 'p' );

		} else {
			$notice = $( '#update-theme' ).closest( '.notice' ).removeClass( 'notice-large' );

			$notice.find( 'h3' ).remove();

			$notice = $notice.add( $( '[data-slug="' + args.slug + '"]' ).find( '.update-message' ) );
			$notice = $notice.addClass( 'updating-message' ).find( 'p' );
		}

		if ( $notice.html() !== mn.updates.l10n.updating ) {
			$notice.data( 'originaltext', $notice.html() );
		}

		mn.a11y.speak( mn.updates.l10n.updatingMsg, 'polite' );
		$notice.text( mn.updates.l10n.updating );

		$document.trigger( 'mn-theme-updating', args );

		return mn.updates.ajax( 'update-theme', args );
	};

	/**
	 * Updates the UI appropriately after a successful theme update.
	 *
	 * @since 4.6.0
	 *
	 * @typedef {object} updateThemeSuccess
	 * @param {object} response
	 * @param {string} response.slug       Slug of the theme to be updated.
	 * @param {object} response.theme      Updated theme.
	 * @param {string} response.oldVersion Old version of the theme.
	 * @param {string} response.newVersion New version of the theme.
	 */
	mn.updates.updateThemeSuccess = function( response ) {
		var isModalOpen    = $( 'body.modal-open' ).length,
			$theme         = $( '[data-slug="' + response.slug + '"]' ),
			updatedMessage = {
				className: 'updated-message notice-success notice-alt',
				message:   mn.updates.l10n.updated
			},
			$notice, newText;

		if ( 'themes-network' === pagenow ) {
			$notice = $theme.find( '.update-message' );

			// Update the version number in the row.
			newText = $theme.find( '.theme-version-author-uri' ).html().replace( response.oldVersion, response.newVersion );
			$theme.find( '.theme-version-author-uri' ).html( newText );
		} else {
			$notice = $( '.theme-info .notice' ).add( $theme.find( '.update-message' ) );

			// Focus on Customize button after updating.
			if ( isModalOpen ) {
				$( '.load-customize:visible' ).focus();
			} else {
				$theme.find( '.load-customize' ).focus();
			}
		}

		mn.updates.addAdminNotice( _.extend( { selector: $notice }, updatedMessage ) );
		mn.a11y.speak( mn.updates.l10n.updatedMsg, 'polite' );

		mn.updates.decrementCount( 'theme' );

		$document.trigger( 'mn-theme-update-success', response );

		// Show updated message after modal re-rendered.
		if ( isModalOpen ) {
			$( '.theme-info .theme-author' ).after( mn.updates.adminNotice( updatedMessage ) );
		}
	};

	/**
	 * Updates the UI appropriately after a failed theme update.
	 *
	 * @since 4.6.0
	 *
	 * @typedef {object} updateThemeError
	 * @param {object} response              Response from the server.
	 * @param {string} response.slug         Slug of the theme to be updated.
	 * @param {string} response.errorCode    Error code for the error that occurred.
	 * @param {string} response.errorMessage The error that occurred.
	 */
	mn.updates.updateThemeError = function( response ) {
		var $theme       = $( '[data-slug="' + response.slug + '"]' ),
			errorMessage = mn.updates.l10n.updateFailed.replace( '%s', response.errorMessage ),
			$notice;

		if ( ! mn.updates.isValidResponse( response, 'update' ) ) {
			return;
		}

		if ( mn.updates.maybeHandleCredentialError( response, 'update-theme' ) ) {
			return;
		}

		if ( 'themes-network' === pagenow ) {
			$notice = $theme.find( '.update-message ' );
		} else {
			$notice = $( '.theme-info .notice' ).add( $theme.find( '.notice' ) );

			$( 'body.modal-open' ).length ? $( '.load-customize:visible' ).focus() : $theme.find( '.load-customize' ).focus();
		}

		mn.updates.addAdminNotice( {
			selector:  $notice,
			className: 'update-message notice-error notice-alt is-dismissible',
			message:   errorMessage
		} );

		mn.a11y.speak( errorMessage, 'polite' );

		$document.trigger( 'mn-theme-update-error', response );
	};

	/**
	 * Sends an Ajax request to the server to install a theme.
	 *
	 * @since 4.6.0
	 *
	 * @param {object}               args
	 * @param {string}               args.slug    Theme stylesheet.
	 * @param {installThemeSuccess=} args.success Optional. Success callback. Default: mn.updates.installThemeSuccess
	 * @param {installThemeError=}   args.error   Optional. Error callback. Default: mn.updates.installThemeError
	 * @return {$.promise} A jQuery promise that represents the request,
	 *                     decorated with an abort() method.
	 */
	mn.updates.installTheme = function( args ) {
		var $message = $( '.theme-install[data-slug="' + args.slug + '"]' );

		args = _.extend( {
			success: mn.updates.installThemeSuccess,
			error: mn.updates.installThemeError
		}, args );

		$message.addClass( 'updating-message' );
		$message.parents( '.theme' ).addClass( 'focus' );
		if ( $message.html() !== mn.updates.l10n.installing ) {
			$message.data( 'originaltext', $message.html() );
		}

		$message
			.text( mn.updates.l10n.installing )
			.attr( 'aria-label', mn.updates.l10n.themeInstallingLabel.replace( '%s', $message.data( 'name' ) ) );
		mn.a11y.speak( mn.updates.l10n.installingMsg, 'polite' );

		// Remove previous error messages, if any.
		$( '.install-theme-info, [data-slug="' + args.slug + '"]' ).removeClass( 'theme-install-failed' ).find( '.notice.notice-error' ).remove();

		$document.trigger( 'mn-theme-installing', args );

		return mn.updates.ajax( 'install-theme', args );
	};

	/**
	 * Updates the UI appropriately after a successful theme install.
	 *
	 * @since 4.6.0
	 *
	 * @typedef {object} installThemeSuccess
	 * @param {object} response              Response from the server.
	 * @param {string} response.slug         Slug of the theme to be installed.
	 * @param {string} response.customizeUrl URL to the Customizer for the just installed theme.
	 * @param {string} response.activateUrl  URL to activate the just installed theme.
	 */
	mn.updates.installThemeSuccess = function( response ) {
		var $card = $( '.mn-full-overlay-header, [data-slug=' + response.slug + ']' ),
			$message;

		$document.trigger( 'mn-theme-install-success', response );

		$message = $card.find( '.button-primary' )
			.removeClass( 'updating-message' )
			.addClass( 'updated-message disabled' )
			.attr( 'aria-label', mn.updates.l10n.themeInstalledLabel.replace( '%s', response.themeName ) )
			.text( mn.updates.l10n.installed );

		mn.a11y.speak( mn.updates.l10n.installedMsg, 'polite' );

		setTimeout( function() {

			if ( response.activateUrl ) {

				// Transform the 'Install' button into an 'Activate' button.
				$message
					.attr( 'href', response.activateUrl )
					.removeClass( 'theme-install updated-message disabled' )
					.addClass( 'activate' )
					.attr( 'aria-label', mn.updates.l10n.activateThemeLabel.replace( '%s', response.themeName ) )
					.text( mn.updates.l10n.activateTheme );
			}

			if ( response.customizeUrl ) {

				// Transform the 'Preview' button into a 'Live Preview' button.
				$message.siblings( '.preview' ).replaceWith( function () {
					return $( '<a>' )
						.attr( 'href', response.customizeUrl )
						.addClass( 'button load-customize' )
						.text( mn.updates.l10n.livePreview );
				} );
			}
		}, 1000 );
	};

	/**
	 * Updates the UI appropriately after a failed theme install.
	 *
	 * @since 4.6.0
	 *
	 * @typedef {object} installThemeError
	 * @param {object} response              Response from the server.
	 * @param {string} response.slug         Slug of the theme to be installed.
	 * @param {string} response.errorCode    Error code for the error that occurred.
	 * @param {string} response.errorMessage The error that occurred.
	 */
	mn.updates.installThemeError = function( response ) {
		var $card, $button,
			errorMessage = mn.updates.l10n.installFailed.replace( '%s', response.errorMessage ),
			$message     = mn.updates.adminNotice( {
				className: 'update-message notice-error notice-alt',
				message:   errorMessage
			} );

		if ( ! mn.updates.isValidResponse( response, 'install' ) ) {
			return;
		}

		if ( mn.updates.maybeHandleCredentialError( response, 'install-theme' ) ) {
			return;
		}

		if ( $document.find( 'body' ).hasClass( 'full-overlay-active' ) ) {
			$button = $( '.theme-install[data-slug="' + response.slug + '"]' );
			$card   = $( '.install-theme-info' ).prepend( $message );
		} else {
			$card   = $( '[data-slug="' + response.slug + '"]' ).removeClass( 'focus' ).addClass( 'theme-install-failed' ).append( $message );
			$button = $card.find( '.theme-install' );
		}

		$button
			.removeClass( 'updating-message' )
			.attr( 'aria-label', mn.updates.l10n.themeInstallFailedLabel.replace( '%s', $button.data( 'name' ) ) )
			.text( mn.updates.l10n.installFailedShort );

		mn.a11y.speak( errorMessage, 'assertive' );

		$document.trigger( 'mn-theme-install-error', response );
	};

	/**
	 * Sends an Ajax request to the server to install a theme.
	 *
	 * @since 4.6.0
	 *
	 * @param {object}              args
	 * @param {string}              args.slug    Theme stylesheet.
	 * @param {deleteThemeSuccess=} args.success Optional. Success callback. Default: mn.updates.deleteThemeSuccess
	 * @param {deleteThemeError=}   args.error   Optional. Error callback. Default: mn.updates.deleteThemeError
	 * @return {$.promise} A jQuery promise that represents the request,
	 *                     decorated with an abort() method.
	 */
	mn.updates.deleteTheme = function( args ) {
		var $button;

		if ( 'themes' === pagenow ) {
			$button = $( '.theme-actions .delete-theme' );
		} else if ( 'themes-network' === pagenow ) {
			$button = $( '[data-slug="' + args.slug + '"]' ).find( '.row-actions a.delete' );
		}

		args = _.extend( {
			success: mn.updates.deleteThemeSuccess,
			error: mn.updates.deleteThemeError
		}, args );

		if ( $button && $button.html() !== mn.updates.l10n.deleting ) {
			$button
				.data( 'originaltext', $button.html() )
				.text( mn.updates.l10n.deleting );
		}

		mn.a11y.speak( mn.updates.l10n.deleting, 'polite' );

		// Remove previous error messages, if any.
		$( '.theme-info .update-message' ).remove();

		$document.trigger( 'mn-theme-deleting', args );

		return mn.updates.ajax( 'delete-theme', args );
	};

	/**
	 * Updates the UI appropriately after a successful theme deletion.
	 *
	 * @since 4.6.0
	 *
	 * @typedef {object} deleteThemeSuccess
	 * @param {object} response      Response from the server.
	 * @param {string} response.slug Slug of the theme that was deleted.
	 */
	mn.updates.deleteThemeSuccess = function( response ) {
		var $themeRows = $( '[data-slug="' + response.slug + '"]' );

		if ( 'themes-network' === pagenow ) {

			// Removes the theme and updates rows.
			$themeRows.css( { backgroundColor: '#faafaa' } ).fadeOut( 350, function() {
				var $views     = $( '.subsubsub' ),
					$themeRow  = $( this ),
					totals     = settings.themes,
					deletedRow = mn.template( 'item-deleted-row' );

				if ( ! $themeRow.hasClass( 'plugin-update-tr' ) ) {
					$themeRow.after(
						deletedRow( {
							slug:    response.slug,
							colspan: $( '#bulk-action-form' ).find( 'thead th:not(.hidden), thead td' ).length,
							name:    $themeRow.find( '.theme-title strong' ).text()
						} )
					);
				}

				$themeRow.remove();

				// Remove theme from update count.
				if ( $themeRow.hasClass( 'update' ) ) {
					totals.upgrade--;
					mn.updates.decrementCount( 'theme' );
				}

				// Remove from views.
				if ( $themeRow.hasClass( 'inactive' ) ) {
					totals.disabled--;
					if ( totals.disabled ) {
						$views.find( '.disabled .count' ).text( '(' + totals.disabled + ')' );
					} else {
						$views.find( '.disabled' ).remove();
					}
				}

				// There is always at least one theme available.
				$views.find( '.all .count' ).text( '(' + --totals.all + ')' );
			} );
		}

		mn.a11y.speak( mn.updates.l10n.deleted, 'polite' );

		$document.trigger( 'mn-theme-delete-success', response );
	};

	/**
	 * Updates the UI appropriately after a failed theme deletion.
	 *
	 * @since 4.6.0
	 *
	 * @typedef {object} deleteThemeError
	 * @param {object} response              Response from the server.
	 * @param {string} response.slug         Slug of the theme to be deleted.
	 * @param {string} response.errorCode    Error code for the error that occurred.
	 * @param {string} response.errorMessage The error that occurred.
	 */
	mn.updates.deleteThemeError = function( response ) {
		var $themeRow    = $( 'tr.inactive[data-slug="' + response.slug + '"]' ),
			$button      = $( '.theme-actions .delete-theme' ),
			updateRow    = mn.template( 'item-update-row' ),
			$updateRow   = $themeRow.siblings( '#' + response.slug + '-update' ),
			errorMessage = mn.updates.l10n.deleteFailed.replace( '%s', response.errorMessage ),
			$message     = mn.updates.adminNotice( {
				className: 'update-message notice-error notice-alt',
				message:   errorMessage
			} );

		if ( mn.updates.maybeHandleCredentialError( response, 'delete-theme' ) ) {
			return;
		}

		if ( 'themes-network' === pagenow ) {
			if ( ! $updateRow.length ) {
				$themeRow.addClass( 'update' ).after(
					updateRow( {
						slug: response.slug,
						colspan: $( '#bulk-action-form' ).find( 'thead th:not(.hidden), thead td' ).length,
						content: $message
					} )
				);
			} else {
				// Remove previous error messages, if any.
				$updateRow.find( '.notice-error' ).remove();
				$updateRow.find( '.plugin-update' ).append( $message );
			}
		} else {
			$( '.theme-info .theme-description' ).before( $message );
		}

		$button.html( $button.data( 'originaltext' ) );

		mn.a11y.speak( errorMessage, 'assertive' );

		$document.trigger( 'mn-theme-delete-error', response );
	};

	/**
	 * Adds the appropriate callback based on the type of action and the current page.
	 *
	 * @since 4.6.0
	 * @private
	 *
	 * @param {object} data   AJAX payload.
	 * @param {string} action The type of request to perform.
	 * @return {object} The AJAX payload with the appropriate callbacks.
	 */
	mn.updates._addCallbacks = function( data, action ) {
		if ( 'import' === pagenow && 'install-plugin' === action ) {
			data.success = mn.updates.installImporterSuccess;
			data.error   = mn.updates.installImporterError;
		}

		return data;
	};

	/**
	 * Pulls available jobs from the queue and runs them.
	 *
	 * @since 4.2.0
	 * @since 4.6.0 Can handle multiple job types.
	 */
	mn.updates.queueChecker = function() {
		var job;

		if ( mn.updates.ajaxLocked || ! mn.updates.queue.length ) {
			return;
		}

		job = mn.updates.queue.shift();

		// Handle a queue job.
		switch ( job.action ) {
			case 'install-plugin':
				mn.updates.installPlugin( job.data );
				break;

			case 'update-plugin':
				mn.updates.updatePlugin( job.data );
				break;

			case 'delete-plugin':
				mn.updates.deletePlugin( job.data );
				break;

			case 'install-theme':
				mn.updates.installTheme( job.data );
				break;

			case 'update-theme':
				mn.updates.updateTheme( job.data );
				break;

			case 'delete-theme':
				mn.updates.deleteTheme( job.data );
				break;

			default:
				break;
		}
	};

	/**
	 * Requests the users filesystem credentials if they aren't already known.
	 *
	 * @since 4.2.0
	 *
	 * @param {Event=} event Optional. Event interface.
	 */
	mn.updates.requestFilesystemCredentials = function( event ) {
		if ( false === mn.updates.filesystemCredentials.available ) {
			/*
			 * After exiting the credentials request modal,
			 * return the focus to the element triggering the request.
			 */
			if ( event && ! mn.updates.$elToReturnFocusToFromCredentialsModal ) {
				mn.updates.$elToReturnFocusToFromCredentialsModal = $( event.target );
			}

			mn.updates.ajaxLocked = true;
			mn.updates.requestForCredentialsModalOpen();
		}
	};

	/**
	 * Requests the users filesystem credentials if needed and there is no lock.
	 *
	 * @since 4.6.0
	 *
	 * @param {Event=} event Optional. Event interface.
	 */
	mn.updates.maybeRequestFilesystemCredentials = function( event ) {
		if ( mn.updates.shouldRequestFilesystemCredentials && ! mn.updates.ajaxLocked ) {
			mn.updates.requestFilesystemCredentials( event );
		}
	};

	/**
	 * Keydown handler for the request for credentials modal.
	 *
	 * Closes the modal when the escape key is pressed and
	 * constrains keyboard navigation to inside the modal.
	 *
	 * @since 4.2.0
	 *
	 * @param {Event} event Event interface.
	 */
	mn.updates.keydown = function( event ) {
		if ( 27 === event.keyCode ) {
			mn.updates.requestForCredentialsModalCancel();
		} else if ( 9 === event.keyCode ) {

			// #upgrade button must always be the last focus-able element in the dialog.
			if ( 'upgrade' === event.target.id && ! event.shiftKey ) {
				$( '#hostname' ).focus();

				event.preventDefault();
			} else if ( 'hostname' === event.target.id && event.shiftKey ) {
				$( '#upgrade' ).focus();

				event.preventDefault();
			}
		}
	};

	/**
	 * Opens the request for credentials modal.
	 *
	 * @since 4.2.0
	 */
	mn.updates.requestForCredentialsModalOpen = function() {
		var $modal = $( '#request-filesystem-credentials-dialog' );

		$( 'body' ).addClass( 'modal-open' );
		$modal.show();
		$modal.find( 'input:enabled:first' ).focus();
		$modal.on( 'keydown', mn.updates.keydown );
	};

	/**
	 * Closes the request for credentials modal.
	 *
	 * @since 4.2.0
	 */
	mn.updates.requestForCredentialsModalClose = function() {
		$( '#request-filesystem-credentials-dialog' ).hide();
		$( 'body' ).removeClass( 'modal-open' );

		if ( mn.updates.$elToReturnFocusToFromCredentialsModal ) {
			mn.updates.$elToReturnFocusToFromCredentialsModal.focus();
		}
	};

	/**
	 * Takes care of the steps that need to happen when the modal is canceled out.
	 *
	 * @since 4.2.0
	 * @since 4.6.0 Triggers an event for callbacks to listen to and add their actions.
	 */
	mn.updates.requestForCredentialsModalCancel = function() {

		// Not ajaxLocked and no queue means we already have cleared things up.
		if ( ! mn.updates.ajaxLocked && ! mn.updates.queue.length ) {
			return;
		}

		_.each( mn.updates.queue, function( job ) {
			$document.trigger( 'credential-modal-cancel', job );
		} );

		// Remove the lock, and clear the queue.
		mn.updates.ajaxLocked = false;
		mn.updates.queue = [];

		mn.updates.requestForCredentialsModalClose();
	};

	/**
	 * Displays an error message in the request for credentials form.
	 *
	 * @since 4.2.0
	 *
	 * @param {string} message Error message.
	 */
	mn.updates.showErrorInCredentialsForm = function( message ) {
		var $modal = $( '#request-filesystem-credentials-form' );

		// Remove any existing error.
		$modal.find( '.notice' ).remove();
		$modal.find( '#request-filesystem-credentials-title' ).after( '<div class="notice notice-alt notice-error"><p>' + message + '</p></div>' );
	};

	/**
	 * Handles credential errors and runs events that need to happen in that case.
	 *
	 * @since 4.2.0
	 *
	 * @param {object} response Ajax response.
	 * @param {string} action   The type of request to perform.
	 */
	mn.updates.credentialError = function( response, action ) {

		// Restore callbacks.
		response = mn.updates._addCallbacks( response, action );

		mn.updates.queue.unshift( {
			action: action,

			/*
			 * Not cool that we're depending on response for this data.
			 * This would feel more whole in a view all tied together.
			 */
			data: response
		} );

		mn.updates.filesystemCredentials.available = false;
		mn.updates.showErrorInCredentialsForm( response.errorMessage );
		mn.updates.requestFilesystemCredentials();
	};

	/**
	 * Handles credentials errors if it could not connect to the filesystem.
	 *
	 * @since 4.6.0
	 *
	 * @typedef {object} maybeHandleCredentialError
	 * @param {object} response              Response from the server.
	 * @param {string} response.errorCode    Error code for the error that occurred.
	 * @param {string} response.errorMessage The error that occurred.
	 * @param {string} action                The type of request to perform.
	 * @returns {boolean} Whether there is an error that needs to be handled or not.
	 */
	mn.updates.maybeHandleCredentialError = function( response, action ) {
		if ( mn.updates.shouldRequestFilesystemCredentials && response.errorCode && 'unable_to_connect_to_filesystem' === response.errorCode ) {
			mn.updates.credentialError( response, action );
			return true;
		}

		return false;
	};

	/**
	 * Validates an AJAX response to ensure it's a proper object.
	 *
	 * If the response deems to be invalid, an admin notice is being displayed.
	 *
	 * @param {(object|string)} response              Response from the server.
	 * @param {function=}       response.always       Optional. Callback for when the Deferred is resolved or rejected.
	 * @param {string=}         response.statusText   Optional. Status message corresponding to the status code.
	 * @param {string=}         response.responseText Optional. Request response as text.
	 * @param {string}          action                Type of action the response is referring to. Can be 'delete',
	 *                                                'update' or 'install'.
	 */
	mn.updates.isValidResponse = function( response, action ) {
		var error = mn.updates.l10n.unknownError,
		    errorMessage;

		// Make sure the response is a valid data object and not a Promise object.
		if ( _.isObject( response ) && ! _.isFunction( response.always ) ) {
			return true;
		}

		if ( _.isString( response ) && '-1' === response ) {
			error = mn.updates.l10n.nonceError;
		} else if ( _.isString( response ) ) {
			error = response;
		} else if ( 'undefined' !== typeof response.readyState && 0 === response.readyState ) {
			error = mn.updates.l10n.connectionError;
		} else if ( _.isString( response.responseText ) && '' !== response.responseText ) {
			error = response.responseText;
		} else if ( _.isString( response.statusText ) ) {
			error = response.statusText;
		}

		switch ( action ) {
			case 'update':
				errorMessage = mn.updates.l10n.updateFailed;
				break;

			case 'install':
				errorMessage = mn.updates.l10n.installFailed;
				break;

			case 'delete':
				errorMessage = mn.updates.l10n.deleteFailed;
				break;
		}

		// Messages are escaped, remove HTML tags to make them more readable.
		error = error.replace( /<[\/a-z][^<>]*>/gi, '' );
		errorMessage = errorMessage.replace( '%s', error );

		// Add admin notice.
		mn.updates.addAdminNotice( {
			id:        'unknown_error',
			className: 'notice-error is-dismissible',
			message:   _.escape( errorMessage )
		} );

		// Remove the lock, and clear the queue.
		mn.updates.ajaxLocked = false;
		mn.updates.queue      = [];

		// Change buttons of all running updates.
		$( '.button.updating-message' )
			.removeClass( 'updating-message' )
			.removeAttr( 'aria-label' )
			.prop( 'disabled', true )
			.text( mn.updates.l10n.updateFailedShort );

		$( '.updating-message:not(.button):not(.thickbox)' )
			.removeClass( 'updating-message notice-warning' )
			.addClass( 'notice-error' )
			.find( 'p' )
				.removeAttr( 'aria-label' )
				.text( errorMessage );

		mn.a11y.speak( errorMessage, 'assertive' );

		return false;
	};

	/**
	 * Potentially adds an AYS to a user attempting to leave the page.
	 *
	 * If an update is on-going and a user attempts to leave the page,
	 * opens an "Are you sure?" alert.
	 *
	 * @since 4.2.0
	 */
	mn.updates.beforeunload = function() {
		if ( mn.updates.ajaxLocked ) {
			return mn.updates.l10n.beforeunload;
		}
	};

	$( function() {
		var $pluginFilter        = $( '#plugin-filter' ),
			$bulkActionForm      = $( '#bulk-action-form' ),
			$filesystemModal     = $( '#request-filesystem-credentials-dialog' ),
			$pluginSearch        = $( '.plugins-php .mn-filter-search' ),
			$pluginInstallSearch = $( '.plugin-install-php .mn-filter-search' );

		settings = _.extend( settings, window._mnUpdatesItemCounts || {} );

		if ( settings.totals ) {
			mn.updates.refreshCount();
		}

		/*
		 * Whether a user needs to submit filesystem credentials.
		 *
		 * This is based on whether the form was output on the page server-side.
		 *
		 * @see {mn_print_request_filesystem_credentials_modal() in PHP}
		 */
		mn.updates.shouldRequestFilesystemCredentials = $filesystemModal.length > 0;

		/**
		 * File system credentials form submit noop-er / handler.
		 *
		 * @since 4.2.0
		 */
		$filesystemModal.on( 'submit', 'form', function( event ) {
			event.preventDefault();

			// Persist the credentials input by the user for the duration of the page load.
			mn.updates.filesystemCredentials.ftp.hostname       = $( '#hostname' ).val();
			mn.updates.filesystemCredentials.ftp.username       = $( '#username' ).val();
			mn.updates.filesystemCredentials.ftp.password       = $( '#password' ).val();
			mn.updates.filesystemCredentials.ftp.connectionType = $( 'input[name="connection_type"]:checked' ).val();
			mn.updates.filesystemCredentials.ssh.publicKey      = $( '#public_key' ).val();
			mn.updates.filesystemCredentials.ssh.privateKey     = $( '#private_key' ).val();
			mn.updates.filesystemCredentials.available          = true;

			// Unlock and invoke the queue.
			mn.updates.ajaxLocked = false;
			mn.updates.queueChecker();

			mn.updates.requestForCredentialsModalClose();
		} );

		/**
		 * Closes the request credentials modal when clicking the 'Cancel' button or outside of the modal.
		 *
		 * @since 4.2.0
		 */
		$filesystemModal.on( 'click', '[data-js-action="close"], .notification-dialog-background', mn.updates.requestForCredentialsModalCancel );

		/**
		 * Hide SSH fields when not selected.
		 *
		 * @since 4.2.0
		 */
		$filesystemModal.on( 'change', 'input[name="connection_type"]', function() {
			$( '#ssh-keys' ).toggleClass( 'hidden', ( 'ssh' !== $( this ).val() ) );
		} ).change();

		/**
		 * Handles events after the credential modal was closed.
		 *
		 * @since 4.6.0
		 *
		 * @param {Event}  event Event interface.
		 * @param {string} job   The install/update.delete request.
		 */
		$document.on( 'credential-modal-cancel', function( event, job ) {
			var $updatingMessage = $( '.updating-message' ),
				$message, originalText;

			if ( 'import' === pagenow ) {
				$updatingMessage.removeClass( 'updating-message' );
			} else if ( 'plugins' === pagenow || 'plugins-network' === pagenow ) {
				if ( 'update-plugin' === job.action ) {
					$message = $( 'tr[data-plugin="' + job.data.plugin + '"]' ).find( '.update-message' );
				} else if ( 'delete-plugin' === job.action ) {
					$message = $( '[data-plugin="' + job.data.plugin + '"]' ).find( '.row-actions a.delete' );
				}
			} else if ( 'themes' === pagenow || 'themes-network' === pagenow ) {
				if ( 'update-theme' === job.action ) {
					$message = $( '[data-slug="' + job.data.slug + '"]' ).find( '.update-message' );
				} else if ( 'delete-theme' === job.action && 'themes-network' === pagenow ) {
					$message = $( '[data-slug="' + job.data.slug + '"]' ).find( '.row-actions a.delete' );
				} else if ( 'delete-theme' === job.action && 'themes' === pagenow ) {
					$message = $( '.theme-actions .delete-theme' );
				}
			} else {
				$message = $updatingMessage;
			}

			if ( $message && $message.hasClass( 'updating-message' ) ) {
				originalText = $message.data( 'originaltext' );

				if ( 'undefined' === typeof originalText ) {
					originalText = $( '<p>' ).html( $message.find( 'p' ).data( 'originaltext' ) );
				}

				$message
					.removeClass( 'updating-message' )
					.html( originalText );

				if ( 'plugin-install' === pagenow || 'plugin-install-network' === pagenow ) {
					if ( 'update-plugin' === job.action ) {
						$message.attr( 'aria-label', mn.updates.l10n.updateNowLabel.replace( '%s', $message.data( 'name' ) ) );
					} else if ( 'install-plugin' === job.action ) {
						$message.attr( 'aria-label', mn.updates.l10n.installNowLabel.replace( '%s', $message.data( 'name' ) ) );
					}
				}
			}

			mn.a11y.speak( mn.updates.l10n.updateCancel, 'polite' );
		} );

		/**
		 * Click handler for plugin updates in List Table view.
		 *
		 * @since 4.2.0
		 *
		 * @param {Event} event Event interface.
		 */
		$bulkActionForm.on( 'click', '[data-plugin] .update-link', function( event ) {
			var $message   = $( event.target ),
				$pluginRow = $message.parents( 'tr' );

			event.preventDefault();

			if ( $message.hasClass( 'updating-message' ) || $message.hasClass( 'button-disabled' ) ) {
				return;
			}

			mn.updates.maybeRequestFilesystemCredentials( event );

			// Return the user to the input box of the plugin's table row after closing the modal.
			mn.updates.$elToReturnFocusToFromCredentialsModal = $pluginRow.find( '.check-column input' );
			mn.updates.updatePlugin( {
				plugin: $pluginRow.data( 'plugin' ),
				slug:   $pluginRow.data( 'slug' )
			} );
		} );

		/**
		 * Click handler for plugin updates in plugin install view.
		 *
		 * @since 4.2.0
		 *
		 * @param {Event} event Event interface.
		 */
		$pluginFilter.on( 'click', '.update-now', function( event ) {
			var $button = $( event.target );
			event.preventDefault();

			if ( $button.hasClass( 'updating-message' ) || $button.hasClass( 'button-disabled' ) ) {
				return;
			}

			mn.updates.maybeRequestFilesystemCredentials( event );

			mn.updates.updatePlugin( {
				plugin: $button.data( 'plugin' ),
				slug:   $button.data( 'slug' )
			} );
		} );

		/**
		 * Click handler for plugin installs in plugin install view.
		 *
		 * @since 4.6.0
		 *
		 * @param {Event} event Event interface.
		 */
		$pluginFilter.on( 'click', '.install-now', function( event ) {
			var $button = $( event.target );
			event.preventDefault();

			if ( $button.hasClass( 'updating-message' ) || $button.hasClass( 'button-disabled' ) ) {
				return;
			}

			if ( mn.updates.shouldRequestFilesystemCredentials && ! mn.updates.ajaxLocked ) {
				mn.updates.requestFilesystemCredentials( event );

				$document.on( 'credential-modal-cancel', function() {
					var $message = $( '.install-now.updating-message' );

					$message
						.removeClass( 'updating-message' )
						.text( mn.updates.l10n.installNow );

					mn.a11y.speak( mn.updates.l10n.updateCancel, 'polite' );
				} );
			}

			mn.updates.installPlugin( {
				slug: $button.data( 'slug' )
			} );
		} );

		/**
		 * Click handler for importer plugins installs in the Import screen.
		 *
		 * @since 4.6.0
		 *
		 * @param {Event} event Event interface.
		 */
		$document.on( 'click', '.importer-item .install-now', function( event ) {
			var $button = $( event.target ),
				pluginName = $( this ).data( 'name' );

			event.preventDefault();

			if ( $button.hasClass( 'updating-message' ) ) {
				return;
			}

			if ( mn.updates.shouldRequestFilesystemCredentials && ! mn.updates.ajaxLocked ) {
				mn.updates.requestFilesystemCredentials( event );

				$document.on( 'credential-modal-cancel', function() {

					$button
						.removeClass( 'updating-message' )
						.text( mn.updates.l10n.installNow )
						.attr( 'aria-label', mn.updates.l10n.installNowLabel.replace( '%s', pluginName ) );

					mn.a11y.speak( mn.updates.l10n.updateCancel, 'polite' );
				} );
			}

			mn.updates.installPlugin( {
				slug:    $button.data( 'slug' ),
				pagenow: pagenow,
				success: mn.updates.installImporterSuccess,
				error:   mn.updates.installImporterError
			} );
		} );

		/**
		 * Click handler for plugin deletions.
		 *
		 * @since 4.6.0
		 *
		 * @param {Event} event Event interface.
		 */
		$bulkActionForm.on( 'click', '[data-plugin] a.delete', function( event ) {
			var $pluginRow = $( event.target ).parents( 'tr' );

			event.preventDefault();

			if ( ! window.confirm( mn.updates.l10n.aysDeleteUninstall.replace( '%s', $pluginRow.find( '.plugin-title strong' ).text() ) ) ) {
				return;
			}

			mn.updates.maybeRequestFilesystemCredentials( event );

			mn.updates.deletePlugin( {
				plugin: $pluginRow.data( 'plugin' ),
				slug:   $pluginRow.data( 'slug' )
			} );

		} );

		/**
		 * Click handler for theme updates.
		 *
		 * @since 4.6.0
		 *
		 * @param {Event} event Event interface.
		 */
		$document.on( 'click', '.themes-php.network-admin .update-link', function( event ) {
			var $message  = $( event.target ),
				$themeRow = $message.parents( 'tr' );

			event.preventDefault();

			if ( $message.hasClass( 'updating-message' ) || $message.hasClass( 'button-disabled' ) ) {
				return;
			}

			mn.updates.maybeRequestFilesystemCredentials( event );

			// Return the user to the input box of the theme's table row after closing the modal.
			mn.updates.$elToReturnFocusToFromCredentialsModal = $themeRow.find( '.check-column input' );
			mn.updates.updateTheme( {
				slug: $themeRow.data( 'slug' )
			} );
		} );

		/**
		 * Click handler for theme deletions.
		 *
		 * @since 4.6.0
		 *
		 * @param {Event} event Event interface.
		 */
		$document.on( 'click', '.themes-php.network-admin a.delete', function( event ) {
			var $themeRow = $( event.target ).parents( 'tr' );

			event.preventDefault();

			if ( ! window.confirm( mn.updates.l10n.aysDelete.replace( '%s', $themeRow.find( '.theme-title strong' ).text() ) ) ) {
				return;
			}

			mn.updates.maybeRequestFilesystemCredentials( event );

			mn.updates.deleteTheme( {
				slug: $themeRow.data( 'slug' )
			} );
		} );

		/**
		 * Bulk action handler for plugins and themes.
		 *
		 * Handles both deletions and updates.
		 *
		 * @since 4.6.0
		 *
		 * @param {Event} event Event interface.
		 */
		$bulkActionForm.on( 'click', '[type="submit"]', function( event ) {
			var bulkAction    = $( event.target ).siblings( 'select' ).val(),
				itemsSelected = $bulkActionForm.find( 'input[name="checked[]"]:checked' ),
				success       = 0,
				error         = 0,
				errorMessages = [],
				type, action;

			// Determine which type of item we're dealing with.
			switch ( pagenow ) {
				case 'plugins':
				case 'plugins-network':
					type = 'plugin';
					break;

				case 'themes-network':
					type = 'theme';
					break;

				default:
					return;
			}

			// Bail if there were no items selected.
			if ( ! itemsSelected.length ) {
				event.preventDefault();
				$( 'html, body' ).animate( { scrollTop: 0 } );

				return mn.updates.addAdminNotice( {
					id:        'no-items-selected',
					className: 'notice-error is-dismissible',
					message:   mn.updates.l10n.noItemsSelected
				} );
			}

			// Determine the type of request we're dealing with.
			switch ( bulkAction ) {
				case 'update-selected':
					action = bulkAction.replace( 'selected', type );
					break;

				case 'delete-selected':
					if ( ! window.confirm( 'plugin' === type ? mn.updates.l10n.aysBulkDelete : mn.updates.l10n.aysBulkDeleteThemes ) ) {
						event.preventDefault();
						return;
					}

					action = bulkAction.replace( 'selected', type );
					break;

				default:
					return;
			}

			mn.updates.maybeRequestFilesystemCredentials( event );

			event.preventDefault();

			// Un-check the bulk checkboxes.
			$bulkActionForm.find( '.manage-column [type="checkbox"]' ).prop( 'checked', false );

			$document.trigger( 'mn-' + type + '-bulk-' + bulkAction, itemsSelected );

			// Find all the checkboxes which have been checked.
			itemsSelected.each( function( index, element ) {
				var $checkbox = $( element ),
					$itemRow = $checkbox.parents( 'tr' );

				// Only add update-able items to the update queue.
				if ( 'update-selected' === bulkAction && ( ! $itemRow.hasClass( 'update' ) || $itemRow.find( 'notice-error' ).length ) ) {

					// Un-check the box.
					$checkbox.prop( 'checked', false );
					return;
				}

				// Add it to the queue.
				mn.updates.queue.push( {
					action: action,
					data:   {
						plugin: $itemRow.data( 'plugin' ),
						slug:   $itemRow.data( 'slug' )
					}
				} );
			} );

			// Display bulk notification for updates of any kind.
			$document.on( 'mn-plugin-update-success mn-plugin-update-error mn-theme-update-success mn-theme-update-error', function( event, response ) {
				var $itemRow = $( '[data-slug="' + response.slug + '"]' ),
					$bulkActionNotice, itemName;

				if ( 'mn-' + response.update + '-update-success' === event.type ) {
					success++;
				} else {
					itemName = response.pluginName ? response.pluginName : $itemRow.find( '.column-primary strong' ).text();

					error++;
					errorMessages.push( itemName + ': ' + response.errorMessage );
				}

				$itemRow.find( 'input[name="checked[]"]:checked' ).prop( 'checked', false );

				mn.updates.adminNotice = mn.template( 'mn-bulk-updates-admin-notice' );

				mn.updates.addAdminNotice( {
					id:            'bulk-action-notice',
					className:     'bulk-action-notice',
					successes:     success,
					errors:        error,
					errorMessages: errorMessages,
					type:          response.update
				} );

				$bulkActionNotice = $( '#bulk-action-notice' ).on( 'click', 'button', function() {
					// $( this ) is the clicked button, no need to get it again.
					$( this )
						.toggleClass( 'bulk-action-errors-collapsed' )
						.attr( 'aria-expanded', ! $( this ).hasClass( 'bulk-action-errors-collapsed' ) );
					// Show the errors list.
					$bulkActionNotice.find( '.bulk-action-errors' ).toggleClass( 'hidden' );
				} );

				if ( error > 0 && ! mn.updates.queue.length ) {
					$( 'html, body' ).animate( { scrollTop: 0 } );
				}
			} );

			// Reset admin notice template after #bulk-action-notice was added.
			$document.on( 'mn-updates-notice-added', function() {
				mn.updates.adminNotice = mn.template( 'mn-updates-admin-notice' );
			} );

			// Check the queue, now that the event handlers have been added.
			mn.updates.queueChecker();
		} );

		if ( $pluginInstallSearch.length ) {
			$pluginInstallSearch.attr( 'aria-describedby', 'live-search-desc' );
		}

		/**
		 * Handles changes to the plugin search box on the new-plugin page,
		 * searching the repository dynamically.
		 *
		 * @since 4.6.0
		 */
		$pluginInstallSearch.on( 'keyup input', _.debounce( function( event, eventtype ) {
			var $searchTab = $( '.plugin-install-search' ), data, searchLocation;

			data = {
				_ajax_nonce: mn.updates.ajaxNonce,
				s:           event.target.value,
				tab:         'search',
				type:        $( '#typeselector' ).val(),
				pagenow:     pagenow
			};
			searchLocation = location.href.split( '?' )[ 0 ] + '?' + $.param( _.omit( data, [ '_ajax_nonce', 'pagenow' ] ) );

			// Clear on escape.
			if ( 'keyup' === event.type && 27 === event.which ) {
				event.target.value = '';
			}

			if ( mn.updates.searchTerm === data.s && 'typechange' !== eventtype ) {
				return;
			} else {
				$pluginFilter.empty();
				mn.updates.searchTerm = data.s;
			}

			if ( window.history && window.history.replaceState ) {
				window.history.replaceState( null, '', searchLocation );
			}

			if ( ! $searchTab.length ) {
				$searchTab = $( '<li class="plugin-install-search" />' )
					.append( $( '<a />', {
						'class': 'current',
						'href': searchLocation,
						'text': mn.updates.l10n.searchResultsLabel
					} ) );

				$( '.mn-filter .filter-links .current' )
					.removeClass( 'current' )
					.parents( '.filter-links' )
					.prepend( $searchTab );

				$pluginFilter.prev( 'p' ).remove();
				$( '.plugins-popular-tags-wrapper' ).remove();
			}

			if ( 'undefined' !== typeof mn.updates.searchRequest ) {
				mn.updates.searchRequest.abort();
			}
			$( 'body' ).addClass( 'loading-content' );

			mn.updates.searchRequest = mn.ajax.post( 'search-install-plugins', data ).done( function( response ) {
				$( 'body' ).removeClass( 'loading-content' );
				$pluginFilter.append( response.items );
				delete mn.updates.searchRequest;

				if ( 0 === response.count ) {
					mn.a11y.speak( mn.updates.l10n.noPluginsFound );
				} else {
					mn.a11y.speak( mn.updates.l10n.pluginsFound.replace( '%d', response.count ) );
				}
			} );
		}, 500 ) );

		if ( $pluginSearch.length ) {
			$pluginSearch.attr( 'aria-describedby', 'live-search-desc' );
		}

		/**
		 * Handles changes to the plugin search box on the Installed Plugins screen,
		 * searching the plugin list dynamically.
		 *
		 * @since 4.6.0
		 */
		$pluginSearch.on( 'keyup input', _.debounce( function( event ) {
			var data = {
				_ajax_nonce:   mn.updates.ajaxNonce,
				s:             event.target.value,
				pagenow:       pagenow,
				plugin_status: 'all'
			},
			queryArgs;

			// Clear on escape.
			if ( 'keyup' === event.type && 27 === event.which ) {
				event.target.value = '';
			}

			if ( mn.updates.searchTerm === data.s ) {
				return;
			} else {
				mn.updates.searchTerm = data.s;
			}

			queryArgs = _.object( _.compact( _.map( location.search.slice( 1 ).split( '&' ), function( item ) {
				if ( item ) return item.split( '=' );
			} ) ) );

			data.plugin_status = queryArgs.plugin_status || 'all';

			if ( window.history && window.history.replaceState ) {
				window.history.replaceState( null, '', location.href.split( '?' )[ 0 ] + '?s=' + data.s + '&plugin_status=' + data.plugin_status );
			}

			if ( 'undefined' !== typeof mn.updates.searchRequest ) {
				mn.updates.searchRequest.abort();
			}

			$bulkActionForm.empty();
			$( 'body' ).addClass( 'loading-content' );
			$( '.subsubsub .current' ).removeClass( 'current' );

			mn.updates.searchRequest = mn.ajax.post( 'search-plugins', data ).done( function( response ) {

				// Can we just ditch this whole subtitle business?
				var $subTitle    = $( '<span />' ).addClass( 'subtitle' ).html( mn.updates.l10n.searchResults.replace( '%s', _.escape( data.s ) ) ),
					$oldSubTitle = $( '.wrap .subtitle' );

				if ( ! data.s.length ) {
					$oldSubTitle.remove();
					$( '.subsubsub .' + data.plugin_status + ' a' ).addClass( 'current' );
				} else if ( $oldSubTitle.length ) {
					$oldSubTitle.replaceWith( $subTitle );
				} else {
					$( '.wrap h1' ).append( $subTitle );
				}

				$( 'body' ).removeClass( 'loading-content' );
				$bulkActionForm.append( response.items );
				delete mn.updates.searchRequest;

				if ( 0 === response.count ) {
					mn.a11y.speak( mn.updates.l10n.noPluginsFound );
				} else {
					mn.a11y.speak( mn.updates.l10n.pluginsFound.replace( '%d', response.count ) );
				}
			} );
		}, 500 ) );

		/**
		 * Trigger a search event when the search form gets submitted.
		 *
		 * @since 4.6.0
		 */
		$document.on( 'submit', '.search-plugins', function( event ) {
			event.preventDefault();

			$( 'input.mn-filter-search' ).trigger( 'input' );
		} );

		/**
		 * Trigger a search event when the search type gets changed.
		 *
		 * @since 4.6.0
		 */
		$( '#typeselector' ).on( 'change', function() {
			var $search = $( 'input[name="s"]' );

			if ( $search.val().length ) {
				$search.trigger( 'input', 'typechange' );
			}
		} );

		/**
		 * Click handler for updating a plugin from the details modal on `plugin-install.php`.
		 *
		 * @since 4.2.0
		 *
		 * @param {Event} event Event interface.
		 */
		$( '#plugin_update_from_iframe' ).on( 'click', function( event ) {
			var target = window.parent === window ? null : window.parent,
				update;

			$.support.postMessage = !! window.postMessage;

			if ( false === $.support.postMessage || null === target || -1 !== window.parent.location.pathname.indexOf( 'update-core.php' ) ) {
				return;
			}

			event.preventDefault();

			update = {
				action: 'update-plugin',
				data:   {
					plugin: $( this ).data( 'plugin' ),
					slug:   $( this ).data( 'slug' )
				}
			};

			target.postMessage( JSON.stringify( update ), window.location.origin );
		} );

		/**
		 * Click handler for installing a plugin from the details modal on `plugin-install.php`.
		 *
		 * @since 4.6.0
		 *
		 * @param {Event} event Event interface.
		 */
		$( '#plugin_install_from_iframe' ).on( 'click', function( event ) {
			var target = window.parent === window ? null : window.parent,
				install;

			$.support.postMessage = !! window.postMessage;

			if ( false === $.support.postMessage || null === target || -1 !== window.parent.location.pathname.indexOf( 'index.php' ) ) {
				return;
			}

			event.preventDefault();

			install = {
				action: 'install-plugin',
				data:   {
					slug: $( this ).data( 'slug' )
				}
			};

			target.postMessage( JSON.stringify( install ), window.location.origin );
		} );

		/**
		 * Handles postMessage events.
		 *
		 * @since 4.2.0
		 * @since 4.6.0 Switched `update-plugin` action to use the queue.
		 *
		 * @param {Event} event Event interface.
		 */
		$( window ).on( 'message', function( event ) {
			var originalEvent  = event.originalEvent,
				expectedOrigin = document.location.protocol + '//' + document.location.hostname,
				message;

			if ( originalEvent.origin !== expectedOrigin ) {
				return;
			}

			try {
				message = $.parseJSON( originalEvent.data );
			} catch ( e ) {
				return;
			}

			if ( 'undefined' === typeof message.action ) {
				return;
			}

			switch ( message.action ) {

				// Called from `admin/includes/class-mn-upgrader-skins.php`.
				case 'decrementUpdateCount':
					/** @property {string} message.upgradeType */
					mn.updates.decrementCount( message.upgradeType );
					break;

				case 'install-plugin':
				case 'update-plugin':
					/* jscs:disable requireCamelCaseOrUpperCaseIdentifiers */
					window.tb_remove();
					/* jscs:enable */

					message.data = mn.updates._addCallbacks( message.data, message.action );

					mn.updates.queue.push( message );
					mn.updates.queueChecker();
					break;
			}
		} );

		/**
		 * Adds a callback to display a warning before leaving the page.
		 *
		 * @since 4.2.0
		 */
		$( window ).on( 'beforeunload', mn.updates.beforeunload );
	} );
})( jQuery, window.mn, window._mnUpdatesSettings );
