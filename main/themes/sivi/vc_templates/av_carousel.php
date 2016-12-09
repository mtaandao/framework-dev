<?php
/****************** content carousel ********************/
class MNBakeryShortCode_Carousel extends MNBakeryShortCodesContainer{

}
/***/
class MNBakeryShortCode_Caritem extends MNBakeryShortCode {

}

/****/
vc_map( array(
   "name" => __("Sivi Content Slider", "Sivi"),
	"show_settings_on_create" => true,
	'is_container' => true,
	"content_element" => true,
	"as_parent" => array('only' => 'caritem'),
   "base" => "carousel",
   "class" => "",
   "icon" => "icon-mnb-my_av_carousel",
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
         "type" => "dropdown",
         "class" => "",
         "heading" => __("Navigation", "Sivi"),
         "param_name" => "nav",
         "value" => array("Yes"=>"true", "No"=>"false")
      ),
	   array(
         "type" => "dropdown",
         "class" => "",
         "heading" => __("Pagination", "Sivi"),
         "param_name" => "pag",
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
         "heading" => __("Slide Items", "Sivi"),
         "param_name" => "sitems",
         "value" => '3'
      ),

      array(
         "type" => "textfield",
         "class" => "",
         "heading" => __("Extra class", "Sivi"),
         "param_name" => "class",
         "description" => __(' Extra class name', "Sivi")
      ),
   ),
	"js_view" => 'VcColumnView'
) );
/**********************************************************/
vc_map( array(
	'name' => __( 'Carousel Item', 'Sivi' ),
	'base' => 'caritem',
	"icon" => "icon-mnb-ui-accordion",
	"as_child" => array('only' => 'carousel'),
	'content_element' => true,
	'params' => array(
		array(
		 "type" => "textarea_html",
		  "holder"=>"div",	
		  "class" => "",
		  "heading" => __("Content", "Sivi"),
		  "param_name" => "content"
		 ),		
		)
	))
?>