<?php
/********************************************************************

	[FOR OLD LEGACY GATEWAYS ONLY - THIS FILE WILL BE REMOVED]

********************************************************************/
if (!class_exists( 'WPPizza' ) ) {return;}

	class WPPIZZA_SEND_ORDER_EMAILS extends WPPIZZA_ACTIONS {

		function __construct() {
			parent::__construct();

			/**extend**/
			$this->wppizza_order_emails_extend();

			/**timestamp the order*/
			$this->currentTime= current_time('timestamp');
			//$this->orderTimestamp =date("d-M-Y H:i:s", current_time('timestamp'));
			$this->orderTimestamp ="".date_i18n(get_option('date_format'),$this->currentTime)." ".date_i18n(get_option('time_format'),$this->currentTime)."";


			/**name and email of whoever is ordering*/
			$this->orderClientName 	='';
			$this->orderClientEmail ='';

			/** post variables from order form **/
			$this->orderPostVars	=$_POST;

			/**subject vars for email subject**/
			$this->subjectPrefix 	=	wppizza_email_decode_entities(get_bloginfo(),WPPIZZA_CHARSET).': ';
			$this->subject 			=	wppizza_email_decode_entities(''.$this->pluginOptions['localization']['your_order']['lbl'].'',WPPIZZA_CHARSET).' ';
			$this->subjectSuffix 	=	''.$this->orderTimestamp.'';

			/**make subject filterable**/
			$this->subjectPrefix = apply_filters('wppizza_filter_email_subject_prefix', $this->subjectPrefix);
			$this->subject = apply_filters('wppizza_filter_email_subject', $this->subject);
			$this->subjectSuffix = apply_filters('wppizza_filter_email_subject_suffix', $this->subjectSuffix);

			/*******thank you when returning : totally superflous i think, but leave it here for the moment**********/
			//add_action('gateway_order_on_thankyou', array( $this, 'gateway_order_on_thankyou_process'));


			/************************
				[add filters
			************************/
			/**filter order items when returned from db as its all stored in a array**/
			add_filter('wppizza_filter_order_db_return', array( $this, 'wppizza_filter_order_db_return'),10,1);

			/****filter transaction id's******/
			add_filter( 'wppizza_email_filter_transaction_id', array( $this, 'wppizza_filter_transaction_id'),10,2);

			/**filter vars after they've come out of the db***/
			add_filter('wppizza_filter_order_additional_info', array( $this, 'wppizza_filter_order_additional_info'),10,1);/*customer details to plaintext str*/
			add_filter('wppizza_filter_customer_details_to_plaintext', array( $this, 'wppizza_filter_customer_details_to_plaintext'),10,1);/*customer details to plaintext str*/
			add_filter('wppizza_filter_order_items_to_plaintext', array( $this, 'wppizza_filter_order_items_to_plaintext'),10,1);/*order items  to plaintext str*/
			add_filter('wppizza_filter_order_summary_to_plaintext', array( $this, 'wppizza_filter_order_summary_to_plaintext'),10,1);/*order summary  to plaintext str*/
			add_filter('wppizza_filter_customer_details_html', array( $this, 'wppizza_filter_customer_details_html'),10,1);/*order items to html*/
			add_filter('wppizza_filter_order_items_html', array( $this, 'wppizza_filter_order_items_html'),10,2);/*order items to html*/


			/**filter and sort selected items by their categoryies**/
			add_filter( 'wppizza_emailhtml_filter_items', array( $this, 'wppizza_filter_items_by_category'),10,2);
			add_filter( 'wppizza_emailplaintext_filter_items', array( $this, 'wppizza_filter_items_by_category'),10,2);
			add_filter( 'wppizza_show_order_filter_items', array( $this, 'wppizza_filter_items_by_category'),10,2);

			/**print category **/
			add_action('wppizza_emailhtml_item', array( $this, 'wppizza_items_emailhtml_print_category'),10,2);
			add_filter('wppizza_emailplaintext_item', array( $this, 'wppizza_items_emailplaintext_print_category'),10,2);
			add_action('wppizza_show_order_item', array( $this, 'wppizza_items_show_order_print_category'));


			/**wpml**/
			add_action('init', array( $this, 'wppizza_wpml_localization'),99);
			add_action('init', array( $this, 'wppizza_wpml_localization_gateways'),99);


			/* we also need any overrides by extensions in the mmain class to be used here**/
			$this->wppizza_extend();
		}

		/***************************************************************
			[allow some extension classes to allow to modify variables]
			class must start with 'WPPIZZA_ORDER_EMAILS_EXTEND_'
		***************************************************************/
		function wppizza_order_emails_extend(){
			$allClasses=get_declared_classes();
			$wppizzaOrderExtend=array();
			foreach ($allClasses AS $oe=>$class){
				$chkStr=substr($class,0,28);
				if($chkStr=='WPPIZZA_ORDER_EMAILS_EXTEND_'){
					$wppizzaOrderExtend[$oe]=new $class;
					foreach($wppizzaOrderExtend[$oe] as $k=>$v){
						$this->$k=$v;
					}
				}
			}
			return ;
		}

		/*****************************************************************************************************************************
		*
		*	[construct plaintext and html email from fields in db]
		*
		*****************************************************************************************************************************/
		function wppizza_order_email($orderid,$blogid=false){
			global $wpdb;
			/**select the right blog table */
			if($blogid && is_int($blogid) && $blogid>1){$wpdb->prefix=$wpdb->base_prefix . $blogid.'_';}

			$res = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix . $this->pluginOrderTable." WHERE id='".$orderid."' ");

			/*initialize vars**/
			if($res){

				/*********************************************************************
				*
				*
				*	get and filter customer and order details as required
				*
				*
				**********************************************************************/
				/*cutstomer details**/
				$cDetails=maybe_unserialize($res->customer_ini);


				/****************************************************************
					include wpml to also send store/emails translated.
					will not affect items (they will always be the translated one's
					or - more accurately - be the ones that were put in the cart
					don't use require once
				****************************************************************/
				/**set appropriate language. as this can be a language agnostic ipn request, set it specifically depending on what was stored in the db**/
				if(function_exists('icl_translate') && isset($cDetails['wppizza_wpml_lang']) && $cDetails['wppizza_wpml_lang']!=''){
						global $sitepress;
						/*polylang for instance does not recognise switch_lang*/
						if(method_exists($sitepress, 'switch_lang')){
							$sitepress->switch_lang($cDetails['wppizza_wpml_lang']);
						}
						require(WPPIZZA_PATH .'inc/wpml.inc.php');
						require(WPPIZZA_PATH .'inc/wpml.gateways.inc.php');
				}
				/***get (possibly wpml'ed) options**/
				$pOptions=$this->pluginOptions;


				/*order details: unserialize and filter**/
				$oIni=maybe_unserialize($res->order_ini);
				$oDetails = apply_filters('wppizza_filter_order_db_return', $oIni);
				/*********************************************************************
				*
				*		[currency position]
				*
				*********************************************************************/
				$currency_left=wppizza_email_decode_entities($oDetails['currency'],WPPIZZA_CHARSET).' ';
				$currency_right='';
				if($this->pluginOptions['layout']['currency_symbol_position']=='right'){/*right aligned*/
					$currency_left='';
					$currency_right=' '.wppizza_email_decode_entities($oDetails['currency'],WPPIZZA_CHARSET);
				}


				/*********************************************************************
				*
				*		[update user meta data]
				*
				*********************************************************************/
				if($res->wp_user_id>0){
					/**update profile**/
					if(!empty($oIni['update_profile'])){
	    				$ff=$pOptions['order_form'];
	    				$ff = apply_filters('wppizza_filter_formfields_update_profile', $ff);
						foreach( $ff as $field ) {
						if(!empty($field['enabled'])) {
							if( $field['type']!='select'){
								update_user_meta( $res->wp_user_id, 'wppizza_'.$field['key'], wppizza_validate_string($cDetails[$field['key']]) );	/*we've validated already, but lets just be save*/
							}else{
								$selKey = array_search($cDetails[$field['key']], $field['value']);
								update_user_meta( $res->wp_user_id, 'wppizza_'.$field['key'], $selKey );
							}
						}}
						/**also update WP email...hmmm better not*/
						//if(!empty($field['enabled']) && $field['key']=='cemail' && !empty($cDetails['cemail'])) {
						//	wp_update_user( array ( 'ID' => $res->wp_user_id, 'user_email' => $cDetails['cemail'] ) ) ;
						//}
					}
					/**the below isnt really needed anymore, but - for legacy reasons - let's keep it for the moment*/
					$userMeta=$cDetails;
					/*tidy up a bit*/
					if($userMeta['wppizza-gateway']){unset($userMeta['wppizza-gateway']);}
					if($userMeta['wppizza_hash']){unset($userMeta['wppizza_hash']);}
					if(isset($userMeta['update_profile'])){unset($userMeta['update_profile']);}
					update_user_meta($res->wp_user_id, 'wppizza_user_meta', $userMeta);
				}
				/*********************************************************************
				*
				*
				*	customer details : posted and stored variables from order page
				*
				*
				**********************************************************************/

				/*********************************************
				*	[posted input fields of this plugin]
				*********************************************/
				$wppizzaEmailCustomerDetails=array();
				/**protect these keys, so no other extension uses it*/
				$protectedKeys=array();
				foreach($this->pluginOptions['order_form'] as $k=>$v){
					$protectedKeys[$v['key']]=$v;
				}
				$protectedKeys = apply_filters('wppizza_filter_order_form_fields', $protectedKeys);

				$i=0;
				foreach($cDetails as $k=>$v){
					/*****default input fields of this plugin*****/
					if(isset($protectedKeys[$k])){
						$wppizzaEmailCustomerDetails[]=array('label'=>$protectedKeys[$k]['lbl'],'value'=>$cDetails[$k],'type'=>$protectedKeys[$k]['type'],'key'=>$k);
					}
					/**********************************************************************************************************
					*
					*
					*	THIS BIT BELOW SHOULD NOW BE REDUNDANT NOW AS WE CAN UST USE THE FILTER ABOVE TO ADD TO THE - SO CALLED - PROTECTED KEYS
					*	let's leave it here for now, but it will probably be removed in future versions, as it has never been documnted publicly anyway
					*	as far as i know......
					*
					**********************************************************************************************************/
					/*********************************************************************
						if another plugin/extension wants to add field value pairs, make sure
						its an array having [label] and [value] to display in email
						i.e:
						<input type="hidden" name="distinct_name[label]" value="some value"/>';
						<input type="text" name="distinct_name[value]" value="some value"/>';
						(make sure there are no clashes with other input fields)
						ought to make this "classable" at some point anyway
					**********************************************************************/
					if(!isset($protectedKeys[$k])){
						if(is_array($v) && isset($v['label']) && isset($v['value']) && !isset($protectedKeys[$k]) ){
							$wppizzaEmailCustomerDetails[]=array('label'=>$v['label'],'value'=>$v['value'],'type'=>'text', 'key'=>$k);
						}
					}
				}
				/*****************************************************************************************
				*
				*
				*	[order items: individual items]
				*
				*
				****************************************************************************************/
				$wppizzaEmailOrderItems=array();

				foreach($oDetails['item'] as $k=>$v){
					$wppizzaEmailOrderItems[$k]=$v;
					/**for convenience, we concat vars into label and value and add them to array */
					$wppizzaEmailOrderItems[$k]['label']=''.$v['quantity'].'x '.$v['name'].' '.$v['size'].' ['.$currency_left.''.$v['price'].''.$currency_right.']';
					$wppizzaEmailOrderItems[$k]['value']=''.$currency_left.$v['pricetotal'].$currency_right.'';
				}
				/**********************************************************************************************
				*
				*
				*	[order summary]
				*	--we can probably loose all the currencies here. oh well. who knows maybe useful one day
				*
				*********************************************************************************************/
				$wppizzaEmailOrderSummary=array();
				/**********************************************************
				*	[cart items
				**********************************************************/
					$wppizzaEmailOrderSummary['cartitems']=array('label'=>($pOptions['localization']['order_items']['lbl']),'price'=>$oDetails['total_price_items'],'currency'=>$oDetails['currency'] );
				/**********************************************************
				*	[discount]
				**********************************************************/
				if($oDetails['discount']>0){
					$wppizzaEmailOrderSummary['discount']=array('label'=>($pOptions['localization']['discount']['lbl']),'price'=>$oDetails['discount'],'currency'=>$oDetails['currency'] );
				}
				/**********************************************************
				*	[item tax - tax applied to items only]
				**********************************************************/
				if($oDetails['item_tax']>0 && !($pOptions['order']['shipping_tax'])){
					$wppizzaEmailOrderSummary['item_tax']=array('label'=>($pOptions['localization']['item_tax_total']['lbl']),'price'=>$oDetails['item_tax'],'currency'=>$oDetails['currency'] );
				}
				/**********************************************************
				*	[delivery charges - no self pickup enabled or selected]
				**********************************************************/
				if($pOptions['order']['delivery_selected']!='no_delivery'){/*delivery disabled*/
				if(!isset($oDetails['selfPickup']) || $oDetails['selfPickup']==0){
					if($oDetails['delivery_charges']!=''){
						$wppizzaEmailOrderSummary['delivery']=array('label'=>($pOptions['localization']['delivery_charges']['lbl']),'price'=>$oDetails['delivery_charges'],'currency'=>$oDetails['currency'] );
					}else{
						$wppizzaEmailOrderSummary['delivery']=array('label'=>($pOptions['localization']['free_delivery']['lbl']),'price'=>'','currency'=>'' );
					}
				}
				}
				/**********************************************************
				*	[item tax - tax applied to items only]
				**********************************************************/
				if($oDetails['item_tax']>0 && $pOptions['order']['shipping_tax']){
					$wppizzaEmailOrderSummary['item_tax']=array('label'=>($pOptions['localization']['item_tax_total']['lbl']),'price'=>$oDetails['item_tax'],'currency'=>$oDetails['currency'] );
				}

				/**********************************************************
				*	[taxes included]
				**********************************************************/
				if($oDetails['taxes_included']>0 && $pOptions['order']['taxes_included']){
					$wppizzaEmailOrderSummary['taxes_included']=array('label'=>sprintf(''.$pOptions['localization']['taxes_included']['lbl'].'',$pOptions['order']['item_tax']),'price'=>$oDetails['taxes_included'],'currency'=>$oDetails['currency'] );
				}

				/**********************************************************
				*	[handling charges - (most likely to be used for vv payment)]
				**********************************************************/
				if(isset($oDetails['handling_charge']) && $oDetails['handling_charge']>0){
					$wppizzaEmailOrderSummary['handling_charge']=array('label'=>($pOptions['localization']['order_page_handling']['lbl']),'price'=>wppizza_output_format_price($oDetails['handling_charge'],$pOptions['layout']['hide_decimals']),'currency'=>$oDetails['currency'] );
				}
				/**********************************************************
				*	[tips )]
				**********************************************************/
				if(isset($oDetails['tips']) && $oDetails['tips']>0){
					$wppizzaEmailOrderSummary['tips']=array('label'=>($pOptions['localization']['tips']['lbl']),'price'=>wppizza_output_format_price($oDetails['tips'],$pOptions['layout']['hide_decimals']),'currency'=>$oDetails['currency'] );
				}
				/**********************************************************
					[order total]
				**********************************************************/
					$wppizzaEmailOrderSummary['total']=array('label'=>($pOptions['localization']['order_total']['lbl']),'price'=>$oDetails['total'],'currency'=>$oDetails['currency'] );
				/****************************************************
					[self pickup (enabled and selected) / no delivery offered ]
				****************************************************/
				if(isset($oDetails['selfPickup']) && $oDetails['selfPickup']>=1){
					if($oDetails['selfPickup']==1){
						$wppizzaEmailOrderSummary['self_pickup']=array('label'=>($pOptions['localization']['order_page_self_pickup']['lbl']),'price'=>'','currency'=>'' );
					}
					if($oDetails['selfPickup']==2){
						$wppizzaEmailOrderSummary['self_pickup']=array('label'=>($pOptions['localization']['order_page_no_delivery']['lbl']),'price'=>'','currency'=>'' );
					}
				}

		/*********************************************************************************************************************************
		*
		*
		*
		*	[now lets do something with it all, like filtering etc]
		*
		*
		*
		**********************************************************************************************************************************/

				/**filter old legacy additional info keys**/
				$wppizzaEmailOrderItems = apply_filters('wppizza_filter_order_additional_info', $wppizzaEmailOrderItems);
				/**filter new/current extend additional info keys**/
				$wppizzaEmailOrderItems = apply_filters('wppizza_filter_order_extend', $wppizzaEmailOrderItems);

				/****************************************************
					[allow filtering of summary too]
				****************************************************/
				$wppizzaEmailOrderSummary = apply_filters('wppizza_filter_order_summary_parameters_emails', $wppizzaEmailOrderSummary,$oDetails);


				/***********************************************************************************************
				*
				*	[set the relevant class vars]
				*
				***********************************************************************************************/

					/**********************************************
						[all db vals - maybe useful at some point in the future]
					************************************************/
					$this->orderResults=$res;
					/***********************************************
						[set currency etc]
					************************************************/
					$this->orderCurrency=$oDetails['currency'];
					$this->orderTransactionId=$res->transaction_id;
					$this->orderGatewayUsed=$res->initiator;
					/***********************************************
						[set localization vars]
					************************************************/
					foreach($pOptions['localization'] as $k=>$v){
						$orderLabel['html'][$k]=$v['lbl'];
						$orderLabel['plaintext'][$k]=wppizza_email_decode_entities($v['lbl'],WPPIZZA_CHARSET);
					}
					$this->orderLabels=$orderLabel;


				/************************************************************************************************************************
				*
				*	[set plaintext variables for emails and order history]
				*	lets get all the plaintext things we need, making htmldecoded strings out of customer details and summary
				*	, and htmldecoded array out of order items to be used in plaintext email template and to save into order history
				*
				************************************************************************************************************************/
					/***********************************************
						[set general vars]
					************************************************/
					$gatewayUsed=$res->initiator;

					/**get gateway frontend label instead of just COD or similar**/
					$gatewayLabel=$res->initiator;
					$wppizzaGateways=new WPPIZZA_GATEWAYS();
					$this->pluginGateways=$wppizzaGateways->wppizza_instanciate_gateways_frontend();
					if(isset($this->pluginGateways[$res->initiator])){
						$gatewayLabel=!empty($this->pluginGateways[$res->initiator]->gatewayOptions['gateway_label']) ? $this->pluginGateways[$res->initiator]->gatewayOptions['gateway_label'] : $gatewayLabel;
					}
					/**********************/

					$transactionId=$res->transaction_id;
					/**filter as required**/
					$transactionId = apply_filters('wppizza_email_filter_transaction_id', $transactionId, $res->id );

					$nowdate=$this->orderTimestamp;
					$orderLabel=$this->orderLabels['plaintext'];


					/**customer details as plaintext string: to use in plaintext emails and save into order history->customer details**/
					$emailPlaintext['customer_details'] = apply_filters('wppizza_filter_customer_details_to_plaintext', $wppizzaEmailCustomerDetails);
					/**order details as plaintext string: to use in plaintext emails and save into order history->order details**/
					$emailPlaintext['items'] = apply_filters('wppizza_filter_order_items_to_plaintext', $wppizzaEmailOrderItems);/**for plaintext email template**/

					/**items as string to insert into db**/
					$emailPlaintext['db_items'] ='';
						/***allow filtering of items (sort, add categories and whatnot)****/
						$emailPlaintext['items'] = apply_filters('wppizza_emailplaintext_filter_items', $emailPlaintext['items'], 'plaintextemail');
						foreach($emailPlaintext['items'] as $k=>$v){
							/***allow action per item - probably to use in conjunction with filter above****/
							$emailPlaintext['db_items'] = apply_filters('wppizza_emailplaintext_item', $v, $emailPlaintext['db_items']);

							$strPartLeft=''.$v['label'].'';/*made up of => '.$v['quantity'].'x '.$v['name'].' '.$v['size'].' ['.$v['currency'].' '.$v['price'].']'*/
							$spaces=75-strlen($strPartLeft);
							$strPartRight=''.$v['value'].'';/*made up of => '.$v['currency'].' '.$v['pricetotal'].'*/
							/**add to string, spacing left and right out somewhat and put linebreak before any additional info**/
							$emailPlaintext['db_items'].=''.$strPartLeft.''.str_pad($strPartRight,$spaces," ",STR_PAD_LEFT).PHP_EOL.'';

							/**NOTE: DO NOT DELETE OR ALTER THE ADDITIONAL INFO DECLARATIONS OR YOU MIGHT BREAK THINGS. IF NOT NOW THAN POSSIBLY IN THE FUTURE AS OTHER EXTENSIONS MAY RELY ON THIS!!!*/
							if(isset($v['additional_info']) && trim($v['additional_info'])!=''){$emailPlaintext['db_items'].=''.$v['additional_info'].'';
								/**add additional line break as spacer between items**/
								$emailPlaintext['db_items'].=PHP_EOL;
							}
						}

					/**summary details as plaintext string: to use in plaintext emails and save into order history->order details**/
					$emailPlaintext['order_summary'] = apply_filters('wppizza_filter_order_summary_to_plaintext', $wppizzaEmailOrderSummary);

					/**include plaintext template**/
					$orderEmailPlaintext='';
					if(file_exists( $this->pluginTemplateDir . '/wppizza-order-email-plaintext.php')){
						ob_start();
						require_once($this->pluginTemplateDir.'/wppizza-order-email-plaintext.php');
						$orderEmailPlaintext = ob_get_clean();
					}else{
						ob_start();
						require_once(WPPIZZA_PATH.'templates/wppizza-order-email-plaintext.php');
						$orderEmailPlaintext = ob_get_clean();
					}


					$this->orderMessage['plaintext']="".PHP_EOL.PHP_EOL.$orderEmailPlaintext.PHP_EOL ;

					/***********************************************
						[set html email vars]
					************************************************/
					$this->orderMessage['html']['customer_details']=$wppizzaEmailCustomerDetails;
					$this->orderMessage['html']['order_items']=$wppizzaEmailOrderItems;
					$this->orderMessage['html']['order_summary']=$wppizzaEmailOrderSummary;

					/***********************************************
						[customer and order details to be saved in db and displayed in history]
					************************************************/
					$this->customerDetails=esc_sql($emailPlaintext['customer_details']);
					$this->orderDetails=esc_sql(PHP_EOL.$emailPlaintext['db_items'].PHP_EOL.$emailPlaintext['order_summary'].PHP_EOL);
					/***********************************************************
						[set name and email of the the person that is ordering]
					***********************************************************/
					$recipientName =!empty($cDetails['cname']) ? wppizza_validate_string($cDetails['cname']) : '';
					$fromEmails=!empty($cDetails['cemail']) ? wppizza_validate_email_array($cDetails['cemail']) : '';
					$this->orderClientName= wppizza_email_decode_entities($recipientName,WPPIZZA_CHARSET).'';
					$this->orderClientEmail=!empty($fromEmails[0]) ? $fromEmails[0] : '';


					/**update db entry with the current time timestamp of when the order was actually send**/
					$orderDate=date('Y-m-d H:i:s',$this->currentTime);
					/**add timestamp to order_ini**/
					$oIni['time']=$this->currentTime;

					$wpdb->query("UPDATE ".$wpdb->prefix  . $this->pluginOrderTable." SET order_date='".$orderDate."',order_ini='".esc_sql(serialize($oIni))."' WHERE id='".$orderid."' ");

			}
			return;
		}

		/*****************************************************************************************************************************
		*
		*	[choose function to send email]
		*	[send order email]
		*	[return status -> "true" if successful, "false" if not]
		*	[if false, will return error message and var[mailer] to indicate which function was used]
		*
		*****************************************************************************************************************************/
		function wppizza_order_send_email($orderid=false, $blogid=false){//,$options=false

			$options=$this->pluginOptions;
			$options = apply_filters('wppizza_filter_order_email_options', $options );

			/***create/set email html and plaintext strings***/
			$this->wppizza_order_email($orderid, $blogid);


			/*avoid some strict notices**/
			$phpVersion=phpversion();
			/*overwrite from and from name with static values if set**/
			$orderFromName=$this->orderClientName;
			$orderFromEmail=$this->orderClientEmail;
			if($options['order']['order_email_from_name']!=''){
				$orderFromName=wppizza_email_decode_entities($options['order']['order_email_from_name'],WPPIZZA_CHARSET);
			}
			if($options['order']['order_email_from']!=''){
				$orderFromEmail=$options['order']['order_email_from'];
			}

			/**if empty, use first half of email address**/
			if(trim($orderFromName)==''){
				$xplEmail=explode("@",$orderFromEmail);
				$orderFromName=''.$xplEmail[0].'';
			}

			/**who to send the order to. put it in a var so we can reuse it**/
			$this->orderShopEmail='';/**set default to admin email perhaps ? like so get_option('admin_email')**/
			if(count($options['order']['order_email_to'])>0){
				$this->orderShopEmail=implode(",",$options['order']['order_email_to']);/*trying to get rid of strict errors->passed by reference*/
			}

			/**send order using mail**/
			if($options['plugin_data']['mail_type']=='mail'){
				/************set headers*************/
				$header = '';
				if($orderFromEmail!=''){
					$header .= 'From: '.$orderFromName.' <'.$orderFromEmail.'>' . PHP_EOL.
					'Reply-To: '.$this->orderClientEmail.'' . PHP_EOL .
					'X-Mailer: PHP/' . $phpVersion;
					$header .= PHP_EOL;
					$header .= 'Cc: '.$this->orderClientEmail.'' . PHP_EOL;
				}else{
					$header .= 'From: -------- <>' . PHP_EOL.
					'Reply-To: '.$this->orderClientEmail.'' . PHP_EOL .
					'X-Mailer: PHP/' . $phpVersion;
					$header .= PHP_EOL;
				}
				if(count($options['order']['order_email_bcc'])>0){
					$bccs=implode(",",$options['order']['order_email_bcc']);/*trying to get rid of strict errors->passed by reference*/
					$header .= 'Bcc: '.$bccs.'' . PHP_EOL;
				}
				$header .= 'MIME-Version: 1.0' . PHP_EOL;
				$header .= 'Content-type: text/plain; charset='.WPPIZZA_CHARSET.'' . PHP_EOL;

				/************send mail**************/
				if( mail($this->orderShopEmail, $this->subjectPrefix.$this->subject.$this->subjectSuffix, $this->orderMessage['plaintext'], $header)) {
					$sendMail['status']=true;
				}else{
					$sendMail['status']=false;
					$sendMail['error']= error_get_last();
				}
				/**ident to identify mail function*/
				$sendMail['mailer']='mail';
			}
			/**send order using wp_mail**/
			if($options['plugin_data']['mail_type']=='wp_mail'){
				/************set headers*************/
				$wpMailHeaders=array();
				if($orderFromEmail!=''){
					$wpMailHeaders[] = 'From: '.$orderFromName.' <'.$orderFromEmail.'>';
					$wpMailHeaders[] = 'Cc: '.$this->orderClientEmail.'';
				}else{
					$wpMailHeaders[] = 'From: -------- <>';
				}

				if(count($options['order']['order_email_bcc'])>0){
					$bccs=implode(",",$options['order']['order_email_bcc']);/*trying to get rid of strict errors->passed by reference*/
					$wpMailHeaders[]= 'Bcc: '.$bccs.'';
				}
				$wpMailHeaders[] = 'Reply-To: '.$this->orderClientEmail.'';

				/***attachments***/
				$wpMailAttachments = array();
				/**any attachments set in options ?**/
				if(count($options['order']['order_email_attachments'])>0){
					foreach($options['order']['order_email_attachments'] as $attachment){
						if(is_file($attachment)){
							$wpMailAttachments[]=$attachment;
						}
					}
				}

				/************send mail**************/
				if(wp_mail($this->orderShopEmail, $this->subjectPrefix.$this->subject.$this->subjectSuffix, $this->orderMessage['plaintext'], $wpMailHeaders, $wpMailAttachments)) {
					$sendMail['status']=true;
				}else{
					$sendMail['status']=false;
					$sendMail['error']=error_get_last();
				}
				/**ident to identify mail function*/
				$sendMail['mailer']='wp_mail';
			}


			/**send order using phpmailer**/
			if($options['plugin_data']['mail_type']=='phpmailer'){
				require_once ABSPATH . WPINC . '/class-phpmailer.php';

				/************************************************************************
				*	for legacy reasons, in case templates and settings from
				*	earlier versions of this plugin, were copied to the theme folder
				*	we create some static vars here which
				*	also makes it a bit easier/obvious to edit the template
				***********************************************************************/
				$phpmailer = new PHPMailer(true);/*will return $sendMail array**/
				/**to be used in html template**/
				$nowdate=$this->orderTimestamp;
				$transactionId=$this->orderTransactionId;
				/**filter as required**/
				$transactionId = apply_filters('wppizza_email_filter_transaction_id', $transactionId, $orderid );

				$gatewayUsed=$this->orderGatewayUsed;
				/**get gateway frontend label instead of just COD or similar**/
				$gatewayLabel=$this->orderGatewayUsed;
				if(isset($this->pluginGateways[$this->orderGatewayUsed])){
					$gatewayLabel=!empty($this->pluginGateways[$this->orderGatewayUsed]->gatewayOptions['gateway_label']) ? $this->pluginGateways[$this->orderGatewayUsed]->gatewayOptions['gateway_label'] : $gatewayLabel;
				}
				/**********************/
				$currency=$this->orderCurrency;
				/****************************************************
					[set currency positions]
				****************************************************/
				$currency_left=$this->orderCurrency.' ';
				$currency_right='';
				if($options['layout']['currency_symbol_position']=='right'){/*right aligned*/
					$currency_left='';
					$currency_right=' '.$this->orderCurrency;
				}

				/***get localization vars**/
				$orderLabel=$this->orderLabels['html'];

				/**html template variables**/
				$customer_details_array=$this->orderMessage['html']['customer_details'];
				$customer_details_array = apply_filters('wppizza_filter_customer_details_html', $customer_details_array);

				$order_items=$this->orderMessage['html']['order_items'];
				$order_items = apply_filters('wppizza_filter_order_items_html', $order_items, 'additional_info');
				$order_summary=$this->orderMessage['html']['order_summary'];
				$order_summary['tax_applied']='items_only';
				if($options['order']['shipping_tax']){
					$order_summary['tax_applied']='items_and_shipping';
				}

				/*return $orderHtml*/
				$orderHtml='';
				/*for legacy reasons, someone might use an old template in their theme directory***/
				if (file_exists( $this->pluginTemplateDir . '/wppizza-order-html-email.php')){
					require_once($this->pluginTemplateDir.'/wppizza-order-html-email.php');
				}
				elseif(file_exists( $this->pluginTemplateDir . '/wppizza-order-email-html.php')){
					ob_start();
					require_once($this->pluginTemplateDir.'/wppizza-order-email-html.php');
					$orderHtml = ob_get_clean();
				}else{
					ob_start();
					require_once(WPPIZZA_PATH.'templates/wppizza-order-email-html.php');
					$orderHtml = ob_get_clean();
				}

				/**set phpmailer settings, setting variables for legacy gateways**/
				$email_subject=$this->subjectPrefix.$this->subject.$this->subjectSuffix;
				$email_recipients=$this->pluginOptions['order']['order_email_to'];
				$email_recipients_cc=!empty($this->orderClientEmail) ? array($this->orderClientEmail) : array();
				$email_recipients_bcc=$this->pluginOptions['order']['order_email_bcc'];

				$email_markup['html']=$orderHtml;
				$email_markup['plaintext']=$this->orderMessage['plaintext'];
				/***attachments***/
				$email_attachments = array();
				/**any attachments set in options ?**/
				if(count($options['order']['order_email_attachments'])>0){
					foreach($options['order']['order_email_attachments'] as $attachment){
						if(is_file($attachment)){
							$email_attachments[]=$attachment;
						}
					}
				}

				/*include settings*/
				$phpmailer_mail_results['status']=true;
				require_once(WPPIZZA_PATH.'inc/phpmailer.php');
			}

			/************return status (success or fail - with error messages if any) **************/
			return $phpmailer_mail_results;
		}
		/********************************************************************************************
		*
		*
		*	[ORDER BY COD/Ajax]
		*
		*
		********************************************************************************************/
		function wppizza_order_results($mailResults,$orderId){

			/**include wpml dont use require once  **/
			if(function_exists('icl_translate')){
				require(WPPIZZA_PATH .'inc/wpml.inc.php');
				//require(WPPIZZA_PATH .'inc/wpml.gateways.inc.php');
			}
			$output='';
			/***successfully sent***/
			if(isset($mailResults['status']) && empty($mailResults['error'])){
				$output.="<div class='mailSuccess'><h1>".$this->pluginOptions['localization']['thank_you']['lbl']."</h1>".nl2br($this->pluginOptions['localization']['thank_you_p']['lbl'])."</div>";
				$output.=$this->gateway_order_on_thankyou_process($orderId,$this->pluginOptions);
				$this->wppizza_unset_cart();
			}
			/***mail sending error or transaction already processes -> show error***/
			if(!($mailResults['status']) || !isset($mailResults['status'])  ){
				$output="<p class='wppizza-mail-error'>".$this->pluginOptions['localization']['thank_you_error']['lbl']."</p>";
				$output.="<p class='wppizza-mail-error'>".$mailResults['mailer'].": ".print_r($mailResults['error'],true)."</p>";
			}
		return $output;
		}


	/******************************************************************
	*
	*	[show order details on thank you page if set ]
	*	[$res = object or id]
	******************************************************************/
	function gateway_order_on_thankyou($res,$options=false){
		/**legacy**/
		if(!$options){
			$options=$this->pluginOptions;
		}
		print"".$this->gateway_order_on_thankyou_process($res,$options);
	}
	function gateway_order_on_thankyou_process($res,$options=false){
		$output='';
		/**legacy**/
		if(!$options){
			$options=$this->pluginOptions;
		}

		/**check if we are displaying the order on the thank you page**/
		if($options['gateways']['gateway_showorder_on_thankyou']){
			/*if we are only passing the id, try and get the order from the db first**/
			if(!is_object($res) && is_numeric($res)){
				global $wpdb;
				$res = $wpdb->get_row("SELECT id,transaction_id,order_ini,customer_ini,initiator FROM ".$wpdb->prefix . $this->pluginOrderTable." WHERE id='".$res."' ");
			}
			/**********************************************************
				[get relevant vars out of db
			**********************************************************/
			$thisCustomerDetails=maybe_unserialize($res->customer_ini);
			$thisOrderDetails=maybe_unserialize($res->order_ini);
			$thisOrderDetails = apply_filters('wppizza_filter_order_db_return', $thisOrderDetails);

			/**********************************************************
				[organize vars to make them easier to use in template]
			**********************************************************/
			$order['transaction_id']=$res->transaction_id;
			/**filter as required**/
			$order['transaction_id'] = apply_filters('wppizza_email_filter_transaction_id', $order['transaction_id'], $res->id, 'order on thank you' );
			$order['transaction_date_time']="".date_i18n(get_option('date_format'),$thisOrderDetails['time'])." ".date_i18n(get_option('time_format'),$thisOrderDetails['time'])."";

			$order['gatewayUsed']=$res->initiator;

			/**get gateway frontend label instead of just COD or similar**/
			$order['gatewayLabel']=$res->initiator;
				$wppizzaGateways=new WPPIZZA_GATEWAYS();
				$this->pluginGateways=$wppizzaGateways->wppizza_instanciate_gateways_frontend();
				/**
					get wpml vars of gatewway (label and info essentially)- should be done better one day using action hooks), but for the moment this will have to do.
				**/
				$wppizzaGateways->wppizza_wpml_localization_gateways();

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

			$order['currencyiso']=$thisOrderDetails['currencyiso'];

			/***********************************************
				[set localization vars]
			************************************************/
			$orderlbl=array();
			foreach($options['localization'] as $k=>$v){
				if($k=='taxes_included'){
					$orderlbl[$k]=sprintf(''.$v['lbl'].'',$options['order']['item_tax']);
				}else{
					$orderlbl[$k]=$v['lbl'];
				}
			}

			/***********************************************
				[customer details]
				[we should make this into a filter at some point
				in conjuction with wppizza_filter_customer_details_html
				used above]
			***********************************************/
			$customer=array();
			$customerlbl=array();
			$protectedKeys=array();
			foreach($options['order_form'] as $k=>$v){
				$protectedKeys[$v['key']]=$v;
			}
			$protectedKeys = apply_filters('wppizza_filter_order_form_fields', $protectedKeys);

			foreach($thisCustomerDetails as $k=>$v){
				/*****default input fields of this plugin*****/
				if(isset($protectedKeys[$k])){
					$customerlbl[$k]=$protectedKeys[$k]['lbl'];
					if($protectedKeys[$k]['type']!='textarea'){
						$customer[$k]=$v;
					}else{
						$customer[$k]='<div class="wppizza-order-textarea">'.nl2br($v).'</div>';
					}
				}
				/**********************************************************************************************************
				*
				*
				*	THIS BIT BELOW SHOULD NOW BE REDUNDANT NOW AS WE CAN UST USE THE FILTER ABOVE TO ADD TO THE - SO CALLED - PROTECTED KEYS
				*	let's leave it here for now, but it will probably be removed in future versions, as it has never been documnted publicly anyway
				*	as far as i know......
				*
				**********************************************************************************************************/
				if(!isset($protectedKeys[$k]) && is_array($v) && isset($v['label']) && isset($v['value'])){
					$customer[''.$v['label'].'']=$v['value'];
					$customerlbl[''.$v['label'].'']=$v['label'];
				}
			}
			/***********************************************
				[order items]
			***********************************************/
			$items=$thisOrderDetails['item'];
			/**filter old legacy additional info keys**/
			$items = apply_filters('wppizza_filter_order_additional_info', $items);
			/**filter new/current extend additional info keys**/
			$items = apply_filters('wppizza_filter_order_extend', $items);/**can be run by other plugins**/
			/**return items with html additional info**/
			$items = apply_filters('wppizza_filter_order_items_html', $items, 'additionalInfo');


			/***********************************************
				[order summary
			***********************************************/
			$summary['total_price_items']=$thisOrderDetails['total_price_items'];
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
				$summary['handling_charge']=wppizza_output_format_price($thisOrderDetails['handling_charge'],$options['layout']['hide_decimals']);
			}
			if(isset($thisOrderDetails['tips']) && $thisOrderDetails['tips']>0){
				$summary['tips']=wppizza_output_format_price($thisOrderDetails['tips'],$options['layout']['hide_decimals']);
			}


			/***allow some filtering of order and summary***/
			$order = apply_filters('wppizza_filter_order_show_order_page', $order, $thisOrderDetails);
			$summary = apply_filters('wppizza_filter_summary_show_order_page', $summary, $thisOrderDetails);

			/***********************************************
				[if template copied to theme directory use
				that one otherwise use default]
			***********************************************/
			ob_start();
			if (file_exists( $this->pluginTemplateDir . '/wppizza-show-order.php')){
				include($this->pluginTemplateDir . '/wppizza-show-order.php');
			}else{
				include(WPPIZZA_PATH.'templates/wppizza-show-order.php');
			}
			$output .= ob_get_clean();
		}
		return $output;
	}
}
?>