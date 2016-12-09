<?php
/**
 * General settings administration panel.
 *
 * @package Mtaandao
 * @subpackage Administration
 */

/** Mtaandao Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

/** Mtaandao Translation Install API */
require_once( ABSPATH . 'admin/includes/translation-install.php' );

if ( ! current_user_can( 'manage_options' ) )
	mn_die( __( 'Sorry, you are not allowed to manage options for this site.' ) );

$title = __('Database Settings');
$parent_file = 'options-general.php';
/* translators: date and time format for exact current time, mainly about timezones, see https://secure.php.net/date */
$timezone_format = _x('Y-m-d H:i:s', 'timezone date format');

add_action('admin_head', 'options_general_add_js');

$options_help = '<p>' . __('This page is for adminstering your wordpress database from within your Mtaandao installation.') . '</p>' .
	'<p>' . __('Most themes display the site title at the top of every page, in the title bar of the browser, and as the identifying name for syndicated feeds. The tagline is also displayed by many themes.') . '</p>';

if ( ! is_multisite() ) {
	$options_help .= '<p>' . __('You can manage sevral databases fro here since it allows for serverwide login') . '</p>' ;
}

get_current_screen()->add_help_tab( array(
	'id'      => 'overview',
	'title'   => __('Overview'),
	'content' => $options_help,
) );

get_current_screen()->set_help_sidebar(
	'<p><strong>' . __('For more information:') . '</strong></p>' .
	'<p>' . __('<a href="https://mtaandao.github.io/Settings_General_Screen">Documentation on General Settings</a>') . '</p>' .
	'<p>' . __('<a href="https://mtaandao.co.ke/support/">Support Forums</a>') . '</p>'
);

include( ABSPATH . 'admin/admin-header.php' );
include( ABSPATH . 'admin/config/db.php' );
?>
<div class="wrap">
<h1><?php echo esc_html( $title ); ?></h1>
<style>
table {
    font-family: arial, sans-serif;
    border-collapse: collapse;
    width: 100%;
}

td, th {
    border: 1px solid #dddddd;
    text-align: left;
    padding: 8px;
}

tr:nth-child(even) {
    background-color: #dddddd;
}
</style>
<div id="database-options">
<table>
  <tr>
    <th>Server</th>
    <th>Database Name</th>
    <th>Database Password</th>
    <th>Database Password</th>
  </tr>
  <tr>
    <td><?php echo  DB_HOST ; ?></td>
    <td><?php echo  DB_USER ; ?></td>
    <td><?php echo  DB_NAME ; ?></td>
    <td><?php echo DB_PASSWORD ; ?></td>
  </tr>
</table>
<div id="login_error" class="headline-feature feature-video">
<iframe width="100%" height="1000px" src="database-admin.php") frameborder="0" allowfullscreen="yes">	
</iframe>
</div>
</div>
</div>

<?php include( ABSPATH . 'admin/admin-footer.php' ); ?>