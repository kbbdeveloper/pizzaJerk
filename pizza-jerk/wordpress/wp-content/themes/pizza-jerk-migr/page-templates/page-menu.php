<div>
<?php
    /*
    Template Name: Menu
    */
?>

<?php get_header (); ?>
<div id="pageMainContent">
		<section>

		<?php
		while ( have_posts() ) : the_post(); ?>





			
		
		<?php endwhile; ?>

		</section>
</div>

<?php get_footer (); ?>

</div>