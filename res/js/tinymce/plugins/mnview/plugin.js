/**
 * Mtaandao View plugin.
 */
( function( tinymce, mn ) {
	tinymce.PluginManager.add( 'mnview', function( editor ) {
		function noop () {}

		if ( ! mn || ! mn.mce || ! mn.mce.views ) {
			return {
				getView: noop
			};
		}

		// Check if a node is a view or not.
		function isView( node ) {
			return editor.dom.hasClass( node, 'mnview' );
		}

		// Replace view tags with their text.
		function resetViews( content ) {
			function callback( match, $1 ) {
				return '<p>' + window.decodeURIComponent( $1 ) + '</p>';
			}

			if ( ! content ) {
				return content;
			}

			return content
				.replace( /<div[^>]+data-mnview-text="([^"]+)"[^>]*>(?:\.|[\s\S]+?mnview-end[^>]+>\s*<\/span>\s*)?<\/div>/g, callback )
				.replace( /<p[^>]+data-mnview-marker="([^"]+)"[^>]*>[\s\S]*?<\/p>/g, callback );
		}

		editor.on( 'init', function() {
			var MutationObserver = window.MutationObserver || window.WebKitMutationObserver;

			if ( MutationObserver ) {
				new MutationObserver( function() {
					editor.fire( 'mn-body-class-change' );
				} )
				.observe( editor.getBody(), {
					attributes: true,
					attributeFilter: ['class']
				} );
			}

			// Pass on body class name changes from the editor to the mnView iframes.
			editor.on( 'mn-body-class-change', function() {
				var className = editor.getBody().className;

				editor.$( 'iframe[class="mnview-sandbox"]' ).each( function( i, iframe ) {
					// Make sure it is a local iframe
					// jshint scripturl: true
					if ( ! iframe.src || iframe.src === 'javascript:""' ) {
						try {
							iframe.contentWindow.document.body.className = className;
						} catch( er ) {}
					}
				});
			} );
		});

		// Scan new content for matching view patterns and replace them with markers.
		editor.on( 'beforesetcontent', function( event ) {
			var node;

			if ( ! event.selection ) {
				mn.mce.views.unbind();
			}

			if ( ! event.content ) {
				return;
			}

			if ( ! event.load ) {
				node = editor.selection.getNode();

				if ( node && node !== editor.getBody() && /^\s*https?:\/\/\S+\s*$/i.test( event.content ) ) {
					// When a url is pasted or inserted, only try to embed it when it is in an empty paragrapgh.
					node = editor.dom.getParent( node, 'p' );

					if ( node && /^[\s\uFEFF\u00A0]*$/.test( editor.$( node ).text() || '' ) ) {
						// Make sure there are no empty inline elements in the <p>
						node.innerHTML = '';
					} else {
						return;
					}
				}
			}

			event.content = mn.mce.views.setMarkers( event.content );
		} );

		// Replace any new markers nodes with views.
		editor.on( 'setcontent', function( event ) {
			if ( event.load && ! event.initial && editor.quirks.refreshContentEditable ) {
				// Make sure there is a selection in Gecko browsers.
				// Or it will refresh the content internally which resets the iframes.
				editor.quirks.refreshContentEditable();
			}

			mn.mce.views.render();
		} );

		// Empty view nodes for easier processing.
		editor.on( 'preprocess hide', function( event ) {
			editor.$( 'div[data-mnview-text], p[data-mnview-marker]', event.node ).each( function( i, node ) {
				node.innerHTML = '.';
			} );
		}, true );

		// Replace views with their text.
		editor.on( 'postprocess', function( event ) {
			event.content = resetViews( event.content );
		} );

		// Replace views with their text inside undo levels.
		// This also prevents that new levels are added when there are changes inside the views.
		editor.on( 'beforeaddundo', function( event ) {
			event.level.content = resetViews( event.level.content );
		} );

		// Make sure views are copied as their text.
		editor.on( 'drop objectselected', function( event ) {
			if ( isView( event.targetClone ) ) {
				event.targetClone = editor.getDoc().createTextNode(
					window.decodeURIComponent( editor.dom.getAttrib( event.targetClone, 'data-mnview-text' ) )
				);
			}
		} );

		// Clean up URLs for easier processing.
		editor.on( 'pastepreprocess', function( event ) {
			var content = event.content;

			if ( content ) {
				content = tinymce.trim( content.replace( /<[^>]+>/g, '' ) );

				if ( /^https?:\/\/\S+$/i.test( content ) ) {
					event.content = content;
				}
			}
		} );

		// Show the view type in the element path.
		editor.on( 'resolvename', function( event ) {
			if ( isView( event.target ) ) {
				event.name = editor.dom.getAttrib( event.target, 'data-mnview-type' ) || 'object';
			}
		} );

		// See `media` plugin.
		editor.on( 'click keyup', function() {
			var node = editor.selection.getNode();

			if ( isView( node ) ) {
				if ( editor.dom.getAttrib( node, 'data-mce-selected' ) ) {
					node.setAttribute( 'data-mce-selected', '2' );
				}
			}
		} );

		editor.addButton( 'mn_view_edit', {
			tooltip: 'Edit ', // trailing space is needed, used for context
			icon: 'dashicon dashicons-edit',
			onclick: function() {
				var node = editor.selection.getNode();

				if ( isView( node ) ) {
					mn.mce.views.edit( editor, node );
				}
			}
		} );

		editor.addButton( 'mn_view_remove', {
			tooltip: 'Remove',
			icon: 'dashicon dashicons-no',
			onclick: function() {
				editor.fire( 'cut' );
			}
		} );

		editor.once( 'preinit', function() {
			var toolbar;

			if ( editor.mn && editor.mn._createToolbar ) {
				toolbar = editor.mn._createToolbar( [
					'mn_view_edit',
					'mn_view_remove'
				] );

				editor.on( 'mntoolbar', function( event ) {
					if ( isView( event.element ) ) {
						event.toolbar = toolbar;
					}
				} );
			}
		} );

		editor.mn = editor.mn || {};
		editor.mn.getView = noop;
		editor.mn.setViewCursor = noop;

		return {
			getView: noop
		};
	} );
} )( window.tinymce, window.mn );
