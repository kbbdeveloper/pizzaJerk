<?php
if(!defined('DOING_AJAX') || !DOING_AJAX){
	header('HTTP/1.0 400 Bad Request', true, 400);
	print"you cannot call this script directly";
  exit; //just for good measure
}
/**testing variables ****************************/
//unset($_SESSION[$this->pluginSession]);
//sleep(5);//when testing jquery fadeins etc
/******************************************/
$options=$this->pluginOptions;
global $blog_id;
/******************************************
	[supress errors unless debug]
******************************************/
$wppizzaDebug=wppizza_debug();
if(!$wppizzaDebug){
	error_reporting(0);
}
/***************************************************************
*
*
*	[add / remove item from cart session]
*
*
***************************************************************/
if(isset($_POST['vars']['type']) && (($_POST['vars']['type']=='add' || $_POST['vars']['type']=='remove' || $_POST['vars']['type']=='removeall' || $_POST['vars']['type']=='increment') && $_POST['vars']['id']!='') ||  $_POST['vars']['type']=='refresh' || $_POST['vars']['type']=='wppizza-update-order'){


	/**set count as int*********/
	$itemCount=1;
	if(isset($_POST['vars']['itemCount'])){
	$itemCount=(int)$_POST['vars']['itemCount'];
	}
	/**category id*********/
	$catIdSelected='';
	if(isset($_POST['vars']['catId']) && $_POST['vars']['catId']!=''){
		$catIdSelected=(int)$_POST['vars']['catId'];
	}

	/**initialize price array***/
	$itemprice=array();
	$itempricefordelivery=array();/*if we have excluded item to count towards free delivery */
	$itempricefordiscount=array();/*if we have excluded item to count towards discount */
	/**********set header********************/
	header('Content-type: application/json');
	/**add to cart**/
	if($_POST['vars']['type']=='add'){
		/*explode into item id and selected size***/
		$itemVars=explode("-",$_POST['vars']['id']);
		//$meta=get_post_meta($itemVars[1], $this->pluginSlug, true );
		$itemName=get_the_title($itemVars[1]);
		$groupId=$itemVars[1].'.'.$itemVars[3];//group items by id and size . ensure there's a seperator between (as 8 and 31 would otherwise be the same as 83 and 1. furthermore , dont use "-" as the js splits by this
		/**add category to group id (distinct cat id will only be passed if catdisplay enabled in layout)**/
		if($catIdSelected!='' && $this->pluginOptions['layout']['items_group_sort_print_by_category']){/*if we dont need to or want to split by category, do not add another distinction to the group*/
			$groupId.='.'.$catIdSelected;
		}
		//add blog id too as item with the same name could be in different subsites **/
		$groupId.='.'.$blog_id;

		/*get item set meta values to get price for this size**/
		$meta_values = get_post_meta($itemVars[1],$this->pluginSlug,true);
		$meta_values = apply_filters('wppizza_filter_loop_meta_ajax', $meta_values, $itemVars[1]);

		/**get all category id's item is assigned to**/
		$terms = get_the_terms( $itemVars[1],WPPIZZA_TAXONOMY );
		$itemCats=array();
		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach($terms as $term){
				$itemCats[$term->term_id]=$term->term_id;
			}
		}
		/**round if hiding decimals in case price entered was not entered as integer*/
		$itemSizePrice=wppizza_round_value($meta_values['prices'][$itemVars[3]],$this->pluginOptions['layout']['hide_decimals']);

		/**are we hiding pricetier name if only one available ?**/
		if(count($meta_values['prices'])<=1 && $options['layout']['hide_single_pricetier']==1){
			$itemSizeName='';
		}else{
			$itemSizeName=$options['sizes'][$itemVars[2]][$itemVars[3]]['lbl'];
		}

		/*add item to session array. adding lowercase name first to simplify sorting with asort**/
		$_SESSION[$this->pluginSession]['items'][$groupId][]=array('sortname'=>strtolower($itemName),'size'=>$itemVars[3], 'price'=>$itemSizePrice, 'sizename'=>$itemSizeName, 'printname'=>$itemName, 'id'=>$itemVars[1], 'allCatIds'=>$itemCats, 'catIdSelected'=>$catIdSelected, 'blogid' => $blog_id);

	}

	/**increment when using textbox**/
	if($_POST['vars']['type']=='increment'){
		$groupSel=explode("-",$_POST['vars']['id']);
		$groupId=$groupSel[2];
		$setGroup=$_SESSION[$this->pluginSession]['items'][$groupId][0];
		unset($_SESSION[$this->pluginSession]['items'][$groupId]);
		/**reset from scratch**/
		for($i=0;$i<$itemCount;$i++){
			$_SESSION[$this->pluginSession]['items'][$groupId][]=$setGroup;
		}
	}

	/**remove from cart -> just unset**/
	if($_POST['vars']['type']=='remove'){
		/**explode and get last in array (the id)**/
		$chkX=explode("-",$_POST['vars']['id']);
		$groupId=end($chkX);
		end($_SESSION[$this->pluginSession]['items'][$groupId]);
		$last=key($_SESSION[$this->pluginSession]['items'][$groupId]);
		unset($_SESSION[$this->pluginSession]['items'][$groupId][$last]);
		/*if there are 0x this ingredient, unset completely**/
		if(count($_SESSION[$this->pluginSession]['items'][$groupId])==0 || $itemCount==0){
			unset($_SESSION[$this->pluginSession]['items'][$groupId]);
		}
	}
	/**empty  cart -> just unset**/
	if($_POST['vars']['type']=='removeall'){
		unset($_SESSION[$this->pluginSession]['items']);
		$_SESSION[$this->pluginSession]['items']=array();
	}

	/**update from orderpage*/
	if($_POST['vars']['type']=='wppizza-update-order'){
		foreach($_POST['vars']['data'] as $groupId=>$quantity){
			$currentCount=empty($_SESSION[$this->pluginSession]['items'][$groupId]) ? 0 : count($_SESSION[$this->pluginSession]['items'][$groupId]);
			if($quantity==0){
				unset($_SESSION[$this->pluginSession]['items'][$groupId]);
			}else{
				//**fewer items than before, just shorten**
				if($quantity<$currentCount){
					$_SESSION[$this->pluginSession]['items'][$groupId] = array_slice($_SESSION[$this->pluginSession]['items'][$groupId], 0, $quantity);
				}
				//**more items than before, add the required multiple times**
				if($quantity>$currentCount){
					$addCount=$quantity-$currentCount;
					for($i=0;$i<$addCount;$i++){
						array_push($_SESSION[$this->pluginSession]['items'][$groupId], $_SESSION[$this->pluginSession]['items'][$groupId][0]);
					}
				}
				///same no of items -> just ignore
			}
		}
	}


	/*total price*/
	foreach($_SESSION[$this->pluginSession]['items'] as $k=>$group){
		if(is_array($group)){
		foreach($group as $v){
			$itemprice[]=$v['price'];

			$calcForDelivery=true;
			/**exclude items that are set to be excluded from calculating whether or not free delivery applies**/
			if(isset($options['order']['delivery_calculation_exclude_item']) && in_array($group[0]['id'],$options['order']['delivery_calculation_exclude_item'])){
				$calcForDelivery=false;
			}
			/**exclude items that are set to be excluded from calculating whether or not free delivery applies(category)**/
			if(isset($options['order']['delivery_calculation_exclude_cat'])){
				$intersect=array_intersect_key($v['allCatIds'],$options['order']['delivery_calculation_exclude_cat']);
				if(count($intersect)>0){/*menu item is in category that was excluded*/
					$calcForDelivery=false;
				}
			}
			if($calcForDelivery){
				$itempricefordelivery[]=$v['price'];
			}


			$calcForDiscount=true;
			/**exclude items that are set to be excluded from calculating discount (individually)**/
			if(isset($options['order']['discount_calculation_exclude_item']) && isset($options['order']['discount_calculation_exclude_item'][$group[0]['id']])){
				$calcForDiscount=false;
			}
			/**exclude items that are set to be excluded from calculating discount (category)**/
			if(isset($options['order']['discount_calculation_exclude_cat'])){
				$intersect=array_intersect_key($v['allCatIds'],$options['order']['discount_calculation_exclude_cat']);
				if(count($intersect)>0){/*menu item is in category that was excluded*/
					$calcForDiscount=false;
				}
			}
			if($calcForDiscount){
				$itempricefordiscount[]=$v['price'];
			}
		}}
	}

	$totalitemprice=array_sum($itemprice);
	$totalitempricefordelivery=array_sum($itempricefordelivery);
	$totalitempricefordiscount=array_sum($itempricefordiscount);

	/**total tax on all items -> currently not used as we will be calculating tax AFTER any discounts**/
	$_SESSION[$this->pluginSession]['total_items_tax']=0;
	if($options['order']['item_tax']>0){
		$_SESSION[$this->pluginSession]['total_items_tax']=$totalitemprice/100*$options['order']['item_tax'];
	}


	$_SESSION[$this->pluginSession]['total_price_items']=$totalitemprice;
	$_SESSION[$this->pluginSession]['total_price_calc_delivery']=$totalitempricefordelivery;
	$_SESSION[$this->pluginSession]['total_price_calc_discount']=$totalitempricefordiscount;

	print"".json_encode(wppizza_order_summary($_SESSION[$this->pluginSession],$options, 'cartajax', true))."";
	exit();
}
/***************************************************************************************************************************
*
*
*	[set self pickup]
*
*
***************************************************************************************************************************/
if(isset($_POST['vars']['type']) && $_POST['vars']['type']=='order-pickup'){


	/*****************************************
		[set session variable]
	*****************************************/
	$pickup=false;
	if($_POST['vars']['value']=='true'){
		$pickup=true;
	}
	/*****************************************
		[if pickup is set as default pickup 
		will be false if selecting checkbox
		as we are switching to delivery when 
		checking the box - essentially invert 
		the "normal" behaviour]
	*****************************************/	
	if(!empty($options['order']['order_pickup_as_default'])){
		$pickup = ($_POST['vars']['value']==='true') ? false : true;	
	}

	/*****************************************
		set default location -> to be overwritten below if required
	*****************************************/
	$location=$_POST['vars']['locHref'];

	/*****************************************
		[get and parse all post variables
		provided we are actually on the order
		page, otherwise there's nothing to do
	*****************************************/
	if($_POST['vars']['data']!=''){
		/***************************************************************
			[get and parse all user post variables and save in session
		***************************************************************/
		$this->wppizza_sessionise_userdata($_POST['vars']['data'],$options['order_form']);

		/*****************************************
			[parse and add all get variables
		*****************************************/
		$getParameters = array();
		if($_POST['vars']['urlGetVars']!=''){
			parse_str(substr($_POST['vars']['urlGetVars'],1), $getParameters);/*loose the '?'  */
		}
		/*********build the location url making sure permalinks are taken care of too**/
		$location=$this->wppizza_set_redirect_url($_POST['vars']['locHref'],$getParameters);

	}

	$vars['location']=$location;
	/*element name to go to on page refresh*/
	if(trim($options['layout']['element_name_refresh_page'])!=''){
		$vars['anchor']='#'.trim($options['layout']['element_name_refresh_page']);
	}

	/***do action of some sort***/
	do_action('wppizza_pickup_toggle',$pickup);


	/**set session to be pickup true or false**/
	if($pickup){
		$_SESSION[$this->pluginSession]['selfPickup']=1;
	}else{
		if(isset($_SESSION[$this->pluginSession]['selfPickup'])){
			unset($_SESSION[$this->pluginSession]['selfPickup']);
		}
	}

	print"".json_encode($vars)."";
exit();
}
/****************************************************************************************************************************************
*
*
*	[get the confirm order page]
*
*
****************************************************************************************************************************************/
if(isset($_POST['vars']['type']) && $_POST['vars']['type']=='confirmorder'){
	header('Content-type: text/html');
		/***************************************************************
			[get and parse all user post variables and save in session
		***************************************************************/
		if(count($_POST['vars']['data'])>0){
			$param=$this->wppizza_sessionise_userdata($_POST['vars']['data'],$options['order_form']);
			/**add hash**/
			$atts['hash']=!empty($param['wppizza_hash']) ? wppizza_validate_string($param['wppizza_hash']) : '';
			/**add used gateway*/
			$atts['gateway']=!empty($param['wppizza-gateway']) ? wppizza_validate_string($param['wppizza-gateway']) : '';
			/**ajax**/
			if($_POST['vars']['hasClassAjax']=='true'){
				$atts['hasClassAjax']=1;
			}
			/**custom**/
			if($_POST['vars']['hasClassCustom']=='true'){
				$atts['hasClassCustom']=1;
			}
		}
		ob_start();
		$this->wppizza_include_shortcode_template('confirmationpage',$atts);
		$markup = ob_get_clean();

	print"".$markup;
	exit();
}
/********************************************************************************************************************
*
*
*	[create a nonce]
*
*
********************************************************************************************************************/
if(isset($_POST['vars']['type']) && $_POST['vars']['type']=='nonce' && isset($_POST['vars']['val'])){
	$nonceType=$_POST['vars']['val'];
	$nonce=''.wp_nonce_field( 'wppizza_nonce_'.$nonceType.'','wppizza_nonce_'.$nonceType.'',false, false).'';
print"".$nonce;
exit();
}
/********************************************************************************************************************
*
*	[(try to) add new account, registering email as username]
*	[if it already exists, just send the username and password again]
*	[if it fails. just ignore]
*
********************************************************************************************************************/
if(isset($_POST['vars']['type']) && $_POST['vars']['type']=='new-account'){
	global $wp_version;

	/*****************************************
		[get and parse all post variables
	*****************************************/
	$postedVars = array();
	parse_str($_POST['vars']['data'], $postedVars);
	$postedVars = apply_filters('wppizza_filter_sanitize_post_vars', $postedVars);
	$output['pVar']=$postedVars;
	/****************************************
		verify nonce first
	******************************************/
	if (isset($postedVars['wppizza_nonce_register']) && wp_verify_nonce($postedVars['wppizza_nonce_register'],'wppizza_nonce_register') ){

		/************************************************
			check if email exists already
			if it does not carry on adding account
		************************************************/
		$user_id = username_exists( $postedVars['cemail'] );
		$email_id = email_exists( $postedVars['cemail'] );

		/**new user**/
		if(!$user_id && !$email_id){
			/************************************************************************************
				we do NOT only want to save form fields here that are set to "use for registering"
				but update / add all enabled ones, so let's change the action/method
				and set distinct POST vars
			************************************************************************************/
			remove_action('user_register', array( $this, 'wppizza_user_register_form_save_fields' ),100 );
			add_action('user_register', array( $this, 'wppizza_user_register_order_page' ),100 );
			$_POST=array();
			foreach($postedVars as $k=>$v){
				$_POST[$k]=$v;
			}
			/*generate a pw**/
			$user_password = wp_generate_password( $length=10, $include_standard_special_chars=true );
			/*create the user**/
			$user_id_new = wp_create_user( $postedVars['cemail'], $user_password, $postedVars['cemail'] );
			/**this should never happen really**/
			if(is_wp_error($user_id_new)){
				$output['error']="<div class='wppizza-login-error'>Error: ".$user_id_new->get_error_message()."</div>";
			}
			/*send un/pw to user*/
			if($user_id_new && $user_password!=''){/*bit of overkill*/
				/*old wp versions <4.3**/
 				if ( version_compare( $wp_version, '4.3', '<' ) ) {
            		wp_new_user_notification( $user_id_new, $user_password );
        		}
 				if ( version_compare( $wp_version, '4.3', '==' ) ) {
            		wp_new_user_notification( $user_id_new, 'both' );
        		}
        		if ( version_compare( $wp_version, '4.3.1', '>=' ) ) {
					wp_new_user_notification( $user_id_new, null, 'both' );
        		}
				wp_set_auth_cookie( $user_id_new );/**login too*/
				/*associate order with this userid now**/
				global $wpdb;
				$wpdb->update( 
					$wpdb->prefix . $this->pluginOrderTable, 
					array( 
						'wp_user_id' => (int)$user_id_new,	// integer
					), 
					array( 'hash' => $postedVars['wppizza_hash'] ), 
					array( 
						'%d'	// integer
					), 
					array( '%s' ) //string
				);				
			}
		}else{
			$output['error']="<div class='wppizza-login-error'>".$options['localization']['register_option_create_account_error']['lbl']."</div>";
		}
	}
	print"".json_encode($output);/*not outputted but may one day come in handy for debug purposes*/
	exit();
}

/*******************************************************************************************************
*
*
*	[profile update]
*
*
*******************************************************************************************************/
if(isset($_POST['vars']['type']) && $_POST['vars']['type']=='profile_update'){
	/*****************************************
		[get and parse all post variables to get hash
	*****************************************/
	$params = array();
	parse_str($_POST['vars']['data'], $params);
	/*****************************************
		[get the order]
	*****************************************/
	global $wpdb;
	$order = $wpdb->get_row("SELECT id,order_ini FROM ".$wpdb->prefix . $this->pluginOrderTable." WHERE hash='".$params['wppizza_hash']."' ");
	$oDetails=maybe_unserialize($order->order_ini);
	$oDetails['update_profile']=1;
	/*update order to say we want to update profile when done**/
	$wpdb->query("UPDATE ".$wpdb->prefix . $this->pluginOrderTable." SET order_ini='".maybe_serialize($oDetails)."'WHERE id='".$order->id."' ");
exit();
}

/*******************************************************************************************************
*
*
*	[tip added ]
*
*
*******************************************************************************************************/
if(isset($_POST['vars']['type']) && $_POST['vars']['type']=='add_tips'){

	/***************************************************************
		[get and parse all user post variables and save in session and return parsed $params
	***************************************************************/
	$params = $this->wppizza_sessionise_userdata($_POST['vars']['data'],$options['order_form']);

	/*****************************************
		[sanitize gratuity]
	*****************************************/
	$tips=wppizza_validate_float_only($params['ctips'],2);
	global $wpdb;
	/*might as well delete the previously initialized order. So we do not delete arbitrary stuff when messing with the hash, restrict to INITIALIZED and orders of 3 minutes or less. Ought to be reasonably safe**/
	$res=$wpdb->query( $wpdb->prepare( "DELETE FROM ".$wpdb->prefix . $this->pluginOrderTable." WHERE hash=%s AND payment_status='INITIALIZED' AND order_date > TIMESTAMPADD(MINUTE,-3,NOW()) ",$params['wppizza_hash']));

	/**add tips distincly to session*/
	$_SESSION[$this->pluginSession]['tips']=$tips;

	/*****************************************
		[parse and add all get variables
	*****************************************/
	$getParameters = array();
	if($_POST['vars']['urlGetVars']!=''){
		parse_str(substr($_POST['vars']['urlGetVars'],1), $getParameters);/*loose the '?'  */
	}

	/*********build the location url making sure permalinks are taken care of too**/
	$location=$this->wppizza_set_redirect_url($_POST['vars']['locHref'],$getParameters);


	$vars['location']=$location;

	print"".json_encode($vars)."";
exit();
}
/****************************************************************************************************************************************
*
*
*	[choose and set gateway to calculate surcharges (if any)]
*	[also update order details to be able to use non-redirect overlay/js gateways]
*
****************************************************************************************************************************************/
if(isset($_POST['vars']['type']) && $_POST['vars']['type']=='wppizza-select-gateway'){
	if(count($_POST['vars']['data'])>0){
		$userdata=$this->wppizza_sessionise_userdata($_POST['vars']['data'],$options['order_form']);
	}
	print"".json_encode($_SESSION[$this->pluginSessionGlobal]['userdata'])."";/*not being output anywhere though*/
	exit();
}

/****************************************************************************************************************************************
*
*
*	general function that can be used async to sessionize user data entered on order page (kind of the same as above...)
*
*
****************************************************************************************************************************************/
if(isset($_POST['vars']['type']) && $_POST['vars']['type']=='wppizza-set-userdata'){
	if(count($_POST['vars']['data'])>0){
		$userdata=$this->wppizza_sessionise_userdata($_POST['vars']['data'],$options['order_form']);
	}

	/**update db with customer entered form fields if hash is defined. for js/overlay gateways that do not return user data back to site**/
	if(!empty($_POST['vars']['hash']) && !empty($userdata)){

		/*****************************************
			[get and parse all post variables
		*****************************************/
		$postVars = array();
		parse_str($_POST['vars']['data'], $postVars);

		/*****************************************
			include and ini WPPIZZA_GATEWAYS class
		*****************************************/
		if (!class_exists( 'WPPIZZA_GATEWAYS' ) ) {
			require(WPPIZZA_PATH.'classes/wppizza.gateways.inc.php');
		}
		$WPPIZZA_GATEWAYS=new WPPIZZA_GATEWAYS();

		/****************************************
			update customer_ini with posted customer data
		*****************************************/
		$order=$WPPIZZA_GATEWAYS->wppizza_gateway_get_order_details($_POST['vars']['hash']);
		$WPPIZZA_GATEWAYS->wppizza_gateway_update_customer_details($order->id, $postVars);
	}

	print"".json_encode($_SESSION[$this->pluginSessionGlobal]['userdata'])."";/*not being output anywhere though*/
	exit();
}
/************************************************************************************************
*
*
*	[using a cache plugin, load full cart dynamically]
*
*
************************************************************************************************/
if(isset($_POST['vars']['type']) && $_POST['vars']['type']=='hasCachePlugin'){
		/**********set header********************/
		header('Content-type: application/json');

		ob_start();
		$postAttributes=empty($_POST['vars']['attributes']) ? '' : stripslashes($_POST['vars']['attributes']);
		$attributes=json_decode($postAttributes,true);
		$cart=$this->wppizza_include_shortcode_template('cart',$attributes);
		$markup = ob_get_clean();
		/*return html and cart separately*/
		$res['markup']=$markup;
		$res['cart']=$cart;

	print"".json_encode($res)."";
exit();
}
/************************************************************************************************
*
*
*	[getting totals via shortcode]
*
*
************************************************************************************************/
if(isset($_POST['vars']['type']) && $_POST['vars']['type']=='gettotals'){
	/**********set header********************/
	header('Content-type: application/json');
	$res=wppizza_order_summary($_SESSION[$this->pluginSession],$options);

	$res['itemcount']=0;
	foreach($res['items'] as $item){
	$res['itemcount']+=$item['count'];
	}
	$res['viewcart']='<input class="btn btn-primary" type="button" value="'.$options['localization']['view_cart']['lbl'].'">';
	print"".json_encode($res)."";
exit();
}
/****************************************************************************************************************************************
*
*
*	[checkif shop is still open when submitting order]
*
*
****************************************************************************************************************************************/
if(isset($_POST['vars']['type']) && $_POST['vars']['type']=='checkifopen'){
	$res=array();
	$isOpen=wpizza_are_we_open($options['opening_times_standard'],$options['opening_times_custom'],$options['times_closed_standard']);
	$isOpen = apply_filters('wppizza_filter_is_open', $isOpen);/*let others override*/
	if($isOpen==0){//closed -> alert
		$res['isclosed']=''.$options['localization']['alert_closed']['lbl'].'';
	}
	print"".json_encode($res)."";

exit();
}
/****************************************************************************************************************************************
*
*
*	[send the order by email and update db]
*
*
****************************************************************************************************************************************/
if(isset($_POST['vars']['type']) && $_POST['vars']['type']=='sendorder'){
	/**********set header********************/
	//header('Content-type: text/plain');

	/***************************************************************
		[get and parse all user post variables, save in session and return parsed $params
	***************************************************************/
	$this->wppizza_sessionise_userdata($_POST['vars']['data'],$options['order_form']);

	/*****************************************
		[get and parse all post variables
	*****************************************/
	$params = array();
	parse_str($_POST['vars']['data'], $params);

	/********************************************
		[new send order email class]
	********************************************/
	if (!class_exists( 'WPPIZZA_SEND_EMAILS' ) ) {
		require_once(WPPIZZA_PATH .'classes/wppizza.send-emails.inc.php');
	}
	$WPPIZZA_SEND_EMAILS=new WPPIZZA_SEND_EMAILS();
	$WPPIZZA_SEND_EMAILS->send_email_ajax($params);
exit();
}
/************************************************************************************************
*
*
*	[in case one wants to do/add more things in functions.php]
*
*
************************************************************************************************/
do_action('wppizza_ajax_action',$_POST);

exit();
?>