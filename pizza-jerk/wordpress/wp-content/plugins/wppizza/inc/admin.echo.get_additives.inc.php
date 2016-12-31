<?php if ( ! defined( 'ABSPATH' ) ) exit;/*Exit if accessed directly*/ ?>
<?php
	$str='';
	$str.="<span class='wppizza_option'>";

	if(!is_array($v)){/**for legacy reasons as it really should have been an array to start off with**/
		$additiveVal['name']=$v;
		$additiveVal['sort']='';
	}else{
		$additiveVal=$v;
	}

	$str.="".__('sort', 'wppizza-locale').": <input name='".$this->pluginSlug."[".$field."][".$k."][sort]' size='3' type='text' value='". $additiveVal['sort'] ."' placeholder=''/>";
	$str.="".__('name', 'wppizza-locale').": <input id='wppizza_".$field."_".$k."' name='".$this->pluginSlug."[".$field."][".$k."][name]' size='30' class='wppizza-getkey' type='text' value='".$additiveVal['name']."' />";
	/**if not in use or just added via js***/
	if(!isset($optionInUse) || (isset($optionInUse) && !in_array($k,$optionInUse['additives']))){
		$str.="<a href='#' class='wppizza-delete button' title='".__('delete', 'wppizza-locale')."'> [X] </a>";
	}else{
		$str.="".__('in use', 'wppizza-locale')."";
	}
	$str.="</span>";
?>