<?php
/*********************Divider************************/


/*************************************************/
vc_map( array(
   "name" => __("Sivi Divider", "Sivi"),
   "base" => "divider",
   "class" => "",
   "icon" => "icon-mnb-my_divider",
   'admin_enqueue_css' => array(get_template_directory_uri().'/vc_templates/shortcodes.css'),
   "category" => __('Content', "Sivi"),
   "params" => array(
        
       array(
         "type" => "dropdown",
         "class" => "",
         "heading" => __("Type", "Sivi"),
         "param_name" => "type",
         "value" => array("Blank Spacer"=>"blank-spacer", "Line"=>"line" ),
      ),
       array(
         "type" => "textfield",
         "class" => "",
         "heading" => __("Custom Size for Blank Spacer", "Sivi"),
         "param_name" => "customsize",
         "description" => __('In pixels', "Sivi")
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
/*********************************************/
?>