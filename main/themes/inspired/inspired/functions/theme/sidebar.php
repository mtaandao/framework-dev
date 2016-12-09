<?php
/*-----------------------------------------------------------------------------------*/
/* Initializing Widgetized Areas (Sidebars)				 							 */
/*-----------------------------------------------------------------------------------*/


/*----------------------------------*/
/* Homepage widgetized areas		*/
/*----------------------------------*/

register_sidebar(array(
	'name'=>'Homepage',
	'id' => 'home-full',
	'description' => 'Widget area for page template "Homepage (Widgetized)". &#13; &#10; &#09; Add here: "Mtaa: Portfolio Scroller", "Mtaa: Portfolio Showcase" widgets.',
	'before_widget' => '<div class="widget %2$s" id="%1$s">',
	'after_widget' => '<div class="clear"></div></div>',
	'before_title' => '<h2 class="section-title">',
	'after_title' => '</h2>',
));


/*----------------------------------*/
/* Footer widgetized areas		    */
/*----------------------------------*/


register_sidebar(array('name'=>'Footer: Column 1',
	'id' => 'footer_1',
 	'before_widget' => '<div class="widget %2$s" id="%1$s">',
	'after_widget' => '<div class="clear"></div></div>',
	'before_title' => '<h3 class="title">',
	'after_title' => '</h3>',
));

register_sidebar(array('name'=>'Footer: Column 2',
	'id' => 'footer_2',
 	'before_widget' => '<div class="widget %2$s" id="%1$s">',
	'after_widget' => '<div class="clear"></div></div>',
	'before_title' => '<h3 class="title">',
	'after_title' => '</h3>',
));

register_sidebar(array('name'=>'Footer: Column 3',
	'id' => 'footer_3',
 	'before_widget' => '<div class="widget %2$s" id="%1$s">',
	'after_widget' => '<div class="clear"></div></div>',
	'before_title' => '<h3 class="title">',
	'after_title' => '</h3>',
));

?>