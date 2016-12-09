jQuery(function($){

	var frame = mn.media({
		title: 'Pick an Image for This Slide',
		multiple: false,
		library: { type: 'image' },
		button: { text: 'Use This Image' }
	});

	frame.on('open', function(){
		if ( typeof frame.currID == 'undefined' || isNaN(frame.currID) || frame.currID < 1 ) return;

		var selection = frame.state().get('selection');
		attachment = mn.media.attachment(frame.currID);
		attachment.fetch();
		selection.add( attachment ? [ attachment ] : [] );

		return;
	});

	frame.on('close', function(){
		if ( !('currSlide' in frame) ) return;

		var $thisSlide = $('#' + frame.currSlide),
		    selection = frame.state().get('selection').first(),
		    attr = typeof selection != 'undefined' && typeof selection.attributes != 'undefined' ? selection.attributes : {},
		    id = 'id' in attr && !isNaN(attr.id) && attr.id > 0 ? attr.id : '',
		    url = 'sizes' in attr && 'medium' in attr.sizes && 'url' in attr.sizes.medium ? $.trim(attr.sizes.medium.url) : '';

		if ( url == '' ) url = 'sizes' in attr && 'full' in attr.sizes && 'url' in attr.sizes.full ? $.trim(attr.sizes.full.url) : '';

		$('.mtaa_slide_upload_image', $thisSlide).val(id);
		$('.mtaa_slide_preview_image', $thisSlide).attr('src', url != '' ? url : $('.mtaa_slide_preview_image', $thisSlide).data('defaultimg'));

		$('.mtaa_slide_clear_image_button', $thisSlide).removeClass('button-disabled');
		if ( $.trim($('.mtaa_slide_upload_image', $thisSlide).val()) == '' && !$('.mtaa_slide_clear_image_button', $thisSlide).hasClass('button-disabled') )
			$('.mtaa_slide_clear_image_button', $thisSlide).addClass('button-disabled');

		frame.reset();

		return;
	});

	$('.mtaa_slide_upload_image_button').click(function(e){
		e.preventDefault();

		var id = parseInt($(this).prev('.mtaa_slide_upload_image').val());
		frame.currID = !isNaN(id) && id > 0 ? id : 0;
		frame.currSlide = $(this).closest('li').attr('id');

		frame.open();

		return;
	});

	$('.mtaa_slide_clear_image_button').click(function(e){
		e.preventDefault();

		if ( $(this).hasClass('button-disabled') ) return;

		$('.mtaa_slide_upload_image', $(this).closest('.mtaa_slide_preview')).val('');
		$('.mtaa_slide_preview_image', $(this).closest('.mtaa_slide_preview')).attr('src', $('.mtaa_slide_preview_image', $(this).closest('.mtaa_slide_preview')).data('defaultimg'));

		if ( !$(this).hasClass('button-disabled') ) $(this).addClass('button-disabled');

		return;
	});
	
	var mnzSlideEmbedInputTimeout,
	    mnzValidIframeRegex = /<iframe[^>]* src="[^"]+"[^>]*><\/iframe>/i; // This isn't super strict... It just loosely checks to see if the string kinda looks like it contains an embed code.

	$('.mtaa_slide_embed_code').on('input', function(){
		clearTimeout(mnzSlideEmbedInputTimeout);

		var thisVal = $(this).val(),
		    $thisParent = $(this).closest('.mtaa_slide_preview');

		if ( $.trim(thisVal) != '' && mnzValidIframeRegex.test(thisVal) ) {

			mnzSlideEmbedInputTimeout = setTimeout(function(){
				$.ajax({
					url: ajaxurl,
					type: 'post',
					data: { action: 'mtaa_sliderthumb_get', mtaa_sliderthumb_embedcode: thisVal, mtaa_sliderthumb_postid: $('#post_ID').val() },
					dataType: 'json',
					success: function(response) {
						if (response.success && response.data.thumb_url) {
							$thisParent.css('background-image', 'url(' + response.data.thumb_url + ')');
						} else {
							$thisParent.removeAttr('style');
						}
					},
					error: function() {
						$thisParent.removeAttr('style');
					}
				});

				return;
			}, 1000);

		} else {

			mnzSlideEmbedInputTimeout = setTimeout(function(){ $thisParent.removeAttr('style'); }, 1000);

		}
	});

	$('.mtaa_slide_add').click(function(e){
		e.preventDefault();

		var $lastSlide = $('.mtaa_slider li:last', $(this).closest('.inside')),
		    $newSlide = $lastSlide.clone(true);

		function incrementNew(index, name) {
			return name.replace(/(\d+)/, function(fullMatch, n) {
				return Number(n) + 1;
			});
		}

		$newSlide.attr('id', incrementNew).removeClass('image video').addClass('image');
		$('input, textarea', $newSlide).val('').attr('name', incrementNew);
		$('.mtaa_slide_type_input', $newSlide).val('image');

		$('.mtaa_slide_preview', $newSlide).removeAttr('style');
		$('.mtaa_slide_preview_image', $newSlide).attr('src', $('.mtaa_slide_preview_image', $newSlide).data('defaultimg'));

		if ( !$('.mtaa_slide_clear_image_button', $newSlide).hasClass('button-disabled') ) $('.mtaa_slide_clear_image_button', $newSlide).addClass('button-disabled');

		$newSlide.insertAfter($lastSlide);

		if ( $('.mtaa_slider li').length > 1 ) $('.mtaa_slider').removeClass('onlyone');

		return;
	});

	$('.mtaa_slide_type_image, .mtaa_slide_type_video').click(function(e){
		e.preventDefault();

		var $li = $(this).closest('li').removeClass('image video');

		if ( $(this).hasClass('mtaa_slide_type_image') ) {

			$li.addClass('image');
			$('.mtaa_slide_type_input', $(this).closest('.mtaa_slide_type')).val('image');

		} else if ( $(this).hasClass('mtaa_slide_type_video') ) {

			$li.addClass('video');
			$('.mtaa_slide_type_input', $(this).closest('.mtaa_slide_type')).val('video');

		}

		return;
	});

	$('.mtaa_slide_remove').click(function(e){
		e.preventDefault();

		$(this).parent().remove();

		if ( $('.mtaa_slider li').length <= 1 ) $('.mtaa_slider').addClass('onlyone');

		return;
	});

	$('.mtaa_slider').sortable({
		opacity: 0.6,
		revert: true,
		cursor: 'move',
		handle: '.sort'
	});

});