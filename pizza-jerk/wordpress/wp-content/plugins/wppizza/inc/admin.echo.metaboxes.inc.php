<?php if ( ! defined( 'ABSPATH' ) ) exit;/*Exit if accessed directly*/ ?>
<?php
	$options = $this->pluginOptions;
	$optionsSizes =wppizza_sizes_available($options['sizes'],true);
	$optionsCurrency =$options['order']['currency_symbol'];
	$optionsDecimals =$options['layout']['hide_decimals'];
	$meta_values = get_post_meta($meta_options->ID, $this->pluginSlug);
	$meta_values = $meta_values[0];
	$wppizza_meta_box=array();


	/****  alternative taxrate ***/
	$wppizza_meta_box['alt_tax']='';
	$wppizza_meta_box['alt_tax'].="<div class='".$this->pluginSlug."_option'>";
	$wppizza_meta_box['alt_tax'].="<div class='wppizza-meta-label'>".sprintf( __( 'alternative taxrate (%s%%)', 'wppizza-locale' ), $options['order']['item_tax_alt'] )." ?</div> ";
	$wppizza_meta_box['alt_tax'].="<label class='button'>";
	$wppizza_meta_box['alt_tax'].="<input name='".$this->pluginSlug."[item_tax_alt]' size='5' ". checked(!empty($meta_values['item_tax_alt']),true,false)." type='checkbox' value='1' /> ".__( 'yes/no', 'wppizza-locale' )."";//". checked(in_array($s,$meta_values['additives']),true,false)."
	$wppizza_meta_box['alt_tax'].="</label>";
	$wppizza_meta_box['alt_tax'].=" <span class='description'>[".__('set in wppizza->order settings', 'wppizza-locale')."]</span>";
	$wppizza_meta_box['alt_tax'].="</div>";

	/****  pricetiers and prices ***/
	$wppizza_meta_box['prices']='';
	$wppizza_meta_box['prices'].="<div class='".$this->pluginSlug."_option'>";
	$wppizza_meta_box['prices'].="<div class='wppizza-meta-label'>".__('price tier and prices', 'wppizza-locale').":</div> ";
	$wppizza_meta_box['prices'].="<select name='".$this->pluginSlug."[sizes]' class='wppizza_pricetier_select wppizza_pricetier_select_meta'>";
	foreach($optionsSizes as $l=>$m){
		if($l==$meta_values['sizes']){$sel=" selected='selected'";}else{$sel='';}
		$ident=!empty($options['sizes'][$l][0]['lbladmin']) && $options['sizes'][$l][0]['lbladmin']!='' ? $options['sizes'][$l][0]['lbladmin'] :'ID:'.$l.'';
		$wppizza_meta_box['prices'].="<option value='".$l."'".$sel.">".implode(", ",$m['lbl'])." [".$ident."]</option>";
	}
	$wppizza_meta_box['prices'].="</select>";
	$wppizza_meta_box['prices'].="<span class='wppizza_pricetiers'>";
		foreach($meta_values['prices'] as $k=>$v){
			$wppizza_meta_box['prices'].="<input name='".$this->pluginSlug."[prices][]' size='5' type='text' value='".wppizza_output_format_price($v,$optionsDecimals)."' />".$optionsCurrency."";
		}
	$wppizza_meta_box['prices'].="</span>";
	$wppizza_meta_box['prices'].="</div>";


	if(isset($options['additives']) && is_array($options['additives']) && count($options['additives'])>0){
		/*->*** which additives in item ***/
		$wppizza_meta_box['additives']='';
		$wppizza_meta_box['additives'].="<div class='".$this->pluginSlug."_option'>";
		$wppizza_meta_box['additives'].="<div class='wppizza-meta-label'>".__('contains additives', 'wppizza-locale').":</div> ";
		asort($options['additives']);//sort but keep index
		foreach($options['additives']  as $s=>$o){
			if(!is_array($o)){$lbl=$o;}else{$lbl=''.$o['name'];}/*legacy*/
			$wppizza_meta_box['additives'].="<label class='button'>";
			$wppizza_meta_box['additives'].="<input name='".$this->pluginSlug."[additives][".$s."]' size='5' type='checkbox' ". checked(in_array($s,$meta_values['additives']),true,false)." value='".$s."' /> ".$lbl."";
			$wppizza_meta_box['additives'].="</label>";
		}
		$wppizza_meta_box['additives'].="</div>";
	}

	/**add filter**/
	$wppizza_meta_box=apply_filters('wppizza_filter_admin_metaboxes', $wppizza_meta_box, $meta_values, $optionsSizes);

	/**implode and output**/
	$output=implode('',$wppizza_meta_box);
	print"".$output;
?>