<?php
/**
 * The template part for displaying the main navigation
 *
 * Learn more: http://codex.mtaandao.org/Template_Hierarchy
 *
 * @package Ese
 */

?>

<div class="mdl-layout__header-row">
  <!-- Title -->
  <span class="mdl-layout-title"><?php bloginfo( 'name' ); ?></span>
  <!-- Add spacer, to align navigation to the right -->
  <div class="mdl-layout-spacer"></div>

	<div class="ese-search-box mdl-textfield mdl-js-textfield mdl-textfield--expandable mdl-textfield--floating-label mdl-textfield--align-right mdl-textfield--full-width">   
      <?php get_search_form(); ?>
    </div>

  
    <?php
		$args = array(
	        'theme_location' => 'primary',
	        'container'       => 'nav',
	        'items_wrap' => '%3$s',
	        'container_class' => 'mdl-navigation mdl-layout--large-screen-only',
			'walker' => new Ese_Nav_Walker()
		);

		if (has_nav_menu('primary')) {
		       mn_nav_menu($args);
		    }
	?>
    
</div>
