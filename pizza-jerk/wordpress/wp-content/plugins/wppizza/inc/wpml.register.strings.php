<?php if ( ! defined( 'ABSPATH' ) ) exit;/*Exit if accessed directly*/ ?>
<?php

	/*no need to run this when no wpml or on wppizza first install*/
	if($this->pluginOptions!=0 && function_exists('icl_register_string') && is_admin() && ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) ) {

		/**we are updating the plugin, make sure we also register any new items that may have been added**/
		if(!empty($plugin_update)){
			$options=$update_options;
		}
		

		/**********************************************localization**************************************************/
		if(isset($_POST[''.$this->pluginSlug.'_localization']) || !empty($plugin_update) ){
		if(isset($options['localization']) && is_array($options['localization'])){
		foreach($options['localization'] as $k=>$arr){
			if ( is_admin() && ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) ){
				icl_register_string(WPPIZZA_SLUG,''. $k.'', $arr['lbl']);
			}
		}}}
		/************************************************additives**************************************************/
		if(isset($_POST[''.$this->pluginSlug.'_additives']) || !empty($plugin_update) ){
		if(isset($options['additives']) && is_array($options['additives'])){
		foreach($options['additives'] as $k=>$str){
			if(is_array($str) && isset($str['name'])){$str=$str['name'];}/*legacy*/
			icl_register_string(WPPIZZA_SLUG,'additives_'. $k.'', $str);
		}}}
		/***************************************************sizes***************************************************/
		if(isset($_POST[''.$this->pluginSlug.'_sizes']) || !empty($plugin_update) ){
		if(isset($options['sizes']) && is_array($options['sizes'])){
		foreach($options['sizes'] as $k=>$arr){
			foreach($arr as $sKey=>$sArr){
				icl_register_string(WPPIZZA_SLUG,'sizes_'. $k.'_'.$sKey.'', $sArr['lbl']);
			}
		}}}
		/***************************************************order form**********************************************/
		if(isset($_POST[''.$this->pluginSlug.'_order_form']) || !empty($plugin_update) ){
			/** order form**/
			if(isset($options['order_form']) && is_array($options['order_form'])){
			foreach($options['order_form'] as $k=>$arr){
				icl_register_string(WPPIZZA_SLUG,'order_form_'. $k.'', $arr['lbl']);
			}}
			/**confirmation_form**/
			if(isset($options['confirmation_form']) && is_array($options['confirmation_form'])){
			foreach($options['confirmation_form'] as $k=>$arr){
				icl_register_string(WPPIZZA_SLUG,'confirmation_form_'. $k.'', $arr['lbl']);
			}}
			/**localization_confirmation_form**/
			if(isset($options['localization_confirmation_form']) && is_array($options['localization_confirmation_form'])){
			foreach($options['localization_confirmation_form'] as $k=>$arr){
				icl_register_string(WPPIZZA_SLUG,'confirmation_'. $k.'', $arr['lbl']);
			}}
		}
		/************************************************order settings*******************************************/
		if(isset($_POST[''.$this->pluginSlug.'_order']) || !empty($plugin_update) ){
			
			/**order from**/
			if(isset($options['order']['order_email_from'])){
				icl_register_string(WPPIZZA_SLUG,'order_email_from',$options['order']['order_email_from']);
			}
			/**order from name**/
			if(isset($options['order']['order_email_from_name'])){
				icl_register_string(WPPIZZA_SLUG,'order_email_from_name', $options['order']['order_email_from_name']);		
			}
		
			/**order email to **/
			if(isset($options['order']['order_email_to']) && is_array($options['order']['order_email_to'])){
			foreach($options['order']['order_email_to'] as $k=>$arr){
				icl_register_string(WPPIZZA_SLUG,'order_email_to_'. $k.'', $arr);
			}}		
			/**order email bcc **/
			if(isset($options['order']['order_email_bcc']) && is_array($options['order']['order_email_bcc'])){
			foreach($options['order']['order_email_bcc'] as $k=>$arr){
				icl_register_string(WPPIZZA_SLUG,'order_email_bcc_'. $k.'', $arr);
			}}		
		
			/**order email attachments **/
			if(isset($options['order']['order_email_attachments']) && is_array($options['order']['order_email_attachments'])){
			foreach($options['order']['order_email_attachments'] as $k=>$arr){
				icl_register_string(WPPIZZA_SLUG,'order_email_attachments_'. $k.'', $arr);
			}}
		}
		/**********************************************global settings**************************************************/
		if(isset($_POST[''.$this->pluginSlug.'_global']) || !empty($plugin_update) ){
			/**single item permalink**/
			icl_register_string(WPPIZZA_SLUG,'single_item_permalink_rewrite', $options['plugin_data']['single_item_permalink_rewrite']);
		}
		/**********************************************global settings**************************************************/
		if(isset($_POST[''.$this->pluginSlug.'_gateways']) || !empty($plugin_update) ){
			/**gateways select label**/
			icl_register_string(WPPIZZA_SLUG,'gateway_select_label', $options['gateways']['gateway_select_label']);
			/**all other gateway strings are registered via wppizza_gateway_register_wpml_variables to be in their own context in WPML string translation*/
		}
	}
?>