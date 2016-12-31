<?php if ( ! defined( 'ABSPATH' ) ) exit;/*Exit if accessed directly*/ ?>
<?php
		register_setting($this->pluginSlug,$this->pluginSlug, array( $this, 'wppizza_admin_options_validate') );

		$settings_sections=array();
		$settings_fields=array();

		/**global settings**/
		$settings_sections['global'] = array('global','', 'global');
		$settings_fields['global']['version'] = array('version', '<b>'.__('Plugin Version:', 'wppizza-locale').'</b>', 'global', 'global', 'version' );
		$settings_fields['global']['mail_type'] = array('mail_type', '<b>'.__('Select Type of Mail Delivery:', 'wppizza-locale').'</b>', 'global', 'global', 'mail_type' );
		$settings_fields['global']['search_include'] = array('search_include', '<b>'.__('Include wppizza menu items in regular search results:', 'wppizza-locale').'', 'global', 'global', 'search_include' );
		$settings_fields['global']['using_cache_plugin'] = array('using_cache_plugin', '<b>'.__('I am using a caching plugin:', 'wppizza-locale').'', 'global', 'global', 'using_cache_plugin' );
		$settings_fields['global']['ssl_on_checkout'] = array('ssl_on_checkout', '<b>'.__('SSL on checkout:', 'wppizza-locale').'', 'global', 'global', 'ssl_on_checkout' );
		$settings_fields['global']['js_in_footer'] = array('js_in_footer', '<b>'.__('Javascript in Footer:', 'wppizza-locale').'</b>', 'global', 'global', 'js_in_footer' );


		/**permalinks settings**/
		$settings_sections['permalinks'] = array('permalinks','', 'permalinks');
		$settings_fields['permalinks']['category_parent_page'] = array('category_parent_page', ''.__('<b>Permalinks - Categories/Pages:<br/>(only used and relevant when using widget or shortcode to display wppizza category navigation !!!)<br/><span style="color:red">when changing this setting, you MUST re-save your permalink settings</span></b><br/>(page cannot be used as static post page (wp settings) or have any children', 'wppizza-locale').'', 'permalinks', 'permalinks', 'category_parent_page' );
		$settings_fields['permalinks']['single_item_permalink_rewrite'] = array('single_item_permalink_rewrite', ''.__('<b>Permalinks - Single Menu Items:<br/>(only used and relevant when actually linking to a single item from anywhere)<br/><span style="color:red">when changing this setting, you MUST re-save your permalink settings</span></b>', 'wppizza-locale').'', 'permalinks', 'permalinks', 'single_item_permalink_rewrite' );

		/**multisite settings**/
		$settings_sections['multisite'] = array('multisite','', 'multisite');
		$settings_fields['multisite']['wp_multisite_session_per_site'] = array('wp_multisite_session_per_site', '<b>'.__('Cart per site:', 'wppizza-locale').'</b>', 'multisite', 'multisite', 'wp_multisite_session_per_site' );
		/*parentonly*/
		global $blog_id;
		if(is_multisite() && $blog_id==BLOG_ID_CURRENT_SITE){
			$settings_fields['multisite']['wp_multisite_reports_all_sites'] = array('wp_multisite_reports_all_sites', '<b>'.__('Reports all subsites (WPPizza->Reports):', 'wppizza-locale').'</b>', 'multisite', 'multisite', 'wp_multisite_reports_all_sites' );
			$settings_fields['multisite']['wp_multisite_order_history_all_sites'] = array('wp_multisite_order_history_all_sites', '<b>'.__('History all subsites (WPPizza->Order History):', 'wppizza-locale').'</b>', 'multisite', 'multisite', 'wp_multisite_order_history_all_sites' );
		}
		/**miscellaneous settings**/
		$settings_sections['global_miscellaneous'] = array('global_miscellaneous','', 'global_miscellaneous');
		$settings_fields['global_miscellaneous']['admin_order_history_max_results'] = array('admin_order_history_max_results', '<b>'.__('Results order history:', 'wppizza-locale').'</b>', 'global_miscellaneous', 'global_miscellaneous', 'admin_order_history_max_results' );
		$settings_fields['global_miscellaneous']['admin_order_history_polling_auto'] = array('admin_order_history_polling_auto', '<b>'.__('Order history - polling:', 'wppizza-locale').'</b>', 'global_miscellaneous', 'global_miscellaneous', 'admin_order_history_polling_auto' );
		$settings_fields['global_miscellaneous']['use_old_admin_order_print'] = array('use_old_admin_order_print', '<b>'.__('Old style order printing', 'wppizza-locale').'</b>', 'global_miscellaneous', 'global_miscellaneous', 'use_old_admin_order_print' );
		$settings_fields['global_miscellaneous']['always_load_all_scripts_and_styles'] = array('always_load_all_scripts_and_styles', '<b>'.__('Load css and js on all pages', 'wppizza-locale').'</b>', 'global_miscellaneous', 'global_miscellaneous', 'always_load_all_scripts_and_styles' );
		$settings_fields['global_miscellaneous']['dequeue_scripts'] = array('dequeue_scripts', '<b>'.__('Dequeue wppizza scripts:', 'wppizza-locale').'</b><br /><span style="color:red">'.__('NOTE: if you dequeue any script other plugins rely on you WILL break things', 'wppizza-locale').'</span>', 'global_miscellaneous', 'global_miscellaneous', 'dequeue_scripts' );
		$settings_fields['global_miscellaneous']['experimental_js'] = array('experimental_js', '<b>'.__('Experimental JavaScript', 'wppizza-locale').'</b>', 'global_miscellaneous', 'global_miscellaneous', 'experimental_js' );

		/**smtp settings**/
		$settings_sections['global_use_smtp'] = array('global_use_smtp','', 'global_use_smtp');
		$settings_fields['global_use_smtp']['smtp_enable'] = array('smtp_enable', '<b>'.__('Use SMTP', 'wppizza-locale').'</b>', 'global_use_smtp', 'global_use_smtp', 'smtp_enable' );
		$settings_fields['global_use_smtp']['smtp_host'] = array('smtp_host', '<b>'.__('SMTP Host', 'wppizza-locale').'</b>', 'global_use_smtp', 'global_use_smtp', 'smtp_host' );
		$settings_fields['global_use_smtp']['smtp_port'] = array('smtp_port', '<b>'.__('SMTP Port', 'wppizza-locale').'</b>', 'global_use_smtp', 'global_use_smtp', 'smtp_port' );
		$settings_fields['global_use_smtp']['smtp_encryption'] = array('smtp_encryption', '<b>'.__('SMTP Encryption', 'wppizza-locale').'</b>', 'global_use_smtp', 'global_use_smtp', 'smtp_encryption' );
		$settings_fields['global_use_smtp']['smtp_authentication'] = array('smtp_authentication', '<b>'.__('SMTP Authentication', 'wppizza-locale').'</b>', 'global_use_smtp', 'global_use_smtp', 'smtp_authentication' );
		$settings_fields['global_use_smtp']['smtp_username'] = array('smtp_username', '<b>'.__('SMTP Username', 'wppizza-locale').'</b>', 'global_use_smtp', 'global_use_smtp', 'smtp_username' );
		$settings_fields['global_use_smtp']['smtp_password'] = array('smtp_password', '<b>'.__('SMTP Password', 'wppizza-locale').'</b>', 'global_use_smtp', 'global_use_smtp', 'smtp_password' );
		$settings_fields['global_use_smtp']['smtp_debug'] = array('smtp_debug', '<b>'.__('SMTP Debug', 'wppizza-locale').'</b>', 'global_use_smtp', 'global_use_smtp', 'smtp_debug' );
		$settings_fields['global_use_smtp']['smtp_test'] = array('smtp_test', '<b>'.__('SMTP Test', 'wppizza-locale').'</b>', 'global_use_smtp', 'global_use_smtp', 'smtp_test' );


		/**gateways settings**/
		$settings_sections['gateways'] = array('gateways','', 'gateways');
		$settings_fields['gateways']['gateways'] = array('gateways', '<b>'.__('Set Gateway Options:', 'wppizza-locale').'</b>', 'gateways', 'gateways', 'gateways' );

		/**layout settings**/
		$settings_sections['layout'] = array('layout','', 'layout');
		$settings_fields['layout']['items_per_loop'] = array('items_per_loop', '<b>'.__('Menu Items per page:', 'wppizza-locale').'</b><br/>'.__('how many menu items per category page (displays pagination, if there are more menu items for the selected category)<br/>[options: -1=all, >1=items per page]<br/><span style="color:red">if not set to -1, it must be >= wordpress settings->reading->Blog pages show at most</span>', 'wppizza-locale').'', 'layout', 'layout', 'items_per_loop' );
		$settings_fields['layout']['include_css'] = array('include_css', '<b>'.__('Include CSS:', 'wppizza-locale').'</b><br/>'.__('include frontend css that came with this plugin (untick if you want to provide your own styles somewhere else)', 'wppizza-locale').'', 'layout', 'layout', 'include_css' );
		$settings_fields['layout']['style'] = array('style', '<b>'.__('Which style to use (if enabled above):', 'wppizza-locale').'</b>', 'layout', 'layout', 'style' );
		$settings_fields['layout']['items_sort_orderby'] = array('items_sort_orderby', ''.__('Menu items sort order (in category):', 'wppizza-locale').'', 'layout', 'layout', 'items_sort_orderby' );
		$settings_fields['layout']['items_group_sort_print_by_category'] = array('items_group_sort_print_by_category', ''.__('Group, sort and display menu items by category:', 'wppizza-locale').'', 'layout', 'layout', 'items_group_sort_print_by_category' );
		$settings_fields['layout']['opening_times_format'] = array('opening_times_format', '<b>'.__('Format of openingtimes (if displayed):', 'wppizza-locale').'</b>', 'layout', 'layout', 'opening_times_format' );
		$settings_fields['layout']['add_to_cart_on_title_click'] = array('add_to_cart_on_title_click', '<b>'.__('Add item to cart on click of *item title* if there is only one pricetier for a menu item:', 'wppizza-locale').'</b>', 'layout', 'layout', 'add_to_cart_on_title_click' );
		$settings_fields['layout']['jquery_feedback_added_to_cart'] = array('jquery_feedback_added_to_cart', '<b>'.__('Briefly display text in place of price when adding item to cart', 'wppizza-locale').'</b>', 'layout', 'layout', 'jquery_feedback_added_to_cart' );
		$settings_fields['layout']['placeholder_img'] = array('placeholder_img', '<b>'.__('Display placeholder image when no image associated with meal item:', 'wppizza-locale').'</b>', 'layout', 'layout', 'placeholder_img' );
		$settings_fields['layout']['prettyPhoto'] = array('prettyPhoto', '<b>'.__('Enable prettyPhoto (Lightbox Clone) on menu item images', 'wppizza-locale').'</b>', 'layout', 'layout', 'prettyPhoto' );
		$settings_fields['layout']['prettyPhotoStyle'] = array('prettyPhotoStyle', '<b>'.__('Set prettyPhoto Style', 'wppizza-locale').'</b>', 'layout', 'layout', 'prettyPhotoStyle' );
		$settings_fields['layout']['suppress_loop_headers'] = array('suppress_loop_headers', '<b>'.__('Globally suppress headers above list of menu items:', 'wppizza-locale').'</b>', 'layout', 'layout', 'suppress_loop_headers' );
		$settings_fields['layout']['hide_cart_icon'] = array('hide_cart_icon', '<b>'.__('Hide cart icon next to prices:', 'wppizza-locale').'</b>', 'layout', 'layout', 'hide_cart_icon' );
		$settings_fields['layout']['show_currency_with_price'] = array('show_currency_with_price', '<b>'.__('Show a currency symbol directly next to each price', 'wppizza-locale').'</b>', 'layout', 'layout', 'show_currency_with_price' );
		$settings_fields['layout']['hide_item_currency_symbol'] = array('hide_item_currency_symbol', '<b>'.__('Hide *main* currency symbol next to each menu item:', 'wppizza-locale').'</b><br/>'.__('won\'t affect cart, summaries or emails', 'wppizza-locale').'', 'layout', 'layout', 'hide_item_currency_symbol' );
		$settings_fields['layout']['currency_symbol_left'] = array('currency_symbol_left', '<b>'.__('Show *main* currency symbol on the left - if not set to hidden', 'wppizza-locale').'</b>', 'layout', 'layout', 'currency_symbol_left' );
		$settings_fields['layout']['currency_symbol_position'] = array('currency_symbol_position', '<b>'.__('All other [cart, order page, email] currency symbols', 'wppizza-locale').'</b>', 'layout', 'layout', 'currency_symbol_position' );
		$settings_fields['layout']['hide_single_pricetier'] = array('hide_single_pricetier', '<b>'.__('Hide pricetier name and cart icon if item has only one size:', 'wppizza-locale').'</b>', 'layout', 'layout', 'hide_single_pricetier' );
		$settings_fields['layout']['hide_prices'] = array('hide_prices', '<b>'.__('Hide prices altogether:', 'wppizza-locale').'</b><br/><span style="color:red">'.__('this will disable the adding of any item to the shoppingcart.', 'wppizza-locale').'</span><br/>'.__('Really only useful if you want to display your menu without offering online orders', 'wppizza-locale').'', 'layout', 'layout', 'hide_prices' );
		$settings_fields['layout']['hide_decimals'] = array('hide_decimals', '<b>'.__('Don\'t show decimals:', 'wppizza-locale').'</b><br/>'.__('[prices will be rounded if necessary]', 'wppizza-locale').'', 'layout', 'layout', 'hide_decimals' );
		$settings_fields['layout']['cart_increase'] = array('cart_increase', '<b>'.__('Enable increase/decrease of items in cart via input field/textbox', 'wppizza-locale').'</b>', 'layout', 'layout', 'cart_increase' );
		$settings_fields['layout']['order_page_quantity_change'] = array('order_page_quantity_change', '<b>'.__('Enable increase/decrease of items in order form via input field/textbox', 'wppizza-locale').'</b>', 'layout', 'layout', 'order_page_quantity_change' );
		$settings_fields['layout']['empty_cart_button'] = array('empty_cart_button', '<b>'.__('Enable "empty cart" button', 'wppizza-locale').'</b>', 'layout', 'layout', 'empty_cart_button' );
		$settings_fields['layout']['sticky_cart_settings'] = array('sticky_cart_settings', '<b>'.__('"sticky/scolling" cart settings [if used]', 'wppizza-locale').'</b>', 'layout', 'layout', 'sticky_cart_settings' );
		$settings_fields['layout']['minicart_max_width_active'] = array('minicart_max_width_active', '<b>'.__('minicart settings [if used in widget or shortcode]', 'wppizza-locale').'</b><br /><span style="color:red">'.__('could interfere with some layouts. if that is the case, using some of the options provided here with additional css declarations *might* let you get around this.', 'wppizza-locale').'</span>', 'layout', 'layout', 'minicart_max_width_active' );
		$settings_fields['layout']['element_name_refresh_page'] = array('element_name_refresh_page', '<b>'.__('page refresh element', 'wppizza-locale').'</b>', 'layout', 'layout', 'element_name_refresh_page' );
		$settings_fields['layout']['disable_online_order'] = array('disable_online_order', '<b>'.__('Completely disable online orders:', 'wppizza-locale').'</b><br/><span style="color:red">'.__('this will still display prices (unless set to be hidden above), but will disable shoppingcart and orderpage', 'wppizza-locale').'</span><br/>'.__('Useful if you want to display your menu and prices but without offering online orders.', 'wppizza-locale').'', 'layout', 'layout', 'disable_online_order' );




		/**opening times**/
		$settings_sections['opening_times'] = array('opening_times','', 'opening_times');
		$settings_fields['globals']['close_shop_now'] = array('close_shop_now', '<b>'.__('Close Shop:', 'wppizza-locale').'</b>', 'opening_times', 'opening_times', 'close_shop_now' );
		$settings_fields['opening_times']['opening_times_standard'] = array('opening_times_standard', '<b>'.__('Standard opening times:', 'wppizza-locale').'</b>', 'opening_times', 'opening_times', 'opening_times_standard' );
		$settings_fields['opening_times']['opening_times_custom'] = array('opening_times_custom', '<b>'.__('Any dates/days where opening times differ from the standard times above (such as christmas etc).', 'wppizza-locale').'</b>', 'opening_times', 'opening_times', 'opening_times_custom' );
		$settings_fields['opening_times']['times_closed_standard'] = array('times_closed_standard', '<b>'.__('Closed (if you close for lunch for example):', 'wppizza-locale').'</b><br/><br/>'.__('If you are closed on certain days for a number of hours, enter them here<br/>i.e. if you are generally open on Tuesdays - as set above - from 9:30 to 23:00, but close for lunch between 12:00 and 14:00, enter Tuesdays 12:00 - 14:00 here. If you are also closed on Tuesday between 17:30 and 18:00, set this as well and so on ', 'wppizza-locale').'<br/><br/>'.__('Furthermore, do not enter times here that span midnight. If you are however closed from - let\'s say - 11:00PM Mondays to 1:00AM Tuesdays, enter "Mondays 23:00 to 23:59" as well as "Tuesdays 0:00 to 1:00', 'wppizza-locale').'<br/><br/>'.__('If you have setup any custom dates above (for example christmas or whatever), select "Custom Dates" instead of the day of week if you want to apply these closing times only to those dates', 'wppizza-locale').'<br/><br/><span style="color:red">'.__('Note: if you set anything here, it will not be reflected when displaying openingtimes via shortcode or in the widget, so you might want to display your openingtimes manually somewhere. It DOES, however close the shoppingcart, the ability to order etc as required)', 'wppizza-locale').'</span>', 'opening_times', 'opening_times', 'times_closed_standard' );

		/**order**/
		$settings_sections['order'] = array('order', '', 'order');
		$settings_fields['order']['currency'] = array('currency', '<b>'.__('Currency:', 'wppizza-locale').'</b><br/>'.__('set to --none-- to have no currency displayed anywhere', 'wppizza-locale').'', 'order', 'order', 'currency' );
		$settings_fields['order']['orderpage'] = array('orderpage', '<b>'.__('Order Page:', 'wppizza-locale').'</b><br/>'.__('ensure the page includes [wppizza type="orderpage"] or the widget equivalent. <b>You might also want to consider NOT displaying the shopping cart on this page</b> (although it won\'t break things)', 'wppizza-locale').'', 'order', 'order', 'orderpage' );
		$settings_fields['order']['delivery'] = array('delivery', '<b>'.__('Delivery Charges:', 'wppizza-locale').'</b>', 'order', 'order', 'delivery' );
		$settings_fields['order']['item_tax'] = array('item_tax', '<b>'.__('(Sales)Tax applied to items in cart [in % - 0 to disable]:', 'wppizza-locale').'</b>', 'order', 'order', 'item_tax' );
		$settings_fields['order']['order_pickup'] = array('order_pickup', '<b>'.__('Allow order pickup by customer:', 'wppizza-locale').'</b><br />'.__('Customer can choose to pickup the order him/herself. No delivery charges will be applied if customer chooses to do so.', 'wppizza-locale').'', 'order', 'order', 'order_pickup' );
		$settings_fields['order']['order_pickup_display_location'] = array('order_pickup_display_location', '<b>'.__('Where would you like to display the checkbox to let customer select self pickup of order ?', 'wppizza-locale').'</b> '.__('[if enabled above]', 'wppizza-locale').'', 'order', 'order', 'order_pickup_display_location' );
		$settings_fields['order']['discounts'] = array('discounts', '<b>'.__('Discounts:', 'wppizza-locale').'</b>', 'order', 'order', 'discounts' );
		$settings_fields['order']['append_internal_id_to_transaction_id'] = array('append_internal_id_to_transaction_id', '<b>'.__('Append internal ID to transaction ID:', 'wppizza-locale').'</b>', 'order', 'order', 'append_internal_id_to_transaction_id' );
		$settings_fields['order']['order_email_to'] = array('order_email_to', '<b>'.__('Which email address should any orders be sent to [separated by comma if multiple]:', 'wppizza-locale').'</b>', 'order', 'order', 'order_email_to' );
		$settings_fields['order']['order_email_bcc'] = array('order_email_bcc', '<b>'.__('If you would like to BCC order emails add these here [separated by comma if multiple]:', 'wppizza-locale').'</b>', 'order', 'order', 'order_email_bcc' );
		$settings_fields['order']['order_email_from'] = array('order_email_from', '<b>'.__('If you want to set a static "From" email address set it here, otherwise leave blank . All emails will appear to have been sent from this address. (Some fax gateways for example require a distinct FROM email address). However, the customers email address will still be stored in the db/order history if entered', 'wppizza-locale').'</b>', 'order', 'order', 'order_email_from' );
		$settings_fields['order']['order_email_from_name'] = array('order_email_from_name', '<b>'.__('You can set a static "name" here (in conjunction with any "From" email address set above). If left empty, the email address will be used', 'wppizza-locale').'</b>', 'order', 'order', 'order_email_from_name' );
		$settings_fields['order']['order_email_attachments'] = array('order_email_attachments', '<b>'.__('Email Attachments [separated by comma if multiple]:', 'wppizza-locale').'</b>', 'order', 'order', 'order_email_attachments' );


		/**order form**/
		$settings_sections['order_form'] = array('order_form', '', 'order_form');
		$settings_fields['order_form']['order_form'] = array('order_form', '<b>'.__('Form Fields:', 'wppizza-locale').'</b>', 'order_form', 'order_form', 'order_form' );
		$settings_fields['order_form']['confirmation_form'] = array('confirmation_form', '<b>'.__('Confirmation Page:', 'wppizza-locale').'</b>', 'order_form', 'order_form', 'confirmation_form' );

		/**size options**/
		$settings_sections['sizes'] = array('sizes', __('Size Options Available', 'wppizza-locale'), 'sizes');
		$settings_fields['sizes']['sizes'] = array('sizes', '', 'sizes', 'sizes', 'sizes' );

		/**additives**/
		$settings_sections['additives'] = array('additives', '', 'additives');
		$settings_fields['additives']['additives'] = array('additives', '', 'additives', 'additives', 'additives' );

		/**localization**/
		$settings_sections['localization'] = array('localization', __('Frontened Localization', 'wppizza-locale').' - '.__('edit as required:', 'wppizza-locale'), 'localization');
		$settings_fields['localization']['localization'] = array('localization', '', 'localization', 'localization', 'localization' );

		/**order history**/
		$settings_sections['history'] = array('history', '', 'history');
		$settings_fields['history']['history'] = array('history', '', 'history', 'history', 'history' );

		/**order reports**/
		$settings_sections['reports'] = array('reports', '', 'reports');

		/**access rights**/
		$settings_sections['access'] = array('access', '', 'access');
		$settings_fields['access']['access'] = array('access', '', 'access', 'access', 'access' );

		/**templates**/
		$settings_sections['templates'] = array('templates', '', 'templates');
		$settings_fields['templates']['templates'] = array('templates', '', 'templates', 'templates', 'templates' );

		/**tools**/
		$settings_sections['tools'] = array('tools', __('Miscellaneous Tools', 'wppizza-locale'), 'tools');
		$settings_fields['tools']['tools'] = array('tools', '', 'tools', 'tools', 'tools' );


		/**********************************************************************
			FILTERS SETTINGS SECTIONS / FIELDS
		**********************************************************************/
		/**
			allow filtering of settings sections
		**/
		$settings_sections=apply_filters('wppizza_filter_settings_section',$settings_sections);
		/**
			allow filtering of settings fields
		**/
		$settings_fields=apply_filters('wppizza_filter_settings_fields', $settings_fields);


		/**********************************************************************
			SETTINGS SECTIONS
		**********************************************************************/
		/**
			output sections
		**/
		foreach($settings_sections as $settings_section_array){
			add_settings_section($settings_section_array[0], $settings_section_array[1], array( $this, 'wppizza_admin_page_text_header'), $settings_section_array[2]);
		}


		/**********************************************************************
			SETTINGS FIELDS
		**********************************************************************/

		/**
			output fields
		**/
		foreach($settings_fields as $settings_section_key=>$settings_field_section){
			foreach($settings_field_section as $settings_fields_key=>$settings_field_array){
				add_settings_field($settings_field_array[0], $settings_field_array[1], array( $this, 'wppizza_admin_settings_input'), $settings_field_array[2], $settings_field_array[3], $settings_field_array[4]);
			}
		}

?>