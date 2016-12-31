<?php
if (!class_exists( 'WPPizza' ) ) {return ;}
class WPPIZZA_ACTIONS extends WPPIZZA {

      private static $_this;

      function __construct() {
    	parent::__construct();

		self::$_this = $this;
		/************************************************************************
			[runs in front AND backend]
		*************************************************************************/
			/*sort categories**/
			add_action('init', array( $this, 'wppizza_add_get_terms_filter'));

	    	add_action('init', array( $this, 'wppizza_require_common_input_validation_functions'));/*include input validation functions**/
	    	add_action('init', array( $this, 'wppizza_require_common_output_formatting_functions'));/*include output formatting functions**/
	    	add_action('init', array( $this, 'wppizza_require_common_helper_functions'));
	    	add_action('init', array( $this, 'wppizza_register_custom_posttypes'));/*register custom posttype*/


			add_action('init', array( $this, 'wppizza_register_custom_taxonomies'));/*register taxonomies*/
			add_action('init', array( $this,'wppizza_init_sessions'));/*needed for admin AND frontend***/			/**add sessions to keep track of shoppingcart***/
			add_shortcode($this->pluginSlug, array($this, 'wppizza_add_shortcode'));//used in ajax request for cart contents so must be available when ajax and on front AND backend!

			/*class to send order emails. LEGACY WILL BE DEPRECATED*/
			add_action('init', array( $this, 'wppizza_send_order_emails'));


			/**override shop opening - closing it now with hight priority*/
			add_filter('wppizza_filter_is_open', array( $this, 'wppizza_filter_close_shop_now'),1000);

			/***************
				[filters]
			***************/
			/**sanitize db input vars**/
			add_filter('wppizza_filter_sanitize_order', array( $this, 'wppizza_filter_sanitize_order'),10,1);
			add_filter('wppizza_filter_sanitize_post_vars', array( $this, 'wppizza_filter_sanitize_post_vars'),10,1);
			add_filter('wppizza_filter_order_summary', array( $this, 'wppizza_filter_order_summary_legacy'),10,1);

			/***************
				[add selected fields to the registration process]
			***************/
			add_action( 'register_form', array( $this,'wppizza_user_register_form_display_fields') );
			add_action( 'user_register', array( $this,'wppizza_user_register_form_save_fields'), 100 );
			/*multisite*/
			if(is_multisite()){
				add_action( 'signup_extra_fields', array( $this,'wppizza_user_register_form_display_fields') );
				add_filter( 'add_signup_meta',array($this, 'wppizza_ms_user_register_add_signup_meta'));//capture the data
				add_action( 'wpmu_activate_user', array($this, 'wppizza_ms_user_register_form_save_fields'), 10, 3 );//get the meta data out of signups and push it into wp_usermeta during activation
			}

			/**allow rewriting of single item permalinks**/
			add_filter('wppizza_cpt_args',array( $this,'wppizza_single_item_permalink_rewrite'));


		/************************************************************************
			[runs only for frontend]
		*************************************************************************/
		if(!is_admin()){

			/***EXPERIMENTAL frontend scripts - load straight after jquery ***/
			add_action('wp_enqueue_scripts', array( $this, 'wppizza_load_experimental_scripts_and_styles'),9);

			/***enqueue frontend scripts and styles***/
			add_action('wp_enqueue_scripts', array( $this, 'wppizza_register_scripts_and_styles'),$this->pluginOptions['layout']['css_priority']);
			/*dequeue if set**/
			add_action('wp_print_scripts', array( $this, 'wppizza_dequeue_scripts'),100);
			/***************
				[filters]
			***************/
			/*include template**/
			add_filter('template_include', array( $this,'wppizza_include_template'), 1 );

			/******add js functions to run after cart refresh (if using shortcode type=totals**********************************/
			add_filter('wppizza_filter_js_cart_refresh_functions', array( $this, 'wppizza_filter_totals_js_cart_refresh_functions'),10,1);


			/**set possibly missing vars if using templates **/
			add_filter('wppizza_loop_top', array( $this,'wppizza_loop_include_vars'), 1);


			/***use loop for single post***/
			add_filter('wppizza_filter_loop', array( $this, 'wppizza_filter_loop'),10,2);

			/***exclude selected order page from navigation */
			add_filter('get_pages', array($this,'wppizza_exclude_order_page_from_navigation'));

			/***dont put WPPizza Categories intitle tag */
			add_filter( 'wp_title', array( $this, 'wppizza_filter_title_tag'),20,3);


			/***order form, login, logout, register, guest ****/
			add_action('wppizza_order_form_before', array( $this, 'wppizza_do_login_form'));/**login/logout**/
			add_filter('wp_authenticate', array( $this, 'wppizza_authenticate'),1,2);/*authenticate and redirect**/
			add_filter('login_redirect', array( $this, 'wppizza_login_redirect'),10,3);/**successful login redirect**/
			add_action('wppizza_gateway_choice_before', array( $this, 'wppizza_create_user_option'));/**continue and register or as guest**/

			/**add additional text to initial order form display (if not empty)**/
			add_action('wppizza_order_form_inside_top', array( $this, 'wppizza_do_additional_info_order_form'));/**add info**/

			/***reset loop query***/
			add_action('wppizza_loop_template_end', array( $this, 'wppizza_reset_loop_query'));/**needed by some themes **/

			/***alter search query if/when required**/
			add_filter( 'pre_get_posts', array( $this, 'wppizza_set_search_query' ));//filter search to include wppizza

			/**filter order items when returned from db as its all stored in a array**/
			add_filter('wppizza_filter_order_db_return', array( $this, 'wppizza_filter_order_db_return'),10,2);
			/****************************************************************
				[single menu items]
				if we are using a template (single-wppizza.php) or default
				we can ignore the below, otherwise we will have to rewrite
				some vars and disaply it in a chosen page.
				(in case we do not want to or are not capable to deal with templates, it's at least something)
			****************************************************************/
			if(isset($this->pluginOptions['plugin_data']['post_single_template']) && $this->pluginOptions['plugin_data']['post_single_template']>0){
				/**change te permalink of any wppizza menu item to use loop template for single item instead of having to create a different template*/
				add_filter('the_permalink', array( $this, 'wppizza_search_results_permalink'));
				/**change the loop query when dealing with single menu items**/
				add_filter('pre_get_posts', array( $this, 'wppizza_single_items'));/**use loop template to also display single items**/
			}
			/***allow quantities to be changed or order page***/
			if($this->pluginOptions['layout']['order_page_quantity_change']){
				/**filter output to add spinner/text input*/
				add_filter('wppizza_filter_order_item_markup', array( $this, 'wppizza_order_form_item_quantity_update'),10,3);
				/**add update button to page**/
				add_action('wppizza_order_form_last_item', array( $this, 'wppizza_order_form_item_quantity_update_button'));
			}

			/***
				if we want to be able to pass the currently selected category for this item to be able to group and sort items by category in emails etc
				we add a hidden field to the individual items.
			**/
			add_action('wppizza_loop_inside_after_content', array( $this, 'wppizza_add_category_hidden_field'),10,4);
			/**add category to permalink if necessary**/
			add_filter('wppizza_filter_loop_permalink', array($this,'wppizza_permalink_append_category'), 10, 4);
		}


		/************************************************************************
			[runs only for admin]
		*************************************************************************/
		if(is_admin()){
			/**check requirements*/
			add_action('admin_init', array( $this, 'wppizza_check_plugin_requirements'));/*check if we have the relevant php version etc**/
			add_action('admin_init', array( $this, 'wppizza_admin_options_init'));/*if necessary, add the db option table and fill with defaults**/
    		add_action('admin_menu', array( $this, 'register_admin_menu_pages' ) );
    		add_action('admin_init', array( $this, 'wppizza_admin_pages_init' ) );
    		add_action('admin_init', array( $this, 'wppizza_admin_metaboxes') );
			add_action('admin_init', array( $this, 'wppizza_do_admin_notice'));/*if necessary,show admin info screens**/
			add_action('admin_notices', array( $this, 'wppizza_dmarc_nag') );/*dmarc nag*/
			add_action('admin_notices', array( $this, 'wppizza_mysql_outdated_nag') );/*mysql is old nag*/

			/***enqueue backend scripts and styles***/
			add_action('admin_enqueue_scripts', array( $this, 'wppizza_register_scripts_and_styles_admin'));
			/*when deleting or creating categories*/
			add_filter('delete_'.WPPIZZA_TAXONOMY.'', array($this,'wppizza_save_sorted_custom_category'),10,3);
			add_action('create_'.WPPIZZA_TAXONOMY.'', array($this,'wppizza_save_sorted_custom_category'),10,2);//runs as ajax call
			add_action('edit_'.WPPIZZA_TAXONOMY.'', array($this,'wppizza_save_sorted_custom_category'),10,2);
			/*when saving custom post*/
			add_action('save_post', array( $this, 'wppizza_admin_save_metaboxes'), 10, 2 );
			/**sort menu item column in admin by name**/
			add_filter('request', array( $this, 'wppizza_items_sort') );

			/*add order/prices columns**/
			add_action('manage_edit-wppizza_columns', array( $this, 'wppizza_new_wppizza_column'));
			add_action('manage_wppizza_posts_custom_column',array( $this,'wppizza_show_order_column'), 10, 2 );
			add_filter('manage_edit-wppizza_sortable_columns',array( $this,'wppizza_order_column_register_sortable'));//currently not in use

			/*add prices to quickedit*/
			add_action('quick_edit_custom_box',  array( $this, 'wppizza_add_quick_edit'), 10, 2);
			add_action('admin_footer', array( $this, 'wppizza_quick_edit_js'));
			add_filter('post_row_actions', array( $this, 'wppizza_expand_quick_edit_link'), 10, 2);
			add_action('save_post',  array( $this, 'wppizza_save_quick_edit_data'), 10, 2);

			/*confirmation page add categories (if enabled) - as it's fetched by ajax, this must be is_admin() !! **/
			add_filter('wppizza_confirmation_form_filter_items', array( $this, 'wppizza_filter_items_by_category'),10,2);
			add_action('wppizza_confirmation_form_item', array( $this, 'wppizza_items_confirmation_form_print_category'),10,3);

			/**registration fields***/
			add_action( 'show_user_profile', array( $this, 'wppizza_user_info') );
			add_action( 'edit_user_profile', array( $this, 'wppizza_user_info') );
			add_action( 'personal_options_update', array( $this, 'wppizza_user_update_meta' ));
			add_action( 'edit_user_profile_update', array( $this, 'wppizza_user_update_meta' ));

			/**set capability to update options otherwise only a user with 'manage_options' could update anything. **/
			add_filter( 'option_page_capability_'.$this->pluginSlug.'', array($this, 'wppizza_admin_option_page_capability' ));

			/**allow custom order status fields. priority must be<10**/
			add_action( 'admin_init', array( $this, 'wppizza_set_order_status' ),9);

			/**reports**/
			add_action( 'admin_init', array( $this, 'wppizza_reports'));

			/**dashboard widget**/
			add_action( 'wp_dashboard_setup', array( $this, 'wppizza_dashboard_widget'));

			/**cron to delete old orders**/
			add_action( 'wppizza_cron', array( $this, 'wppizza_cron_do'));

			/*notification sound on new orders - not yet implemented/activated*/
		   	//add_action('admin_footer', array( $this, 'wppizza_notify_new_orders'));

			/*upgrade message**/
			add_action( 'in_plugin_update_message-wppizza/wppizza.php', array( $this, 'wppizza_in_plugin_update_message' ) );

		}


		/************************************************************************************************************************
			[sort by and print categories to order page, cart and history
		*************************************************************************************************************************/
		if(!is_admin()){
			/**registration fields if using themed profile***/
			add_action( 'show_user_profile', array( $this, 'wppizza_user_info') );
			add_action( 'personal_options_update', array( $this, 'wppizza_user_update_meta' ));

			/**filter and sort selected items by their categoryies in order page **/
			add_filter('wppizza_order_form_filter_items', array( $this, 'wppizza_filter_items_by_category'),10,2);
			add_filter('wppizza_orderhistory_filter_items', array( $this, 'wppizza_filter_items_by_category'),10,4);
			/**print category **/
			add_action('wppizza_order_form_item', array( $this, 'wppizza_items_order_form_print_category'));
			add_action('wppizza_orderhistory_item', array( $this, 'wppizza_items_show_order_print_category'));

			/******************order history******************/
			add_action('wppizza_get_orderhistory', array( $this, 'wppizza_get_orderhistory'),10,2);
			add_action('wppizza_history_after_orders', array( $this, 'wppizza_orderhistory_pagination'),10,3);
			add_filter('wppizza_filter_orderhistory_additional_info', array( $this, 'wppizza_filter_order_additional_info'),10,1);
			add_filter('wppizza_filter_orderhistory_items_html', array( $this, 'wppizza_filter_order_items_html'),10,2);
		}

		/****filter transaction id's******/
		add_filter( 'wppizza_filter_transaction_id', array( $this, 'wppizza_filter_transaction_id'),10,2);

		/************************************************************************
			[ajax]
		*************************************************************************/
		add_action('wp_ajax_wppizza_admin_json', array($this,'wppizza_admin_json') );
		add_action('wp_ajax_wppizza_json', array($this,'wppizza_json') );// non logged in users
		add_action('wp_ajax_nopriv_wppizza_json', array($this,'wppizza_json') );

      }


/*********************************************************

	Show plugin changes. Code adapted from W3 Total Cache.

*********************************************************/
	public static function wppizza_in_plugin_update_message($args) {
		$major_version='3';
		/*temp for testing*/
		//$args['new_version']='3';

		/**currently available wppizza version in wp repo**/
		$current_version_in_repo=$args['new_version'];

		/**ini upgrade string**/
		$upgrade_notice ='';

		/*
			only show if current version still < major update and new version >= major update
		*/
		if ( version_compare( WPPIZZA_VERSION, $major_version , '<' )  && version_compare( $current_version_in_repo, $major_version, '>=' ) ) {

			$transient_name = 'wppizza_upgrade_notice_' . $args['Version'];

			if ( false === ( $parsed_upgrade_notice = get_transient( $transient_name ) ) ) {
				/** get readme from WP repo **/
				$response = wp_safe_remote_get( 'https://plugins.svn.wordpress.org/wppizza/trunk/readme.txt' );// for testing use http://localhost/wp-content/plugins/wppizza/readme.txt

				if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) ) {
					$parsed_upgrade_notice = self::parse_update_notice( $response['body'] );
					set_transient( $transient_name, $parsed_upgrade_notice, DAY_IN_SECONDS );
				}
			}

			$upgrade_notice .= '<div class="wppizza_plugin_major_upgrade_notice">';
			$upgrade_notice .= '<b>UPGRADE NOTICE: WPPIZZA version '.$current_version_in_repo.' IS A MAJOR UPDATE.</b><br><br />';
			$upgrade_notice .=  nl2br(wp_kses_post( $parsed_upgrade_notice ));
			$upgrade_notice .= '</div>';
		}

		/*
			always show if current version still < major update
		*/
		if ( version_compare( WPPIZZA_VERSION, $major_version , '<' ) && version_compare( $current_version_in_repo, $major_version, '<' ) ) {
			$upgrade_notice .= '<div class="wppizza_plugin_advance_upgrade_notice">';
			$upgrade_notice .= '<b>(VERY:)) ADVANCE NOTICE - WPPIZZA v3.0+ :</b><br><br>Please note - although <b style="color:#666666">many months away yet</b> and <b style="color:#666666">not impacting the update available above</b> - there will be a major update to wppizza (version 3.0+) to make going forward and future development more manageable in addition to added functionalities etc.<br />';
			$upgrade_notice .= 'A beta version will be made available in good time before it gets released to the wordpress repo for it to show up as automatic update in your wordpress installation.<br />';
			$upgrade_notice .= 'To keep up to date, please follow the <a href="http://blog.wp-pizza.com/">development blog</a><br />';
			$upgrade_notice .= '</div>';
		}

	echo ( $upgrade_notice );
	}

	/**
	 * Parse update notice from readme file
	 * @param  string $content
	 * @return string
	 */
	private static function parse_update_notice( $content) {
		// Output Upgrade Notice
		$matches        = null;
		$regexp         = '~==\s*Upgrade Notice\s*==\s*=\s*(.*)\s*=(.*)(=\s* ' . preg_quote(WPPIZZA_VERSION) . '\s*=|$)~Uis';
		$upgrade_notice = '';
		if ( preg_match( $regexp, $content, $matches ) ) {
			$notices = (array) preg_split('~[\r\n]+~', trim( $matches[2] ) );
			foreach ( $notices as $index => $line ) {
				$upgrade_notice .= wp_kses_post( preg_replace( '~\[([^\]]*)\]\(([^\)]*)\)~', '<a href="${2}">${1}</a>', $line ) ).PHP_EOL;
			}
		}
		return wp_kses_post( $upgrade_notice );
	}


/*********************************************************
*
*	[allow others to hook into this]
*
*********************************************************/
	static function this() {
    	return self::$_this;
 	}
/*********************************************************************************
*
*	[filters and actions for order page: login, logout[currently omitted], register, continue as guest]
*
*********************************************************************************/
	/************************************************************************
		[order page add spinner/input to be able to update quantity (if enabled)]
	************************************************************************/
	function wppizza_order_form_item_quantity_update($markup,$item,$itemKey){
		$pos='right';
		/**disaply left**/
		if($this->pluginOptions['layout']['order_page_quantity_change_left']){
			$pos='left';
		}
		if($pos=='left'){
			$markup['quantity']='<input type="number" size="1" id="wppizza-qkey-'.($itemKey).'" class="wppizza-item-quantity" value="'.$item['count'].'" />';
		}else{
			$markup['price_total']='<span class="wppizza-quantity-wrap"><input type="number" size="1" id="wppizza-qkey-'.($itemKey).'" class="wppizza-item-quantity" value="'.$item['count'].'" /></span>'.$markup['price_total'];
		}
		return $markup;
	}
	/************************************************************************
		[order page add button to be able to update quantity (if enabled)]
	************************************************************************/
	function wppizza_order_form_item_quantity_update_button($item){
		print"<div class='wppizza-update-quantity'><input type='button' class='submit wppizza-update-order' value='".$this->pluginOptions['localization']['update_order']['lbl']."' /></div>";
	}

	/************************************************************************
		[output some additional info before order form details before submitting]
	************************************************************************/
	function wppizza_do_additional_info_order_form(){
		if($this->pluginOptions['localization']['order_ini_additional_info']['lbl']!=''){
			print"".$this->pluginOptions['localization']['order_ini_additional_info']['lbl']."";
		}
	}
	/************************************************************************
		[output login form or logout link on order page]
	************************************************************************/
	function wppizza_do_login_form($cart,$orderhistory=false){
		if(get_option('users_can_register')==0){return;}


		$txt=$this->pluginOptions['localization'];
		/**skip if we just want to show the login**/
		if($orderhistory){$items=1;}
		if(!$orderhistory){
			$items=count($cart['items']);
			/**logged in users - i dont think a logout link belongs there really. let's not do this for now**/
			if(is_user_logged_in() && (int)$items>0) {
				$html='';
			//	$html.='<div id="wppizza-user-logout"><a href="'.wp_logout_url( $_SERVER['REQUEST_URI'] ).'" title="'.__( 'Log Out' ).'">'.__( 'Log Out' ).'</a></div>';
				echo $html;
				return;
			}
			/**as someone might be using previous versions of the wppizza-order.php template copied to their theme
				it might not have that variable defined in the do_action, so let's assume a default of 1 as otherwsie the login would just never be displayed
				caveat being, that it also gets displayed when there's nothing in the cart (although it isn't necessarily a problem
				having a login option even if the cart is empty. just my personal preference not to have it.)
			**/
			if(!is_int($items)){$items=1;}
		}

		/**non logged in users**/
		if(!is_user_logged_in() && $items>0) {
			/**any login errors ?**/
			$error='';
			$style='style="display:none"';/*login hidden if not fail**/
			$styleAlt='style="display:block"';/*login hidden if not fail**/
			if(isset($_GET['fail'])){
				$style='style="display:block"';
				$styleAlt='style="display:none"';
				$wp_error = new WP_Error('authentication_failed', __('<strong>ERROR</strong>: Invalid username or incorrect password.'));/*native wp*/
				$errMsg = $wp_error->get_error_message();
				$error="<div class='wppizza-login-error'>".$errMsg."</div>";
			}

			/***html***/
			$html='';
			$html.='<a name="login"></a>';
			$html.='<div id="wppizza-user-login">';
				$html.='<div id="wppizza-user-login-option">';
					$html.='<span>'.$txt['loginout_have_account']['lbl'].'</span> <span><a href="javascript:void(0);" id="wppizza-login" '.$styleAlt.'>'. __( 'Log In' ).'</a></span>';
					$html.='<span><a href="javascript:void(0);" id="wppizza-login-cancel" '.$style.'>'. __( 'Cancel' ).'</a></span>';
				$html.='</div>';
				$html.='<div id="wppizza-user-login-action" '.$style.'>';
	            	$html.='<form id="wppizza-login-frm" action="'.wp_login_url().'" method="post">';
            			$html.='<span><div class="wppizza-unpw-lbl">'.__( 'Username' ).'</div><input type="text" name="log" id="user_login" class="input wppizza_user_login" size="15" value="" placeholder="'.__( 'Username' ).'" required="required" /></span>';
            			$html.='<span><div class="wppizza-unpw-lbl">'.__( 'Password' ).'</div><input type="password" name="pwd" id="user_pass" class="input wppizza_user_pass"  size="15" value="" placeholder="'.__( 'Password' ).'" required="required" /></span>';
                		$html.='<span><div class="wppizza-unpw-lbl">&nbsp;</div><input type="submit" value="'.__( 'Log In' ).'" id="wppizza_btn_login" /></span>';
                		$html.='<span class="wppizza-unpw-lostpw"><div class="wppizza-unpw-lbl"><a href="'.wp_lostpassword_url( get_permalink() ).'" title="'.__( 'Lost Password' ).'">'.__( 'Lost Password' ).'</a></div></span>';
                		$html.=''.wp_nonce_field( 'wppizza_nonce_login','wppizza_nonce_login',true,false).'';
            		$html.='</form>';
					$html.=$error;/**add errors if any**/
				$html.='</div>';
			$html.='</div>';
			echo $html;
			return;
		}
	}

	/************************************************************************
		[check login and redirect back to order page if failed]
	************************************************************************/
	function wppizza_authenticate( $username, $password){
		if (isset($_POST['wppizza_nonce_login'])){

			$chkLogin = apply_filters('authenticate', null, $username, $password);
			$setVars=false;
			$unsetVars=false;

			/**failed login**/
			if ( is_wp_error( $chkLogin ) ) {
				$errors = $chkLogin->get_error_codes();
				if(count($errors)>0){
					$setVars=array('fail'=>1);
				}
				$redirectUrl=$this->wppizza_set_redirect_url($_POST['_wp_http_referer'],$setVars,$unsetVars);
				wp_redirect( $redirectUrl);
				exit();
			}
		}
	}
	/************************************************************************
		[successful login from order page->redirect back to that page]
	************************************************************************/
	function wppizza_login_redirect( $redirect_to, $request, $user  ){
		/**check if we are coming from the order page**/
		if (isset($_POST['wppizza_nonce_login'])){
			/**verify nonce**/
			if (wp_verify_nonce($_POST['wppizza_nonce_login'],'wppizza_nonce_login') ){
				$redirectUrl=$this->wppizza_set_redirect_url($_POST['_wp_http_referer'],array(),array('fail'));
				return $redirectUrl;
			}else{
				return $redirect_to;
			}
		}
		/**redirect others as normal*/
		return $redirect_to;
	}
	/********************************************************************************
		[helper to set redirect location with ability to add or unset some query vars
	********************************************************************************/
	function wppizza_set_redirect_url( $location, $setVars=false,$unsetVars=false){
			/**
				check one day if one could/should be using this instead
				wp_redirect( add_query_arg('login', 'failed', $referrer) );
			*/

			/**split original into url and query vars**/
			$locUrl=explode('?',$location);
			/**make url**/
			$redirectLocation='';
			$redirectLocation.=$locUrl[0];/*get url before get vars*/
			/**make query variables**/

			if(isset($locUrl[1]) && $locUrl[1]!=''){// && count($locUrl[1])>0
				parse_str($locUrl[1],$qString);
			}else{
				$qString=array();
			}
			/**add vars if set**/
			if($setVars && is_array($setVars) && count($setVars)>0){
				foreach($setVars as $qVarsKey=>$qVarsVal){
					$qString[$qVarsKey]=$qVarsVal;
				}
			}
			/**remove vars if set**/
			if($unsetVars && is_array($unsetVars) && count($unsetVars)>0){
				foreach($unsetVars as $qVarsKey){
					if(isset($qString[$qVarsKey])){
						unset($qString[$qVarsKey]);
					}
				}
			}
			/**add query variables if any**/
			if(count($qString)>0){
				$redirectLocation.='?'.http_build_query($qString,'','&');
			}

		return $redirectLocation;
	}
	/********************************************************************************
		[output div with radio options to continue as guest or simultaneous registration]
	********************************************************************************/

	function wppizza_create_user_option(){
		if(!is_user_logged_in() && get_option('users_can_register')==1) {
			$formelements=$this->pluginOptions['order_form'];
			/***********************************************************
				check if we have the email set to enabled and required
				as otherwise new registration on order will not work
				as there's noweher to send the password
			*************************************************************/
			$emailSet=false;
			foreach($formelements as $k=>$oForm){
				if($oForm['key']=='cemail' && $oForm['enabled']  && $oForm['required']){
					$emailSet=true;
					break;
				}
			}
			if($emailSet){
				$txt=$this->pluginOptions['localization'];

				$html='';
				$html.='<div id="wppizza-create-account">';
					$html.='<span>'.$txt['register_option_label']['lbl'].'</span>';
					$html.='<label><input type="radio" id="wppizza_account" name="wppizza_account" value="guest" checked="checked" />'.$txt['register_option_guest']['lbl'].'</label>';
					$html.='<label><input type="radio" id="wppizza_account" name="wppizza_account" value="register" />'.$txt['register_option_create_account']['lbl'].'</label>';
				$html.='</div>';

		        $html.='<div id="wppizza-user-register-info">';
	            	$html.=''.$txt['register_option_create_account_info']['lbl'].'';
				$html.='</div>';
				echo $html;
			}
		}
	}

/*********************************************************************************
*
*	[filter: wppizza custom fields]
*
*********************************************************************************/
	/***profile page***/
	function wppizza_user_info($user){
		$userMetaData=get_user_meta( $user->ID );
		$ff=$this->pluginOptions['order_form'];
	    /**allow filtering of order form form elements**/
		$ff = apply_filters('wppizza_filter_formfields_profile', $ff);
		asort($ff);
		if($this->pluginOptions['localization']['user_profile_label_additional_info']['lbl']!=''){
			print"<h3> ".$this->pluginOptions['localization']['user_profile_label_additional_info']['lbl']."</h3>";
		}
		print"<table class='form-table'>";
			foreach( $ff as $field ) {

				/**lets exclude disabled and "email" as wp already has this of course, as well as gratuities**/
				if(!empty($field['enabled']) && $field['type']!='email' && $field['type']!='tips'){
				$selectedValue=!empty($userMetaData['wppizza_'.$field['key'].''][0]) ? esc_attr($userMetaData['wppizza_'.$field['key'].''][0]) : '';

				print"<tr><th><label for='wppizza_".$field['key']."'>".$field['lbl']."</label></th><td>";

					/**normal text input**/
					if ( $field['type']=='text'){
			    		print'<input type="text" name="wppizza_'.$field['key'].'" id="wppizza_'.$field['key'].'" value="'.$selectedValue.'" class="regular-text" />';
					}
					/**textareas**/
					if ( $field['type']=='textarea'){
						print'<textarea name="wppizza_'.$field['key'].'" id="wppizza_'.$field['key'].'" rows="5" cols="30">'.$selectedValue.'</textarea>';
					}
					/**select**/
					if ( $field['type']=='select'){
						print'<select name="wppizza_'.$field['key'].'" id="wppizza_'.$field['key'].'">';
							print'<option value="">--------</option>';
							foreach($field['value'] as $key=>$value){
							print'<option value="'.$key.'" '.selected($key,$selectedValue,false).'>'.$value.'</option>';
							}
						print'</select>';
					}
				print"</td></tr>";
			}}
		print"</table>";
	}
	/**update profile**/
	function wppizza_user_update_meta($user_id){
		if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }
	    $ff=$this->pluginOptions['order_form'];
	    $ff = apply_filters('wppizza_filter_user_update_meta', $ff);
		foreach( $ff as $field ) {
		if(!empty($field['enabled']) && $field['key']!='cemail') {
			$sanitizeInput=wppizza_validate_string($_POST['wppizza_'.$field['key']]);
			update_user_meta( $user_id, 'wppizza_'.$field['key'], $sanitizeInput );
		}}
		/**distinctly add email from wp email field**/
		$sanitizeEmail=wppizza_validate_string($_POST['email']);
		update_user_meta( $user_id, 'wppizza_cemail', $sanitizeEmail );
	}
	/***show selected fields on registration page***/
	function wppizza_user_register_form_display_fields(){

	    $ff=$this->pluginOptions['order_form'];
	    /**allow filtering of order form form elements**/
		$ff = apply_filters('wppizza_filter_formfields_register', $ff);
		asort($ff);
	    foreach( $ff as $field ) {
	    	if(!empty($field['enabled']) && !empty($field['onregister'])) {
	    	if( isset( $_POST[ 'wppizza_'.$field['key'] ] ) ) { $field_value = stripslashes(wppizza_validate_string($_POST['wppizza_'.$field['key'] ])); } else { $field_value=''; }
	 		echo"<p>";
	 			echo"<label for='wppizza_".$field['key']."'>".$field['lbl']."<br />";
	 			if ( $field['type']=='text'){
	 				echo"<input type='text' name='wppizza_".$field['key']."' id='wppizza_".$field['key']."' class='input' value='". $field_value."' size='20' /></label>";
	 			}
				/**textareas**/
				if ( $field['type']=='textarea'){
					print'<textarea  class="input" name="wppizza_'.$field['key'].'" id="wppizza_'.$field['key'].'" rows="5" cols="30">'.$field_value.'</textarea>';
				}
				/**select**/
				if ( $field['type']=='select'){
					print'<select name="wppizza_'.$field['key'].'" id="wppizza_'.$field['key'].'" class="input">';
						print'<option value="">--------</option>';
						foreach($field['value'] as $key=>$value){
							print'<option value="'.$key.'" '.selected($key,$field_value,false).'>'.$value.'</option>';
						}
					print'</select>';
				}
	 		echo"</p>";
	    	}
	    }
	}
	function wppizza_user_register_form_save_fields( $user_id, $password = '', $meta = array() ){
	    $userdata       = array();
		$userdata['ID'] = $user_id;

	    $ff=$this->pluginOptions['order_form'];
	    $ff = apply_filters('wppizza_filter_formfields_register_save', $ff);
		asort($ff);
	    foreach( $ff as $field ) {
	    if(!empty($field['enabled']) && !empty($field['onregister']) && isset($_POST['wppizza_'.$field['key']])) {
	    		$sanitizeInput=wppizza_validate_string($_POST['wppizza_'.$field['key']]);
				update_user_meta( $user_id, 'wppizza_'.$field['key'], $sanitizeInput );
		}}
		/**distinctly add email from wp email field**/
		if(isset($_POST['user_email'])){
			$sanitizeEmail=wppizza_validate_string($_POST['user_email']);
			update_user_meta( $user_id, 'wppizza_cemail', $sanitizeEmail );
		}

	 $new_user_id = wp_update_user( $userdata );
	}

	/****update/add user meta when registering via "order page" **/
	function wppizza_user_register_order_page( $user_id, $password = '', $meta = array() ){
	    $ff=$this->pluginOptions['order_form'];
	    $ff = apply_filters('wppizza_filter_formfields_register_save_onorder', $ff);
	    foreach( $ff as $field ) {
	    if(!empty($field['enabled']) && $field['type']!='cemail' && $field['type']!='tips') {
	    		/**selects should be stored by index**/
	    		if($field['type']=='select'){
	    			$sanitizeInput=array_search($_POST[$field['key']], $field['value']);
	    		}else{
	    		$sanitizeInput=wppizza_validate_string($_POST[$field['key']]);
	    		}
				update_user_meta( $user_id, 'wppizza_'.$field['key'], $sanitizeInput );
		}}
		/**turn off admin bar by default**/
		update_user_meta( $user_id, 'show_admin_bar_front', 'false' );
	}

	/*multisite push meta to user*/
	function wppizza_ms_user_register_form_save_fields( $user_id, $password = '', $meta = array() ){
		foreach($meta as $k=>$v){
			update_user_meta( $user_id, $k, $v );
		}
	}

	/*multisite signup add meta*/
	function wppizza_ms_user_register_add_signup_meta($meta){
	    $ff=$this->pluginOptions['order_form'];
	    $ff = apply_filters('wppizza_filter_formfields_register_save', $ff);
	    foreach( $ff as $field ) {
	    if(!empty($field['enabled']) && !empty($field['onregister']) && isset($_POST['wppizza_'.$field['key']])) {
	    	/**selects should be stored by index**/
	    	if($field['type']=='select'){
	    		$posted=$_POST['wppizza_'.$field['key']];
	    		$sanitizeInput = isset($field['value'][$posted]) ? $posted : null;
	    	}else{
	    		$sanitizeInput=wppizza_validate_string($_POST['wppizza_'.$field['key']]);
	    	}

			$meta['wppizza_'.$field['key']]=$sanitizeInput;
		}}

	return $meta;
	}
/***********************************************************************************************
*
*
*	[check requirements, start session, initialize options, register custom post type, metaboxes]
*
*
***********************************************************************************************/
	/********************************************************
		[PHP 5.2 (json_decode) required ,
		so if PHP version is lower then 5.2,
		display an error message and deactivate the plugin]
	********************************************************/
	function wppizza_check_plugin_requirements(){
		if(!extension_loaded('mbstring')) {
			add_action('admin_notices', array( $this, 'wppizza_required_notice_extensions') );
		}

		if( version_compare( PHP_VERSION, '5.2', '<' )) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
			deactivate_plugins($this->pluginPath);
			wp_die(  __('WPPizza requires the server on which your site resides to be running PHP 5.2 or higher. As of version 3.2, WordPress itself will also <a href="http://wordpress.org/news/2010/07/eol-for-php4-and-mysql4">have this requirement</a>. You should get in touch with your web hosting provider and ask them to update PHP.<br /><br /><a href="' . admin_url( 'plugins.php' ) . '">Back to Plugins</a>', 'wppizza-locale') );
		}
	}
	function wppizza_required_notice_extensions(){
			echo'<div id="message" class="error wppizza_admin_notice" style="padding:20px;">';
			 echo"".__('ATTENTION:', 'wppizza-locale')."<br/><br/>";
			 echo"".__('WPPizza requires the <b>"mbstring"</b> extension in your php installation to be available/installed to work reliably. Please enable this module. If you do not know how to, speak to your hostinng provider', 'wppizza-locale');
			 echo"<br/>".__('This notice will disappear as soon as mbstring is available.', 'wppizza-locale');
			echo"</div>";
	}
	/*******************************************************
		[insert options and defaults on first install]
	******************************************************/
	public function wppizza_admin_options_init(){
		$options = $this->pluginOptions;
		if($options==0){/*no options db entry->do stuff*/
			$install_options=1;
			/****set nag notice to 1 as its first install*******/
			$this->pluginNagNotice=1;
			/**include and insert default options***/
			require_once(WPPIZZA_PATH .'inc/admin.setup.default.options.inc.php');
			/*insert $options;*/
			update_option($this->pluginSlug, $defaultOptions );
			/*create order table*/
			require_once(WPPIZZA_PATH .'inc/admin.create.order.table.inc.php');
		}else{
			/****************************************************************************************
				@forceUpdate
				[in case we want  to force update without upgrading version, uncomment below
				 - DEVELOPMENT PURPOSES ONLY when adding/deleting default options.
			**************************************************************************************/
			//$forceUpdate=1;
			//$install_options=1;
			/**update  options if installed version < current version***/
			if( version_compare( $options['plugin_data']['version'], 	$this->pluginVersion, '<' ) || isset($forceUpdate)) {
				$plugin_update=true;

				/**get default options. do not use require_once, as we need this more than once ***/
				require(WPPIZZA_PATH .'inc/admin.setup.default.options.inc.php');

				/**compare table options against default options and delete/add as required***/
				require_once(WPPIZZA_PATH .'inc/admin.update.options.inc.php');

				/**compare currently installed options vs this version**/
				if(	version_compare( $options['plugin_data']['version'], '2.0', '<' )){$update_options['plugin_data']['nag_notice']='2.1';}
				if(	version_compare( $options['plugin_data']['version'], '2.8.7', '<' )){$update_options['plugin_data']['nag_notice']='2.8.7';}
				/**check for child themes**/
				if(	version_compare( $options['plugin_data']['version'], '2.8.9.7', '<' ) && get_stylesheet_directory()!=get_template_directory()){$update_options['plugin_data']['nag_notice']='2.8.9.7';}
				/***** admin order print by template now******/
				if(	version_compare( $options['plugin_data']['version'], '2.11.6', '<' )){$update_options['plugin_data']['nag_notice']='2.11.6';}

				/**update options**/
				update_option($this->pluginSlug, $update_options );

				/*update order table*/
				require_once(WPPIZZA_PATH .'inc/admin.create.order.table.inc.php');

				/*(re)-register wpml strings*/
				require(WPPIZZA_PATH .'inc/wpml.register.strings.php');

				/*reset session when updating plugin*/
				$this->wppizza_clear_session();
			}
		}
	}
	/************************************************************************************
		[clear session data - used when updating plugin]
	************************************************************************************/
	function wppizza_clear_session(){
		if(isset($_SESSION[$this->pluginSessionGlobal])){
			unset($_SESSION[$this->pluginSessionGlobal]);
		}
		if(isset($_SESSION[$this->pluginSession])){
			unset($_SESSION[$this->pluginSession]);
		}
	}

	/************************************************************************************
		[DMARCE NAG - ALWAYS LEAVE THIS]
	************************************************************************************/
    function wppizza_dmarc_nag() {
    	global $current_screen;
		if(!$this->pluginOptions['order']['dmarc_nag_off'] && empty($_POST) && $current_screen->post_type == WPPIZZA_POST_TYPE){
			/*get domain*/
			$urlobj=parse_url(get_site_url());
			$domain=$urlobj['host'];
			if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
    			$domain=$regs['domain'];
  			}
			/*check if in static from email*/
			$staticFromEmail=$this->pluginOptions['order']['order_email_from'];
			$pos = strpos($staticFromEmail, $domain);
			if ($pos === false) {
				/*dmarc nag**/
				print'<div id="message" class="error"><b>
					WPPIZZA - EMAIL DMARC email policies:<br /><br />
					Due to policy changes by yahoo (others may follow suit) it is <span style="color:red">strongly advised to set a static "from email address"</span> in wppizza->order settings ->set static from email address, that corrosponds to your domain name.<br />
					as your domain appears to be "'.$domain.'" you should <span style="color:red">set an email address like "abc@'.$domain.'"</span><br />
					<span style="color:red">if you do NOT do this, some emails might NOT get delivered to you or your customers</span> as they would violate DMARC policies (search on google for "DMARC" if you would like to find out more)<br /><br />
					this notice will remain until acted upon or you forcefully switch it off in wppizza->order settings -> next to the "static from address" settings.<br /><br />
					Thank you
				</b></div>';
			}
		}
  	}
	/************************************************************************************
		[MYSQL IS OLD NAG]
	************************************************************************************/
	function wppizza_mysql_outdated_nag (){
  		global $current_screen;
  		if($current_screen->post_type == WPPIZZA_POST_TYPE){

	  		/**check mysql version */
  			if(empty($this->pluginOptions['plugin_data']['mysql_version_ok'])){

  				/**get options**/
				$options = $this->pluginOptions;

  				$mysql_info=wppizza_get_mysql_version();
  				if( !empty($mysql_info['version']) && version_compare( $mysql_info['version'], ''.$this->pluginMinMysqlVersionRequired.'', '<' )) {

  					$options['plugin_data']['mysql_version_ok']=false;

					print'<div  id="message" class="error" style="padding:10px;">';
						print'<b>WPPizza requires mysql version '.$this->pluginMinMysqlVersionRequired.'+ to work ! Please update your version of mysql.</b><br />';
						print'See <a href="https://wordpress.org/plugins/wppizza/" target="_blank">https://wordpress.org/plugins/wppizza/</a> for requirements';
						print'<br /><br />';
						print'<b>After you have upgraded mysql, go to WPPizza->Tools, check "Update order table" and save once to update the wppizza database table</b>';
					print'</div>';

  				}else{
  					$options['plugin_data']['mysql_version_ok']=true;//will also be true if we cannot determine mysql version
  				}
  				update_option($this->pluginSlug, $options );
  			}
  		}
	}
	/************************************************************************************
		[admin notices: show and dismiss]
	************************************************************************************/
    function wppizza_do_admin_notice() {/*check if we need to show any notices i.e when set to 1 or on first install*/
		if($this->pluginOptions['plugin_data']['nag_notice']!=0 || $this->pluginOptions==0){
			add_action('admin_notices', array( $this, 'wppizza_install_notice') );
			add_action('admin_head', array($this, 'wppizza_dismiss_notice_js') );
			add_action('wp_ajax_wppizza_dismiss_notice', array($this, 'wppizza_dismiss_notice'));
    	}
  	}

	/* plugin admin screen notices/nags */
    function wppizza_install_notice() {
			/**get url to info screens**/
   			$pluginInfoInstallationUrl = admin_url( 'plugin-install.php?tab=plugin-information&plugin='.WPPIZZA_SLUG.'&section=installation&TB_iframe=true&width=600&height=800');
			$pluginInfoFaqUrl = admin_url( 'plugin-install.php?tab=plugin-information&plugin='.WPPIZZA_SLUG.'&section=faq&TB_iframe=true&width=600&height=800');

			$pluginUpdatedNotice='';
			$pluginUpdatedNotice.='<div id="message" class="updated wppizza_admin_notice" style="padding:20px;">';
			/*set text depending on notice number*/
			if($this->pluginOptions['plugin_data']['nag_notice']=='1' || $this->pluginNagNotice==1){
				$pluginUpdatedNotice.='<b>'.$this->pluginName.' Installed</b><br/><br/>';
				$pluginUpdatedNotice.='Thank you for installing '.WPPIZZA_NAME.' <br/>';
				$pluginUpdatedNotice.='Please make sure to read the <a href="'.$pluginInfoInstallationUrl.'" class="thickbox">"Installation Instructions"</a> and <a href="'.$pluginInfoFaqUrl.'" class="thickbox">"FAQ"</a> ';
				$pluginUpdatedNotice.='<br/>';
			}

			if($this->pluginOptions['plugin_data']['nag_notice']=='2.1'){
				$pluginUpdatedNotice.='<b>Update Notice '.WPPIZZA_NAME.' '.$this->pluginVersion.':</b><br/><br/>';
				$pluginUpdatedNotice.='To aid the further development of new options and extensions now and in the future as well as being able to take advantage of various new options added to this plugin, the way the plugin handles various things was changed quite significantly.';
				$pluginUpdatedNotice.='<br/><br/>';
				$pluginUpdatedNotice.='<b>IMPORTANT: if you have customised and copied any of the following templates/files to your theme directory, read on below, as you MIGHT have to update them !!!</b>';
				$pluginUpdatedNotice.='<br/><span style="color:red"><b>(IF you are not using any customised files you should be just fine and can ignore all of this)</b></span>';
				$pluginUpdatedNotice.='<br/><blockquote>templates/wppizza-order.php<br/>templates/wppizza-phpmailer-settings.php<br/>templates/wppizza-cart.php<br/>templates/wppizza-order-html-email.php<br/>css/wppizza-default.css</blockquote><br/>';
				$pluginUpdatedNotice.='<span style="color:red"><b>IF</b></span> you are using customised versions of the following files (i.e you have copied any of these files to your theme directory) <b>you will have to</b> update them as marked below (changes are marked in the relevant files).';
				$pluginUpdatedNotice.='<br/><br/>';
				$pluginUpdatedNotice.='<b>Affected Files and priorty of having to change your customised versions</b> <span style="color:red">(only if you are using customised versions of these files in your theme directory of course)</span>: ';
				$pluginUpdatedNotice.='<blockquote>';
					$pluginUpdatedNotice.='<b>templates/wppizza-order.php</b> [UPDATE REQUIRED]:<br/>you <b>MUST</b> update any customised version (if used). Changes in this file are marked with "NEW IN VERSION" ';
						$pluginUpdatedNotice.='<br/><br/>';
					$pluginUpdatedNotice.='<b>templates/wppizza-phpmailer-settings.php</b> [UPDATE REQUIRED IF USING PHPMAILER]:<br/><b>IF</b> you are using a customised version of this <b>AND</b> are using phpmailer to send the emails this plugin generates, you MUST update your customised version according to the file find in the templates/ directory ';
				$pluginUpdatedNotice.='<br/><br/>';
					$pluginUpdatedNotice.='<b>templates/wppizza-cart.php</b> [OPTIONAL].<br/><b>However</b>, if you want to take advantage of charging tax on items or enable self pickup (with or without offering discounts) etc , you\'ll have to update your customised version. (search for "CONDITIONAL ADDED/CHANGED" in the file to find any changes made)';
				$pluginUpdatedNotice.='<br/><br/>';
					$pluginUpdatedNotice.='<b>templates/wppizza-order-html-email.php.</b>[OPTIONAL].<br/><b>However</b>, if you want to take advantage of charging tax on items or enable self pickup (with or without offering discounts) etc , you\'ll have to update your customised version. (search for "NEW IN VERSION " to see changes)';
				$pluginUpdatedNotice.='<br/><br/>';
					$pluginUpdatedNotice.='<b>css/wppizza-default.css</b><br/>[not strictly required, <b>but probably a good idea</b>]. changes are marked with "NEW IN VERSION"';
				$pluginUpdatedNotice.='</blockquote>';
				$pluginUpdatedNotice.='with regards to all of the above, I would recommend you first make a backup of you customisation so you can refer to it';
				$pluginUpdatedNotice.='<br/><br/>';
				$pluginUpdatedNotice.='<b>Sorry about all this. Just had to be done. I hope the above is clear, but if you have any questions mail me at dev[at]wp-pizza.com</b>';
				$pluginUpdatedNotice.='<br/>';
			}

			if($this->pluginOptions['plugin_data']['nag_notice']=='2.8.7'){
				$pluginUpdatedNotice.='<b>Update Notice '.WPPIZZA_NAME.' '.$this->pluginVersion.':</b><br/><br/>';
				$pluginUpdatedNotice.='If you are using the "Cart visible on page when scrolling ?" in the wppizza shoppingcart widget (or per shortcode) please check your settings (notably the background colour) in WPPizza->Layout-> "sticky/scolling" cart settings [if used]" ';
				$pluginUpdatedNotice.='<br/><br/>thank you<br/>';
			}
			if($this->pluginOptions['plugin_data']['nag_notice']=='2.8.9.7'){
				$pluginUpdatedNotice.='<b>Update Notice '.WPPIZZA_NAME.' '.$this->pluginVersion.' - you appear to be using a child theme:</b><br/><br/>';
				$pluginUpdatedNotice.='<br />';
				$pluginUpdatedNotice.='<b>Previous Versions - erroneously - only checked for and used customised wppizza templates that have been copied into the parent theme directory.<br />Therefore, <span style="color:red">if you are using any customised wppizza templates, please move them from your parent to your child theme.</span></b><br />';
				$pluginUpdatedNotice.='<br /><b>Note: the above <b>does not yet apply</b> to customised files/css of other extensions which will be updated shortly to keep things consistent.</b>';
				$pluginUpdatedNotice.='<br/><br/>thank you<br/>';
			}

			if($this->pluginOptions['plugin_data']['nag_notice']=='2.11.6'){
				$pluginUpdatedNotice.='<b>Update Notice '.WPPIZZA_NAME.' '.$this->pluginVersion.'+ </b><b style="color:red">Wppizza->Order History : Order Printing:</b>';
				$pluginUpdatedNotice.='<br />';
				$pluginUpdatedNotice.='<br /><b>Please note: the way the printing of orders is handled in wppizza->order history has been completely re-written and has - finally - had it\'s own template added instead of having to mess around with the plaintext email templates</b>';
				$pluginUpdatedNotice.='<br /><b>For the next few weeks, while getting feedback on this, it will not yet be editable and the actions/filters within will most likely change. Therefore any feedback - good or bad - as to the way this is handled by your printer would be appreciated. (ideally with a photo of your printouts even if possible)</b>';
				$pluginUpdatedNotice.='<br /><br /><b style="color:red">If you wish to revert to the previous way orders were printed (because you already used some filters/actions/edits to make this work for your particular scenario, you will find a checkbox in wppizza->settings which will allow you to do so</b>';
				$pluginUpdatedNotice.='<br /><br /><b>You can safely ignore this notice if you have no need to print your orders from the order history screen</b>';

				$pluginUpdatedNotice.='<br /><br /><b>As ever - if there are any questions - please let me know</b>';

				$pluginUpdatedNotice.='<br/><br/>thank you<br/>';
			}

			$pluginUpdatedNotice= $pluginUpdatedNotice;
			$pluginUpdatedNotice.='<br/><a href="#" onclick="wppizza_dismiss_notice(); return false;" class="button-primary">dismiss</a>';
			$pluginUpdatedNotice.='</div>';


			/*output*/
			print"".$pluginUpdatedNotice."";
    }
    function wppizza_dismiss_notice_js () {
        $js="";
        $js.="<script type='text/javascript' >".PHP_EOL."";
        $js.="jQuery(document).ready(function($) {".PHP_EOL."";
            $js.="wppizza_dismiss_notice = function () {".PHP_EOL."";
	        	$js.="var data = {action: 'wppizza_dismiss_notice'};".PHP_EOL."";
	        	// since wp2.8 ajaxurl is defined in admin header pointing to admin-ajax.php
	        	$js.="jQuery.post(ajaxurl, data, function(response) {".PHP_EOL."";
			        $js.="$('.wppizza_admin_notice').hide('slow');".PHP_EOL."";
	        	$js.="});".PHP_EOL."";
	        $js.="};".PHP_EOL."";
        $js.="});".PHP_EOL."";
        $js.="</script>".PHP_EOL."";
        print"".$js;
    }
    public function wppizza_dismiss_notice() {
    	$options = $this->pluginOptions;
    	$options['plugin_data']['nag_notice']=0;
    	update_option($this->pluginSlug, $options);
        die();
    }

	/*****************************************************
 	 [sort admin column by title]
 	*****************************************************/
	function wppizza_items_sort( $request ) {
		if(isset ($request['post_type']) && $request['post_type']==''.WPPIZZA_POST_TYPE.''){
			if ( !isset( $request['orderby'] ) || ( isset( $request['orderby'] ) &&  $request['orderby']=='title' ) ) {
				$request = array_merge( $request, array('orderby' => 'title'));
			}
			if ( !isset( $request['order'] )) {
				$request = array_merge( $request, array('order' => 'asc'));
			}
		}
	return $request;
	}
	/*******************************************************
		[show order column in wppizza menu table]
	******************************************************/
	function wppizza_new_wppizza_column($header_text_columns) {
		$header_text_columns['wppizza-prices'] = __('Prices', 'wppizza-locale');

		//ignore for now
		//$header_text_columns['wppizza-menu_order'] = __('Order', 'wppizza-locale');

  		return $header_text_columns;
	}
	/**
	* show custom order column values
	*/
	function wppizza_show_order_column($name, $id){

	  switch($name){

		//ignore for now
		//case 'wppizza-menu_order':
	    // 	$order = $post->menu_order;
	    // 	echo $order;
		//break;

		case 'wppizza-prices':
	     	$meta=get_post_meta($id, WPPIZZA_POST_TYPE, true );
	     	$sizes=$this->pluginOptions['sizes'][$meta['sizes']];
	     	$str='';
	     	if(is_array($sizes)){
	     		/*do not use tables here or bulk edit won't work - no , dunno either why*/
	      		$str.='<div class="wppizza-prices-column">';
	      		foreach($sizes as $k=>$s){
	      			$str.='<span>'.$s['lbl'].'<br />'.wppizza_output_format_price($meta['prices'][$k],$this->pluginOptions['layout']['hide_decimals']).'</span>';
	      		}
	      		$str.='</div>';
     		}
			echo $str;
		break;
		default:
		break;
	   }
	}
	/**
	* 	make column sortable /  not in use / ignored
	*	ignore for now
	*/
	function wppizza_order_column_register_sortable($columns){
	  //$columns['wppizza-menu_order'] = 'menu_order';
	  //$columns['prices'] = 'prices';
	  return $columns;
	}

	/*****************************************************
	*
	*	[quickedit prices]
	*
	*****************************************************/
	/*add element to quickedit*/
	function wppizza_add_quick_edit($column, $post_type) {
		if ($column != 'wppizza-prices' || $post_type!=WPPIZZA_POST_TYPE ){ return;}

		/*do we need this ?*/
    	//static $printNonce = TRUE;
    	//if ( $printNonce ) {
        //	$printNonce = FALSE;
        //	wp_nonce_field( plugin_basename( __FILE__ ), 'wppizza_edit_nonce' );
    	//}
		echo'<fieldset class="inline-edit-col-right inline-edit-wppizza-prices" style="width:auto;border:1px dotted #cecece;margin:5px">';
			echo'<div class="inline-edit-col column-'.$column.'">';
				echo'<div style="font-weight:600;text-align:center;text-decoration:underline">'.__('Item Price(s)', 'wppizza-locale').'</div>';
				echo'<div id="wppizza_quickedit_prices"></div>';
			echo'</div>';
		echo'</fieldset>';

	}
	/*set js to insert values*/
	function wppizza_quick_edit_js() {
	    global $current_screen;
	    if (($current_screen->post_type != WPPIZZA_POST_TYPE) || $current_screen->parent_base!='edit') {return;}
		echo'<script type="text/javascript">'.PHP_EOL;
		echo'function wppizza_set_prices(sizes, prices, labels, nonce) {'.PHP_EOL;
		        // refresh the quick menu properly
		        echo'inlineEditPost.revert();'.PHP_EOL;
		        echo'var itemSizes= sizes.split(":");'.PHP_EOL;
		        echo'var itemPrices= prices.split(":");'.PHP_EOL;
		        echo'var itemLabels= labels.split(":");'.PHP_EOL;

		        echo'var doInputs="<div class=\'wppizza-prices-column\'>";'.PHP_EOL;
		        echo'for(var i=0;i<itemSizes.length;i++){'.PHP_EOL;
		       	echo'doInputs+=\'<span>\'+itemLabels[i]+\'<br /><input type="text" name="wppizza[prices][\'+itemSizes[i]+\']" size="5" value="\'+itemPrices[i]+\'" /></span>\';'.PHP_EOL;
		        echo'}';

		        echo'doInputs+="</div>";'.PHP_EOL;
		        echo'jQuery("#wppizza_quickedit_prices").html(doInputs);'.PHP_EOL;
		echo'}'.PHP_EOL;
		echo'</script>'.PHP_EOL;
	}

	function wppizza_expand_quick_edit_link($actions, $post) {

	    if ($post->post_type != WPPIZZA_POST_TYPE) {return $actions;}
	    /*do we need this ?*/
	    //$nonce = wp_create_nonce( 'wppizza_'.$post->ID);

		$getMeta=get_post_meta($post->ID, WPPIZZA_POST_TYPE, true );
		$getSizes=$this->pluginOptions['sizes'][$getMeta['sizes']];

		$sizes=array();
		$prices=array();
		$labels=array();
		foreach($getSizes as $k=>$s){
			$sizes[$k]=$k;
			$prices[$k]=wppizza_output_format_price($getMeta['prices'][$k],$this->pluginOptions['layout']['hide_decimals']);
			$labels[$k]=$s['lbl'];
		}
		$jsSizes=implode(':',$sizes);
		$jsPrices=implode(':',$prices);
		$jsLabels=implode(':',$labels);

	    /**hijack quick edit link*/
	    $actions['inline hide-if-no-js'] = '<a href="#" class="editinline" title="';
	    $actions['inline hide-if-no-js'] .= esc_attr( __( 'Edit this item inline' ) ) . '"';
	    $actions['inline hide-if-no-js'] .= " onclick=\"wppizza_set_prices('".$jsSizes."','".$jsPrices."','".$jsLabels."')\" >";
	    $actions['inline hide-if-no-js'] .= __( 'Quick Edit' );
	    $actions['inline hide-if-no-js'] .= '</a>';

	    return $actions;
	}

	function wppizza_save_quick_edit_data($post_id, $post){
	 // verify if this is an auto save routine.
	  if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {return $post_id;}
	  // Check permissions
	  if ( isset($_POST['post_type'])  && WPPIZZA_POST_TYPE == $_POST['post_type'] ) {
	    if ( !current_user_can( 'edit_page', $post_id ) )
	      return $post_id;
	  } else {
	    if ( !current_user_can( 'edit_post', $post_id ) )
	    return $post_id;
	  }
	  // Authentication passed now we save the data
	  if (!empty($_POST['wppizza']['prices']) && is_array($_POST['wppizza']['prices']) && ($post->post_type != 'revision')) {
		/**current meta*/
		$metaValues=get_post_meta($post_id, WPPIZZA_POST_TYPE, true );
		/**get new prices*/
		$metaValues['prices']=array();
		foreach($_POST['wppizza']['prices'] as $k=>$price){
			$metaValues['prices'][$k]=wppizza_validate_float_only($price,2);;
		}
		update_post_meta($post_id, WPPIZZA_POST_TYPE, $metaValues);

		return $metaValues['prices'];
	  }

	return	;
	}
	/*****************************************************
		[save sortorder when creating or deleting, categories]
	*****************************************************/
	function wppizza_save_sorted_custom_category( $column, $term_id, $term_obj=null ) {
	static $run_once=0;
	if($run_once>0){return;}

		/******************************************
			bypass when activating plugin as we are installing
			already sorted default items via wp_insert_post()
		********************************************/
		if(!isset($_GET['activate'])){

			$newSort['layout']['category_sort']=$this->pluginOptions['layout']['category_sort'];/*legacy perhaps in use in other plugins*/
			$newSort['layout']['category_sort_hierarchy']=$this->pluginOptions['layout']['category_sort_hierarchy'];


			/************************************************************************
			*
			*	we are adding,
			*	reorder as required
			*
			**************************************************************************/
			if(!isset($newSort['layout']['category_sort_hierarchy'][$column]) && !empty($_POST['action']) && $_POST['action']=='add-tag'  ){

				/********************************************************
				*	adding new category or sub category
				*	set order on save
				*	as this runs after the term have been updated in the db by wordpress
				*	a simple call to wppizza_get_cats_hierarchy will do
				*********************************************************/
					/**get categories sorted */
					$update_cat_sort=$this->wppizza_get_cats_hierarchy();

					/**if we are adding a parent cat, set to topmost as by default it would be sorted by name*/
					if(isset($_POST['parent']) && $_POST['parent']==-1){
						unset($update_cat_sort[$column]);

						/**resort, with new cat id set to being the first*/
						$new_sorter=0;
						$update_cat_re_sort=array();
						$update_cat_re_sort[$column]=$new_sorter;
						foreach($update_cat_sort as $catId=>$sort){
							$new_sorter++;
							$update_cat_re_sort[$catId]=$new_sorter;
						}
						$update_cat_sort=$update_cat_re_sort;
					}

					$newSort['layout']['category_sort_hierarchy']=$update_cat_sort;
					/**also add category_sort too for legacy reasons*/
					$newSort['layout']['category_sort']=$update_cat_sort;

					/*update option*/
					update_option( $this->pluginSlug, $newSort );
			}

			/************************************************************************
			*	we are editing in single cat edit screen (where we can change parent)
			*	save new order as parent might have changed
			*	this runs after the term have been updated in the db by wordpress
			**************************************************************************/
			if(isset($newSort['layout']['category_sort_hierarchy'][$column]) && !empty($_POST['action']) && $_POST['action']=='editedtag'){

				/**get categories sorted */
				$update_cat_sort=$this->wppizza_get_cats_hierarchy();
				$newSort['layout']['category_sort_hierarchy']=$update_cat_sort;
				/**also add category_sort too for legacy reasons*/
				$newSort['layout']['category_sort']=$update_cat_sort;

				/*update option*/
				update_option( $this->pluginSlug, $newSort );
			}
			/************************************************************************
			*	we are deleting -  ajax or bulk delete,
			*	save new order
			*	as this runs after the term have been updated in the db by wordpress
			*	a simple call to wppizza_get_cats_hierarchy will do
			**************************************************************************/
			if(isset($newSort['layout']['category_sort_hierarchy'][$column]) && !empty($_POST['action']) && ( $_POST['action']=='delete-tag'  || (isset($_POST['delete_tags']) && is_array($_POST['delete_tags']))) ){

				/*
					bulk delete
				*/
				if(!empty($_POST['delete_tags'])){
					foreach($_POST['delete_tags'] as $cat_id_delete){
						unset($newSort['layout']['category_sort'][$cat_id_delete]);//unset as deleted legacy perhaps in use in other plugins
						unset($newSort['layout']['category_sort_hierarchy'][$cat_id_delete]);//unset as deleted
					}
					/**resort, now we have unset deleted*/
					$new_sorter=0;
					$update_cat_sort=array();
					foreach($newSort['layout']['category_sort_hierarchy'] as $catId=>$sort){
						$update_cat_sort[$catId]=$new_sorter;
						$new_sorter++;
					}
				}else{
					/**
						just get categories sorted on ajax delete,
						as this runs after the term have been updated in the db by wordpress
					**/
					$update_cat_sort=$this->wppizza_get_cats_hierarchy();
				}

				$newSort['layout']['category_sort_hierarchy']=$update_cat_sort;
				/**also add category_sort too for legacy reasons*/
				$newSort['layout']['category_sort']=$update_cat_sort;

				/*update option*/
				update_option( $this->pluginSlug, $newSort );
			}

		$run_once++;
		}
	}

/******************************************************************
	[meta boxes , render , save on creation/update of post]
*******************************************************************/
	function wppizza_admin_metaboxes() {
    	add_meta_box( $this->pluginSlug,__('Set Item Options', 'wppizza-locale'),array($this,'wppizza_admin_render_metaboxes'),$this->pluginSlug, 'normal', 'high');
	}
	function wppizza_admin_render_metaboxes( $meta_options ) {
		require_once(WPPIZZA_PATH .'inc/admin.echo.metaboxes.inc.php');
	}
	function wppizza_admin_save_metaboxes($item_id, $item_details ) {

		/** bypass, when doing "quickedit" (ajax) and /or "bulk edit"  as it will otherwsie loose all meta info (i.e prices, additives etc)!!!***/
		if ( defined('DOING_AJAX') || isset($_GET['bulk_edit'])){return;}


		/**bypass the below when activating plugin as we are installing the default items on first activation via wp_insert_post()**/
		if(!isset($_GET['activate'])){
			/***as this function gets called when creating a new page, we will also insert some default values (as $_POST will be empty)**/
			// Check post type first
		    if(isset($item_details->post_type) && $item_details->post_type == $this->pluginSlug ){
		    	//**additives array**//
		    	$itemMeta['additives']=array();
		    	if(isset($_POST[$this->pluginSlug]['additives'])){
		    	foreach($_POST[$this->pluginSlug]['additives'] as $k=>$v){
		    		$itemMeta['additives'][$k]				= (int)$_POST[$this->pluginSlug]['additives'][$k];
		    	}}

		    	/**alt tax rate**/
		    	$itemMeta['item_tax_alt']							= false;
		    	if(isset($_POST[$this->pluginSlug]['item_tax_alt'])){
		    	$itemMeta['item_tax_alt']							= true;
		    	}

		    	/**set some default values (namely sizes and prices) when adding new page**/
		    	if(!isset($_POST[$this->pluginSlug]['sizes'])){
					$options = $this->pluginOptions;
					$optionsSizes =wppizza_sizes_available($options['sizes']);
					/**get no of price input fields of first available size option**/
					reset($optionsSizes);
					$first_key = key($optionsSizes);
					$_POST[$this->pluginSlug]['sizes']=$first_key;
					if(isset($optionsSizes[$first_key]['price'])){
					$_POST[$this->pluginSlug]['prices']=$optionsSizes[$first_key]['price'];
					}
		    	}

				//**sizes**//
				$itemMeta['sizes']							= (int)$_POST[$this->pluginSlug]['sizes'];

		    	//**prices**//
		    	$itemMeta['prices']=array();
		    	if(isset($_POST[$this->pluginSlug]['prices'])){
		    	foreach($_POST[$this->pluginSlug]['prices'] as $k=>$v){
		    		$itemMeta['prices'][$k]					= wppizza_validate_float_only($_POST[$this->pluginSlug]['prices'][$k],2);
		    	}}

		    	/**add filter**/
				$itemMeta=apply_filters('wppizza_filter_admin_save_metaboxes', $itemMeta, $item_id, $this->pluginSlug);


		    	update_post_meta($item_id, $this->pluginSlug, $itemMeta);
			}
		}
	}
/***********************************************************************************************
*
*
*	[Admin output, settings and options]
*
*
************************************************************************************************/
public function register_admin_menu_pages() {
	require_once(WPPIZZA_PATH .'inc/admin.echo.register.submenu.pages.inc.php');
}
function wppizza_admin_pages_init(){
	require_once(WPPIZZA_PATH .'inc/admin.echo.settings.sections.inc.php');
}
function wppizza_admin_page_text_header($v) {
	require_once(WPPIZZA_PATH .'inc/admin.echo.settings.text.header.inc.php');
}
public function admin_manage_additives(){
	require_once(WPPIZZA_PATH .'inc/admin.echo.manage_additives.inc.php');
}
public function admin_manage_opening_times(){
	require_once(WPPIZZA_PATH .'inc/admin.echo.manage_opening_times.inc.php');
}
public function admin_manage_meal_sizes(){
	require_once(WPPIZZA_PATH .'inc/admin.echo.manage_meal_sizes.inc.php');
}
public function admin_manage_order_history(){
	require_once(WPPIZZA_PATH .'inc/admin.echo.manage_order_history.inc.php');
}
public function admin_manage_tools(){
	$this->wppizza_admin_tabs_tools();
	require_once(WPPIZZA_PATH .'inc/admin.echo.manage_tools.inc.php');
}
public function admin_manage_access_rights(){
	require_once(WPPIZZA_PATH .'inc/admin.echo.manage_access_rights.inc.php');
}
public function admin_manage_templates(){
	$this->wppizza_admin_tabs_templates();
	require_once(WPPIZZA_PATH .'inc/admin.echo.manage_templates.inc.php');
}
public function admin_manage_reports(){
	require_once(WPPIZZA_PATH .'inc/admin.echo.manage_reports.inc.php');
}
public function admin_manage_layout(){
	require_once(WPPIZZA_PATH .'inc/admin.echo.manage_layout.inc.php');
}
public function admin_manage_order_settings(){
	require_once(WPPIZZA_PATH .'inc/admin.echo.manage_order_settings.inc.php');
}
public function admin_manage_order_form(){
	require_once(WPPIZZA_PATH .'inc/admin.echo.manage_order_form.inc.php');
}
public function admin_manage_localization(){
	require_once(WPPIZZA_PATH .'inc/admin.echo.manage_localization.inc.php');
}
public function admin_manage_settings(){
	require_once(WPPIZZA_PATH .'inc/admin.echo.manage_settings.inc.php');
}
public function admin_manage_gateways(){
	require_once(WPPIZZA_PATH .'inc/admin.echo.manage_gateways.inc.php');
}
public function wppizza_admin_settings_input($field='') {
	/*to include some queries and result sets only once where needed*/
	static $fieldcount=0;
	static $cats;
	static $pages;
	/*
		some variables we need for some fields  -
		only include the first time
		[could be included more elegantly else where i think - one day]*
	*/
	/*****************settings***********************************/
	if($fieldcount==0 && ($field=='search_include' || $field=='category_parent_page') ){
		/*get all pages - possibly get these hierarchical to save some child queries for 'category_parent_page' **/
		$pages=get_pages(array('post_type'=> 'page','echo'=>0,'title_li'=>''));
		$fieldcount++;
	}
	/*****************order settinsg***********************************/
	if($fieldcount==0 && ($field=='delivery' || $field=='discounts') ){
		$args = array('taxonomy' => ''.WPPIZZA_TAXONOMY.'');
		$cats=get_categories($args);
		$fieldcount++;
	}
	require(WPPIZZA_PATH .'inc/admin.echo.settings.input.fields.inc.php');
}

/*********************************************************
	[Admin Subpages TABS]
*********************************************************/
function wppizza_admin_tabs_tools() {
	$tabs[]=array('lbl'=>__('Tools','wppizza-locale'),'slug'=>'tools');
	$tabs[]=array('lbl'=>__('System Info','wppizza-locale'),'slug'=>'sysinfo');
	$current = !empty($_GET['tab']) ?  $_GET['tab'] : $tabs[0]['slug'];
	echo '<div id="icon-themes" class="icon32"><br></div>';
	echo '<h2 class="nav-tab-wrapper">';
	foreach( $tabs as $tab => $arr ){
	    $class = ( $arr['slug'] == $current ) ? ' nav-tab-active' : '';
	    echo "<a class='nav-tab ".$class."' href='?post_type=".WPPIZZA_POST_TYPE."&page=wppizza-tools&tab=".$arr['slug']."'>".$arr['lbl']."</a>";
	}
	echo '</h2>';
}
function wppizza_admin_tabs_templates() {
	$tabs[]=array('lbl'=>__('eMails','wppizza-locale'),'slug'=>'emails');
	$tabs[]=array('lbl'=>__('Print Order','wppizza-locale'),'slug'=>'print');
	$current = !empty($_GET['tab']) ?  $_GET['tab'] : $tabs[0]['slug'];
	echo '<div id="icon-themes" class="icon32"><br></div>';
	echo '<h2 class="nav-tab-wrapper">';
	foreach( $tabs as $tab => $arr ){
	    $class = ( $arr['slug'] == $current ) ? ' nav-tab-active' : '';
	    echo "<a class='nav-tab ".$class."' href='?post_type=".WPPIZZA_POST_TYPE."&page=wppizza-templates&tab=".$arr['slug']."'>".$arr['lbl']."</a>";
	}
	echo '</h2>';
}

/*********************************************************
*
*		[reports]
*
*********************************************************/
/**get the data**/
function wppizza_reports(){
	global $typenow,$pagenow;
	if($typenow==WPPIZZA_POST_TYPE && $pagenow=='edit.php' && isset($_GET['page']) && $_GET['page']=='wppizza-reports'){
		add_action('admin_init', array( $this, 'wppizza_require_report_functions'),11);
		add_action('admin_init', array( $this, 'wppizza_export_report'),12);
		//add_action( 'current_screen', array( $this, 'wppizza_show_report'));
	}
}
/***export**/
function wppizza_export_report(){
	if(isset($_GET['export'])){
		$data=wppizza_report_dataset($this->pluginOptions,$this->pluginOrderTable, true);
		wppizza_report_export($data['dataset']);/*exits**/
	}
}
/***include required functions**/
function wppizza_require_report_functions(){
	require_once(WPPIZZA_PATH .'inc/admin.report.functions.php');
}

/*********************************************************
*
*		[dashboard widget]
*
*********************************************************/
function wppizza_dashboard_widget(){
	if (current_user_can('wppizza_cap_reports')){
		$dpwTitle=__('Overview','wppizza-locale');
		if(!empty($this->pluginOptions['plugin_data']['wp_multisite_reports_all_sites'])){
			$dpwTitle.=' '.__('[All Sites]','wppizza-locale');
		}
		/*filter if you want*/
		$dpwTitle = apply_filters('wppizza_filter_dbw_title', $dpwTitle);

		wp_add_dashboard_widget('wppizza_dashboard_widget',WPPIZZA_NAME.' '.$dpwTitle, array($this,'wppizza_do_dashboard_widget'));
	}
}
function wppizza_do_dashboard_widget() {

	if( version_compare( PHP_VERSION, '5.3', '<' )) {
		print"<div style='text-align:center;margin:50px 0'>Sorry, reporting is only available with php >=5.3</div>";
		return;
	}

	/**number of posts**/
	$count_posts = wp_count_posts(WPPIZZA_POST_TYPE);

	/*number of categories**/
	$taxonomy = WPPIZZA_TAXONOMY;
	$terms = get_terms($taxonomy);
	$count_categories=0;
	if ( $terms && !is_wp_error( $terms ) ){
	$count_categories=count($terms);
	}

	/***sales***/
	require_once(WPPIZZA_PATH .'inc/admin.report.functions.php');
	$data=wppizza_report_dataset($this->pluginOptions,$this->pluginOrderTable);
	//print_r($data['dataset']);

	/**totals**/
	$totalSalesValue=$data['dataset']['sales_value_total'];
	$totalSalesCount=$data['dataset']['sales_count_total'];
	$totalItemsCount=$data['dataset']['items_count_total'];
	/**today**/
	$totalSalesValueToday=0;
	$totalSalesCountToday=0;
	$totalItemsCountToday=0;
	if(isset($data['dataset']['sales'][date("Y-m-d")])){
	$totalSalesValueToday=$data['dataset']['sales'][date("Y-m-d")]['sales_value_total'];
	$totalSalesCountToday=$data['dataset']['sales'][date("Y-m-d")]['sales_count_total'];
	$totalItemsCountToday=$data['dataset']['sales'][date("Y-m-d")]['items_count_total'];
	}
	echo'
			<table class="wppizza-dash wppizza-dash-sales">
				<thead>
					<tr>
						<th>&nbsp;</th>
						<th>'.__('Total','wppizza-locale').'</th>
						<th>'.__('Today','wppizza-locale').'</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>'.__('Earnings','wppizza-locale').'</td>
						<td>'.$data['currency'].' '.$totalSalesValue.'</td>
						<td>'.$data['currency'].' '.$totalSalesValueToday.'</td>
					</tr>
					<tr>
						<td>'.__('Sales','wppizza-locale').'</td>
						<td>'.$totalSalesCount.'</td>
						<td>'.$totalSalesCountToday.'</td>
					</tr>
					<tr>
						<td>'.__('Items Sold','wppizza-locale').'</td>
						<td>'.$totalItemsCount.'</td>
						<td>'.$totalItemsCountToday.'</td>
					</tr>
				</tbody>
			</table>

			<table class="wppizza-dash wppizza-dash-items">
				<thead>
					<tr>
						<th>'.__('Menu Items (active)','wppizza-locale').'</th>
						<th>'.__('Categories','wppizza-locale').'</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>'.$count_posts->publish.'</td>
						<td>'.$count_categories.'</td>
					</tr>
				</tbody>
			</table>


	';
}
/*********************************************************
*
*		[cron events]
*
*********************************************************/
function wppizza_cron_setup_schedule($cronOptions) {
	/**clear all other old schedules**/
	wp_clear_scheduled_hook( 'wppizza_cron');
	/*setup new**/
	if($cronOptions['schedule']!=''){
		if ( ! wp_next_scheduled( 'wppizza_cron' ) ) {
			wp_schedule_event( time(), $cronOptions['schedule'], 'wppizza_cron');
		}
	}
}

function wppizza_cron_do() {
	global $wpdb;
	/*get options**/
	$cronOptions=$this->pluginOptions['cron'];
	/*days to delete**/
	$days=$cronOptions['days_delete'];

	/**do or dont delete all non completed orders**/
	$pStatusQuery=" IN ('INITIALIZED','CANCELLED')";
	if(!empty($cronOptions['failed_delete'])){
		$pStatusQuery=" NOT IN ('COMPLETED','PENDING','REFUNDED','CAPTURED','COD','AUTHORIZED')";
	}
	$sql="DELETE FROM ".$wpdb->prefix . $this->pluginOrderTable." WHERE order_date < TIMESTAMPADD(DAY, %d, NOW()) AND payment_status ".$pStatusQuery."";
	$res=$wpdb->query( $wpdb->prepare($sql, -$days));

	/**add to log**/
	error_log("WPPIZZA CRON RUN");
}

/*********************************************************
*
*		[array of wp capabilities]
*
*********************************************************/
function wppizza_set_capabilities($get_user_caps=false){
	$tabs['settings']=array('name'=>__('Settings','wppizza-locale'),'cap'=>'wppizza_cap_settings');
	$tabs['order-settings']=array('name'=>__('Order Settings','wppizza-locale'),'cap'=>'wppizza_cap_order_settings');
	$tabs['gateways']=array('name'=>__('Gateways','wppizza-locale'),'cap'=>'wppizza_cap_gateways');
	$tabs['order-form-settings']=array('name'=>__('Order Form Settings','wppizza-locale'),'cap'=>'wppizza_cap_order_form_settings');
	$tabs['opening-times']=array('name'=>__('Opening Times','wppizza-locale'),'cap'=>'wppizza_cap_opening_times');
	$tabs['meal-sizes']=array('name'=>__('Meal Sizes','wppizza-locale'),'cap'=>'wppizza_cap_meal_sizes');
	$tabs['additives']=array('name'=>__('Additives','wppizza-locale'),'cap'=>'wppizza_cap_additives');
	$tabs['layout']=array('name'=>__('Layout','wppizza-locale'),'cap'=>'wppizza_cap_layout');
	$tabs['localization']=array('name'=>__('Localization','wppizza-locale'),'cap'=>'wppizza_cap_localization');
	$tabs['order-history']=array('name'=>__('Order History','wppizza-locale'),'cap'=>'wppizza_cap_order_history');
	$tabs['access']=array('name'=>__('Access Rights','wppizza-locale'),'cap'=>'wppizza_cap_access');
	$tabs['templates']=array('name'=>__('Templates','wppizza-locale'),'cap'=>'wppizza_cap_templates');
	$tabs['reports']=array('name'=>__('Reports','wppizza-locale'),'cap'=>'wppizza_cap_reports');
	$tabs['tools']=array('name'=>__('Tools','wppizza-locale'),'cap'=>'wppizza_cap_tools');
	$tabs['delete-order']=array('name'=>__('Delete Orders','wppizza-locale'),'cap'=>'wppizza_cap_delete_order');


	if($get_user_caps){
		global $current_user;
		$usercaps=array();
		$capUnique=array();/*dont need to have the same thing multiple times*/
		/*user can have more than one role**/
		foreach($current_user->roles as $roleName){
			$userRole = get_role($roleName);
			foreach($tabs as $tab=>$v){
				if(isset($userRole->capabilities[$v['cap']]) && !isset($capUnique[$v['cap']])){
					$usercaps[]=array('tab'=>$tab,'cap'=>$v['cap'],'name'=>$v['name']);
					$capUnique[$v['cap']]=1;
				}
			}
		}
		return $usercaps;
	}

	return $tabs;
}
/****************************************************************************************
*
*	[set capability to  save options, if we can't find one (although this should not ever happen)
*	but something might have gotten screwed up,
*	automatically use 'manage_option' default so at least an admin can do stuff]
*
*****************************************************************************************/
function wppizza_admin_option_page_capability($capability) {
	$currentUserCaps=$this->wppizza_set_capabilities(true);
	if(isset($currentUserCaps[0])){
		$capability=$currentUserCaps[0]['cap'];
	}
	return $capability;
}

/*********************************************************
*
*	[admin output print functions -
*	to consistantly add admin output sections,
*	whether or not added via ajax]
*********************************************************/
/*********************************************************
		[system info]
*********************************************************/
private function wppizza_system_info_include(){
	ob_start();
	include(WPPIZZA_PATH.'inc/admin.system-info.php');
	$sysinfo = ob_get_clean();
	return $sysinfo;
}
/*********************************************************
		[opening times]
*********************************************************/
private function wppizza_admin_section_opening_times_custom($field,$k=null,$options=null){
	require(WPPIZZA_PATH .'inc/admin.echo.get_openingtimes.inc.php');
	return $str;
}
/*********************************************************
		[times closed]
*********************************************************/
private function wppizza_admin_section_times_closed_standard($field,$k=null,$options=null){
	require(WPPIZZA_PATH .'inc/admin.echo.get_timesclosed.inc.php');
	return $str;
}
/*********************************************************
		[additives]
*********************************************************/
private function wppizza_admin_section_additives($field,$k,$v,$optionInUse=null){
	require(WPPIZZA_PATH .'inc/admin.echo.get_additives.inc.php');
	return $str;
}
/*********************************************************
		[gateways]
*********************************************************/
private function wppizza_admin_section_gateways($field,$options){
	require(WPPIZZA_PATH .'inc/admin.echo.get_gateways.inc.php');
}
/**************************************************************
	[get registered gateways. must start with WPPIZZA_GATEWAY_]
**************************************************************/
function wppizza_get_registered_gateways() {
	$paymentGateways = array();
	$gatewayDetails=$this->pluginOptions['gateways']['gateway_selected'];

	/*set sort order depending on the order they are stored in the db (by key),
		true/false declares whether gateway is enabled in frontend
	**/
	/*bit convoluted maybe, but saves me some db entry**/
	$i=0;
	foreach($gatewayDetails as $k=>$v){
		$gatewayOrder[$k]=$i;
	 $i++;
	}

	$allClasses=get_declared_classes();
	foreach ($allClasses AS $class){
		$chkStr=substr($class,0,16);
		if($chkStr=='WPPIZZA_GATEWAY_'){
			$iDent=substr($class,16);
			$c=new $class;
			$c->gateway_settings();
			$paymentGateways[] =array(
				'sort'=>!empty($gatewayOrder[$iDent]) ? $gatewayOrder[$iDent] : '0',
				'enabled'=>!empty($gatewayDetails[$iDent]) ? $gatewayDetails[$iDent] : false,
				'ident'=>$iDent,
				'gatewayName'=>$c->gatewayName,
				'gatewayDescription'=>$c->gatewayDescription,
				'gatewayAdditionalInfo'=>$c->gatewayAdditionalInfo,
				'gatewayOptionsName'=>$c->gatewayOptionsName,
				'gatewayOptions'=>$c->gatewayOptions,
				'gatewaySettings'=>$c->gateway_settings(),
				'gatewaySettingsNonEditable'=>method_exists($c,'gateway_settings_non_editable') ? $c->gateway_settings_non_editable() : array()
			);
		}
	}

	return $paymentGateways;
}

/*********************************************************
		[available sizes of meal items]
*********************************************************/
private function wppizza_admin_section_sizes($field,$k,$v=null,$optionInUse=null){
	require(WPPIZZA_PATH .'inc/admin.echo.get_mealsizes.inc.php');
	return $str;
}
/***********************************************************************************************
*
*
* 	[ajax calls]
*
*
***********************************************************************************************/
	/******************
     [admin ajax call]
    *******************/
	public function wppizza_admin_json(){
		require(WPPIZZA_PATH.'ajax/admin-get-json.php');
		die();
	}
	/*******************
     [frontend ajax call]
    ********************/
	public function wppizza_json(){
		require(WPPIZZA_PATH.'ajax/get-json.php');
		die();
	}
/***********************************************************************************************
*
* 	[shortcode functions]
*	[ensure shortcodes are enabled - DOH]
*	[to use shortcodes in text widgets add  "add_filter('widget_text','do_shortcode')" to theme function file
*	or use any suitable plugin]
*
************************************************************************************************/
    /*****************************************************
     * Generates shortcode output utilising templates
     * @atts    The array of shortcode attributes
     ******************************************************/
	public function wppizza_add_shortcode($atts){
		$markup='';
		include(WPPIZZA_PATH.'views/shortcode.php');
		return $markup;
		die();//needed !!!
	}

	/*****************************************************
	* bestseller loop arguments filter
	* @args    The array query arguments
	* sort output according to populariry (i.e how many times an item has been bought)
	*****************************************************/
	function wppizza_filter_shortcode_post_in_args($args){
		$args['orderby']='post__in';
		return $args;
	}

    /*****************************************************
    *
    * add to cart button by shortcode
    *
    ******************************************************/
	function wppizza_add_item_to_cart_button($atts , $options){
		$str='';/*ini empty*/

		/*missing or empty attributes**/
		if(!isset($atts['id']) ||  $atts['id']<=0 || $atts['id']=='' ){
			return $str;
		}
		/**not the correct post type*/
		$posttype=get_post_type( $atts['id'] );
		if($posttype!=WPPIZZA_POST_TYPE){
			return $str;
		}

		/* get sizes for this menu item*/
		$meta=get_post_meta($atts['id'],$this->pluginSlug, true);//, $this->pluginSlug, true

		/*size id*/
		$size=$meta['sizes'];

		/*price/tier id*/
		$selectedPrice=0;//default to first
		/*check if size exists*/
		if(isset($atts['size']) && isset($meta['prices'][$atts['size']])){
			$selectedPrice=$atts['size'];
		}
		$dropdown='';
		$hasdropdown=false;
		if(!isset($atts['single']) || !$atts['single']){/*if not forced to single button*/
			if(count($meta['prices'])>1){
				$hasdropdown=true;
				$dropdown.='<select id="wppizza-add-to-cart-size-'.$atts['id'].'" class="wppizza-add-to-cart-size">';
					foreach($meta['prices'] as $selPrice=>$value){
					$dropdown.='<option value="'.$atts['id'].'-'.$size.'-'.$selPrice.'" '.selected($selPrice,$selectedPrice,false).'>'.$options['sizes'][$size][$selPrice]['lbl'].'</option>';
					}
				$dropdown.='</select>';
			}
		}

		/****label - filterable****/
		$options['localization']['add_to_cart']['lbl'] = apply_filters('wppizza_filter_add_item_to_cart_button_label', $options['localization']['add_to_cart']['lbl'],$atts);

		/****output****/
		$str='<span id="wppizza-add-to-cart-btn-'.$atts['id'].'" class="wppizza-add-to-cart-btn-wrap">';
		 if($hasdropdown){
			/**dropdown if multiple*/
			$str.=$dropdown;
			/*size id and tier*/
			$str.='<input type="button" id="'.$this->pluginSlug.'-add-to-cart-select-'.$atts['id'].'" class="'.$this->pluginSlug.'-add-to-cart-select '.$this->pluginSlug.'-add-to-cart-btn" value="'.$options['localization']['add_to_cart']['lbl'].'" />';
		 	/*span that gets the trigger must be next element after button*/
		 	$str.='<span id="'.$this->pluginSlug.'-'.$atts['id'].'-'.$size.'-'.$selectedPrice.'" class="'.$this->pluginSlug.'-add-to-cart '.$this->pluginSlug.'-add-to-cart-select-trigger"></span>';
		 }else{
			/*direct selection*/
			$str.='<input type="button" id="'.$this->pluginSlug.'-'.$atts['id'].'-'.$size.'-'.$selectedPrice.'" class="'.$this->pluginSlug.'-add-to-cart '.$this->pluginSlug.'-add-to-cart-btn" value="'.$options['localization']['add_to_cart']['lbl'].'" />';
		 }
		$str.='</span>';
		return $str;
	}
    /*****************************************************
    * include relevant template depending on shortcode
    * [see header of template for details]
    ******************************************************/
	public function wppizza_include_shortcode_template($type,$atts=null){

		/***************************************
			[include category loop template]
		***************************************/
		if($type=='category'){
			$options = $this->pluginOptions;
			$querys=array();
			/*********************************************************************
				[as we have changed in v.2.8.7.4 to have the additives as array
				so we can custom sort them but dont really want to screw up
				anyones edited templates, re-map key and name]
			*******************************************************************/
				$mapAdditives=array();
				if(isset($options['additives']) && is_array($options['additives'])){
					foreach($options['additives'] as $o=>$a){
						if(is_array($a)){
							if($a['sort']==''){$a['sort']=$o;}
							$mapAdditives[$a['sort']]=$a['name'];
						}else{
							/**in case we have not yet re-saved the additives**/
							$mapAdditives[$o]=$a;
						}
					}
				}
				//ksort($mapAdditives,SORT_NATURAL);//php 5.4 only
				uksort($mapAdditives, 'strnatcmp');

				$options['additives']=$mapAdditives;
				/*******re-map additives inside loop too **************************/
				add_filter('wppizza_filter_loop_meta', array( $this, 'wppizza_additives_remap'),10,1);
			/*******************************************************************
			*
			*	[end of additives re-mapping
			*
			********************************************************************/


			/**********************************************************************************
			*
			*	[top 10 (or whatever) -> bestsellers ]
			*
			**********************************************************************************/
			if(isset($atts['bestsellers'])){
				global $wpdb;
				/**wppizza posts to compare against, making sure posts still exists**/
				$wppPostsQuery="SELECT ID FROM ".$wpdb->prefix ."posts where post_type='wppizza' AND post_status='publish' ";
				$wppPostsRes = $wpdb->get_results($wppPostsQuery,OBJECT_K );

				/**run the query**/
				$bestsellersQuery="SELECT id,order_ini FROM ".$wpdb->prefix . $this->pluginOrderTable." WHERE payment_status='COMPLETED' ";
				$bestsellersRes = $wpdb->get_results($bestsellersQuery);
				$bestsellers=array();

				/**loop through items and get quantities**/
				foreach($bestsellersRes as $b=>$bs){
					$thisOrderDetails=maybe_unserialize($bs->order_ini);
					if(isset($thisOrderDetails['item']) && is_array($thisOrderDetails['item'])){
						foreach($thisOrderDetails['item'] as $item){
							/**make sure this post still exists and has been sold more than 0 times**/
							if(isset($wppPostsRes[$item['postId']]) && $item['quantity']>0){
								if(!isset($bestsellers[$item['postId']])){
									$bestsellers[$item['postId']]=$item['quantity'];
								}else{
									$bestsellers[$item['postId']]+=$item['quantity'];
								}
							}
						}
					}
				}

				/*sort by quantity*/
				arsort($bestsellers);

				/*chunk to required bits*/
				$chunks=(int)$atts['bestsellers'];
				$bestsellers=array_chunk($bestsellers, $chunks, true);

				if(count($bestsellers)>0){
					/**required bestsellers**/
					$bestsellersIncl=implode(',',array_keys($bestsellers[0]));
				}else{
					$bestsellersIncl='';
				}

				/**get/set  id's **/
				$inclAtts=array();
				if($bestsellersIncl!=''){
					$inclAtts[]=$bestsellersIncl;
				}
				/**add distinctly set includes (if any)**/
				if(isset($atts['include'])){
					$inclAtts[]=$atts['include'];
				}

				/**finally set the includes id's**/
				$atts['include']=implode(',',$inclAtts);


				/***add a filter that can be used if required for alternative sorting for example*****/
				$atts['include'] = apply_filters('wppizza_filter_bestsellers_include', $atts['include']);

				//alternatives if empty*/
				if((!isset($atts['include']) || $atts['include']=='') && isset($atts['ifempty'])){
					$atts['include'] = 	$atts['ifempty'];
				}

				/*distincly unset any category and header**/
				unset($atts['category']);/*unset all categories as query_vars below will hold an array of all the categories**/
				$atts['noheader']=1;/**omit header**/

				/*get and set all terms in the taxonomy and convert to array of all slugs for tax query */
				$terms = get_terms( WPPIZZA_TAXONOMY );
				$query_var = wp_list_pluck( $terms, 'slug' );
				/** filter arguments -> sort output according to populariry (i.e how many tims an item has been bought)**/
				add_filter('wppizza_filter_loop', array( $this, 'wppizza_filter_shortcode_post_in_args'),10,1);

				/**********************************************************
					as bestsellers are not tied to a particular category
					set some static variables so we can use it in the
					loop templates
				**********************************************************/
				$querys['bestsellers'] = new stdClass;
				$querys['bestsellers']->term_id = array_keys($query_var);
				$querys['bestsellers']->name = 'bestsellers';//should be a slug. no spaces etc
				$querys['bestsellers']->slug = $query_var;
				$querys['bestsellers']->category_id = 0;//unknown / not applicable
			}


			/**********************************************************************************
			*
			*	[single item]
			*
			**********************************************************************************/
			if(isset($atts['single'])){
				$atts['include']=(int)$atts['single'];
				unset($atts['category']);/*unset all categories as query_vars below will hold an array of all the categories**/
				$atts['noheader']=1;/**omit header**/
				/*get and set all terms in the taxonomy and convert to array of all slugs for tax query */
				$termDetails = wp_get_post_terms( $atts['include'], WPPIZZA_TAXONOMY);
				if ($termDetails && !is_wp_error($termDetails)){
					$query_var = wp_list_pluck( $termDetails, 'slug', 'term_id');
				}else{
					/*cat find any, get all*/
					$terms = get_terms( WPPIZZA_TAXONOMY );
					$query_var = wp_list_pluck( $terms, 'slug' );
				}
				/*indexed array of term to be able to set some sort of category id*/
				$termKeys=array_keys($query_var);
				/** filter arguments -> sort output according to populariry (i.e how many tims an item has been bought)**/
				add_filter('wppizza_filter_loop', array( $this, 'wppizza_filter_shortcode_post_in_args'),10,1);

				/**********************************************************
					as single items are not tied to a particular category
					(they can be in several)
					set some static variables so we can use it in the
					loop templates
				**********************************************************/
				$querys['single'] = new stdClass;
				$querys['single']->term_id = $termKeys;
				$querys['single']->name = 'single';//should be a slug. no spaces etc
				$querys['single']->slug = $query_var[$termKeys[0]];//get first slug
				$querys['single']->category_id = $termKeys[0];//just use the first we have

			}

			/**********************************************************************************
			*
			*	[select first category if none selected->used when using shortcode without category,
			*	unless we are looking for bestsellers]
			*
			**********************************************************************************/
			if(!isset($atts['category']) && !isset($atts['bestsellers']) && !isset($atts['single']) ){
				/*set drag and drop sort*/
				$termSort=$options['layout']['category_sort_hierarchy'];

				/**check first if categories translate for wppizza taxonomy is actually enabled **/
				$wpml_wppizza_cat_translate_enabled=$this->wppizza_wpml_cats_translated(WPPIZZA_TAXONOMY);

				//if wpml enabled for wppizza xategories get first cat for that language by comparing to category_sort_hierarchy */
				if (!empty($wpml_wppizza_cat_translate_enabled)){

					/*will get parent cats for current language*/
					$args=array('orderby' => 'name', 'order' => 'ASC', 'hide_empty' => 0, 'parent' => 0, 'taxonomy' => WPPIZZA_TAXONOMY	);
					$wpml_lang_categories = get_categories($args);

					if(!empty($wpml_lang_categories)){
					foreach($wpml_lang_categories as $wpml_lang_parent_cats){
						$wpml_lang_cats[$wpml_lang_parent_cats->term_id]=$wpml_lang_parent_cats->term_id;
					}}

					foreach($termSort as $cat_id=>$sort){
						if(isset($wpml_lang_cats[$cat_id])){
							$firstTermId=$cat_id;
							break;
						}
					}

					/*
						failsafe in case current language has no cats set yet -
						possible, but unlikely to happen as there would be no point in
						enabling category translation in the first place
					*/
					if(empty($firstTermId)){
						asort($termSort);
						reset($termSort);
						$firstTermId=key($termSort);
					}
				}else{
					/**standard cat sort without wpml , get first cat (even though it might not have any posts)*/
					asort($termSort);
					reset($termSort);
					$firstTermId=key($termSort);
				}


				/*get slug and taxonomy from id*/
				$querys[]=get_term_by('id', $firstTermId, WPPIZZA_TAXONOMY);
			}

			/**********************************************************************************
			*
			*	[category(ies) has/have been selected]
			*
			**********************************************************************************/
			if(isset($atts['category'])){
				$catSlugsToArray=explode(',',$atts['category']);
				if(in_array('!all',$catSlugsToArray)){
					/**check if we are excluding some categories**/
					$excludeCategory=array();
					foreach($catSlugsToArray as $sKey=>$slug){
						if(substr($slug,0,1)=='-'){
							$thisCategory=get_term_by('slug',$slug,WPPIZZA_TAXONOMY);
							$excludeCategory[$thisCategory->term_id]=$thisCategory->term_id;
						}
					}
					/**get all sorted ones*/
					$termSort=$options['layout']['category_sort_hierarchy'];
					asort($termSort);
					foreach($termSort as $termId=>$sorter){
						if(!isset($excludeCategory[$termId])){//exclude if necessary
							/*
								WPML always returns the id of original language which 
								might result in duplicate categories when using !all,
								so let's "unique" it by setting the id as key  
							*/
							if(function_exists('icl_object_id')){
								$wpml_id = icl_object_id($termId, WPPIZZA_TAXONOMY);//return_original_if_missing
								$querys[$wpml_id]=get_term_by('id', $wpml_id, WPPIZZA_TAXONOMY);
							}else{/* non wpml */
								$querys[]=get_term_by('id', $termId, WPPIZZA_TAXONOMY);
							}
						}
					}
					/*********
						filter any empty ones if more cats in $options['layout']['category_sort'] for some reason than actual categories

						temp solution for !all shortcode:
						in some (as yet unknown) cases/situations the sorted categories variable gets saved
						including some old/non existing ones and therefore consists of
						more than actually existing ones
						this can result in the last category being displayed multiple times when using !all shortcode

						so - as to at least for now fix the symptoms - filter empty ones
					********/
					$querys=array_filter($querys);

				}else{
					foreach($catSlugsToArray as $sKey=>$slug){
						if(trim($slug)!=''){//in case of using two commas omitting slug
							/*get slug and taxonomy from slug*/
							$querys[]=get_term_by('slug',$slug,WPPIZZA_TAXONOMY);
						}
					}
				}
			}

			/*exclude header*/
			if(isset($atts['noheader']) || $options['layout']['suppress_loop_headers']){
				$noheader=1;
			}
			/**if we want to capture the category id a menu item is currently in **/
			/**DEPRECATED ->this is not required anymore, but we'll leave it here in case someone is using an old customised loop template that wants this variable**/
			if($options['layout']['items_group_sort_print_by_category']){
				$getSlugDetails=1;
			}
			/*show.hide additives at bottom of loop*/
			if(isset($atts['showadditives'])){
				$showadditives=$atts['showadditives'];
			}

			$exclude=array();
			if(isset($atts['exclude'])){
				$exclXplode=explode(",",$atts['exclude']);
				foreach($exclXplode as $exclId){
					$exclude[$exclId]=$exclId;
				}
			}

			/*include specific items only -> overrides exclude*****/
			$include=array();
			if(isset($atts['include'])){
				$exclude=array();/*empty exclude*/
				$inclXplode=explode(",",$atts['include']);
				foreach($inclXplode as $inclId){
					$include[$inclId]=$inclId;
				}
			}
			/*set template style if !default*/
			$loStyle='';
			if($options['layout']['style']!='default'){
				$loStyle='-'.$options['layout']['style'].''	;
			}

			/**check which template to use**/
			/*include template from theme if exists*/
			if ($template_file = locate_template( array ($this->pluginLocateDir.'wppizza-loop'.$loStyle.'.php' ))){

			}else{
			/*if template not in theme, fallback to template in plugin*/
				$template_file=''.WPPIZZA_PATH.'templates/wppizza-loop'.$loStyle.'.php';
			}

			/*cat count*/
			$catCount=count($querys);
			static $loopCount=0;//set static in case there's more than one shortcode on page
			foreach($querys as $query){
				/**q vars**/
				if(isset($query) && $query){
					$query_var=$query->slug;
				}
				/*include the template**/
				include($template_file);
				do_action('wppizza_loop_template_end');

				$loopCount++;
			}
			return;//after loop
		}
		/***************************************
			[include navigation template]
		***************************************/
		if($type=='navigation'){
			extract(shortcode_atts(array('title' => ''), $atts));
			$child_of=0;
			if(isset($atts['parent'])){
				$query=get_term_by('slug',$atts['parent'],WPPIZZA_TAXONOMY);
				if($query){
					$child_of=$query->term_id;
				}
			}
			$excludeIds='';
			if(isset($atts['exclude'])){
			$excludeIds=$atts['exclude'];
			}
			$post_type=$this->pluginSlug;
			$args = array(
			  'taxonomy'     => WPPIZZA_TAXONOMY,
			  'orderby'      => 'name',
			  'show_count'   => 0,      // 1 for yes, 0 for no
			  'pad_counts'   => 0,      // 1 for yes, 0 for no
			  'hierarchical' => 1,      // 1 for yes, 0 for no
			  'title_li'     => $title,
			  'depth'     	 => 0,
			  'exclude'      => $excludeIds,
			  'child_of'     => $child_of,
			  'show_option_none'   => __('Nothing here'),
			  'hide_empty'   => 1,
			  'echo'   => 0				// keep as variable
			);

			/***add a filter if required*****/
			$args = apply_filters('wppizza_filter_navigation', $args);

			/*check if the file exists in the theme, otherwise serve the file from the plugin directory if possible*/
			if ($template_file = locate_template( array ($this->pluginLocateDir.'wppizza-navigation.php'))){
				include($template_file);
				return;
			}
			/*check if it exists in plugin directory (it should really BE there), otherwise we will have to serve defaults**/
			if (is_file(''.WPPIZZA_PATH.'templates/wppizza-navigation.php')){
				$template_file=''.WPPIZZA_PATH.'templates/wppizza-navigation.php';
				include($template_file);
				return;
			}
		}
		/***************************************
			[include shopping cart template]
		***************************************/
		if($type=='cart'){

			/***************************************
				[add cart filters]
			***************************************/
			add_filter( 'wppizza_cart_filter_items', array( $this, 'wppizza_filter_items_by_category'),10,2);
			add_filter('wppizza_cart_item', array( $this, 'wppizza_items_cart_print_category'),10,2);

			/**if request is ajax , return formatted tems**/
			if(isset($atts['request'])){
			$request=$atts['request'];
			}

			/**variables to use in template**/
			$options = $this->pluginOptions;
			$cart=wppizza_order_summary($_SESSION[$this->pluginSession],$options ,'cart');
			$cart = apply_filters('wppizza_filter_order_summary', $cart);
			/**txt variables from settings->localization*/
			$txt = $options['localization'];/*put all text varibles into something easier to deal with**/

			/*check if we set width,height**/
			$style=array();
			if(isset($atts['width']) && $atts['width']!=''){$style['width']='width:'.esc_html($atts['width']).'';}
			if(isset($atts['height']) && $atts['height']!=''){$style['height']='height:'.(int)($atts['height']).'px';}
			if(count($style)>0){$cartStyle['cart']=' style="'.implode(";",$style).'"';}
			if(isset($style['width'])){
				$cartStyle['width']=' style="'.$style['width'].'"';
			}
			/**make cart sticky**/
			$stickycart='';
			if(isset($atts['stickycart'])){
				$stickycart='wppizza-cart-sticky';
			}

			/**display order info like discounts and delivery costs**/
			if(isset($atts['orderinfo'])){
				$orderinfo=true;
			}
			/**display openingtimes above - depending on template - cart? **/
			if(isset($atts['openingtimes'])){
				$openingTimes=wppizza_frontendOpeningTimes($options);
			}
			/*check if the file exists in the theme, otherwise serve the file from the plugin directory if possible*/
			if ($template_file = locate_template( array ($this->pluginLocateDir.''.$this->pluginSlug.'-cart.php'))){
				include($template_file);
				return $cart;
			}
			/*check if it exists in plugin directory, otherwise we will have to serve defaults**/
			if (is_file(''.WPPIZZA_PATH.'templates/'.$this->pluginSlug.'-cart.php')){
				$template_file=''.WPPIZZA_PATH.'templates/'.$this->pluginSlug.'-cart.php';
				include($template_file);
				return $cart;
			}
		}
		/***************************************
			[include order page template]
		***************************************/
		if($type=='orderpage'){
			/*******get the variables***/
			$options = $this->pluginOptions;
			$cart=wppizza_order_summary($_SESSION[$this->pluginSession], $options, 'orderpage');
			$cart = apply_filters('wppizza_filter_order_summary', $cart);

			/**txt variables from settings->localization*/
			$txt = $options['localization'];
			/**formelements from settings->order form*/
			$formelements=$options['order_form'];
			/**allow filtering of order form form elements**/
			$formelements = apply_filters('wppizza_filter_formfields_order', $formelements);
			/**sort as appropriate***/
			$formelements = $this->wppizza_formfields_sort($formelements, $type);

			/**set session user vars as get vars (legacy reasons) to prefill form fields***/
			if(isset($_SESSION[$this->pluginSessionGlobal]['userdata']) && is_array($_SESSION[$this->pluginSessionGlobal]['userdata'])){
				foreach($_SESSION[$this->pluginSessionGlobal]['userdata'] as $k=>$v){
					$_GET[$k]=$v;
				}
			}

				if($cart['shopopen']){

					/**if the user is logged in , pre-enter the info we have (if prefill is selected in wppizza->order form settings. CHANGED IN VERSION 2.6.5.3***/
					if(is_user_logged_in() ) {
						global $current_user;
						$getUserMeta=get_user_meta( $current_user->ID );
						foreach($getUserMeta as $k=>$v){
							/**for legacy reasons, strip wppizza_ from key*/
							if(substr($k,0,8)=='wppizza_'){
								$k=substr($k,8);
							}
							$userMeta[$k]=$v[0];
						}
					}
					/***if we are adding get vars to the url (if a tip has been added for instance the page will be refreshed with vars appended), force prefill to be enabled and set values accordingly. ADDED IN VERSION 2.8.6**/
					/*
						$_GET will also include session data set in $_SESSION[$this->pluginSessionGlobal]['userdata'] as they will not be appended to the url anymore (it's just ugly).
						MODIFIED IN VERSION 2.8.8.3, but no changes made to this file
					*/
					$isSelfPickup=!empty($_SESSION[$this->pluginSession]['selfPickup']) ? 1:0;/**check if self pickup has been selected and make fields required as set in order form settings, ADDED in 2.8.9.10*/
					foreach($formelements as $elmKey=>$elm){
						if(isset($_GET[$elm['key']])){
							$formelements[$elmKey]['prefill']=1;
							$userMeta[$elm['key']]=$_GET[$elm['key']];
						}
						/**do NOT set required flag on selected elements on self-pickup. ADDED in 2.8.9.10 **/
						if($isSelfPickup==1 && !$elm['required_on_pickup']){
							$formelements[$elmKey]['required']=false;
						}

						/**DO set required flag on selected elements on self-pickup even if main required is false. ADDED in 2.10.2.1 **/
						if($isSelfPickup==1 && $elm['required_on_pickup']){
							$formelements[$elmKey]['required']=true;
						}
					}

					/**add invisible div to order page be be able to still add things to cart and reload (for upsells etc)**/
					add_action('wppizza_order_form_inside_top', array( $this, 'wppizza_invisible_cart'));


					/*check if the file exists in the theme, otherwise serve the file from the plugin directory if possible*/
					if ($template_file = locate_template( array ($this->pluginLocateDir.''.$this->pluginSlug.'-order.php' ))){
					include($template_file);
						return;
					}
					/*check if it exists in plugin directory, otherwise we will have to serve defaults**/
					if (is_file(''.WPPIZZA_PATH.'templates/'.$this->pluginSlug.'-order.php')){
						$template_file =''.WPPIZZA_PATH.'templates/'.$this->pluginSlug.'-order.php';
						include($template_file);
						return;
					}
				}else{
					/**shop closed->to stop still active sessions**/
					print"<div class='wpppizza-order-shopclosed'><p>".$cart['innercartinfo']."</p></div>";
				}
		}

		/***************************************
			[include orderhistory template]
		***************************************/
		if($type=='orderhistory'){
			/**just show the login form and return if user is not logged in**/
			if(!is_user_logged_in() ) {
				$this->wppizza_do_login_form(null, true);
				return;
			}else{
				global $current_user ;
				$current_user->ID;
				do_action('wppizza_get_orderhistory',$current_user->ID,$atts);
			}
		}

		/***************************************
			[include confirmation page template]
		***************************************/
		if($type=='confirmationpage'){

			/*******get the variables***/
			$options = $this->pluginOptions;
			$cart=wppizza_order_summary($_SESSION[$this->pluginSession],$options,$type);
			$cart = apply_filters('wppizza_filter_order_summary', $cart);

			/**check if tax was included in prices**/
			$taxIncluded=!empty($options['order']['taxes_included']) ? true : false;

			/**txt variables from settings->localization additional vars > localization_confirmation_form*/
			$localize = array_merge($options['localization'],$options['localization_confirmation_form']);
			$txt=array();
			foreach($localize as $k=>$v){
				$txt[$k]=$v['lbl'];
			}

			/**set session user vars as get vars to prefill form fields***/
			$userdata=array();
			if(isset($_SESSION[$this->pluginSessionGlobal]['userdata']) && is_array($_SESSION[$this->pluginSessionGlobal]['userdata'])){
				foreach($_SESSION[$this->pluginSessionGlobal]['userdata'] as $k=>$v){
					$userdata[$k]=$v;
				}
			}

			/******************************************
				output button and payment method
				and associated costs
			******************************************/
			$gwClass=new WPPIZZA_GATEWAYS;
			$getGateways=$gwClass->wppizza_instanciate_gateways_frontend();
			/**get wpml vars - should be done better one day using action hooks or something , but for the moment this will have to do**/
			$gwClass->wppizza_wpml_localization_gateways();

			/********************************************************
			*
			*
			*	output variables
			*
			*
			********************************************************/
			/************************************************
				link back to order page
			************************************************/
			$orderpagelink=$cart['orderpagelink'];
			/************************************************
				link to amend order
			************************************************/
			$amendorderlink=$cart['amendorderlink'];

			/************************************************
				order button
			************************************************/
			$orderbutton='';

			/*add gateway class to button**/
			$gwIdent=!empty($_SESSION[$this->pluginSession]['gateway-selected']['gw']) ? 'wppizza-ordernow-'.$_SESSION[$this->pluginSession]['gateway-selected']['gw'].'' : '';

			/**add required fields**/
			$addClass='';
			/**add class if customised to - for example - use an overlay **/
			if(!empty($atts['hasClassAjax'])){
				$addClass='class="wppizzaGwAjaxSubmit"';
			}
			if(!empty($atts['hasClassCustom'])){
				$addClass='class="wppizzaGwCustom"';
			}

			$orderbutton.='<input id="wppizza_hash" name="wppizza_hash" type="hidden" value="'.$atts['hash'].'"/>';
			$orderbutton.='<input id="wppizza-gateway" name="wppizza-gateway" '.$addClass.' type="hidden" value="'.$atts['gateway'].'"/>';
			$orderbutton.=$gwClass->wppizza_gateway_standard_button($txt['confirm_now_button'], $gwIdent);

			/************************************************
				formelements to confirm
				from settings->order form
			************************************************/
			$confirmationelements=array();
			foreach($options['confirmation_form'] as $elmKey=>$elm){
				if($elm['enabled']){
					$confirmationelements[$elmKey]=$elm;
				}
			}
			sort($confirmationelements);

			/************************************************
				submitted and filtered formelements
			************************************************/
			$formelements=$options['order_form'];
			$formelements = apply_filters('wppizza_filter_formfields_confirmation', $formelements);
			/**sort as appropriate***/
			$formelements = $this->wppizza_formfields_sort($formelements, $type);

			foreach($formelements as $k=>$oForm){
				$key=$oForm['key'];
				if($oForm['key']=='ctips' || !$oForm['enabled']){/***exclude disabled and tips (as those belong to order details)**/
					unset($formelements[$k]);
				}else{
					if($oForm['type']!='select'){
						$formelements[$k]['userVal']=!empty($userdata[$key]) ? $userdata[$key] :'';
					}else{
						$formelements[$k]['userVal']=!empty($oForm['value'][$userdata[$key]]) ? $oForm['value'][$userdata[$key]] :'';
					}
				}
			}

			/************************************************
				paymentmethod used
			************************************************/
			$gatewayLabel=strtoupper($atts['gateway']);
			$getwayUsedIdent=$gatewayLabel;
			if(isset($getGateways[$getwayUsedIdent])){
				$gatewayLabel=!empty($getGateways[$getwayUsedIdent]->gatewayName) ? $getGateways[$getwayUsedIdent]->gatewayName : $atts['gateway'];
				$gatewayLabel=!empty($getGateways[$getwayUsedIdent]->gatewayOptions['gateway_label']) ? $getGateways[$getwayUsedIdent]->gatewayOptions['gateway_label'] : $gatewayLabel;
			}

			/*************allow to add to paymentmethod by filter*****/
			$paymentmethod=array();
			$paymentmethod = apply_filters('wppizza_filter_paymentmethod_confirmation', $paymentmethod);
			sort($paymentmethod);
			foreach($paymentmethod as $k=>$oForm){
				$key=$oForm['key'];
				if($oForm['type']!='select'){
					$paymentmethod[$k]['userVal']=!empty($userdata[$key]) ? $userdata[$key] :'';
				}else{
					$paymentmethod[$k]['userVal']=!empty($oForm['value'][$userdata[$key]]) ? $oForm['value'][$userdata[$key]] :'';
				}
			}


			/***********************************************
				output what needs outputting
			***********************************************/
			/*check if the file exists in the theme, otherwise serve the file from the plugin directory if possible*/
			if ($template_file = locate_template( array ($this->pluginLocateDir.''.$this->pluginSlug.'-confirmation.php' ))){
				include($template_file);
				return;
			}
			/*check if it exists in plugin directory, otherwise we will have to serve defaults**/
			if (is_file(''.WPPIZZA_PATH.'templates/'.$this->pluginSlug.'-confirmation.php')){
				$template_file =''.WPPIZZA_PATH.'templates/'.$this->pluginSlug.'-confirmation.php';
				include($template_file);
				return;
			}

		}

		/*********************************************************
			[include search box (using shortcode/widget)]
			uses class to filter/add search variables
		*********************************************************/
		if($type=='search'){
			/**only display for logged in users**/
			if(isset($atts['loggedinonly']) && !is_user_logged_in()){
				return;
			}
			/**use class to filter/add search variables when using**/
			$searchvars = new WPPIZZA_SEARCH_VARS();
			$searchvars->atts = $atts;
			$searchvars->options = $this->pluginOptions;
			add_filter( 'get_search_form', array( $searchvars, 'searchvars' ) );/**add hidden wppizza input elm**/
			get_search_form();/*output - now altered - search form**/
			remove_filter('get_search_form',array( $searchvars, 'searchvars' ));//** reset to original or we will always have the post_type appended to the serach form once it has been run**/
			return;
		}

		/*********************************************************
			[additives]
		*********************************************************/
		if($type=='additives'){
			$options = $this->pluginOptions;
			$additives=$options['additives'];

			if(is_array($additives) && count($additives)>0){
				asort($additives);

				$str="<div class='wppizza_additives'>";
				foreach($additives as $k=>$additive){
					$str.="<span class='wppizza_additive_".$k." wppizza_additive'><span class='wppizza_additive_id'>".$k."</span><span class='wppizza_additive_name_".$k." wppizza_additive_name'>".$additive['name']."</span></span>";
				}
				$str.="</div>";

				echo $str;
			}
			return;
		}
	}


	/*******************************************************
     *
     *	add minicart on pages that have the cart to show
     *	if main cart is out of view (mobile/scrolling)
     *
     ******************************************************/
	function wppizza_mini_cart_if_cart_invisible(){
		static $c=0;$c++;
		/*really only once**/
		if($c==1){

		$options = $this->pluginOptions;
		/*checkout as button*/
		$atts['checkout']='button';
		/*add view cart button*/
		if(!empty($options['layout']['minicart_viewcart'])){
			$atts['viewcart']='1';
		}
		$atts = apply_filters('wppizza_filter_minicart_atts', $atts);
		$markup = wppizza_frontendTotals($options,$atts);

		/**if we have set some class or if**/
		$class=empty($options['layout']['minicart_add_to_element']) ? 'fixed' : 'relative';
		/**always visible**/
		$visibility=empty($options['layout']['minicart_always_shown']) ? '' : 'wppizza-mini-cart-static';

		echo'<div id="wppizza-mini-cart" class="wppizza-mini-cart-'.$class.' '.$visibility.'">'.$markup.'</div>';
		}
	}
	/*******************************************************
     *
     *	add hidden input/div  - when open - to orderpage
     *	to be be able to still add things to cart and reload (for upsells etc)
     *	even if no cart on page
     ******************************************************/
	function wppizza_invisible_cart(){
		echo'<span class="wppizza-open wppizza-cart" style="display:none"></span>';
	}
	/*******************************************************
     *
     *	[add a js function when using shortcode to display totals]
     *
     ******************************************************/
	function wppizza_filter_totals_js_cart_refresh_functions($array){
		$array[]='wppizzaShortcodeTotals';
		return $array;
	}

	function wppizza_additives_remap($meta){
		$convAdditives=array();
		if(isset($meta['additives']) && is_array($meta['additives'])){
		foreach($meta['additives'] as $o=>$a){
			if(is_array($this->pluginOptions['additives'][$o])){
				$thisAdditiveSort=$this->pluginOptions['additives'][$o]['sort'];
				if($thisAdditiveSort==''){$thisAdditiveSort=$o;}
				$thisAdditiveName=$this->pluginOptions['additives'][$o]['name'];

				$convAdditives[$thisAdditiveSort]=$thisAdditiveName;
			}else{/*in case we still have old vars*/
				$convAdditives[$o]=$a;
			}
		}
		}
		//ksort($convAdditives,SORT_NATURAL);//php 5.4 only
		uksort($convAdditives, 'strnatcmp');

		$meta['additives']=$convAdditives;
		return $meta;
	}
/*********************************************************
*
*		[include validation function]
*
*********************************************************/
public function wppizza_require_common_input_validation_functions(){
	require_once(WPPIZZA_PATH .'inc/common.input.validation.functions.inc.php');
}
/*********************************************************
*
*	[output formatting functions]
*
*********************************************************/
	public function wppizza_require_common_output_formatting_functions(){
		require_once(WPPIZZA_PATH .'inc/common.output.formatting.functions.inc.php');
	}
/*********************************************************
*
*	[common helper functions]
*
*********************************************************/
	public function wppizza_require_common_helper_functions(){
		require_once(WPPIZZA_PATH .'inc/common.helper.functions.inc.php');
	}
	/*******************************************************
		[register custom post type]
	******************************************************/
	public function wppizza_register_custom_posttypes(){
		$labels = array(
			'name'               => WPPIZZA_NAME.' '.__( 'Menu Items', 'wppizza-locale'),
			'singular_name'      => WPPIZZA_NAME.' '.__( 'Menu Item', 'wppizza-locale'),
			'add_new'            => __( 'Add New',  'wppizza-locale' ),
			'add_new_item'       => __( 'Add New Menu Item','wppizza-locale' ),
			'edit'				 => __( 'Edit', 'wppizza-locale' ),
			'edit_item'          => __( 'Edit Menu Item','wppizza-locale' ),
			'new_item'           => __( 'New Menu Item','wppizza-locale' ),
			'all_items'          => __( 'All Menu Items','wppizza-locale' ),
			'view'               => __( 'View', 'wppizza-locale' ),
			'view_item'          => __( 'View Menu Item','wppizza-locale' ),
			'search_items'       => __( 'Search Menu Items','wppizza-locale' ),
			'not_found'          => __( 'No items found','wppizza-locale' ),
			'not_found_in_trash' => __( 'No items found in the Trash','wppizza-locale' ),
			'parent_item_colon'  => '',
			'menu_name'          => ''.$this->pluginName.''
		);
		/**add a filter to labels if you want to...**/
		$labels = apply_filters('wppizza_cpt_lbls', $labels);

		$args = array(
			'labels'        => $labels,
			'description'   => sprintf( __( 'Holds %1$s  menu items data', 'wppizza-locale' ), $this->pluginName ),
			'show_ui'		=> true,
			'public'        => true,
			'menu_position' => 100,
			'menu_icon'		=> defined('WPPIZZA_MENU_ICON') ? WPPIZZA_MENU_ICON : plugins_url( 'img/pizza_16.png', $this->pluginPath ),
			'has_archive'   => false,
			'hierarchical'	=> false,
			'supports'      => array( 'title', 'editor', 'author','thumbnail','page-attributes','comments'),
			'taxonomies'    => array('') /* 'post_tag' for example*/
		);

		/**add a filter to arguments if you want to**/
		$args = apply_filters('wppizza_cpt_args', $args);

		register_post_type( $this->pluginSlug, $args );
	}


	/*******************************************************
		[register taxonomy + taxonomy related functions]
	******************************************************/
	public function wppizza_register_custom_taxonomies(){
		$options = $this->pluginOptions;

		/**********************
			when using permalinks, we can either set the
			parent to be a dedicated page (admin->settings)
			.........
		***********************/
		/**get the right one when using wpml**/
		if(function_exists('icl_object_id') && $options!=0) {
			$options['plugin_data']['category_parent_page']=icl_object_id($options['plugin_data']['category_parent_page'],'page');
		}
		$sel_category_parent=get_post($options['plugin_data']['category_parent_page'],ARRAY_A);/*orig*/
		/**********************
		........or use/set a default
		(required as other pages wont work without it when permalinked
		**********************/
		if($sel_category_parent['post_name']==''){
			$sel_category_parent['post_name']=WPPIZZA_TAXONOMY;
		}

		  // Add new taxonomy, make it hierarchical (like categories)
		  $labels = array(
		    'name' => WPPIZZA_NAME. ' ' ._x( 'Categories', 'taxonomy general name' ),
		    'singular_name' => _x( 'Category', 'taxonomy singular name' ),
		    'search_items' =>  __( 'Search Categories' ),
		    'all_items' => __( 'All Categories' ),
		    'parent_item' => __( 'Parent Category' ),
		    'parent_item_colon' => __( 'Parent Category:' ),
		    'edit_item' => __( 'Edit Category' ),
		    'update_item' => __( 'Update Category' ),
		    'add_new_item' => __( 'Add New Category' ),
		    'new_item_name' => __( 'New Category Name' ),
		    'menu_name' => __( 'Categories' )
		  );
		  register_taxonomy(WPPIZZA_TAXONOMY,array($this->pluginSlug), array(
		    'hierarchical' => true,
		    'labels' => $labels,
		    'show_ui' => true,
		    'show_admin_column' => true,
		    'query_var' => true,
		    'rewrite' => array( 'slug' => ''.$sel_category_parent['post_name'].'','hierarchical'=>true )
		  ));
	}
	/*******************************************************
		[rewrite single item slug/permalink]
	******************************************************/
	function wppizza_single_item_permalink_rewrite($args){
		if(isset($this->pluginOptions['plugin_data']['single_item_permalink_rewrite']) && $this->pluginOptions['plugin_data']['single_item_permalink_rewrite']!=''){
			/**change single item post slug from wppizza to selected slug**/
			$args['rewrite'] = array( 'slug' => sprintf( __( '%s', 'wppizza-locale' ), $this->pluginOptions['plugin_data']['single_item_permalink_rewrite'] ) );
		}
		return $args;
	}

	/*******************************************************
		[set search box queries ]
	******************************************************/
	function wppizza_set_search_query( $query ) {
    	if (is_search() && $query->is_main_query()) {

			/*******************************************************************
				exclude wppizza cpt from search results if not enabled

				furthermore, if no post_type var has been set in query, set all other used ones
				if they HAVE been set, we should not need to do anything
				as the wppizza cpt will be specifically set/added or not
			*******************************************************************/
			if(!$this->pluginOptions['plugin_data']['search_include'] && !isset($_REQUEST['post_type'])){
				if(!isset($query->query_vars['post_type']) ){
					/**get all queryable and exclude/unset wppizza***/
					$post_types = get_post_types( array('public' => true,'exclude_from_search' => false), 'names' );
					unset($post_types[WPPIZZA_POST_TYPE]);
					$query->set('post_type',$post_types);
				}
			}
			/**post types set when using shortcodes/widget etc**/
			if(isset($_REQUEST['post_type'])){
				$request_types=explode(",",$_REQUEST['post_type']);
				/**if we have set another permalink for single mnu items, rewrite this here so the query finds wppizza after all**/
				if(isset($this->pluginOptions['plugin_data']['single_item_permalink_rewrite']) && $this->pluginOptions['plugin_data']['single_item_permalink_rewrite']!='' && in_array($this->pluginOptions['plugin_data']['single_item_permalink_rewrite'],$request_types)){
					$key = array_search($this->pluginOptions['plugin_data']['single_item_permalink_rewrite'], $request_types);
					$request_types[$key]=WPPIZZA_POST_TYPE;
				}
				/**get all queryable and get intersection just to be tidy and stop people from entering random query vars***/
				$post_types = get_post_types( array('public' => true,'exclude_from_search' => false), 'names' );
				$post_types_array = array_intersect($request_types, $post_types);
				$query->set( 'post_type', $post_types_array);


    			$query = apply_filters('wppizza_filter_search', $query);
			}
    	}


    	return $query;
	}
	/*******************************************************
		[wppizza modify permalink in search results to use
		wppizza loop template when clicking on link instead
		of normal blog layout - only used when no proper single-wppizza.php
		template is in use]
	******************************************************/
	function wppizza_search_results_permalink($url) {
		if(is_search() && get_post_type()==WPPIZZA_POST_TYPE){
			/*get slug**/
			$post_data = get_post(get_the_ID(), ARRAY_A);
			$slug = $post_data['post_name'];
			/*set args**/
    		$args=array();
    		$args['page_id']=$this->pluginOptions['plugin_data']['post_single_template'];/*use selected page to display things in to keep layout*/
    		$args['wppizza']=false;
    		$args[WPPIZZA_SINGLE_PERMALINK_VAR]=''.$slug.'';//'.$slug.'
    		/**amend permalink**/
    		return esc_url_raw(add_query_arg($args, $url));
		}else{
			return	$url;
		}
	}

	/***************************************************************
		filter loop when using single item (for example a link from
		searchresults) to only display this single item
		if not using dedicated single-wppizza.php template
	****************************************************************/
	function wppizza_single_items($query){/**add relevant filters*/
		global $post;
		if(isset($_GET[WPPIZZA_SINGLE_PERMALINK_VAR]) && $query->is_main_query()){
		add_filter('wppizza_filter_loop', array( $this, 'wppizza_filter_single_item'),10,1);
		add_filter('the_content', array( $this, 'wppizza_show_single_item'),0);
		}
	}
	/**run the filter on loop query **/
	function wppizza_filter_single_item($args){
		if(isset($_GET[WPPIZZA_SINGLE_PERMALINK_VAR])){
			$args=array(
			  'name' => $_GET[WPPIZZA_SINGLE_PERMALINK_VAR],
			  'post_type' => WPPIZZA_POST_TYPE,
			  'post_status' => 'publish',
			  'numberposts' => 1
			);
			$menuItem = get_posts($args);
			if( $menuItem ) {
				$args['post__in']=array($menuItem[0]->ID);
				unset($args['tax_query']);
			}
		}
		return $args;
	}
	/*filter content*/
	function  wppizza_show_single_item($content){
		global $post;
		/*replace the content od this page with relevant shortcode*/
		if($post->ID==$this->pluginOptions['plugin_data']['post_single_template']){
			ob_start();
			$content='';
	        echo do_shortcode( '[wppizza noheader=1]' );/*no need to add any other atts as the query takes care of the rest*/
			$content = ob_get_clean();
			return $content;
		}else{
			return $content;
		}
	}
	/********************************************************
		[lets attempt to get rid of WPPizza Categories in title tag
	*********************************************************/
	function wppizza_filter_title_tag($title, $sep=false , $loc='right'){
		if(get_post_type()==WPPIZZA_POST_TYPE){
				$titleOrig=$title;

			/**for safeties sake loop through all conotations (though the last one probanly does the trick) */
			$catTitleSearch[]=__('WPPizza Categories','wppizza-locale');
			$catTitleSearch[]=__('Categories WPPizza','wppizza-locale');
			$catTitleSearch[]=WPPIZZA_NAME. ' '.__('Categories');

			foreach($catTitleSearch as $strSearch){

				if($sep && $loc=='right'){
					$title=str_ireplace(''.$strSearch.' '.$sep.'','',$title);
				}
				if($sep && $loc!='right'){
					$title=str_ireplace(''.$sep.' '.$strSearch.'','',$title);
				}
				/*if we dont have a seperator or nothing has been done yet and its still the same, just try a normal str replace*/
				if(!$sep || trim($sep)=='' || $title==$titleOrig){
					$title=str_ireplace($strSearch,'',$title);
				}
				/**as last resort if it's still in the title somehow***/
				$pos = stripos($title, $strSearch);
				if ($pos !== false) {
	    			$title=str_ireplace($strSearch,'',$title);
	    			/*and - just to be sure - replace any leftover double seperators with single ones**/
	    			$title=str_replace($sep.$sep,$sep,$title);
				}

				/**might as well trim it*/
				$title=trim($title);
			}

		}

		return $title;
	}
	/*******************************************************
		[start session]
	******************************************************/
	function wppizza_init_sessions() {
	    if (!session_id()) {session_start();}
	    /*initialize if not set*/
	    if(!isset($_SESSION[$this->pluginSession])){
	    	/*holds items in cart*/
	    	$_SESSION[$this->pluginSession]['items']=array();
	    	/*gross sum of all items in cart,before discounts etc*/
	    	$_SESSION[$this->pluginSession]['total_price_items']=0;
	    	/*
	    		note to self: session ini vars to make filterable in v3.0
	    	*/
	    	/*ini to self pickup if set */
	    	$options = $this->pluginOptions;
	    	if(!empty($options['order']['order_pickup']) && !empty($options['order']['order_pickup_as_default'])){
	    		$_SESSION[$this->pluginSession]['selfPickup']=1;
	    	}
	    }
	    if(!isset($_SESSION[$this->pluginSessionGlobal])){
	    	/**userdata like address etc*****/
	    	$_SESSION[$this->pluginSessionGlobal]=array();
	    }
	}
/*********************************************************
*
*	[include send order emails class FOR OLD LEGACY GATEWAYS]
*
*********************************************************/
	function wppizza_send_order_emails() {
		require_once(WPPIZZA_PATH .'classes/wppizza.send-order-emails.deprecated.inc.php');
	}
/***********************************************************************************************
*
* 	[template functions - include the relevant templates depending on shortcode/widget type and atts]
*	[if there's a copy of the template in the current theme folder use that one,
*	otherwise use the one in plugin template directory]
*
************************************************************************************************/
	/**set possibly missing vars if using templates **/
	function wppizza_loop_include_vars($options){
		if(!$options){
			$options=$this->pluginOptions;
				$mapAdditives=array();
				if(isset($options['additives']) && is_array($options['additives'])){
					foreach($options['additives'] as $o=>$a){
						if(is_array($a)){
							if($a['sort']===''){$a['sort']=$o;}
							$mapAdditives[$a['sort']]=$a['name'];
						}else{
							/**in case we have not yet re-saved the additives**/
							$mapAdditives[$o]=$a;
						}
					}
				}
				//ksort($mapAdditives,SORT_NATURAL);//php 5.4 only
				uksort($mapAdditives, 'strnatcmp');

				$options['additives']=$mapAdditives;

		}
		/*******re-map additives inside loop too **************************/
		add_filter('wppizza_filter_loop_meta', array( $this, 'wppizza_additives_remap'),10,1);


		return $options;
	}
    /*****************************************************
    * reset loop query. some themes need this
    ******************************************************/
	function wppizza_reset_loop_query(){
		wp_reset_postdata();
	}
    /*****************************************************
     * Wrapper template when displying items in custom post type category
     * [see header of templates/wppizza-wrapper.php for details]
     ******************************************************/
	public function wppizza_include_template($template_path){
		/******list of all items in this particular taxonomy category(term), provided ist not a search query******/
		if ( get_post_type() == $this->pluginSlug && !is_search()) {
			$post_type=get_post_type();
			$options = $this->pluginOptions;

			/*********************************************************************
				[as we have changed in v.2.8.7.4 to have the additives as array
				so we can custom sort them but dont really want to screw up
				anyones edited templates, re-map key and name]
			*******************************************************************/
				$mapAdditives=array();
				if(isset($options['additives']) && is_array($options['additives'])){
					foreach($options['additives'] as $o=>$a){
						if(is_array($a)){
							if($a['sort']==''){$a['sort']=$o;}
							$mapAdditives[$a['sort']]=$a['name'];
						}else{
							/**in case we have not yet re-saved the additives**/
							$mapAdditives[$o]=$a;
						}
					}
				}
				//ksort($mapAdditives,SORT_NATURAL);//php 5.4 only
				uksort($mapAdditives, 'strnatcmp');

				$options['additives']=$mapAdditives;

			/*exclude header*/
			if($options['layout']['suppress_loop_headers']){
				$noheader=1;
			}
			/**if we want to capture the category id a menu item is currently in **/
			/**DEPRECATED ->this is not required anymore, but we'll leave it here in case someone is using an old customised loop template that wants this variable**/
			if($options['layout']['items_group_sort_print_by_category']){
				$getSlugDetails=1;
			}
			if ( !is_single() ) {
				/*check if the file exists in the theme, otherwise serve the file from the plugin directory if possible*/
				if ($theme_file = locate_template( array ($this->pluginLocateDir.'wppizza-wrapper.php' ))){
					include($theme_file);
					return;
				}
				/*check if it exists in plugin directory, otherwise we will have to serve defaults**/
				if (is_file(''.WPPIZZA_PATH.'templates/wppizza-wrapper.php' )){
					$theme_file=''.WPPIZZA_PATH.'templates/wppizza-wrapper.php';
					include($theme_file);
					return;
				}
			}
		}
		return $template_path;
	}
    /*****************************************************
     * Use loop template when displying SINGLE ITEMS in custom post type category
     * [see header of templates/wppizza-single.php for details]
     ******************************************************/
	function wppizza_filter_loop($args,$args2=null){
		if(is_single() && get_post_type()==WPPIZZA_POST_TYPE){
			global $post;

			$args['p']=$post->ID;
			$catTerms = get_the_terms($post->ID, WPPIZZA_TAXONOMY);
			if ( $catTerms && ! is_wp_error( $catTerms ) ){
				$firstCat=reset($catTerms);
				$args['tax_query'][0]['terms']=$firstCat->slug;
			}
			$args['posts_per_page']=1;
			$args['max_num_pages']=-1;
		}
		return $args;
	}

/***********************************************************************************************
*
*
*	[Register and Enqueue scripts and styles]
*
*
************************************************************************************************/
    /**************
     	[Admin]
	***************/
    public function wppizza_register_scripts_and_styles_admin($hook) {
        if(is_admin()) {
            global $current_screen, $wp_styles;

            /**css**/
            	if (file_exists( $this->pluginTemplateDir . '/wppizza-admin.css')){
					/**copy stylesheet to template directory to keep settings**/
					wp_register_style($this->pluginSlug.'-admin', $this->pluginTemplateUri.'/wppizza-admin.css', array(), $this->pluginVersion);
            	}else{
					wp_register_style($this->pluginSlug.'-admin', plugins_url( 'css/styles-admin.css',$this->pluginPath), array(), $this->pluginVersion);
            	}
				/**if we want to keep all the original css (including future changes) but only want to overwrite some lines , add wppizza-admin-custom.css to your template directory*/
				if (file_exists( $this->pluginTemplateDir . '/wppizza-admin-custom.css')){
					wp_register_style($this->pluginSlug.'-admin-custom', $this->pluginTemplateUri.'/wppizza-admin-custom.css', array(''.$this->pluginSlug.'-admin'), $this->pluginVersion);
					wp_enqueue_style($this->pluginSlug.'-admin-custom');
				}
				/**for timepicker, chosen etc*/
				if(get_current_screen()->post_type==$this->pluginSlug){
					wp_enqueue_style('jquery-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/themes/smoothness/jquery-ui.css');
					/**chosen**/
        			wp_register_style($this->pluginSlug.'-chosen', plugins_url( 'css/wppizza-chosen.min.css', $this->pluginPath ), array(), $this->pluginVersion);
        			wp_enqueue_style($this->pluginSlug.'-chosen');
				}
 				wp_enqueue_style($this->pluginSlug.'-admin');

      		/**js***/
      			/**thickbox on order history**/
      			if($current_screen->id==''.$this->pluginSlug.'_page_'.$this->pluginSlug.'-order-history'){
      				add_thickbox();
      			}

      			/**include reporting js**/
      			if($current_screen->id==''.$this->pluginSlug.'_page_'.$this->pluginSlug.'-reports'){
      				wp_register_script($this->pluginSlug.'-flot', plugins_url( 'js/jquery.flot.min.js', $this->pluginPath ), array('jquery'), $this->pluginVersion ,true);
      				wp_enqueue_script($this->pluginSlug.'-flot');
      				wp_register_script($this->pluginSlug.'-flotcats', plugins_url( 'js/jquery.flot.categories.min.js', $this->pluginPath ), array('jquery'), $this->pluginVersion ,true);
      				wp_enqueue_script($this->pluginSlug.'-flotcats');
      			}
      			/**only include on wppizza post type**/
				if($current_screen->post_type==$this->pluginSlug){
	      			wp_enqueue_script('jquery-ui-sortable');
            		wp_enqueue_script('jquery-ui-datepicker');
            		/**
            			at some point, we should probably call this $this->pluginSlug.'-admin' and then wp_enqueue_script($this->pluginSlug.'-admin');
            			however, at the moment, other plugins (might) depend on it being just $this->pluginSlug , so lets keep it as is for now
            			until wppizza 3.0 or so
            		**/
            		wp_register_script($this->pluginSlug.'', plugins_url( 'js/scripts.admin.min.js', $this->pluginPath ), array('jquery'), $this->pluginVersion ,true);
            		wp_register_script($this->pluginSlug.'-timepick', plugins_url( 'js/jquery.ui.timepicker.js', $this->pluginPath ), array('jquery'), $this->pluginVersion ,true);
            		wp_register_script($this->pluginSlug.'-chosen', plugins_url( 'js/chosen.jquery.min.js', $this->pluginPath ), array('jquery'), $this->pluginVersion ,true);

            		wp_enqueue_script($this->pluginSlug.'');
            		wp_enqueue_script($this->pluginSlug.'-timepick');
            		wp_enqueue_script($this->pluginSlug.'-chosen');

				}
				/**include everywhere (expecially widget pages)*/
				wp_register_script($this->pluginSlug.'-global', plugins_url( 'js/scripts.admin.global.js', $this->pluginPath ), array('jquery'), $this->pluginVersion ,true);
				wp_enqueue_script($this->pluginSlug.'-global');
        }
    }
    /**************

     	[Frontend]

	***************/

    /*some experimental things that might or might not make it into the core, depending on the outcome*/
    public function wppizza_load_experimental_scripts_and_styles() {
    	/**EXPERIMENTAL JS - currently empty/unused**/
    	if(isset($this->pluginOptions['plugin_data']['experimental_js']) && $this->pluginOptions['plugin_data']['experimental_js']){
    		wp_register_script($this->pluginSlug.'-exp', plugins_url( 'js/scripts.exp.min.js', $this->pluginPath ), array('jquery'), $this->pluginVersion ,$this->pluginOptions['plugin_data']['js_in_footer']);
    		wp_enqueue_script($this->pluginSlug.'-exp');
    	}
    }

    public function wppizza_register_scripts_and_styles($hook) {
    	global $wp_scripts,$post;
		$options = $this->pluginOptions;

    	/**************
    		css
    	**************/
		if($options['layout']['include_css']){
			/*gridbased ?*/
			$doGridCss=false;
			if($options['layout']['style']==='grid'){
				/**load responsive css plus grid css which only overwrites needed declarations*/
				$options['layout']['style']='responsive';
				/**set flag**/
				$doGridCss=true;
			}
			if (file_exists( $this->pluginTemplateDir . '/wppizza-'.$options['layout']['style'].'.css')){
				/**copy stylesheet to template directory to keep settings**/
				wp_register_style($this->pluginSlug, $this->pluginTemplateUri.'/wppizza-'.$options['layout']['style'].'.css', array(), $this->pluginVersion);
			}else{
				wp_register_style($this->pluginSlug, plugins_url( 'css/wppizza-'.$options['layout']['style'].'.css', $this->pluginPath ), array(), $this->pluginVersion);
			}
			wp_enqueue_style($this->pluginSlug);

			/**load grid css if required**/
			if($doGridCss){
				/*grid based layouts set parameters - using php to calculate within css*/
				$gridParameters='?grid='.$options['layout']['style_grid_columns'].'-'.$options['layout']['style_grid_margins'].'-'.$options['layout']['style_grid_full_width'].'';
				if (file_exists( $this->pluginTemplateDir . '/wppizza-grid.css')){
					/**copy stylesheet to template directory to keep settings**/
					wp_register_style($this->pluginSlug.'-grid', $this->pluginTemplateUri.'/wppizza-grid.css.php'.$gridParameters, array($this->pluginSlug), $this->pluginVersion);
				}else{
					wp_register_style($this->pluginSlug.'-grid', plugins_url( 'css/wppizza-grid.css.php'.$gridParameters, $this->pluginPath ), array($this->pluginSlug), $this->pluginVersion);
				}
				wp_enqueue_style($this->pluginSlug.'-grid');
			}
		}

		/**pretty photo css**/
		if($options['layout']['prettyPhoto']){
			wp_register_style($this->pluginSlug.'-prettyPhoto', plugins_url( 'css/wppizza-prettyPhoto.css', $this->pluginPath ), array(), $this->pluginVersion);
			wp_enqueue_style($this->pluginSlug.'-prettyPhoto');
		}

		if($options['layout']['include_css']){
			/**if we want to keep all the original css (including future changes) but only want to overwrite some lines , add wppizza-custom.css to your template directory*/
			if (file_exists( $this->pluginTemplateDir . '/wppizza-custom.css')){
				wp_register_style($this->pluginSlug.'-custom', $this->pluginTemplateUri.'/wppizza-custom.css', array(''.$this->pluginSlug.''), $this->pluginVersion);
				wp_enqueue_style($this->pluginSlug.'-custom');
			}
		}
		/**include spinner css on orderpage if enabled**/
		if($options['layout']['order_page_quantity_change'] && $options['layout']['order_page_quantity_change_style']!='' && ( $options['order']['orderpage']==get_the_ID() || $options['plugin_data']['always_load_all_scripts_and_styles'] ) ){
			$ui = $wp_scripts->query('jquery-ui-spinner');
			wp_enqueue_style('jquery-ui-'.$options['layout']['order_page_quantity_change_style'].'', "//ajax.googleapis.com/ajax/libs/jqueryui/".$ui->ver."/themes/".$options['layout']['order_page_quantity_change_style']."/jquery-ui.min.css", false, null);
		}
		/****************
			js
		****************/
		/**include spinner js on orderpage if enabled**/
		if($options['layout']['order_page_quantity_change'] && ( $options['order']['orderpage']==get_the_ID() || $options['plugin_data']['always_load_all_scripts_and_styles'] ) ){
			wp_enqueue_script("jquery-ui-spinner");
		}
		/*only load easing if necessary**/
		if(!in_array($options['layout']['sticky_cart_animation_style'],array('','swing','linear')) && $options['layout']['sticky_cart_animation']>0){
			wp_enqueue_script("jquery-effects-core");
		}

		/*main js**/
    	wp_register_script($this->pluginSlug, plugins_url( 'js/scripts.min.js', $this->pluginPath ), array('jquery'), $this->pluginVersion ,$options['plugin_data']['js_in_footer']);
    	wp_enqueue_script($this->pluginSlug);

		/*validation*/
    	wp_register_script($this->pluginSlug.'-validate', plugins_url( 'js/jquery.validate.min.js', $this->pluginPath ), array($this->pluginSlug), $this->pluginVersion ,$options['plugin_data']['js_in_footer']);
    	wp_enqueue_script($this->pluginSlug.'-validate');

    	/**pretty photo**/
    	if($options['layout']['prettyPhoto']){
    		wp_register_script($this->pluginSlug.'-prettyPhoto', plugins_url( 'js/jquery.prettyPhoto.js', $this->pluginPath ), array('jquery'), $this->pluginVersion ,$options['plugin_data']['js_in_footer']);
    		wp_enqueue_script($this->pluginSlug.'-prettyPhoto');
    		/**copy js to template directory to edit settings (theme etc)**/
    		if (file_exists( $this->pluginTemplateDir . '/wppizza.prettyPhoto.custom.js')){
	    		wp_register_script($this->pluginSlug.'-ppCustom', $this->pluginTemplateUri.'/wppizza.prettyPhoto.custom.js', array('jquery'), $this->pluginVersion ,$options['plugin_data']['js_in_footer']);
    		}else{
	    		wp_register_script($this->pluginSlug.'-ppCustom', plugins_url( 'js/wppizza.prettyPhoto.custom.js.php?t='.$options['layout']['prettyPhotoStyle'].'', $this->pluginPath ), array('jquery'), $this->pluginVersion ,$options['plugin_data']['js_in_footer']);
    		}
    		wp_enqueue_script($this->pluginSlug.'-ppCustom');
    	}


    	/**localized js***/
		wp_enqueue_script( $this->pluginSlug );
		$jsMessages=array();
		$jsMessages['closed']=''.$options['localization']['alert_closed']['lbl'].'';
		if($options['layout']['add_to_cart_on_title_click']){
			$jsMessages['choosesize']=''.$options['localization']['alert_choose_size']['lbl'].'';
		}
		if($options['order']['order_pickup'] && $options['order']['order_pickup_alert'] && empty($options['order']['order_pickup_as_default'])){//
			$jsMessages['pickup']=''.$options['localization']['order_self_pickup_cart_js']['lbl'].'';
		}
		/*switch to using delivery message if using pickup as default*/
		if($options['order']['order_pickup'] && $options['order']['order_pickup_alert'] && !empty($options['order']['order_pickup_as_default'])){//
			$jsMessages['pickup']=''.$options['localization']['order_delivery_cart_js']['lbl'].'';
		}

		if($options['order']['order_pickup'] && $options['order']['order_pickup_alert_confirm'] ){
			$localized_options['pickupConfirm']=1;
		}

		/*add functions (names) to run when cart has been refreshed**/
		$jsCartRefreshCompleteFunctions['functionsCartRefresh']=array();
		$jsCartRefreshCompleteFunctions['functionsCartRefresh'] = apply_filters('wppizza_filter_js_cart_refresh_functions', $jsCartRefreshCompleteFunctions['functionsCartRefresh']);
		$jsCartRefreshCompleteFunctions['functionsCartRefresh'] = array_keys(array_flip($jsCartRefreshCompleteFunctions['functionsCartRefresh']));/*flip to make unique, keys to just get the function name to sanitise things*/

		/**allow adding of veriables for extending plugins**/
		$jsExtend['jsExtend']=array();
		$jsExtend['jsExtend'] = apply_filters('wppizza_filter_js_extend', $jsExtend['jsExtend']);

		/**in case force_ssl_admin is set */
		$ajaxUrl = admin_url('admin-ajax.php');
		if ( force_ssl_admin() &&  !is_ssl() ) {
			$ajaxUrl = set_url_scheme($ajaxUrl, 'http');
		}
		$localized_array = array( 'ajaxurl' =>$ajaxUrl, 'validate_error'=>array('email'=>''.$options['localization']['required_field_email']['lbl'].'','required'=>''.$options['localization']['required_field']['lbl'].'','decimal'=>''.$options['localization']['required_field_decimal']['lbl'].''),'msg'=>$jsMessages,'funcCartRefr'=>$jsCartRefreshCompleteFunctions['functionsCartRefresh'],'extend'=>$jsExtend['jsExtend']);
		/**are we using a cache plugin ?**/
		if($options['plugin_data']['using_cache_plugin']){
			$localized_array['usingCache']=1;
		}
		/**set flag to indicate we are on checkout page**/
			/**wpml select of order page**/
			if(function_exists('icl_object_id')) {
				$options['order']['orderpage']=icl_object_id($options['order']['orderpage'],'page');
			}
			/**set flag that we are on order page to not do any redirection for example**/
			if(is_object($post) && $post->ID==$options['order']['orderpage']){
				$localized_array['isCheckout']=1;
			}

		/**are we using a confirmation form too ?**/
		if($options['confirmation_form_enabled']){
			$localized_array['cfrm']=1;
		}
		/**do we want to be able to still change quantities on order page**/
		if($options['layout']['order_page_quantity_change'] && ( $options['order']['orderpage']==get_the_ID() || $options['plugin_data']['always_load_all_scripts_and_styles'] ) ){
			$localized_array['ofqc']=1;
		}

		/***various options**/
		if(isset($localized_options)){
			$localized_array['opt']=$localized_options;
		}

		/**sticky cart settings**/
			$localized_array['crt']=array();

			$localized_array['crt']['anim']=$options['layout']['sticky_cart_animation'];
			$localized_array['crt']['fx']=$options['layout']['sticky_cart_animation_style'];
			$localized_array['crt']['mt']=$options['layout']['sticky_cart_margin_top'];
			$localized_array['crt']['bg']=$options['layout']['sticky_cart_background'];
			if($options['layout']['sticky_cart_limit_bottom_elm_id']!=''){
			$localized_array['crt']['lmtb']=$options['layout']['sticky_cart_limit_bottom_elm_id'];
			}
			/**minicart max width**/
			if($options['layout']['minicart_max_width_active']>0){
				$localized_array['crt']['mCartMaxWidth']=$options['layout']['minicart_max_width_active'];
			}
			/**minicart body padding top**/
			if($options['layout']['minicart_elm_padding_top']>0){
				$localized_array['crt']['mCartPadTop']=$options['layout']['minicart_elm_padding_top'];
				/**minicart padding to distinct element**/
				if($options['layout']['minicart_elm_padding_selector']!=''){
					$localized_array['crt']['mCartPadElm']=$options['layout']['minicart_elm_padding_selector'];
				}
			}


			/**minicart add to element**/
			if($options['layout']['minicart_add_to_element']!=''){
				$localized_array['crt']['mCartElm']=$options['layout']['minicart_add_to_element'];
			}
			/**minicart always displayed**/
			if(!empty($options['layout']['minicart_always_shown'])){
				$localized_array['crt']['mCartStatic']=1;
			}

			if($options['layout']['jquery_fb_add_to_cart']!=''){
				$localized_array['itm']['fbatc']=$options['localization']['jquery_fb_add_to_cart_info']['lbl'];
				$localized_array['itm']['fbatcms']=$options['layout']['jquery_fb_add_to_cart_ms'];
			}

			/** 
				js decode and escape messages and validation errors 
			**/
			foreach($localized_array['msg'] as $jsmKey => $jsMessage){
				$localized_array['msg'][$jsmKey] = wppizza_decode_entities($jsMessage, WPPIZZA_CHARSET);	
			}
			/** js escape messages and validation errors */
			foreach($localized_array['validate_error'] as $jsmKey => $jsMessage){
				$localized_array['validate_error'][$jsmKey] = wppizza_decode_entities($jsMessage, WPPIZZA_CHARSET);	
			}	

		wp_localize_script( $this->pluginSlug,$this->pluginSlug, $localized_array );

    }
	function wppizza_dequeue_scripts(){
		if(isset($this->pluginOptions['plugin_data']['dequeue_scripts']) && $this->pluginOptions['plugin_data']['dequeue_scripts']!=''){
			/*dequeue main*/
			if($this->pluginOptions['plugin_data']['dequeue_scripts']=='all'){
				wp_dequeue_script($this->pluginSlug);
			}
			/*dequeue jquery validate too or only*/
			if($this->pluginOptions['plugin_data']['dequeue_scripts']=='all' || $this->pluginOptions['plugin_data']['dequeue_scripts']=='validation'){
				wp_dequeue_script($this->pluginSlug.'-validate');
			}
		}
	}
/*********************************************************
*
*		[add filter and filter related functions]
*
*********************************************************/
	public function wppizza_exclude_order_page_from_navigation($pages) {
		if($this->pluginOptions['order']['orderpage_exclude']){
			$pageCount = count($pages);
			for ( $i=0; $i<$pageCount; $i++ ) {
				$page = & $pages[$i];

				/**wpml select of order page**/
				if(function_exists('icl_object_id')) {
					$this->pluginOptions['order']['orderpage']=icl_object_id($this->pluginOptions['order']['orderpage'],'page');
				}

				if ($page->ID==$this->pluginOptions['order']['orderpage']) {
					unset( $pages[$i] );/*unset the order page*/
				}
			}
			if ( ! is_array( $pages ) ) $pages = (array) $pages;
			$pages = array_values( $pages );
		}
		return $pages;
	}

	/*********************************************************
	*
	*	[display categories in order saved in options]
	*
	*********************************************************/
		/*****************************************************
			add get term filter where and as required
		*****************************************************/
		function wppizza_add_get_terms_filter() {
			/*do not run when deleting tags via bulk actions*/
			if(empty($_POST['delete_tags'])){
				add_filter('get_terms', array($this,'wppizza_do_sort_custom_posts_category'), 10, 2);
			}
		}
		/*****************************************************
			return wppizza category sort order according to
			custom sorting sort order
		*****************************************************/
		function wppizza_do_sort_custom_posts_category($terms, $taxonomy) {
			/*should be bypassed when creating/deleting categories (i.e when there's a post[action])**/
			if(isset($_GET['taxonomy']) && $_GET['taxonomy']==WPPIZZA_TAXONOMY && in_array(WPPIZZA_TAXONOMY,$taxonomy)){
				$termArray=array();
				foreach($terms as $k=>$term){
					if(is_object($term)){
					$key=$this->pluginOptions['layout']['category_sort_hierarchy'][$term->term_id];
					$termArray[$key]=$term;
					}
				}
				ksort($termArray);

				return	$termArray;
			}else{
				return	$terms;
			}
		}
		/*************************************************************************************************
			[get fully sorted WP recursive hierarchy (sorted by WP default -> name asc)
			of wppizza categories and subcategories in a flat array with key=>categoryId,  val=>sortorder]
			(if $custom_sort=false and $full_details=false)

			if $custom_sort=array() - typically set 'category_sort_hierarchy' variable OR $full_details=true),
			it will returns full category object
			(currently unused)

			wppizza_do_sort_custom_posts_category can be used to return categories
			in custom set order set in ['category_sort_hierarchy']

		*************************************************************************************************/
		function wppizza_get_cats_hierarchy($custom_sort=false, $full_details=false){

			/*custom sort set*/
			if(!empty($custom_sort)){$full_details=true;}

			/*ini sort order*/
			$sort=0;
			/*ini results array*/
			$cats_in_order=array();
			/*run query, gatting all wppizza parent categories*/
			$args=array('orderby' => 'name', 'order' => 'ASC', 'hide_empty' => 0, 'parent' => 0, 'taxonomy' => WPPIZZA_TAXONOMY	);
			$default_sort_cats = get_categories($args);
			$wpml_sort_cats_lang = array();/*categories for all other non current languages if WPML category translation*/

			/**if site is WPML  enabled, get_categories only ever returns cats of currently active language , so let's get all others too*/
			if(function_exists('icl_get_languages')){
				global $sitepress;
				$languages = icl_get_languages();
				$current_language = $sitepress->get_current_language();
				/**loop through non current languages to get categories**/
				foreach($languages as $lang_code=>$lang_arr){
					if($lang_code!=ICL_LANGUAGE_CODE){
						$sitepress->switch_lang($lang_code);
						/*get cats for that language**/
						$wpml_sort_cats_lang[$lang_code] = get_categories($args);
					}
				}
				/**make sure we switch back to current language when done**/
				$sitepress->switch_lang(ICL_LANGUAGE_CODE);
			}

			/**loop through parent cats - non wpml or if wpml , current language*/
			foreach($default_sort_cats as $cat){

				/**add parent to results array*/
				if($full_details){
					$cats_in_order[$sort]= array('sort'=>$sort, 'id'=>$cat->term_id, 'parent'=>$cat->parent, 'name'=>$cat->name);
				}else{
					$cats_in_order[$cat->term_id]= $sort;
				}
				$sort++;/*advance sorter*/

				/**get subcategory tree for ththis parent**/
				$get_category_tree= $this->wppizza_cat_tree_recursive( $cat->term_id, $sort, $full_details);

				if(!empty($get_category_tree)){
					/*add full tree to parent cat*/
					$cats_in_order+=$get_category_tree;
					$sort+=count($get_category_tree);/*advance sorter*/
				}
			}
			/**************************************************
				if cats are wpml enabled and there's more
				than one category language, add wpml cats tree
				for categories in this language
			****************************************************/
			if(count($wpml_sort_cats_lang)>=1){
				foreach($wpml_sort_cats_lang as $lang_code=>$default_sort_cats){
					$sitepress->switch_lang($lang_code);
					foreach($default_sort_cats as $cat){
						/**add parent to results array*/
						if($full_details){
							$cats_in_order[$sort]= array('sort'=>$sort, 'id'=>$cat->term_id, 'parent'=>$cat->parent, 'name'=>$cat->name);
						}else{
							$cats_in_order[$cat->term_id]= $sort;
						}
						$sort++;/*advance sorter*/

						/**get subcategory tree for ththis parent**/
						$get_category_tree= $this->wppizza_cat_tree_recursive( $cat->term_id, $sort, $full_details);

						if(!empty($get_category_tree)){
							/*add full tree to parent cat*/
							$cats_in_order+=$get_category_tree;
							$sort+=count($get_category_tree);/*advance sorter*/
						}
					}
				}
				/**make sure we switch back to current language when done**/
				$sitepress->switch_lang(ICL_LANGUAGE_CODE);
			}

			/*custom sorting */
			if(!empty($custom_sort) && is_array($custom_sort)){
				$cats_in_order_custom_sort=array();
				foreach($cats_in_order as $key=>$arr){
					$cSort=$custom_sort[$arr['id']];/*set key [sort] according to custom sort */
					$cats_in_order_custom_sort[$arr['id']]=array();
					$cats_in_order_custom_sort[$arr['id']]['sort']=$cSort;
					$cats_in_order_custom_sort[$arr['id']]['id']=$arr['id'];
					$cats_in_order_custom_sort[$arr['id']]['parent']=$arr['parent'];
					$cats_in_order_custom_sort[$arr['id']]['name']=$arr['name'];
				}
				asort($cats_in_order_custom_sort);/*sort by sort flag, key = catid*/
				$cats_in_order=$cats_in_order_custom_sort;/*set sorted*/
			}


			/*return sorted cats - either default or by custom sort array*/
			return $cats_in_order;
		}
		/*recursively get hierarchy tree for parent category*/
		function wppizza_cat_tree_recursive( $cat, $sort, $full_details=false ) {
			/**get categories of this parent*/
			$args=array('orderby' => 'name', 'order' => 'ASC', 'hide_empty' => 0, 'parent' => $cat, 'taxonomy' => WPPIZZA_TAXONOMY);
			$sub_cat_tree = get_categories($args);

			if($sub_cat_tree){
			foreach( $sub_cat_tree as $cat ){
				/**add to results**/
				if($full_details){
					$cats_in_order[$sort]=array('sort'=>$sort, 'id'=>$cat->term_id, 'parent'=>$cat->parent, 'name'=>$cat->name);
				}else{
					$cats_in_order[$cat->term_id]= $sort;
				}

				$sort++;/*advance sort*/
				/*recursive*/
				$process= $this->wppizza_cat_tree_recursive( $cat->term_id, $sort, $full_details);
				if(!empty($process)){
					$cats_in_order+=$process;//$sort as key
					$sort+=count($process);
				}
			}}

			if(!empty($cats_in_order)){
				return $cats_in_order;
			}
			return;
		}
		/*************************************************
			check if wppizza cats (wppizza_menu taxonomy)
			are enabled for wpml translation
		*************************************************/
		function wppizza_wpml_cats_translated($taxonomy){
			$taxonomy_translated=false;
			/**check if wpml enabled first **/
			if ( function_exists('icl_object_id') ){
				global $sitepress_settings;
				/**if taxonomy enabled for translation, return true*/
				if(!empty($sitepress_settings['taxonomies_sync_option'][$taxonomy])){
					$taxonomy_translated=true;
				}
			}
			return $taxonomy_translated;
		}

/*********************************************************
*
*		[admin options validation]
*
*********************************************************/
    public function wppizza_admin_options_validate($input){
		/*do not use require_once here as it may be used more than once .doh!**/
		require(WPPIZZA_PATH .'inc/admin.options.validate.inc.php');
		/**register applicable/new WPML strings on options save*/
		require(WPPIZZA_PATH .'inc/wpml.register.strings.php');
	return $options;
    }
/*********************************************************
*
*		[empty custom posts]
*
*********************************************************/
	public function wppizza_empty_taxonomy($deleteAttachments=false){
		require_once(WPPIZZA_PATH .'inc/admin.empty.taxonomy.data.php');
	}
	public function wppizza_truncate_order_table(){
		global $wpdb;
		/*no backticks or apostrophies please**/
		/** see http://codex.wordpress.org/Creating_Tables_with_Plugins **/
		$sql="TRUNCATE ".$wpdb->prefix . $this->pluginOrderTable."";
		$e = $wpdb->query($sql);
	}
/*********************************************************************************
*
*	[changes wppizza custom sort order query to display category navigation in the right order]
*
*********************************************************************************/
	function wppizza_term_filter($pieces, $taxonomies=false, $args=false){
		/**allow to pass sort vars**/
		if($args && isset($args['get']['wppizza_category_sort']) && is_array($args['get']['wppizza_category_sort'])){
			$cat=$args['get']['wppizza_category_sort'];
		}else{
			$cat=$this->pluginOptions['layout']['category_sort_hierarchy'];
		}
		if(!isset($cat)){
		return $pieces;
		}
		asort($cat);
		$sort=implode(",",array_keys($cat));
		/*customise order by clause*/
		$pieces['orderby'] = 'ORDER BY FIELD(t.term_id,'.$sort.')';
	return $pieces;
	}
/*********************************************************************************
*
*	[filter legacy: as we might still be using additional info in templates]
*	make extend array into addinfo array
*
*********************************************************************************/
	function wppizza_filter_order_summary_legacy($orderItems){
		foreach($orderItems['items'] as $k=>$oItem){
			/**extend key has priority over additional info (legacy)*/
			if(isset($oItem['extend']) && count($oItem['extend'])>0){
				$orderItems['items'][$k]['additionalinfo']=$oItem['extend'];
				//unset($orderItems['items'][$k]['extend']);/*do not unset actually as simple asort will not work anymore in some places*/
			}
		}
		return $orderItems;
	}

/*********************************************************************************
*
*	[filter: sanitize whats going into the db]
*
*********************************************************************************/
	function wppizza_filter_sanitize_order_recursive(&$val,$key){
		/**let's first decode all already encode ones to not double encode**/
		$val=wppizza_email_decode_entities($val,WPPIZZA_CHARSET);
		/*now entitize the lot again*/
		$val=wppizza_email_html_entities($val);
	}
	function wppizza_filter_sanitize_order($arr){
		array_walk_recursive($arr,array($this,'wppizza_filter_sanitize_order_recursive'));
		return $arr;
	}

	function wppizza_filter_sanitize_post_vars_recursive(&$val,$key){
		$val=stripslashes($val);
		/**let's first decode all already encode ones to not double encode**/
		$val=wppizza_email_decode_entities($val,WPPIZZA_CHARSET);
		/**strip things**/
		$val=wp_kses($val,array());
		/*now entitize the lot again*/
		$val=wppizza_email_html_entities($val);
	}

	function wppizza_filter_sanitize_post_vars($arr){
		if(is_array($arr)){
			array_walk_recursive($arr,array($this,'wppizza_filter_sanitize_post_vars_recursive'));
		}
		/**as tips belong to order details and not customer details, we exclude them from the post vars that get stored in the db customer_ini*/
		if(isset($arr['ctips'])){unset($arr['ctips']);}

		return $arr;
	}
/*********************************************************************************
*
*	[filter: sanitize order details returned from db]
*
*********************************************************************************/
	function wppizza_filter_order_db_return($oDetails, $type=''){
	static $s=0;$s++;

		$orderDetails=$oDetails;
		foreach($oDetails['item'] as $k=>$v){
			$orderDetails['item'][$k]['blogid']=!empty($v['blogId']) ? (int)$v['blogId'] : '';/*old orders might not have a blogid*/
			$orderDetails['item'][$k]['postId']=$v['postId'];
			$orderDetails['item'][$k]['count']=$v['count'];
			$orderDetails['item'][$k]['quantity']=$v['count'];/*legacy*/
			$orderDetails['item'][$k]['name']=$v['name'];
			$orderDetails['item'][$k]['size']=$v['size'];
			$orderDetails['item'][$k]['price']=wppizza_output_format_price($v['price'],$this->pluginOptions['layout']['hide_decimals']);
			$orderDetails['item'][$k]['pricetotal']=wppizza_output_format_price($v['pricetotal'],$this->pluginOptions['layout']['hide_decimals']);
			$orderDetails['item'][$k]['categories']=$v['categories'];
			$orderDetails['item'][$k]['additionalinfo']=$v['additionalinfo'];
			$orderDetails['item'][$k]['extend']=$v['extend'];
			$orderDetails['item'][$k]['catIdSelected']=$v['catIdSelected'];
		}


		$orderDetails['total_price_items']=wppizza_output_format_price($oDetails['total_price_items'],$this->pluginOptions['layout']['hide_decimals']);
		$orderDetails['discount']=wppizza_output_format_price($oDetails['discount'],$this->pluginOptions['layout']['hide_decimals']);
		$orderDetails['item_tax']=wppizza_output_format_price($oDetails['item_tax'],$this->pluginOptions['layout']['hide_decimals']);
		$orderDetails['taxes_included']=wppizza_output_format_price($oDetails['taxes_included'],$this->pluginOptions['layout']['hide_decimals']);

		$orderDetails['delivery_charges']=!empty($oDetails['delivery_charges']) ? wppizza_output_format_price($oDetails['delivery_charges'],$this->pluginOptions['layout']['hide_decimals']) : '';
		$orderDetails['selfPickup']=!empty($oDetails['selfPickup']) ? wppizza_validate_int_only($oDetails['selfPickup']) : 0;
		$orderDetails['total']=wppizza_output_format_price($oDetails['total'],$this->pluginOptions['layout']['hide_decimals']);
		if(isset($oDetails['handling_charge'])){
			$orderDetails['handling_charge']=wppizza_output_format_price($oDetails['handling_charge'],$this->pluginOptions['layout']['hide_decimals']);
		}
		if(isset($oDetails['tips'])){
			$orderDetails['tips']=wppizza_output_format_price($oDetails['tips'],$this->pluginOptions['layout']['hide_decimals']);
		}

	return $orderDetails;
	}

/************************************************************************************************************************************************
*
*	[filter/action: sort by and print categories in
*	order page, thank you page and emails if enabled]
*
************************************************************************************************************************************************/
	/*******************
	* filter transaction id
	*******************/
	function wppizza_filter_transaction_id($transactionId, $orderId){
		/**allow custom filter**/
		$transactionId = apply_filters('wppizza_custom_transaction_id', $transactionId, $orderId);
		/**add id to end**/
		if($this->pluginOptions['order']['append_internal_id_to_transaction_id']){
			$transactionId.='/'.$orderId.'';
		}
		return $transactionId;
	}

	/*************************************************
	* add hidden field to loop to identify selected category (as an iten could be in 2 or more, when wanting to group items by category in emails etc)
	*************************************************/
	function wppizza_add_category_hidden_field($postId,  $options, $termSlug, $categoryId){
		if($options['layout']['items_group_sort_print_by_category'] && $categoryId>0){
			print'<input type="hidden" id="wppizza-category-'.$postId.'" value="'.$categoryId.'" />';
		}
	}
	/*************************************************************
	* add identifiable category id to item permalink if
	grouping by category and item is assigned to more than one category
	*************************************************************/
	function wppizza_permalink_append_category($postId, $permalink, $termDetails, $categoryId){
		if ($this->pluginOptions['layout']['items_group_sort_print_by_category']) {

			$termDetails = wp_get_post_terms( $postId, WPPIZZA_TAXONOMY);
			$taxonomies=array();
			if ($termDetails && ! is_wp_error($termDetails)){
				$taxonomies = wp_list_pluck( $termDetails, 'slug', 'term_id');
			}
			/********************************************************
			check if selected item is in more than one category
			and if so, append selected category to permalink so we
			can reliably add idden input field to be able to add item
			to cart under selected grouped category
			*******************************************************/
			$multiTaxonomy=false;
			if(count($taxonomies)>1){
				$multiTaxonomy=true;
				$setTaxonomy=$taxonomies[$categoryId];
			}
			if($multiTaxonomy){
				$params = array( 'c' => $setTaxonomy );//$setTaxonomy//$categoryId
				$permalink = esc_url_raw(add_query_arg( $params, $permalink ));
			}
		}
		return $permalink;
	}
	/*******************
	* sort by category
	*******************/
	function wppizza_filter_items_by_category($items, $page, $options=false, $blogid=false){

		/*if using multisite in order history, set the options according to that sites settings**/
		if($options){
			$this->pluginOptions=$options;
		}
		/*skip the whole thing if not enabled**/
		if(!$this->pluginOptions['layout']['items_group_sort_print_by_category']){
			return $items;
		}
		$setCatOrder=$this->pluginOptions['layout']['category_sort_hierarchy'];
		$separator=$this->pluginOptions['layout']['items_category_separator'];
		$itemsCategorySort=array();
		$existingCatHierarchy=array();
		$existingBlogName=array();
		$existingBlogId=array();

		/**get categories and group/sort**/
		foreach($items as $k=>$v){
			/**associate blog id with set sortorder*/
			$blogId=!empty($v['blogid']) ? $v['blogid'] : $blogid;/***04 07 2015 edited****/
			/*work around inconsistency in emails*/
			if($page=='htmlemail' || $page=='plaintextemail'){
				$blogId=!empty($v['blogId']) ? $v['blogId'] : $blogid;/***04 07 2015 edited****/
			}

			/**associate category id with set sortorder*/
			$itemCat=!empty($setCatOrder[$v['catIdSelected']]) ? $setCatOrder[$v['catIdSelected']] : '';
			/**prepend blogid and first key of sortorder , append category hierarchy**/
			$itemsCategorySort[$k]=array('blogId'=>$blogId,'catsort'=>$itemCat)+$v;//+array('itemCatHierarchy'=>$setCatHierarchy);
		}
		/*now sort by blogid, category**/
		asort($itemsCategorySort);

		/***reiterate over items and display category name if first time****/
		foreach($itemsCategorySort as $k=>$v){
			$itemCatHierarchy=$this->wppizza_cat_parents($this->pluginOptions, $v['blogId'], $v['catIdSelected'], $separator , $page );/***04 07 2015 edited****/
			/**set catnames to be empty if it's the same again as we only want to print  this the first time as header so to speak**/
			if(!in_array($itemCatHierarchy,$existingCatHierarchy)){
				$setCatHierarchy=$itemCatHierarchy;
			}else{
				$setCatHierarchy='';
			}
			/**append category hierarchy name**/
			$itemsCategorySort[$k]=$v+array('itemCatHierarchy'=>$setCatHierarchy);
			/*set current cat hierarchy to avoid double display**/
			$existingCatHierarchy[]=$itemCatHierarchy;


			/**append blog info name, if multisite we will have to switch to blog first**/
			/**set blog name to be empty if it's the same again as we only want to print this the first time as header so to speak**/
			$itemBlogId=$v['blogId'];
			if(!in_array($itemBlogId,$existingBlogId)){
				if(is_multisite()){
						switch_to_blog($itemBlogId);
							$setBlogName=get_bloginfo('name');
						restore_current_blog();
				}else{
					$setBlogName=get_bloginfo('name');
				}
			}else{
				/**if we have already set this once, keep empty*/
				$setBlogName='';
			}
			$existingBlogId[]=$itemBlogId;


			$itemsCategorySort[$k]=$itemsCategorySort[$k]+array('itemBlogName'=>$setBlogName);
		}
		return $itemsCategorySort;
	}
	/*******************************************************************
	* print category
	* if you want output to be different, use remove_action/filter and add_action
	*******************************************************************/
	function wppizza_items_cart_print_category($item, $cartContents){
		/****[add blogname too]***/
		if($this->pluginOptions['layout']['items_blog_hierarchy_cart'] && isset($item['itemBlogName']) && $item['itemBlogName']!=''){
			$cartContents.='<li id="wppizza-item-blog-'.$item['blogId'].'" class="wppizza-item-blogname"><span>'.$item['itemBlogName'].'</span></li>';
		}
		/**only print if not empty and enabled**/
		if(isset($item['itemCatHierarchy']) && $item['itemCatHierarchy']!=''){
			$cartContents.='<li class="wppizza-item-category"><span>'.$item['itemCatHierarchy'].'</span></li>';
		}
		return $cartContents;
	}
	function wppizza_items_order_form_print_category($item){
		/****[add blogname too]***/
		if($this->pluginOptions['layout']['items_blog_hierarchy_cart'] && isset($item['itemBlogName']) && $item['itemBlogName']!=''){
			echo'<li id="wppizza-item-blog-'.$item['blogId'].'" class="wppizza-item-blogname">'.$item['itemBlogName'].'</li>';
		}
		/**only print if not empty and enabled**/
		if(isset($item['itemCatHierarchy']) && $item['itemCatHierarchy']!=''){
			echo'<li class="wppizza-item-category">'.$item['itemCatHierarchy'].'</li>';
		}
	}
	function wppizza_items_confirmation_form_print_category($item, $key, $header_elm_count){
		/****[add blogname too]***/
		if($this->pluginOptions['layout']['items_blog_hierarchy_cart'] && isset($item['itemBlogName']) && $item['itemBlogName']!=''){
			echo'<tr id="wppizza-item-blog-'.$item['blogId'].'" class="wppizza-item-blogname"><td colspan="'.$header_elm_count.'">'.$item['itemBlogName'].'</td></tr>';
		}
		/**only print if not empty and enabled**/
		if(isset($item['itemCatHierarchy']) && $item['itemCatHierarchy']!=''){
			echo'<tr class="wppizza-item-category"><td colspan="'.$header_elm_count.'">'.$item['itemCatHierarchy'].'</td></tr>';
		}
	}
	function wppizza_items_emailhtml_print_category($item,$style){
		/**only print if not empty and enabled**/
		if(isset($item['itemCatHierarchy']) && $item['itemCatHierarchy']!=''){
			echo'<tr><td style="'.$style['categories'].'" colspan="2">'.$item['itemCatHierarchy'].':</td></tr>';
		}
	}

	function wppizza_items_emailplaintext_print_category($item,$output){
		static $c=0;
		/**only print if not empty and enabled**/
		if(isset($item['itemCatHierarchy']) && $item['itemCatHierarchy']!=''){
			//if($c>0){$output.=''.PHP_EOL;}/**skip topmost EOL*/
			$output.=PHP_EOL.'['.$item['itemCatHierarchy'].']'.PHP_EOL;
			$c++;
		}
		return $output;
	}
	function wppizza_items_show_order_print_category($item){
		$markup='';
		/****[add blogname too]***/
		if($this->pluginOptions['layout']['items_blog_hierarchy_cart'] && isset($item['itemBlogName']) && $item['itemBlogName']!=''){
			$markup.='<li id="wppizza-item-blog-'.$item['blogId'].'" class="wppizza-item-blogname">'.$item['itemBlogName'].'</li>';
		}
		/**only print if not empty and enabled**/
		if(isset($item['itemCatHierarchy']) && $item['itemCatHierarchy']!=''){
			$markup.='<li class="wppizza-item-category">'.$item['itemCatHierarchy'].'</li>';
		}
		echo $markup;
	}
	function wppizza_items_print_category($str, $item, $catElm='tr', $blogElm='tr', $styleblog=false, $stylecat=false){

		$str='';

		/****
		*
		*	[add blogname]
		*
		****/
		if($this->pluginOptions['layout']['items_blog_hierarchy'] && isset($item['itemBlogName']) && $item['itemBlogName']!=''){
			if($blogElm=='tr'){
				if(!$styleblog){
					$str.='<tr id="wppizza-item-blog-'.$item['blogId'].'" class="wppizza-item-blogname"><td colspan="3">'.$item['itemBlogName'].'</td></tr>';
				}else{
					$styleblog=($styleblog!='') ? ' style="'.$styleblog.'"' : '' ;
					$str.='<tr><td colspan="3" '.$styleblog.'>'.$item['itemBlogName'].'</td></tr>';
				}
			}
			if($blogElm=='li'){
				if(!$styleblog){
					$str.='<li id="wppizza-item-blog-'.$item['blogId'].'" class="wppizza-item-blogname">'.$item['itemBlogName'].'</li>';
				}else{
					$styleblog=($styleblog!='') ? ' style="'.$styleblog.'"' : '' ;
					$str.='<li '.$styleblog.'>'.$item['itemBlogName'].'</li>';
				}
			}
			/*open and close set with array*/
			if(is_array($blogElm)){
				$str.='';
				/*opening char*/
				if(isset($blogElm[0])){
					$str.=$blogElm[0];
				}
				/*blog name*/
				$str.=''.$item['itemBlogName'].'';
				/*closing char*/
				if(isset($blogElm[1])){
					$str.=$blogElm[1];
				}
				/**additional PHP_EOL for example*/
				if(isset($blogElm[2])){
					$str.=$blogElm[2];
				}
			}
		}
		/****

			[add category name/path]

		****/
		/**only print if not empty and enabled**/
		if(isset($item['itemCatHierarchy']) && $item['itemCatHierarchy']!=''){

			if($catElm=='tr'){
				if(!$stylecat){
					$str.='<tr class="item-category"><td colspan="3">'.$item['itemCatHierarchy'].'</td></tr>';
				}else{
					$stylecat=($stylecat!='') ? ' style="'.$stylecat.'"' : '' ;
					$str.='<tr><td colspan="3" '.$stylecat.'>'.$item['itemCatHierarchy'].'</td></tr>';
				}
			}
			if($catElm=='li'){
				if(!$stylecat){
					$str.='<li class="item-category">'.$item['itemCatHierarchy'].'</li>';
				}else{
					$stylecat=($stylecat!='') ? ' style="'.$stylecat.'"' : '' ;
					$str.='<li '.$stylecat.'>'.$item['itemCatHierarchy'].'</li>';
				}
			}
			/*open and close set with array*/
			if(is_array($catElm)){


				$str.='';
				/*opening char*/
				if(isset($catElm[0])){
					$str.=$catElm[0];
				}
				/*cat name*/
				$str.=''.$item['itemCatHierarchy'].'';
				/*closing char*/
				if(isset($catElm[1])){
					$str.=$catElm[1];
				}
				/**additional PHP_EOL for example*/
				if(isset($catElm[2])){
					$str.=$catElm[2];
				}
			}
			return $str;
		}
	}

/**
 * Retrieve category parents with separator for general taxonomies.
 * Modified version of get_category_parents()
 * @return string
 */
function wppizza_cat_parents($options, $blogid, $id, $separator =' &raquo; ', $page , $taxonomy = 'wppizza_menu', $visited = array()) {

	$topmost=false;
	$parentOnly=false;

	/**if not set differently, the whole path will be displayed**/
	/**cart**/
	if($page=='cart'){
		/*no display*/
		if($options['layout']['items_category_hierarchy_cart']=='none'){
			return;
		}
		/*topmost category only*/
		if($options['layout']['items_category_hierarchy_cart']=='topmost'){
			$topmost=true;
		}
		/*direct parent cat only*/
		if($options['layout']['items_category_hierarchy_cart']=='parent'){
			$parentOnly=true;
		}
	}
	/**all others**/
	if($page!='cart'){
		/*topmost category only*/
		if($options['layout']['items_category_hierarchy']=='topmost'){
			$topmost=true;
		}
		/*direct parent cat only*/
		if($options['layout']['items_category_hierarchy']=='parent'){
			$parentOnly=true;
		}
	}

	$c=0;
	$chain = '';
	$name = '';

	/**mutlisite ->switch (only order history might have the blogid set via multisite attribute in shortcode**/
	if(is_multisite() && $blogid){switch_to_blog($blogid);}

	/**get term**/
	$parent = get_term( $id, $taxonomy);

	/**mutlisite -> restore current **/
	if(is_multisite() && $blogid){restore_current_blog();}


	if ( is_wp_error( $parent ) || !is_object($parent)){
		return;
	}

	$name = $parent->name;

	/**direct parent only**/
	if($parentOnly){
		return $name;
	}

	if ( $parent->parent && ( $parent->parent != $parent->term_id ) && !in_array( $parent->parent, $visited ) ) {
		$visited[] = $parent->parent;
		if($topmost){/**topmost (grand) parent only**/
			$name = $this->wppizza_cat_parents($options, $blogid, $parent->parent, $separator, $page ,$taxonomy, $visited );
		}else{/**full hierarchy**/
			$chain .= $this->wppizza_cat_parents($options, $blogid, $parent->parent, $separator, $page, $taxonomy, $visited );
		}
	$c++;
	}

	if($topmost){/**topmost (grand) parent only**/
		return $name;
	}else{/**full hierarchy**/
		if($c>0){$chain .= ''.$separator.'';}
		$chain .= $name;
		return $chain;
	}
}
/*********************************************************************************
*
*	[filter plaintext customer / order details converting array to string when returned from db]
*	[as everything that gets stored in the db is entitized, we need to un-entitize stuff
*	that gets returned from the db and make a string out of it]
*
*********************************************************************************/
/*seems to run 2x too ?! need to find out why that is reasonably soon*/
	function wppizza_filter_customer_details_to_plaintext($customerDetails){
		if(is_array($customerDetails)){
				$pad=WPPIZZA_PLAINTEXT_MAX_LINE_LENGTH;
				$customerDetailsString='';
				foreach($customerDetails as $k=>$v){
					/*non-textarea*/
					if($v['type']!='textarea'){
						$strPartLeft=''.wppizza_email_decode_entities($v['label'],WPPIZZA_CHARSET).'';
						$spaces=$pad-strlen($strPartLeft);
						$strPartRight=''.wppizza_email_decode_entities($v['value'],WPPIZZA_CHARSET);
						/**add to string**/
						$customerDetailsString.=''.$strPartLeft.''.str_pad($strPartRight,$spaces," ",STR_PAD_LEFT).''.PHP_EOL;
					}
					/**textareas, nl and indent*/
					if($v['type']=='textarea'){
						/**add to string**/
						$customerDetailsString.=''.wppizza_email_decode_entities($v['label'],WPPIZZA_CHARSET).PHP_EOL;
						if($v['value']!=''){
							$customerDetailsString.='     '.wppizza_wordwrap_indent(wordwrap(wppizza_email_decode_entities($v['value'],WPPIZZA_CHARSET), $pad, PHP_EOL, true));
							$customerDetailsString.=PHP_EOL.PHP_EOL;
						}
					}
				}
			return $customerDetailsString;
		}else{
			return $customerDetails;
		}
	}

	function wppizza_filter_order_items_to_plaintext($orderItems){
		if(is_array($orderItems)){
			$pad=WPPIZZA_PLAINTEXT_MAX_LINE_LENGTH;
			/**set originals**/
			$oItems=$orderItems;

			/*override/entitydecode individual keys as required**/
			foreach($orderItems as $k=>$v){
				$oItems[$k]['name']=wppizza_email_decode_entities($v['name'],WPPIZZA_CHARSET);
				$oItems[$k]['size']=wppizza_email_decode_entities($v['size'],WPPIZZA_CHARSET);
				$oItems[$k]['label']=wppizza_email_decode_entities($v['label'],WPPIZZA_CHARSET);
				$oItems[$k]['value']=wppizza_email_decode_entities($v['value'],WPPIZZA_CHARSET);
				if(isset($v['categories']) && is_array($v['categories'])){
				foreach($v['categories'] as $catId=>$cat){
					$oItems[$k]['categories'][$catId]['name']=wppizza_email_decode_entities($cat['name'],WPPIZZA_CHARSET);
					$oItems[$k]['categories'][$catId]['description']=wppizza_email_decode_entities($cat['description'],WPPIZZA_CHARSET);
				}}
				/**decoded string add spaces to front***/
				$oItems[$k]['additional_info']='   '.wppizza_email_decode_entities(''.$v['addinfo']['txt'].'',WPPIZZA_CHARSET).PHP_EOL;

				/**now unset vars we dont need anymore**/
				unset($oItems[$k]['additionalinfo']);
				unset($oItems[$k]['extend']);
				unset($oItems[$k]['additionalInfo']);
				unset($oItems[$k]['addinfo']);
			}
			return $oItems;
		}
	}


	function wppizza_filter_order_items_html($orderItems,$returnKey){
		/**set originals**/
		$oItems=$orderItems;
		if(isset($orderItems) && is_array($orderItems)){
		foreach($orderItems as $k=>$v){
			/**unset vars we dont need anymore**/
			unset($oItems[$k]['additionalinfo']);
			unset($oItems[$k]['extend']);
			unset($oItems[$k]['additionalInfo']);
			/**return additional info html with set returnKey (whatever is used in template*/
			$oItems[$k][''.$returnKey.'']=!empty($v['addinfo']['html']) ? ''.$v['addinfo']['html'].'' : '' ;
			/**now unset addinfo var as we dont need it anymore**/
			unset($oItems[$k]['addinfo']);
		}}
		return $oItems;
	}

	function wppizza_filter_customer_details_html($cDetails){
		if(isset($cDetails) && is_array($cDetails)){
		foreach($cDetails as $k=>$v){
			if($v['type']=='textarea'){
				$cDetails[$k]['value']='<div class="wppizza-order-textarea">'.nl2br($v['value']).'</div>';

			}
		}}
		return	$cDetails;
	}

	function wppizza_filter_order_summary_to_plaintext($orderSummary){
		if(is_array($orderSummary)){
				$pad=WPPIZZA_PLAINTEXT_MAX_LINE_LENGTH;
				$orderSummaryString='';
				foreach($orderSummary as $k=>$v){
						/**add - symbol to discount**/
						if($k=='discount'){
							$v['price']='-'.$v['price'];
						}
						if($k=='self_pickup'){
							$strPartLeft=PHP_EOL.wordwrap(strip_tags(wppizza_email_decode_entities($v['label'],WPPIZZA_CHARSET)), $pad, "\n", true).PHP_EOL;
						}else{
							$strPartLeft=''.wppizza_email_decode_entities($v['label'],WPPIZZA_CHARSET).'';
						}
						$spaces=$pad-strlen($strPartLeft);
						if($this->pluginOptions['layout']['currency_symbol_position']=='right'){/*right aligned*/
						$strPartRight=wppizza_email_decode_entities(''.$v['price'].' '.$v['currency'].'',WPPIZZA_CHARSET);
						}else{
						$strPartRight=wppizza_email_decode_entities(''.$v['currency'].' '.$v['price'].'',WPPIZZA_CHARSET);
						}
						/**add to string**/
						$orderSummaryString.=''.$strPartLeft.''.str_pad($strPartRight,$spaces," ",STR_PAD_LEFT).''.PHP_EOL;
				}
				/**decode entities for plaintext**/
			return $orderSummaryString;
		}else{
			return $orderSummary;
		}
	}

	/***********************************************************
		pad a string left and right with a specific character up to max length

		@str - string
		@return padded string
	***********************************************************/
	function wppizza_plaintext_padstring($str, $char='='){

		/**filter/change chars if you need to **/
		$char=apply_filters('wppizza_plaintext_padstring_char', $char);

		/*length parts  label*/
		$strLength=strlen(utf8_decode(wppizza_email_decode_entities($str,WPPIZZA_CHARSET)));

		/*paddcount -2 to have one space either side of str */
		$pad=floor((WPPIZZA_PLAINTEXT_MAX_LINE_LENGTH-$strLength-2)/2);

		/*skip padding if too long to start off with**/
		if($pad<=0){
			return $str;
		}

		$strPadded='';
		/*str_pad wont work as it will miscount multibytes*/
		for($c=0;$c<$pad;$c++){
			$strPadded.=$char;
		}
		/****keep one space either side****/
			$strPadded.=' '.$str.' ';

		/*str_pad wont work as it will miscount multibytes*/
		for($c=0;$c<$pad;$c++){
			$strPadded.=$char;
		}

	return $strPadded;
	}

	/***********************************************************
		filter html styles in templates
		to at least *try* to get rid of totally invalid css
		@return array
	***********************************************************/
	function wppizza_html_sanitize_style($array, $css=false, $decodeSingleQuotes=false){
		foreach($array as $k=>$v){
			foreach($v as $w=>$x){
				$array[$k][$w]=$this->wppizza_html_sanitize_style_value($x, $css, $decodeSingleQuotes);
			}
		}
	return $array;
	}

	/***********************************************************
		helper for function above.

		this could be better no doubt, but will have to do for now

		$decodeSingleQuotes to pull it out again with single quotes being that again

		@returs string
	***********************************************************/
	function wppizza_html_sanitize_style_value($str, $css=false, $decodeSingleQuotes=false){
		$charRemove=array('{','}','<','>','`');
		/*if we are validating full css style sheets, do not strip { and } */
		if($css){
			$charRemove=array('`');
		}

		/*remove linebreaks*/
		$str=str_replace(PHP_EOL,'',$str);
		/**first convert all " to ' */
		$str=str_replace('"','\'',$str);
		/*strip tags*/
		$str=strip_tags($str);
		/*trim*/
		$str=trim($str);
		/*now ltes replace totally invalid things*/
		$str=str_replace($charRemove,'',$str);
		/*convert remaining namely single quotes */
		//$str=htmlspecialchars($str,ENT_QUOTES);

		if($decodeSingleQuotes){
			$str=str_replace("&#039;","'",$str);
		}


		return $str;
	}



	function wppizza_filter_order_additional_info($orderItems){
		foreach($orderItems as $k=>$oItems){
			/*legacy => will be deprecated. it should all be in [extend] as array***/
			if(isset($oItems['additionalinfo']) && is_array($oItems['additionalinfo'])){
				$orderItems[$k]['addinfo']['html']=implode(", ",$oItems['additionalinfo']);
				$orderItems[$k]['addinfo']['txt']=wppizza_email_decode_entities(implode(", ",$oItems['additionalinfo']),WPPIZZA_CHARSET);
			}
		}
		return $orderItems;
	}

	/******************************************************************
	*	[show order history]
	*	[$order = object or id]
	******************************************************************/
	function wppizza_get_orderhistory($userid,$atts){
		global $wpdb;

		/*******get the variables***/
		$options = $this->pluginOptions;
		/**initialize some defaults**/
		$orders=array();
		$ordersPerPage=10;
		/*set attribute max per page**/
		if(isset($atts['maxpp']) && (int)$atts['maxpp']>0){
			$ordersPerPage=(int)$atts['maxpp'];
		}
		$ordersPerPage = apply_filters('wppizza_history_ordersperpage_filter', $ordersPerPage);


		if(!isset($_GET['pg']) || (int)$_GET['pg']<1){
			$limitOffset=0;
		}else{
			$limitOffset=(int)($_GET['pg']-1)*$ordersPerPage;
		}


		/**multisite get all orders from all sites*/
		$ordersMultisite=false;
		if(is_multisite() && isset($atts['multisite']) ){
			/*set flag*/
			$ordersMultisite=true;

			$historyRes=array();
			$blogs = $wpdb->get_results("SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A);
			if ($blogs) {
				foreach($blogs as $blog){
					$blogid=$blog['blog_id'];
					$details=get_blog_details($blogid);
					switch_to_blog($blogid);

					$multiQuery="SELECT order_date, id,transaction_id,order_status,order_ini,customer_ini,initiator FROM ".$wpdb->prefix . $this->pluginOrderTable." WHERE payment_status IN ('COD','COMPLETED') ";
					/*allow another where condition**/
					$multiQuery = apply_filters('wppizza_history_query_where_filter', $multiQuery);
					/**limit to user**/
					$multiQuery.="AND wp_user_id=".$userid." ";
					/**sort**/
					$multiQuery.="ORDER BY order_date DESC ";

					/**get the results and add to array**/
					$multiQueryRes = $wpdb->get_results($multiQuery);
					foreach($multiQueryRes as $qRes){
						$orderBlogId=$qRes->id.'_'.$blogid;//concat order id and blog id to make sure we have a unique id
						$historyRes[$orderBlogId]=$qRes;
						$historyRes[$orderBlogId]->blogid=$blogid;
						$historyRes[$orderBlogId]->blogname=$details->blogname;
					}

					/**we also need the blog options**/
					$blogoptions[$blogid]=get_option(WPPIZZA_SLUG);

				/*restore current*/
				restore_current_blog();
				}
			}
			/*get count**/
			$historyCount=count($historyRes);
			/*sort by date in reverse**/
			arsort($historyRes);
			/**slice to get max per page on page**/
			$historyRes = array_slice($historyRes, $limitOffset, $ordersPerPage);
		}

		/******************************
			not using multisite
		******************************/
		if(!$ordersMultisite){
				/**run the query**/
				$historyQuery="SELECT id,transaction_id,order_status,order_ini,customer_ini,initiator FROM ".$wpdb->prefix . $this->pluginOrderTable." WHERE payment_status IN ('COD','COMPLETED') ";

				/*allow another where condition**/
				$historyQuery = apply_filters('wppizza_history_query_where_filter', $historyQuery);

				/**limit to user**/
				$historyQuery.="AND wp_user_id=".$userid." ";
				/**sort**/
				$historyQuery.="ORDER BY order_date DESC ";
				/**limit**/
				$historyQuery.="limit ".$limitOffset.",".$ordersPerPage."";

				$historyRes = $wpdb->get_results($historyQuery);

				/*get count**/
				$historyCount="SELECT count(*) as count FROM ".$wpdb->prefix . $this->pluginOrderTable." WHERE payment_status IN ('COD','COMPLETED') AND wp_user_id=".$userid." ";
				$historyCount = $wpdb->get_results($historyCount);
				$historyCount=$historyCount[0]->count;
		}

		if(count($historyRes>0)){
			/***********************************************
				[set some global localization vars]
			************************************************/
			$orderlbl=array();
			foreach($this->pluginOptions['localization'] as $k=>$v){
				if($k=='taxes_included'){
					$orderlbl[$k]=sprintf(''.$v['lbl'].'',$this->pluginOptions['order']['item_tax']);
				}else{
					$orderlbl[$k]=$v['lbl'];
				}
			}


			$output='';
			foreach($historyRes as $k=>$res){
				/**********************************************************
					[get relevant vars out of db
				**********************************************************/
				$thisCustomerDetails=maybe_unserialize($res->customer_ini);
				$thisOrderDetails=maybe_unserialize($res->order_ini);
				$thisOrderDetails = apply_filters('wppizza_filter_order_db_return', $thisOrderDetails, 'orderhistory');

				if(isset($thisOrderDetails['total'])){/*just to be sure*/

					/***initialize array of this order**/
					$orders[$res->id]=array();

					/**multisite , get option of appropriate site otherwise use options of this site**/
					if(isset($res->blogid) && isset($atts['sitetitle']) && isset($blogoptions[$res->blogid])){
						$options=$blogoptions[$res->blogid];
						$blogid=$res->blogid;
					}else{
						$options=$this->pluginOptions;
						$blogid=false;
					}
					/**********************************************************
						[organize vars to make them easier to use in template]
					**********************************************************/
					$order['transaction_id']=$res->transaction_id;
					$order['order_status']=$res->order_status;
					/**filter as required**/
					$order['transaction_id'] = apply_filters('wppizza_filter_transaction_id', $order['transaction_id'], $res->id );
					$order['transaction_date_time']="".date_i18n(get_option('date_format'),$thisOrderDetails['time'])." ".date_i18n(get_option('time_format'),$thisOrderDetails['time'])."";

					/*useful in multisite perhaps to identify which site the order was made on**/
					$order['site_title']='';
					if(isset($res->blogname) && isset($atts['sitetitle'])){
						$order['site_title']=' <span class="wppizza-history-sitetitle wppizza-history-site-'.$res->blogid.'">'.$res->blogname.'</span>';
					}

					$order['gatewayUsed']=$res->initiator;

					/**get gateway frontend label instead of just COD or similar**/
					$order['gatewayLabel']=$res->initiator;
						$wppizzaGateways=new WPPIZZA_GATEWAYS();
						$this->pluginGateways=$wppizzaGateways->wppizza_instanciate_gateways_frontend();
						$gwIni=strtoupper($res->initiator);
					if(isset($this->pluginGateways[$gwIni])){
						$order['gatewayLabel']=!empty($this->pluginGateways[$gwIni]->gatewayOptions['gateway_label']) ? $this->pluginGateways[$gwIni]->gatewayOptions['gateway_label'] : $order['gatewayLabel'];
					}

					/**********************/
					$order['currency']=$thisOrderDetails['currency'];
					/****************************************************
						[set currency positions]
					****************************************************/
					$order['currency_left']=$thisOrderDetails['currency'].' ';
					$order['currency_right']='';
					if($options['layout']['currency_symbol_position']=='right'){/*right aligned*/
						$order['currency_left']='';
						$order['currency_right']=' '.$thisOrderDetails['currency'];
					}
					$order['currency_left'] = apply_filters('wppizza_filter_currency_display_left', $order['currency_left']);
					$order['currency_right'] = apply_filters('wppizza_filter_currency_display_rigth', $order['currency_right']);


					$order['currencyiso']=$thisOrderDetails['currencyiso'];

					/***allow some filtering of order and summary***/
					$order = apply_filters('wppizza_filter_order_history', $order, $thisOrderDetails);

					/***********************************************
						[order items]
					***********************************************/
					$items=$thisOrderDetails['item'];
					/**filter old legacy additional info keys**/
					$items = apply_filters('wppizza_filter_orderhistory_additional_info', $items);
					/**filter new/current extend additional info keys**/
					$items = apply_filters('wppizza_filter_order_extend', $items);
					/**return items with html additional info**/
					$items = apply_filters('wppizza_filter_orderhistory_items_html', $items, 'additionalInfo');

					/***********************************************
						[order summary
					***********************************************/
					//$summary['total_price_items']=$thisOrderDetails['total_price_items'];
					$summary['discount']=$thisOrderDetails['discount'];
					$summary['item_tax']=$thisOrderDetails['item_tax'];
					$summary['taxes_included']=$thisOrderDetails['taxes_included'];
					if($options['order']['delivery_selected']!='no_delivery'){/*delivery disabled*/
						$summary['delivery_charges']=$thisOrderDetails['delivery_charges'];
					}
					$summary['total_price_items']=$thisOrderDetails['total_price_items'];
					$summary['selfPickup']=$thisOrderDetails['selfPickup'];
					$summary['total']=$thisOrderDetails['total'];
					$summary['tax_applied']='items_only';
					if($options['order']['shipping_tax']){
						$summary['tax_applied']='items_and_shipping';
					}
					if($options['order']['taxes_included']){
						$summary['tax_applied']='taxes_included';
					}

					if(isset($thisOrderDetails['handling_charge']) && $thisOrderDetails['handling_charge']>0){
						$summary['handling_charge']=$thisOrderDetails['handling_charge'];
					}
					if(isset($thisOrderDetails['tips']) && $thisOrderDetails['tips']>0){
						$summary['tips']=$thisOrderDetails['tips'];
					}

					$orders[$res->id]['order']=$order;
					$orders[$res->id]['items']=$items;
					$orders[$res->id]['summary']=$summary;
					$orders[$res->id]['options']=$options;
					$orders[$res->id]['blogid']=$blogid;

					/***allow some filtering of order and summary***/
					$summary = apply_filters('wppizza_filter_summary_history', $summary, $thisOrderDetails);
				}
		}}

		$ordersOnPage=count($orders);
		$numberOfOrders=$historyCount;

		/***********************************************
			[if template copied to theme directory use
			that one otherwise use default]
		***********************************************/
		ob_start();
		if (file_exists( $this->pluginTemplateDir . '/wppizza-orderhistory.php')){
			include($this->pluginTemplateDir . '/wppizza-orderhistory.php');
		}else{
			include(WPPIZZA_PATH.'templates/wppizza-orderhistory.php');
		}
		$output .= ob_get_clean();

		print"".$output;
	}

	function wppizza_orderhistory_pagination($numberOfOrders,$ordersOnPage,$atts=array()){
		$ordersPerPage=10;
		if(isset($atts['maxpp']) && (int)$atts['maxpp']>0){
			$ordersPerPage=(int)$atts['maxpp'];
		}
		$ordersPerPage = apply_filters('wppizza_history_ordersperpage_filter', $ordersPerPage);


		$total_page=ceil($numberOfOrders/$ordersPerPage);
		$currentPageLink=get_permalink();

		if(!isset($_GET['pg'])){
			$page_cur=1;
		}else{
			$page_cur=(int)$_GET['pg'];
		}

		echo'<div class="wppizza-history-pagination-wrap">';
		if($page_cur>1){
			$link= esc_url_raw(add_query_arg(array('pg' => ($page_cur-1)), $currentPageLink ));
			echo '<a href="'.$link.'" class="wppizza-history-pagination-txt">'.__('Previous').'</a>';
		}else{
			echo '<a class="wppizza-history-pagination-txt-disabled" disabled="disabled">'.__('Previous').'</a>';
		}

		for($i=1;$i<=$total_page;$i++){
			if($page_cur==$i){
				echo '<a href="javascript:void(0)" class="wppizza-history-pagination-selected">'.$i.'</a>';
			}else{
				$link= esc_url_raw(add_query_arg(array('pg' => $i), $currentPageLink ));
				echo '<a href="'.$link.'" class="wppizza-history-pagination">'.$i.'</a>';
			}
		}

		if($page_cur<$total_page){
			$link= esc_url_raw(add_query_arg(array('pg' => ($page_cur+1)), $currentPageLink ));
			echo '<a href="'.$link.'" class="wppizza-history-pagination-txt">'.__('Next').'</a>';
		}else{
			echo '<a class="wppizza-history-pagination-txt-disabled" disabled="disabled">'.__('Next').'</a>';
		}
		echo'</div>';
	}
/*******************************************************
*
*	[filter: allow order statuses to be changed]
*	[WILL ALTER THE DB WPPIZZA_ORDERS TABLE - USE WITH CARE]
*	[BACKUP YOUR DATA]
*
******************************************************/
	function wppizza_set_order_status(){
		$setStatus=wppizza_custom_order_status();

		/**compare and see if we have to do anything**/
		if($this->pluginOptions!=0 && (!isset($this->pluginOptions['plugin_data']['db_order_status_options']) || $this->pluginOptions['plugin_data']['db_order_status_options']!=$setStatus)){
			global $wpdb;
			$usedOrderStatus = $wpdb->get_col("SELECT DISTINCT(order_status) FROM ".$wpdb->prefix . $this->pluginOrderTable." ");
			$newStatus=array();
			foreach($setStatus as $k=>$v){
				$newStatus[]=wppizza_validate_alpha_only(str_replace(" ","_",strtoupper($v)));
			}
			/**explicitly add all options already in use**/
			foreach($usedOrderStatus as $k=>$v){
				$newStatus[]=wppizza_validate_alpha_only(str_replace(" ","_",strtoupper($v)));
			}
			/**explicitly add NEW option (as its default)*****/
			$newStatus[]='NEW';

			/**update options**/
			$update_options=$this->pluginOptions;
			$update_options['plugin_data']['db_order_status_options']=$setStatus;

			update_option($this->pluginSlug, $update_options );
			/**ALTER TABLE**/
			$setNewOrderStatus=array_unique($newStatus);
			require_once(WPPIZZA_PATH .'inc/admin.create.order.table.inc.php');
		}
	}

/**********************************************************************************************************************
*
*
*
*	[MISCELLANEOUS]
*
*
*
**********************************************************************************************************************/
	    /**************************************************
	    *
	    *  close shop right now, overriding anything else
	    *
	    **************************************************/
		function wppizza_filter_close_shop_now($is_open){

			if(!empty($this->pluginOptions['globals']['close_shop_now'])){
				$is_open=0;
			}
			return $is_open;
		}

	    /**************************************************
	    *
	    *  make sure some plaintext outputs  (comments etc)
	    *  are not ridiculously long without wrapping in order history for example
	    *  allowing a bit of leeway though by using 80 as opposed to 74
	    *
	    **************************************************/
		function wppizza_force_plaintext_wordwrap($str){
			//return $str;
			/*explode by linebreak and wrap resulting*/
			$str=explode(PHP_EOL,$str);
			$wrapped=array();
			foreach($str as $line){
				$wrapped[]=wordwrap($line, WPPIZZA_PLAINTEXT_MAX_LINE_LENGTH_WORDWRAP, PHP_EOL, false);
			}
			/*re-implode*/
			$str=implode(PHP_EOL,$wrapped);
			return $str;
		}
	    /**************************************************
	    *
	    * sort formfields on order and confirmation page
	    *
	    **************************************************/
	    function wppizza_formfields_sort($formelements,$type){
			$ffSort=array();
			$ffByKey=array();
			foreach($formelements as $ffKey=>$ffVals){
				/*some value checking*/
				$sort=empty($ffVals['sort']) ? 0 : $ffVals['sort'];/*distinctly set sort even if omitted*/
				$key=empty($ffVals['key']) ? $ffKey : $ffVals['key'];/*incase it's omitted somewhere, though not really likely */
				/**create array to sort **/
				$ffSort[$key]=array('sort'=>$sort);
				$ffByKey[$key]=$ffVals;/*associate key */
			}
			/*sort array by sort, keeping key associations*/
			asort($ffSort);

			/*recreate array in right order*/
			$ffsorted=array();
			foreach($ffSort as $ffKey=>$ffVals){
				$ffsorted[$ffKey]=$ffByKey[$ffKey];
			}
			$formelements=$ffsorted;

	    	return $formelements;
	    }
	 /*************************************************************************
	        play sound on new order in order history.
	        repeats every 5 sec (5000 ms) until there's no
	        new order anymore (just change the status)....
	        set timeout (5000) to whatever timeinterval is required
	        set soundfile (notifySound)  to whatever sound is supposed to be played
	        if using IE, use an mp3 file instead of .wav

	        - currently not enabled -
	    ***************************************************************************/
	    function wppizza_notify_new_orders(){
	        global $current_screen;
	        if(isset($current_screen) && $current_screen->id=='wppizza_page_wppizza-order-history'){

	       	$SOUNDFILE_PATH=WPPIZZA_URL.'/assets';

	        echo"<script type='text/javascript'>
	            /* <![CDATA[ */
	            jQuery(document).ready(function($){
	                    var notifySound = '".$SOUNDFILE_PATH."/notify.mp3';
	                    var notifyNewOrders = new Audio(notifySound);
	                    var notifyNewOrdersInterval=setInterval(function(){
	                    if($('.wppizza-ord-status-new').length>0){
	                        notifyNewOrders.play();
	                    }},(5000));

	            });
	            /* ]]> */
	        </script>";
	        }
	    }
}
/************************************************************************************************************
*
*	[helper class: add hidden wppizza field defining what post type to search for
*	to search form when using shortcode/widget]
*
*************************************************************************************************************/
class WPPIZZA_SEARCH_VARS {
    public $atts = '';
    public $options = '';
    function searchvars( $form ) {
    	if(isset($this->atts['include']) && $this->atts['include']!=''){
    		$val=$this->atts['include'];
    		$inc=explode(",",$this->atts['include']);
    		if(in_array(WPPIZZA_POST_TYPE,$inc)){
    			/**if we have set another permalink for single mnu items, rewrite this here so the query finds wppizza after all**/
				if($this->options['plugin_data']['single_item_permalink_rewrite']!=''){
					$key = array_search(WPPIZZA_POST_TYPE, $inc);
					$inc[$key]=$this->options['plugin_data']['single_item_permalink_rewrite'];
					$val=implode(",",$inc);
				}
    			$hiddenWppField='<input type="hidden" name="post_type" value="'.$val.'" />'.PHP_EOL.'</form';/*leave form tag open here to allow for spaces**/
				$form=str_ireplace('</form',$hiddenWppField,$form);
    		}
    	}
        return $form;
    }
}
?>