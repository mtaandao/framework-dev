<?php
/**
 * Helper functions for displaying a list of items in an ajaxified HTML table.
 *
 * @package Mtaandao
 * @subpackage List_Table
 * @since 3.1.0
 */

/**
 * Fetch an instance of a MN_List_Table class.
 *
 * @access private
 * @since 3.1.0
 *
 * @global string $hook_suffix
 *
 * @param string $class The type of the list table, which is the class name.
 * @param array $args Optional. Arguments to pass to the class. Accepts 'screen'.
 * @return object|bool Object on success, false if the class does not exist.
 */
function _get_list_table( $class, $args = array() ) {
	$core_classes = array(
		//Site Admin
		'MN_Posts_List_Table' => 'posts',
		'MN_Media_List_Table' => 'media',
		'MN_Terms_List_Table' => 'terms',
		'MN_Users_List_Table' => 'users',
		'MN_Comments_List_Table' => 'comments',
		'MN_Post_Comments_List_Table' => array( 'comments', 'post-comments' ),
		'MN_Links_List_Table' => 'links',
		'MN_Plugin_Install_List_Table' => 'plugin-install',
		'MN_Themes_List_Table' => 'themes',
		'MN_Theme_Install_List_Table' => array( 'themes', 'theme-install' ),
		'MN_Plugins_List_Table' => 'plugins',
		// Network Admin
		'MN_MS_Sites_List_Table' => 'ms-sites',
		'MN_MS_Users_List_Table' => 'ms-users',
		'MN_MS_Themes_List_Table' => 'ms-themes',
	);

	if ( isset( $core_classes[ $class ] ) ) {
		foreach ( (array) $core_classes[ $class ] as $required )
			require_once( ABSPATH . 'admin/includes/class-mn-' . $required . '-list-table.php' );

		if ( isset( $args['screen'] ) )
			$args['screen'] = convert_to_screen( $args['screen'] );
		elseif ( isset( $GLOBALS['hook_suffix'] ) )
			$args['screen'] = get_current_screen();
		else
			$args['screen'] = null;

		return new $class( $args );
	}

	return false;
}

/**
 * Register column headers for a particular screen.
 *
 * @since 2.7.0
 *
 * @param string $screen The handle for the screen to add help to. This is usually the hook name returned by the add_*_page() functions.
 * @param array $columns An array of columns with column IDs as the keys and translated column names as the values
 * @see get_column_headers(), print_column_headers(), get_hidden_columns()
 */
function register_column_headers($screen, $columns) {
	new _MN_List_Table_Compat( $screen, $columns );
}

/**
 * Prints column headers for a particular screen.
 *
 * @since 2.7.0
 *
 * @param string|MN_Screen $screen  The screen hook name or screen object.
 * @param bool             $with_id Whether to set the id attribute or not.
 */
function print_column_headers( $screen, $with_id = true ) {
	$mn_list_table = new _MN_List_Table_Compat($screen);

	$mn_list_table->print_column_headers( $with_id );
}
