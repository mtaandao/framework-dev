<?php
$sliderLoop = new MN_Query( array(
	'post_type' 	 => 'slider',
	'posts_per_page' => option::get( 'featured_posts_posts' )
) );
?>

<div id="slider">

	<?php if ( $sliderLoop->have_posts() ) : ?>

		<ul class="slides">

			<?php while ( $sliderLoop->have_posts() ) : $sliderLoop->the_post(); ?>

				<?php
				$slide_url = trim( get_post_meta( get_the_ID(), 'mtaa_slide_url', true ) );
				$btn_title = trim( get_post_meta( get_the_ID(), 'mtaa_slide_button_title', true ) );
				$btn_url = trim( get_post_meta( get_the_ID(), 'mtaa_slide_button_url', true ) );
				$large_image_url = mn_get_attachment_image_src( get_post_thumbnail_id(), 'featured');
				$small_image_url = mn_get_attachment_image_src( get_post_thumbnail_id(), 'featured-small');

				$style = ' style="background-image:url(\'' . $small_image_url[0] . '\')" data-bigimg="' . $large_image_url[0] . '"' ;
				?>

				<li<?php echo $style; ?>>
					<div class="slide-background-overlay"></div>
					<div class="li-wrap">

						<?php if ( empty( $slide_url ) ) : ?>

							<?php the_title( '<h3 class="missing-url">', '</h3>' ); ?>

						<?php else: ?>

							<?php the_title( sprintf( '<h3><a href="%s">', esc_url( $slide_url ) ), '</a></h3>' ); ?>

						<?php endif; ?>

						<div class="excerpt"><?php the_excerpt(); ?></div>

						<?php if ( !empty( $btn_title ) && !empty( $btn_url ) ) {
							?><div class="slide_button">
								<a href="<?php echo esc_url( $btn_url ); ?>"><?php echo esc_html( $btn_title ); ?></a>
							</div><?php
						} ?>
					</div>
	 			</li>
			<?php endwhile; ?>

		</ul>

		<div id="scroll-to-content" title="<?php esc_attr_e( 'Scroll to Content', 'mtaa' ); ?>">
			<?php _e( 'Scroll to Content', 'mtaa' ); ?>
		</div>

	<?php else: ?>

		<div class="empty-slider">
			<p><strong><?php _e( 'You are now ready to set-up your Slideshow content.', 'mtaa' ); ?></strong></p>

			<p>
				<?php
				printf(
					__( 'For more information about adding posts to the slider, please <strong><a href="%1$s">read the documentation</a></strong> or <a href="%2$s">add a new post</a>.', 'mtaa' ),
					'http://www.mtaandao.co.ke/documentation/inspired/',
					admin_url( 'post-new.php?post_type=slider' )
				);
				?>
			</p>
		</div>

	<?php endif; ?>

</div>