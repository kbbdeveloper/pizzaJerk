<?php if ( ! defined( 'ABSPATH' ) ) exit;/*Exit if accessed directly*/ ?>
<?php
 /****************************************************************************************
 *
 *
 *	WPPizza - get/add plugin options
 *	get set options via require_once as we only need this one time
 *	(in case we are displaying more than one category)
 *
 *
 ****************************************************************************************/
/*ADDED IN VERSION 2.9.5 - moved to include file in 2.11.2*/
/**to - for example - allow to set options when using this file as template part as options might not be set yet**/
$options=apply_filters('wppizza_loop_top',$options=!empty($options) ? $options : false);
?>