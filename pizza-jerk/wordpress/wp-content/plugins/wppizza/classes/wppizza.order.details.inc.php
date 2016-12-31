<?php
/**************************************************************************************************************************************

	CLASS - WPPIZZA_ORDER_DETAILS

	gets all order details as array of a specific order by passing it an order id

	currently this is only used for printing the order in the admin order history

	however, over time this will expand to replace the somewhat convoluted coding the plugin uses in different places
	to get these details (when sending/creating emails for example or displaying orders in order history etc

	FUTURE/TODO: get the variables not only from existing order, but also from session data before order has been made/stored


	usage
	require_once(WPPIZZA_PATH.'classes/wppizza.order.details.inc.php');
	$orderdetails=new WPPIZZA_ORDER_DETAILS();
	$orderdetails->setOrderId($id);


**************************************************************************************************************************************/
if (!class_exists( 'WPPizza' ) ) {return ;}

if (!class_exists('WPPIZZA_ORDER_DETAILS')) {
	class WPPIZZA_ORDER_DETAILS extends WPPIZZA_ACTIONS {

	public $orderId=false;
	public $blogId=false;
	public $decode=false;
	public $plaintext=false;
	public $rtl=false;
	public $translate=false;
	public $language=false;
	public $session=false;
	public $type='order_history_print';

/**********************************************************************************************
*
*
*	[construct]
*
*
*********************************************************************************************/
		function __construct() {

			parent::__construct();
			/************************
				[add filters]
			************************/
			/**filter order items when returned from db as its all stored in a array**/
			//add_filter('wppizza_filter_order_details_db_return', array( $this, 'wppizza_filter_order_db_return'),10,2);
			/**filter transaction id appending db id if so required**/
			//add_filter('wppizza_filter_order_details_transaction_id', array( $this, 'wppizza_filter_transaction_id'),10,2);
			/**filter items to sort by category**/
			add_filter('wppizza_filter_print_order_items', array( $this, 'wppizza_filter_items_by_category'),10,2);
			/**filter items to sort by category**/
			add_action('wppizza_orderhistory_item', array( $this, 'wppizza_items_show_order_print_category'));
			/**output category name above each item group  (if enabled) **/
			add_filter('wppizza_filter_print_order_single_item_category', array( $this, 'wppizza_items_print_category'),10,6);
			/**filter localization depending some options set **/
			add_filter('wppizza_filter_order_details_localization', array( $this, 'wppizza_filter_order_details_localization'),10,4);
			/**filter multisite - to get differences between current site and site where order was made**/
			add_filter('wppizza_filter_order_details_multisite', array( $this, 'wppizza_filter_order_details_multisite'),10,4);


			/**wpml**/
//			add_action('init', array( $this, 'wppizza_wpml_localization'),99);//should not be needed anymore
//			add_action('init', array( $this, 'wppizza_wpml_localization_gateways'),99);

		}
/**********************************************************************************************
*
*
*	[public methods]
*
*
*********************************************************************************************/
		/*set  the order id we need to get values for*/
		function setOrderId($orderId){
			$this->orderId=$orderId;
		}
		/**set blog id (might be required in multisite setups - currently not in use )*/
		function setBlogId($blogid){
			$this->blogId=$blogid;
		}
		/*return plaintext instead of html variables if true*/
		function setPlaintext($plaintext){
			$this->plaintext=$plaintext;
		}
		/*decode html encoded things - currently not used but might come in handy at some point*/
		function setDecode($decode){
			$this->decode=$decode;
		}
		/*set right to left - currently not used but might come in handy at some point*/
		function setRtl($rtl){
			$this->rtl=$rtl;
		}
		/*set use translated txt vars*/
		function setTranslate($translate){
			$this->translate=$translate;
		}
		/*set use switch to a distinct wpml language (en, de, etc)*/
		function setLanguage($language){
			$this->language=$language;
		}
		/* use session instead of order id  - currently not used but will allow - in future - session data to be used instead of order id (before order has been executed/stored)*/
		function setSession($session){
			$this->session=$session;
		}
		/* to identify type. defaults to 'order_history_print', but can be used - in future - to use different filters etc for html email, plaintext emails, frontend order history, thank you pages etc etc */
		function setType($type){
			$this->type=$type;
		}

		/**********************************
		*
		*	get all order details as array
		*
		*	@return array
		**********************************/
		function getOrder(){
			static $o=0;$o++;
			global $wpdb;
			/**select the right blog table if set **/
			if($this->blogId!='' && $this->blogId && (int)$this->blogId>1){
				$wpdb->prefix=$wpdb->base_prefix . $this->blogId.'_';
			}
			$orderDetails = $wpdb->get_row("SELECT id, wp_user_id, order_date,  order_update, order_status, customer_ini, order_ini, transaction_id, initiator, payment_status, notes FROM " .$wpdb->prefix . "wppizza_orders WHERE id='".(int)$this->orderId."' ");
			if(!is_object($orderDetails)){$orderDetails=false; return $orderDetails;}

			/**unserialize and filter order data**/
			$oOrder =maybe_unserialize($orderDetails->order_ini);
			//$oOrder = apply_filters('wppizza_filter_order_details_db_return', $oOrder, 'get-order');
			$oOrder = $this->wppizza_filter_order_db_return($oOrder, 'get-order');


			/**unserialize customer data**/
			$oCustomer=maybe_unserialize($orderDetails->customer_ini);

			/**main wppizza options**/
			$pOptions=$this->getPluginOptions();

			/*****************************
				get currency and position
				used in private methods
			******************************/
			$currencydecode=wppizza_email_decode_entities($oOrder['currency'],WPPIZZA_CHARSET);
			$currency['currency']=$currencydecode;
			$currency['currencyiso']=$oOrder['currencyiso'];
			$currency['left']=$currencydecode.' ';
			$currency['right']='';
			if($pOptions['layout']['currency_symbol_position']=='right'){/*right aligned*/
				$currency['left']='';
				$currency['right']=' '.$currencydecode;
			}
			/*allow filtering*/
			$currency['left'] = apply_filters('wppizza_filter_currency_display_left', $currency['left']);
			$currency['right'] = apply_filters('wppizza_filter_currency_display_right', $currency['right']);


			/**************************************************************************
			*
			*	group variables to use in output
			*
			***************************************************************************/
				/******************************************
					site details (useful in multisite)
				******************************************/
				$order['site']=$this->getSiteDetails($this->blogId);

				/******************************************
					parent site details (useful in multisite)
					might return the same as site if order
					was made at parent
				******************************************/
				$order['parent_site']=$this->getSiteDetails($this->blogId, $order['site']);
				/******************************************
					return any differences there might be between
					current site and site the order was made on (if any)
					returns empty array if current site == parent site
					or we are not in a multisite setup
				******************************************/
				$order['multisite']=$this->blogsCompare($this->blogId, $order['site']);
				$order['multisite'] = apply_filters('wppizza_filter_order_details_multisite', $order['multisite'], $this->blogId, $pOptions, $this->type);
				/******************************************
					customer
				******************************************/
				$order['customer']=$this->getCustomerDetails($oCustomer,$pOptions);
				/*******************************
					items
				******************************************/
				$order['items']=$this->getItemDetails($oOrder,$currency);

				/******************************************
					summary
				******************************************/
				$order['summary']=$this->getSummaryDetails($oOrder,$currency,$pOptions);
				/******************************************
					localization
				******************************************/
				$order['localization'] = $this->getLocalization($pOptions);
				/*[multisite setup, parent site only], when displaying orders from ALL sites in parent site, use header/address from site order was made at (if enabled in wppizza->settings))**/
				$order['localization'] = apply_filters('wppizza_filter_order_details_localization', $order['localization'], $pOptions, $this->blogId, $this->type);
				/****************************************
					other order vars (date/payment type/notes  etc)
				*****************************************/
				$order['ordervars']=$this->getOrderDetails($orderDetails,$oOrder,$pOptions,$currency);



			/**allow filter if required - not yet enabled and probably not relevant**/
			//$order = apply_filters('wppizza_order_details_filter_order', $order, $pOptions);
			return $order;
		}
		/************************************************
		*
		*	only get available keys, variables
		*	in case we need them without an
		*	actual order (for sessions etc)
		*
		*	@return array
		************************************************/
		function getOrderVariables(){
			/**get main wppizza options**/
			$pOptions=$this->getPluginOptions();

			/*initialize array**/
			$vars=array();

			/*******************************************
				customer
			******************************************/
			$vars['customer']=$this->getCustomerVariables(false,$pOptions);
			/*******************************************
				items
			******************************************/
			$vars['items']=$this->getItemVariables();


			return $vars;
		}
/**********************************************************************************************
*
*
*
*	[helpers to only get variables/keys]
*
*
*
*********************************************************************************************/
		/**********************************************************************************************
		*
		*	CORRESPONDING TO KEYS AS SET IN getOrderDetails();
		*
		*	[other miscellaneous keys/variables ]
		*
		* 	@$include - array of keys  to return. defaults to something sensible
		* 	@$exclude - array of keys  to exclude. (priority over include)
		*
		*	@return array();
		*********************************************************************************************/
		function getDbFields($include=false,$exclude=false){
			/*do some useful defaults**/
			if(!$include || !is_array($include)){
				$include=array('order_date','order_id','payment_due', 'pickup_delivery', 'payment_type', 'payment_method', 'transaction_id', 'total');
				/*
					other available: 'wp_user_id', 'order_update','payment_status','initiator','notes','total'
				*/
			}

			/**get main wppizza options**/
			$pOptions=$this->getPluginOptions();
			/*on first install, these do not exist yet*/
			//if(!isset($pOptions['localization'])){return;}

			/***********************
				return array
			***********************/
			$orderDetails=array();/*ini*/

			/*wp user id - currently unused*/
			$orderDetails['wp_user_id']=array('key'=>'wp_user_id', 'label'=>$pOptions['localization']['common_label_order_wp_user_id']['lbl']);

			/**order id*/
			//$orderDetails['id']=array('key'=>'id', 'label'=>$pOptions['localization']['common_label_order_order_id']['lbl']);
			$orderDetails['order_id']=array('key'=>'order_id', 'label'=>$pOptions['localization']['common_label_order_order_id']['lbl']);

			/**transaction_id*/
			$orderDetails['transaction_id']=array('key'=>'transaction_id', 'label'=>$pOptions['localization']['common_label_order_transaction_id']['lbl']);

			/**order_date*/
			$orderDetails['order_date']=array('key'=>'order_date', 'label'=>$pOptions['localization']['common_label_order_order_date']['lbl'] );

			/**payment_type*/
			$orderDetails['payment_type']=array('key'=>'payment_type', 'label'=>$pOptions['localization']['common_label_order_payment_type']['lbl']);

			/**payment_status*/
			$orderDetails['payment_method']=array('key'=>'payment_method', 'label'=>$pOptions['localization']['common_label_order_payment_method']['lbl']);

			/**payment_due*/
			$orderDetails['payment_due']=array('key'=>'payment_due', 'label'=>$pOptions['localization']['common_label_order_payment_outstanding']['lbl']);

			/**currency - in use but without label*/
			$orderDetails['currency']=array('key'=>'currency', 'label'=>$pOptions['localization']['common_label_order_currency']['lbl']);
			$orderDetails['currencyiso']=array('key'=>'currencyiso', 'label'=>$pOptions['localization']['common_label_order_currency']['lbl']);

			/**pickup/delivery**/
			$orderDetails['pickup_delivery']=array('key'=>'pickup_delivery', 'label'=>$pOptions['localization']['common_label_order_delivery_type']['lbl']);

			/*total add here too, might be useful*/
			$orderDetails['total']=array('key'=>'total', 'label'=>$pOptions['localization']['order_total']['lbl']);


			/*
				currently unused variables without labels defined yet in localization
				might come in useful somewhere one day
			*/
			//order_update
			$orderDetails['order_update']=array('key'=>'order_update', 'label'=>__('Last Status Update', 'wppizza-locale'));
			//payment_status
			$orderDetails['payment_status']=array('key'=>'payment_status', 'label'=>__('Payment Status', 'wppizza-locale'));
			//initiator
			$orderDetails['initiator']=array('key'=>'initiator', 'label'=>__('Initiator', 'wppizza-locale'));
			//notes
			$orderDetails['notes']=array('key'=>'notes', 'label'=>__('Notes', 'wppizza-locale'));

			/*
				all others unused (not even returnd from db)
				if we want to use these, make sure to set $wpdb->get_row("SELECT xx
				as appropriate in getOrder() too
				for the time being , i think these are wholly unnecessary to return anywhere

				$orderDetails['hash']=array('key'=>'hash', 'label'=>__('Hash', 'wppizza-locale'));
				$orderDetails['transaction_details']=array('key'=>'transaction_details', 'label'=>__('Transaction Details', 'wppizza-locale'));
				$orderDetails['transaction_errors']=array('key'=>'transaction_errors', 'label'=>__('Transaction Errors', 'wppizza-locale'));
				$orderDetails['mail_sent']=array('key'=>'mail_sent', 'label'=>__('Mail Sent ?', 'wppizza-locale'));
				$orderDetails['mail_error']=array('key'=>'mail_error', 'label'=>__('Mail Error', 'wppizza-locale'));
			*/


			/**only INCLUDE set vars**/
			$columns=array();
			foreach($include as $xKey){
				$columns[$xKey]=$orderDetails[$xKey];
			}

			/*any to exclude */
			if($exclude && is_array($exclude)){
			foreach($exclude as $xKey){
				unset($columns[$xKey]);
			}}


			return $columns;

		}
		/**********************************************************************************************
		*
		*	[customer variables keys only]
		*	sorted and disabled removed
		*	@$all - return all vars, enabled or not
		*	@$pOptions - main wppizza plugin options
		*	@$exclude - exclude specific vars/keys
		*
		*	note: ctips is a special case and should be excluded by default, but not excluded in getSummaryVariables
		*
		*	@return array()
		*********************************************************************************************/
		function getCustomerVariables($all=false, $pOptions=false, $exclude=array('ctips')){

			if(!$pOptions){
				$pOptions=$this->getPluginOptions();
			}

			$setFields=array();
			if(isset($pOptions['order_form']) && is_array($pOptions['order_form'])){
			asort($pOptions['order_form']);
			foreach($pOptions['order_form'] as $k=>$v){
				if($all || (!$all && $v['enabled'])){
					$setFields[$v['key']]=$v;
				}
			}}
			/*apply any filters used*/
			$setFields = apply_filters('wppizza_filter_order_form_fields', $setFields);

			/*any others to exclude ?*/
			if($exclude && is_array($exclude)){
			foreach($exclude as $xKey){
				unset($setFields[$xKey]);
			}}

			return $setFields;
		}
		/**********************************************************************************************
		*
		*	[order items keys only]
		* 	@$exclude - array of keys not to return (setting some sensible defaults)
		*
		*	@return array();
		*********************************************************************************************/
		function getItemVariables($exclude=false){

				/*do some useful defaults**/
				if(!$exclude || !is_array($exclude)){
					$exclude=array('postId', 'label', 'catIdSelected', 'currency', 'categories', 'value', 'valuetotal');
				}

				$keys=array();
				$keys['quantity']=array('key'=>'quantity', 'label'=>__('quantity','wppizza-locale'));
				$keys['postId']=array('key'=>'postId', 'label'=>__('menu item id','wppizza-locale'));
				$keys['name']=array('key'=>'name', 'label'=>__('name','wppizza-locale'));
				$keys['size']=array('key'=>'size', 'label'=>__('size ','wppizza-locale'));
				$keys['price']=array('key'=>'price', 'label'=>__('single item price','wppizza-locale'));
				$keys['pricetotal']=array('key'=>'pricetotal', 'label'=>__('subtotal item','wppizza-locale'));
				$keys['value']=array('key'=>'value', 'label'=>__('single item price [no currency]','wppizza-locale'));
				$keys['valuetotal']=array('key'=>'valuetotal', 'label'=>__('subtotal [no currency]','wppizza-locale'));
				$keys['categories']=array('key'=>'categories', 'label'=>__('categories','wppizza-locale'));
				$keys['catIdSelected']=array('key'=>'catIdSelected', 'label'=>__('selected category','wppizza-locale'));
				$keys['currency']=array('key'=>'currency', 'label'=>__('currency','wppizza-locale'));
				$keys['addinfo']=array('key'=>'addinfo', 'label'=>__('additional info','wppizza-locale'));
				$keys['label']=array('key'=>'label', 'label'=>__('premade label [for convenience]','wppizza-locale'));

				foreach($exclude as $xKey){
					unset($keys[$xKey]);
				}
				/*****************************************************
					allow filtering 
				*****************************************************/
				$keys = apply_filters('wppizza_filter_get_item_variables', $keys);
				
			return $keys;
		}
		/**********************************************************************************************
		*
		*	[order summary keys only]
		* 	@$exclude - array of keys not to return
		*
		*	@return array();
		*********************************************************************************************/
		function getSummaryVariables($pOptions=false,$exclude=array()){

			if(!$pOptions){
				$pOptions=$this->getPluginOptions();
			}
			/*which form fields are actually enabled ? (including tips here)*/
			$fieldsEnabled=$this->getCustomerVariables(false, $pOptions, false);

			/**********************************************************
			*	[initialize array
			**********************************************************/
			$keys=array();
			/**********************************************************
			*	[cart items
			**********************************************************/
				$keys['cartitems']=array('key'=>'cartitems', 'label'=>($pOptions['localization']['order_items']['lbl']));
			/**********************************************************
			*	[discount]
			**********************************************************/
				$keys['discount']=array('key'=>'discount', 'label'=>($pOptions['localization']['discount']['lbl']));
			/**********************************************************
			*	[tax ]
			**********************************************************/
				$keys['item_tax']=array('key'=>'item_tax', 'label'=>($pOptions['localization']['item_tax_total']['lbl']));
			/**********************************************************
			*	[delivery charges]
			**********************************************************/
			if($pOptions['order']['delivery_selected']!='no_delivery'){/*delivery disabled*/
					$keys['delivery']=array('key'=>'delivery', 'label'=>($pOptions['localization']['delivery_charges']['lbl']));
			}
			/**********************************************************
			*	[taxes included]
			**********************************************************/
			if($pOptions['order']['taxes_included']){
				$keys['taxes_included']=array('key'=>'taxes_included', 'label'=>sprintf(''.$pOptions['localization']['taxes_included']['lbl'].'',$pOptions['order']['item_tax']));
			}

			/**********************************************************
			*	[handling charges - (most likely to be used for cc payment)]
			**********************************************************/
				$keys['handling_charge']=array('key'=>'handling_charge', 'label'=>$pOptions['localization']['order_page_handling']['lbl']);

			/**********************************************************
			*	[tips )]
			**********************************************************/
			if(isset($fieldsEnabled['ctips'])){
				$keys['tips']=array('key'=>'tips', 'label'=>($pOptions['localization']['tips']['lbl']));
			}
			/**********************************************************
				[order total]
			**********************************************************/
				$keys['total']=array('key'=>'total', 'label'=>($pOptions['localization']['order_total']['lbl']));
			/****************************************************
				[self pickup (enabled and selected) / no delivery offered ]
			****************************************************/
				$keys['self_pickup']=array('key'=>'self_pickup', 'label'=>__('self pickup note','wppizza-locale') );

			/****************************************************
				[self pickup (enabled and selected) / no delivery offered ]
			****************************************************/
				$keys['delivery_note']=array('key'=>'delivery_note', 'label'=>__('delivery note (if not empty)','wppizza-locale') );


				foreach($exclude as $xKey){
					unset($keys[$xKey]);
				}
			/*****************************************************
				allow filtering 
			*****************************************************/
				$keys = apply_filters('wppizza_filter_get_summary_variables', $keys, $pOptions);
				
			return $keys;
		}

		/**********************************************************************************************
		*
		*	[site variables keys only]
		* 	@$exclude - array of keys not to return (with somewhat sensible defaults)
		*
		*	@return array();
		*********************************************************************************************/
		function getSiteVariables($exclude=array('site_id','lang_id')){
			$keys=array();
			if(is_multisite()){
				$keys['parent_site']=array('key'=>'parent_site', 'label'=>__('parent site name (if different)','wppizza-locale'));
				$keys['parent_site_url']=array('key'=>'parent_site_url', 'label'=>__('parent site url (if different)','wppizza-locale'));
				$keys['site_id']=array('key'=>'site_id', 'label'=>__('site id','wppizza-locale'));
				$keys['blog_id']=array('key'=>'blog_id', 'label'=>__('blog id','wppizza-locale'));
			}
			$keys['blogname']=array('key'=>'blogname', 'label'=>__('site name','wppizza-locale'));
			$keys['siteurl']=array('key'=>'siteurl', 'label'=>__('siteurl','wppizza-locale'));
			$keys['header']=array('key'=>'header', 'label'=>__('header','wppizza-locale'));//(->localization) header_order_print_header
			$keys['address']=array('key'=>'address', 'label'=>__('address','wppizza-locale'));//(->localization) header_order_print_shop_address
			$keys['lang_id']=array('key'=>'lang_id', 'label'=>__('lang id','wppizza-locale'));


			foreach($exclude as $xKey){
				unset($keys[$xKey]);
			}
			/*****************************************************
				allow filtering 
			*****************************************************/
			$keys = apply_filters('wppizza_filter_get_site_variables', $keys);
			
		return $keys;
		}
/**********************************************************************************************************************************************************
*
*
*	[private methods]
*
*
**********************************************************************************************************************************************************/
		/**********************************************************************************************
		*
		*	[get parent plugin options - private]
		*
		*********************************************************************************************/
		private function getPluginOptions(){
			/****************************************************************
				include wpml to use translated localization variables.
				will not affect items (they will always be the translated one's
				or - more accurately - be the ones that were put in the cart
				don't use require once
			****************************************************************/
			if($this->translate && function_exists('icl_translate')){// && isset($cDetails['wppizza_wpml_lang']) && $cDetails['wppizza_wpml_lang']!='')
				global $sitepress;
				/*if set, switch to a distinct language*/
				if($this->language){
					$sitepress->switch_lang($this->language);
				}
				require(WPPIZZA_PATH .'inc/wpml.inc.php');
				require(WPPIZZA_PATH .'inc/wpml.gateways.inc.php');
			}
			/***get (possibly wpml'ed) options**/
			return $this->pluginOptions;
		}
		/**********************************************************************************************
		*
		*	[order items - private]
		*
		*********************************************************************************************/
		private function getItemDetails($oOrder, $currency){

			$oItems=array();
			foreach($oOrder['item'] as $itemKey=>$item){
				$oItems[$itemKey]=$item;
				/**for convenience, we concat vars into label and value and add them to array */
				$oItems[$itemKey]['label']=''.$item['quantity'].'x '.$item['name'].' '.$item['size'].' ['.$currency['left'].''.$item['price'].''.$currency['right'].']';
			}
			/**filter new/current extend additional info keys - to be run by external plugin if required**/
			$oItems = apply_filters('wppizza_filter_order_extend', $oItems);


			/****************************************************
				[simplify array of item details]
			****************************************************/
			$items=array();
			foreach($oItems as $itemKey=>$item){
				/*single item vars*/
				$items[$itemKey]['blogId']			=!empty($item['blogId']) ? $item['blogId'] : '' ;
				$items[$itemKey]['postId']			=$item['postId'];
				$items[$itemKey]['name']			=$item['name'];
				$items[$itemKey]['size']			=$item['size'];
				$items[$itemKey]['quantity']		=$item['quantity'];
				$items[$itemKey]['price']			=$currency['left'].$item['price'].$currency['right'];
				$items[$itemKey]['pricetotal']		=$currency['left'].$item['pricetotal'].$currency['right'];
				$items[$itemKey]['value']			=$item['price'];
				$items[$itemKey]['valuetotal']		=$item['pricetotal'];
				$items[$itemKey]['categories']		=$item['categories'];
				$items[$itemKey]['catIdSelected']	=$item['catIdSelected'];
				$items[$itemKey]['currency']		=$currency['currency'];

				/*extended vars*/
				if(!$this->plaintext){/*as html*/
					$items[$itemKey]['addinfo']			=!empty($item['addinfo']['html']) ? $item['addinfo']['html'] : '' ;
				}
				if($this->plaintext){/*as plaintext*/
					$items[$itemKey]['addinfo']			=!empty($item['addinfo']['txt']) ? $item['addinfo']['txt'] : '' ;
				}

				/*extended vars templates*/
				$items[$itemKey]['addinfo_plaintext']	=!empty($item['addinfo']['txt']) ? $item['addinfo']['txt'] : '' ;
				$items[$itemKey]['addinfo_html']		=!empty($item['addinfo']['html']) ? $item['addinfo']['html'] : '' ;

				/*add above concat vars too*/
				$items[$itemKey]['label']			=$item['label'];

				/**all vars - not used, use filter below instead**/
				//$items[$itemKey]['all']				=$item;

				/**allow filtering to add/remove/alter. currently used for SKU's**/
				$items[$itemKey]=apply_filters('wppizza_filter_order_item_details', $items[$itemKey], $item, 'order_item_details');
			}

			/*
			below filters perhaps needed in future for backwards compatibility :
			wppizza_filter_items_by_category

			wppizza_filter_order_items_to_plaintext //if so add filter above in construct (see WPPIZZA_ACTIONS and old wppizza.send-order-emails.inc.php)
			wppizza_filter_order_items_html//if so add filter above in construct (see WPPIZZA_ACTIONS and wppizza.send-order-emails.inc.php)
			*/
			return $items;
		}

		/**********************************************************************************************
		*
		*	[customer details - private]
		*
		*********************************************************************************************/
		private function getCustomerDetails($oCustomer,$pOptions){

			/*get enabled plugin order form fields*/
			$setFields=$this->getCustomerVariables(true, $pOptions);


			$customer=array();
			$customer['post']=array();
			$customer['other']=array();
			foreach($oCustomer as $k=>$v){
				/*array  field labels, values and type of post values*/
				if(isset($setFields[$k])){
					$customer['post'][$k]=array('label'=>$setFields[$k]['lbl'],'value'=>$oCustomer[$k],'type'=>$setFields[$k]['type']);
				}
				/*array any other saves values*/
				if(!isset($setFields[$k])){
					$customer['other'][$k]=$v;//=array('label'=>$setFields[$k]['lbl'],'value'=>$oCustomer[$k],'type'=>$setFields[$k]['type']);
				}
			}

			/**unset some irrelevant vars**/
			unset($customer['other']['wppizza-gateway']);
			unset($customer['other']['wppizza_hash']);

			/**
				filter if required - probably overkill as there is already a filter above
				but perhaps needed in future for backwards compatibility :

				$customer = apply_filters('wppizza_order_details_filter_customer', $customer);
			**/

			/****************************************************
				[simplify array]
			****************************************************/
			$customerDetails=array();
			foreach($customer['post'] as $key=>$values){
				$customerDetails['post'][$key]['label']=$values['label'];
				$customerDetails['post'][$key]['value']=$values['value'];
				$customerDetails['post'][$key]['type']=$values['type'];
			}
			/*might have been added by other plugins*/
			foreach($customer['other'] as $key=>$values){
				$customerDetails['other'][$key]['label']=$values;
			}

			return $customerDetails;
		}

		/**********************************************************************************************
		*
		*	[order summary - private]
		*	we can probably loose all the currencies here. oh well, who knows maybe useful one day
		*
		*********************************************************************************************/
		private function getSummaryDetails($oDetails,$currency,$pOptions){

			/**********************************************************
			*	[initialize array
			**********************************************************/
			$summary=array();
			/**********************************************************
			*	[cart items
			**********************************************************/
				$summary['cartitems']=array('label'=>($pOptions['localization']['order_items']['lbl']),'price'=>$oDetails['total_price_items'],'currency'=>$oDetails['currency'] );
			/**********************************************************
			*	[discount]
			**********************************************************/
			if(wppizza_cast_to_integer($oDetails['discount'])>0){
				$summary['discount']=array('label'=>($pOptions['localization']['discount']['lbl']),'price'=>$oDetails['discount'],'currency'=>$oDetails['currency'] );
			}
			/**********************************************************
			*	[item tax - tax applied to items only]
			**********************************************************/
			if(wppizza_cast_to_integer($oDetails['item_tax'])>0 && !($pOptions['order']['shipping_tax'])){
				$summary['item_tax']=array('label'=>($pOptions['localization']['item_tax_total']['lbl']),'price'=>$oDetails['item_tax'],'currency'=>$oDetails['currency'] );
			}
			/**********************************************************
			*	[delivery charges - no self pickup enabled or selected]
			**********************************************************/
			if($pOptions['order']['delivery_selected']!='no_delivery'){/*delivery disabled*/
			if(!isset($oDetails['selfPickup']) || $oDetails['selfPickup']==0){
				if($oDetails['delivery_charges']!=''){
					$summary['delivery']=array('label'=>($pOptions['localization']['delivery_charges']['lbl']),'price'=>$oDetails['delivery_charges'],'currency'=>$oDetails['currency'] );
				}else{
					$summary['delivery']=array('label'=>($pOptions['localization']['free_delivery']['lbl']),'price'=>'','currency'=>'' );
				}
			}
			}
			/**********************************************************
			*	[item tax - tax applied to items only]
			**********************************************************/
			if(wppizza_cast_to_integer($oDetails['item_tax'])>0 && $pOptions['order']['shipping_tax']){
				$summary['item_tax']=array('label'=>($pOptions['localization']['item_tax_total']['lbl']),'price'=>$oDetails['item_tax'],'currency'=>$oDetails['currency'] );
			}

			/**********************************************************
			*	[taxes included]
			**********************************************************/
			if(wppizza_cast_to_integer($oDetails['taxes_included'])>0 && $pOptions['order']['taxes_included']){
				$summary['taxes_included']=array('label'=>sprintf(''.$pOptions['localization']['taxes_included']['lbl'].'',$pOptions['order']['item_tax']),'price'=>$oDetails['taxes_included'],'currency'=>$oDetails['currency'] );
			}

			/**********************************************************
			*	[handling charges - (most likely to be used for vv payment)]
			**********************************************************/
			if(isset($oDetails['handling_charge']) && wppizza_cast_to_integer($oDetails['handling_charge'])>0){
				$summary['handling_charge']=array('label'=>($pOptions['localization']['order_page_handling']['lbl']),'price'=>$oDetails['handling_charge'],'currency'=>$oDetails['currency'] );
			}
			/**********************************************************
			*	[tips )]
			**********************************************************/
			if(isset($oDetails['tips']) && wppizza_cast_to_integer($oDetails['tips'])>0){
				$summary['tips']=array('label'=>($pOptions['localization']['tips']['lbl']),'price'=>$oDetails['tips'],'currency'=>$oDetails['currency'] );
			}
			/**********************************************************
				[order total]
			**********************************************************/
				$summary['total']=array('label'=>($pOptions['localization']['order_total']['lbl']),'price'=>$oDetails['total'],'currency'=>$oDetails['currency'] );
			/****************************************************
				[self pickup (enabled and selected) or no delivery offered ]
			****************************************************/
			if(isset($oDetails['selfPickup']) && $oDetails['selfPickup']>=1){
				if($oDetails['selfPickup']==1){
					$summary['self_pickup']=array('label'=>($pOptions['localization']['order_page_self_pickup']['lbl']),'price'=>'','currency'=>'', 'type'=>1 );/*get "type" here too*/
				}
				if($oDetails['selfPickup']==2){
					$summary['self_pickup']=array('label'=>($pOptions['localization']['order_page_no_delivery']['lbl']),'price'=>'','currency'=>'', 'type'=>2 );/*get "type" here too*/
				}
			}

			/****************************************************
				[delivery (selected)]
			****************************************************/
			if(empty($oDetails['selfPickup'])){
				$summary['delivery_note']=array('label'=>($pOptions['localization']['order_page_delivery']['lbl']),'price'=>'','currency'=>'');/*get "type" here too*/
			}

			/****************************************************
				[allow filtering of summary]
			****************************************************/
			$summary = apply_filters('wppizza_filter_order_summary_parameters_emails', $summary, $oDetails);

			/****************************************************
				[simplify array taking account of currency left/right, adding type if required  etc]
			****************************************************/
			$summaryDetails=array();
			foreach($summary as $key=>$values){
				$summaryDetails[$key]['label']=$values['label'];
				$summaryDetails[$key]['value']=!empty($values['currency']) ? $currency['left'].$values['price'].$currency['right'] : $values['price'];
				if(isset($values['type'])){
					$summaryDetails[$key]['type']=$values['type'];
				}
			}

			return $summaryDetails;
		}
		/**********************************************************************************************
		*
		*	[miscellaneous single order details - private]
		*
		*********************************************************************************************/
		private function getOrderDetails($orderFields, $oDetails, $pOptions, $currency, $nolabel=false){

			/**********************************
				exclude some labels in front of output
			**********************************/
			if(!$nolabel || !is_array($nolabel)){
				$nolabel['order_date']=true;//i think we should omit label for order date as it's obvious
			}

			/***********************
				format some variables
			***********************/
			/**mk timestamp*/
			$oDateTimeStamp=strtotime($orderFields->order_date);
			$uDateTimeStamp=strtotime($orderFields->order_update);
			/***********************
				format dates
			***********************/
			$order_date="".date_i18n(get_option('date_format'),$oDateTimeStamp)." ".date_i18n(get_option('time_format'),$oDateTimeStamp)."";//format by set wordpress date/time
			$order_update="".date_i18n(get_option('date_format'),$uDateTimeStamp)." ".date_i18n(get_option('time_format'),$uDateTimeStamp)."";//format by set wordpress date/time


			/***********************
				add gateway name/label as payment_type to single order vars
			***********************/
			$gateways=new WPPIZZA_GATEWAYS();
			$gateways->wppizza_instanciate_gateways_frontend();
			$gateways->wppizza_wpml_localization_gateways();
			$gwIni=strtoupper($orderFields->initiator);
			/*payment method*/
			$gw_payment_method='CC';/*ini as cc payment*/
			if($gwIni=='COD'){/*COD is always cash*/
				$gw_payment_method='CASH';
			}
			/**ini just using the simple initiator value*/
			$payment_type=$orderFields->initiator;

			/**use full label if exists*/
			if(isset($gateways->pluginGateways[$gwIni])){
				$payment_type=!empty($gateways->pluginGateways[$gwIni]->gatewayOptions['gateway_label']) ? $gateways->pluginGateways[$gwIni]->gatewayOptions['gateway_label'] : $orderFields->initiator;

				/*in case a customised non CC gateway was added, set back to Cash*/
				if(!empty($gateways->pluginGateways[$gwIni]->gatewayTypeSubmit) && $gateways->pluginGateways[$gwIni]->gatewayTypeSubmit=='ajax'){
					$gw_payment_method='CASH';
				}
			}
			/**get string for paymnt method used*/
			$payment_method=$pOptions['localization']['common_value_order_credit_card']['lbl'];
			if($gw_payment_method=='CASH'){
				$payment_method=$pOptions['localization']['common_value_order_cash']['lbl'];
			}
			/***********************
				payment due (if credit card->0)
			***********************/
			$payment_due=$currency['left'].wppizza_output_format_price(0,$pOptions['layout']['hide_decimals']).$currency['right'];
			if($gw_payment_method=='CASH'){
				$payment_due=$currency['left'].$oDetails['total'].$currency['right'];
			}

			/***********************
				pickup or delivery
			***********************/
			$pickup_delivery=$pOptions['localization']['common_value_order_delivery']['lbl'];
			if(isset($oDetails['selfPickup']) && $oDetails['selfPickup']>=1){
			$pickup_delivery=$pOptions['localization']['common_value_order_pickup']['lbl'];
			}


			/***********************
				return array
			***********************/
			$orderDetails=array();/*ini*/

			/*wp user id - currently unused*/
			$orderDetails['wp_user_id']=array('label'=>!empty($nolabel['wp_user_id']) ? '' : $pOptions['localization']['common_label_order_wp_user_id']['lbl'],'value'=>$orderFields->wp_user_id);

			/**order id*/
			$orderDetails['id']=array('label'=>!empty($nolabel['id']) ? '' : $pOptions['localization']['common_label_order_order_id']['lbl'],'value'=>$orderFields->id);
			$orderDetails['order_id']=array('label'=>!empty($nolabel['order_id']) ? '' : $pOptions['localization']['common_label_order_order_id']['lbl'],'value'=>$orderFields->id);

			/**transaction_id*/
			$orderFields->transaction_id = $this->wppizza_filter_transaction_id($orderFields->transaction_id, $orderFields->id);
			/*NOTE TO SELF: using apply_filters here as below seems to run multiple times if more than one email is being sent. needs to be investigated why at some point. let's use the above for the moment*/
			//$orderFields->transaction_id = apply_filters('wppizza_filter_transaction_id', $orderFields->transaction_id, $orderFields->id);
			$orderDetails['transaction_id']=array('label'=>!empty($nolabel['transaction_id']) ? '' : $pOptions['localization']['common_label_order_transaction_id']['lbl'],'value'=>$orderFields->transaction_id);

			/**order_date*/
			$orderDetails['order_date']=array('label'=>!empty($nolabel['order_date']) ? '' : $pOptions['localization']['common_label_order_order_date']['lbl'],'value'=>$order_date);

			/**payment_type*/
			$orderDetails['payment_type']=array('label'=>!empty($nolabel['payment_type']) ? '' : $pOptions['localization']['common_label_order_payment_type']['lbl'],'value'=>$payment_type);

			/**payment_status*/
			$orderDetails['payment_method']=array('label'=>!empty($nolabel['payment_method']) ? '' : $pOptions['localization']['common_label_order_payment_method']['lbl'],'value'=>$payment_method);

			/**payment_due*/
			$orderDetails['payment_due']=array('label'=>!empty($nolabel['payment_due']) ? '' : $pOptions['localization']['common_label_order_payment_outstanding']['lbl'],'value'=>$payment_due);

			/**currency - in use but without label*/
			$orderDetails['currency']=array('label'=>!empty($nolabel['currency']) ? '' : $pOptions['localization']['common_label_order_currency']['lbl'],'value'=>$currency['currency']);
			$orderDetails['currencyiso']=array('label'=>!empty($nolabel['currencyiso']) ? '' : $pOptions['localization']['common_label_order_currency']['lbl'],'value'=>$currency['currencyiso']);

			/**pickup/delivery**/
			$orderDetails['pickup_delivery']=array('label'=>!empty($nolabel['pickup_delivery']) ? '' : $pOptions['localization']['common_label_order_delivery_type']['lbl'],'value'=>$pickup_delivery);

			/*total add here too, might be useful*/
			$orderDetails['total']=array('label'=>!empty($nolabel['total']) ? '' : $pOptions['localization']['order_total']['lbl'],'value'=>$currency['left'].$oDetails['total'].$currency['right']);


			/*
				currently unused variables without labels defined yet in localization
				might come in useful somewhere one day
			*/
			//notes
			$orderDetails['notes']=array('label'=>'','value'=>$orderFields->notes);

			//payment_status
			$orderDetails['payment_status']=array('label'=>'','value'=>$orderFields->payment_status);

			//order_update
			$orderDetails['order_update']=array('label'=>'','value'=>$order_update);

			//initiator
			$orderDetails['initiator']=array('label'=>'','value'=>$orderFields->initiator);

			return $orderDetails;
		}
		/**********************************************************************************************
		*
		*	[site details  - private - return '' if not multisite to start off with]
		*
		*********************************************************************************************/
		private function getSiteDetails($blogid, $siteOfOrder=false){


			$siteDetails=array();
			$localizedVars=$this->getLocalization($this->pluginOptions);

			/* no blogid*/
			if(!$blogid || $blogid==''){
				return $siteDetails;
			}


			/*multisite*/
			if($blogid){// && $blogid>1

				if(is_multisite()){
					/**get parents details if different**/
					if($siteOfOrder && is_array($siteOfOrder)){
						/**same as site, just return site details we already have**/
						if($blogid==BLOG_ID_CURRENT_SITE){
							$sDetails=$siteOfOrder;
							return $sDetails;
						}else{
							$sDetails=get_blog_details(BLOG_ID_CURRENT_SITE);
						}
					}else{
						$sDetails=get_blog_details($blogid);
					}

					$siteDetails['site_id']=$sDetails->site_id;
					$siteDetails['blog_id']=$sDetails->blog_id;
					$siteDetails['lang_id']=$sDetails->lang_id;
					$siteDetails['siteurl']=$sDetails->siteurl;
					$siteDetails['blogname']=$sDetails->blogname;

					$siteDetails['parent_site']=($sDetails->blog_id!=BLOG_ID_CURRENT_SITE) ? ''.$sDetails->blogname.'' : '';
					$siteDetails['parent_site_url']=($sDetails->blog_id!=BLOG_ID_CURRENT_SITE) ? ''.$sDetails->siteurl.'' : '';

					/**/
					$siteDetails['header']=$localizedVars['header_order_print_header'];
					$siteDetails['address']=$localizedVars['header_order_print_shop_address'];



				}
				if(!is_multisite()){
					$siteDetails['site_id']=1;
					$siteDetails['blog_id']=1;
					$siteDetails['lang_id']=get_bloginfo('language');
					$siteDetails['siteurl']=get_bloginfo('url');
					$siteDetails['blogname']=get_bloginfo('name');
					/**/
					$siteDetails['header']=$localizedVars['header_order_print_header'];
					$siteDetails['address']=$localizedVars['header_order_print_shop_address'];

					//header
					//address
					$siteDetails['parent_site']=false;
				}
			}


			return $siteDetails;
		}

		/**********************************************************************************************
		*
		*	[get differences - if any - between site where oder was made and current (parent) site]
		*
		*********************************************************************************************/
		private function blogsCompare($blogid, $site){
			$differences=array();

			if(is_multisite()){
				/**same blog, return empty array**/
				if(!$blogid || $blogid=='' || $blogid==BLOG_ID_CURRENT_SITE){
					return $differences;
				}
				/**blogid is not parent and not empty, return child site details **/
				else{
					$differences=$site;
					/**switch to child blog add localization vars**/
					switch_to_blog($blogid);
					/**switch and get options from blog we switched to **/
					$blogOptions=get_option(WPPIZZA_SLUG);
					$differences['localization']=$this->getLocalization($blogOptions);
					/**restore**/
					restore_current_blog();
				}
			}
			return $differences;
		}
		/**********************************************************************************************
		*
		*	[localization variables - private]
		*
		*********************************************************************************************/
		private function getLocalization($pOptions){
			/**txt variables from settings->localization additional vars > localization_confirmation_form*/
			$localize = array_merge($pOptions['localization'],$pOptions['localization_confirmation_form']);
			$txt=array();
			foreach($localize as $k=>$v){
				if(!$this->plaintext){/*as html*/
					$txt[$k]=$v['lbl'];
				}
				if($this->plaintext){/*as plaintext*/
					$txt[$k]=wppizza_email_decode_entities($v['lbl'],WPPIZZA_CHARSET);
				}
			}
			return $txt;
		}


/**********************************************************************************************************************************************************
*
*
*	[filters]
*	i think there is a better place/file for those, but for the time being leave them here
*
**********************************************************************************************************************************************************/
		/**********************************************************************************************
		*
		*
		*	[localization variables - filter depending on options - only in multisite setups]
		*
		*	this will no doubt grow / change over time allowing for more options
		*
		*
		*********************************************************************************************/
		function wppizza_filter_order_details_localization($localization, $pOptions, $blogId, $type){

			/*************************

				multisite only

			**************************/
			if(is_multisite()){
				/**
					check if we need to switch blog,
				**/
				$switchBlog=false;
				if($blogId!=BLOG_ID_CURRENT_SITE){
					$switchBlog=true;
				}
				/*
					if we actually need to switch blogs
					otherwise don't change do anything
				*/
				if($switchBlog){

					/** now switch blog **/
					switch_to_blog($blogId);

					/*******************************************************************************
					*
					*
					*	filter -> order history print
					*
					*
					*******************************************************************************/
					if($type=='order_history_print'){

						/************
							parent site only, set to display ALL orders from ALL sites
							will be false for child sites.
							alter name and address in header
						************/
						if($pOptions['plugin_data']['wp_multisite_order_history_all_sites'] && !empty($pOptions['plugin_data']['wp_multisite_order_history_print']['header_from_child'])){

							/**switch and get options from blog we switched to **/
							$switchedBlogWppizzaOptions=get_option(WPPIZZA_SLUG);

							/**set name and address**/
							$localization['header_order_print_header']=$switchedBlogWppizzaOptions['localization']['header_order_print_header']['lbl'];
							$localization['header_order_print_shop_address']=$switchedBlogWppizzaOptions['localization']['header_order_print_shop_address']['lbl'];
						}
					}


					/**restore current blog **/
					restore_current_blog();
				}
			}


			return $localization;
		}
		/**********************************************************************************************
		*
		*
		*	[multisite]
		*	get the info of the site where the order was ORIGINALLY made
		*	for example: on multisite parent sites we can print orders from all child sites which
		*	by default would print the header/info of the parent site
		*	if we also want to display the info of the site where the order was originally made we can use the returned vars below
		*
		*	returns empty array if not on parent site, not in multisite setups or site of order == parent site
		*********************************************************************************************/
		function wppizza_filter_order_details_multisite($multiSiteInfo, $blogId, $pOptions, $type){

			/*not multisite or no info available**/
			if(!is_multisite()  || empty($blogId) || count($multiSiteInfo)<=0){
				return array();
			}


			/*******************************************************************************
			*
			*	filter -> order history print
			*	print small blogname under timestamp if current site differs from site where
			*	order was made. UNSETS ALL OTHER VARS
			*
			*******************************************************************************/
			if($type=='order_history_print'){
			 	/**************
			 		order was made on child site => $blogId will not be empty
			 		AND
			 		we are on parent site with orders from all sites showing
			 		AND
			 		we are NOT using headers from sites where order was made
			 		AND
			 		we want to display small original site name under order date
			 	*************/
			 	if(	!empty($blogId) &&
			 		!empty($pOptions['plugin_data']['wp_multisite_order_history_all_sites']) &&
			 		empty($pOptions['plugin_data']['wp_multisite_order_history_print']['header_from_child']) &&
			 		!empty($pOptions['plugin_data']['wp_multisite_order_history_print']['multisite_info'])
			 	){

					/**only return site name**/
					$showInfo=array();
					$showInfo['header_order_print_header']=$multiSiteInfo['localization']['header_order_print_header'];
					/*if someone wants to override it*/
					$showInfo = apply_filters('wppizza_filter_order_details_multisite_print_order', $showInfo, $multiSiteInfo, $blogId, $pOptions);

				return $showInfo;
			 	}
			}

			return array();
		}
	}
}
?>