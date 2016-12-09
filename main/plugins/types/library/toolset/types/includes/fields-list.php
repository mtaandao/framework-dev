<?php
/*
 * Fields and groups list functions
 *
 *
 */

/**
 * Renders 'widefat' table.
 */
function mncf_admin_fields_list()
{
    include_once dirname(__FILE__).'/classes/class.mncf.custom.fields.list.table.php';
    //Create an instance of our package class...
    $listTable = new MNCF_Custom_Fields_List_Table();
    //Fetch, prepare, sort, and filter our data...
    $listTable->prepare_items();
    ?>
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="cf-filter" method="post">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']); ?>" />
            <?php $listTable->search_box(__('Search Post Field Groups', 'mncf'), 'search_id'); ?>
            <!-- Now we can render the completed list table -->
            <?php $listTable->display() ?>
        </form>
    <?php
    do_action('mncf_groups_list_table_after');
}

/**
 * Action after group list.
 *
 * This access allow to add something after group list
 *
 * @since 1.8.0
 *
 */
add_action('mncf_admin_footer_mncf-cf', 'mncf_admin_fields_list_metabox_to_custom_fields_control');

/**
 * Show link to Control Custom Field
 *
 * @since 1.8.0
 *
 */
function mncf_admin_fields_list_metabox_to_custom_fields_control()
{
    $form['table-1-open'] = array(
        '#type' => 'markup',
        '#markup' => '<table class="mncf-types-form-table widefat js-mncf-slugize-container"><thead><tr><th>' . __( 'Post Field Control', 'mncf' ) . '</th></tr></thead><tbody>',
        '_builtin' => true,
    );
    $form['table-row-1-open'] = array(
        '#type' => 'markup',
        '#markup' => '<tr><td>',
        '_builtin' => true,
    );

    $form['table-row-1-content-1'] = array(
        '#type' => 'markup',
        '#markup' => '<p>'.__('You can control Post Fields by removing them from the groups, changing type or just deleting.', 'mncf'),
        '_builtin' => true,
    );

    $form['table-row-1-content-2'] = array(
        '#type' => 'markup',
        '#markup' => sprintf(
            ' <a class="button" href="%s">%s</a></p>',
	        Types_Page_Field_Control::get_page_url( Types_Field_Utils::DOMAIN_POSTS ),
            __('Post Field Control', 'mncf')
        ),
        '_builtin' => true,
    );

    $form['table-row-1-close'] = array(
        '#type' => 'markup',
        '#markup' => '</td></tr>',
        '_builtin' => true,
    );
    $form['table-1-close'] = array(
        '#type' => 'markup',
        '#markup' => '</tbody></table>',
        '_builtin' => true,
    );
    $form = mncf_form( __FUNCTION__, $form );
    echo $form->renderForm();

}

