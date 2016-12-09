(function ( tinymce ) {
	'use strict';

	tinymce.PluginManager.add( 'mnembed', function ( editor, url ) {
		editor.on( 'init', function () {
			var scriptId = editor.dom.uniqueId();

			var scriptElm = editor.dom.create( 'script', {
				id: scriptId,
				type: 'text/javascript',
				src: url + '/../../../mn-embed.js'
			} );

			editor.getDoc().getElementsByTagName( 'head' )[ 0 ].appendChild( scriptElm );
		} );
	} );
})( window.tinymce );
