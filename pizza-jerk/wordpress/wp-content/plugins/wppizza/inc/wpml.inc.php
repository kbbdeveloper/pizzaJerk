<?php if ( ! defined( 'ABSPATH' ) ) exit;/*Exit if accessed directly*/ ?>
<?php
	/*no need to run this when no wpml or on wppizza first install*/
	if($this->pluginOptions!=0) {

		/******************************************get icl_object_ids if wpml function exists***************************************************************/

		if(function_exists('icl_object_id')){
			/*get wpml'ed order page*/
			$this->pluginOptions['order']['orderpage']=icl_object_id($this->pluginOptions['order']['orderpage'],'page', true);
		}

		/******************************************get icl_translate if wpml function exists***************************************************************/
		if(function_exists('icl_translate')){

			/**only for non admin or ajax requests with wppizza_json action**/
			if ( !is_admin() || ( is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX && ( isset($_POST['action']) && $_POST['action']=='wppizza_json')) ){


				/**localization**/
				if(isset($this->pluginOptions['localization']) && is_array($this->pluginOptions['localization'])){
				foreach($this->pluginOptions['localization'] as $k=>$arr){
					$this->pluginOptions['localization'][$k]['lbl'] = icl_translate(WPPIZZA_SLUG,''. $k.'', $arr['lbl']);
				}}

				/**additives**/
				if(isset($this->pluginOptions['additives']) && is_array($this->pluginOptions['additives'])){
				foreach($this->pluginOptions['additives'] as $k=>$str){
					if(is_array($str) && isset($str['name'])){$str=$str['name'];}/*legacy*/
					$this->pluginOptions['additives'][$k] = icl_translate(WPPIZZA_SLUG,'additives_'. $k.'', $str);
				}}

				/**sizes**/
				if(isset($this->pluginOptions['sizes']) && is_array($this->pluginOptions['sizes'])){
				foreach($this->pluginOptions['sizes'] as $k=>$arr){
					foreach($arr as $sKey=>$sArr){
						$this->pluginOptions['sizes'][$k][$sKey]['lbl'] = icl_translate(WPPIZZA_SLUG,'sizes_'. $k.'_'.$sKey.'', $sArr['lbl']);
					}
				}}

				/**order_form**/
				if(isset($this->pluginOptions['order_form']) && is_array($this->pluginOptions['order_form'])){
				foreach($this->pluginOptions['order_form'] as $k=>$arr){
					$this->pluginOptions['order_form'][$k]['lbl'] = icl_translate(WPPIZZA_SLUG,'order_form_'. $k.'', $arr['lbl']);
				}}

				/**confirmation_form**/
				if(isset($this->pluginOptions['confirmation_form']) && is_array($this->pluginOptions['confirmation_form'])){
				foreach($this->pluginOptions['confirmation_form'] as $k=>$arr){
					$this->pluginOptions['confirmation_form'][$k]['lbl'] = icl_translate(WPPIZZA_SLUG,'confirmation_form_'. $k.'', $arr['lbl']);
				}}

				/**localization_confirmation_form**/
				if(isset($this->pluginOptions['localization_confirmation_form']) && is_array($this->pluginOptions['localization_confirmation_form'])){
				foreach($this->pluginOptions['localization_confirmation_form'] as $k=>$arr){
					$this->pluginOptions['localization_confirmation_form'][$k]['lbl'] = icl_translate(WPPIZZA_SLUG,'confirmation_'. $k.'', $arr['lbl']);
				}}


				/**order**/
				$this->pluginOptions['order']['order_email_from'] = icl_translate(WPPIZZA_SLUG,'order_email_from', $this->pluginOptions['order']['order_email_from']);
				$this->pluginOptions['order']['order_email_from_name'] = icl_translate(WPPIZZA_SLUG,'order_email_from_name', $this->pluginOptions['order']['order_email_from_name']);

				/**order email to **/
				if(isset($this->pluginOptions['order']['order_email_to']) && is_array($this->pluginOptions['order']['order_email_to'])){
				foreach($this->pluginOptions['order']['order_email_to'] as $k=>$arr){
					$this->pluginOptions['order']['order_email_to'][$k] = icl_translate(WPPIZZA_SLUG,'order_email_to_'.$k.'', $arr);
				}}
				/**order email bcc **/
				if(isset($this->pluginOptions['order']['order_email_bcc']) && is_array($this->pluginOptions['order']['order_email_bcc'])){
				foreach($this->pluginOptions['order']['order_email_bcc'] as $k=>$arr){
					$this->pluginOptions['order']['order_email_bcc'][$k] = icl_translate(WPPIZZA_SLUG,'order_email_bcc_'. $k.'', $arr);
				}}
				/**order email attachments **/
				if(isset($this->pluginOptions['order']['order_email_attachments']) && is_array($this->pluginOptions['order']['order_email_attachments'])){
				foreach($this->pluginOptions['order']['order_email_attachments'] as $k=>$arr){
					$this->pluginOptions['order']['order_email_attachments'][$k] = icl_translate(WPPIZZA_SLUG,'order_email_attachments_'. $k.'', $arr);
				}}


				/**single item permalink**/
				$this->pluginOptions['plugin_data']['single_item_permalink_rewrite'] = icl_translate(WPPIZZA_SLUG,'single_item_permalink_rewrite', $this->pluginOptions['plugin_data']['single_item_permalink_rewrite']);

				/**gateways select label**/
				$this->pluginOptions['gateways']['gateway_select_label'] = icl_translate(WPPIZZA_SLUG,'gateway_select_label', $this->pluginOptions['gateways']['gateway_select_label']);
			}
		}
	}
?>