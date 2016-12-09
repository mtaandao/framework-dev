( function( tinymce ) {
	tinymce.ui.MNLinkPreview = tinymce.ui.Control.extend( {
		url: '#',
		renderHtml: function() {
			return (
				'<div id="' + this._id + '" class="mn-link-preview">' +
					'<a href="' + this.url + '" target="_blank" tabindex="-1">' + this.url + '</a>' +
				'</div>'
			);
		},
		setURL: function( url ) {
			var index, lastIndex;

			if ( this.url !== url ) {
				this.url = url;

				url = window.decodeURIComponent( url );

				url = url.replace( /^(?:https?:)?\/\/(?:www\.)?/, '' );

				if ( ( index = url.indexOf( '?' ) ) !== -1 ) {
					url = url.slice( 0, index );
				}

				if ( ( index = url.indexOf( '#' ) ) !== -1 ) {
					url = url.slice( 0, index );
				}

				url = url.replace( /(?:index)?\.html$/, '' );

				if ( url.charAt( url.length - 1 ) === '/' ) {
					url = url.slice( 0, -1 );
				}

				// If nothing's left (maybe the URL was just a fragment), use the whole URL.
				if ( url === '' ) {
					url = this.url;
				}

				// If the URL is longer that 40 chars, concatenate the beginning (after the domain) and ending with ...
				if ( url.length > 40 && ( index = url.indexOf( '/' ) ) !== -1 && ( lastIndex = url.lastIndexOf( '/' ) ) !== -1 && lastIndex !== index ) {
					// If the beginning + ending are shorter that 40 chars, show more of the ending
					if ( index + url.length - lastIndex < 40 ) {
						lastIndex = -( 40 - ( index + 1 ) );
					}

					url = url.slice( 0, index + 1 ) + '\u2026' + url.slice( lastIndex );
				}

				tinymce.$( this.getEl().firstChild ).attr( 'href', this.url ).text( url );
			}
		}
	} );

	tinymce.ui.MNLinkInput = tinymce.ui.Control.extend( {
		renderHtml: function() {
			return (
				'<div id="' + this._id + '" class="mn-link-input">' +
					'<input type="text" value="" placeholder="' + tinymce.translate( 'Paste URL or type to search' ) + '" />' +
					'<input type="text" style="display:none" value="" />' +
				'</div>'
			);
		},
		setURL: function( url ) {
			this.getEl().firstChild.value = url;
		},
		getURL: function() {
			return tinymce.trim( this.getEl().firstChild.value );
		},
		getLinkText: function() {
			var text = this.getEl().firstChild.nextSibling.value;

			if ( ! tinymce.trim( text ) ) {
				return '';
			}

			return text.replace( /[\r\n\t ]+/g, ' ' );
		},
		reset: function() {
			var urlInput = this.getEl().firstChild;

			urlInput.value = '';
			urlInput.nextSibling.value = '';
		}
	} );

	tinymce.PluginManager.add( 'mnlink', function( editor ) {
		var toolbar;
		var editToolbar;
		var previewInstance;
		var inputInstance;
		var linkNode;
		var doingUndoRedo;
		var doingUndoRedoTimer;
		var $ = window.jQuery;
		var emailRegex = /^(mailto:)?[a-z0-9._%+-]+@[a-z0-9][a-z0-9.-]*\.[a-z]{2,63}$/i;
		var urlRegex1 = /^https?:\/\/([^\s/?.#-][^\s\/?.#]*\.?)+(\/[^\s"]*)?$/i;
		var urlRegex2 = /^https?:\/\/[^\/]+\.[^\/]+($|\/)/i;
		var speak = ( typeof window.mn !== 'undefined' && window.mn.a11y && window.mn.a11y.speak ) ? window.mn.a11y.speak : function() {};
		var hasLinkError = false;

		function getSelectedLink() {
			var href, html,
				node = editor.selection.getNode(),
				link = editor.dom.getParent( node, 'a[href]' );

			if ( ! link ) {
				html = editor.selection.getContent({ format: 'raw' });

				if ( html && html.indexOf( '</a>' ) !== -1 ) {
					href = html.match( /href="([^">]+)"/ );

					if ( href && href[1] ) {
						link = editor.$( 'a[href="' + href[1] + '"]', node )[0];
					}

					if ( link ) {
						editor.selection.select( link );
					}
				}
			}

			return link;
		}

		function removePlaceholders() {
			editor.$( 'a' ).each( function( i, element ) {
				var $element = editor.$( element );

				if ( $element.attr( 'href' ) === '_mn_link_placeholder' ) {
					editor.dom.remove( element, true );
				} else if ( $element.attr( 'data-mnlink-edit' ) ) {
					$element.attr( 'data-mnlink-edit', null );
				}
			});
		}

		function removePlaceholderStrings( content, dataAttr ) {
			return content.replace( /(<a [^>]+>)([\s\S]*?)<\/a>/g, function( all, tag, text ) {
				if ( tag.indexOf( ' href="_mn_link_placeholder"' ) > -1 ) {
					return text;
				}

				if ( dataAttr ) {
					tag = tag.replace( / data-mnlink-edit="true"/g, '' );
				}

				tag = tag.replace( / data-mnlink-url-error="true"/g, '' );

				return tag + text + '</a>';
			});
		}

		function checkLink( node ) {
			var $link = editor.$( node );
			var href = $link.attr( 'href' );

			if ( ! href || typeof $ === 'undefined' ) {
				return;
			}

			hasLinkError = false;

			if ( /^http/i.test( href ) && ( ! urlRegex1.test( href ) || ! urlRegex2.test( href ) ) ) {
				hasLinkError = true;
				$link.attr( 'data-mnlink-url-error', 'true' );
				speak( editor.translate( 'Warning: the link has been inserted but may have errors. Please test it.' ), 'assertive' );
			} else {
				$link.removeAttr( 'data-mnlink-url-error' );
			}
		}

		editor.on( 'preinit', function() {
			if ( editor.mn && editor.mn._createToolbar ) {
				toolbar = editor.mn._createToolbar( [
					'mn_link_preview',
					'mn_link_edit',
					'mn_link_remove'
				], true );

				var editButtons = [
					'mn_link_input',
					'mn_link_apply'
				];

				if ( typeof window.mnLink !== 'undefined' ) {
					editButtons.push( 'mn_link_advanced' );
				}

				editToolbar = editor.mn._createToolbar( editButtons, true );

				editToolbar.on( 'show', function() {
					if ( typeof window.mnLink === 'undefined' || ! window.mnLink.modalOpen ) {
						window.setTimeout( function() {
							var element = editToolbar.$el.find( 'input.ui-autocomplete-input' )[0],
								selection = linkNode && ( linkNode.textContent || linkNode.innerText );

							if ( element ) {
								if ( ! element.value && selection && typeof window.mnLink !== 'undefined' ) {
									element.value = window.mnLink.getUrlFromSelection( selection );
								}

								if ( ! doingUndoRedo ) {
									element.focus();
									element.select();
								}
							}
						} );
					}
				} );

				editToolbar.on( 'hide', function() {
					if ( ! editToolbar.scrolling ) {
						editor.execCommand( 'mn_link_cancel' );
					}
				} );
			}
		} );

		editor.addCommand( 'MN_Link', function() {
			if ( tinymce.Env.ie && tinymce.Env.ie < 10 && typeof window.mnLink !== 'undefined' ) {
				window.mnLink.open( editor.id );
				return;
			}

			linkNode = getSelectedLink();
			editToolbar.tempHide = false;

			if ( linkNode ) {
				editor.dom.setAttribs( linkNode, { 'data-mnlink-edit': true } );
			} else {
				removePlaceholders();
				editor.execCommand( 'mceInsertLink', false, { href: '_mn_link_placeholder' } );

				linkNode = editor.$( 'a[href="_mn_link_placeholder"]' )[0];
				editor.nodeChanged();
			}
		} );

		editor.addCommand( 'mn_link_apply', function() {
			if ( editToolbar.scrolling ) {
				return;
			}

			var href, text;

			if ( linkNode ) {
				href = inputInstance.getURL();
				text = inputInstance.getLinkText();
				editor.focus();

				if ( ! href ) {
					editor.dom.remove( linkNode, true );
					return;
				}

				if ( ! /^(?:[a-z]+:|#|\?|\.|\/)/.test( href ) && ! emailRegex.test( href ) ) {
					href = 'http://' + href;
				}

				editor.dom.setAttribs( linkNode, { href: href, 'data-mnlink-edit': null } );

				if ( ! tinymce.trim( linkNode.innerHTML ) ) {
					editor.$( linkNode ).text( text || href );
				}

				checkLink( linkNode );
			}

			inputInstance.reset();
			editor.nodeChanged();

			// Audible confirmation message when a link has been inserted in the Editor.
			if ( typeof window.mnLinkL10n !== 'undefined' && ! hasLinkError ) {
				speak( window.mnLinkL10n.linkInserted );
			}
		} );

		editor.addCommand( 'mn_link_cancel', function() {
			if ( ! editToolbar.tempHide ) {
				inputInstance.reset();
				removePlaceholders();
			}
		} );

		editor.addCommand( 'mn_unlink', function() {
			editor.execCommand( 'unlink' );
			editToolbar.tempHide = false;
			editor.execCommand( 'mn_link_cancel' );
		} );

		// MN default shortcuts
		editor.addShortcut( 'access+a', '', 'MN_Link' );
		editor.addShortcut( 'access+s', '', 'mn_unlink' );
		// The "de-facto standard" shortcut, see #27305
		editor.addShortcut( 'meta+k', '', 'MN_Link' );

		editor.addButton( 'link', {
			icon: 'link',
			tooltip: 'Insert/edit link',
			cmd: 'MN_Link',
			stateSelector: 'a[href]'
		});

		editor.addButton( 'unlink', {
			icon: 'unlink',
			tooltip: 'Remove link',
			cmd: 'unlink'
		});

		editor.addMenuItem( 'link', {
			icon: 'link',
			text: 'Insert/edit link',
			cmd: 'MN_Link',
			stateSelector: 'a[href]',
			context: 'insert',
			prependToContext: true
		});

		editor.on( 'pastepreprocess', function( event ) {
			var pastedStr = event.content,
				regExp = /^(?:https?:)?\/\/\S+$/i;

			if ( ! editor.selection.isCollapsed() && ! regExp.test( editor.selection.getContent() ) ) {
				pastedStr = pastedStr.replace( /<[^>]+>/g, '' );
				pastedStr = tinymce.trim( pastedStr );

				if ( regExp.test( pastedStr ) ) {
					editor.execCommand( 'mceInsertLink', false, {
						href: editor.dom.decode( pastedStr )
					} );

					event.preventDefault();
				}
			}
		} );

		// Remove any remaining placeholders on saving.
		editor.on( 'savecontent', function( event ) {
			event.content = removePlaceholderStrings( event.content, true );
		});

		// Prevent adding undo levels on inserting link placeholder.
		editor.on( 'BeforeAddUndo', function( event ) {
			if ( event.lastLevel && event.lastLevel.content && event.level.content &&
				event.lastLevel.content === removePlaceholderStrings( event.level.content ) ) {

				event.preventDefault();
			}
		});

		// When doing undo and redo with keyboard shortcuts (Ctrl|Cmd+Z, Ctrl|Cmd+Shift+Z, Ctrl|Cmd+Y),
		// set a flag to not focus the inline dialog. The editor has to remain focused so the users can do consecutive undo/redo.
		editor.on( 'keydown', function( event ) {
			if ( event.keyCode === 27 ) { // Esc
				editor.execCommand( 'mn_link_cancel' );
			}

			if ( event.altKey || ( tinymce.Env.mac && ( ! event.metaKey || event.ctrlKey ) ) ||
				( ! tinymce.Env.mac && ! event.ctrlKey ) ) {

				return;
			}

			if ( event.keyCode === 89 || event.keyCode === 90 ) { // Y or Z
				doingUndoRedo = true;

				window.clearTimeout( doingUndoRedoTimer );
				doingUndoRedoTimer = window.setTimeout( function() {
					doingUndoRedo = false;
				}, 500 );
			}
		} );

		editor.addButton( 'mn_link_preview', {
			type: 'MNLinkPreview',
			onPostRender: function() {
				previewInstance = this;
			}
		} );

		editor.addButton( 'mn_link_input', {
			type: 'MNLinkInput',
			onPostRender: function() {
				var element = this.getEl(),
					input = element.firstChild,
					$input, cache, last;

				inputInstance = this;

				if ( $ && $.ui && $.ui.autocomplete ) {
					$input = $( input );

					$input.on( 'keydown', function() {
						$input.removeAttr( 'aria-activedescendant' );
					} )
					.autocomplete( {
						source: function( request, response ) {
							if ( last === request.term ) {
								response( cache );
								return;
							}

							if ( /^https?:/.test( request.term ) || request.term.indexOf( '.' ) !== -1 ) {
								return response();
							}

							$.post( window.ajaxurl, {
								action: 'mn-link-ajax',
								page: 1,
								search: request.term,
								_ajax_linking_nonce: $( '#_ajax_linking_nonce' ).val()
							}, function( data ) {
								cache = data;
								response( data );
							}, 'json' );

							last = request.term;
						},
						focus: function( event, ui ) {
							$input.attr( 'aria-activedescendant', 'mce-mn-autocomplete-' + ui.item.ID );
							/*
							 * Don't empty the URL input field, when using the arrow keys to
							 * highlight items. See api.jqueryui.com/autocomplete/#event-focus
							 */
							event.preventDefault();
						},
						select: function( event, ui ) {
							$input.val( ui.item.permalink );
							$( element.firstChild.nextSibling ).val( ui.item.title );

							if ( 9 === event.keyCode && typeof window.mnLinkL10n !== 'undefined' ) {
								// Audible confirmation message when a link has been selected.
								speak( window.mnLinkL10n.linkSelected );
							}

							return false;
						},
						open: function() {
							$input.attr( 'aria-expanded', 'true' );
							editToolbar.blockHide = true;
						},
						close: function() {
							$input.attr( 'aria-expanded', 'false' );
							editToolbar.blockHide = false;
						},
						minLength: 2,
						position: {
							my: 'left top+2'
						},
						messages: {
							noResults: ( typeof window.uiAutocompleteL10n !== 'undefined' ) ? window.uiAutocompleteL10n.noResults : '',
							results: function( number ) {
								if ( typeof window.uiAutocompleteL10n !== 'undefined' ) {
									if ( number > 1 ) {
										return window.uiAutocompleteL10n.manyResults.replace( '%d', number );
									}

									return window.uiAutocompleteL10n.oneResult;
								}
							}
						}
					} ).autocomplete( 'instance' )._renderItem = function( ul, item ) {
						return $( '<li role="option" id="mce-mn-autocomplete-' + item.ID + '">' )
						.append( '<span>' + item.title + '</span>&nbsp;<span class="mn-editor-float-right">' + item.info + '</span>' )
						.appendTo( ul );
					};

					$input.attr( {
						'role': 'combobox',
						'aria-autocomplete': 'list',
						'aria-expanded': 'false',
						'aria-owns': $input.autocomplete( 'widget' ).attr( 'id' )
					} )
					.on( 'focus', function() {
						var inputValue = $input.val();
						/*
						 * Don't trigger a search if the URL field already has a link or is empty.
						 * Also, avoids screen readers announce `No search results`.
						 */
						if ( inputValue && ! /^https?:/.test( inputValue ) ) {
							$input.autocomplete( 'search' );
						}
					} )
					// Returns a jQuery object containing the menu element.
					.autocomplete( 'widget' )
						.addClass( 'mnlink-autocomplete' )
						.attr( 'role', 'listbox' )
						.removeAttr( 'tabindex' ) // Remove the `tabindex=0` attribute added by jQuery UI.
						/*
						 * Looks like Safari and VoiceOver need an `aria-selected` attribute. See ticket #33301.
						 * The `menufocus` and `menublur` events are the same events used to add and remove
						 * the `ui-state-focus` CSS class on the menu items. See jQuery UI Menu Widget.
						 */
						.on( 'menufocus', function( event, ui ) {
							ui.item.attr( 'aria-selected', 'true' );
						})
						.on( 'menublur', function() {
							/*
							 * The `menublur` event returns an object where the item is `null`
							 * so we need to find the active item with other means.
							 */
							$( this ).find( '[aria-selected="true"]' ).removeAttr( 'aria-selected' );
						});
				}

				tinymce.$( input ).on( 'keydown', function( event ) {
					if ( event.keyCode === 13 ) {
						editor.execCommand( 'mn_link_apply' );
						event.preventDefault();
					}
				} );
			}
		} );

		editor.on( 'mntoolbar', function( event ) {
			var linkNode = editor.dom.getParent( event.element, 'a' ),
				$linkNode, href, edit;

			if ( typeof window.mnLink !== 'undefined' && window.mnLink.modalOpen ) {
				editToolbar.tempHide = true;
				return;
			}

			editToolbar.tempHide = false;

			if ( linkNode ) {
				$linkNode = editor.$( linkNode );
				href = $linkNode.attr( 'href' );
				edit = $linkNode.attr( 'data-mnlink-edit' );

				if ( href === '_mn_link_placeholder' || edit ) {
					if ( href !== '_mn_link_placeholder' && ! inputInstance.getURL() ) {
						inputInstance.setURL( href );
					}

					event.element = linkNode;
					event.toolbar = editToolbar;
				} else if ( href && ! $linkNode.find( 'img' ).length ) {
					previewInstance.setURL( href );
					event.element = linkNode;
					event.toolbar = toolbar;

					if ( $linkNode.attr( 'data-mnlink-url-error' ) === 'true' ) {
						toolbar.$el.find( '.mn-link-preview a' ).addClass( 'mnlink-url-error' );
					} else {
						toolbar.$el.find( '.mn-link-preview a' ).removeClass( 'mnlink-url-error' );
						hasLinkError = false;
					}
				}
			} else if ( editToolbar.visible() ) {
				editor.execCommand( 'mn_link_cancel' );
			}
		} );

		editor.addButton( 'mn_link_edit', {
			tooltip: 'Edit ', // trailing space is needed, used for context
			icon: 'dashicon dashicons-edit',
			cmd: 'MN_Link'
		} );

		editor.addButton( 'mn_link_remove', {
			tooltip: 'Remove link',
			icon: 'dashicon dashicons-editor-unlink',
			cmd: 'mn_unlink'
		} );

		editor.addButton( 'mn_link_advanced', {
			tooltip: 'Link options',
			icon: 'dashicon dashicons-admin-generic',
			onclick: function() {
				if ( typeof window.mnLink !== 'undefined' ) {
					var url = inputInstance.getURL() || null,
						text = inputInstance.getLinkText() || null;

					/*
					 * Accessibility note: moving focus back to the editor confuses
					 * screen readers. They will announce again the Editor ARIA role
					 * `application` and the iframe `title` attribute.
					 *
					 * Unfortunately IE looses the selection when the editor iframe
					 * looses focus, so without returning focus to the editor, the code
					 * in the modal will not be able to get the selection, place the caret
					 * at the same location, etc.
					 */
					if ( tinymce.Env.ie ) {
						editor.focus(); // Needed for IE
					}

					window.mnLink.open( editor.id, url, text, linkNode );

					editToolbar.tempHide = true;
					inputInstance.reset();
				}
			}
		} );

		editor.addButton( 'mn_link_apply', {
			tooltip: 'Apply',
			icon: 'dashicon dashicons-editor-break',
			cmd: 'mn_link_apply',
			classes: 'widget btn primary'
		} );

		return {
			close: function() {
				editToolbar.tempHide = false;
				editor.execCommand( 'mn_link_cancel' );
			},
			checkLink: checkLink
		};
	} );
} )( window.tinymce );
