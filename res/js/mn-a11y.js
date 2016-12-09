window.mn = window.mn || {};

( function ( mn, $ ) {
	'use strict';

	var $containerPolite,
		$containerAssertive;

	/**
	 * Update the ARIA live notification area text node.
	 *
	 * @since 4.2.0
	 * @since 4.3.0 Introduced the 'ariaLive' argument.
	 *
	 * @param {String} message  The message to be announced by Assistive Technologies.
	 * @param {String} ariaLive Optional. The politeness level for aria-live. Possible values:
	 *                          polite or assertive. Default polite.
	 */
	function speak( message, ariaLive ) {
		// Clear previous messages to allow repeated strings being read out.
		clear();

		// Ensure only text is sent to screen readers.
		message = $( '<p>' ).html( message ).text();

		if ( $containerAssertive && 'assertive' === ariaLive ) {
			$containerAssertive.text( message );
		} else if ( $containerPolite ) {
			$containerPolite.text( message );
		}
	}

	/**
	 * Build the live regions markup.
	 *
	 * @since 4.3.0
	 *
	 * @param {String} ariaLive Optional. Value for the 'aria-live' attribute, default 'polite'.
	 *
	 * @return {Object} $container The ARIA live region jQuery object.
	 */
	function addContainer( ariaLive ) {
		ariaLive = ariaLive || 'polite';

		var $container = $( '<div>', {
			'id': 'mn-a11y-speak-' + ariaLive,
			'aria-live': ariaLive,
			'aria-relevant': 'additions text',
			'aria-atomic': 'true',
			'class': 'screen-reader-text mn-a11y-speak-region'
		});

		$( document.body ).append( $container );
		return $container;
	}

	/**
	 * Clear the live regions.
	 *
	 * @since 4.3.0
	 */
	function clear() {
		$( '.mn-a11y-speak-region' ).text( '' );
	}

	/**
	 * Initialize mn.a11y and define ARIA live notification area.
	 *
	 * @since 4.2.0
	 * @since 4.3.0 Added the assertive live region.
	 */
	$( document ).ready( function() {
		$containerPolite = $( '#mn-a11y-speak-polite' );
		$containerAssertive = $( '#mn-a11y-speak-assertive' );

		if ( ! $containerPolite.length ) {
			$containerPolite = addContainer( 'polite' );
		}

		if ( ! $containerAssertive.length ) {
			$containerAssertive = addContainer( 'assertive' );
		}
	});

	mn.a11y = mn.a11y || {};
	mn.a11y.speak = speak;

}( window.mn, window.jQuery ));
