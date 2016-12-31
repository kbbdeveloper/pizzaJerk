
<?php
    /*
    Template Name: Home
    */
?>
<div id="homeTopWrapper">
	<?php get_header (); ?>
	<div id="homePageMainContent">
			

			<?php
			while ( have_posts() ) : the_post(); ?>


			<img src="<?php bloginfo('template_url'); ?>/img/pjerktrivialpursuit2.png" class="trivPizza">

		

			
			<?php endwhile; ?>

			
	
	</div>
</div>
<?php get_footer (); ?>


