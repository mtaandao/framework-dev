(function() {
	tinymce.create('tinymce.plugins.Addshortcodes', {
		init : function(ed, url) {
		
			//Add Sivi shortcodes button
			ed.addButton('Sivi', {
				title : 'Add Sivi shortcodes',
				cmd : 'alc_sivi',
				image : url + '/images/sivi.png'
			});
			ed.addCommand('alc_sivi', function() {
				ed.windowManager.open({file : url + '/ui.php?page=sivi',  width : 604 ,	height : 520 ,	inline : 1});
			});	
		},
		getInfo : function() {
			return {
				longname : "Weblusive Shortcodes",
				author : 'Weblusive',
				authorurl : 'http://www.weblusive.com/',
				infourl : 'http://www.weblusive.com/',
				version : "1.0"
			};
		}
	});
	tinymce.PluginManager.add('SiviShortcodes', tinymce.plugins.Addshortcodes);	
	
})();

