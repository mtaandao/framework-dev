/**
 * Hook shortcodes to TineMCE
 *
 * @return void
 */
(function() {
    var icon_url = mtaaFramework.assetsUri + '/images/shortcodes/icon.png';

    tinymce.create(
        "tinymce.plugins.mtaaShortcodes",
        {
            init: function(d, e) {
                d.addCommand("mnzOpenDialog", function(a, c) {
                    // Grab the selected text from the content editor.
                    selectedText = '';

                    if (d.selection.getContent().length > 0) {
                        selectedText = d.selection.getContent();
                    }

                    mnzSelectedShortcodeType = c.identifier;
                    mnzSelectedShortcodeTitle = c.title;

                    jQuery.get(ajaxurl + '?action=mtaa_shortcodes_ajax_dialog', function(b) {
                        jQuery( '#mnz-options').addClass('shortcode-' + mnzSelectedShortcodeType);
                        jQuery( '#mnz-preview').addClass('shortcode-' + mnzSelectedShortcodeType);

                        // Skip the popup on certain shortcodes.
                        var a;
                        switch (mnzSelectedShortcodeType) {
                            // Highlight
                            case 'highlight':
                                a = '[highlight]' + selectedText + '[/highlight]';
                                tinyMCE.activeEditor.execCommand("mceInsertContent", false, a);
                                break;

                            // Dropcap
                            case 'dropcap':
                                a = '[dropcap]' + selectedText + '[/dropcap]';
                                tinyMCE.activeEditor.execCommand("mceInsertContent", false, a);
                                break;

                            default:
                                var width  = jQuery(window).width()
                                  , height = jQuery(window).height() - 84;

                                width  = ((720 < width) ? 720 : width) - 80;

                                jQuery("#mnz-dialog").remove();
                                jQuery("body").append(b);
                                jQuery("#mnz-dialog").hide();

                                tb_show("Insert " + mnzSelectedShortcodeTitle + " Shortcode", "#TB_inline?width=" + width + "&height=" + height + "&inlineId=mnz-dialog");
                                jQuery("#mnz-options h3:first").text("Customize the " + c.title + " Shortcode");
                                break;
                        }

                    });

                });

                // d.onNodeChange.add(function(a,c){ c.setDisabled( "mtaa_shortcodes_button",a.selection.getContent().length>0 ) } ) // Disables the button if text is highlighted in the editor.
            },

            createControl:function(d, e) {
                if (d == "mtaa_shortcodes_button") {
                    d = e.createMenuButton("mtaa_shortcodes_button", {
                        'title' : "Insert Shortcode",
                        'image' : icon_url,
                        'icons' : false
                    });

                    var that = this;
                    d.onRenderMenu.add(function(c, b) {
                        that.addWithDialog(b, "Button", "button");
                        that.addWithDialog(b, "Icon Link", "ilink");
                        b.addSeparator();

                        that.addWithDialog(b, "Info Box", "box");
                        b.addSeparator();

                        that.addWithDialog(b, "Column Layout", "column");
                        that.addWithDialog(b, "Tabbed Layout", "tab");
                        b.addSeparator();

                        c = b.addMenu({ 'title' : "List Generator" });
                        that.addWithDialog(c, "Unordered List", "unordered_list");
                        that.addWithDialog(c, "Ordered List", "ordered_list");

                        c = b.addMenu({ 'title' : "Social Buttons" });
                        that.addWithDialog(c, "Social Profile Icon", "social_icon");
                        c.addSeparator();

                        that.addWithDialog(c, "Twitter", "twitter");
                        that.addWithDialog(c, "Like on Facebook", "fblike");
                    });

                    return d;
                }

                return null;
            },

            addImmediate: function(d, e, a) {
                d.add({
                    'title'   : e,
                    'onclick' : function() {
                        tinyMCE.activeEditor.execCommand("mceInsertContent", false, a);
                    }
                });
            },

            addWithDialog: function(d, e, a) {
                d.add({
                    'title'   : e,
                    'onclick' : function() {
                        tinyMCE.activeEditor.execCommand("mnzOpenDialog", false, { 'title' : e, 'identifier' : a});
                    }
                });
            },

            getInfo: function() {
                return {
                    'longname'  : "Mtaa Shortcode Generator",
                    'author'    : "Mtaa",
                    'authorurl' : "http://mtaandao.co.ke/",
                    'infourl'   : "http://www.mtaandao.co.ke/framework-tour/",
                    'version'   : "1.0"
                };
            }
        }
    );

    tinymce.PluginManager.add("mtaaShortcodes", tinymce.plugins.mtaaShortcodes);
})();
