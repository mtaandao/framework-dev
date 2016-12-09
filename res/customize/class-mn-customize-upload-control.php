<?php
/**
 * Customize API: MN_Customize_Upload_Control class
 *
 * @package Mtaandao
 * @subpackage Customize
 * @since 4.4.0
 */

/**
 * Customize Upload Control Class.
 *
 * @since 3.4.0
 *
 * @see MN_Customize_Media_Control
 */
class MN_Customize_Upload_Control extends MN_Customize_Media_Control {
	public $type          = 'upload';
	public $mime_type     = '';
	public $button_labels = array();
	public $removed = ''; // unused
	public $context; // unused
	public $extensions = array(); // unused

	/**
	 * Refresh the parameters passed to the JavaScript via JSON.
	 *
	 * @since 3.4.0
	 *
	 * @uses MN_Customize_Media_Control::to_json()
	 */
	public function to_json() {
		parent::to_json();

		$value = $this->value();
		if ( $value ) {
			// Get the attachment model for the existing file.
			$attachment_id = attachment_url_to_postid( $value );
			if ( $attachment_id ) {
				$this->json['attachment'] = mn_prepare_attachment_for_js( $attachment_id );
			}
		}
	}
}
