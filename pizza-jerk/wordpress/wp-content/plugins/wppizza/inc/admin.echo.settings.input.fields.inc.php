<?php if ( ! defined( 'ABSPATH' ) ) exit;/*Exit if accessed directly*/ ?>
<?php
$options = $this->pluginOptions;
	if($options!=0){

	global $blog_id;
	// removed as i dont think this is actually in use anywhere here ?!
	//$optionSizes=wppizza_sizes_available($options['sizes']);
	$optionsDecimals=$options['layout']['hide_decimals'];

			/**allow adding of further settings fileds**/
			do_action('wppizza_action_echo_settings_field', $field, $options);

			if($field=='version'){
				echo "{$options['plugin_data'][$field]}";
			}
			if($field=='js_in_footer'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[plugin_data][".$field."]' type='checkbox'  ". checked($options['plugin_data'][$field],true,false)." value='1' />";
				echo" <span class='description'>".__('combines all jsVars in one tidy place, but requires wp_footer in theme', 'wppizza-locale')."</span>";
			}
			if($field=='ssl_on_checkout'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[plugin_data][".$field."]' type='checkbox'  ". checked($options['plugin_data'][$field],true,false)." value='1' />";
				echo" <span class='description'>".__('set your order page to be https (you must have an SSL certificate installed)', 'wppizza-locale')."</span>";
			}

			if($field=='admin_order_history_max_results'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[plugin_data][".$field."]' size='2' type='text'  value='{$options['plugin_data']['admin_order_history_max_results']}' />";
				echo" <span class='description'>".__('default number of results to show in admin order history', 'wppizza-locale')."</span>";

				echo"<br />";
				echo" <input id='".$field."' name='".$this->pluginSlug."[plugin_data][admin_order_history_include_failed]' type='checkbox'  ". checked($options['plugin_data']['admin_order_history_include_failed'],true,false)." value='1' />";
				echo" <span class='description'>".__('also include failed orders in order history', 'wppizza-locale')."</span>";

			}

			if($field=='admin_order_history_polling_auto'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[plugin_data][".$field."]' type='checkbox'  ". checked($options['plugin_data'][$field],true,false)." value='1' />";
				echo" <span class='description'>".__('automatically activate order polling on page load', 'wppizza-locale')."</span><br />";
				echo "<input id='admin_order_history_polling_time' name='".$this->pluginSlug."[plugin_data][admin_order_history_polling_time]' size='2' type='text'  value='{$options['plugin_data']['admin_order_history_polling_time']}' />";
				echo" <span class='description'>".__('default polling time [in seconds]', 'wppizza-locale')."</span>";
			}

			/**only displayed in multisite installs**/
			if($field=='wp_multisite_session_per_site'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[plugin_data][".$field."]' type='checkbox'  ". checked($options['plugin_data'][$field],true,false)." value='1' />";
				echo" <span class='description'>".__('Set cart contents and order on a per site basis when using subdirectories. This has no effect/relevance when there\'s no multisite setup or using different domains per site on the network. Chances are that you want this on when you have a multisite/network install.', 'wppizza-locale')."</span>";
				echo"<br /><span class='description' style='color:red'>".__('THERE ARE ONLY VERY FEW SECENARIOS WHERE YOU MIGHT WANT THIS OFF', 'wppizza-locale')."</span>";
			}

			/**only displayed in multisite installs and on parent site**/
			if($field=='wp_multisite_reports_all_sites'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[plugin_data][".$field."]' type='checkbox'  ". checked($options['plugin_data'][$field],true,false)." value='1' />";
				echo" <span class='description'>".__('check to have reporting to use all orders of all child sites', 'wppizza-locale')."</span>";
				echo"<br /><span><b>".__('only applicable in parent site\'s reporting. reporting in child sites will only ever show values based on that sites orders', 'wppizza-locale')."</b></span>";
				echo"<br /><span class='description' style='color:red'>".__('NOTE: THIS MIGHT SLOW THINGS DOWN IN THE ADMIN REPORTING PAGE OF YOUR MAIN/PARENT SITE CONSIDERABLY', 'wppizza-locale')."</span>";
			}
			/**only displayed in multisite installs and on parent site**/
			if($field=='wp_multisite_order_history_all_sites'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[plugin_data][".$field."]' type='checkbox'  ". checked($options['plugin_data'][$field],true,false)." value='1' />";
				echo" <span class='description'>".__('check to have order history to use all orders of all child sites', 'wppizza-locale')."</span>";
				echo"<br /><span><b>".__('only applicable in parent site\'s order history. order history in child sites will only ever show values based on that sites orders', 'wppizza-locale')."</b></span>";
				echo"<br /><span class='description' style='color:red'>".__('NOTE: THIS MIGHT SLOW THINGS DOWN IN THE ADMIN ORDER HISTORY PAGE OF YOUR MAIN/PARENT SITE CONSIDERABLY', 'wppizza-locale')."</span>";

				echo"<br />";
				echo"<br /><strong>".__('Order history print options', 'wppizza-locale')."</strong>";
				echo"<br />";
				echo "<input id='wp_multisite_order_history_print_header_from_child' name='".$this->pluginSlug."[plugin_data][wp_multisite_order_history_print][header_from_child]' type='checkbox'  ". checked(!empty($options['plugin_data']['wp_multisite_order_history_print']['header_from_child']),true,false)." value='1' />";
				echo" <span class='description'>".__('use header name/address of site <b>where the order was made</b> (set in localization : Print Order Admin - [Header] | Print Order Admin - [Address] <b>of that site</b>)', 'wppizza-locale')."</span>";

				echo"<br />";
				echo "<input id='wp_multisite_order_history_print_multisite_info' name='".$this->pluginSlug."[plugin_data][wp_multisite_order_history_print][multisite_info]' type='checkbox'  ". checked(!empty($options['plugin_data']['wp_multisite_order_history_print']['multisite_info']),true,false)." value='1' />";
				echo" <span class='description'>".__('add small display of order site\'s name if different from name in header [e.g if header displays parent name/address, but order was made at child site]', 'wppizza-locale')."</span>";

			}

			if($field=='using_cache_plugin'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[plugin_data][".$field."]' type='checkbox'  ". checked($options['plugin_data'][$field],true,false)." value='1' />";
				echo" <span class='description'>".__('Will ALWAYS load the cart dynamically via ajax. Especially useful if your caching plugin does not support the exclusion of only parts of a page.', 'wppizza-locale')."</span>";
				echo"<br /><span class='description'><b>".__('Note: you still want to exclude your entire *order page* - or at least the main content of that page - from being cached in your cache plugin (please see the documentation for your choosen cache plugin for how to do this). After you enable this, clear your cache.', 'wppizza-locale')."</b></span>";
			}
			if($field=='use_old_admin_order_print'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[plugin_data][".$field."]' type='checkbox'  ". checked($options['plugin_data'][$field],true,false)." value='1' />";
				echo" <span class='description'>".__('revert to old printing style of orders in "wppizza -> order history" when clicking on "print order"', 'wppizza-locale')."</span>";
			}

			if($field=='experimental_js'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[plugin_data][".$field."]' type='checkbox'  ". checked($options['plugin_data'][$field],true,false)." value='1' />";
				echo" <span class='description'>".__('<b>this should be off unless otherwise requested</b> (things tested here will eventually make it into the core js)', 'wppizza-locale')."</span>";
			}

			if($field=='always_load_all_scripts_and_styles'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[plugin_data][".$field."]' type='checkbox'  ". checked($options['plugin_data'][$field],true,false)." value='1' />";
				echo" <span class='description'>".__('load *all* css and javascripts on all pages. Might be necessary for themes that hijack normal pagelinks', 'wppizza-locale')."</span>";
			}

			/************smtp*********************/
			if($field=='smtp_enable'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[plugin_data][".$field."]' type='checkbox'  ". checked($options['plugin_data'][$field],true,false)." value='1' />";
				echo" <span class='description'>".__('check to use SMTP when sending wppizza related emails', 'wppizza-locale')."</span>";
			}
			if($field=='smtp_host'){
				echo"<input  id='wppizza_".$field."' name='".$this->pluginSlug."[plugin_data][".$field."]' type='text' value='".$options['plugin_data'][$field]."' />";
			}
			if($field=='smtp_port'){
				echo"<input id='wppizza_".$field."' name='".$this->pluginSlug."[plugin_data][".$field."]' size='3' type='text' value='".$options['plugin_data'][$field]."' />";
			}
			if($field=='smtp_encryption'){
				echo "<select id='wppizza_".$field."' name='".$this->pluginSlug."[plugin_data][".$field."]' />";
					echo"<option value='' ".selected($options['plugin_data'][$field],"",false).">".__('No Encryption', 'wppizza-locale')."</option>";
					echo"<option value='ssl' ".selected($options['plugin_data'][$field],"ssl",false).">".__('SSL', 'wppizza-locale')."</option>";
					echo"<option value='tls' ".selected($options['plugin_data'][$field],"tls",false).">".__('TLS', 'wppizza-locale')."</option>";
				echo "</select>";
			}

			if($field=='smtp_authentication'){
				echo "<input id='wppizza_".$field."'  name='".$this->pluginSlug."[plugin_data][".$field."]' type='checkbox'  ". checked($options['plugin_data'][$field],true,false)." value='1' />";
				echo" <span class='description'>".__('check to enable authentication', 'wppizza-locale')."</span>";
			}

			if($field=='smtp_username'){
				echo"<input id='wppizza_".$field."' name='".$this->pluginSlug."[plugin_data][".$field."]' type='text' value='".$options['plugin_data'][$field]."' />";
			}
			if($field=='smtp_password'){
				/*info string*/
				$SmtpPwPlaceholder='no password set';
				if(!empty($options['plugin_data'][$field])){$SmtpPwPlaceholder='a password has been set. enter a new password to change the current password';}
				/*show password if defined*/
				$smtpPW=defined('WPPIZZA_VIEW_SMTP_PASSWORD') ? wppizza_encrypt_decrypt($options['plugin_data'][$field],false) : '';
				echo"<input id='wppizza_".$field."' name='".$this->pluginSlug."[plugin_data][".$field."]' type='text' value='".$smtpPW."' placeholder='********' />".$SmtpPwPlaceholder."";
				echo"<br />";
				if(!function_exists('openssl_encrypt')){
					echo" <span style='color:red'>".__('WARNING: you do not seem to have open ssl installed. your smtp password will be saved in plaintext in your database. this could pose a security risk', 'wppizza-locale')."</span>";
				}else{
					echo" <span style='color:red'>".__('Note: if you move or clone this wppizza installation to another wordpress installation, you MUST re-enter and re-save your smtp password there unless your wp-config.php\'s are identical.', 'wppizza-locale')."</span>";
				}
			}
			if($field=='smtp_debug'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[plugin_data][".$field."]' type='checkbox'  ". checked($options['plugin_data'][$field],true,false)." value='1' />";
				echo" <span class='description'>".__('This should be OFF on production servers. Development/Testing only', 'wppizza-locale')."</span>";
			}
			if($field=='smtp_test'){
				echo"<br />";
				echo "To: <input id='wppizza_smtp_test_email' type='text' value='' placeholder='email@domain.com' />";
				echo" <span>".__('To test your smtp settings, enter your email address on the left and <a href="javascript:void(0)" id="wppizza_smtp_test">click here</a>. results will appear below', 'wppizza-locale')."</span>";
				echo"<br />";
				echo"<div id='wppizza_smtp_test_results'><pre></pre></div>";
			}

			/************smtp end*********************/

			if($field=='mail_type'){
				/*ouput mail delivery options*/
				echo"".wppizza_admin_mail_delivery_options($options);
				echo" <span>".__('might be worth changing if you have trouble when sending/receiving orders with the default settings or prefer html emails', 'wppizza-locale')."</span>";
				echo"<br /><span class='description'>".__('if using PHPMailer function you probably want to edit the html template. To do so, move "wppizza-order-html-email.php" from the wppizza template directory to your theme folder and edit as required', 'wppizza-locale')."</span>";
			}

			if($field=='dequeue_scripts'){
				echo "<select name='".$this->pluginSlug."[plugin_data][".$field."]' />";
					echo"<option value='' ".selected($options['plugin_data'][$field],"",false).">".__('leave as is', 'wppizza-locale')."</option>";
					echo"<option value='all' ".selected($options['plugin_data'][$field],"all",false).">".__('dequeue both, main wppizza and jquery validation', 'wppizza-locale')."</option>";
					echo"<option value='validation' ".selected($options['plugin_data'][$field],"validation",false).">".__('dequeue jquery validation only', 'wppizza-locale')."</option>";
				echo "</select>";
				echo"<br /><span class='description'><b>".__('If you are *certain* you do not require the main wppizza javascript and jquery validation and nothing else depends on them, or another plugin is already including jquery validation elsewhere, use the settings above as required. if you do not now, just leave it as is', 'wppizza-locale')."</b></span>";
			}

			if($field=='search_include' ){
				echo "<input id='".$field."' name='".$this->pluginSlug."[plugin_data][".$field."]' type='checkbox'  ". checked($options['plugin_data'][$field],true,false)." value='1' />";
				echo" <span class='description'>".__('you could also leave this off and use the wppizza widget/shortcode for a dedicated search box', 'wppizza-locale')."</span>";

				/**which single post template ?*/
				echo"<br />";
				echo "<select name='".$this->pluginSlug."[plugin_data][post_single_template]' />";
					echo"<option value=''>".__('default or custom template [single-wppizza.php if exists]', 'wppizza-locale')."</option>";
					foreach($pages as $k=>$v){
						if(isset($options['plugin_data']['post_single_template']) && $options['plugin_data']['post_single_template']==$v->ID){$sel=' selected="selected"';}else{$sel='';}
						echo"<option value='".$v->ID."' ".$sel.">".$v->post_title."</option>";
					}
				echo "</select>";
				echo" <span class='description'>".__('how to display single wppizza menu items.', 'wppizza-locale')."</span>";
				echo" <span class='description' style='color:blue'>".__('please see the <a href="https://www.wp-pizza.com/topic/single-wppizza-menu-items-display/">faq\'s -> single wppizza menu items display</a> for details as to how this works', 'wppizza-locale')."</span>";
			}

			if($field=='category_parent_page'){
				/**check which pages have children (so we can exclude from dropdown as otherwise children pages will not be accessible*/
				$exclude=array();
				foreach($pages as $k=>$v){
					$children = get_pages('child_of='.$v->ID);
					if( count( $children ) != 0 ) {$exclude[]=$v->ID;}
				}
				$exclude[]=get_option('page_for_posts');/*also exclude page thats set for default posts*/

				echo "<select name='".$this->pluginSlug."[plugin_data][".$field."]' />";
					echo"<option value=''>".__('no parent [default]', 'wppizza-locale')."</option>";
					foreach($pages as $k=>$v){
						if(in_array($v->ID,$exclude)){
							echo"<option value='' style='color:red'>".$v->post_title." ".__('[not selectable]', 'wppizza-locale')."</option>";
						}else{
							if($options['plugin_data'][$field]==$v->ID){$sel=' selected="selected"';}else{$sel='';}
							echo"<option value='".$v->ID."' ".$sel.">".$v->post_title."</option>";
						}
					}
				echo "</select>";
			}
			if($field=='single_item_permalink_rewrite'){
				echo "<input name='".$this->pluginSlug."[plugin_data][".$field."]' size='20' type='text'  value='{$options['plugin_data'][$field]}' />";
				echo " ".__('defaults to "wppizza" if left empty. Any value used here cannot not be used in any other custom post type', 'wppizza-locale')."";
				echo "<br />".__('Note: by default, wppizza templates/shortcodes do not link to any single menu items. However, if you are including mneu items in search results for example or have edited a/the template(s) to include links to individual menu items you will also (probably) want to edit the single item template. see https://www.wp-pizza.com/topic/single-wppizza-menu-items-display', 'wppizza-locale')."";

			}

			if($field=='include_css' ){
				echo "<input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='checkbox'  ". checked($options['layout'][$field],true,false)." value='1' />";
				echo "<input name='".$this->pluginSlug."[layout][css_priority]' size='2' type='text'  value='{$options['layout']['css_priority']}' />";
				echo "".__('Stylesheet Priority', 'wppizza-locale')."";
				echo "<br />".__('By default, the stylesheet will be loaded AFTER the main theme stylesheet (which should have a priority of "10"). If you experience strange behaviour or layout issues (in conjunction with other plugins for example), you can try adjusting this priority here (the bigger the number, the later it gets loaded).', 'wppizza-locale')."";

			}
			if($field=='element_name_refresh_page'){
				echo "<input name='".$this->pluginSlug."[layout][".$field."]' size='20' type='text'  value='{$options['layout'][$field]}' />";
				echo " ".__('[blank to ignore]', 'wppizza-locale')."";
				echo "<br />".__('set an element name the page should go to on refresh when <b>switching beetween pickup and delivery</b> (if enabled). perhaps useful in long/one-page layouts to go to a specific part of the page on refresh', 'wppizza-locale')."";
				echo "<br /><span class='description'>".__('example: put an element on your page in the appropriate place like &#60;a name="somename" /&#62; and set the value here to "somename" ', 'wppizza-locale')."</span>";

			}
			if($field=='hide_decimals' ){
				echo "<input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='checkbox'  ". checked($options['layout'][$field],true,false)." value='1' />";
			}
			if($field=='show_currency_with_price'){
				echo "".__('do not show', 'wppizza-locale')." <input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='radio'  ".checked($options['layout'][$field],0,false)." value='0' /> ";
				echo "".__('on left', 'wppizza-locale')." <input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='radio'  ".checked($options['layout'][$field],1,false)." value='1' />";
				echo "".__('on right', 'wppizza-locale')." <input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='radio'  ".checked($options['layout'][$field],2,false)." value='2' />";
			}

			if($field=='items_sort_orderby'){
				echo "<select name='".$this->pluginSlug."[layout][".$field."]' />";
						echo"<option value='menu_order' ".selected($options['layout'][$field],"menu_order",false).">".__('Default (set order attribute)', 'wppizza-locale')."</option>";
						echo"<option value='title' ".selected($options['layout'][$field],"title",false).">".__('Title', 'wppizza-locale')."</option>";
						echo"<option value='ID' ".selected($options['layout'][$field],"ID",false).">".__('ID', 'wppizza-locale')."</option>";
						echo"<option value='date' ".selected($options['layout'][$field],"date",false).">".__('Date', 'wppizza-locale')."</option>";
				echo "</select>";
				echo "<select name='".$this->pluginSlug."[layout][items_sort_order]' />";
						echo"<option value='ASC' ".selected($options['layout']['items_sort_order'],"ASC",false).">".__('Ascending', 'wppizza-locale')."</option>";
						echo"<option value='DESC' ".selected($options['layout']['items_sort_order'],"DESC",false).">".__('Descending', 'wppizza-locale')."</option>";
				echo "</select>";
			}
			if($field=='items_group_sort_print_by_category'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='checkbox'  ". checked($options['layout'][$field],true,false)." value='1' />";
				echo" <span class='description'>".__('displays categories in cart, order page and emails', 'wppizza-locale')."</span>";
				echo "<br />";
				echo "<br />";
				echo" <span class='description'>".__('How would you like to display the categories in order pages and emails ? [only relevant in hierarchical category structure]', 'wppizza-locale')."</span>";
				echo "<br />";
				echo "".__('full path', 'wppizza-locale')." <input name='".$this->pluginSlug."[layout][items_category_hierarchy]' type='radio'  ".checked($options['layout']['items_category_hierarchy'],'full',false)." value='full' /> ";
				echo "".__('parent category', 'wppizza-locale')." <input name='".$this->pluginSlug."[layout][items_category_hierarchy]' type='radio'  ".checked($options['layout']['items_category_hierarchy'],'parent',false)." value='parent' />";
				echo "".__('topmost category', 'wppizza-locale')." <input name='".$this->pluginSlug."[layout][items_category_hierarchy]' type='radio'  ".checked($options['layout']['items_category_hierarchy'],'topmost',false)." value='topmost' />";
				/*allow inclusion of blogname in multisite*/
				if(is_multisite()){
					echo "<br />";
					echo "".__('show blogname too', 'wppizza-locale')." <input name='".$this->pluginSlug."[layout][items_blog_hierarchy]' type='checkbox'  ".checked($options['layout']['items_blog_hierarchy'],true,false)." value='1' />";
				}
				echo "<br />";
				echo "<br />";
				echo" <span class='description'>".__('How would you like to display the categories in the cart ?  [as the cart might have space restrictions you can adjust this separately]', 'wppizza-locale')."</span>";
				echo "<br />";
				echo "".__('do not display categories', 'wppizza-locale')." <input name='".$this->pluginSlug."[layout][items_category_hierarchy_cart]' type='radio'  ".checked($options['layout']['items_category_hierarchy_cart'],'none',false)." value='none' /> ";
				echo "".__('full path', 'wppizza-locale')." <input name='".$this->pluginSlug."[layout][items_category_hierarchy_cart]' type='radio'  ".checked($options['layout']['items_category_hierarchy_cart'],'full',false)." value='full' /> ";
				echo "".__('parent category', 'wppizza-locale')." <input name='".$this->pluginSlug."[layout][items_category_hierarchy_cart]' type='radio'  ".checked($options['layout']['items_category_hierarchy_cart'],'parent',false)." value='parent' />";
				echo "".__('topmost category', 'wppizza-locale')." <input name='".$this->pluginSlug."[layout][items_category_hierarchy_cart]' type='radio'  ".checked($options['layout']['items_category_hierarchy_cart'],'topmost',false)." value='topmost' />";
				/*allow inclusion of blogname in multisite*/
				if(is_multisite()){
					echo "<br />";
					echo "".__('show blogname too', 'wppizza-locale')." <input name='".$this->pluginSlug."[layout][items_blog_hierarchy_cart]' type='checkbox'  ".checked($options['layout']['items_blog_hierarchy_cart'],true,false)." value='1' />";
				}
				echo "<br />";
				echo "<br />";
				echo "<input name='".$this->pluginSlug."[layout][items_category_separator]' size='2' type='text'  value='{$options['layout']['items_category_separator']}' />";
				echo" <span class='description'>".__('Category Separator', 'wppizza-locale')."</span>";
			}

			if($field=='currency_symbol_left'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='checkbox'  ". checked($options['layout'][$field],true,false)." value='1' />";
			}
			if($field=='currency_symbol_position'){
				echo "".__('on left', 'wppizza-locale')." <input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='radio'  ".checked($options['layout'][$field],'left',false)." value='left' />";
				echo "".__('on right', 'wppizza-locale')." <input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='radio'  ".checked($options['layout'][$field],'right',false)." value='right' />";
			}
			if($field=='cart_increase'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='checkbox'  ". checked($options['layout'][$field],true,false)." value='1' />";
			}
			if($field=='order_page_quantity_change'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='checkbox'  ". checked($options['layout'][$field],true,false)." value='1' />";

				echo"<br />";
				echo "<input name='".$this->pluginSlug."[layout][order_page_quantity_change_left]' type='checkbox' ". checked($options['layout']['order_page_quantity_change_left'],true,false)." value='1' />";
				echo" <span class='description'>".__('display left of the item(s) [by default it will be on the right]', 'wppizza-locale')."</span>";

				echo"<br />";
				$uiStyle=array('ui-lightness','ui-darkness','smoothness','start','redmond','sunny','overcast','le-frog','flick','pepper-grinder','eggplant','dark-hive','cupertino','south-street','blitzer','humanity','hot-sneaks','excite-bike','vader','dot-luv','mint-choc','black-tie','trontastic','swanky-purse');
				sort($uiStyle);
				echo "<select name='".$this->pluginSlug."[layout][order_page_quantity_change_style]'>";
					foreach($uiStyle as $k=>$style){
					echo "<option value='".$style."' ".selected($options['layout']['order_page_quantity_change_style'],$style,false).">".$style."</option>";
					}
					echo "<option value='' ".selected($options['layout']['order_page_quantity_change_style'],'',false).">".__('a (themeroller) style is already loaded / I provide my own style', 'wppizza-locale')."</option>";
				echo "</select>";
				echo" <span class='description'>".__('style to use', 'wppizza-locale')."</span>";
			}

			if($field=='items_per_loop' ){
				echo "<input name='".$this->pluginSlug."[layout][".$field."]' size='2' type='text'  value='{$options['layout'][$field]}' />";
			}
			if($field=='style'){
				echo "<select id='".$this->pluginSlug."_layout_".$field."' name='".$this->pluginSlug."[layout][".$field."]' />";
					foreach(wppizza_public_styles($options['layout'][$field]) as $k=>$v){
						echo"<option value='".$v['id']."' ".$v['selected'].">".$v['value']."</option>";
					}
				echo "</select>";

				$gridOptionsShow=($options['layout'][$field]=='grid') ? 'block' : 'none' ;
				echo"<div id='".$this->pluginSlug."-".$field."-grid' style='display:".$gridOptionsShow."'>";
				echo "<input id='' name='".$this->pluginSlug."[layout][style_grid_columns]' size='4' type='text' value='{$options['layout']['style_grid_columns']}' />";
				echo" <span>".__('How many columns per row [minimum 1]', 'wppizza-locale')."</span>";
				echo"<br />";
				echo "<input id='' name='".$this->pluginSlug."[layout][style_grid_margins]' size='4' type='text' value='{$options['layout']['style_grid_margins']}' />";
				echo" <span>".__('margins between columns [in %]', 'wppizza-locale')."</span>";
				echo"<br />";
				echo "<input id='' name='".$this->pluginSlug."[layout][style_grid_full_width]' size='4' type='text' value='{$options['layout']['style_grid_full_width']}' />";
				echo" <span>".__('maximum browser width for layout to revert to 1 column per row [in px] - (for mobile / small screen devices)', 'wppizza-locale')."</span>";
				echo"<br /><br />";
				echo" <span class='description' >".__('you will probably have to tweak the above to work with your theme and/or add some custom css. make sure to check things with different browsers', 'wppizza-locale')."</span>";
				echo"</div>";
			}


			if($field=='add_to_cart_on_title_click' ){
				echo "<input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='checkbox'  ". checked($options['layout'][$field],true,false)." value='1' />";
			}

			if($field=='opening_times_format'){
				echo"<span class='wppizza_label'>".__('Hours', 'wppizza-locale')."</span>";
				echo "<select name='".$this->pluginSlug."[".$field."][hour]' />";
						echo"<option value='G' ".selected($options[$field]['hour'],"G",false).">".__('24-hour format without leading zeros', 'wppizza-locale')."</option>";
						echo"<option value='g' ".selected($options[$field]['hour'],"g",false).">".__('12-hour format without leading zeros', 'wppizza-locale')."</option>";
						echo"<option value='H' ".selected($options[$field]['hour'],"H",false).">".__('24-hour format with leading zeros', 'wppizza-locale')."</option>";
						echo"<option value='h' ".selected($options[$field]['hour'],"h",false).">".__('12-hour format with leading zeros', 'wppizza-locale')."</option>";
				echo "</select>";
				echo "<br />";
				echo"<span class='wppizza_label'>".__('Separator', 'wppizza-locale')."</span>";
				echo "<select name='".$this->pluginSlug."[".$field."][separator]' />";
						echo"<option value='' ".selected($options[$field]['separator'],"",false).">".__('no seperator', 'wppizza-locale')."</option>";
						echo"<option value='&nbsp;' ".selected($options[$field]['separator'],"&nbsp;",false).">".__('space', 'wppizza-locale')."</option>";
						echo"<option value=':' ".selected($options[$field]['separator'],":",false).">:</option>";
						echo"<option value='.' ".selected($options[$field]['separator'],".",false).">.</option>";
						echo"<option value='-' ".selected($options[$field]['separator'],"-",false).">-</option>";
						echo"<option value=';' ".selected($options[$field]['separator'],";",false).">;</option>";
				echo "</select>";
				echo "<br />";
				echo"<span class='wppizza_label'>".__('Minutes', 'wppizza-locale')."</span>";
				echo "<select name='".$this->pluginSlug."[".$field."][minute]' />";
						echo"<option value='' ".selected($options[$field]['minute'],"",false).">".__('hide minutes', 'wppizza-locale')."</option>";
						echo"<option value='i' ".selected($options[$field]['minute'],"i",false).">".__('show minutes', 'wppizza-locale')."</option>";
				echo "</select>";
				echo "<br />";
				echo"<span class='wppizza_label'>".__('Show AM/PM ?', 'wppizza-locale')."</span>";
				echo "<select name='".$this->pluginSlug."[".$field."][ampm]' />";
						echo"<option value='' ".selected($options[$field]['ampm'],"",false).">".__('do not show', 'wppizza-locale')."</option>";
						echo"<option value='a' ".selected($options[$field]['ampm'],"a",false).">".__('lowercase', 'wppizza-locale')."</option>";
						echo"<option value='A' ".selected($options[$field]['ampm'],"A",false).">".__('UPPERCASE', 'wppizza-locale')."</option>";
						echo"<option value=' a' ".selected($options[$field]['ampm']," a",false).">".__('lowercase (with leading space)', 'wppizza-locale')."</option>";
						echo"<option value=' A' ".selected($options[$field]['ampm']," A",false).">".__('UPPERCASE (width leading space)', 'wppizza-locale')."</option>";
				echo "</select>";
			}


			if($field=='jquery_feedback_added_to_cart'){
				echo "<input id='' name='".$this->pluginSlug."[layout][jquery_fb_add_to_cart]' type='checkbox'  ". checked($options['layout']['jquery_fb_add_to_cart'],true,false)." value='1' />";
				echo" <span class='description'>".__('Replace item price with customised text when adding an item to cart [set/edit text in localization]', 'wppizza-locale')."</span>";
				echo "<br />";
				echo "<input id='' name='".$this->pluginSlug."[layout][jquery_fb_add_to_cart_ms]' size='4' type='text'  value='{$options['layout']['jquery_fb_add_to_cart_ms']}' />";
				echo" <span class='description'>".__('How long is it visible for before reverting back to displaying price [in ms]', 'wppizza-locale')."</span>";
			}



			if($field=='placeholder_img'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='checkbox'  ". checked($options['layout'][$field],true,false)." value='1' />";
			}
			if($field=='prettyPhoto' ){
				echo "<input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='checkbox'  ". checked($options['layout'][$field],true,false)." value='1' />";
			}
			if($field=='prettyPhotoStyle'){
				echo "<select name='".$this->pluginSlug."[layout][".$field."]'>";
					echo "<option value='pp_default' ".selected($options['layout'][$field],"pp_default",false).">default</option>";
					echo "<option value='light_rounded' ".selected($options['layout'][$field],"light_rounded",false).">light rounded</option>";
					echo "<option value='dark_rounded' ".selected($options['layout'][$field],"dark_rounded",false).">dark rounded</option>";
					echo "<option value='light_square' ".selected($options['layout'][$field],"light_square",false).">light square</option>";
					echo "<option value='dark_square' ".selected($options['layout'][$field],"dark_square",false).">dark square</option>";
					echo "<option value='facebook' ".selected($options['layout'][$field],"facebook",false).">facebook</option>";
				echo "</select>";
				echo' '.__('see wppizza.prettyPhoto.custom.js.php if you want to adjust prettyPhoto options', 'wppizza-locale').'';
			}




			if($field=='hide_cart_icon'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='checkbox'  ". checked($options['layout'][$field],true,false)." value='1' />";
			}
			if($field=='hide_item_currency_symbol'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='checkbox'  ". checked($options['layout'][$field],true,false)." value='1' />";
			}
			if($field=='hide_single_pricetier'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='checkbox'  ". checked($options['layout'][$field],true,false)." value='1' />";
			}
			if($field=='hide_prices'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='checkbox'  ". checked($options['layout'][$field],true,false)." value='1' />";
			}
			if($field=='disable_online_order'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='checkbox'  ". checked($options['layout'][$field],true,false)." value='1' />";
			}
			if($field=='suppress_loop_headers'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='checkbox'  ". checked($options['layout'][$field],true,false)." value='1' />";
			}
			if($field=='empty_cart_button'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[layout][".$field."]' type='checkbox'  ". checked($options['layout'][$field],true,false)." value='1' />";
			}
			if($field=='sticky_cart_settings'){
				echo "<input id='' name='".$this->pluginSlug."[layout][sticky_cart_animation]' size='2' type='text'  value='{$options['layout']['sticky_cart_animation']}' />";
				echo" <span class='description'>".__('Animation Speed [in ms - 0 to disable animation]', 'wppizza-locale')."</span>";
				echo"<br />";
				echo "<select name='".$this->pluginSlug."[layout][sticky_cart_animation_style]'>";
					echo "<option value='' ".selected($options['layout']['sticky_cart_animation_style'],'',false).">---".__('no animation', 'wppizza-locale')."---</option>";
					echo "<option value='linear' ".selected($options['layout']['sticky_cart_animation_style'],'linear',false).">linear</option>";
					echo "<option value='swing' ".selected($options['layout']['sticky_cart_animation_style'],'swing',false).">swing</option>";
					echo "<option value='easeInQuad' ".selected($options['layout']['sticky_cart_animation_style'],'easeInQuad',false).">easeInQuad</option>";
					echo "<option value='easeOutQuad' ".selected($options['layout']['sticky_cart_animation_style'],'easeOutQuad',false).">easeOutQuad</option>";
					echo "<option value='easeInOutQuad' ".selected($options['layout']['sticky_cart_animation_style'],'easeInOutQuad',false).">easeInOutQuad</option>";
					echo "<option value='easeInCubic' ".selected($options['layout']['sticky_cart_animation_style'],'easeInCubic',false).">easeInCubic</option>";
					echo "<option value='easeOutCubic' ".selected($options['layout']['sticky_cart_animation_style'],'easeOutCubic',false).">easeOutCubic</option>";
					echo "<option value='easeInOutCubic' ".selected($options['layout']['sticky_cart_animation_style'],'easeInOutCubic',false).">easeInOutCubic</option>";
					echo "<option value='easeInQuart' ".selected($options['layout']['sticky_cart_animation_style'],'easeInQuart',false).">easeInQuart</option>";
					echo "<option value='easeOutQuart' ".selected($options['layout']['sticky_cart_animation_style'],'easeOutQuart',false).">easeOutQuart</option>";
					echo "<option value='easeInOutQuart' ".selected($options['layout']['sticky_cart_animation_style'],'easeInOutQuart',false).">easeInOutQuart</option>";
					echo "<option value='easeInQuint' ".selected($options['layout']['sticky_cart_animation_style'],'easeInQuint',false).">easeInQuint</option>";
					echo "<option value='easeOutQuint' ".selected($options['layout']['sticky_cart_animation_style'],'easeOutQuint',false).">easeOutQuint</option>";
					echo "<option value='easeInOutQuint' ".selected($options['layout']['sticky_cart_animation_style'],'easeInOutQuint',false).">easeInOutQuint</option>";
					echo "<option value='easeInExpo' ".selected($options['layout']['sticky_cart_animation_style'],'easeInExpo',false).">easeInExpo</option>";
					echo "<option value='easeOutExpo' ".selected($options['layout']['sticky_cart_animation_style'],'easeOutExpo',false).">easeOutExpo</option>";
					echo "<option value='easeInOutExpo' ".selected($options['layout']['sticky_cart_animation_style'],'easeInOutExpo',false).">easeInOutExpo</option>";
					echo "<option value='easeInSine' ".selected($options['layout']['sticky_cart_animation_style'],'easeInSine',false).">easeInSine</option>";
					echo "<option value='easeOutSine' ".selected($options['layout']['sticky_cart_animation_style'],'easeOutSine',false).">easeOutSine</option>";
					echo "<option value='easeInOutSine' ".selected($options['layout']['sticky_cart_animation_style'],'easeInOutSine',false).">easeInOutSine</option>";
					echo "<option value='easeInCirc' ".selected($options['layout']['sticky_cart_animation_style'],'easeInCirc',false).">easeInCirc</option>";
					echo "<option value='easeOutCirc' ".selected($options['layout']['sticky_cart_animation_style'],'easeOutCirc',false).">easeOutCirc</option>";
					echo "<option value='easeInOutCirc' ".selected($options['layout']['sticky_cart_animation_style'],'easeInOutCirc',false).">easeInOutCirc</option>";
					echo "<option value='easeInElastic' ".selected($options['layout']['sticky_cart_animation_style'],'easeInElastic',false).">easeInElastic</option>";
					echo "<option value='easeOutElastic' ".selected($options['layout']['sticky_cart_animation_style'],'easeOutElastic',false).">easeOutElastic</option>";
					echo "<option value='easeInOutElastic' ".selected($options['layout']['sticky_cart_animation_style'],'easeInOutElastic',false).">easeInOutElastic</option>";
					echo "<option value='easeInBack' ".selected($options['layout']['sticky_cart_animation_style'],'easeInBack',false).">easeInBack</option>";
					echo "<option value='easeOutBack' ".selected($options['layout']['sticky_cart_animation_style'],'easeOutBack',false).">easeOutBack</option>";
					echo "<option value='easeInOutBack' ".selected($options['layout']['sticky_cart_animation_style'],'easeInOutBack',false).">easeInOutBack</option>";
					echo "<option value='easeInBounce' ".selected($options['layout']['sticky_cart_animation_style'],'easeInBounce',false).">easeInBounce</option>";
					echo "<option value='easeOutBounce' ".selected($options['layout']['sticky_cart_animation_style'],'easeOutBounce',false).">easeOutBounce</option>";
					echo "<option value='easeInOutBounce' ".selected($options['layout']['sticky_cart_animation_style'],'easeInOutBounce',false).">easeInOutBounce</option>";
				echo "</select>";
				echo" <span class='description'>".__('Animation Style ["no animation" to disable].<br />Note: any style other than "swing" or "linear" will additionally include "jquery.ui.effect" in the page [if not already loaded]', 'wppizza-locale')."</span>";
				echo"<br />";
				echo " <input id='' name='".$this->pluginSlug."[layout][sticky_cart_margin_top]' size='2' type='text'  value='{$options['layout']['sticky_cart_margin_top']}' />";
				echo" <span class='description'>".__('Distance from top of browser when cart is "sticky" to allow for theme specific requirements [in px]', 'wppizza-locale')."</span>";
				echo"<br />";
				echo " <input id='' name='".$this->pluginSlug."[layout][sticky_cart_background]' size='5' type='text'  value='{$options['layout']['sticky_cart_background']}' />";
				echo" <span class='description'>".__('Distinct CSS Background Colour when cart is "sticky" [hexdec (i.e #ffeeff) or string (i.e transparent, inherit, red etc)]', 'wppizza-locale')."</span>";
				echo"<br />";
				echo " <input id='' name='".$this->pluginSlug."[layout][sticky_cart_limit_bottom_elm_id]' size='5' type='text'  value='{$options['layout']['sticky_cart_limit_bottom_elm_id']}' />";
				echo" <span class='description'>".__('If you want to have a sticky cart NOT scroll further down than the TOP of a particular element that is further down on the page (might be useful in some layouts/themes), set that elements ID here [leave blank to ignore]', 'wppizza-locale')."</span>";
			}

			if($field=='minicart_max_width_active'){
				echo "<input name='".$this->pluginSlug."[layout][minicart_viewcart]' type='checkbox'  ". checked($options['layout']['minicart_viewcart'],true,false)." value='1' />";
				echo" <span>".__('display "view cart" button', 'wppizza-locale')."</span>";
				echo"<br />";
				echo "<input name='".$this->pluginSlug."[layout][minicart_always_shown]' type='checkbox'  ". checked($options['layout']['minicart_always_shown'],true,false)." value='1' />";
				echo" <span>".__('always show minicart, even if main cart is in view', 'wppizza-locale')."</span>";
				echo"<br />";
				echo "<input name='".$this->pluginSlug."[layout][".$field."]' size='2' type='text'  value='{$options['layout'][$field]}' />";
				echo" <span>".__('max browser width up to which the minicart will be shown. useful for themes that, under a certain browser window width, change to a responsive design that moves elements to different places. [in px, 0 to ignore]', 'wppizza-locale')."</span>";
				echo"<br />";
				echo "<input name='".$this->pluginSlug."[layout][minicart_elm_padding_top]' size='2' type='text'  value='{$options['layout']['minicart_elm_padding_top']}' />";
				echo" <span>".__('add additional top padding to body element if small cart is displayed. [in px, 0 to ignore]', 'wppizza-locale')."</span>";

				echo"<hr />";
				echo"<hr />";
				echo "<input name='".$this->pluginSlug."[layout][minicart_add_to_element]' size='20' type='text'  value='{$options['layout']['minicart_add_to_element']}' />";
				echo" <span>".__('by default, the minicart will be added just before closing body tag with a css of position:fixed;top:0. if you want it appended elsewhere, set the relevant element here ', 'wppizza-locale')."</span>";
				echo"<br /><span class='description'>".__('use jQuery selectors, such as #my-elm-id or .my_elm_class etc. You might have to use additional css declarations for your theme.', 'wppizza-locale')."</span>";
				echo"<br />";
				echo "<input name='".$this->pluginSlug."[layout][minicart_elm_padding_selector]' size='20' type='text'  value='{$options['layout']['minicart_elm_padding_selector']}' />";
				echo" <span>".__('add above set padding to another element *instead* of the body tag (use jQuery selectors, such as #my-elm-id or .my_elm_class etc)', 'wppizza-locale')."</span>";
			}

			if($field=='close_shop_now'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[globals][".$field."]' type='checkbox'  ". checked($options['globals'][$field],true,false)." value='1' />";
				echo" <span class='description'>".__('enable and save to <b>close the shop *right now* ignoring all set opening times</b>. do not forget to disable if you want to be open again !!', 'wppizza-locale')."</span>";				
			}

			if($field=='opening_times_standard'){
				echo"<div id='wppizza_".$field."'>";
				foreach(wppizza_days() as $k=>$v){
					echo "<span class='wppizza_option'>";
					echo"<span class='wppizza_weekday'>".$v.":</span> ".__('open from', 'wppizza-locale').":";
					echo "<input name='".$this->pluginSlug."[".$field."][".$k."][open]' size='3' type='text' class='wppizza-time-select' value='{$options[$field][$k]['open']}' />";
					echo"".__('to', 'wppizza-locale').":";
					echo "<input name='".$this->pluginSlug."[".$field."][".$k."][close]' size='3' type='text' class='wppizza-time-select' value='{$options[$field][$k]['close']}' />";
					echo"</span>";
				}
				echo"</div>";
			}

			if($field=='opening_times_custom'){
				echo"<div id='wppizza_".$field."' >";
				echo"<div id='wppizza_".$field."_options'>";
				if(isset($options[$field]['date'])){
				foreach($options[$field]['date'] as $k=>$v){
					echo"".$this->wppizza_admin_section_opening_times_custom($field,$k,$options[$field]);
				}}
				echo"</div>";
				echo "<a href='#' id='wppizza_add_".$field."' class='button'>".__('add', 'wppizza-locale')."</a>";
				echo"</div>";
			}


			if($field=='times_closed_standard'){
				echo"<div id='wppizza_".$field."' >";
				echo"<div id='wppizza_".$field."_options'>";
				if(isset($options[$field]) && is_array($options[$field])){
				foreach($options[$field] as $k=>$v){
					echo"".$this->wppizza_admin_section_times_closed_standard($field,$k,$v);
				}}
				echo"</div>";
				echo "<a href='#' id='wppizza_add_".$field."' class='button'>".__('add', 'wppizza-locale')."</a>";
				echo"</div>";
			}


			if($field=='currency'){
				echo "<select name='".$this->pluginSlug."[order][".$field."]'>";
				foreach(wppizza_currencies($options['order'][$field]) as $l=>$m){
					echo "<option value='".$m['id']."' ".$m['selected'].">[".$m['id']."] - ".$m['value']."</option>";
				}
				echo "</select>";
			}

			if($field=='orderpage'){
				wp_dropdown_pages('name='.$this->pluginSlug.'[order]['.$field.']&selected='.$options['order'][$field].'&show_option_none='.__('select your orderpage', 'wppizza-locale').'');
				echo " ".__('Exclude from Navigation ?', 'wppizza-locale')." <input id='orderpage_exclude' name='".$this->pluginSlug."[order][orderpage_exclude]' type='checkbox'  ". checked($options['order']['orderpage_exclude'],true,false)." value='1' />";
			}

			if($field=='order_form'){

				asort($options[$field]);

				echo"<table id='wppizza_".$field."'>";
					echo"<tr><th>".__('Sort', 'wppizza-locale')."</th><th>".__('Label', 'wppizza-locale')."</th><th>".__('Enabled', 'wppizza-locale')."</th><th>".__('Required:<br />on Delivery', 'wppizza-locale')."</th><th>".__('Required:<br />on Pickup', 'wppizza-locale')."</th><th>".__('Prefill<br />[if known]', 'wppizza-locale')."</th><th>".__('Use when<br />Registering ?', 'wppizza-locale')."</th><th>".__('add to email<br />subject line ?', 'wppizza-locale')."</th><th>".__('Type', 'wppizza-locale')."</th></tr>";
				foreach($options[$field] as $k=>$v){
					$disableRegister=false;$disablePrefill=false;$fixedType='';$fixedTypeLabel='';

					if($v['key']=='cemail'){$disableRegister=true;$fixedType='email';$fixedTypeLabel='email';}
					if($v['key']=='ctips'){$disableRegister=true;$disablePrefill=true;$fixedType='tips';$fixedTypeLabel='';}
					if($v['key']=='csurcharges'){$disableRegister=true;$disablePrefill=true;$fixedType='selectcustom';}


					if($v['key']=='cemail'){$style=' style="margin-bottom:0"';}else{$style='';}
					echo"<tr class='".$v['key']."'>";
					echo"<td><input name='".$this->pluginSlug."[".$field."][".$k."][sort]' size='1' type='text' value='".$v['sort']."' /></td>";
					echo"<td><input name='".$this->pluginSlug."[".$field."][".$k."][lbl]' size='15' type='text' value='".$v['lbl']."' /></td>";
					echo"<td><input name='".$this->pluginSlug."[".$field."][".$k."][enabled]' type='checkbox' ". checked($v['enabled'],true,false)." value='1' /></td>";
					echo"<td><input name='".$this->pluginSlug."[".$field."][".$k."][required]' type='checkbox' ". checked($v['required'],true,false)." value='1' /></td>";
					echo"<td><input name='".$this->pluginSlug."[".$field."][".$k."][required_on_pickup]' type='checkbox' ". checked($v['required_on_pickup'],true,false)." value='1' /></td>";
					echo"<td>";
						if(!$disablePrefill){echo"<input name='".$this->pluginSlug."[".$field."][".$k."][prefill]' type='checkbox' ". checked($v['prefill'],true,false)." value='1' />";}else{echo"".__('N/A', 'wppizza-locale')."";}
					echo"</td>";
					echo"<td>";
						if(!$disableRegister){echo"<input name='".$this->pluginSlug."[".$field."][".$k."][onregister]' type='checkbox' ". checked($v['onregister'],true,false)." value='1' />";}else{echo"".__('N/A', 'wppizza-locale')."";}
					echo"</td>";
					echo"<td><input name='".$this->pluginSlug."[".$field."][".$k."][add_to_subject_line]' type='checkbox' ". checked($v['add_to_subject_line'],true,false)." value='1' /></td>";
					echo "<td>";

						if($fixedType!=''){
							echo "<input type='hidden' id='".$this->pluginSlug."_".$field."_type_".$k."' name='".$this->pluginSlug."[".$field."][".$k."][type]' value='".$fixedType."' />";
							echo "".$fixedTypeLabel."";
						}else{
							echo "<select id='".$this->pluginSlug."_".$field."_type_".$k."' class='".$this->pluginSlug."_".$field."_type' name='".$this->pluginSlug."[".$field."][".$k."][type]' />";
								echo'<option value="text" '.selected($v['type'],"text",false).'>text</option>';
								echo'<option value="email" '.selected($v['type'],"email",false).'>email</option>';
								echo'<option value="textarea" '.selected($v['type'],"textarea",false).'>textarea</option>';
								echo'<option value="select" '.selected($v['type'],"select",false).'>select</option>';
							echo "</select>";
						}

						$display=' style="display:none"';$val='';

						if($v['type']=='select'){$display='';$val=''.implode(",",$v['value']).'';}
						if($v['type']=='selectcustom'){$display='';
							$valArr=array();
							foreach($v['value'] as $vKey=>$vVal){
								$valArr[]=''.$vKey.':'.$vVal.'';
							}
							$val=implode("|",$valArr);
						}


						echo "<span class='".$this->pluginSlug."_".$field."_select'".$display.">";
							echo "<input name='".$this->pluginSlug."[".$field."][".$k."][value]' type='text' value='".$val."' />";
						echo "</span>";
						echo "<span class='".$this->pluginSlug."_".$field."_select'".$display.">";
							if($v['type']!='selectcustom'){echo "<span class='description'>".__('separate multiple with comma', 'wppizza-locale')."</span>";}
							if($v['type']=='selectcustom'){echo "".__('enter required value pairs', 'wppizza-locale')."";}
						echo "</span>";
					echo"</td>";// ".$v['key']." ".$v['type']."
					echo"</tr>";

					if($v['key']=='ctips'){
						echo"<tr class='".$v['key']."'><td colspan='9' style='margin:0;padding:0 0 0 10px'>";
						echo"<span class='description'>";
						echo"".__('<b>Tips/Gratuities:</b> allow the customer can enter a <b>numerical</b> amount to be used as tips/gratuities.<br />This field will not be added to the users profile and can therefore not be pre-filled or used in the registration form.', 'wppizza-locale')."";
						/**the following notice can probably be removed in a few months**/
						if (class_exists( 'WPPIZZA_GATEWAY_PAYPAL') ) {
							$pluginPath=dirname(dirname(plugin_dir_path( __FILE__ )));
							$gwPaypalData=get_plugin_data($pluginPath.'/wppizza-gateway-paypal/wppizza-gateway-paypal.php', false, false );
							if( version_compare( $gwPaypalData['Version'], '2.1' , '<' )) {
								echo"<br /><span style='color:red'>If you want to enable this field you MUST update to Wppizza Paypal Gateway 2.1+.<br />If your version of the paypal gateway is < 2.0 <a href='mailto:dev@wp-pizza.com'> contact me</a> with your purchase id for an update.<br />If your version is >= 2.0 you should be able to update via your dashboard (provided you activated your license).<br />This notice will disappear as soon as you have updated the Paypal Gateway. </span>";
							}
						}
						/*********************end of notice******************************/
						echo"</span>";
						echo"</td></tr>";
					}
				}
				echo"</table>";
			}

			if($field=='confirmation_form'){
				echo"<hr /><br />";
				echo "<input id='confirmation_form_enabled' name='".$this->pluginSlug."[confirmation_form_enabled]' type='checkbox'  ". checked($options['confirmation_form_enabled'],true,false)." value='1' />";
				echo"<span class='description'><b>".__('Some Countries/Jurisdictions require another, final , non-editable confirmation page before sending the order. If this is the case, tick this box and save. You will get some additional formfields you can make available in that final form', 'wppizza-locale')."</b></span>";
				if($options['confirmation_form_enabled']){
				echo"<br /><span style='color:red'>".__('Disclaimer: it is your responsibility to adhere to any required laws that might apply in your locality/jurisdiction', 'wppizza-locale')."</span>";
				}
				echo"<br /><br />";
				if($options['confirmation_form_enabled']){

					/***form fields*/
					asort($options[$field]);
					echo"<table id='wppizza_".$field."'>";
						echo"<tr><th colspan='5'>".__('Legal', 'wppizza-locale')." <span class='description'>[".__('enable some formfields or text/links you might want to use and/or make required', 'wppizza-locale')."]</span></th></tr>";
						echo"<tr><th>".__('Sort', 'wppizza-locale')."</th><th>".__('Label [html allowed]', 'wppizza-locale')."</th><th>".__('Enabled', 'wppizza-locale')."</th><th>".__('Required', 'wppizza-locale')."</th><th>".__('Type', 'wppizza-locale')."</th></tr>";
					foreach($options[$field] as $k=>$v){
						echo"<tr class='".$v['key']."'>";
						echo"<td><input name='".$this->pluginSlug."[".$field."][".$k."][sort]' size='1' type='text' value='".$v['sort']."' /></td>";
						echo"<td><input name='".$this->pluginSlug."[".$field."][".$k."][lbl]' size='55' type='text' value='".esc_html($v['lbl'])."' /></td>";
						echo"<td><input name='".$this->pluginSlug."[".$field."][".$k."][enabled]' type='checkbox' ". checked($v['enabled'],true,false)." value='1' /></td>";
						echo"<td><input name='".$this->pluginSlug."[".$field."][".$k."][required]' type='checkbox' ". checked($v['required'],true,false)." value='1' /></td>";
						echo"<td>";
							echo "<select id='".$this->pluginSlug."_".$field."_type_".$k."' class='".$this->pluginSlug."_".$field."_type' name='".$this->pluginSlug."[".$field."][".$k."][type]' />";
								echo'<option value="checkbox" '.selected($v['type'],"checkbox",false).'>'.__('checkbox', 'wppizza-locale').'</option>';
								echo'<option value="text" '.selected($v['type'],"text",false).'>'.__('text/link', 'wppizza-locale').'</option>';
							echo "</select>";
						echo"</td>";
						echo"</tr>";

					}
					echo"</table>";

					/***localization****/
					$includeDefaultOptions['localization_confirmation_form']=true;
					/**to get descriptions include default options. do not use require_once, as we need this more than once**/
					require(WPPIZZA_PATH .'inc/admin.setup.default.options.inc.php');
					/**add description to array**/
					$localizeOptions=array();
					foreach($defaultOptions['localization_confirmation_form'] as $k=>$v){
						$localizeOptions[$k]['descr']=$v['descr'];
						$localizeOptions[$k]['lbl']=$options['localization_confirmation_form'][$k]['lbl'];
					}
					asort($localizeOptions);
					echo"<table id='wppizza_".$field."'>";
					echo"<tr><th>".__('Localization', 'wppizza-locale')."</th></tr>";
					foreach($localizeOptions as $k=>$v){
						echo "<tr><td>";
						echo "<input name='".$this->pluginSlug."[localization_confirmation_form][".$k."]' size='30' type='text' value='".$v['lbl']."' />";
						echo"<span class='description'>".$v['descr']."</span>";
						if($k=='change_order_details'){
							echo"<br />";
							wp_dropdown_pages('name='.$this->pluginSlug.'[confirmation_form_amend_order_link]&selected='.$options['confirmation_form_amend_order_link'].'&show_option_none='.__('-- select page to link to --', 'wppizza-locale').'');
							echo"<span class='description'>".__('set link to page to allow customer to amend order', 'wppizza-locale')."</span>";
						}
						echo "</td></tr>";
					}
					echo"</table>";
				}
			}

			if($field=='delivery'){
				/****sort in a more sensible manner**/
				$options['order'][$field]=array('no_delivery'=>$options['order'][$field]['no_delivery'],'minimum_total'=>$options['order'][$field]['minimum_total'],'standard'=>$options['order'][$field]['standard'],'per_item'=>$options['order'][$field]['per_item']);
				/**end custom sort**/
				echo "<span id='wppizza-delivery-options-select'>";
				foreach($options['order'][$field] as $k=>$v){
					echo "<span class='wppizza_option'>";
					echo "<input name='".$this->pluginSlug."[order][delivery_selected]' type='radio' ". checked($options['order']['delivery_selected']==$k,true,false)." value='".$k."' />";

					if($k=='no_delivery'){
						echo" ".__('No delivery offered / pickup only', 'wppizza-locale')."";
						echo"<br /><span class='description'>".__('removes any labels, text, charges, checkboxes etc associated with delivery options. You can still set a minimum order value below.', 'wppizza-locale')."</span>";

					}
					if($k=='minimum_total'){
						echo" ".__('Free delivery when total order value reaches', 'wppizza-locale').":";
						echo"<input name='".$this->pluginSlug."[order][".$field."][minimum_total][min_total]' size='3' type='text' value='".wppizza_output_format_price($options['order'][$field]['minimum_total']['min_total'],$optionsDecimals)."' />";
						echo"<div style='margin-left:20px'>";
						echo"<input name='".$this->pluginSlug."[order][".$field."][minimum_total][deliver_below_total]' type='checkbox' ". checked($v['deliver_below_total'],true,false)." value='1' />";
						echo" ".__('Deliver even when total order value is below minimum (the difference between total and "Minimum Total" above will be added to the Total as "Delivery Charges")', 'wppizza-locale')."";
						echo"<br />";
						echo"<span class='description'>".__('(If this is not selected and the total order is below the set value above, the customer will not be able to submit the order to you)', 'wppizza-locale')."</span>";
						echo"<br />";
						echo"<input name='".$this->pluginSlug."[order][".$field."][minimum_total][deliverycharges_below_total]' size='3' type='text' value='".wppizza_output_format_price($options['order'][$field]['minimum_total']['deliverycharges_below_total'],$optionsDecimals)."' />";
						echo" ".__('Fixed Delivery charges if order has not reached total for free delivery [0 to disable]', 'wppizza-locale')."";
						echo"<br />";
						echo" <em style='color:red'>(".__('if set (i.e. not 0) "Deliver even when total order value is below minimum" must be checked for this to have any effect', 'wppizza-locale').")</em>";

						echo"</div>";
					}
					if($k=='standard'){
						echo" ".__('Fixed Delivery Charges [added to order total]', 'wppizza-locale').":";
						echo "<input name='".$this->pluginSlug."[order][".$field."][standard][delivery_charge]' size='3' type='text' value='".wppizza_output_format_price($options['order'][$field]['standard']['delivery_charge'],$optionsDecimals)."' />";

					}
					if($k=='per_item'){
						echo" ".__('Delivery Charges per item', 'wppizza-locale').":";
						echo"<div style='margin-left:20px'>";
						echo "<input name='".$this->pluginSlug."[order][".$field."][per_item][delivery_charge_per_item]' size='3' type='text' value='".wppizza_output_format_price($options['order'][$field]['per_item']['delivery_charge_per_item'],$optionsDecimals)."' />";
						echo" ".__('Do not apply delivery charges when total order value reaches ', 'wppizza-locale').":";
						echo"<input name='".$this->pluginSlug."[order][".$field."][per_item][delivery_per_item_free]' size='3' type='text' value='".wppizza_output_format_price($options['order'][$field]['per_item']['delivery_per_item_free'],$optionsDecimals)."' />";
						echo" ".__('[set to 0 to always apply charges per item]', 'wppizza-locale')."";
						echo"</div>";
					}
					echo "</span>";
				}
				echo "</span>";

				/**min order for delivery**/
				echo "<span class='wppizza_option' style='margin-top:20px'>";
				echo"<input name='".$this->pluginSlug."[order][order_min_for_delivery]' size='3' type='text' value='".wppizza_output_format_price($options['order']['order_min_for_delivery'],$optionsDecimals)."' />";
				echo" ".__('minimum order value - *on delivery* [will disable "place order" button in cart and order page until set order value (before any discounts etc) has been reached. 0 to disable.]', 'wppizza-locale')."<br />";
				echo" <span class='description'>".__('Customer can still choose "self-pickup" (if enabled / applicable).', 'wppizza-locale')."</span>";

				/**min order for pickup**/
				echo"<br />";
				echo"<input name='".$this->pluginSlug."[order][order_min_for_pickup]' size='3' type='text' value='".wppizza_output_format_price($options['order']['order_min_for_pickup'],$optionsDecimals)."' />";
				echo" ".__('minimum order value - *on self pickup* [will disable "place order" button in cart and order page until set order value (before any discounts etc) has been reached. 0 to disable.]', 'wppizza-locale')."<br />";
				echo "</span>";

				/**minimum order on totals**/
				echo"<br />";
				echo "<input id='".$field."' name='".$this->pluginSlug."[order][order_min_on_totals]' type='checkbox'  ". checked($options['order']['order_min_on_totals'],true,false)." value='1' />";
				echo" ".__('apply minimum order value on total sum of order [if left unchecked, minimum order uses sum of items in cart before any discounts etc]', 'wppizza-locale')."<br />";
				echo "</span>";

				/**Exclude following menu items when calculating if free delivery**/
				echo "<span class='wppizza_option' style='margin:20px 0'>";
					echo" ".__('<b>Exclude</b> following menu items when calculating if free delivery applies', 'wppizza-locale')." :<br />";
					echo'<span class="description">'.__('For example: you might want to offer free delivery only when total order of *meals* exceeds the set free delivery amount. In this case, exclude all your *drinks and non-meals* by selecting those below.', 'wppizza-locale').'</span><br />';
					echo"<select name='".$this->pluginSlug."[order][delivery_calculation_exclude_item][]' multiple='multiple' data-placeholder='".__('N/A', 'wppizza-locale')."' class='wppizza_delivery_calculation_exclude_item'>";
					$args = array('post_type' => ''.WPPIZZA_POST_TYPE.'','posts_per_page' => -1, 'orderby'=>'title' ,'order' => 'ASC');
					$query = new WP_Query( $args );
					foreach($query->posts as $pKey=>$pVal){
						echo"<option value='".$pVal->ID."' ";
							if(isset($options['order']['delivery_calculation_exclude_item']) && in_array($pVal->ID,$options['order']['delivery_calculation_exclude_item'])){
								echo" selected='selected'";
							}
						echo">".$pVal->post_title."</option>";
					}
					echo"</select>";
				echo "</span>";


				/**Exclude following categories  calculating  delivery**/
				if(is_array($cats)){
				echo "<span class='wppizza_option' style='margin:20px 0'>";
					echo" ".__('<b>Exclude</b> all  menu items belonging to following *categories* when calculating if free delivery applies', 'wppizza-locale')." :<br />";
					echo"<select name='".$this->pluginSlug."[order][delivery_calculation_exclude_cat][]' multiple='multiple'  data-placeholder='".__('N/A', 'wppizza-locale')."' class='wppizza_delivery_calculation_exclude_cat'>";
					foreach($cats as $cKey=>$cVal){
						echo"<option value='".$cVal->term_id."' ";
							if(isset($options['order']['delivery_calculation_exclude_cat']) && isset($options['order']['delivery_calculation_exclude_cat'][$cVal->term_id])){
								echo" selected='selected'";
							}
						echo">".$cVal->name."</option>";
					}
					echo"</select>";
				echo "</span>";
				}

			}
			/**I don't think this actually in use anywhere ?!**/
			if($field=='delivery_per_item'){
				echo"<input name='".$this->pluginSlug."[order][".$field."]' size='3' type='text' value='".wppizza_output_format_price($options['order'][$field],$optionsDecimals)."' />";
			}

			if($field=='order_pickup'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[order][".$field."]' type='checkbox'  ". checked($options['order'][$field],true,false)." value='1' /> ".__('tick to enable', 'wppizza-locale')."";

				echo "<br />".__('Discount for self-pickup ?', 'wppizza-locale')." <input id='order_pickup_discount' name='".$this->pluginSlug."[order][order_pickup_discount]' size='5' type='text' value='".wppizza_output_format_float($options['order']['order_pickup_discount'],'percent')."' /> ".__('in % - 0 to disable', 'wppizza-locale')."";

				echo "<br /><input id='order_pickup_alert' name='".$this->pluginSlug."[order][order_pickup_alert]' type='checkbox'  ". checked($options['order']['order_pickup_alert'],true,false)." value='1' /> ".__('enable javascript alert when user selects self pickup (set corresponding text in localization)', 'wppizza-locale')."";

				echo "<br /><input id='order_pickup_alert_confirm' name='".$this->pluginSlug."[order][order_pickup_alert_confirm]' type='checkbox'  ". checked($options['order']['order_pickup_alert_confirm'],true,false)." value='1' /> ".__('make user *confirm* change of pickup/delivery', 'wppizza-locale')."";

				echo "<br /><input id='order_pickup_as_default' name='".$this->pluginSlug."[order][order_pickup_as_default]' type='checkbox'  ". checked($options['order']['order_pickup_as_default'],true,false)." value='1' /> ".__('set "pickup" to be the default selection (make sure to clear your browser cache to see the effect)', 'wppizza-locale')."";
			}
			if($field=='order_pickup_display_location'){
				echo "".__('under cart only', 'wppizza-locale')."<input id='".$field."' name='".$this->pluginSlug."[order][".$field."]' type='radio'  ".checked($options['order'][$field],1,false)." value='1' /> ";
				echo "".__('on order page only', 'wppizza-locale')."<input id='".$field."' name='".$this->pluginSlug."[order][".$field."]' type='radio'  ".checked($options['order'][$field],2,false)." value='2' /> ";
				echo "".__('both', 'wppizza-locale')."<input id='".$field."' name='".$this->pluginSlug."[order][".$field."]' type='radio'  ".checked($options['order'][$field],3,false)." value='3' />";
			}


			if($field=='discounts'){
				foreach($options['order'][$field] as $k=>$v){
					echo "<span class='wppizza_option'>";
					echo "<input name='".$this->pluginSlug."[order][discount_selected]' type='radio' ". checked($options['order']['discount_selected']==$k,true,false)." value='".$k."' />";
					if($k=='none'){
						echo"".__('No Discounts', 'wppizza-locale')."";
					}
					if($k=='percentage'){
						echo"".__('Percentage Discount', 'wppizza-locale').":";
						echo"<br />";
						foreach($v['discounts'] as $l=>$m){
							echo"".__('If order total >', 'wppizza-locale').":";
							echo"<input name='".$this->pluginSlug."[order][".$field."][".$k."][discounts][".$l."][min_total]' size='3' type='text' value='".wppizza_output_format_price($options['order'][$field][$k]['discounts'][$l]['min_total'],$optionsDecimals)."' />";
							echo"".__('discount', 'wppizza-locale').":";
							echo"<input name='".$this->pluginSlug."[order][".$field."][".$k."][discounts][".$l."][discount]' size='5' type='text' value='".wppizza_output_format_float($options['order'][$field][$k]['discounts'][$l]['discount'],'percent')."' />";
							echo"".__('percent', 'wppizza-locale')."";
							echo"<br />";
						}
					}
					if($k=='standard'){
						echo"".__('Standard Discount [money off]', 'wppizza-locale').":";
						echo"<br />";
						foreach($v['discounts'] as $l=>$m){
							echo"".__('If order total >', 'wppizza-locale').":";
							echo"<input name='".$this->pluginSlug."[order][".$field."][".$k."][discounts][".$l."][min_total]' size='3' type='text' value='".wppizza_output_format_price($options['order'][$field][$k]['discounts'][$l]['min_total'],$optionsDecimals)."' />";
							echo"".__('get', 'wppizza-locale').":";
							echo"<input name='".$this->pluginSlug."[order][".$field."][".$k."][discounts][".$l."][discount]' size='3' type='text' value='".wppizza_output_format_price($options['order'][$field][$k]['discounts'][$l]['discount'],$optionsDecimals)."' />";
							echo"".__('off', 'wppizza-locale')."";
							echo"<br />";
						}
					}
					echo "</span>";
				}

				/**Exclude following menu items when calculating  discounts**/
				echo "<span class='wppizza_option' style='margin:20px 0'>";
					echo" ".__('<b>Exclude</b> following menu items when calculating discounts', 'wppizza-locale')." :<br />";
					echo"<select name='".$this->pluginSlug."[order][discount_calculation_exclude_item][]' multiple='multiple' data-placeholder='".__('N/A', 'wppizza-locale')."' class='wppizza_discount_calculation_exclude_item'>";
					$args = array('post_type' => ''.WPPIZZA_POST_TYPE.'','posts_per_page' => -1, 'orderby'=>'title' ,'order' => 'ASC');
					$query = new WP_Query( $args );
					foreach($query->posts as $pKey=>$pVal){
						echo"<option value='".$pVal->ID."' ";
							if(isset($options['order']['discount_calculation_exclude_item']) && isset($options['order']['discount_calculation_exclude_item'][$pVal->ID])){
								echo" selected='selected'";
							}
						echo">".$pVal->post_title."</option>";
					}
					echo"</select>";
				echo "</span>";


				/**Exclude following categories  calculating  discounts**/
				if(is_array($cats)){
				echo "<span class='wppizza_option' style='margin:20px 0'>";
					echo" ".__('<b>Exclude</b> all  menu items belonging to following categories when calculating discounts', 'wppizza-locale')." :<br />";
					echo"<select name='".$this->pluginSlug."[order][discount_calculation_exclude_cat][]' multiple='multiple'  data-placeholder='".__('N/A', 'wppizza-locale')."' class='wppizza_discount_calculation_exclude_cat'>";
					foreach($cats as $cKey=>$cVal){
						echo"<option value='".$cVal->term_id."' ";
							if(isset($options['order']['discount_calculation_exclude_cat']) && isset($options['order']['discount_calculation_exclude_cat'][$cVal->term_id])){
								echo" selected='selected'";
							}
						echo">".$cVal->name."</option>";
					}
					echo"</select>";
				echo "</span>";
				}
			}

			if($field=='append_internal_id_to_transaction_id'){
				echo "<input name='".$this->pluginSlug."[order][".$field."]' type='checkbox'  ". checked($options['order'][$field],true,false)." value='1' />";
				echo" ".__('enable to append internal order ID to transaction ID [e.g COD13966037358 will become COD13966037358/123 where 123 = internal id of order table]', 'wppizza-locale')."";
			}

			if($field=='order_email_to' || $field=='order_email_cc' || $field=='order_email_bcc' ){//$field==order_sms => not implemented
				if(is_array($options['order'][$field])){$val=implode(",",$options['order'][$field]);}else{$val='';}
				echo "<input id='".$field."' name='".$this->pluginSlug."[order][".$field."]' size='30' type='text' value='".$val."' />";
			}
			if($field=='order_email_attachments'){
				if(isset($options['order'][$field]) && is_array($options['order'][$field])){$val=implode(",",$options['order'][$field]);}else{$val='';}
				echo "<input id='".$field."' name='".$this->pluginSlug."[order][".$field."]' size='30' type='text' value='".$val."' placeholder='/absolute/path/to/your/file'/>";
				echo" <span class='description'>".__('if you wish to add an attachment to the order emails add the FULL ABSOLUTE PATH to the file(s) here', 'wppizza-locale')."</span>";
			}

			if($field=='order_email_from'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[order][".$field."]' size='30' type='text' value='".$options['order'][$field]."' /><br />";
				echo" <input name='".$this->pluginSlug."[order][dmarc_nag_off]' type='checkbox'  ". checked($options['order']['dmarc_nag_off'],true,false)." value='1' /> ";
				echo"".__('<b>if a DMARC notice is displayed at the top of the page (otherwise you can ignore this setting)</b>:<br />I know what I\'m doing, have read the DMARC nag notice about setting a static from address (if applicable) and/or have already set the appropriate email here. Stop bugging me.', 'wppizza-locale')."";
			}
			if($field=='order_email_from_name'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[order][".$field."]' size='30' type='text' value='".$options['order'][$field]."' />";
			}



			if($field=='item_tax'){
				echo "<input id='".$field."' name='".$this->pluginSlug."[order][".$field."]' size='5' type='text' value='".wppizza_output_format_float($options['order'][$field],'percent')."' />%";
				echo"<br />";
				echo "<input id='".$field."' name='".$this->pluginSlug."[order][item_tax_alt]' size='5' type='text' value='".wppizza_output_format_float($options['order']['item_tax_alt'],'percent')."' />%";
				echo" <span class='description'>".__('alternative taxrate [assign on a per menu item basis]', 'wppizza-locale')."</span>";
				echo"<br />";
				echo"<input name='".$this->pluginSlug."[order][shipping_tax]' type='checkbox'  ". checked($options['order']['shipping_tax'],true,false)." value='1' />";
				echo" ".__('apply tax to delivery/shipping too at', 'wppizza-locale')."";
				echo "<input id='".$field."' name='".$this->pluginSlug."[order][shipping_tax_rate]' size='5' type='text' value='".wppizza_output_format_float($options['order']['shipping_tax_rate'],'percent')."' />%";
				echo"<br />";
				echo"<input name='".$this->pluginSlug."[order][taxes_included]' type='checkbox'  ". checked($options['order']['taxes_included'],true,false)." value='1' />";
				echo" ".__('all prices are entered including tax, but I distinctly need to display the sum of taxes applied', 'wppizza-locale')."";
				echo"<br /><span class='description'>".__('if enabled, the sum of applicable taxes will be displayed separately without however adding it to the total (if taxrate > 0%).', 'wppizza-locale')."</span>";
				echo"<br /><span class='description' style='color:red'>".__('if you set different taxrates, make sure to set your text in wppizza->localization regarding taxes as appropriate', 'wppizza-locale')."</span>";

				echo"<br /><br />";
				echo"<input name='".$this->pluginSlug."[order][taxes_round_natural]' type='checkbox'  ". checked($options['order']['taxes_round_natural'],true,false)." value='1' />";
				echo" ".__('Typically any decimal fractions of applicable taxes are rounded up. Tick this box if your tax laws allow for "natural" rounding (i.e rounding down if fractions are below .5)', 'wppizza-locale')."";
			}
			if($field=='sizes'){
				/*get all sizes in use to allow for "in use" flag */
				$optionInUse=wppizza_options_in_use('sizes');

				echo"<div id='wppizza_".$field."'>";
				echo"<div id='wppizza_".$field."_options'>";
				foreach($options[$field] as $k=>$v){
					echo"".$this->wppizza_admin_section_sizes($field,$k,$v,$optionInUse);
				}
				echo"</div>";
				echo"<div id='wppizza-sizes-add'>";
				echo "<a href='#' id='wppizza_add_".$field."' class='button'>".__('add', 'wppizza-locale')."</a>";
				echo "<input id='wppizza_add_".$field."_fields' size='1' type='text' value='1' />".__('how many size option fields ?', 'wppizza-locale')."";
				echo"</div>";
				echo"</div>";
			}
			if($field=='additives'){
				/*get all additives in use to allow for "in use" flag */
				$optionInUse=wppizza_options_in_use('additives');

				echo"<div id='wppizza_".$field."'>";
					echo"<div id='wppizza_".$field."_options'>";
					if(isset($options[$field]) && is_array($options[$field])){
					asort($options[$field]);//sort
					foreach($options[$field] as $k=>$v){
						echo"".$this->wppizza_admin_section_additives($field,$k,$options[$field][$k],$optionInUse);
					}}
					echo"</div>";
					echo"<div id='wppizza-additives-add'>";
					echo "<a href='#' id='wppizza_add_".$field."' class='button'>".__('add', 'wppizza-locale')."</a>";
					echo"</div>";
				echo"</div>";
			}

			if($field=='gateways'){
				echo"<div id='wppizza_".$field."'>";

				echo"<div id='wppizza_".$field."_options'>";
					echo"<div>";
						echo"<input name='".$this->pluginSlug."[gateways][gateway_select_as_dropdown]' type='checkbox'  ". checked($options['gateways']['gateway_select_as_dropdown'],true,false)." value='1' />";
						echo" <b>".__('Display Gateway choices as dropdowns instead of buttons', 'wppizza-locale')."</b> ".__('[only applicable if more than one gateway installed, activated and enabled]', 'wppizza-locale')."";
					echo"</div>";

					echo"<div>";
						echo"<b>".__('Label:', 'wppizza-locale')."</b> ";
						echo"<input name='".$this->pluginSlug."[gateways][gateway_select_label]' type='text' size='50' value='". $options['gateways']['gateway_select_label']."' />";
						echo"<br />".__('by default displayed above if choices are displayed as full width buttons, next to if dropdown. edit css as required', 'wppizza-locale')." ".__('[only applicable if more than one gateway installed, activated and enabled]', 'wppizza-locale')."";
					echo"</div>";

					echo"<div>";
						echo"<input name='".$this->pluginSlug."[gateways][gateway_showorder_on_thankyou]' type='checkbox'  ". checked($options['gateways']['gateway_showorder_on_thankyou'],true,false)." value='1' />";
						echo" <b>".__('Show Order Details on "Thank You" page (Y/N)', 'wppizza-locale')."</b> ".__('Will add any order details after your thank you text on successful order', 'wppizza-locale')."";
					echo"</div>";

						$this->wppizza_admin_section_gateways($field,$options[$field]);

					echo"</div>";
				echo"</div>";
			}
			if($field=='localization'){

				$includeDefaultOptions['localization']=true;
				/**to get descriptions include default options. do not use require_once, as we need this more than once**/
				require(WPPIZZA_PATH .'inc/admin.setup.default.options.inc.php');

				/**add description to array**/
				$localizeOptions=array();
				foreach($defaultOptions['localization'] as $k=>$v){
					$localizeOptions[$k]['descr']=$v['descr'];
					$localizeOptions[$k]['lbl']=$options[$field][$k]['lbl'];
				}

				$textArea=array('thank_you_p','order_ini_additional_info');/*tinymce textareas*/
				echo"<div id='wppizza_".$field."'>";
					echo"<div id='wppizza_".$field."_options'>";
					asort($localizeOptions);
					$lngOddEvenArray=__('0,9,13,14,16,19,24,26,28,31,55,63,71,82,90', 'wppizza-locale');
					$lngOddEvan=explode(",",$lngOddEvenArray);
					$bgStyle=$lngOddEvan;
					$i=0;
					foreach($localizeOptions as $k=>$v){
					if(in_array($i,$bgStyle)){echo'<div>';}
						if(in_array($k,$textArea)){
							$editorId="".strtolower($this->pluginSlug."_".$field."_".$k)."";/* WP 3.9 doesnt like brackets in id's*/
							$editorName="".$this->pluginSlug."[".$field."][".$k."]";
							echo"<div class='wppizza_localization_textarea_wrap'>";
							echo"<span>".$v['descr']."</span>";
							echo"<div class='wppizza_localization_textarea'>";
							wp_editor( $v['lbl'], $editorId, array('teeny'=>1,'wpautop'=>false,'textarea_name'=>$editorName) );
							echo"</div>";
							echo"</div><br />";
						//echo "<textarea name='".$this->pluginSlug."[".$field."][".$k."]' style='width:185px;height:150px'>".$v['lbl']."</textarea>";
						}else{
						echo "<input name='".$this->pluginSlug."[".$field."][".$k."]' size='30' type='text' value='".$v['lbl']."' />";
						echo"<span class='description'>".$v['descr']."</span><br />";
						}
					$i++;
					if(in_array($i,$bgStyle)){echo'</div>';}
					}
					echo"</div>";
				echo"</div>";
			}


			if($field=='access'){
				global $current_user,$user_level,$wp_roles;
				echo"<div id='wppizza_".$field."'>";
					$roles=get_editable_roles();/*only get roles user is allowed to edit**/
					/*do not display current users role (otherwise he can screw his own access) or levels higher than current*/
					if(is_array($current_user->roles)){
					foreach($current_user->roles as $curRoles){
						if(isset($roles[$curRoles])){
							unset($roles[$curRoles]);
						}
					}}

					$access=$this->wppizza_set_capabilities();
					foreach($roles as $roleName=>$v){

						$userRole = get_role($roleName);
							echo"<div class='wppizza-access'>";
							echo"<input type='hidden' name='".$this->pluginSlug."[admin_access_caps][".$roleName."]' value='".$roleName."'>";
							echo"<ul>";
							print"<li style='width:150px'><b>".$roleName.":</b></li>";
								foreach($access as $aKey=>$aArray){
									echo"<li><input name='".$this->pluginSlug."[admin_access_caps][".$roleName."][".$aArray['cap']."]' type='checkbox'  ". checked(isset($userRole->capabilities[$aArray['cap']]),true,false)." value='".$aArray['cap']."' /> ".$aArray['name']."<br /></li>";//". checked($options['plugin_data']['access_level'],true,false)."
								}
							echo"</ul>";
							echo"</div>";
					}
				echo"</div>";
			}

			if($field=='templates'){
				require_once(WPPIZZA_PATH.'classes/wppizza.templates.inc.php');
				$templates=new WPPIZZA_TEMPLATES();
				$templateSettingsAdmin=$templates->getTemplateSettings($field,$options);
				echo $templateSettingsAdmin;
			}
			if($field=='history'){
				echo"<div id='wppizza_".$field."'>";

					echo"<div id='wppizza_".$field."_totals'></div>";
					

					echo"<div id='wppizza_".$field."_search' class='button' style='overflow:auto'>";

						echo "<span style='float:left;'>";
						echo "<a href='#' id='".$field."_get_orders' class='button' style='margin-top:6px'>".__('show most recent *confirmed* orders', 'wppizza-locale')."</a>";
						echo" ".__('status', 'wppizza-locale').": ";
						echo "<select id='".$field."_orders_status' name='".$field."_orders_status'>";
							echo"<option value=''>".__('-- All --', 'wppizza-locale')."</option>";
							echo"<option value='NEW'>".__('new', 'wppizza-locale')."</option>";
							echo"<option value='ACKNOWLEDGED'>".__('acknowledged', 'wppizza-locale')."</option>";
							echo"<option value='ON_HOLD'>".__('on hold', 'wppizza-locale')."</option>";
							echo"<option value='PROCESSED'>".__('processed', 'wppizza-locale')."</option>";
							echo"<option value='DELIVERED'>".__('delivered', 'wppizza-locale')."</option>";
							echo"<option value='REJECTED'>".__('rejected', 'wppizza-locale')."</option>";
							echo"<option value='REFUNDED'>".__('refunded', 'wppizza-locale')."</option>";
							echo"<option value='OTHER'>".__('other', 'wppizza-locale')."</option>";
						echo "</select>";
						echo "</span>";
						
					/* add action hook */
					do_action('wppizza_admin_orderhistory_header_after_status');
						
						echo " <span style='float:left;'>".__('maximum results [0 to show all]', 'wppizza-locale')."<input id='".$field."_orders_limit' name='".$field."_orders_limit' size='3' type='text' value='".$options['plugin_data']['admin_order_history_max_results']."' /></span>";
					
					/* add action hook */
					do_action('wppizza_admin_orderhistory_header_after_max_results');


						echo "<span style='float:right;margin-right:50px;'>";
						echo " ".__('poll for new orders every', 'wppizza-locale')."<input id='".$field."_orders_poll_interval' name='".$field."_orders_poll_interval' size='2' type='text' value='".$options['plugin_data']['admin_order_history_polling_time']."' />".__('seconds', 'wppizza-locale')." ";
						echo "<label class='button' style='margin-top:6px'><input id='".$field."_orders_poll_enabled' type='checkbox' ". checked($options['plugin_data']['admin_order_history_polling_auto'],true,false)." value='1' />".__('on|off', 'wppizza-locale')."</span>";
								echo "<span id='wppizza-orders-polling'></span>";/*shows loading icon*/
						echo "</label>";
					echo"</div>";
					
					/* container div for orders */
					echo"<div id='wppizza_".$field."_orders'></div>";

				echo"</div>";

			}
			if($field=='tools'){
				if(!isset($_GET['tab']) || $_GET['tab']=='tools'){
				echo"<div id='wppizza-general'>";

					/*****************************
					*
					*	clear abandoned orders
					*
					****************************/

					echo"<div class='wppizza_option'>";
						echo" <b>".__('Delete abandoned/cancelled orders from database older than', 'wppizza-locale')."</b> ";
						echo"<input id='wppizza_order_days_delete' type='text' size='2' name='".$this->pluginSlug."[cron][days_delete]' value='".$options['cron']['days_delete']."' />";
						echo" <b>".__('Days (minimum: 1)', 'wppizza-locale')."</b> ";
						echo"<input id='wppizza_order_failed_delete' name='".$this->pluginSlug."[cron][failed_delete]' ". checked($options['cron']['failed_delete'],true,false)." type='checkbox' value='1' />";
						echo" <b>".__('delete failed, tampered or otherwise invalid entries too.', 'wppizza-locale')."</b> ";
						echo"<br /><span id='wppizza_order_abandoned_delete' class='button'>".__('do it now', 'wppizza-locale')."</span>";

						/*schedule cron**/
						$cronJobs=''.print_r(get_option('cron'),true);/**if we deactivated the plugin, cron will have been disabled for this, so we set the flag accordingly**/
						$wppizzaCronRunning = strpos($cronJobs, 'wppizza_cron');/**just search for wppizza_cron in string*/
						if ($wppizzaCronRunning === false) {
							$options['cron']['schedule']='';
						}
						echo"<br /><b>".__('schedule above to run automatically', 'wppizza-locale')."</b>";
						echo "<select name='".$this->pluginSlug."[cron][schedule]' />";
							echo"<option value=''>".__('do not run', 'wppizza-locale')."</option>";
							echo"<option value='hourly' ".selected($options['cron']['schedule'],"hourly",false).">".__('hourly', 'wppizza-locale')."</option>";
							echo"<option value='daily' ".selected($options['cron']['schedule'],"daily",false).">".__('daily', 'wppizza-locale')."</option>";
						echo "</select>";
						echo"".__('uses wp_cron', 'wppizza-locale')."";


						echo"<br />".__('As soon as customers go to the order page an order will be initialized and stored in the db to be checked against when going through with the purchase to make sure nothing has been tampered with. However, not every customer will actually go through with the purchase which leaves this initialised order orphaned in the db.Click the "ok" button to clean your db of these entries (it will NOT affect any completed or pending orders)', 'wppizza-locale')."";
						echo"<br /><br /><span style='color:red'>".__('Note: This will delete these entries PERMANENTLY from the db and is not reversable.', 'wppizza-locale')."</span>";
					echo"</div>";

					/*****************************
					*
					*	repair category orders
					*
					****************************/
					echo"<div class='wppizza_option'>";
						echo"<input id='wppizza_category_repair' name='".$this->pluginSlug."[maintenance][category_repair]' type='checkbox' value='1' />";
						echo" <b>".__('repair categories.', 'wppizza-locale')."</b> ";
						echo"<br />".__('There exists an (as yet) unknown sequence of events related to saving/adding/editing/deleting categories of this plugin that may result in the last category being repeated when using the category=!all shortcode attribute and/or not all categories showing up in the admin of the plugin.<br />If this should be the case, you can try repairing this by checking the box above and saving once.<br /><br /><b>If you use this function, categories will be re-set using default alphabetical sort order, so please ensure your category order is still as required as you might have to re-sort - i.e drag and drop -  categories again</b>.<br /><br />In case this does not solve the issue, please contact me, letting me know anything you did before this issue occured if possible.', 'wppizza-locale')."";
					echo"</div>";


					/*****************************
					*
					*	truncate order table
					*
					****************************/
					echo"<div class='wppizza_option'>";
						echo"<input id='wppizza_truncate_orders' name='".$this->pluginSlug."[maintenance][truncate_orders]' type='checkbox'  value='1' />";
						echo" <b>".__('Empty order table ?', 'wppizza-locale')."</b><br />";
						echo"<span style='color:red'>".__('completely and irreversibly EMPTIES the order table deleting ALL orders.', 'wppizza-locale')."</span>";
					echo"</div>";

					/*****************************
					*
					*	empty categories and items
					*
					****************************/
					echo"<div class='wppizza_option'>";
						echo "<input id='empty_category_and_items' name='".$this->pluginSlug."[maintenance][empty_category_and_items]' type='checkbox'  value='1' />";
						echo '<b>'.__('Delete ALL WPPizza Categories and Items<br/><span style="color:red">use with care<br/>if you select "delete images too", all featured images used for any wppizza menu items will be deleted too.<br/>if you use these images elsewhere, you should not select this !</span>', 'wppizza-locale').'</b>';
						echo"<br /><input id='empty_category_and_items_delete_attachments' name='".$this->pluginSlug."[maintenance][delete_attachments]' type='checkbox'  value='1' />";
						echo" ".__('delete images too', 'wppizza-locale')."";
					echo"</div>";

					/*****************************
					*
					*	MISC TOOLS
					*
					****************************/
					/*debug legacy for other plugin*/
					echo"<div class='wppizza_option'>";
						echo"<input id='wppizza_debug' name='".$this->pluginSlug."[".$field."][debug]' type='checkbox' ". checked($options[$field]['debug'],true,false)." value='1' />";
						echo" <b>".__('Debug', 'wppizza-locale')."</b>";
						echo" <span class='description'>".__('should be *OFF* unless asked to enable', 'wppizza-locale')."</span>";
					echo"</div>";

					/*send emails*/
					echo"<div class='wppizza_option'>";
						echo"<input id='wppizza_disable_emails' name='".$this->pluginSlug."[".$field."][disable_emails]' type='checkbox' ". checked($options[$field]['disable_emails'],true,false)." value='1' />";
						echo" <b>".__('Disable email sending', 'wppizza-locale')."</b>";
						echo" <span class='description'>".__('Check this box to stop sending emails. If you want to test things without actually sending any emails', 'wppizza-locale')."</span>";
					echo"</div>";

					/*****************************
					*
					*	update order table
					*
					****************************/
					echo"<div class='wppizza_option'>";
						echo "<input id='update_order_table' name='".$this->pluginSlug."[maintenance][check_order_table]' type='checkbox'  value='1' />";
						echo ' <b>'.sprintf(__('Update order table (check this and save once *after* you have updated you mysql version to the *required* %1$s+. If you are already using %1$s+, ignore this)', 'wppizza-locale'), $this->pluginMinMysqlVersionRequired ).'</b>';
					echo"</div>";


					/*****************************
					*
					*	initialize wpml strings
					*
					****************************/
					if(function_exists('icl_register_string')){
					echo"<div class='wppizza_option'>";
						echo "<input id='wpml_ini_string' name='".$this->pluginSlug."[maintenance][wpml_ini_string]' type='checkbox'  value='1' />";
						echo ' <b>'.__('If you have installed WPML _after_ wppizza,  strings might not yet be registered in WPML string translation. Check this checkbox and save once to do so.', 'wppizza-locale').'</b>';
					echo"</div>";
					}


					/*insert hidden field to know we are submitting tools , even if all checkboxes unchecked*/
					echo"<input name='".$this->pluginSlug."[".$field."][ini]' type='hidden'  value='1' />";
				echo"</div>";
				}

				if(isset($_GET['tab']) && $_GET['tab']=='sysinfo'){
				echo"<div id='wppizza_".$field."_system_info'>";
					echo"<br /><h2>".__('System Info', 'wppizza-locale')."</h2> ";
					echo $this->wppizza_system_info_include();
					echo"<br /><h2>".__('WPPizza options set', 'wppizza-locale')."</h2> ";
					$wppizzaOptions=get_option(WPPIZZA_SLUG);
					echo"<textarea  readonly='readonly' onclick='this.focus();this.select();' style='width:100%;height:150px'>### ALL WPPIZZA VARIABLES ###".PHP_EOL.print_r(maybe_serialize($wppizzaOptions),true)."</textarea>";


					/*in case some other plugin wants to display things here too*/
					do_action('wppizza_admin_tools_system_info');

					echo"<br />";
					echo "<h2><a href='javascript:void(0)' id='wppizza_show_php_vars' class='button'>".__('show php configuration', 'wppizza-locale')."</a></h2>";
					echo"<div id='wppizza_php_info'></div>";
				echo"</div>";
				}
			}

	}
?>