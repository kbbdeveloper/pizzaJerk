<?php if ( ! defined( 'ABSPATH' ) ) exit;/*Exit if accessed directly*/ ?>
<?php
$str='';
	$str.="<span class='wppizza_option'>";
	$str.="<input id='wppizza_".$field."_".$k."' class='wppizza-getkey' type='hidden'>";

	$str.="<span class='wppizza_label'>ID: ".$k."<br/>".__('Admin Screen Label', 'wppizza-locale').":</span>";
	/*existing*/
	if(is_array($v)){
	$i=0;
	foreach($v as $c=>$obj){
		if($i==0){
		$val=!empty($obj['lbladmin']) ? $obj['lbladmin'] : '';
		$str.="<input name='".$this->pluginSlug."[".$field."][".$k."][".$c."][lbladmin]' size='10' type='text' value='". $val ."' /><span class='description'> ".__('optional, use to identify groups with the same labels. ', 'wppizza-locale')."</span>";
		$i++;
		}
	}}
	/*ajax*/
	if(isset($v) && !is_array($v)){
	$i=0;
	for($i=0;$i<$v;$i++){
		if($i==0){
		$str.="<input name='".$this->pluginSlug."[".$field."][".$k."][".$i."][lbladmin]' size='10' type='text' value='' /><span class='description'> ".__('optional, use to identify groups with the same labels. ', 'wppizza-locale')."</span>";
		$i++;
		}
	}}

	$str.="<br/>";

	$str.="<span class='wppizza_label'>".__('Label [Frontend]', 'wppizza-locale').":</span>";
	/*existing*/
	if(is_array($v)){
	foreach($v as $c=>$obj){
		$str.="<input name='".$this->pluginSlug."[".$field."][".$k."][".$c."][lbl]' size='10' type='text' value='".$obj['lbl']."' />";
	}}
	/*ajax*/
	if(isset($v) && !is_array($v)){
	for($i=0;$i<$v;$i++){
		$str.="<input name='".$this->pluginSlug."[".$field."][".$k."][".$i."][lbl]' size='10' type='text' value='' />";
	}}

	$str.="<br/>";
	$str.="<span class='wppizza_label'>".__('Default Prices', 'wppizza-locale').":</span>";
	/*existing*/
	if(is_array($v)){
	foreach($v as $c=>$obj){
		$str.="<input name='".$this->pluginSlug."[".$field."][".$k."][".$c."][price]' size='10' type='text' value='".$obj['price']."' />";
	}}
	/*ajax*/
	if(isset($v) && !is_array($v)){
	for($i=0;$i<$v;$i++){
		$str.="<input name='".$this->pluginSlug."[".$field."][".$k."][".$i."][price]' size='10' type='text' value='' />";
	}}

	if(!isset($optionInUse) || (isset($optionInUse) && !in_array($k,$optionInUse['sizes']))){
	$str.="<a href='#' class='wppizza-delete ".$field." button' title='".__('delete', 'wppizza-locale')."'> [X] </a>";
	}else{
	$str.="".__('in use', 'wppizza-locale')."";
	}
	$str.="</span>";
?>