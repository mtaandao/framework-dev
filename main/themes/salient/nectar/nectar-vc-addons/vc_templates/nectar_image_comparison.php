<?php 

extract(shortcode_atts(array(
  "image_url" => '',
  "image_2_url" => ''
),
$atts));

mn_enqueue_script('twentytwenty');
mn_enqueue_style('twentytwenty');

$alt_tag = null;
$alt_tag_2 = null;

if(!empty($image_url)) {
		
	if(!preg_match('/^\d+$/',$image_url)){
			
		$image_url = $image_url;
	
	} else {

		$mn_img_alt_tag = get_post_meta( $image_url, '_mn_attachment_image_alt', true );
		if(!empty($mn_img_alt_tag)) $alt_tag = $mn_img_alt_tag;

		$image_src = mn_get_attachment_image_src($image_url, 'full');
		
		$image_url = $image_src[0];
	}
	
} else 
	$image_url = vc_asset_url( 'images/before.jpg' );

if(!empty($image_2_url)) {
		
	if(!preg_match('/^\d+$/',$image_2_url)){
			
		$image_2_url = $image_2_url;
	
	} else {
		
		$mn_img_alt_tag_2 = get_post_meta( $image_2_url, '_mn_attachment_image_alt', true );
		if(!empty($mn_img_alt_tag_2)) $alt_tag_2 = $mn_img_alt_tag_2;

		$image_src = mn_get_attachment_image_src($image_2_url, 'full');
		$image_2_url = $image_src[0];
	}
	
} else 
	$image_2_url = vc_asset_url( 'images/after.jpg' );

echo "<div class='twentytwenty-container'>
  <img src='".$image_url."' alt='".$alt_tag."'>
  <img src='".$image_2_url."' alt='".$alt_tag_2."'>
</div>";

?>