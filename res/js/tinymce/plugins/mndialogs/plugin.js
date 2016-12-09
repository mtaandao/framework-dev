/* global tinymce */
/**
 * Included for back-compat.
 * The default WindowManager in TinyMCE 4.0 supports three types of dialogs:
 *	- With HTML created from JS.
 *	- With inline HTML (like MNWindowManager).
 *	- Old type iframe based dialogs.
 * For examples see the default plugins: https://github.com/tinymce/tinymce/tree/master/js/tinymce/plugins
 */
tinymce.MNWindowManager = tinymce.InlineWindowManager = function( editor ) {
	if ( this.mn ) {
		return this;
	}

	this.mn = {};
	this.parent = editor.windowManager;
	this.editor = editor;

	tinymce.extend( this, this.parent );

	this.open = function( args, params ) {
		var $element,
			self = this,
			mn = this.mn;

		if ( ! args.mnDialog ) {
			return this.parent.open.apply( this, arguments );
		} else if ( ! args.id ) {
			return;
		}

		if ( typeof jQuery === 'undefined' || ! jQuery.mn || ! jQuery.mn.mndialog ) {
			// mndialog.js is not loaded
			if ( window.console && window.console.error ) {
				window.console.error('mndialog.js is not loaded. Please set "mndialogs" as dependency for your script when calling mn_enqueue_script(). You may also want to enqueue the "mn-jquery-ui-dialog" stylesheet.');
			}

			return;
		}

		mn.$element = $element = jQuery( '#' + args.id );

		if ( ! $element.length ) {
			return;
		}

		if ( window.console && window.console.log ) {
			window.console.log('tinymce.MNWindowManager is deprecated. Use the default editor.windowManager to open dialogs with inline HTML.');
		}

		mn.features = args;
		mn.params = params;

		// Store selection. Takes a snapshot in the FocusManager of the selection before focus is moved to the dialog.
		editor.nodeChanged();

		// Create the dialog if necessary
		if ( ! $element.data('mndialog') ) {
			$element.mndialog({
				title: args.title,
				width: args.width,
				height: args.height,
				modal: true,
				dialogClass: 'mn-dialog',
				zIndex: 300000
			});
		}

		$element.mndialog('open');

		$element.on( 'mndialogclose', function() {
			if ( self.mn.$element ) {
				self.mn = {};
			}
		});
	};

	this.close = function() {
		if ( ! this.mn.features || ! this.mn.features.mnDialog ) {
			return this.parent.close.apply( this, arguments );
		}

		this.mn.$element.mndialog('close');
	};
};

tinymce.PluginManager.add( 'mndialogs', function( editor ) {
	// Replace window manager
	editor.on( 'init', function() {
		editor.windowManager = new tinymce.MNWindowManager( editor );
	});
});
