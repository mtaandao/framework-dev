<?php

/****************************Modal BOX****************/

vc_map( array(
   "name" => __("Sivi Modal box", "Sivi"),
   "base" => "reveal",
   "class" => "",
   "icon" => "icon-mnb-my_reveal",
   'admin_enqueue_css' => array(get_template_directory_uri().'/vc_templates/shortcodes.css'),
   "category" => __('Content', "Sivi"),
   "params" => array(
       array(
         "type" => "dropdown",
         
         "class" => "",
         "heading" => __("Type", "Sivi"),
         "param_name" => "type",
         "value" => array("Button"=>"button solid-button","Link Style"=>""),
         "description" => __("Choose button type", "Sivi")
      ),
       array(
         "type" => "dropdown",
         
         "class" => "",
         "heading" => __("Color", "Sivi"),
         "param_name" => "color",
         "value" => array("Purple"=>"purple", "White"=>"white",  "Dark"=>"dark", "Primary"=>"btn-primary", "Info"=>"btn-info", "Success"=>"btn-success","Warning"=>"btn-warning", "Danger"=>"btn-danger"),
         "description" => __("Choose button color.", "Sivi")
      ),
      array(
         "type" => "dropdown",
         
         "class" => "",
         "heading" => __("Size", "Sivi"),
         "param_name" => "size",
         "value" => array("Large"=>"btn-lg", "Default"=>"", "Small"=>"btn-sm","Very Small"=>"btn-xs"),
         "description" => __("Choose button size.", "Sivi")
      ),
      
      array(
         "type" => "textfield",
         "holder" =>'div',
         "class" => "",
         "heading" => __("Button Text", "Sivi"),
         "param_name" => "button"
      ),
      array(
         "type" => "textfield",
         
         "class" => "",
         "heading" => __("Box Title", "Sivi"),
         "param_name" => "revtitle"
      ),
	   
       array(
         "type" => "textarea_html",
         
         "class" => "",
         "heading" => __("Content", "Sivi"),
         "param_name" => "content",
         "description" => __(" Box content.", "Sivi")
      ),
       array(
         "type" => "textfield",
         "class" => "",
         "heading" => __("Extra class", "Sivi"),
         "param_name" => "class",
         "description" => __(' Extra class name', "Sivi")
      )
   )
) );

/************************************************/
?>