(function($) {

// Object for creating Mtaandao 3.5 media upload menu 
// for selecting theme images.
mn.media.shibaMediaManager = {
     
    init: function() {
        // Create the media frame.
        this.frame = mn.media.frames.shibaMediaManager = mn.media({
            title: 'Choose Image',
            library: {
                type: 'image'
            },
            button: {
                text: 'Insert into skin',
            }
        });
		
		// When an image is selected, run a callback.
		this.frame.on( 'select', function() {
			// Grab the selected attachment.
			var attachment = mn.media.shibaMediaManager.frame.state().get('selection').first(),
				controllerName = mn.media.shibaMediaManager.$el.data('controller');
			 
			controller = mn.customize.control.instance(controllerName);
			controller.thumbnailSrc(attachment.attributes.url);
			controller.setting.set(attachment.attributes.url);
		});
         
        $('.choose-from-library-link').click( function( event ) {
			
            mn.media.shibaMediaManager.$el = $(this);
            var controllerName = $(this).data('controller');
            event.preventDefault();
 
            mn.media.shibaMediaManager.frame.open();
        });
         
    } // end init
}; // end shibaMediaManager
 
mn.media.shibaMediaManager.init();
 
}(jQuery));