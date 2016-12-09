<?php
/**
 * The template used for displaying page content in templates/page-ribbon.php
 *
 * @package Ese
 */

?>

<?php
    // Gets the stored background color value 
    $color_value = get_post_meta( get_the_ID(), 'ese-ribbon-bg-color', true ); 
    // Checks and returns the color value
  	$color = (!empty( $color_value ) ? 'background-color:' . $color_value . ';' : '');

  	// Gets the stored height value 
    $height_value = get_post_meta( get_the_ID(), 'ese-ribbon-height', true ); 
    // Checks and returns the height value
  	$height = (!empty( $height_value ) ? 'height:' . $height_value . ';' : '');

  	 // Gets the uploaded featured image
  	$featured_img = mn_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );
  	// Checks and returns the featured image
  	$bg = (!empty( $featured_img ) ? "background-image: url('". $featured_img[0] ."');" : '');
?>

<div class="ribbon" style="<?php echo $color . $bg . $height; ?> "></div>

<div class="ese-page-ribbon">
	<div class="mdl-grid ese-1600">
		<div class="mdl-cell mdl-cell--2-col mdl-cell--hide-tablet mdl-cell--hide-phone"></div>
		<div class="mdl-cell mdl-cell--8-col mdl-card mdl-shadow--2dp ribbon-content"> 
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				
				<header>
					<?php the_title( sprintf( '<h3>','</h3>' )); ?>
				</header><!-- .entry-header -->

				<div class="entry-content mdl-color-text--grey-600">
					<?php the_content(); ?>
					<?php
						mn_link_pages( array(
							'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'ese' ),
							'after'  => '</div>',
						) );
					?>
				</div><!-- .entry-content -->
			</article><!-- #post-## -->
		</div> <!-- .mdl-cell -->
	</div>
</div>


