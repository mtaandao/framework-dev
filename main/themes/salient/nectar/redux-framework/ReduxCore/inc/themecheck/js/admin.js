(function( $ ) {
	"use strict";

	$(function() {

		$('#theme-check > h2').html( $('#theme-check > h2').html() + ' with Redux Theme-Check' );

		if ( typeof redux_check_intro !== 'undefined' ) {
			$('#theme-check .theme-check').append( redux_check_intro.text );
		}
		$('#theme-check form' ).append('&nbsp;&nbsp;<input name="redux_mnorg" type="checkbox">  Extra MN.org Requirements.');
	});

}(jQuery));
