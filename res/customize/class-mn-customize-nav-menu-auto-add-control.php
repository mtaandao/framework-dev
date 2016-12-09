<?php
/**
 * Customize API: MN_Customize_Nav_Menu_Auto_Add_Control class
 *
 * @package Mtaandao
 * @subpackage Customize
 * @since 4.4.0
 */

/**
 * Customize control to represent the auto_add field for a given menu.
 *
 * @since 4.3.0
 *
 * @see MN_Customize_Control
 */
class MN_Customize_Nav_Menu_Auto_Add_Control extends MN_Customize_Control {

	/**
	 * Type of control, used by JS.
	 *
	 * @since 4.3.0
	 * @access public
	 * @var string
	 */
	public $type = 'nav_menu_auto_add';

	/**
	 * No-op since we're using JS template.
	 *
	 * @since 4.3.0
	 * @access protected
	 */
	protected function render_content() {}

	/**
	 * Render the Underscore template for this control.
	 *
	 * @since 4.3.0
	 * @access protected
	 */
	protected function content_template() {
		?>
		<span class="customize-control-title"><?php _e( 'Menu Options' ); ?></span>
		<label>
			<input type="checkbox" class="auto_add" />
			<?php _e( 'Automatically add new top-level pages to this menu' ); ?>
		</label>
		<?php
	}
}
