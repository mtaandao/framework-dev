<div id="comments">
<?php if ( post_password_required() ) : ?>
	<p class="nopassword"><?php _e( 'This post is password protected. Enter the password to view any comments.', 'mtaa' ); ?></p>
 <?php
		/* Stop the rest of comments.php from being processed,
		 * but don't kill the script entirely -- we still have
		 * to fully load the template.
		 */
		return;
	endif;
?>

<?php
	// You can start editing here -- including this comment!
?>

<?php if ( have_comments() ) : ?>

	<h3><?php comments_number(__('No Comments','mtaa'), __('One Comment','mtaa'), __('% Comments','mtaa') );?></h3>

	<ol class="commentlist">
		<?php
			/* Loop through and list the comments. Tell mn_list_comments()
			 * to use mtaa_comment() to format the comments.
			 * If you want to overload this in a child theme then you can
			 * define mtaa_comment() and that will be used instead.
			 * See mtaa_comment() in functions/theme/functions.php for more.
			 */
			mn_list_comments( array( 'callback' => 'mtaa_comment' ) );
		?>
	</ol>

	<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
		<div class="navigation">
			<?php paginate_comments_links( array('prev_text' => ''.__( '<span class="meta-nav">&larr;</span> Older Comments', 'mtaa' ).'', 'next_text' => ''.__( 'Newer Comments <span class="meta-nav">&rarr;</span>', 'mtaa' ).'') );?>
		</div><!-- .navigation -->
	<?php endif; // check for comment navigation ?>


	<?php else : // or, if we don't have comments:

		/* If there are no comments and comments are closed,
		 * let's leave a little note, shall we?
		 */
		if ( ! comments_open() ) :
	?>
		<p class="nocomments"><?php _e( 'Comments are closed.', 'mtaa' ); ?></p>
	<?php endif; // end ! comments_open() ?>

<?php endif; // end have_comments() ?>

<?php

$commenter = mn_get_current_commenter();
$req = get_option( 'require_name_email' );
$aria_req = ( $req ? " aria-required='true'" : '' );

$custom_comment_form = array( 'fields' => apply_filters( 'comment_form_default_fields', array(
    'author' => '<div class="form_fields"><p class="comment-form-author">' .
			'<label for="author">' . __( 'Name:' , 'mtaa' ) . '</label> ' .
			'<input id="author" name="author" type="text" value="' .
			esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' class="required" />' .
			'' .
 			'</p>',
    'email'  => '<p class="comment-form-email">' .
			'<label for="email">' . __( 'Email Address:' , 'mtaa' ) . '</label> ' .
 			'<input id="email" name="email" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' class="required email" />' .
 			'' .
 			'</p>',
    'url'    =>  '<p class="comment-form-url">' .
			'<label for="url">' . __( 'Website:' , 'mtaa' ) . '</label> ' .
 			'<input id="url" name="url" type="text" value="' . esc_attr(  $commenter['comment_author_url'] ) . '" size="30"' . $aria_req . ' />' .
 			'</p></div><div class="clear"></div>') ),
	'comment_field' => '<p class="comment-form-comment">' .
			'<label for="comment">' . __( 'Message:' , 'mtaa' ) . '</label> ' .
 			'<textarea id="comment" name="comment" cols="35" rows="5" aria-required="true" class="required"></textarea>' .
			'</p><div class="clear"></div>',
	'logged_in_as' => '<p class="logged-in-as">' . sprintf( __( 'Logged in as <a href="%1$s">%2$s</a>. <a href="%3$s">Log out?</a>', 'mtaa' ), admin_url( 'profile.php' ), $user_identity, mn_logout_url( apply_filters( 'the_permalink', get_permalink() ) ) ) . '</p>',
	'title_reply' => __( 'Leave a Comment' , 'mtaa' ),
  	'cancel_reply_link' => __( 'Cancel' , 'mtaa' ),
	'label_submit' => __( 'Post Comment' , 'mtaa' ),
	'comment_form_after' => '<div class="clear"></div>'
);
comment_form($custom_comment_form);
?>

</div><!-- #comments -->