<?php
/**
 * Media Library view of all avatars in use.
 *
 * @package User Avatar
 * @version 1.9.13
 */

/**
 * @since 1.8
 * @uses object $mnua_admin
 * @uses _mnua_get_list_table()
 * @uses add_query_arg()
 * @uses check_admin_referer()
 * @uses current_action()
 * @uses current_user_can()
 * @uses display()
 * @uses esc_url()
 * @uses find_posts_div()
 * @uses get_pagenum()
 * @uses get_search_query
 * @uses number_format_i18n()
 * @uses prepare_items()
 * @uses remove_query_arg()
 * @uses search_box()
 * @uses views()
 * @uses mn_delete_attachment()
 * @uses mn_die()
 * @uses mn_enqueue_script()
 * @uses mn_get_referer()
 * @uses mn_redirect()
 * @uses mn_unslash()
 */

  /** Mtaandao Administration Bootstrap */
  require_once(ABSPATH.'admin/admin.php');

  if(!current_user_can('upload_files'))
    mn_die(__('You do not have permission to upload files.','mn-user-avatar'));

  global $mnua_admin;

  $mn_list_table = $mnua_admin->_mnua_get_list_table('MN_User_Avatar_List_Table');
  $pagenum = $mn_list_table->get_pagenum();

  // Handle bulk actions
  $doaction = $mn_list_table->current_action();

  if($doaction) {
    check_admin_referer('bulk-media');

    if(isset($_REQUEST['media'])) {
      $post_ids = $_REQUEST['media'];
    } elseif(isset($_REQUEST['ids'])) {
      $post_ids = explode(',', $_REQUEST['ids']);
    }

    $location = esc_url(add_query_arg(array('page' => 'mn-user-avatar-library'), 'admin.php'));
    if($referer = mn_get_referer()) {
      if(false !== strpos($referer, 'admin.php')) {
        $location = remove_query_arg(array('trashed', 'untrashed', 'deleted', 'message', 'ids', 'posted'), $referer);
      }
    }
    switch($doaction) {
      case 'delete':
        if(!isset($post_ids)) {
          break;
        }
        foreach((array) $post_ids as $post_id_del) {
          if(!current_user_can('delete_post', $post_id_del)) {
            mn_die(__('You are not allowed to delete this post.','mn-user-avatar'));
          }
          if(!mn_delete_attachment($post_id_del)) {
            mn_die(__('Error in deleting.','mn-user-avatar'));
          }
        }
      $location = esc_url_raw(add_query_arg('deleted', count($post_ids), $location));
      break;
    }
    mn_redirect($location);
    exit;
  } elseif(!empty($_GET['_mn_http_referer'])) {
    mn_redirect(remove_query_arg(array('_mn_http_referer', '_mnnonce'), mn_unslash($_SERVER['REQUEST_URI'])));
    exit;
  }
  $mn_list_table->prepare_items();
  mn_enqueue_script('mn-ajax-response');
  mn_enqueue_script('jquery-ui-draggable');
  mn_enqueue_script('media');
?>
<div class="wrap">
  <h2>
    <?php _e('Avatars','mn-user-avatar');
      if(!empty($_REQUEST['s'])) {
        printf('<span class="subtitle">'.__('Search results for &#8220;%s&#8221;','mn-user-avatar').'</span>', get_search_query());
      }
    ?>
  </h2>
  <?php
    $message = "";
    if(!empty($_GET['deleted']) && $deleted = absint($_GET['deleted'])) {
      $message = sprintf(_n('Media attachment permanently deleted.', '%d media attachments permanently deleted.', $deleted), number_format_i18n($_GET['deleted']));
      $_SERVER['REQUEST_URI'] = remove_query_arg(array('deleted'), $_SERVER['REQUEST_URI']);
    }
    if(!empty($message)) : ?>
    <div id="message" class="updated"><p><?php echo $message; ?></p></div>
  <?php endif; ?>
  <?php $mn_list_table->views(); ?>
  <form id="posts-filter" action="" method="get">
    <?php $mn_list_table->search_box(__('Search','mn-user-avatar'), 'media'); ?>
    <?php $mn_list_table->display(); ?>
    <div id="ajax-response"></div>
    <?php find_posts_div(); ?>
    <br class="clear" />
  </form>
</div>
