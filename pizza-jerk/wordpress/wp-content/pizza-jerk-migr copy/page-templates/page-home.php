<div>
<?php
    /*
    Template Name: Home
    */
?>

<?php get_header (); ?>
<section id="homeBodyContent">
		

		<?php
		while ( have_posts() ) : the_post(); ?>


<img src="<?php bloginfo('template_url'); ?>/img/pjerktrivialpursuit.jpg">

			
		
		<?php endwhile; ?>

		
</section>

<?php get_footer (); ?>

</div>