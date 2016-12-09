<?php
 
// Do not delete these lines
if (!empty($_SERVER['SCRIPT_FILENAME']) && 'comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
	die ('Please do not load this page directly. Thanks!');
 
if ( post_password_required() ) : ?>
	<p class="nocomment"><?php _e( 'This post is password protected. Enter the password to view any comments.', 'Sivi' ); ?></p>
	
	<?php return;
endif;
?>
 
<?php if ( have_comments() ) : ?>
	<h4>
		<?php printf( _n( '%1$s Comment', '%1$s Comments', get_comments_number(), 'Sivi' ),number_format_i18n( get_comments_number() ), '<em>' . get_the_title() . '</em>' );?>
	</h4>
	<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
		<div class="navigation">
			<div class="nav-previous"><?php previous_comments_link( __( '<span class="meta-nav">&larr;</span> Older Comments', 'Sivi' ) ); ?></div>
			<div class="nav-next"><?php next_comments_link( __( 'Newer Comments <span class="meta-nav">&rarr;</span>', 'Sivi' ) ); ?></div>
		</div> <!-- .navigation -->
	<?php endif; ?>
	 
	<ul class="comments-list">
	    <?php mn_list_comments('callback=sivi_comment'); ?>
	</ul>

<?php else : // this is displayed if there are no comments so far ?>
	<?php if ('open' == $post->comment_status) : ?>
		<!-- If comments are open, but there are no comments. -->
	<?php else : // comments are closed ?>
		<!-- If comments are closed. -->
		<p class="nocomments"><?php _e( 'Comments are closed.', 'Sivi' ); ?></p>
	 <?php endif; ?>
<?php endif; ?>

<?php if ('open' == $post->comment_status) : ?>
	 <div class="leave-comment">
		<h4><?php comment_form_title( 'Leave A Comment', 'Leave a Reply to %s' ); ?></h4> 
		<div class="cancel-comment-reply"><?php cancel_comment_reply_link(); ?></div>
		<div class="inner">
				<?php if ( get_option('comment_registration') && !$user_ID ) : ?>
					<p class="bottom20">
						<?php _e('You must be logged in to post a comment.', 'Sivi')?> 
						<a href="<?php echo get_option('siteurl'); ?>/login.php?redirect_to=<?php echo urlencode(get_permalink()); ?>">
							<?php _e('Click here to login.', 'Sivi')?> 
						</a> 
					</p>
				<?php else : ?> 
					<form action="<?php echo get_option('siteurl'); ?>/article-comments.php" method="post"  class="form-horizontal">
							<?php if ( $user_ID ) : ?>				 
									<p class="com_logined_text">
										<?php _e('Logged in as', 'Sivi')?> 
										<a href="<?php echo get_option('siteurl'); ?>/admin/profile.php"><?php echo esc_attr($user_identity); ?></a>. 
										<a href="<?php echo mn_logout_url(get_permalink()); ?>"><?php _e('Log out', 'Sivi')?> &raquo;</a>
									</p>
							<?php else : ?>
								<div class="form-group">
									<label class="col-sm-2 control-label"><?php _e('Name', 'Sivi')?></label>
									<div class="col-sm-10">
										<input  type="text" name="author" id="author" class="contact-name"  title="<?php _e('Name', 'Sivi')?>"  value="<?php echo $comment_author; ?>"  <?php if ($req) echo "aria-required='true'"; ?> />
									</div> <!-- end .col-sm-10 -->
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label"><?php _e('Email', 'Sivi')?></label>
									<div class="col-sm-10">
										<input  type="email" name="email" id="email" class="contact-email"  title="<?php _e('Email', 'Sivi')?>"  value="<?php echo $comment_author_email; ?>" <?php if ($req) echo "aria-required='true'"; ?>>
									</div> <!-- end .col-sm-10 -->
								</div>
							<?php endif; ?>
							<div class="form-group">
								<label class="col-sm-2 control-label"><?php _e('Message', 'Sivi')?></label>
								<div class="col-sm-10">
									<textarea name="comment" title="<?php _e('Message', 'Sivi')?>" class="contact-message" rows="3" id="comment"></textarea>
								</div> <!-- end .col-sm-10 -->
							</div>
							<div class="form-group">
								<div class="col-sm-10 col-sm-offset-2">
									<input type="submit" class="button solid-button purple"  value="<?php _e('Send Message', 'Sivi')?>" />
									<?php comment_id_fields(); ?>
									<?php do_action('comment_form', $post->ID); ?>
								</div> <!-- end .col-sm-10 -->
							</div>
					</form>
				<?php endif;  ?>
		</div>
	</div>
<?php endif;  ?>
