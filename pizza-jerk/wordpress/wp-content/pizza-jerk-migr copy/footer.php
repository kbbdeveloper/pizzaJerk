<footer>
	<nav class="mobile-nav">

		<?php 
						$defaults = array (
							'container' => false,
							'theme_location' => 'footer-nav',
							'menu_class' => 'footer-nav'
							);

							wp_nav_menu( $defaults );

					?>
	
	</nav>
	<div id="copyright-block"><span id="copyright-text">&copy;2016 Pizza Jerk </span></div>
</footer>

<?php wp_footer(); ?>
</div>
</html>