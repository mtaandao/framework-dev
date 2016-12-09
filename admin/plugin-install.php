<?php
/**
 * Install plugin administration panel.
 *
 * @package Mtaandao
 * @subpackage Administration
 */
// TODO route this pages via a specific iframe handler instead of the do_action below
if ( !defined( 'IFRAME_REQUEST' ) && isset( $_GET['tab'] ) && ( 'plugin-information' == $_GET['tab'] ) )
	define( 'IFRAME_REQUEST', true );

/**
 * Mtaandao Administration Bootstrap.
 */
require_once( dirname( __FILE__ ) . '/admin.php' );

if ( ! current_user_can('install_plugins') )
	mn_die(__('Sorry, you are not allowed to install plugins on this site.'));

if ( is_multisite() && ! is_network_admin() ) {
	mn_redirect( network_admin_url( 'plugin-install.php' ) );
	exit();
}

$mn_list_table = _get_list_table('MN_Plugin_Install_List_Table');
$pagenum = $mn_list_table->get_pagenum();

if ( ! empty( $_REQUEST['_mn_http_referer'] ) ) {
	$location = remove_query_arg( '_mn_http_referer', mn_unslash( $_SERVER['REQUEST_URI'] ) );

	if ( ! empty( $_REQUEST['paged'] ) ) {
		$location = add_query_arg( 'paged', (int) $_REQUEST['paged'], $location );
	}

	mn_redirect( $location );
	exit;
}

$mn_list_table->prepare_items();

$total_pages = $mn_list_table->get_pagination_arg( 'total_pages' );

if ( $pagenum > $total_pages && $total_pages > 0 ) {
	mn_redirect( add_query_arg( 'paged', $total_pages ) );
	exit;
}

$title = __( 'Add Plugins' );
$parent_file = 'plugins.php';

mn_enqueue_script( 'plugin-install' );
if ( 'plugin-information' != $tab )
	add_thickbox();

$body_id = $tab;

mn_enqueue_script( 'updates' );

/**
 * Fires before each tab on the Install Plugins screen is loaded.
 *
 * The dynamic portion of the action hook, `$tab`, allows for targeting
 * individual tabs, for instance 'install_plugins_pre_plugin-information'.
 *
 * @since 2.7.0
 */
do_action( "install_plugins_pre_{$tab}" );

/*
 * Call the pre upload action on every non-upload plugin install screen
 * because the form is always displayed on these screens.
 */
if ( 'upload' !== $tab ) {
	/** This action is documented in admin/plugin-install.php */
	do_action( 'install_plugins_pre_upload' );
}

get_current_screen()->add_help_tab( array(
'id'		=> 'overview',
'title'		=> __('Overview'),
'content'	=>
	'<p>' . sprintf( __('Plugins hook into Mtaandao to extend its functionality with custom features. Plugins are developed independently from the core Mtaandao application by thousands of developers all over the world. All plugins in the official <a href="%s">Mtaandao Plugin Directory</a> are compatible with the license Mtaandao uses.' ), __( 'https://mtaandao.co.ke/plugins/' ) ) . '</p>' .
	'<p>' . __( 'You can find new plugins to install by searching or browsing the directory right here in your own Plugins section.' ) . ' <span id="live-search-desc" class="hide-if-no-js">' . __( 'The search results will be updated as you type.' ) . '</span></p>'

) );
get_current_screen()->add_help_tab( array(
'id'		=> 'adding-plugins',
'title'		=> __('Adding Plugins'),
'content'	=>
	'<p>' . __('If you know what you&#8217;re looking for, Search is your best bet. The Search screen has options to search the Mtaandao Plugin Directory for a particular Term, Author, or Tag. You can also search the directory by selecting popular tags. Tags in larger type mean more plugins have been labeled with that tag.') . '</p>' .
	'<p>' . __( 'If you just want to get an idea of what&#8217;s available, you can browse Featured and Popular plugins by using the links above the plugins list. These sections rotate regularly.' ) . '</p>' .
	'<p>' . __( 'You can also browse a user&#8217;s favorite plugins, by using the Favorites link above the plugins list and entering their Mtaandao.org username.' ) . '</p>' .
	'<p>' . __( 'If you want to install a plugin that you&#8217;ve downloaded elsewhere, click the Upload Plugin button above the plugins list. You will be prompted to upload the .zip package, and once uploaded, you can activate the new plugin.' ) . '</p>'
) );

get_current_screen()->set_help_sidebar(
	'<p><strong>' . __('For more information:') . '</strong></p>' .
	'<p>' . __('<a href="https://mtaandao.github.io/Plugins_Add_New_Screen">Documentation on Installing Plugins</a>') . '</p>' .
	'<p>' . __('<a href="https://mtaandao.co.ke/support/">Support Forums</a>') . '</p>'
);

get_current_screen()->set_screen_reader_content( array(
	'heading_views'      => __( 'Filter plugins list' ),
	'heading_pagination' => __( 'Plugins list navigation' ),
	'heading_list'       => __( 'Plugins list' ),
) );

/**
 * Mtaandao Administration Template Header.
 */
include(ABSPATH . 'admin/admin-header.php');
?>
<div class="wrap <?php echo esc_attr( "plugin-install-tab-$tab" ); ?>">
<h1>
	<?php
	echo esc_html( $title );
	if ( ! empty( $tabs['upload'] ) && current_user_can( 'upload_plugins' ) ) {
		printf( ' <a href="%s" class="upload-view-toggle page-title-action"><span class="upload">%s</span><span class="browse">%s</span></a>',
			( 'upload' === $tab ) ? self_admin_url( 'plugin-install.php' ) : self_admin_url( 'plugin-install.php?tab=upload' ),
			__( 'Upload Plugin' ),
			__( 'Browse Plugins' )
		);
	}
	?>
</h1>

<?php
/*
 * Output the upload plugin form on every non-upload plugin install screen, so it can be
 * displayed via JavaScript rather then opening up the devoted upload plugin page.
 */
if ( 'upload' !== $tab ) {
	?>
	<div class="upload-plugin-wrap">
		<?php
		/** This action is documented in admin/plugin-install.php */
		do_action( 'install_plugins_upload' );
		?>
	</div>
	<?php
	$mn_list_table->views();
	echo '<br class="clear" />';
}

/**
 * Fires after the plugins list table in each tab of the Install Plugins screen.
 *
 * The dynamic portion of the action hook, `$tab`, allows for targeting
 * individual tabs, for instance 'install_plugins_plugin-information'.
 *
 * @since 2.7.0
 *
 * @param int $paged The current page number of the plugins list table.
 */
do_action( "install_plugins_{$tab}", $paged ); ?>

	<span class="spinner"></span>
</div>

<?php
mn_print_request_filesystem_credentials_modal();
mn_print_admin_notice_templates();

/**
 * Mtaandao Administration Template Footer.
 */
include(ABSPATH . 'admin/admin-footer.php');
