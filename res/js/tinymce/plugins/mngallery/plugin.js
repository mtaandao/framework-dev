/* global tinymce */
tinymce.PluginManager.add('mngallery', function( editor ) {

	function replaceGalleryShortcodes( content ) {
		return content.replace( /\[gallery([^\]]*)\]/g, function( match ) {
			return html( 'mn-gallery', match );
		});
	}

	function html( cls, data ) {
		data = window.encodeURIComponent( data );
		return '<img src="' + tinymce.Env.transparentSrc + '" class="mn-media mceItem ' + cls + '" ' +
			'data-mn-media="' + data + '" data-mce-resize="false" data-mce-placeholder="1" alt="" />';
	}

	function restoreMediaShortcodes( content ) {
		function getAttr( str, name ) {
			name = new RegExp( name + '=\"([^\"]+)\"' ).exec( str );
			return name ? window.decodeURIComponent( name[1] ) : '';
		}

		return content.replace( /(?:<p(?: [^>]+)?>)*(<img [^>]+>)(?:<\/p>)*/g, function( match, image ) {
			var data = getAttr( image, 'data-mn-media' );

			if ( data ) {
				return '<p>' + data + '</p>';
			}

			return match;
		});
	}

	function editMedia( node ) {
		var gallery, frame, data;

		if ( node.nodeName !== 'IMG' ) {
			return;
		}

		// Check if the `mn.media` API exists.
		if ( typeof mn === 'undefined' || ! mn.media ) {
			return;
		}

		data = window.decodeURIComponent( editor.dom.getAttrib( node, 'data-mn-media' ) );

		// Make sure we've selected a gallery node.
		if ( editor.dom.hasClass( node, 'mn-gallery' ) && mn.media.gallery ) {
			gallery = mn.media.gallery;
			frame = gallery.edit( data );

			frame.state('gallery-edit').on( 'update', function( selection ) {
				var shortcode = gallery.shortcode( selection ).string();
				editor.dom.setAttrib( node, 'data-mn-media', window.encodeURIComponent( shortcode ) );
				frame.detach();
			});
		}
	}

	// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('...');
	editor.addCommand( 'MN_Gallery', function() {
		editMedia( editor.selection.getNode() );
	});

	editor.on( 'mouseup', function( event ) {
		var dom = editor.dom,
			node = event.target;

		function unselect() {
			dom.removeClass( dom.select( 'img.mn-media-selected' ), 'mn-media-selected' );
		}

		if ( node.nodeName === 'IMG' && dom.getAttrib( node, 'data-mn-media' ) ) {
			// Don't trigger on right-click
			if ( event.button !== 2 ) {
				if ( dom.hasClass( node, 'mn-media-selected' ) ) {
					editMedia( node );
				} else {
					unselect();
					dom.addClass( node, 'mn-media-selected' );
				}
			}
		} else {
			unselect();
		}
	});

	// Display gallery, audio or video instead of img in the element path
	editor.on( 'ResolveName', function( event ) {
		var dom = editor.dom,
			node = event.target;

		if ( node.nodeName === 'IMG' && dom.getAttrib( node, 'data-mn-media' ) ) {
			if ( dom.hasClass( node, 'mn-gallery' ) ) {
				event.name = 'gallery';
			}
		}
	});

	editor.on( 'BeforeSetContent', function( event ) {
		// 'mnview' handles the gallery shortcode when present
		if ( ! editor.plugins.mnview || typeof mn === 'undefined' || ! mn.mce ) {
			event.content = replaceGalleryShortcodes( event.content );
		}
	});

	editor.on( 'PostProcess', function( event ) {
		if ( event.get ) {
			event.content = restoreMediaShortcodes( event.content );
		}
	});
});
