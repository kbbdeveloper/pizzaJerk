<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php
	extract( $args, EXTR_SKIP );
	/*initialize output var***/
	$widgetOutput="";
	// set widget title
	$title = apply_filters( 'widget_title', $instance['title'] );

/*** widget before*********************************************************************************/
	$widgetOutput.="". $before_widget ."";

/*** widget title if not empty and not suppressed*************************************************/
	if( !empty( $title ) && empty( $instance['suppresstitle'] )){
		$widgetOutput.="". $before_title . $title . $after_title."";
	}
/***************shopping cart********************************************************************/
	if($instance['type']=='cart'){
		$wdgArgs=array('type="cart"');
		if(isset( $instance['openingtimes']) && $instance['openingtimes']!=''){
			$wdgArgs[]='openingtimes="1"';
		}
		if(isset( $instance['stickycart']) && $instance['stickycart']!=''){
			$wdgArgs[]='stickycart="1"';
		}
		if(isset( $instance['minicart']) && $instance['minicart']!=''){
			$wdgArgs[]='minicart="1"';
		}
		if(isset( $instance['orderinfo']) && $instance['orderinfo']!=''){
			$wdgArgs[]='orderinfo="1"';
		}
		if(isset( $instance['height']) && $instance['height']>0){
			$wdgArgs[]='height="'.(int)$instance['height'].'px"';
		}
		if(isset( $instance['width']) && $instance['width']!=''){
			$wdgArgs[]='width="'.$instance['width'].'"';
		}
		$widgetOutput.= do_shortcode('['.$this->pluginSlug.' '.implode(" ",$wdgArgs).']');
		/*disable shoppingcart when disable_online_order is set */
		if(isset($this->pluginOptions['layout']['disable_online_order']) && $this->pluginOptions['layout']['disable_online_order']==1){
			$widgetOutput='';
		}
	}
/***************navigation*************************************************************************/
	if($instance['type']=='navigation'){

		$wdgArgs[]='type="'.$instance['type'].'"';

		if($instance['navterm']!=''){
			$wdgArgs[]='parent="'.$instance['navterm'].'"';
		}
		$widgetOutput.= do_shortcode('['.$this->pluginSlug.' '.implode(" ",$wdgArgs).']');
	}
/***************openingtimes***********************************************************************/
	if($instance['type']=='openingtimes'){
		$widgetOutput.= do_shortcode('['.$this->pluginSlug.' type="'.$instance['type'].'"]');
	}
/***************orderpage**************************************************************************/
	if($instance['type']=='orderpage'){
		$widgetOutput.= do_shortcode('['.$this->pluginSlug.' type="'.$instance['type'].'"]');
		/*disable orderpage when disable_online_order is set */
		if(isset($this->pluginOptions['layout']['disable_online_order']) && $this->pluginOptions['layout']['disable_online_order']==1){
			$widgetOutput='';
		}
	}
/***************display items in chosen category or first if not set*******************************/
	if($instance['type']=='category'){
		$wdgArgs=array();
		if($instance['term']!=''){
			$wdgArgs[]='category="'.$instance['term'].'"';
		}
		if(isset($instance['noheader']) && $instance['noheader']!=''){
			$wdgArgs[]='noheader="1"';
		}
		if(isset($instance['showadditives']) && $instance['showadditives']=='1'){
			$wdgArgs[]='showadditives="1"';
		}
		if(isset($instance['showadditives']) && $instance['showadditives']=='0'){
			$wdgArgs[]='showadditives="0"';
		}
		$widgetOutput.= do_shortcode('['.$this->pluginSlug.' '.implode(" ",$wdgArgs).']');
	}
/***************searchbox**************************************************************************/
	if($instance['type']=='search'){

		$wdgArgs=array();
		$incl=array();
		/*types*/
		if(isset($instance['wppizza'])){
			$incl[]=''.WPPIZZA_POST_TYPE.'';
		}
		if(isset($instance['post'])){
			$incl[]='post';
		}
		if(isset($instance['page'])){
			$incl[]='page';
		}
		if(count($incl)>0){
			$wdgArgs[]='include="'.implode(",",$incl).'"';
		}
		if(isset($instance['loggedinonly'])){
			$wdgArgs[]='loggedinonly="1"';
		}

		/*loggedinonly*/
		if(isset($instance['loggedinonly'])  && !is_user_logged_in()){
			$widgetOutput='';
			$after_widget='';
		}else{
			$widgetOutput.= do_shortcode('['.$this->pluginSlug.' type="'.$instance['type'].'" '.implode(" ",$wdgArgs).']');
		}
	}
/*** widget after***********************************************************************************/
	$widgetOutput.="". $after_widget."";


print"".$widgetOutput;
?>