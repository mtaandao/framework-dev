<?php
/****************** SLIDER ********************/
function vc_slider( $atts, $content ){
	$GLOBALS['slideritem_count'] = 0;
	extract(shortcode_atts(array(
		"anim"=>'',
		"class"=>'',
		"nav"=>'',
		"auto"=>'',
		"speed" => '',
		"smimages" => '',
	), $atts));
	do_shortcode( $content );
	$randomId = mt_rand(0, 100000);
	$return = '';
	$anim=(!empty($anim)) ? 'animation '.$anim : '';
	$imgId = explode(',', $smimages);
	$randomId = mt_rand(0, 100000);
	$return.='<div id="single-slider-'.$randomId.'" class="portfolio-slider owl-carousel '.$class.' '.$anim.'">';
	foreach ($imgId as $smimage) {
		$return.='<div><img src="'.mn_get_attachment_url($smimage) . '" alt="' . $smimage . '" class="img-resposive"/></div>';
  		
	}
	
	$return.='</div>';
	$rtl= weblusive_get_option('rtl_mode');
	if($rtl){
		$rtl='true';
	}else{
		$rtl='false';
	}
	$return.='
	<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery("#single-slider-'.$randomId.'").owlCarousel({
				items: 1,
				autoplay: '.$auto.',
				autoplayTimeout:'.$speed.',
				loop: true,
				nav: '.$nav.',
				navText: ["<i class=\"fa fa-angle-left\"></i>","<i class=\"fa fa-angle-right\"></i>"],
				dots: false
				});
		});
	</script>';
	return $return;
}
add_shortcode('vc_slider', 'vc_slider' );

/****/
vc_map( array(
   "name" => __("Sivi Image Slider", "Sivi"),
   "base" => "vc_slider",
   "class" => "",
   "icon" => "icon-mnb-my_slider",
   'admin_enqueue_css' => array(get_template_directory_uri().'/vc_templates/shortcodes.css'),
   "category" => __('Content', "Sivi"),
   "params" => array(
       array(
         "type" => "dropdown",
         "class" => "",
         "heading" => __("Animation", "Sivi"),
         "param_name" => "anim",
         "value" => array( "None"=>"", "bounce"=>"bounce", "flash"=>"flash", "pulse"=>"pulse", "shake"=>"shake", "swing"=>"swing", "tada"=>"tada", "wobble"=>"wobble", "bounceIn"=>"bounceIn", "bounceInDown"=>"bounceInDown", "bounceInLeft"=>"bounceInLeft", "bounceInRight"=>"bounceInRight", "bounceInUp"=>"bounceInUp", "bounceOut"=>"bounceOut", "bounceOutDown"=>"bounceOutDown", "bounceOutLeft"=>"bounceOutLeft", "bounceOutRight"=>"bounceOutRight", "bounceOutUp"=>"bounceOutUp", "fadeIn"=>"fadeIn", "fadeInDown"=>"fadeInDown", "fadeInDownBig"=>"fadeInDownBig", "fadeInLeft"=>"fadeInLeft", "fadeInLeftBig"=>"fadeInLeftBig", "fadeInRight"=>"fadeInRight", "fadeInRightBig"=>"fadeInRightBig", "fadeInUp"=>"fadeInUp", "fadeInUpBig"=>"fadeInUpBig", "fadeOut"=>"fadeOut", "fadeOutDown"=>"fadeOutDown", "fadeOutDownBig"=>"fadeOutDownBig", "fadeOutLeft"=>"fadeOutLeft", "fadeOutLeftBig"=>"fadeOutLeftBig", "fadeOutRight"=>"fadeOutRight", "fadeOutRightBig"=>"fadeOutRightBig", "fadeOutUp"=>"fadeOutUp", "fadeOutUpBig"=>"fadeOutUpBig", "flip"=>"flip", "flipInX"=>"flipInX", "flipInY"=>"flipInY", "flipOutX"=>"flipOutX", "flipOutY"=>"flipOutY", "lightSpeedIn"=>"lightSpeedIn", "lightSpeedOut"=>"lightSpeedOut", "rotateIn"=>"rotateIn", "rotateInDownLeft"=>"rotateInDownLeft", "rotateInDownRight"=>"rotateInDownRight", "rotateInUpLeft"=>"rotateInUpLeft", "rotateInUpRight"=>"rotateInUpRight", "rotateOut"=>"rotateOut", "rotateOutDownLeft"=>"rotateOutDownLeft", "rotateOutDownRight"=>"rotateOutDownRight", "rotateOutUpLeft"=>"rotateOutUpLeft", "rotateOutUpRight"=>"rotateOutUpRight",  "hinge"=>"hinge", "rollIn"=>"rollIn", "rollOut"=>"rollOut", "zoomIn"=>"zoomIn","zoomInDown"=>"zoomInDown", "zoomInLeft"=>"zoomInLeft","zoomInRight"=>"zoomInRight","zoomInUp"=>"zoomInUp","zoomOut"=>"zoomOut", "zoomOutLeft"=>"zoomOutLeft", "zoomOutRight"=>"zoomOutRight","zoomOutUp"=>"zoomOutUp"),
         "description" => __(" Animation.", "Sivi")
      ),
	  
        array(
         "type" => "attach_images",
         "class" => "",
         "heading" => __("Image", "Sivi"),
         "param_name" => "smimages"
      ),
	   array(
         "type" => "dropdown",
         "class" => "",
         "heading" => __("Navigation", "Sivi"),
         "param_name" => "nav",
         "value" => array("Yes"=>"true", "No"=>"false")
      ),
	   
	   array(
         "type" => "dropdown",
         "class" => "",
         "heading" => __("Autoplay", "Sivi"),
         "param_name" => "auto",
         "value" => array("Yes"=>"true", "No"=>"false")
      ),
	    array(
         "type" => "textfield",
         "class" => "",
         "heading" => __("Autoplay speed(milliseconds)", "Sivi"),
         "param_name" => "speed",
         "value" => '3000'
      ),
      array(
         "type" => "textfield",
         "class" => "",
         "heading" => __("Extra class", "Sivi"),
         "param_name" => "class",
         "description" => __(' Extra class name', "Sivi")
      ),
   )
) );
?>