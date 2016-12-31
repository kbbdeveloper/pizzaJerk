<?php if ( ! defined( 'ABSPATH' ) ) exit;/*Exit if accessed directly*/ ?>
<?php
	/**get previously saved options**/
	$options = $this->pluginOptions;

		/**lets not forget static, uneditable options **/
		$options['plugin_data']['version'] = $this->pluginVersion;
		$options['plugin_data']['mysql_version_ok'] = isset($input['plugin_data']['mysql_version_ok']) ? $input['plugin_data']['mysql_version_ok'] : $options['plugin_data']['mysql_version_ok'];
		$options['plugin_data']['nag_notice'] = isset($input['plugin_data']['nag_notice']) ? $input['plugin_data']['nag_notice'] : $options['plugin_data']['nag_notice'];
		$options['plugin_data']['db_order_status_options'] = !empty($input['plugin_data']['db_order_status_options']) ? $input['plugin_data']['db_order_status_options'] : wppizza_order_status_default('keys');

		/**schedule cron**/
		if(isset($input['cron'])){
			$options['cron']['days_delete']= !empty($input['cron']['days_delete']) ? max(1,$input['cron']['days_delete']) : 7;
			$options['cron']['failed_delete']= !empty($input['cron']['failed_delete']) ? true : false;

			$cronSchedule='';
			if(isset($input['cron']['schedule']) && in_array($input['cron']['schedule'],array('hourly','daily')) ){$cronSchedule=$input['cron']['schedule'];}

			$options['cron']['schedule']= $cronSchedule;
			/*schedule or remove cron **/
			$this->wppizza_cron_setup_schedule($options['cron']);
		}


		/*******************************
		*
		*	[global/plugin data settings]
		*
		*******************************/
		if(isset($_POST[''.$this->pluginSlug.'_global'])){
			/**submitted options -> validate***/
			$options['plugin_data']['js_in_footer'] = !empty($input['plugin_data']['js_in_footer']) ? true : false;
			$options['plugin_data']['ssl_on_checkout'] = !empty($input['plugin_data']['ssl_on_checkout']) ? true : false;
			$options['plugin_data']['admin_order_history_max_results'] = wppizza_validate_int_only($input['plugin_data']['admin_order_history_max_results']);
			$options['plugin_data']['admin_order_history_include_failed'] = !empty($input['plugin_data']['admin_order_history_include_failed']) ? true : false;
			$options['plugin_data']['admin_order_history_polling_time'] = wppizza_validate_int_only($input['plugin_data']['admin_order_history_polling_time']);
			$options['plugin_data']['admin_order_history_polling_auto'] = !empty($input['plugin_data']['admin_order_history_polling_auto']) ? true : false;
			$options['plugin_data']['using_cache_plugin'] = !empty($input['plugin_data']['using_cache_plugin']) ? true : false;
			$options['plugin_data']['mail_type'] = wppizza_validate_alpha_only($input['plugin_data']['mail_type']);
			$options['plugin_data']['dequeue_scripts'] = wppizza_validate_alpha_only($input['plugin_data']['dequeue_scripts']);
			$options['plugin_data']['search_include'] = !empty($input['plugin_data']['search_include']) ? true : false;
			$options['plugin_data']['use_old_admin_order_print'] = !empty($input['plugin_data']['use_old_admin_order_print']) ? true : false;
			$options['plugin_data']['experimental_js'] = !empty($input['plugin_data']['experimental_js']) ? true : false;
			$options['plugin_data']['always_load_all_scripts_and_styles'] = !empty($input['plugin_data']['always_load_all_scripts_and_styles']) ? true : false;

			/**smtp vars**/
			$options['plugin_data']['smtp_enable'] = !empty($input['plugin_data']['smtp_enable']) ? true : false;
			$options['plugin_data']['smtp_host']=wppizza_validate_string($input['plugin_data']['smtp_host']);
			$options['plugin_data']['smtp_port']=wppizza_validate_int_only($input['plugin_data']['smtp_port']);
			$options['plugin_data']['smtp_encryption']=wppizza_validate_string($input['plugin_data']['smtp_encryption']);
			$options['plugin_data']['smtp_authentication'] = !empty($input['plugin_data']['smtp_authentication']) ? true : false;
			$options['plugin_data']['smtp_username']=wppizza_validate_string($input['plugin_data']['smtp_username']);
			/*lets not override set ones as we will not display it in the input*/
			if(!empty($input['plugin_data']['smtp_password'])){
				$options['plugin_data']['smtp_password']=wppizza_encrypt_decrypt($input['plugin_data']['smtp_password']);
			}
			$options['plugin_data']['smtp_debug'] = !empty($input['plugin_data']['smtp_debug']) ? true : false;

			/*multisite vars**/
			$options['plugin_data']['wp_multisite_session_per_site'] = !empty($input['plugin_data']['wp_multisite_session_per_site']) ? true : false;
			$options['plugin_data']['wp_multisite_reports_all_sites'] = !empty($input['plugin_data']['wp_multisite_reports_all_sites']) ? true : false;
			$options['plugin_data']['wp_multisite_order_history_all_sites'] = !empty($input['plugin_data']['wp_multisite_order_history_all_sites']) ? true : false;

			/**save as array so - in future - we can enable / disable parts with checkboxes etc without having to use filters or editing templates*/
			$options['plugin_data']['wp_multisite_order_history_print'] =array();//default
			if(is_multisite()){
				$options['plugin_data']['wp_multisite_order_history_print']['header_from_child']  = !empty($input['plugin_data']['wp_multisite_order_history_print']['header_from_child']) ? true : false;
				$options['plugin_data']['wp_multisite_order_history_print']['multisite_info']  = !empty($input['plugin_data']['wp_multisite_order_history_print']['multisite_info']) ? true : false;
			}

			/*if not multisite use defaults*/
			if(!is_multisite()){
				$options['plugin_data']['wp_multisite_session_per_site'] =true;
				$options['plugin_data']['wp_multisite_reports_all_sites'] =false;
				$options['plugin_data']['wp_multisite_order_history_all_sites'] =false;
			}
		}

		/**what template are we using for single results ?**/
		if(isset($input['plugin_data']['post_single_template'])){
			$options['plugin_data']['post_single_template'] = !empty($input['plugin_data']['post_single_template']) ? (int)$input['plugin_data']['post_single_template'] : '';
		}

		/**sets custom menu as child of a parent page**/
		if(isset($input['plugin_data']['category_parent_page'])){
			$options['plugin_data']['category_parent_page'] = !empty($input['plugin_data']['category_parent_page']) ? (int)$input['plugin_data']['category_parent_page'] : '';
		}

		/**sets single item permalink**/
		if(isset($input['plugin_data']['single_item_permalink_rewrite'])){
			$options['plugin_data']['single_item_permalink_rewrite'] = sanitize_title($input['plugin_data']['single_item_permalink_rewrite']);
		}

		/*******************************
		*
		*	[layout settings]
		*
		*******************************/
		if(isset($input['layout']['category_sort'])){/*i dont think this input even exists on any settings page, but might get called on update of plugin. to check one day*/
			$options['layout']['category_sort']=$input['layout']['category_sort'];
		}

		if(isset($input['layout']['category_sort_hierarchy'])){/*i dont think this input even exists on any settings page, but might get called on update of plugin. to check one day*/
			$options['layout']['category_sort_hierarchy']=$input['layout']['category_sort_hierarchy'];
		}

		/*set number of items per loop. must be >= get_option('posts_per_page ')*/
		if(isset($input['layout']['items_per_loop'])){
			/*if minus=>set to -1**/
			if(substr($input['layout']['items_per_loop'],0,1)=='-'){
				$set='-1';
			}else{/*else mk int**/
				if((int)$input['layout']['items_per_loop']>=get_option('posts_per_page ')){
					$set=(int)$input['layout']['items_per_loop'];
				}else{
					$set=get_option('posts_per_page ');
				}
			}

			$options['layout']['items_per_loop']=$set;
		}


		if(isset($_POST[''.$this->pluginSlug.'_layout'])){
			$options['layout']['include_css'] = !empty($input['layout']['include_css']) ? true : false;
			$options['layout']['css_priority'] = wppizza_validate_int_only($input['layout']['css_priority']);
			$options['layout']['hide_decimals'] = !empty($input['layout']['hide_decimals']) ? true : false;
			$options['layout']['style'] = wppizza_validate_alpha_only($input['layout']['style']);
			$options['layout']['style_grid_columns'] = ((int)$input['layout']['style_grid_columns']>0) ? (int)$input['layout']['style_grid_columns'] : 1;
			$options['layout']['style_grid_margins'] = (float)$input['layout']['style_grid_margins'];
			$options['layout']['style_grid_full_width'] = ((int)$input['layout']['style_grid_full_width']>0) ? (int)$input['layout']['style_grid_full_width'] : 480;
			$options['layout']['placeholder_img'] = !empty($input['layout']['placeholder_img']) ? true : false;
			$options['layout']['suppress_loop_headers'] = !empty($input['layout']['suppress_loop_headers']) ? true : false;
			$options['layout']['hide_cart_icon'] = !empty($input['layout']['hide_cart_icon']) ? true : false;
			$options['layout']['hide_item_currency_symbol'] = !empty($input['layout']['hide_item_currency_symbol']) ? true : false;
			$options['layout']['hide_single_pricetier'] = !empty($input['layout']['hide_single_pricetier']) ? true : false;
			$options['layout']['hide_prices'] = !empty($input['layout']['hide_prices']) ? true : false;
			$options['layout']['disable_online_order'] = !empty($input['layout']['disable_online_order']) ? true : false;
			$options['layout']['add_to_cart_on_title_click'] = !empty($input['layout']['add_to_cart_on_title_click']) ? true : false;
			$options['layout']['currency_symbol_left'] = !empty($input['layout']['currency_symbol_left']) ? true : false;
			$options['layout']['currency_symbol_position'] = preg_replace("/[^a-z]/","",$input['layout']['currency_symbol_position']);
			$options['layout']['show_currency_with_price'] = wppizza_validate_int_only($input['layout']['show_currency_with_price']);
			$options['layout']['cart_increase'] = !empty($input['layout']['cart_increase']) ? true : false;
			$options['layout']['order_page_quantity_change'] = !empty($input['layout']['order_page_quantity_change']) ? true : false;
			$options['layout']['order_page_quantity_change_left'] = !empty($input['layout']['order_page_quantity_change_left']) ? true : false;
			$options['layout']['order_page_quantity_change_style']=wppizza_validate_string($input['layout']['order_page_quantity_change_style']);
			$options['layout']['prettyPhoto'] = !empty($input['layout']['prettyPhoto']) ? true : false;
			$options['layout']['prettyPhotoStyle']=wppizza_validate_string($input['layout']['prettyPhotoStyle']);
			$options['layout']['empty_cart_button'] = !empty($input['layout']['empty_cart_button']) ? true : false;
			$options['layout']['items_group_sort_print_by_category'] = !empty($input['layout']['items_group_sort_print_by_category']) ? true : false;
			$options['layout']['items_sort_orderby'] = (in_array($input['layout']['items_sort_orderby'],array('menu_order','title','ID','date'))) ? $input['layout']['items_sort_orderby'] : 'menu_order';
			$options['layout']['items_sort_order'] = (in_array($input['layout']['items_sort_order'],array('ASC','DESC'))) ? $input['layout']['items_sort_order'] : 'ASC';
			$options['layout']['items_blog_hierarchy'] = !empty($input['layout']['items_blog_hierarchy']) ? true : false;
			$options['layout']['items_category_hierarchy'] = preg_replace("/[^a-z]/","",$input['layout']['items_category_hierarchy']);
			$options['layout']['items_blog_hierarchy_cart'] = !empty($input['layout']['items_blog_hierarchy_cart']) ? true : false;
			$options['layout']['items_category_hierarchy_cart'] = preg_replace("/[^a-z]/","",$input['layout']['items_category_hierarchy_cart']);
			$options['layout']['items_category_separator']=wppizza_validate_string($input['layout']['items_category_separator']);
			$options['layout']['sticky_cart_animation']=absint($input['layout']['sticky_cart_animation']);
			$options['layout']['sticky_cart_animation_style']=wppizza_validate_string($input['layout']['sticky_cart_animation_style']);
			$options['layout']['sticky_cart_margin_top']=absint($input['layout']['sticky_cart_margin_top']);
			$options['layout']['sticky_cart_background']=preg_replace("/[^a-zA-Z0-9#]/","",$input['layout']['sticky_cart_background']);
			$options['layout']['sticky_cart_limit_bottom_elm_id']=preg_replace("/[^a-zA-Z0-9_-]/","",$input['layout']['sticky_cart_limit_bottom_elm_id']);
			$options['layout']['jquery_fb_add_to_cart'] = !empty($input['layout']['jquery_fb_add_to_cart']) ? true : false;
			$options['layout']['jquery_fb_add_to_cart_ms']=absint($input['layout']['jquery_fb_add_to_cart_ms']);
			$options['layout']['element_name_refresh_page']=wppizza_validate_string($input['layout']['element_name_refresh_page']);
			$options['layout']['minicart_max_width_active']=wppizza_validate_int_only($input['layout']['minicart_max_width_active']);
			$options['layout']['minicart_elm_padding_top']=wppizza_validate_int_only($input['layout']['minicart_elm_padding_top']);
			$options['layout']['minicart_add_to_element']=preg_replace("/[^a-zA-Z0-9#>\-_\., ]/","",$input['layout']['minicart_add_to_element']);
			$options['layout']['minicart_elm_padding_selector']=preg_replace("/[^a-zA-Z0-9#>\-_\., ]/","",$input['layout']['minicart_elm_padding_selector']);
			$options['layout']['minicart_always_shown'] = !empty($input['layout']['minicart_always_shown']) ? true : false;
			$options['layout']['minicart_viewcart'] = !empty($input['layout']['minicart_viewcart']) ? true : false;
			/**opening_times_format**/
			$options['opening_times_format']['hour']=wppizza_validate_string($input['opening_times_format']['hour']);
			$options['opening_times_format']['separator']=wppizza_validate_string($input['opening_times_format']['separator']);
			$options['opening_times_format']['minute']=wppizza_validate_string($input['opening_times_format']['minute']);
			$options['opening_times_format']['ampm']=wppizza_validate_string($input['opening_times_format']['ampm']);


		}
		/*******************************
		*
		*	[opening times settings]
		*
		*******************************/
		if(isset($_POST[''.$this->pluginSlug.'_opening_times'])){
			
			$options['globals']['close_shop_now'] = !empty($input['globals']['close_shop_now']) ? true : false;
			
			
			$options['opening_times_standard'] = array();//initialize array
			ksort($input['opening_times_standard']);//just for consistency. not really necessary though
			foreach($input['opening_times_standard'] as $k=>$v){
				foreach($v as $l=>$m){
				$options['opening_times_standard'][$k][$l]=wppizza_validate_24hourtime($m);
				}
			}

			$options['opening_times_custom'] = array();//initialize array
			if(isset($input['opening_times_custom'])){
			foreach($input['opening_times_custom'] as $k=>$v){
				foreach($v as $l=>$m){
					if($k=='date'){
						$options['opening_times_custom'][$k][$l]=wppizza_validate_date($m,'Y-m-d');
					}else{
						$options['opening_times_custom'][$k][$l]=wppizza_validate_24hourtime($m);
					}
				}
			}}

			$options['times_closed_standard'] = array();//initialize array
			if(isset($input['times_closed_standard'])){
				foreach($input['times_closed_standard'] as $k=>$v){
					foreach($v as $l=>$m){
						if($k=='day'){
							$options['times_closed_standard'][$l][$k]=(int)$m;
						}else{
							$options['times_closed_standard'][$l][$k]=wppizza_validate_24hourtime($m);
						}
					}
				}
			}
		}
		/*******************************
		*
		*	[order settings]
		*
		*******************************/
		if(isset($_POST[''.$this->pluginSlug.'_order'])){
			$options['order'] = array();//initialize array
			$options['order']['currency'] = strtoupper($input['order']['currency']);//validation a bit overkill, but then again, why not
				$displayCurrency=wppizza_currencies($input['order']['currency'],true);
			$options['order']['currency_symbol'] = $displayCurrency['val'];
			$options['order']['orderpage'] = !empty($input['order']['orderpage']) ? (int)$input['order']['orderpage'] : false;
			$options['order']['orderpage_exclude']=!empty($input['order']['orderpage_exclude']) ? true : false;
			$options['order']['order_pickup']=!empty($input['order']['order_pickup']) ? true : false;
			$options['order']['order_pickup_alert']=!empty($input['order']['order_pickup_alert']) ? true : false;
			$options['order']['order_pickup_alert_confirm']=!empty($input['order']['order_pickup_alert_confirm']) ? true : false;
			$options['order']['order_pickup_as_default']=!empty($input['order']['order_pickup_as_default']) ? true : false;
			$options['order']['order_pickup_discount']=wppizza_validate_float_pc($input['order']['order_pickup_discount']);
			$options['order']['order_min_for_delivery']=wppizza_validate_float_only($input['order']['order_min_for_delivery']);
			$options['order']['order_min_for_pickup']=wppizza_validate_float_only($input['order']['order_min_for_pickup']);
			$options['order']['order_min_on_totals']=!empty($input['order']['order_min_on_totals']) ? true : false;			
			$options['order']['order_pickup_display_location'] = wppizza_validate_int_only($input['order']['order_pickup_display_location']);

			$options['order']['delivery_selected'] = wppizza_validate_alpha_only($input['order']['delivery_selected']);
			$options['order']['discount_selected'] = wppizza_validate_alpha_only($input['order']['discount_selected']);

			$options['order']['delivery'] = array();
			foreach($input['order']['delivery'] as $k=>$v){
				foreach($v as $l=>$m){
					if($l!='deliver_below_total'){
						$options['order']['delivery'][$k][$l]=wppizza_validate_float_only($m,2);
					}
				}
				if($k=='minimum_total'){
					$options['order']['delivery'][$k]['deliver_below_total']=!empty($input['order']['delivery'][$k]['deliver_below_total']) ? true : false;
					$options['order']['delivery'][$k]['deliverycharges_below_total']=wppizza_validate_float_only($input['order']['delivery'][$k]['deliverycharges_below_total']);
				}
			}
			/**hardcode no_delivery (as there are  no submitted input values)*/
					$options['order']['delivery']['no_delivery']='';


			$options['order']['discounts'] = array();//initialize array
			$options['order']['discounts']['none'] = array();//add distinctly as it has no array associated with it
			foreach($input['order']['discounts'] as $a=>$b){
				foreach($b as $c=>$d){
					foreach($d as $e=>$f){
						foreach($f as $g=>$h){
							if($a=='percentage' && $g=='discount'){
								$options['order']['discounts'][$a][$c][$e][$g]=wppizza_validate_float_pc($h);
							}else{
								$options['order']['discounts'][$a][$c][$e][$g]=wppizza_validate_float_only($h,2);
							}
						}
					}
				}
			}

			$options['order']['delivery_calculation_exclude_item'] = !empty($input['order']['delivery_calculation_exclude_item']) ? $input['order']['delivery_calculation_exclude_item'] : array();
			$options['order']['delivery_calculation_exclude_cat'] = !empty($input['order']['delivery_calculation_exclude_cat']) ? array_combine($input['order']['delivery_calculation_exclude_cat'],$input['order']['delivery_calculation_exclude_cat']) : array();/*makes keys == values*/
			$options['order']['discount_calculation_exclude_item'] = !empty($input['order']['discount_calculation_exclude_item']) ? array_combine($input['order']['discount_calculation_exclude_item'],$input['order']['discount_calculation_exclude_item']) : array();/*makes keys == values*/
			$options['order']['discount_calculation_exclude_cat'] = !empty($input['order']['discount_calculation_exclude_cat']) ? array_combine($input['order']['discount_calculation_exclude_cat'],$input['order']['discount_calculation_exclude_cat']) : array();/*makes keys == values*/

			$options['order']['item_tax']=wppizza_validate_float_pc($input['order']['item_tax'],5);//5 decimals should really be enough i would have thought
			$options['order']['item_tax_alt']=wppizza_validate_float_pc($input['order']['item_tax_alt'],5);//5 decimals should really be enough i would have thought
			$options['order']['taxes_included'] = !empty($input['order']['taxes_included']) ? true : false;
			$options['order']['taxes_round_natural'] = !empty($input['order']['taxes_round_natural']) ? true : false;
			$options['order']['shipping_tax'] = !empty($input['order']['shipping_tax']) ? true : false;
			$options['order']['shipping_tax_rate']=wppizza_validate_float_pc($input['order']['shipping_tax_rate'],5);//5 decimals should really be enough i would have thought
			$options['order']['append_internal_id_to_transaction_id'] = !empty($input['order']['append_internal_id_to_transaction_id']) ? true : false;


			$options['order']['order_email_to'] = wppizza_validate_email_array($input['order']['order_email_to']);
			$options['order']['order_email_bcc'] = wppizza_validate_email_array($input['order']['order_email_bcc']);
			$options['order']['order_email_attachments'] = wppizza_strtoarray($input['order']['order_email_attachments']);
			$emailFrom=wppizza_validate_email_array($input['order']['order_email_from']);/*validated as array but we only store the first value as string*/
			$options['order']['order_email_from'] = !empty($emailFrom[0]) ? ''.$emailFrom[0].'' : '' ;
			$options['order']['order_email_from_name'] = wppizza_validate_string($input['order']['order_email_from_name']);

			/**dmarc nag**/
			$options['order']['dmarc_nag_off']= !empty($input['order']['dmarc_nag_off']) ? true : false;
		}
		/*******************************
		*
		*	[order form]
		*
		*******************************/
		if(isset($_POST[''.$this->pluginSlug.'_order_form'])){
			foreach($input['order_form'] as $a=>$b){
				$options['order_form'][$a]['sort'] = (int)($input['order_form'][$a]['sort']);
				$options['order_form'][$a]['key'] = $options['order_form'][$a]['key'];
				$options['order_form'][$a]['lbl'] = wppizza_validate_string($input['order_form'][$a]['lbl']);
				$options['order_form'][$a]['type'] = wppizza_validate_letters_only($input['order_form'][$a]['type']);
				$options['order_form'][$a]['enabled'] = !empty($input['order_form'][$a]['enabled']) ? true : false;
				$options['order_form'][$a]['required'] = !empty($input['order_form'][$a]['required']) ? true : false;
				$options['order_form'][$a]['required_on_pickup'] = !empty($input['order_form'][$a]['required_on_pickup']) ? true : false;
				$options['order_form'][$a]['prefill'] = !empty($input['order_form'][$a]['prefill']) ? true : false;
				$options['order_form'][$a]['onregister'] = !empty($input['order_form'][$a]['onregister']) ? true : false;
				$options['order_form'][$a]['add_to_subject_line'] = !empty($input['order_form'][$a]['add_to_subject_line']) ? true : false;
				$options['order_form'][$a]['value'] = wppizza_strtoarray($input['order_form'][$a]['value']);
			}


			$options['confirmation_form_enabled'] = !empty($input['confirmation_form_enabled']) ? true : false;
			if(isset($input['confirmation_form']) && is_array($input['confirmation_form'])){
			$options['confirmation_form_amend_order_link'] = (int)$input['confirmation_form_amend_order_link'];
			foreach($input['confirmation_form'] as $a=>$b){
				$options['confirmation_form'][$a]['sort'] = (int)($input['confirmation_form'][$a]['sort']);
				$options['confirmation_form'][$a]['key'] = $options['confirmation_form'][$a]['key'];
				$options['confirmation_form'][$a]['lbl'] = wppizza_validate_string($input['confirmation_form'][$a]['lbl'],true);
				$options['confirmation_form'][$a]['type'] = wppizza_validate_letters_only($input['confirmation_form'][$a]['type']);
				$options['confirmation_form'][$a]['enabled'] = !empty($input['confirmation_form'][$a]['enabled']) ? true : false;
				$options['confirmation_form'][$a]['required'] = !empty($input['confirmation_form'][$a]['required']) ? true : false;
			}}else{
				$input['confirmation_form']=array();
				$options['confirmation_form_amend_order_link'] = '';
			}


			if(isset($input['confirmation_form']) && is_array($input['confirmation_form'])){
				if(isset($input['localization_confirmation_form'])){
				//$allowHtml=array('thank_you_p','jquery_fb_add_to_cart_info');/*array of items to allow html (such as tinymce textareas) */
				foreach($input['localization_confirmation_form'] as $a=>$b){
					/*add new value , but keep desciption (as its not editable on frontend)*/
					$html=false;
					//if(in_array($a,$allowHtml)){$html=1;}
					$options['localization_confirmation_form'][$a]=array('lbl'=>wppizza_validate_string($b,$html));
				}}
			}
		}
		/*******************************
		*
		*	[sizes]
		*
		*******************************/
		if(isset($_POST[''.$this->pluginSlug.'_sizes'])){
			$options['sizes'] = array();//initialize array
			if(isset($input['sizes'])){

			foreach($input['sizes'] as $a=>$b){
				$i=0;
				foreach($b as $c=>$d){
					if($i==0){
					$options['sizes'][$a][$c]['lbladmin']=wppizza_validate_string($d['lbladmin']);
					}
					$options['sizes'][$a][$c]['lbl']=wppizza_validate_string($d['lbl']);
					$options['sizes'][$a][$c]['price']=wppizza_validate_float_only($d['price'],2);
				$i++;
				}


			}}
		}
		/*******************************
		*
		*	[additives]
		*
		*******************************/
		if(isset($_POST[''.$this->pluginSlug.'_additives'])){
			$options['additives'] = array();//initialize array
			if(isset($input['additives'])){
			foreach($input['additives'] as $a=>$b){
				if(trim($b['name'])!=''){
					$sort= ($b['sort']!='') ? wppizza_validate_int_only($b['sort']) : '';
					$options['additives'][$a]=array('sort'=>$sort,'name'=>wppizza_validate_string($b['name']));
				}
			}}
		}

		/*******************************
		*
		*	[localization]
		*
		*******************************/
		if(isset($_POST[''.$this->pluginSlug.'_localization'])){
			if(isset($input['localization'])){
			$allowHtml=array('thank_you_p','order_ini_additional_info','jquery_fb_add_to_cart_info','register_option_create_account_info','register_option_create_account_error','header_order_print_shop_address');/*array of items to allow html (such as tinymce textareas) */
			foreach($input['localization'] as $a=>$b){
				/*add new value , but keep desciption (as its not editable on frontend)*/
				if(in_array($a,$allowHtml)){$html=1;}else{$html=false;}
				$options['localization'][$a]=array('lbl'=>wppizza_validate_string($b,$html));
			}}
		}

		/*******************************
		*
		*	[access level]
		*
		*******************************/
		if(isset($_POST['wppizza_access'])){
			$access=$this->wppizza_set_capabilities();
			//$roles=get_editable_roles();/*only get roles user is allowed to edit**/
			foreach($input['admin_access_caps'] as $roleName=>$v){
				$userRole = get_role($roleName);

				foreach($access as $akey=>$aVal){
					/**not checked, but previously selected->remove capability**/
					if(isset($userRole->capabilities[$aVal['cap']]) && ( !is_array($input['admin_access_caps'][$roleName]) || !isset($input['admin_access_caps'][$roleName][$aVal['cap']]))){
						$userRole->remove_cap( ''.$aVal['cap'].'' );
					}
					/**checked and NOT previously selected->add capability*/
					if(is_array($input['admin_access_caps'][$roleName]) && isset($input['admin_access_caps'][$roleName][$aVal['cap']]) && !isset($userRole->capabilities[$aVal['cap']])){
						$userRole->add_cap( ''.$aVal['cap'].'' );
					}
				}
			}
		}

		/*******************************
		*
		*	[gateways]
		*
		*******************************/
		if(isset($_POST[''.$this->pluginSlug.'_gateways'])){

			$options['gateways']['gateway_select_as_dropdown']=!empty($input['gateways']['gateway_select_as_dropdown'])? true : false;
			$options['gateways']['gateway_showorder_on_thankyou']=!empty($input['gateways']['gateway_showorder_on_thankyou'])? true : false;
			$options['gateways']['gateway_select_label']=wppizza_validate_string($input['gateways']['gateway_select_label']);

			/**sort selected gateway*/
			asort($input['gateways']['gateway_order']);

			$options['gateways']['gateway_selected']=array();
			$gwEnabledCount=0;
			foreach($input['gateways']['gateway_order'] as $gw=>$sort){
				$options['gateways']['gateway_selected'][$gw]=!empty($input['gateways']['gateway_selected'][$gw])? true : false;
				if(!empty($input['gateways']['gateway_selected'][$gw])){
				$gwEnabledCount++;
				}
			}
			$options['gateways']['gateway_enabled_count']=$gwEnabledCount;

			/**selected gateway*/
			$gateways=$this->wppizza_get_registered_gateways();

			foreach($gateways as $k=>$v){
				$updateGatewayOptions=array();
				foreach($v['gatewaySettings'] as $l=>$m){
					/*validate value according to callback*/
					if(isset($input['gateways'][$v['gatewayOptionsName']][$m['key']])){
						if($m['validateCallback']!=''){
							if(is_array($m['validateCallback'])){
								$val=$m['validateCallback'][0]($input['gateways'][$v['gatewayOptionsName']][$m['key']],$m['validateCallback'][1]);
							}else{
								$val=$m['validateCallback']($input['gateways'][$v['gatewayOptionsName']][$m['key']]);
							}
						}else{
							/*no callback defined*/
							$val=$input['gateways'][$v['gatewayOptionsName']][$m['key']];
						}
					}else{
						$val='';
					}
					$updateGatewayOptions[$m['key']]=$val;
				}

				/****add label and info*****/
					$lbl=wppizza_validate_string($input['gateways'][$v['gatewayOptionsName']]['gateway_label']);
				$updateGatewayOptions['gateway_label']=!empty($lbl) ? $lbl : $v['gatewayName'];
				$updateGatewayOptions['gateway_info']=wppizza_validate_string($input['gateways'][$v['gatewayOptionsName']]['gateway_info']);

				/****add any non-user-editable gateway specific options (version numbers for example)*****/
				if(isset($v['gatewaySettingsNonEditable']) && is_array($v['gatewaySettingsNonEditable'])){
					foreach($v['gatewaySettingsNonEditable'] as $neKey=>$neVal){
						$updateGatewayOptions[$neKey]=$neVal;
					}
				}
				/********update wpml******/
				$gwclass=new WPPIZZA_GATEWAYS();
				$gwclass->wppizza_gateway_register_wpml_variables($v['ident'],$v['gatewaySettings'], $updateGatewayOptions,true);
				/********update options******/
				update_option($v['gatewayOptionsName'],$updateGatewayOptions);
			}
		}

		/*******************************
		*
		*	[templates]
		*
		*******************************/
		if(isset($_POST[''.$this->pluginSlug.'_templates'])){

			/**loop through ($tplKey) emails and print */
			foreach($input['templates'] as $tplKey=>$tplVal){

			/***********************************************
			*
			*	get all existing first (as they might be paginated)
			*
			***********************************************/
			$options['templates'][$tplKey]=$this->pluginOptions['templates'][$tplKey];
			$options['templates_apply'][$tplKey]=$this->pluginOptions['templates_apply'][$tplKey];


			/*********************************************
			*
			*	for print only . set default printtemplate
			*
			*********************************************/
			if($tplKey=='print'){
				$options['templates_apply'][$tplKey]=(isset($tplVal['print_id']) && $tplVal['print_id']!='') ? (int)$tplVal['print_id'] : $options['templates_apply'][$tplKey] ;
			}

			/*********************************************
			*
			*	for emails only . set default recipients for shop and customer
			*
			*********************************************/
			if($tplKey=='emails'){

				foreach(wppizza_email_recipients() as $rKey=>$rVal){
					if(isset($tplVal['recipients_default'][$rKey])){
						$options['templates_apply'][$tplKey]['recipients_default'][$rKey]=$tplVal['recipients_default'][$rKey];
					}
				}
				/**this should never happen, but just to be sure set to default if we are missing an entry*/
				foreach(wppizza_email_recipients() as $rKey=>$rVal){
					if(!isset($options['templates_apply'][$tplKey]['recipients_default'][$rKey])){
						$options['templates_apply'][$tplKey]['recipients_default'][$rKey]=-1;
					}
				}
				/**additional recipients set in default*/
				if(trim($input['templates'][$tplKey]['recipients_additional'][-1])!=''){
					$add_recipients=explode(',',$input['templates'][$tplKey]['recipients_additional'][-1]);
					$add_recipients=array_unique($add_recipients);
					$options['templates_apply'][$tplKey]['recipients_additional'][-1]=!empty($add_recipients) ? $add_recipients : array();
				}else{
					unset($options['templates_apply'][$tplKey]['recipients_additional'][-1]);
				}
				/**overwrite mail delivery type from default template**/
					$options['plugin_data']['mail_type'] = wppizza_validate_alpha_only($input['plugin_data']['mail_type']);
			}

			/***********************************************
				unset deleted
				however, if we deleted a template that had a
				recipient set, we need to set the recipient to - 1
				to use default further down
			***********************************************/
			if(!empty($input['template_remove'][$tplKey]) && is_array($input['template_remove'][$tplKey])){
				foreach($input['template_remove'][$tplKey] as $iId){
					unset($options['templates'][$tplKey][$iId]);
				}
			}

			/***********************************************
			*
			*	overwrite/add as required per template
			*
			***********************************************/
				if(!empty($input['templates'][$tplKey])){

				/*skip the standard/defaults as there are no values associated with it **/
				if($tplKey=='emails'){
					unset($input['templates'][$tplKey]['recipients_default']);
					unset($input['templates'][$tplKey]['recipients_additional']);
					unset($input['templates'][$tplKey]['mail_delivery']);
				}
				if($tplKey=='print'){
					unset($input['templates'][$tplKey]['print_id']);
				}

				foreach($input['templates'][$tplKey] as $key=>$val){




					/**reset first**/
					$options['templates'][$tplKey][$key]=array();
					/*validate title*/
					$options['templates'][$tplKey][$key]['title']=!empty($val['title']) ? wppizza_validate_string($val['title']) : 'undefined';
					/*html or plaintext ? */
					$options['templates'][$tplKey][$key]['mail_type']=wppizza_validate_alpha_only($val['mail_type']);
					/*omit attachments ?*/
					$options['templates'][$tplKey][$key]['omit_attachments']=!empty($val['omit_attachments']) ? true : false;

					if($tplKey=='emails'){

						/*get/set additional recipients**/
						if(trim($val['recipients_additional'])!=''){
							$recipients_additional=explode(',',$val['recipients_additional']);
							$recipients_additional=array_unique($recipients_additional);
							$options['templates'][$tplKey][$key]['recipients_additional']=!empty($recipients_additional) ? $recipients_additional : array();
						}else{
							$recipients_additional=false;
							$options['templates'][$tplKey][$key]['recipients_additional']=array();

						}
						/************************************************************************************
						*
						*	also add to globally set additional recipients .....
						*
						*************************************************************************************/
						if(!empty($recipients_additional)){
							$options['templates_apply'][$tplKey]['recipients_additional'][$key]=$recipients_additional;
						}
						/*...or unset if empty and exists*/
						if(empty($recipients_additional) && isset(	$options['templates_apply'][$tplKey]['recipients_additional'][$key])){
							unset($options['templates_apply'][$tplKey]['recipients_additional'][$key]);
						}

					}
					/*
						order message variables
						sorted by part and variables
						as was "drag and dropped"
					*/
					$options['templates'][$tplKey][$key]['enabled']=array();
					$options['templates'][$tplKey][$key]['admin_sort']=!empty($val['admin_sort']) ? json_decode($val['admin_sort'],true) : false;
					$options['templates'][$tplKey][$key]['parts_label']=array();
					$options['templates'][$tplKey][$key]['values']=array();
					foreach($val['sort'] as $sortKey=>$sortVal){

						/*part enabled ?*/
						if(!empty($val['enabled'][$sortKey])){
							$options['templates'][$tplKey][$key]['enabled'][$sortKey]=$sortKey;
						}
						/*labels enabled ?*/
						if(!empty($val['parts_label'][$sortKey])){
							$options['templates'][$tplKey][$key]['parts_label'][$sortKey]=true;
						}
						/*values enabled*/
						if(is_array($sortVal)){
						foreach($sortVal as $partsVars){
							if(!empty($val['values'][$sortKey][$partsVars])){
								$options['templates'][$tplKey][$key]['values'][$sortKey][$partsVars]=$partsVars;
							}
						}}
					}
					/*
						html style declarations
					*/
					$styleValues=array();
					/*temp - todo -> sanitize*/
					$css=false;
					if($tplKey=='print'){
						$css=true;
					}
					$styleValues=$this->wppizza_html_sanitize_style($val['style'], $css);
					$options['templates'][$tplKey][$key]['style']=$styleValues;

				}}


				/*********************************************
				*	make double sure we are not left with orphaned
				*	templates_apply for templates that do not exist
				*	/ have been deleted
				*********************************************/
				if($tplKey=='emails'){
					//$options['templates_apply'][$tplKey]=(isset($tplVal['print_id']) && $tplVal['print_id']!='') ? (int)$tplVal['print_id'] : $options['templates_apply'][$tplKey] ;
					if(!empty($options['templates_apply'][$tplKey]['recipients_additional'])){
						foreach($options['templates_apply'][$tplKey]['recipients_additional'] as $raId=>$arr){
							if($raId!=-1){/*ignore defaults as it will always be set*/
							if(!isset($options['templates'][$tplKey][$raId])){
								unset($options['templates_apply'][$tplKey]['recipients_additional'][$raId]);
							}}
						}
					}
				}
			}
		}
/************************************************************************************************************************
*
*
*	[misc tools/maintenance functions]
*
*
************************************************************************************************************************/
	if(isset($input['tools'])){
		$options['tools']['debug'] =  !empty($input['tools']['debug']) ? true : false;
		$options['tools']['disable_emails'] =  !empty($input['tools']['disable_emails']) ? true : false;
	}

	if(isset($input['maintenance'])){
		/*********************************************************
			[repair categories sortorder]
			fix possibly messed up category sorting
			which might result in multiple display when using
			category=!all attribute or not visible cats in admin
			will resort by default
		*********************************************************/
		if(isset($input['maintenance']['category_repair'])){
			//$categorySort=$this->wppizza_maintenance_repair_category_sort();
			$categorySort=$this->wppizza_get_cats_hierarchy();

			/*overwrite old vars**/
			$options['layout']['category_sort_hierarchy']=$categorySort;
			$options['layout']['category_sort']=$categorySort;/*legacy*/
		}
		/**delete wppizza posts, categories and - possibly - images/attachments **/
		if(isset($input['maintenance']['empty_category_and_items']) && $input['maintenance']['empty_category_and_items']==1){
			$this->wppizza_empty_taxonomy(!empty($input['maintenance']['delete_attachments']) ? true : false);
		}
		/**truncate orders**/
		if(isset($input['maintenance']['truncate_orders'])){
			$this->wppizza_truncate_order_table();
		}
		/**update/alter  table if required **/
		if(!empty($input['maintenance']['check_order_table'])){
			require_once('admin.create.order.table.inc.php');
  			/*get mysql version if we can*/
  			$mysql_info=wppizza_get_mysql_version();
  			if( !empty($mysql_info['version']) && version_compare( $mysql_info['version'], $this->pluginMinMysqlVersionRequired, '<' )) {
  				$options['plugin_data']['mysql_version_ok']=false;
  			}else{
  				$options['plugin_data']['mysql_version_ok']=true;//will also be true if we cannot determine mysql version
  			}
		}
		
		/**initialize wpml strings **/
		if(!empty($input['maintenance']['wpml_ini_string'])){
			/** let's force pretend its an update */
			$plugin_update = true;
			$update_options = $options;
			/*(re)-register wpml strings*/
			require(WPPIZZA_PATH .'inc/wpml.register.strings.php');			
		}		
		
		
	}

	/**apply filters to validate options as required*/
	if(!empty($options)){
		$options=apply_filters('wppizza_filter_options_validate', $options, $input);
	}

?>