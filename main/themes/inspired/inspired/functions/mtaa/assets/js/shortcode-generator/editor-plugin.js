( function() {
    // TinyMCE plugin start.
    tinymce.PluginManager.add( 'mtaaShortcodes', function( editor, url ) {
        // Register a command to open the dialog.
        editor.addCommand( 'mnzOpenDialog', function( ui, v ) {
            mnzSelectedShortcodeType = v;
            selectedText = editor.selection.getContent({format: 'text'});

            jQuery.get(ajaxurl + '?action=mtaa_shortcodes_ajax_dialog', function(html) {
                jQuery( '#mnz-options' ).addClass( 'shortcode-' + v );

                var width = Math.min(jQuery(window).width(), 720) - 80;
                var height = jQuery(window).height() - 84;

                jQuery("#mnz-dialog").remove();
                jQuery("body").append(html);
                jQuery("#mnz-dialog").hide();

                tb_show( "Insert ["+ v +"] Shortcode", "#TB_inline?width="+width+"&height="+height+"&inlineId=mnz-dialog" );
                jQuery( "#mnz-options h3:first").text( "Customize the ["+v+"] Shortcode" );
            });
        });

        // Register a command to insert the shortcode immediately.
        editor.addCommand( 'mnzInsertImmediate', function( ui, v ) {
            var selected = editor.selection.getContent({format: 'text'});

            // If we have selected text, close the shortcode.
            if ( '' != selected ) {
                selected += '[/' + v + ']';
            }

            editor.insertContent( '[' + v + ']' + selected );
        });

        // Add a button that opens a window
        editor.addButton( 'mtaa_shortcodes_button', {
            type: 'menubutton',
            icon: 'mnz-shortcode-icon',
            classes: 'btn mnz-shortcode-button',
            tooltip: 'Insert Shortcode',
            menu: [
                {text: 'Button', onclick: function() { editor.execCommand( 'mnzOpenDialog', false, 'button', { title: 'Button' } ); } },
                {text: 'Icon Link', onclick: function() { editor.execCommand( 'mnzOpenDialog', false, 'ilink', { title: 'Icon Link' } ); } },
                {text: 'Info Box', onclick: function() { editor.execCommand( 'mnzOpenDialog', false, 'box', { title: 'Info Box' } ); } },

                {text: 'Column Layout', onclick: function() { editor.execCommand( 'mnzOpenDialog', false, 'column', { title: 'Column Layout' } ); } },
                {text: 'Tabbed Layout', onclick: function() { editor.execCommand( 'mnzOpenDialog', false, 'tab', { title: 'Tabbed Layout' } ); } },

                {text: 'List Generator', menu: [
                    {text: 'Unordered List', onclick: function() { editor.execCommand( 'mnzOpenDialog', false, 'unordered_list', { title: 'Unordered List' } ); } },
                    {text: 'Ordered List', onclick: function() { editor.execCommand( 'mnzOpenDialog', false, 'ordered_list', { title: 'Ordered List' } ); } }
                ]},

                {text: 'Social Buttons', menu: [
                    {text: 'Social Profile Icon', onclick: function() { editor.execCommand( 'mnzOpenDialog', false, 'social_icon', { title: 'Social Profile Icon' } ); } },
                    {text: 'Twitter', onclick: function() { editor.execCommand( 'mnzOpenDialog', false, 'twitter', { title: 'Twitter' } ); } },
                    {text: 'Like on Facebook', onclick: function() { editor.execCommand( 'mnzOpenDialog', false, 'fblike', { title: 'Like on Facebook' } ); } },
                ]},
            ]
        });
    } );
} )();
