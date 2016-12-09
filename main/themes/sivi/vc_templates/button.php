<?php

/******************* BUTTONS ********************/

vc_map( array(
   "name" => __("Sivi button", "Sivi"),
   "base" => "button",
   "class" => "",
   "icon" => "icon-mnb-my_button",
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
         "heading" => __("Color", "Sivi"),
         "param_name" => "color",
         "value" => array("Purple"=>"purple", "White"=>"white",  "Dark"=>"dark", "Primary"=>"btn-primary", "Info"=>"btn-info", "Success"=>"btn-success","Warning"=>"btn-warning", "Danger"=>"btn-danger")
      ),
      array(
         "type" => "dropdown",
         
         "class" => "",
         "heading" => __("Size", "Sivi"),
         "param_name" => "size",
         "value" => array("Large"=>"btn-lg", "Default"=>"defaultsize", "Small"=>"btn-sm","Very small"=>"btn-xs"),
         "description" => __("Choose button size.", "Sivi")
      ),
      
      
      array(
         "type" => "dropdown",
         
         "class" => "",
         "heading" => __("Status", "Sivi"),
         "param_name" => "status",
         "value" => array("Enabled"=>"","Disabled"=>"disabled"),
         "description" => __("Choose button status.", "Sivi")
      ),
      array(
         "type" => "textfield",
         
         "class" => "",
         "heading" => __("Link", "Sivi"),
         "param_name" => "link",
         "description" => __(" Link to.", "Sivi")
      ),
       array(
         "type" => "textfield",
         "holder"=>"div",
         "class" => "",
         "heading" => __("Text", "Sivi"),
         "param_name" => "content",
         "description" => __("Write button text.", "Sivi")
      ),
	  
        array(
         "type" => "textfield",
         "class" => "",
         "heading" => __("Extra class", "Sivi"),
         "param_name" => "class",
         "description" => __('Extra class name', "Sivi")
      )
   )
) );

/************************************************/
?>