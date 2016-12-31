<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
/********************************************************


	WPPIZZA - Single Post Template Instructions

	NOTE: THIS FILE DOES NOT DO ANYTHING BY ITSELF, PLEASE FOLLOW THE INSTRUCTIONS BELOW !!!

	Howto:
	a) locate the single.php file in your theme directory and copy it to the same directory as single-wppizza.php
	b) the file will probably have something like  while ( have_posts() ) : the_post(); .....some stuff ....endwhile; in it
	c) REPLACE this with the code below and you should now have the same layout for a single item as you have when displaying categories (see below)
***************************************************************************************************************/


/***********************************************************************************
	Re c) :
		the code to replace the whole loop with
		(THIS MIGHT BE SOMEWHAT DIFFERENT IN YOUR THEME)
************************************************************************************/
<?php
	while ( have_posts() ) : the_post();

	if(is_single()){/*the is_single() is probably overkill as it should really be a single post anyway, oh well...**/

		if ($template_file = locate_template( array ('wppizza-loop.php' ))){ /*or use wppizza-loop-responsive.php, depending what you use. furthermore, if using a subdirectory called wppizza, use array ('wppizza/wppizza-loop.php' )*/
			//get_template_part( WPPIZZA_POST_TYPE, 'loop' );
			include_once($template_file);
		}else{
			include_once(''.WPPIZZA_PATH.'templates/wppizza-loop.php');	/*or use wppizza-loop-responsive.php, depending what you use*/
		}

	}
	endwhile; // end of the loop.
?>
/************************************************************
	to display the link to the single page add the following somewhere inside the loop template you are using
************************************************************/
<?php
	if(!is_single()){/*no point displaying it when already on single page i would have tought**/
		echo"<a href='".$permalink."'>[whatever you want linked]</a>";
	}
?>