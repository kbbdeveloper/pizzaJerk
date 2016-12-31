
<footer>
	<div id="navFlexContainer">
	<?php wp_nav_menu( array( 'menu' => 'Footer Navigation', 'theme_location' => 'footer-navigation', 'container' => false, 'menu_id' => 'footerNavigation', 'menu_class' => 'footer-nav',  ) ); ?></div>
	<div id="copyright-block"><span id="copyright-text">&copy;2016 Pizza Jerk </span></div>
</footer>

<?php wp_footer(); ?>

</html>