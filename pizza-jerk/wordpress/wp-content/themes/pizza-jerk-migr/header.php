<?php
/**
 * The Header for our theme.
 */
?><!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php wp_title( '|', true, 'right' ); ?></title>
	
	<?php wp_head(); ?>
	
</head>





	<div id="header">
		
	<img src="<?php bloginfo('template_url'); ?>/img/pjlogo.png">
			
		
		<?php wp_nav_menu( array( 'menu' => 'Header Navigation', 'theme_location' => 'header-navigation', 'container' => false, 'menu_id' => 'headerNavigation', 'menu_class' => false  ) ); ?>
	

	</div>

