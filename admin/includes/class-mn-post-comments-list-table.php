<?php
/**
 * List Table API: MN_Post_Comments_List_Table class
 *
 * @package Mtaandao
 * @subpackage Administration
 * @since 4.4.0
 */

/**
 * Core class used to implement displaying post comments in a list table.
 *
 * @since 3.1.0
 * @access private
 *
 * @see MN_Comments_List_Table
 */
class MN_Post_Comments_List_Table extends MN_Comments_List_Table {

	/**
	 *
	 * @return array
	 */
	protected function get_column_info() {
		return array(
			array(
				'author'   => __( 'Author' ),
				'comment'  => _x( 'Comment', 'column name' ),
			),
			array(),
			array(),
			'comment',
		);
	}

	/**
	 *
	 * @return array
	 */
	protected function get_table_classes() {
		$classes = parent::get_table_classes();
		$classes[] = 'mn-list-table';
		$classes[] = 'comments-box';
		return $classes;
	}

	/**
	 *
	 * @param bool $output_empty
	 */
	public function display( $output_empty = false ) {
		$singular = $this->_args['singular'];

		mn_nonce_field( "fetch-list-" . get_class( $this ), '_ajax_fetch_list_nonce' );
?>
<table class="<?php echo implode( ' ', $this->get_table_classes() ); ?>" style="display:none;">
	<tbody id="the-comment-list"<?php
		if ( $singular ) {
			echo " data-mn-lists='list:$singular'";
		} ?>>
		<?php if ( ! $output_empty ) {
			$this->display_rows_or_placeholder();
		} ?>
	</tbody>
</table>
<?php
	}

	/**
	 *
	 * @param bool $comment_status
	 * @return int
	 */
	public function get_per_page( $comment_status = false ) {
		return 10;
	}
}
