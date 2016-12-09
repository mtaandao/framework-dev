<?php
/**
 * Customize API: MN_Customize_Background_Image_Setting class
 *
 * @package Mtaandao
 * @subpackage Customize
 * @since 4.4.0
 */

/**
 * Customizer Background Image Setting class.
 *
 * @since 3.4.0
 *
 * @see MN_Customize_Setting
 */
final class MN_Customize_Background_Image_Setting extends MN_Customize_Setting {
	public $id = 'background_image_thumb';

	/**
	 * @since 3.4.0
	 *
	 * @param $value
	 */
	public function update( $value ) {
		remove_theme_mod( 'background_image_thumb' );
	}
}
