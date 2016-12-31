<div>
<?php
    /*
    Template Name: About
    */
?>

<?php get_header (); ?>
<div id="aboutBodyContent">
		<section>

		<?php
		while ( have_posts() ) : the_post(); ?>





			
		
		<?php endwhile; ?>

		</section>
</div>

<?php get_footer (); ?>

</div>