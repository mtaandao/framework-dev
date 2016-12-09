<?php
/**
 * Mtaandao core upgrade functionality.
 *
 * @package Mtaandao
 * @subpackage Administration
 * @since 2.7.0
 */

/**
 * Stores files to be deleted.
 *
 * @since 2.7.0
 * @global array $_old_files
 * @var array
 * @name $_old_files
 */
global $_old_files;

$_old_files = array(
// 2.0
'admin/import-b2.php',
'admin/import-blogger.php',
'admin/import-greymatter.php',
'admin/import-livejournal.php',
'admin/import-mt.php',
'admin/import-rss.php',
'admin/import-textpattern.php',
'admin/quicktags.js',
'mn-images/fade-butt.png',
'mn-images/get-firefox.png',
'mn-images/header-shadow.png',
'mn-images/smilies',
'mn-images/mn-small.png',
'mn-images/mnminilogo.png',
'mn.php',
// 2.0.8
'res/js/tinymce/plugins/inlinepopups/readme.txt',
// 2.1
'admin/edit-form-ajax-cat.php',
'admin/execute-pings.php',
'admin/inline-uploading.php',
'admin/link-categories.php',
'admin/list-manipulation.js',
'admin/list-manipulation.php',
'res/comment-functions.php',
'res/feed-functions.php',
'res/functions-compat.php',
'res/functions-formatting.php',
'res/functions-post.php',
'res/js/dbx-key.js',
'res/js/tinymce/plugins/autosave/langs/cs.js',
'res/js/tinymce/plugins/autosave/langs/sv.js',
'res/links.php',
'res/pluggable-functions.php',
'res/template-functions-author.php',
'res/template-functions-category.php',
'res/template-functions-general.php',
'res/template-functions-links.php',
'res/template-functions-post.php',
'res/mn-l10n.php',
// 2.2
'admin/cat-js.php',
'admin/import/b2.php',
'res/js/autosave-js.php',
'res/js/list-manipulation-js.php',
'res/js/mn-ajax-js.php',
// 2.3
'admin/admin-db.php',
'admin/cat.js',
'admin/categories.js',
'admin/custom-fields.js',
'admin/dbx-admin-key.js',
'admin/edit-comments.js',
'admin/install-rtl.css',
'admin/install.css',
'admin/upgrade-schema.php',
'admin/upload-functions.php',
'admin/upload-rtl.css',
'admin/upload.css',
'admin/upload.js',
'admin/users.js',
'admin/widgets-rtl.css',
'admin/widgets.css',
'admin/xfn.js',
'res/js/tinymce/license.html',
// 2.5
'admin/css/upload.css',
'admin/images/box-bg-left.gif',
'admin/images/box-bg-right.gif',
'admin/images/box-bg.gif',
'admin/images/box-butt-left.gif',
'admin/images/box-butt-right.gif',
'admin/images/box-butt.gif',
'admin/images/box-head-left.gif',
'admin/images/box-head-right.gif',
'admin/images/box-head.gif',
'admin/images/heading-bg.gif',
'admin/images/login-bkg-bottom.gif',
'admin/images/login-bkg-tile.gif',
'admin/images/notice.gif',
'admin/images/toggle.gif',
'admin/includes/upload.php',
'admin/js/dbx-admin-key.js',
'admin/js/link-cat.js',
'admin/profile-update.php',
'admin/templates.php',
'res/images/wlw/WpComments.png',
'res/images/wlw/WpIcon.png',
'res/images/wlw/WpWatermark.png',
'res/js/dbx.js',
'res/js/fat.js',
'res/js/list-manipulation.js',
'res/js/tinymce/langs/en.js',
'res/js/tinymce/plugins/autosave/editor_plugin_src.js',
'res/js/tinymce/plugins/autosave/langs',
'res/js/tinymce/plugins/directionality/images',
'res/js/tinymce/plugins/directionality/langs',
'res/js/tinymce/plugins/inlinepopups/css',
'res/js/tinymce/plugins/inlinepopups/images',
'res/js/tinymce/plugins/inlinepopups/jscripts',
'res/js/tinymce/plugins/paste/images',
'res/js/tinymce/plugins/paste/jscripts',
'res/js/tinymce/plugins/paste/langs',
'res/js/tinymce/plugins/spellchecker/classes/HttpClient.class.php',
'res/js/tinymce/plugins/spellchecker/classes/TinyGoogleSpell.class.php',
'res/js/tinymce/plugins/spellchecker/classes/TinyPspell.class.php',
'res/js/tinymce/plugins/spellchecker/classes/TinyPspellShell.class.php',
'res/js/tinymce/plugins/spellchecker/css/spellchecker.css',
'res/js/tinymce/plugins/spellchecker/images',
'res/js/tinymce/plugins/spellchecker/langs',
'res/js/tinymce/plugins/spellchecker/tinyspell.php',
'res/js/tinymce/plugins/mtaandao/images',
'res/js/tinymce/plugins/mtaandao/langs',
'res/js/tinymce/plugins/mtaandao/mtaandao.css',
'res/js/tinymce/plugins/mnhelp',
'res/js/tinymce/themes/advanced/css',
'res/js/tinymce/themes/advanced/images',
'res/js/tinymce/themes/advanced/jscripts',
'res/js/tinymce/themes/advanced/langs',
// 2.5.1
'res/js/tinymce/tiny_mce_gzip.php',
// 2.6
'admin/bookmarklet.php',
'res/js/jquery/jquery.dimensions.min.js',
'res/js/tinymce/plugins/mtaandao/popups.css',
'res/js/mn-ajax.js',
// 2.7
'admin/css/press-this-ie-rtl.css',
'admin/css/press-this-ie.css',
'admin/css/upload-rtl.css',
'admin/edit-form.php',
'admin/images/comment-pill.gif',
'admin/images/comment-stalk-classic.gif',
'admin/images/comment-stalk-fresh.gif',
'admin/images/comment-stalk-rtl.gif',
'admin/images/del.png',
'admin/images/gear.png',
'admin/images/media-button-gallery.gif',
'admin/images/media-buttons.gif',
'admin/images/postbox-bg.gif',
'admin/images/tab.png',
'admin/images/tail.gif',
'admin/js/forms.js',
'admin/js/upload.js',
'admin/link-import.php',
'res/images/audio.png',
'res/images/css.png',
'res/images/default.png',
'res/images/doc.png',
'res/images/exe.png',
'res/images/html.png',
'res/images/js.png',
'res/images/pdf.png',
'res/images/swf.png',
'res/images/tar.png',
'res/images/text.png',
'res/images/video.png',
'res/images/zip.png',
'res/js/tinymce/tiny_mce_config.php',
'res/js/tinymce/tiny_mce_ext.js',
// 2.8
'admin/js/users.js',
'res/js/swfupload/plugins/swfupload.documentready.js',
'res/js/swfupload/plugins/swfupload.graceful_degradation.js',
'res/js/swfupload/swfupload_f9.swf',
'res/js/tinymce/plugins/autosave',
'res/js/tinymce/plugins/paste/css',
'res/js/tinymce/utils/mclayer.js',
'res/js/tinymce/mtaandao.css',
// 2.8.5
'admin/import/btt.php',
'admin/import/jkw.php',
// 2.9
'admin/js/page.dev.js',
'admin/js/page.js',
'admin/js/set-post-thumbnail-handler.dev.js',
'admin/js/set-post-thumbnail-handler.js',
'admin/js/slug.dev.js',
'admin/js/slug.js',
'res/gettext.php',
'res/js/tinymce/plugins/mtaandao/js',
'res/streams.php',
// MU
'README.txt',
'htaccess.dist',
'index-install.php',
'admin/css/mu-rtl.css',
'admin/css/mu.css',
'admin/images/site-admin.png',
'admin/includes/mu.php',
'admin/mnmu-admin.php',
'admin/mnmu-blogs.php',
'admin/mnmu-edit.php',
'admin/mnmu-options.php',
'admin/mnmu-themes.php',
'admin/mnmu-upgrade-site.php',
'admin/mnmu-users.php',
'res/images/mtaandao-mu.png',
'res/mnmu-default-filters.php',
'res/mnmu-functions.php',
'mnmu-settings.php',
// 3.0
'admin/categories.php',
'admin/edit-category-form.php',
'admin/edit-page-form.php',
'admin/edit-pages.php',
'admin/images/admin-header-footer.png',
'admin/images/browse-happy.gif',
'admin/images/ico-add.png',
'admin/images/ico-close.png',
'admin/images/ico-edit.png',
'admin/images/ico-viemnage.png',
'admin/images/fav-top.png',
'admin/images/screen-options-left.gif',
'admin/images/mn-logo-vs.gif',
'admin/images/mn-logo.gif',
'admin/import',
'admin/js/mn-gears.dev.js',
'admin/js/mn-gears.js',
'admin/options-misc.php',
'admin/page-new.php',
'admin/page.php',
'admin/rtl.css',
'admin/rtl.dev.css',
'admin/update-links.php',
'admin/admin.css',
'admin/admin.dev.css',
'res/js/codepress',
'res/js/codepress/engines/khtml.js',
'res/js/codepress/engines/older.js',
'res/js/jquery/autocomplete.dev.js',
'res/js/jquery/autocomplete.js',
'res/js/jquery/interface.js',
'res/js/scriptaculous/prototype.js',
'res/js/tinymce/mn-tinymce.js',
// 3.1
'admin/edit-attachment-rows.php',
'admin/edit-link-categories.php',
'admin/edit-link-category-form.php',
'admin/edit-post-rows.php',
'admin/images/button-grad-active-vs.png',
'admin/images/button-grad-vs.png',
'admin/images/fav-arrow-vs-rtl.gif',
'admin/images/fav-arrow-vs.gif',
'admin/images/fav-top-vs.gif',
'admin/images/list-vs.png',
'admin/images/screen-options-right-up.gif',
'admin/images/screen-options-right.gif',
'admin/images/visit-site-button-grad-vs.gif',
'admin/images/visit-site-button-grad.gif',
'admin/link-category.php',
'admin/sidebar.php',
'res/classes.php',
'res/js/tinymce/blank.htm',
'res/js/tinymce/plugins/media/css/content.css',
'res/js/tinymce/plugins/media/img',
'res/js/tinymce/plugins/safari',
// 3.2
'admin/images/logo-login.gif',
'admin/images/star.gif',
'admin/js/list-table.dev.js',
'admin/js/list-table.js',
'res/default-embeds.php',
'res/js/tinymce/plugins/mtaandao/img/help.gif',
'res/js/tinymce/plugins/mtaandao/img/more.gif',
'res/js/tinymce/plugins/mtaandao/img/toolbars.gif',
'res/js/tinymce/themes/advanced/img/fm.gif',
'res/js/tinymce/themes/advanced/img/sflogo.png',
// 3.3
'admin/css/colors-classic-rtl.css',
'admin/css/colors-classic-rtl.dev.css',
'admin/css/colors-fresh-rtl.css',
'admin/css/colors-fresh-rtl.dev.css',
'admin/css/dashboard-rtl.dev.css',
'admin/css/dashboard.dev.css',
'admin/css/global-rtl.css',
'admin/css/global-rtl.dev.css',
'admin/css/global.css',
'admin/css/global.dev.css',
'admin/css/install-rtl.dev.css',
'admin/css/login-rtl.dev.css',
'admin/css/login.dev.css',
'admin/css/ms.css',
'admin/css/ms.dev.css',
'admin/css/nav-menu-rtl.css',
'admin/css/nav-menu-rtl.dev.css',
'admin/css/nav-menu.css',
'admin/css/nav-menu.dev.css',
'admin/css/plugin-install-rtl.css',
'admin/css/plugin-install-rtl.dev.css',
'admin/css/plugin-install.css',
'admin/css/plugin-install.dev.css',
'admin/css/press-this-rtl.dev.css',
'admin/css/press-this.dev.css',
'admin/css/theme-editor-rtl.css',
'admin/css/theme-editor-rtl.dev.css',
'admin/css/theme-editor.css',
'admin/css/theme-editor.dev.css',
'admin/css/theme-install-rtl.css',
'admin/css/theme-install-rtl.dev.css',
'admin/css/theme-install.css',
'admin/css/theme-install.dev.css',
'admin/css/widgets-rtl.dev.css',
'admin/css/widgets.dev.css',
'admin/includes/internal-linking.php',
'res/images/admin-bar-sprite-rtl.png',
'res/js/jquery/ui.button.js',
'res/js/jquery/ui.core.js',
'res/js/jquery/ui.dialog.js',
'res/js/jquery/ui.draggable.js',
'res/js/jquery/ui.droppable.js',
'res/js/jquery/ui.mouse.js',
'res/js/jquery/ui.position.js',
'res/js/jquery/ui.resizable.js',
'res/js/jquery/ui.selectable.js',
'res/js/jquery/ui.sortable.js',
'res/js/jquery/ui.tabs.js',
'res/js/jquery/ui.widget.js',
'res/js/l10n.dev.js',
'res/js/l10n.js',
'res/js/tinymce/plugins/mnlink/css',
'res/js/tinymce/plugins/mnlink/img',
'res/js/tinymce/plugins/mnlink/js',
'res/js/tinymce/themes/advanced/img/mnicons.png',
'res/js/tinymce/themes/advanced/skins/mn_theme/img/butt2.png',
'res/js/tinymce/themes/advanced/skins/mn_theme/img/button_bg.png',
'res/js/tinymce/themes/advanced/skins/mn_theme/img/down_arrow.gif',
'res/js/tinymce/themes/advanced/skins/mn_theme/img/fade-butt.png',
'res/js/tinymce/themes/advanced/skins/mn_theme/img/separator.gif',
// Don't delete, yet: 'mn-rss.php',
// Don't delete, yet: 'mn-rdf.php',
// Don't delete, yet: 'mn-rss2.php',
// Don't delete, yet: 'mn-commentsrss2.php',
// Don't delete, yet: 'mn-atom.php',
// Don't delete, yet: 'mn-feed.php',
// 3.4
'admin/images/gray-star.png',
'admin/images/logo-login.png',
'admin/images/star.png',
'admin/index-extra.php',
'admin/network/index-extra.php',
'admin/user/index-extra.php',
'admin/images/screenshots/admin-flyouts.png',
'admin/images/screenshots/coediting.png',
'admin/images/screenshots/drag-and-drop.png',
'admin/images/screenshots/help-screen.png',
'admin/images/screenshots/media-icon.png',
'admin/images/screenshots/new-feature-pointer.png',
'admin/images/screenshots/welcome-screen.png',
'res/css/editor-buttons.css',
'res/css/editor-buttons.dev.css',
'res/js/tinymce/plugins/paste/blank.htm',
'res/js/tinymce/plugins/mtaandao/css',
'res/js/tinymce/plugins/mtaandao/editor_plugin.dev.js',
'res/js/tinymce/plugins/mtaandao/img/embedded.png',
'res/js/tinymce/plugins/mtaandao/img/more_bug.gif',
'res/js/tinymce/plugins/mtaandao/img/page_bug.gif',
'res/js/tinymce/plugins/mndialogs/editor_plugin.dev.js',
'res/js/tinymce/plugins/mneditimage/css/editimage-rtl.css',
'res/js/tinymce/plugins/mneditimage/editor_plugin.dev.js',
'res/js/tinymce/plugins/mnfullscreen/editor_plugin.dev.js',
'res/js/tinymce/plugins/mngallery/editor_plugin.dev.js',
'res/js/tinymce/plugins/mngallery/img/gallery.png',
'res/js/tinymce/plugins/mnlink/editor_plugin.dev.js',
// Don't delete, yet: 'mn-pass.php',
// Don't delete, yet: 'mn-register.php',
// 3.5
'admin/gears-manifest.php',
'admin/includes/manifest.php',
'admin/images/archive-link.png',
'admin/images/blue-grad.png',
'admin/images/button-grad-active.png',
'admin/images/button-grad.png',
'admin/images/ed-bg-vs.gif',
'admin/images/ed-bg.gif',
'admin/images/fade-butt.png',
'admin/images/fav-arrow-rtl.gif',
'admin/images/fav-arrow.gif',
'admin/images/fav-vs.png',
'admin/images/fav.png',
'admin/images/gray-grad.png',
'admin/images/loading-publish.gif',
'admin/images/logo-ghost.png',
'admin/images/logo.gif',
'admin/images/menu-arrow-frame-rtl.png',
'admin/images/menu-arrow-frame.png',
'admin/images/menu-arrows.gif',
'admin/images/menu-bits-rtl-vs.gif',
'admin/images/menu-bits-rtl.gif',
'admin/images/menu-bits-vs.gif',
'admin/images/menu-bits.gif',
'admin/images/menu-dark-rtl-vs.gif',
'admin/images/menu-dark-rtl.gif',
'admin/images/menu-dark-vs.gif',
'admin/images/menu-dark.gif',
'admin/images/required.gif',
'admin/images/screen-options-toggle-vs.gif',
'admin/images/screen-options-toggle.gif',
'admin/images/toggle-arrow-rtl.gif',
'admin/images/toggle-arrow.gif',
'admin/images/upload-classic.png',
'admin/images/upload-fresh.png',
'admin/images/white-grad-active.png',
'admin/images/white-grad.png',
'admin/images/widgets-arrow-vs.gif',
'admin/images/widgets-arrow.gif',
'admin/images/mnspin_dark.gif',
'res/images/upload.png',
'res/js/prototype.js',
'res/js/scriptaculous',
'admin/css/admin-rtl.dev.css',
'admin/css/admin.dev.css',
'admin/css/media-rtl.dev.css',
'admin/css/media.dev.css',
'admin/css/colors-classic.dev.css',
'admin/css/customize-controls-rtl.dev.css',
'admin/css/customize-controls.dev.css',
'admin/css/ie-rtl.dev.css',
'admin/css/ie.dev.css',
'admin/css/install.dev.css',
'admin/css/colors-fresh.dev.css',
'res/js/customize-base.dev.js',
'res/js/json2.dev.js',
'res/js/comment-reply.dev.js',
'res/js/customize-preview.dev.js',
'res/js/mnlink.dev.js',
'res/js/tw-sack.dev.js',
'res/js/mn-list-revisions.dev.js',
'res/js/autosave.dev.js',
'res/js/admin-bar.dev.js',
'res/js/quicktags.dev.js',
'res/js/mn-ajax-response.dev.js',
'res/js/mn-pointer.dev.js',
'res/js/hoverIntent.dev.js',
'res/js/colorpicker.dev.js',
'res/js/mn-lists.dev.js',
'res/js/customize-loader.dev.js',
'res/js/jquery/jquery.table-hotkeys.dev.js',
'res/js/jquery/jquery.color.dev.js',
'res/js/jquery/jquery.color.js',
'res/js/jquery/jquery.hotkeys.dev.js',
'res/js/jquery/jquery.form.dev.js',
'res/js/jquery/suggest.dev.js',
'admin/js/xfn.dev.js',
'admin/js/set-post-thumbnail.dev.js',
'admin/js/comment.dev.js',
'admin/js/theme.dev.js',
'admin/js/cat.dev.js',
'admin/js/password-strength-meter.dev.js',
'admin/js/user-profile.dev.js',
'admin/js/theme-preview.dev.js',
'admin/js/post.dev.js',
'admin/js/media-upload.dev.js',
'admin/js/word-count.dev.js',
'admin/js/plugin-install.dev.js',
'admin/js/edit-comments.dev.js',
'admin/js/media-gallery.dev.js',
'admin/js/custom-fields.dev.js',
'admin/js/custom-background.dev.js',
'admin/js/common.dev.js',
'admin/js/inline-edit-tax.dev.js',
'admin/js/gallery.dev.js',
'admin/js/utils.dev.js',
'admin/js/widgets.dev.js',
'admin/js/mn-fullscreen.dev.js',
'admin/js/nav-menu.dev.js',
'admin/js/dashboard.dev.js',
'admin/js/link.dev.js',
'admin/js/user-suggest.dev.js',
'admin/js/postbox.dev.js',
'admin/js/tags.dev.js',
'admin/js/image-edit.dev.js',
'admin/js/media.dev.js',
'admin/js/customize-controls.dev.js',
'admin/js/inline-edit-post.dev.js',
'admin/js/categories.dev.js',
'admin/js/editor.dev.js',
'res/js/tinymce/plugins/mneditimage/js/editimage.dev.js',
'res/js/tinymce/plugins/mndialogs/js/popup.dev.js',
'res/js/tinymce/plugins/mndialogs/js/mndialog.dev.js',
'res/js/plupload/handlers.dev.js',
'res/js/plupload/mn-plupload.dev.js',
'res/js/swfupload/handlers.dev.js',
'res/js/jcrop/jquery.Jcrop.dev.js',
'res/js/jcrop/jquery.Jcrop.js',
'res/js/jcrop/jquery.Jcrop.css',
'res/js/imgareaselect/jquery.imgareaselect.dev.js',
'res/css/mn-pointer.dev.css',
'res/css/editor.dev.css',
'res/css/jquery-ui-dialog.dev.css',
'res/css/admin-bar-rtl.dev.css',
'res/css/admin-bar.dev.css',
'res/js/jquery/ui/jquery.effects.clip.min.js',
'res/js/jquery/ui/jquery.effects.scale.min.js',
'res/js/jquery/ui/jquery.effects.blind.min.js',
'res/js/jquery/ui/jquery.effects.core.min.js',
'res/js/jquery/ui/jquery.effects.shake.min.js',
'res/js/jquery/ui/jquery.effects.fade.min.js',
'res/js/jquery/ui/jquery.effects.explode.min.js',
'res/js/jquery/ui/jquery.effects.slide.min.js',
'res/js/jquery/ui/jquery.effects.drop.min.js',
'res/js/jquery/ui/jquery.effects.highlight.min.js',
'res/js/jquery/ui/jquery.effects.bounce.min.js',
'res/js/jquery/ui/jquery.effects.pulsate.min.js',
'res/js/jquery/ui/jquery.effects.transfer.min.js',
'res/js/jquery/ui/jquery.effects.fold.min.js',
'admin/images/screenshots/captions-1.png',
'admin/images/screenshots/captions-2.png',
'admin/images/screenshots/flex-header-1.png',
'admin/images/screenshots/flex-header-2.png',
'admin/images/screenshots/flex-header-3.png',
'admin/images/screenshots/flex-header-media-library.png',
'admin/images/screenshots/theme-customizer.png',
'admin/images/screenshots/twitter-embed-1.png',
'admin/images/screenshots/twitter-embed-2.png',
'admin/js/utils.js',
'admin/options-privacy.php',
'mn-app.php',
'res/class-mn-atom-server.php',
'res/js/tinymce/themes/advanced/skins/mn_theme/ui.css',
// 3.5.2
'res/js/swfupload/swfupload-all.js',
// 3.6
'admin/js/revisions-js.php',
'admin/images/screenshots',
'admin/js/categories.js',
'admin/js/categories.min.js',
'admin/js/custom-fields.js',
'admin/js/custom-fields.min.js',
// 3.7
'admin/js/cat.js',
'admin/js/cat.min.js',
'res/js/tinymce/plugins/mneditimage/js/editimage.min.js',
// 3.8
'res/js/tinymce/themes/advanced/skins/mn_theme/img/page_bug.gif',
'res/js/tinymce/themes/advanced/skins/mn_theme/img/more_bug.gif',
'res/js/thickbox/tb-close-2x.png',
'res/js/thickbox/tb-close.png',
'res/images/mnmini-blue-2x.png',
'res/images/mnmini-blue.png',
'admin/css/colors-fresh.css',
'admin/css/colors-classic.css',
'admin/css/colors-fresh.min.css',
'admin/css/colors-classic.min.css',
'admin/js/about.min.js',
'admin/js/about.js',
'admin/images/arrows-dark-vs-2x.png',
'admin/images/mn-logo-vs.png',
'admin/images/arrows-dark-vs.png',
'admin/images/mn-logo.png',
'admin/images/arrows-pr.png',
'admin/images/arrows-dark.png',
'admin/images/press-this.png',
'admin/images/press-this-2x.png',
'admin/images/arrows-vs-2x.png',
'admin/images/welcome-icons.png',
'admin/images/mn-logo-2x.png',
'admin/images/stars-rtl-2x.png',
'admin/images/arrows-dark-2x.png',
'admin/images/arrows-pr-2x.png',
'admin/images/menu-shadow-rtl.png',
'admin/images/arrows-vs.png',
'admin/images/about-search-2x.png',
'admin/images/bubble_bg-rtl-2x.gif',
'admin/images/mn-badge-2x.png',
'admin/images/mtaandao-logo-2x.png',
'admin/images/bubble_bg-rtl.gif',
'admin/images/mn-badge.png',
'admin/images/menu-shadow.png',
'admin/images/about-globe-2x.png',
'admin/images/welcome-icons-2x.png',
'admin/images/stars-rtl.png',
'admin/images/mn-logo-vs-2x.png',
'admin/images/about-updates-2x.png',
// 3.9
'admin/css/colors.css',
'admin/css/colors.min.css',
'admin/css/colors-rtl.css',
'admin/css/colors-rtl.min.css',
// Following files added back in 4.5 see #36083
// 'admin/css/media-rtl.min.css',
// 'admin/css/media.min.css',
// 'admin/css/farbtastic-rtl.min.css',
'admin/images/lock-2x.png',
'admin/images/lock.png',
'admin/js/theme-preview.js',
'admin/js/theme-install.min.js',
'admin/js/theme-install.js',
'admin/js/theme-preview.min.js',
'res/js/plupload/plupload.html4.js',
'res/js/plupload/plupload.html5.js',
'res/js/plupload/changelog.txt',
'res/js/plupload/plupload.silverlight.js',
'res/js/plupload/plupload.flash.js',
'res/js/plupload/plupload.js',
'res/js/tinymce/plugins/spellchecker',
'res/js/tinymce/plugins/inlinepopups',
'res/js/tinymce/plugins/media/js',
'res/js/tinymce/plugins/media/css',
'res/js/tinymce/plugins/mtaandao/img',
'res/js/tinymce/plugins/mndialogs/js',
'res/js/tinymce/plugins/mneditimage/img',
'res/js/tinymce/plugins/mneditimage/js',
'res/js/tinymce/plugins/mneditimage/css',
'res/js/tinymce/plugins/mngallery/img',
'res/js/tinymce/plugins/mnfullscreen/css',
'res/js/tinymce/plugins/paste/js',
'res/js/tinymce/themes/advanced',
'res/js/tinymce/tiny_mce.js',
'res/js/tinymce/mark_loaded_src.js',
'res/js/tinymce/mn-tinymce-schema.js',
'res/js/tinymce/plugins/media/editor_plugin.js',
'res/js/tinymce/plugins/media/editor_plugin_src.js',
'res/js/tinymce/plugins/media/media.htm',
'res/js/tinymce/plugins/mnview/editor_plugin_src.js',
'res/js/tinymce/plugins/mnview/editor_plugin.js',
'res/js/tinymce/plugins/directionality/editor_plugin.js',
'res/js/tinymce/plugins/directionality/editor_plugin_src.js',
'res/js/tinymce/plugins/mtaandao/editor_plugin.js',
'res/js/tinymce/plugins/mtaandao/editor_plugin_src.js',
'res/js/tinymce/plugins/mndialogs/editor_plugin_src.js',
'res/js/tinymce/plugins/mndialogs/editor_plugin.js',
'res/js/tinymce/plugins/mneditimage/editimage.html',
'res/js/tinymce/plugins/mneditimage/editor_plugin.js',
'res/js/tinymce/plugins/mneditimage/editor_plugin_src.js',
'res/js/tinymce/plugins/fullscreen/editor_plugin_src.js',
'res/js/tinymce/plugins/fullscreen/fullscreen.htm',
'res/js/tinymce/plugins/fullscreen/editor_plugin.js',
'res/js/tinymce/plugins/mnlink/editor_plugin_src.js',
'res/js/tinymce/plugins/mnlink/editor_plugin.js',
'res/js/tinymce/plugins/mngallery/editor_plugin_src.js',
'res/js/tinymce/plugins/mngallery/editor_plugin.js',
'res/js/tinymce/plugins/tabfocus/editor_plugin.js',
'res/js/tinymce/plugins/tabfocus/editor_plugin_src.js',
'res/js/tinymce/plugins/mnfullscreen/editor_plugin.js',
'res/js/tinymce/plugins/mnfullscreen/editor_plugin_src.js',
'res/js/tinymce/plugins/paste/editor_plugin.js',
'res/js/tinymce/plugins/paste/pasteword.htm',
'res/js/tinymce/plugins/paste/editor_plugin_src.js',
'res/js/tinymce/plugins/paste/pastetext.htm',
'res/js/tinymce/langs/mn-langs.php',
// 4.1
'res/js/jquery/ui/jquery.ui.accordion.min.js',
'res/js/jquery/ui/jquery.ui.autocomplete.min.js',
'res/js/jquery/ui/jquery.ui.button.min.js',
'res/js/jquery/ui/jquery.ui.core.min.js',
'res/js/jquery/ui/jquery.ui.datepicker.min.js',
'res/js/jquery/ui/jquery.ui.dialog.min.js',
'res/js/jquery/ui/jquery.ui.draggable.min.js',
'res/js/jquery/ui/jquery.ui.droppable.min.js',
'res/js/jquery/ui/jquery.ui.effect-blind.min.js',
'res/js/jquery/ui/jquery.ui.effect-bounce.min.js',
'res/js/jquery/ui/jquery.ui.effect-clip.min.js',
'res/js/jquery/ui/jquery.ui.effect-drop.min.js',
'res/js/jquery/ui/jquery.ui.effect-explode.min.js',
'res/js/jquery/ui/jquery.ui.effect-fade.min.js',
'res/js/jquery/ui/jquery.ui.effect-fold.min.js',
'res/js/jquery/ui/jquery.ui.effect-highlight.min.js',
'res/js/jquery/ui/jquery.ui.effect-pulsate.min.js',
'res/js/jquery/ui/jquery.ui.effect-scale.min.js',
'res/js/jquery/ui/jquery.ui.effect-shake.min.js',
'res/js/jquery/ui/jquery.ui.effect-slide.min.js',
'res/js/jquery/ui/jquery.ui.effect-transfer.min.js',
'res/js/jquery/ui/jquery.ui.effect.min.js',
'res/js/jquery/ui/jquery.ui.menu.min.js',
'res/js/jquery/ui/jquery.ui.mouse.min.js',
'res/js/jquery/ui/jquery.ui.position.min.js',
'res/js/jquery/ui/jquery.ui.progressbar.min.js',
'res/js/jquery/ui/jquery.ui.resizable.min.js',
'res/js/jquery/ui/jquery.ui.selectable.min.js',
'res/js/jquery/ui/jquery.ui.slider.min.js',
'res/js/jquery/ui/jquery.ui.sortable.min.js',
'res/js/jquery/ui/jquery.ui.spinner.min.js',
'res/js/jquery/ui/jquery.ui.tabs.min.js',
'res/js/jquery/ui/jquery.ui.tooltip.min.js',
'res/js/jquery/ui/jquery.ui.widget.min.js',
'res/js/tinymce/skins/mtaandao/images/dashicon-no-alt.png',
// 4.3
'admin/js/mn-fullscreen.js',
'admin/js/mn-fullscreen.min.js',
'res/js/tinymce/mn-mce-help.php',
'res/js/tinymce/plugins/mnfullscreen',
// 4.5
'res/theme-compat/comments-popup.php',
// 4.6
'admin/includes/class-mn-automatic-upgrader.php', // Wrong file name, see #37628.
);

/**
 * Stores new files in main to copy
 *
 * The contents of this array indicate any new bundled plugins/themes which
 * should be installed with the Mtaandao Upgrade. These items will not be
 * re-installed in future upgrades, this behaviour is controlled by the
 * introduced version present here being older than the current installed version.
 *
 * The content of this array should follow the following format:
 * Filename (relative to main) => Introduced version
 * Directories should be noted by suffixing it with a trailing slash (/)
 *
 * @since 3.2.0
 * @since 4.7.0 New themes were not automatically installed for 4.4-4.6 on
 *              upgrade. New themes are now installed again. To disable new
 *              themes from being installed on upgrade, explicitly define
 *              CORE_UPGRADE_SKIP_NEW_BUNDLED as false.
 * @global array $_new_bundled_files
 * @var array
 * @name $_new_bundled_files
 */
global $_new_bundled_files;

$_new_bundled_files = array(
	'plugins/akismet/'        => '2.0',
	'themes/twentyten/'       => '3.0',
	'themes/twentyeleven/'    => '3.2',
	'themes/twentytwelve/'    => '3.5',
	'themes/twentythirteen/'  => '3.6',
	'themes/twentyfourteen/'  => '3.8',
	'themes/twentyfifteen/'   => '4.1',
	'themes/twentysixteen/'   => '4.4',
	'themes/twentyseventeen/' => '4.7',
);

/**
 * Upgrades the core of Mtaandao.
 *
 * This will create a .maintenance file at the base of the Mtaandao directory
 * to ensure that people can not access the web site, when the files are being
 * copied to their locations.
 *
 * The files in the `$_old_files` list will be removed and the new files
 * copied from the zip file after the database is upgraded.
 *
 * The files in the `$_new_bundled_files` list will be added to the installation
 * if the version is greater than or equal to the old version being upgraded.
 *
 * The steps for the upgrader for after the new release is downloaded and
 * unzipped is:
 *   1. Test unzipped location for select files to ensure that unzipped worked.
 *   2. Create the .maintenance file in current Mtaandao base.
 *   3. Copy new Mtaandao directory over old Mtaandao files.
 *   4. Upgrade Mtaandao to new version.
 *     4.1. Copy all files/folders other than main
 *     4.2. Copy any language files to MN_LANG_DIR (which may differ from MAIN_DIR
 *     4.3. Copy any new bundled themes/plugins to their respective locations
 *   5. Delete new Mtaandao directory path.
 *   6. Delete .maintenance file.
 *   7. Remove old files.
 *   8. Delete 'update_core' option.
 *
 * There are several areas of failure. For instance if PHP times out before step
 * 6, then you will not be able to access any portion of your site. Also, since
 * the upgrade will not continue where it left off, you will not be able to
 * automatically remove old files and remove the 'update_core' option. This
 * isn't that bad.
 *
 * If the copy of the new Mtaandao over the old fails, then the worse is that
 * the new Mtaandao directory will remain.
 *
 * If it is assumed that every file will be copied over, including plugins and
 * themes, then if you edit the default theme, you should rename it, so that
 * your changes remain.
 *
 * @since 2.7.0
 *
 * @global MN_Filesystem_Base $mn_filesystem
 * @global array              $_old_files
 * @global array              $_new_bundled_files
 * @global mndb               $mndb
 * @global string             $mn_version
 * @global string             $required_php_version
 * @global string             $required_mysql_version
 *
 * @param string $from New release unzipped path.
 * @param string $to   Path to old Mtaandao installation.
 * @return MN_Error|null MN_Error on failure, null on success.
 */
function update_core($from, $to) {
	global $mn_filesystem, $_old_files, $_new_bundled_files, $mndb;

	@set_time_limit( 300 );

	/**
	 * Filters feedback messages displayed during the core update process.
	 *
	 * The filter is first evaluated after the zip file for the latest version
	 * has been downloaded and unzipped. It is evaluated five more times during
	 * the process:
	 *
	 * 1. Before Mtaandao begins the core upgrade process.
	 * 2. Before Maintenance Mode is enabled.
	 * 3. Before Mtaandao begins copying over the necessary files.
	 * 4. Before Maintenance Mode is disabled.
	 * 5. Before the database is upgraded.
	 *
	 * @since 2.5.0
	 *
	 * @param string $feedback The core update feedback messages.
	 */
	apply_filters( 'update_feedback', __( 'Verifying the unpacked files&#8230;' ) );

	// Sanity check the unzipped distribution.
	$distro = '';
	$roots = array( '/mtaandao/', '/mtaandao-mu/' );
	foreach ( $roots as $root ) {
		if ( $mn_filesystem->exists( $from . $root . 'readme.html' ) && $mn_filesystem->exists( $from . $root . 'res/version.php' ) ) {
			$distro = $root;
			break;
		}
	}
	if ( ! $distro ) {
		$mn_filesystem->delete( $from, true );
		return new MN_Error( 'insane_distro', __('The update could not be unpacked') );
	}


	/**
	 * Import $mn_version, $required_php_version, and $required_mysql_version from the new version
	 * $mn_filesystem->mn_content_dir() returned unslashed pre-2.8
	 *
	 * @global string $mn_version
	 * @global string $required_php_version
	 * @global string $required_mysql_version
	 */
	global $mn_version, $required_php_version, $required_mysql_version;

	$versions_file = trailingslashit( $mn_filesystem->mn_content_dir() ) . 'upgrade/version-current.php';
	if ( ! $mn_filesystem->copy( $from . $distro . 'res/version.php', $versions_file ) ) {
		$mn_filesystem->delete( $from, true );
		return new MN_Error( 'copy_failed_for_version_file', __( 'The update cannot be installed because we will be unable to copy some files. This is usually due to inconsistent file permissions.' ), 'res/version.php' );
	}

	$mn_filesystem->chmod( $versions_file, FS_CHMOD_FILE );
	require( MAIN_DIR . '/upgrade/version-current.php' );
	$mn_filesystem->delete( $versions_file );

	$php_version    = phpversion();
	$mysql_version  = $mndb->db_version();
	$old_mn_version = $mn_version; // The version of Mtaandao we're updating from
	$development_build = ( false !== strpos( $old_mn_version . $mn_version, '-' )  ); // a dash in the version indicates a Development release
	$php_compat     = version_compare( $php_version, $required_php_version, '>=' );
	if ( file_exists( MAIN_DIR . '/db.php' ) && empty( $mndb->is_mysql ) )
		$mysql_compat = true;
	else
		$mysql_compat = version_compare( $mysql_version, $required_mysql_version, '>=' );

	if ( !$mysql_compat || !$php_compat )
		$mn_filesystem->delete($from, true);

	if ( !$mysql_compat && !$php_compat )
		return new MN_Error( 'php_mysql_not_compatible', sprintf( __('The update cannot be installed because Mtaandao %1$s requires PHP version %2$s or higher and MySQL version %3$s or higher. You are running PHP version %4$s and MySQL version %5$s.'), $mn_version, $required_php_version, $required_mysql_version, $php_version, $mysql_version ) );
	elseif ( !$php_compat )
		return new MN_Error( 'php_not_compatible', sprintf( __('The update cannot be installed because Mtaandao %1$s requires PHP version %2$s or higher. You are running version %3$s.'), $mn_version, $required_php_version, $php_version ) );
	elseif ( !$mysql_compat )
		return new MN_Error( 'mysql_not_compatible', sprintf( __('The update cannot be installed because Mtaandao %1$s requires MySQL version %2$s or higher. You are running version %3$s.'), $mn_version, $required_mysql_version, $mysql_version ) );

	/** This filter is documented in admin/includes/update-core.php */
	apply_filters( 'update_feedback', __( 'Preparing to install the latest version&#8230;' ) );

	// Don't copy main, we'll deal with that below
	// We also copy version.php last so failed updates report their old version
	$skip = array( 'main', 'res/version.php' );
	$check_is_writable = array();

	// Check to see which files don't really need updating - only available for 3.7 and higher
	if ( function_exists( 'get_core_checksums' ) ) {
		// Find the local version of the working directory
		$working_dir_local = MAIN_DIR . '/upgrade/' . basename( $from ) . $distro;

		$checksums = get_core_checksums( $mn_version, isset( $mn_local_package ) ? $mn_local_package : 'en_US' );
		if ( is_array( $checksums ) && isset( $checksums[ $mn_version ] ) )
			$checksums = $checksums[ $mn_version ]; // Compat code for 3.7-beta2
		if ( is_array( $checksums ) ) {
			foreach ( $checksums as $file => $checksum ) {
				if ( 'main' == substr( $file, 0, 10 ) )
					continue;
				if ( ! file_exists( ABSPATH . $file ) )
					continue;
				if ( ! file_exists( $working_dir_local . $file ) )
					continue;
				if ( '.' === dirname( $file ) && in_array( pathinfo( $file, PATHINFO_EXTENSION ), array( 'html', 'txt' ) ) )
					continue;
				if ( md5_file( ABSPATH . $file ) === $checksum )
					$skip[] = $file;
				else
					$check_is_writable[ $file ] = ABSPATH . $file;
			}
		}
	}

	// If we're using the direct method, we can predict write failures that are due to permissions.
	if ( $check_is_writable && 'direct' === $mn_filesystem->method ) {
		$files_writable = array_filter( $check_is_writable, array( $mn_filesystem, 'is_writable' ) );
		if ( $files_writable !== $check_is_writable ) {
			$files_not_writable = array_diff_key( $check_is_writable, $files_writable );
			foreach ( $files_not_writable as $relative_file_not_writable => $file_not_writable ) {
				// If the writable check failed, chmod file to 0644 and try again, same as copy_dir().
				$mn_filesystem->chmod( $file_not_writable, FS_CHMOD_FILE );
				if ( $mn_filesystem->is_writable( $file_not_writable ) )
					unset( $files_not_writable[ $relative_file_not_writable ] );
			}

			// Store package-relative paths (the key) of non-writable files in the MN_Error object.
			$error_data = version_compare( $old_mn_version, '3.7-beta2', '>' ) ? array_keys( $files_not_writable ) : '';

			if ( $files_not_writable )
				return new MN_Error( 'files_not_writable', __( 'The update cannot be installed because we will be unable to copy some files. This is usually due to inconsistent file permissions.' ), implode( ', ', $error_data ) );
		}
	}

	/** This filter is documented in admin/includes/update-core.php */
	apply_filters( 'update_feedback', __( 'Enabling Maintenance mode&#8230;' ) );
	// Create maintenance file to signal that we are upgrading
	$maintenance_string = '<?php $upgrading = ' . time() . '; ?>';
	$maintenance_file = $to . '.maintenance';
	$mn_filesystem->delete($maintenance_file);
	$mn_filesystem->put_contents($maintenance_file, $maintenance_string, FS_CHMOD_FILE);

	/** This filter is documented in admin/includes/update-core.php */
	apply_filters( 'update_feedback', __( 'Copying the required files&#8230;' ) );
	// Copy new versions of MN files into place.
	$result = _copy_dir( $from . $distro, $to, $skip );
	if ( is_mn_error( $result ) )
		$result = new MN_Error( $result->get_error_code(), $result->get_error_message(), substr( $result->get_error_data(), strlen( $to ) ) );

	// Since we know the core files have copied over, we can now copy the version file
	if ( ! is_mn_error( $result ) ) {
		if ( ! $mn_filesystem->copy( $from . $distro . 'res/version.php', $to . 'res/version.php', true /* overwrite */ ) ) {
			$mn_filesystem->delete( $from, true );
			$result = new MN_Error( 'copy_failed_for_version_file', __( 'The update cannot be installed because we will be unable to copy some files. This is usually due to inconsistent file permissions.' ), 'res/version.php' );
		}
		$mn_filesystem->chmod( $to . 'res/version.php', FS_CHMOD_FILE );
	}

	// Check to make sure everything copied correctly, ignoring the contents of main
	$skip = array( 'main' );
	$failed = array();
	if ( isset( $checksums ) && is_array( $checksums ) ) {
		foreach ( $checksums as $file => $checksum ) {
			if ( 'main' == substr( $file, 0, 10 ) )
				continue;
			if ( ! file_exists( $working_dir_local . $file ) )
				continue;
			if ( '.' === dirname( $file ) && in_array( pathinfo( $file, PATHINFO_EXTENSION ), array( 'html', 'txt' ) ) ) {
				$skip[] = $file;
				continue;
			}
			if ( file_exists( ABSPATH . $file ) && md5_file( ABSPATH . $file ) == $checksum )
				$skip[] = $file;
			else
				$failed[] = $file;
		}
	}

	// Some files didn't copy properly
	if ( ! empty( $failed ) ) {
		$total_size = 0;
		foreach ( $failed as $file ) {
			if ( file_exists( $working_dir_local . $file ) )
				$total_size += filesize( $working_dir_local . $file );
		}

		// If we don't have enough free space, it isn't worth trying again.
		// Unlikely to be hit due to the check in unzip_file().
		$available_space = @disk_free_space( ABSPATH );
		if ( $available_space && $total_size >= $available_space ) {
			$result = new MN_Error( 'disk_full', __( 'There is not enough free disk space to complete the update.' ) );
		} else {
			$result = _copy_dir( $from . $distro, $to, $skip );
			if ( is_mn_error( $result ) )
				$result = new MN_Error( $result->get_error_code() . '_retry', $result->get_error_message(), substr( $result->get_error_data(), strlen( $to ) ) );
		}
	}

	// Custom Content Directory needs updating now.
	// Copy Languages
	if ( !is_mn_error($result) && $mn_filesystem->is_dir($from . $distro . 'main/languages') ) {
		if ( MN_LANG_DIR != ABSPATH . RES . '/languages' || @is_dir(MN_LANG_DIR) )
			$lang_dir = MN_LANG_DIR;
		else
			$lang_dir = MAIN_DIR . '/languages';

		if ( !@is_dir($lang_dir) && 0 === strpos($lang_dir, ABSPATH) ) { // Check the language directory exists first
			$mn_filesystem->mkdir($to . str_replace(ABSPATH, '', $lang_dir), FS_CHMOD_DIR); // If it's within the ABSPATH we can handle it here, otherwise they're out of luck.
			clearstatcache(); // for FTP, Need to clear the stat cache
		}

		if ( @is_dir($lang_dir) ) {
			$mn_lang_dir = $mn_filesystem->find_folder($lang_dir);
			if ( $mn_lang_dir ) {
				$result = copy_dir($from . $distro . 'main/languages/', $mn_lang_dir);
				if ( is_mn_error( $result ) )
					$result = new MN_Error( $result->get_error_code() . '_languages', $result->get_error_message(), substr( $result->get_error_data(), strlen( $mn_lang_dir ) ) );
			}
		}
	}

	/** This filter is documented in admin/includes/update-core.php */
	apply_filters( 'update_feedback', __( 'Disabling Maintenance mode&#8230;' ) );
	// Remove maintenance file, we're done with potential site-breaking changes
	$mn_filesystem->delete( $maintenance_file );

	// 3.5 -> 3.5+ - an empty twentytwelve directory was created upon upgrade to 3.5 for some users, preventing installation of Twenty Twelve.
	if ( '3.5' == $old_mn_version ) {
		if ( is_dir( MAIN_DIR . '/themes/twentytwelve' ) && ! file_exists( MAIN_DIR . '/themes/twentytwelve/style.css' )  ) {
			$mn_filesystem->delete( $mn_filesystem->mn_themes_dir() . 'twentytwelve/' );
		}
	}

	// Copy New bundled plugins & themes
	// This gives us the ability to install new plugins & themes bundled with future versions of Mtaandao whilst avoiding the re-install upon upgrade issue.
	// $development_build controls us overwriting bundled themes and plugins when a non-stable release is being updated
	if ( !is_mn_error($result) && ( ! defined('CORE_UPGRADE_SKIP_NEW_BUNDLED') || ! CORE_UPGRADE_SKIP_NEW_BUNDLED ) ) {
		foreach ( (array) $_new_bundled_files as $file => $introduced_version ) {
			// If a $development_build or if $introduced version is greater than what the site was previously running
			if ( $development_build || version_compare( $introduced_version, $old_mn_version, '>' ) ) {
				$directory = ('/' == $file[ strlen($file)-1 ]);
				list($type, $filename) = explode('/', $file, 2);

				// Check to see if the bundled items exist before attempting to copy them
				if ( ! $mn_filesystem->exists( $from . $distro . 'main/' . $file ) )
					continue;

				if ( 'plugins' == $type )
					$dest = $mn_filesystem->mn_plugins_dir();
				elseif ( 'themes' == $type )
					$dest = trailingslashit($mn_filesystem->mn_themes_dir()); // Back-compat, ::mn_themes_dir() did not return trailingslash'd pre-3.2
				else
					continue;

				if ( ! $directory ) {
					if ( ! $development_build && $mn_filesystem->exists( $dest . $filename ) )
						continue;

					if ( ! $mn_filesystem->copy($from . $distro . 'main/' . $file, $dest . $filename, FS_CHMOD_FILE) )
						$result = new MN_Error( "copy_failed_for_new_bundled_$type", __( 'Could not copy file.' ), $dest . $filename );
				} else {
					if ( ! $development_build && $mn_filesystem->is_dir( $dest . $filename ) )
						continue;

					$mn_filesystem->mkdir($dest . $filename, FS_CHMOD_DIR);
					$_result = copy_dir( $from . $distro . 'main/' . $file, $dest . $filename);

					// If a error occurs partway through this final step, keep the error flowing through, but keep process going.
					if ( is_mn_error( $_result ) ) {
						if ( ! is_mn_error( $result ) )
							$result = new MN_Error;
						$result->add( $_result->get_error_code() . "_$type", $_result->get_error_message(), substr( $_result->get_error_data(), strlen( $dest ) ) );
					}
				}
			}
		} //end foreach
	}

	// Handle $result error from the above blocks
	if ( is_mn_error($result) ) {
		$mn_filesystem->delete($from, true);
		return $result;
	}

	// Remove old files
	foreach ( $_old_files as $old_file ) {
		$old_file = $to . $old_file;
		if ( !$mn_filesystem->exists($old_file) )
			continue;
		$mn_filesystem->delete($old_file, true);
	}

	// Remove any Genericons example.html's from the filesystem
	_upgrade_422_remove_genericons();

	// Remove the REST API plugin if its version is Beta 4 or lower
	_upgrade_440_force_deactivate_incompatible_plugins();

	// Upgrade DB with separate request
	/** This filter is documented in admin/includes/update-core.php */
	apply_filters( 'update_feedback', __( 'Upgrading database&#8230;' ) );
	$db_upgrade_url = admin_url('upgrade.php?step=upgrade_db');
	mn_remote_post($db_upgrade_url, array('timeout' => 60));

	// Clear the cache to prevent an update_option() from saving a stale db_version to the cache
	mn_cache_flush();
	// (Not all cache back ends listen to 'flush')
	mn_cache_delete( 'alloptions', 'options' );

	// Remove working directory
	$mn_filesystem->delete($from, true);

	// Force refresh of update information
	if ( function_exists('delete_site_transient') )
		delete_site_transient('update_core');
	else
		delete_option('update_core');

	/**
	 * Fires after Mtaandao core has been successfully updated.
	 *
	 * @since 3.3.0
	 *
	 * @param string $mn_version The current Mtaandao version.
	 */
	do_action( '_core_updated_successfully', $mn_version );

	// Clear the option that blocks auto updates after failures, now that we've been successful.
	if ( function_exists( 'delete_site_option' ) )
		delete_site_option( 'auto_core_update_failed' );

	return $mn_version;
}

/**
 * Copies a directory from one location to another via the Mtaandao Filesystem Abstraction.
 * Assumes that MN_Filesystem() has already been called and setup.
 *
 * This is a temporary function for the 3.1 -> 3.2 upgrade, as well as for those upgrading to
 * 3.7+
 *
 * @ignore
 * @since 3.2.0
 * @since 3.7.0 Updated not to use a regular expression for the skip list
 * @see copy_dir()
 *
 * @global MN_Filesystem_Base $mn_filesystem
 *
 * @param string $from     source directory
 * @param string $to       destination directory
 * @param array $skip_list a list of files/folders to skip copying
 * @return mixed MN_Error on failure, True on success.
 */
function _copy_dir($from, $to, $skip_list = array() ) {
	global $mn_filesystem;

	$dirlist = $mn_filesystem->dirlist($from);

	$from = trailingslashit($from);
	$to = trailingslashit($to);

	foreach ( (array) $dirlist as $filename => $fileinfo ) {
		if ( in_array( $filename, $skip_list ) )
			continue;

		if ( 'f' == $fileinfo['type'] ) {
			if ( ! $mn_filesystem->copy($from . $filename, $to . $filename, true, FS_CHMOD_FILE) ) {
				// If copy failed, chmod file to 0644 and try again.
				$mn_filesystem->chmod( $to . $filename, FS_CHMOD_FILE );
				if ( ! $mn_filesystem->copy($from . $filename, $to . $filename, true, FS_CHMOD_FILE) )
					return new MN_Error( 'copy_failed__copy_dir', __( 'Could not copy file.' ), $to . $filename );
			}
		} elseif ( 'd' == $fileinfo['type'] ) {
			if ( !$mn_filesystem->is_dir($to . $filename) ) {
				if ( !$mn_filesystem->mkdir($to . $filename, FS_CHMOD_DIR) )
					return new MN_Error( 'mkdir_failed__copy_dir', __( 'Could not create directory.' ), $to . $filename );
			}

			/*
			 * Generate the $sub_skip_list for the subdirectory as a sub-set
			 * of the existing $skip_list.
			 */
			$sub_skip_list = array();
			foreach ( $skip_list as $skip_item ) {
				if ( 0 === strpos( $skip_item, $filename . '/' ) )
					$sub_skip_list[] = preg_replace( '!^' . preg_quote( $filename, '!' ) . '/!i', '', $skip_item );
			}

			$result = _copy_dir($from . $filename, $to . $filename, $sub_skip_list);
			if ( is_mn_error($result) )
				return $result;
		}
	}
	return true;
}

/**
 * Redirect to the About Mtaandao page after a successful upgrade.
 *
 * This function is only needed when the existing install is older than 3.4.0.
 *
 * @since 3.3.0
 *
 * @global string $mn_version
 * @global string $pagenow
 * @global string $action
 *
 * @param string $new_version
 */
function _redirect_to_about_mtaandao( $new_version ) {
	global $mn_version, $pagenow, $action;

	if ( version_compare( $mn_version, '3.4-RC1', '>=' ) )
		return;

	// Ensure we only run this on the update-core.php page. The Core_Upgrader may be used in other contexts.
	if ( 'update-core.php' != $pagenow )
		return;

 	if ( 'do-core-upgrade' != $action && 'do-core-reinstall' != $action )
 		return;

	// Load the updated default text localization domain for new strings.
	load_default_textdomain();

	// See do_core_upgrade()
	show_message( __('Mtaandao updated successfully') );

	// self_admin_url() won't exist when upgrading from <= 3.0, so relative URLs are intentional.
	show_message( '<span class="hide-if-no-js">' . sprintf( __( 'Welcome to Mtaandao %1$s. You will be redirected to the About Mtaandao screen. If not, click <a href="%2$s">here</a>.' ), $new_version, 'about.php?updated' ) . '</span>' );
	show_message( '<span class="hide-if-js">' . sprintf( __( 'Welcome to Mtaandao %1$s. <a href="%2$s">Learn more</a>.' ), $new_version, 'about.php?updated' ) . '</span>' );
	echo '</div>';
	?>
<script type="text/javascript">
window.location = 'about.php?updated';
</script>
	<?php

	// Include admin-footer.php and exit.
	include(ABSPATH . 'admin/admin-footer.php');
	exit();
}

/**
 * Cleans up Genericons example files.
 *
 * @since 4.2.2
 *
 * @global array              $mn_theme_directories
 * @global MN_Filesystem_Base $mn_filesystem
 */
function _upgrade_422_remove_genericons() {
	global $mn_theme_directories, $mn_filesystem;

	// A list of the affected files using the filesystem absolute paths.
	$affected_files = array();

	// Themes
	foreach ( $mn_theme_directories as $directory ) {
		$affected_theme_files = _upgrade_422_find_genericons_files_in_folder( $directory );
		$affected_files       = array_merge( $affected_files, $affected_theme_files );
	}

	// Plugins
	$affected_plugin_files = _upgrade_422_find_genericons_files_in_folder( PLUGIN_DIR );
	$affected_files        = array_merge( $affected_files, $affected_plugin_files );

	foreach ( $affected_files as $file ) {
		$gen_dir = $mn_filesystem->find_folder( trailingslashit( dirname( $file ) ) );
		if ( empty( $gen_dir ) ) {
			continue;
		}

		// The path when the file is accessed via MN_Filesystem may differ in the case of FTP
		$remote_file = $gen_dir . basename( $file );

		if ( ! $mn_filesystem->exists( $remote_file ) ) {
			continue;
		}

		if ( ! $mn_filesystem->delete( $remote_file, false, 'f' ) ) {
			$mn_filesystem->put_contents( $remote_file, '' );
		}
	}
}

/**
 * Recursively find Genericons example files in a given folder.
 *
 * @ignore
 * @since 4.2.2
 *
 * @param string $directory Directory path. Expects trailingslashed.
 * @return array
 */
function _upgrade_422_find_genericons_files_in_folder( $directory ) {
	$directory = trailingslashit( $directory );
	$files     = array();

	if ( file_exists( "{$directory}example.html" ) && false !== strpos( file_get_contents( "{$directory}example.html" ), '<title>Genericons</title>' ) ) {
		$files[] = "{$directory}example.html";
	}

	$dirs = glob( $directory . '*', GLOB_ONLYDIR );
	if ( $dirs ) {
		foreach ( $dirs as $dir ) {
			$files = array_merge( $files, _upgrade_422_find_genericons_files_in_folder( $dir ) );
		}
	}

	return $files;
}

/**
 * @ignore
 * @since 4.4.0
 */
function _upgrade_440_force_deactivate_incompatible_plugins() {
	if ( defined( 'REST_API_VERSION' ) && version_compare( REST_API_VERSION, '2.0-beta4', '<=' ) ) {
		deactivate_plugins( array( 'rest-api/plugin.php' ), true );
	}
}
