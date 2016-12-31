<?php
if (!class_exists( 'WPPizza' ) ) {return;}

	class WPPIZZA_SEND_EMAILS extends WPPIZZA {

		function __construct() {
			parent::__construct();

			/**extend**/
			$this->wppizza_order_emails_extend();

			/**order items db str**/
			$this->orderItemsPlaintext='';

			add_filter('wppizza_filter_plaintextemail_item_markup', array( $this, 'items_to_db'), 1000, 1);
		}

	/***************************************************************
	*
	*	get order by id and send emails - typically from c/c gateways
	*	after payment was completed/captured
	*
	*	returns array
	*	['status']->true/false
	*	['error']->''/msg
	*	['mailer']->mail function name
	***************************************************************/
	function send_email_gateway($orderId, $blogid){

			/********************************************************************

				send the email(s) and return mail results

			********************************************************************/
			$mailResults=$this->do_emails($orderId);

			/********************************************************************
			*
			*	depending on $mailResults['status'],
			*	update db. if mail errors, set those too
			*
			********************************************************************/
			$data['order_date']				=	$this->currentTimezoneDate;
			$data['customer_details']		=	$this->customerDetails;
			$data['order_details']			=	$this->orderDetails;
			$data['payment_status']			=	'COMPLETED';
			$data['transaction_errors']		=	'';
			$data['mail_sent']				=	!empty($mailResults['status']) ? 'Y' : 'N';
			$data['mail_error']				=	!empty($mailResults['error']) ? esc_sql(maybe_serialize($mailResults['error'])) : '';

			$where['id']					=	$orderId;
			$format							=	'%s';
			$where_format					=	'%d';
			/*update db will returns  $update_db === false on error else integer (>=0 !!!) of rows updated*/
			$update_db=$this->update_db($data, $where, $format, $where_format, $blogid);

			/*
				update user profile if set 
			*/
			$this->update_userprofile($orderId, false, false);


			/**do additional stuff when order has been executed*/
			do_action('wppizza_on_order_executed', $orderId , $this->pluginOrderTable);

		return $data;
	}
	/***************************************************************
	*
	*	get order by hash and send emails - typically from ajax call
	*
	*	[provided we have a valid order id AND its set
	*	to INITIALIZE send the emails and update db]
	*	returns array
	*	['status']->true/false
	*	['error']->''/msg
	*	['mailer']->mail function name
	***************************************************************/
	function send_email_ajax($param){

		/**get the orderdetails by hash*/
		$order=$this->get_order_details($param['wppizza_hash']);

		/**order exists and is set as initialized**/
		if($order && $order['order_status']=='INITIALIZED' ){


			/*sanitize the post input variables*/
			$order_update['post_vars'] = apply_filters('wppizza_filter_sanitize_post_vars', $param);


			/**add wpml language code so we can send emails in the right language**/
			if(defined('ICL_LANGUAGE_CODE')){
				$order_update['post_vars']['wppizza_wpml_lang']=ICL_LANGUAGE_CODE;
			}

			/**let's make up some transactionid**/
			$now=current_time('timestamp');
			$order_update['gateway_used']=strtoupper($order_update['post_vars']['wppizza-gateway']);
			$order_update['transaction_id']=$order_update['gateway_used'] . $now . $order['order_id'].'';


			/**update the db only with order date user post vars, gateway used and a transaction id first **/
			$data['order_date']		=	$this->currentTimezoneDate;
			$data['transaction_id']	=	$order_update['transaction_id'];
			$data['initiator']		=	$order_update['gateway_used'];
			$data['customer_ini']	=	maybe_serialize($order_update['post_vars']);


			$where['id']			=	$order['order_id'];
			$format					=	'%s';
			$where_format			=	'%d';
			/*update db will returns  $update_db === false on error else integer (>=0 !!!) of rows updated*/
			$update_db=$this->update_db($data, $where, $format, $where_format, false, 'customer_ini');


			/********************************************************************

				send the email(s) and return mail results

			********************************************************************/
			$mailResults=$this->do_emails($order['order_id']);

			/********************************************************************
			*
			*	depending on $mailResults['status'],
			*	update db as COMPLETED and it's relevant db values
			*	or FAILED
			*
			********************************************************************/
			/**update db again with mail results **/
			if($mailResults['status']){
				$mailSent='Y';
				$mailError='';
				$paymentStatus='COMPLETED';
				$transactionDetails=__('SUCCESS','wppizza-locale');
			}else{
				$mailSent='N';
				$mailError=esc_sql(maybe_serialize($mailResults['error']));
				$paymentStatus='FAILED';//should be FAILED COMPLETED only for testing
				$transactionDetails=__('FAILED: Sending of Mail Failed','wppizza-locale');
			}

			$data['transaction_details']	=	$transactionDetails;
			$data['payment_status']			=	$paymentStatus;
			$data['customer_details']		=	$this->customerDetails;
			$data['order_details']			=	$this->orderDetails;
			$data['mail_sent']				=	$mailSent;
			$data['mail_error']				=	$mailError;

			$where['id']					=	$order['order_id'];
			$format							=	'%s';
			$where_format					=	'%d';
			/*update db will returns  $update_db === false on error else integer (>=0 !!!) of rows updated*/
			$update_db=$this->update_db($data, $where, $format, $where_format, $this->orderDetails);


			/*
				update user profile if set 
			*/
			$this->update_userprofile(false, $order, $order_update['post_vars']);
			


			/**do additional stuff when order has been executed*/
			do_action('wppizza_on_order_executed', $order['order_id'] , $this->pluginOrderTable);
		}

		/**order exists but was already dealt with**/
		if($order && $order['order_status']!='INITIALIZED' ){
			$mailResults['error']=__('This order has already been processed','wppizza-locale');
		}

		/**order does not exist**/
		if(!$order){
			$mailResults['error']=__('Sorry, we could not find this order.','wppizza-locale');
			$mailResults['mailer']=__('Error','wppizza-locale'); /**just so we dont have an abandoned colon**/
		}


		/********************************************************************
		*
		*	depending on $mailResults['status'],
		*	print "thankyou" or justs display error
		*
		*	- should be looked at at some point as this could be better,
		*	especially regarding including wpml. for now, this will have to do
		*
		********************************************************************/
		/**include wpml dont use require once **/
		if(function_exists('icl_translate')){
			require(WPPIZZA_PATH .'inc/wpml.inc.php');
		//	//require(WPPIZZA_PATH .'inc/wpml.gateways.inc.php');
		}
		$output='';
		/***successfully sent***/
		if(isset($mailResults['status']) && empty($mailResults['error'])){
			$output.="<div class='mailSuccess'><h1>".$this->pluginOptions['localization']['thank_you']['lbl']."</h1>".($this->pluginOptions['localization']['thank_you_p']['lbl'])."</div>";
			//$output.=$this->gateway_order_on_thankyou_process($orderId,$this->pluginOptions);
			$output.=$this->do_thankyou($order['order_id']);
			$this->wppizza_unset_cart();

		}
		/***mail sending error or transaction already processed -> show error***/
		if(!($mailResults['status']) || !isset($mailResults['status'])  ){
			$output="<p class='wppizza-mail-error'>".$this->pluginOptions['localization']['thank_you_error']['lbl']."</p>";
			$output.="<p class='wppizza-mail-error'>".$mailResults['mailer'].": ".print_r($mailResults['error'],true)."</p>";
		}

		echo $output;
	}

	/**********************************************************
	*
	*	thank you page output
	*
	**********************************************************/
	function do_thankyou($orderId){
		/*ini string*/
		$markup='';
		/*******************************************
			if not enabled, just return empty string
		********************************************/
		if(empty($this->pluginOptions['gateways']['gateway_showorder_on_thankyou'])){
			return $markup;
		}

		/***include and ini action class***/
		if (!class_exists( 'WPPIZZA_ACTIONS' ) ) {
			require(WPPIZZA_PATH.'classes/wppizza.actions.inc.php');
		}
		$WPPIZZA_ACTIONS=new WPPIZZA_ACTIONS();
		/**filter and sort selected items by their categoryies**/
		add_filter('wppizza_show_order_filter_items', array( $WPPIZZA_ACTIONS, 'wppizza_filter_items_by_category'),10,2);
		add_action('wppizza_show_order_item', array( $WPPIZZA_ACTIONS, 'wppizza_items_show_order_print_category'));

		/********************************************
			get order details again
		*********************************************/
		global $blog_id ;
		if (!class_exists( 'WPPIZZA_ORDER_DETAILS' ) ) {
			require(WPPIZZA_PATH.'classes/wppizza.order.details.inc.php');
		}
		$orderDetails=new WPPIZZA_ORDER_DETAILS();
		$orderDetails->setOrderId($orderId);
		$orderDetails->setBlogId($blog_id);
		$orderDetails->setTranslate(true);
		$order_details=$orderDetails->getOrder();/**all order vars**/

		/*************************************
			simplify vars to us in templates
			som currently unused, for future use
			when using dynamic thank you template
		*************************************/
		$siteDetails=$order_details['site'];/*currently unused below*/
		$multiSite=$order_details['multisite'];/*currently unused below*/
		$orderDetails=$order_details['ordervars'];
		$txt=$order_details['localization'];
		$customerDetails=$order_details['customer']['post'];//omit ['others'] here
		$cartitems=$order_details['items'];
		$orderSummary=$order_details['summary'];



		/************************************************************************
		*
		*	[for legacy reasons. map $order vars to vars used in templates]
		*
		************************************************************************/
		$options=$this->pluginOptions;
		/***********************************
			[map vars -> html]
		***********************************/
		$order['transaction_date_time']		=	$orderDetails['order_date']['value'];
		$order['gatewayLabel']	=	$orderDetails['payment_type']['value'];
		/*empty currency as now part of returned price label already*/
		$order['currency_left']	=	'';
		$order['currency_right']	=	'';
		/*transaction id*/
		$order['transaction_id']	=	$orderDetails['transaction_id']['value'];
		/**localization vars*/
		$orderlbl		=	$txt;
		/**customer details **/
		$customer=array();/*customer vars**/
		foreach($customerDetails as $cKey=>$cVal){
			$customerlbl[$cKey]=$cVal['label'];
			$customer[$cKey]=$cVal['value'];
		}

		/**order items**/
		/**
			wppizza-show-order.php should be changed at some point for consistancy,
			for the moment, map addinfo to additionalInfo and
			all other variables as required
		**/
		$items=array();
		foreach($cartitems as $cKey=>$cItem){
			$items[$cKey]=$cItem;
			if(!empty($cItem['addinfo'])){
				$items[$cKey]['additionalInfo']=$cItem['addinfo'];
				unset($items[$cKey]['addinfo']);
			}
		}


		/**summary details **/
		/**
			wppizza-show-order.php should be changed at some point for consistancy,
			for the moment, map variables as required as other plugins might rely on them
		**/
		$summary=array();//$orderSummary;

		/*price items*/
		$summary['total_price_items']=$orderSummary['cartitems']['value'];
		/*discount value*/
		$summary['discount']=!empty($orderSummary['discount']['value']) ? $orderSummary['discount']['value'] : '' ;
		/* self pickup */
		$summary['selfPickup']=!empty($orderSummary['self_pickup']['type']) ? $orderSummary['self_pickup']['type'] : 0 ;
		/*tax*/
		$summary['item_tax']=!empty($orderSummary['item_tax']['value']) ? $orderSummary['item_tax']['value'] : 0 ;
		/*taxes included*/
		$summary['taxes_included']=!empty($order_details['summary']['taxes_included']['value']) ? $order_details['summary']['taxes_included']['value'] : '' ;
		/*what kind of tax*/
		$summary['tax_applied']='items_only';
		if($options['order']['shipping_tax']){
			$summary['tax_applied']='items_and_shipping';
		}
		if($options['order']['taxes_included']){
			$summary['tax_applied']='taxes_included';
			/**get label*/
			$orderlbl['taxes_included']=$orderSummary['taxes_included']['label'];
		}
		/*delivery charges*/
		if($options['order']['delivery_selected']!='no_delivery'){/*delivery disabled*/
			$summary['delivery_charges']=!empty($orderSummary['delivery']['value']) ? $orderSummary['delivery']['value'] : '';
		}
		/*tips*/
		$summary['tips']=!empty($orderSummary['tips']['value']) ? $orderSummary['tips']['value'] : '';

		/**handling charges***/
		$summary['handling_charge']=!empty($orderSummary['handling_charge']['value']) ? $orderSummary['handling_charge']['value'] : 0 ;

		/*total*/
		$summary['total']=$orderSummary['total']['value'];

		/**add all other non set ones for other plugins to hook into again after they added them to the order_ini*/
		foreach($orderSummary as $sKey=>$sVal){
			if(empty($summary[$sKey])){
				$summary[$sKey]=$sVal;
				/**for legacy reasons, add value as price field too*/
				$summary[$sKey]['price']=$sVal['value'];
			}
		}

		/***********************************************
			[if template copied to theme directory use
			that one otherwise use default]
		***********************************************/
		ob_start();

		/*old legacy template**/
		if (file_exists( $this->pluginTemplateDir . '/wppizza-show-order.php')){
			include($this->pluginTemplateDir . '/wppizza-show-order.php');
		}else{
			include(WPPIZZA_PATH.'templates/wppizza-show-order.php');
		}

		/*not yet in use*/
//		if (file_exists( $this->pluginTemplateDir . '/wppizza-order-summary.php')){
//			include($this->pluginTemplateDir . '/wppizza-order-summary.php');
//		}else{
//			include(WPPIZZA_PATH.'templates/wppizza-order-summary.php');
//		}

		$markup .= ob_get_clean();

		return $markup;
	}


	function do_emails($orderId){


		/**ini array of emails we need to send**/
		$email_to_send=array();

		/************************************
		*
		*	get all necessary template id's
		*
		************************************/
		/*ini array*/
		$template_ids=array();
		$tpl_recipients=array();
		/**check if we need html email part anywhere. ini as false*/
		$html_required=false;
		/*defaults - shop and customers*/
		foreach($this->pluginOptions['templates_apply']['emails']['recipients_default'] as $recipient=>$tplId){

			/*default template php file*/
			if($tplId==-1){
				$html=($this->pluginOptions['plugin_data']['mail_type']=='phpmailer') ? true : false;
				$omit_attachments=false;
			}
			/*using template builder*/
			if($tplId!=-1){
				$html=($this->pluginOptions['templates']['emails'][$tplId]['mail_type']=='phpmailer') ? true : false;
				$omit_attachments=!empty($this->pluginOptions['templates']['emails'][$tplId]['omit_attachments']) ? true : false;
			}
			/*needing html part ?*/
			if($html){
				$html_required=true;
			}
			/*set key to id to save us a array_unique call*/
			$template_ids[$tplId]= array('template_id'=>$tplId, 'html'=>$html, 'omit_attachments'=>$omit_attachments);

			/**set array of recipients template will be sent to **/
			if($recipient=='email_shop'){$key='email_shop';}
			if($recipient=='email_customer'){$key='email_customer';}
			$tpl_recipients[$tplId][$key]=true;

		}
		/*additional recipients*/
		if(!empty($this->pluginOptions['templates_apply']['emails']['recipients_additional'])){
		foreach($this->pluginOptions['templates_apply']['emails']['recipients_additional'] as $tplId=>$recipients){
			/*
				previous versions had a bug that might have saved (or not deleted more accurately) recipients_additional for templates
				with id >0 (i.e non defaults) that were deleted, and/or did not exist anymore,
				so lets double check (which is not such a bad idea anyway)
			*/
			if($tplId==-1 || !empty($this->pluginOptions['templates']['emails'][$tplId])){

				/*default template php file*/
				if($tplId==-1){
					$html=($this->pluginOptions['plugin_data']['mail_type']=='phpmailer') ? true : false;
					$omit_attachments=false;
				}
				/*using template builder*/
				if($tplId!=-1){
					$html=($this->pluginOptions['templates']['emails'][$tplId]['mail_type']=='phpmailer') ? true : false;
					$omit_attachments=!empty($this->pluginOptions['templates']['emails'][$tplId]['omit_attachments']) ? true : false;
				}
				/*needing html part ?*/
				if($html){
					$html_required=true;
				}
				/*set key to id to save us a array_unique call*/
				$template_ids[$tplId]= array('template_id'=>$tplId, 'html'=>$html, 'omit_attachments'=>$omit_attachments);

				/**set array of recipients template will be sent to **/
				$tpl_recipients[$tplId]['recipients_additional']=$recipients;
			}}}
		/*for sanities/readabilities sake - entirely optional*/
		ksort($template_ids);


		/************************************
		*
		*	get order details
		*
		************************************/
		global $blog_id ;
		if (!class_exists( 'WPPIZZA_ORDER_DETAILS' ) ) {
			require(WPPIZZA_PATH.'classes/wppizza.order.details.inc.php');
		}
		$orderDetails=new WPPIZZA_ORDER_DETAILS();
		$orderDetails->setOrderId($orderId);
		$orderDetails->setBlogId($blog_id);
		/*get order details for plaintext - always necessary*/
		$orderDetails->setPlaintext(true);
		/*make sure emails are wpml'd*/
		$orderDetails->setTranslate(true);
		$order_details=$orderDetails->getOrder();/**all order vars => plaintext**/

		/***********************************************************
			[set name and email of the the person that is ordering]
		***********************************************************/
		/*get order details for html too if required*/
		$order_details_html=false;/*ini false*/
		if($html_required){
			$orderDetails->setPlaintext(false);
			$order_details_html=$orderDetails->getOrder();/**all order vars =>html**/
		}

		/***include and ini template class***/
		if (!class_exists( 'WPPIZZA_TEMPLATES' ) ) {
			require(WPPIZZA_PATH.'classes/wppizza.templates.inc.php');
		}
		$WPPIZZA_TEMPLATES=new WPPIZZA_TEMPLATES();

		/**add filters for consistant spacing**/
		add_filter('wppizza_filter_template_plaintext_transaction_details',array($WPPIZZA_TEMPLATES,'wppizza_template_plaintext_linelength'));
		add_filter('wppizza_filter_template_plaintext_customer_detail',array($WPPIZZA_TEMPLATES,'wppizza_template_plaintext_linelength'));
		add_filter('wppizza_filter_template_plaintext_cart_item_header',array($WPPIZZA_TEMPLATES,'wppizza_template_plaintext_linelength'),10,3);
		add_filter('wppizza_filter_template_plaintext_cart_item',array($WPPIZZA_TEMPLATES,'wppizza_template_plaintext_linelength'));
		add_filter('wppizza_filter_template_plaintext_cart_item_addinfo',array($WPPIZZA_TEMPLATES,'wppizza_template_plaintext_linelength'));
		add_filter('wppizza_filter_template_plaintext_summary_detail',array($WPPIZZA_TEMPLATES,'wppizza_template_plaintext_linelength'));
		add_filter('wppizza_filter_template_plaintext_padstring',array($WPPIZZA_TEMPLATES,'wppizza_template_plaintext_linelength'));

		/************************************************************************
		*
		*	A)
		*
		*	get all template markups associated with id
		*
		*
		************************************************************************/
		foreach($template_ids as $template_id=>$template_parameters){

			/*get markup*/
			$email_markup[$template_id]=$this->get_email_markup($template_id, $orderId, $order_details, $order_details_html);

			/*any attachmnts ?*/
			$no_attachments[$template_id]=$template_parameters['omit_attachments'];
		}

		/************************************************************************
		*
		*	B)
		*
		*	send emails to shop/customer/additional recipients, returning mail status
		*
		*
		************************************************************************/
		/*ini variables as failed*/
		$send_email_result['status']=false;
		$send_email_result['error']='undefined';
		$send_email_result['mailer']='undefined';
		$send_email_result['customer_details']='';



		foreach($tpl_recipients as $template_id=>$recipients){
			$mail_result=$this->send_email_execute($recipients, $email_markup[$template_id], $template_id, $order_details['customer'], $no_attachments[$template_id]);
			/**only return results for main email sent to shop**/
			if(!empty($recipients['email_shop'])){
				$send_email_result=$mail_result;
			}
		}

		return $send_email_result;
	}


/*********************************************************************************************************************
*
*
*	send email and return mail results
*
*	@customer_in_cc true to cc, false to send separate email
*
*********************************************************************************************************************/
	function send_email_execute($recipients, $email_markup, $template_id, $customer_data, $omit_attachments=false){
		//global $phpmailer;
		static $static=0; $static++;
		/*get plugin options*/
		$options=$this->pluginOptions;
		/**allow filtering***/
		$options = apply_filters('wppizza_filter_order_email_options', $options , $recipients);

		/**********************************************
		*
		*
		*	set some parameters that apply to all
		*
		*
		***********************************************/
			/*get customer email if we can/exists/has been submitted and set as default from**/
			$order_customer_email=!empty($customer_data['post']['cemail']['value']) ? $customer_data['post']['cemail']['value'] : '';
			$orderFromEmail=$order_customer_email;

			/*get customer name if we can/exists/has been submitted**/
			$order_customer_name =!empty($customer_data['post']['cname']['value']) ? wppizza_email_decode_entities($customer_data['post']['cname']['value'],WPPIZZA_CHARSET) : '';
			$orderFromName=$order_customer_name;

			/************************************************************
				overwrite from and from name with static values if set.
				at least email must be set for this to kick in
				otherwise, use  customer email/name id we can
			************************************************************/
			if($options['order']['order_email_from']!=''){
				/*email was set, use instead*/
				$orderFromEmail=$options['order']['order_email_from'];

				if($options['order']['order_email_from_name']!=''){/*name was set, use instead*/
					$orderFromName=wppizza_email_decode_entities($options['order']['order_email_from_name'],WPPIZZA_CHARSET);
				}
				/**if we've set a static from email, but omitted a static from name, set the static email address as name too***/
				if($options['order']['order_email_from']!='' && $options['order']['order_email_from_name']==''){
					$orderFromName=$options['order']['order_email_from'];
				}
			}

//			/**if from name still empty here, use first half of cutomer email address as name**/
//			if(empty($orderFromName)){
//				if(empty($order_customer_name)){
//					$xplEmail=explode("@",$order_customer_email);
//					$orderFromName=!empty($xplEmail[0]) ? ''.$xplEmail[0].'' : '';
//				}else{
//					$orderFromName=$order_customer_name;
//				}
//			}
			/**if email address is still empty, just give up and do some ---- with empty email**/
			if(empty($orderFromEmail) || $orderFromEmail==''){
				$orderFromName='--------';
				$orderFromEmail='';
			}


			/*email subjct*/
			$email_subject=$this->wppizza_create_email_subject($template_id, $customer_data['post'] );
			//$email_subject=trim(implode('',$email_subject));
			//$email_subject=$this->subjectPrefix.$this->subject.$this->subjectSuffix;

			/************************************************************
				email recipient(s)
				- if to shop, customer in cc and additional (if any) in cc too -> recipient:shop, cc:customer/additional
				- if customer (w/o shop), cc to additional -> recipient:customer, cc:additional
				- if additional (w/o shop and customer) -> recipient: additional , cc: none
			************************************************************/
			if(!empty($recipients['email_shop'])){/*email to shop*/
				/*main recipient -> to shop*/
				$email_recipients=$options['order']['order_email_to'];/*array*/

				/*cc customer*/
				if(!empty($recipients['email_customer']) && !empty($order_customer_email)){
					$email_recipients_cc[]=$order_customer_email;
					$email_reply_to=$order_customer_email;
				}
				/*cc additional*/
				if(!empty($recipients['recipients_additional'])){
					foreach($recipients['recipients_additional'] as $add_cc){
						$email_recipients_cc[]=$add_cc;
					}
				}
			}
			if(empty($recipients['email_shop']) && !empty($recipients['email_customer']) && !empty($order_customer_email)){/*email to customer*/
				/*main recipient -> to customer*/
				$email_recipients=array($order_customer_email);/*cast to array*/
				/*cc additional*/
				if(!empty($recipients['recipients_additional'])){
					foreach($recipients['recipients_additional'] as $add_cc){
						$email_recipients_cc[]=$add_cc;
					}
				}
			}

			if(empty($recipients['email_shop']) && empty($recipients['email_customer']) && !empty($recipients['recipients_additional']) ){/*email to additional only*/
				/*main recipient -> to additional*/
				if(!empty($recipients['recipients_additional'])){
					foreach($recipients['recipients_additional'] as $to){
						$email_recipients[]=$to;
					}
				}
			}

			/*shop bcc array. only if email send to shop*/
			if(!empty($recipients['email_shop'])){
				$email_recipients_bcc=!empty($options['order']['order_email_bcc']) ? $options['order']['order_email_bcc'] : array() ;
			}

			/*attachments*/
			if(!$omit_attachments){
			if(!empty($options['order']['order_email_attachments'])){
				foreach($options['order']['order_email_attachments'] as $attachment){
					if(!empty($attachment) && is_file($attachment)){
						$email_attachments[]=$attachment;
					}
				}
			}}

			/**send as html ? */
			$asHtml=!empty($email_markup['html']) ? true : false;


		/**************************************************************
		*
		*	DEVELOPMENT ONLY : DEBUG/VIEW email output:
		*
		*	for html/plaintext email checking in browser without sending any email.
		* 	will only work on chrome and safari ... apparently
		*	must be set to logging (with log path defined)
		*	when using/testing IPN's from gateways
		*
		****************************************************************/

			/*set to true, to view the output of all emails that are being send*/
			/* to be displayed on the thank you page*/
			$viewEmailOutput=false;
			if(defined('WPPIZZA_VIEW_EMAIL_OUTPUT')){/*this should NEVER be defined in production sites*/
				$viewEmailOutput=true;
			}
			/**add actual markup to output*/
			$viewEmailMarkup=true;
			/*set path to file to log, or comment out to display, ALWAYS log to file if using IPN's*/
			//$emailsToLog='/home/emails-sent.log';

			if($viewEmailOutput){
				$display='';
				$display.='<br />=================== emails sent [No '.$static.']:  template_id '.$template_id.'=============================<br />'.PHP_EOL;
				$display.='subject line: '.$email_subject.'<br />'.PHP_EOL;
				$display.='recipients: '.print_r(implode(',',$email_recipients),true).'<br />'.PHP_EOL;

				if(!empty($email_recipients_cc) && count($email_recipients_cc)>0){
					$display.='ccs: '.print_r(implode(',',$email_recipients_cc),true).'<br />'.PHP_EOL;
				}
				if(!empty($email_recipients_bcc) && count($email_recipients_bcc)>0){
					$display.='bcc: '.print_r(implode(',',$email_recipients_bcc),true).'<br />'.PHP_EOL;
				}
				if(!empty($email_attachments)){
					$display.='attachments: '.print_r(implode(',',$email_attachments),true).'<br />'.PHP_EOL;
				}
				/*if not logging output markup in iframe*/
				if(!empty($viewEmailMarkup)){
					if(empty($emailsToLog)){
						if(!empty($email_markup['html'])){
							$display.='<iframe srcdoc="'.str_replace('"','\'',$email_markup['html']).'" src="" width="600" height="600"></iframe>';//html
						}
						$display.='<iframe srcdoc="<textarea style=\'width:100%;height:800px\'>'.str_replace(" "," ",(str_replace('"','\'',$email_markup['plaintext']))).'</textarea>" src="" width="600" height="600"></iframe>';//plaintext
					}else{
						if(!empty($email_markup['html'])){
							$display.=$email_markup['html'].PHP_EOL;
						}
						$display.=$email_markup['plaintext'].PHP_EOL;
					}
				}
				/**display or log*/
				if(empty($emailsToLog)){
					echo $display;
				}else{
					if($static==1){
						file_put_contents($emailsToLog,print_r($display,true).PHP_EOL);/*clear log for first*/
					}else{
						file_put_contents($emailsToLog,print_r($display,true).PHP_EOL,FILE_APPEND);
					}
				}
			}

		/**********************************
		*
		*	send plaintext email
		*	using wp_mail
		*
		**********************************/
			if(!$asHtml){
				/************************************************
					send with smtp if enabled - i dont think this actually does anything
					but lets leave it here
				*************************************************/
				if(!empty($this->pluginOptions['plugin_data']['smtp_enable'])){
					/* below doesnt seem to want to work **/
					add_action('phpmailer_init', array($this, 'wppizza_smtp_send'));
				}				
				/************
					set headers
				*************/
				/*ini*/
				$wp_mail_headers=array();

				/*from*/
				$wp_mail_headers[] = 'From: '.$orderFromName.' <'.$orderFromEmail.'>';

				/*to*/
				$wp_mail_recipients=implode(',',$email_recipients);

				/*reply to*/
				if(!empty($email_reply_to)){
					$wp_mail_headers[]= 'Reply-To: '.$email_reply_to.'';
				}

				/*cc, if set*/
				if(!empty($email_recipients_cc) && count($email_recipients_cc)>0){
					$ccs=implode(",",$email_recipients_cc);
					$wp_mail_headers[]= 'Cc: '.$ccs.'';
				}

				/*bcc, if set*/
				if(!empty($email_recipients_bcc) && count($email_recipients_bcc)>0){
					$bccs=implode(",",$email_recipients_bcc);
					$wp_mail_headers[]= 'Bcc: '.$bccs.'';
				}

				/***attachments if any***/
				$wp_mail_attachments = array();
				if(!empty($email_attachments)){
					foreach($email_attachments as $attachment){
						$wp_mail_attachments[]=$attachment;
					}
				}

				/************************************************
					send plaintext mail
				*************************************************/
				/*disable actual sending if disable_emails set*/
				if(!empty($options['tools']['disable_emails'])){
					$send_email_results['status']=true;
				}else{
					if(wp_mail($wp_mail_recipients, $email_subject, $email_markup['plaintext'], $wp_mail_headers, $wp_mail_attachments)) {
						$send_email_results['status']=true;
					}else{
						$send_email_results['status']=false;
						$send_email_results['error']=$GLOBALS['phpmailer']->ErrorInfo;
						$error_get_last=error_get_last();
						if(!empty($error_get_last)){
						$send_email_results['error'].=' | '.print_r($error_get_last,true);/**sometimes there's somthing in tha variable too*/
						}

					}
				}
				/**add ident to identify we are using wp_mail function*/
				$send_email_results['mailer']='wp_mail';

			}
		/**********************************
		*
		*	send html email
		*	using phpmailer
		*
		**********************************/
			if($asHtml){
				/*include required phpmailer class*/
				require_once ABSPATH . WPINC . '/class-phpmailer.php';
				/*initialize phpmailer class**/
				$phpmailer = new PHPMailer(true);/*will return  array**/

				/************************************************
					send with smtp if enabled
				*************************************************/
				if(!empty($this->pluginOptions['plugin_data']['smtp_enable'])){
					$this->wppizza_smtp_send($phpmailer);
					/* below doesnt seem to want to work **/
					//add_action('phpmailer_init', array($this, 'wppizza_smtp_send'));
				}				
				/*************************************************
					ini status as true, will be overwritten/false
					when exceptions orruc
				*************************************************/
				$phpmailer_mail_results['status']=true;
				/**set phpmailer settings - moved to /inc/ with addded action hooks now**/
				require(WPPIZZA_PATH.'inc/phpmailer.php');

				/*results are returned from class in $phpmailer_mail_results array **/
				$send_email_results=$phpmailer_mail_results;
				/**add ident to identify we are using phpmailer function*/
				$send_email_results['mailer']='phpmailer';
			}
		return $send_email_results;
	}
/*********************************************************************************************************************
*
*
*	get plaintext and html(if required) markup
*
*
*********************************************************************************************************************/
	function get_email_markup($template_id, $orderId, $order_details,  $order_details_html=false, $type='emails'){
		/**options vars**/
		$pOptions=$this->pluginOptions;

		/**check if we are sending plaintext template to webmail (currently only checking for gmail)*/
		$plaintext_to_webmail=$this->plaintext_to_webmail($template_id, $order_details, $order_details_html, $pOptions);


		/*ini markup array*/
		$email_markup=array();

		/***include and ini template class***/
		if (!class_exists( 'WPPIZZA_TEMPLATES' ) ) {
			require(WPPIZZA_PATH.'classes/wppizza.templates.inc.php');
		}
		$WPPIZZA_TEMPLATES=new WPPIZZA_TEMPLATES();
		/************************************************************************
		*
		*	[add filters]
		*
		************************************************************************/

		/**add filters to be able to space details consistantly in plaintext templates***/
		add_filter('wppizza_filter_customer_details_to_plaintext', array( $WPPIZZA_TEMPLATES, 'wppizza_template_plaintext_linelength'));/*customer details to spaced plaintext str*/
		add_filter('wppizza_filter_summary_details_to_plaintext', array( $WPPIZZA_TEMPLATES, 'wppizza_template_plaintext_linelength'));/*sumary details to spaced plaintext str*/

		/*****************************************************************************
		*
		*
		*	old default - legacy - using php templates in templates directory
		*	someone might use an old template in their theme directory
		*
		*****************************************************************************/
		if($template_id==-1){

			/*filters for legacy php templates**/
			/***include and ini action class***/
			if (!class_exists( 'WPPIZZA_ACTIONS' ) ) {
				require(WPPIZZA_PATH.'classes/wppizza.actions.inc.php');
			}
			$WPPIZZA_ACTIONS=new WPPIZZA_ACTIONS();
			/*plaintext filters*/
			add_filter('wppizza_emailplaintext_filter_items', array( $WPPIZZA_ACTIONS, 'wppizza_filter_items_by_category'),10,2);
			add_filter('wppizza_emailplaintext_item', array( $WPPIZZA_ACTIONS, 'wppizza_items_emailplaintext_print_category'),10,2);
			add_filter('wppizza_emailplaintext_single_value_pad', array( $WPPIZZA_ACTIONS, 'wppizza_plaintext_padstring'));


				/**legacy when using plaintext template, to be removed on update of plaintext template*/
				add_filter('wppizza_emailplaintext_item', array( $this, 'items_to_db_print_category'),9,2);

			/*html filters*/
			add_filter('wppizza_emailhtml_filter_items', array( $WPPIZZA_ACTIONS, 'wppizza_filter_items_by_category'),10,2);
			add_action('wppizza_emailhtml_item', array( $WPPIZZA_ACTIONS, 'wppizza_items_emailhtml_print_category'),10,2);


			/************************************************************************
			*
			*	[for legacy reasons. map $order vars to vars used in templates]
			*
			************************************************************************/

			/***********************************
				[map vars -> plaintext and html]
			***********************************/
			$nowdate		=	$order_details['ordervars']['order_date']['value'];
			$gatewayLabel	=	$order_details['ordervars']['payment_type']['value'];
			/*empty currency as now part of returned price label already*/
			$currency_left	=	'';
			$currency_right	=	'';
			/*transaction id*/
			$transactionId	=	$order_details['ordervars']['transaction_id']['value'];
			/**localization vars*/
			$orderLabel		=	$order_details['localization'];
			/**customer details **/
			$customerDetails=$order_details['customer']['post'];/*customer post vars**/
			/**order items**/
			$orderItems=$order_details['items'];
			/**summary details **/
			$summaryDetails=$order_details['summary'];/*summary vars**/


			/*****************************************************************
				[map vars -> plaintext only, always map as it's always needed]
			*****************************************************************/
			/**customer details as plaintext string: to use in plaintext emails **/
			$emailPlaintext['customer_details']='';/*ini string - */ /*Note: as long as we are using / allowing for templates to be copied to themes this variable should not be changes so we do not break things*/
			foreach($customerDetails as $cKey=>$cDetails){
				/*apply filter for line length*/
				$cDetails= apply_filters('wppizza_filter_customer_details_to_plaintext', $cDetails);
				/*add to output*/
				$emailPlaintext['customer_details'].=$cDetails['label'].$cDetails['value'].PHP_EOL;
			}

			/**items ordered**/
			$emailPlaintext['order']='';
			$emailPlaintext['items']=array();/*ini array*/
			foreach($orderItems as $oKey=>$items){
				/*add to output*/
				$emailPlaintext['items'][$oKey]=$items;
				//$emailPlaintext['order'].=print_r($items,true);
				/**map addinfo*/
				foreach($items as $iKey=>$iVal){
					if($iKey=='addinfo'){
						$emailPlaintext['items'][$oKey]['additional_info']=wppizza_email_decode_entities($iVal,WPPIZZA_CHARSET);
					}
				}
			}

			/**summary details as plaintext string: to use in plaintext emails **/
			$emailPlaintext['order_summary']='';/*ini string*/
			foreach($summaryDetails as $sKey=>$sDetails){
				/*apply filter for line length*/
				$sDetails= apply_filters('wppizza_filter_customer_details_to_plaintext', $sDetails);
				/*add to output*/
				$emailPlaintext['order_summary'].=$sDetails['label'].$sDetails['value'].PHP_EOL;
			}


			/*****************************

				[map vars -> html only]

			*****************************/
			if($order_details_html){
				/**pass on options vars**/
				$options=$this->pluginOptions;

				/**customer details as array : to use in html emails **/
				$customer_details_array=$customerDetails;/*customer vars**/
				/**order items as array : to use in html emails **/
				$order_items=$order_details_html['items'];
				/*remap additional info*/
				foreach($order_items as $oKey=>$items){
					foreach($items as $iKey=>$iVal){
						if($iKey=='addinfo'){
							$order_items[$oKey]['additional_info']=$iVal;
						}
					}
				}

				/**summary details as array : to use in html emails **/
				$order_summary=array();/*ini array*/
				foreach($order_details_html['summary'] as $sKey=>$sDetails){
					/*add to array*/
					$order_summary[$sKey]=array('label'=>$sDetails['label'], 'price'=>$sDetails['value']);
				}
			}

			/************************************************************************
			*
			*
			*	[if needed, get/add html template output]
			*
			*
			************************************************************************/
			if($order_details_html){
				$email_markup['html']='';
				if (file_exists( $this->pluginTemplateDir . '/wppizza-order-html-email.php')){
					require_once($this->pluginTemplateDir.'/wppizza-order-html-email.php');
				}
				elseif(file_exists( $this->pluginTemplateDir . '/wppizza-order-email-html.php')){
					ob_start();
					require_once($this->pluginTemplateDir.'/wppizza-order-email-html.php');
					$email_markup['html'] = ob_get_clean();
				}else{
					ob_start();
					require_once(WPPIZZA_PATH.'templates/wppizza-order-email-html.php');
					$email_markup['html'] = ob_get_clean();
				}
			}

			/************************************************************************
			*
			*
			*	[always get plaintext output as we will need it in html emails too
			*	as well as saving customer and order details in plaintext to db]
			*
			*
			************************************************************************/
			/****************************************************
				[some individual filters for plaintext template output]
			****************************************************/
			/**add ===== either side of order label**/
			$orderLabel['order_details']=apply_filters('wppizza_emailplaintext_single_value_pad',$orderLabel['order_details']);

			$email_markup['plaintext']='';
			if(file_exists( $this->pluginTemplateDir . '/wppizza-order-email-plaintext.php')){
				ob_start();
				require_once($this->pluginTemplateDir.'/wppizza-order-email-plaintext.php');
				$email_markup['plaintext'] = ob_get_clean();
			}else{
				ob_start();
				require_once(WPPIZZA_PATH.'templates/wppizza-order-email-plaintext.php');
				$email_markup['plaintext'] = ob_get_clean();
			}

			/**set plaintext to html too if webmail email address and plaintext template*/
			if($plaintext_to_webmail){
				$email_markup['html']=$this->wrap_plaintext_in_html($email_markup['plaintext']);
			}


			/**plaintext items to send to db update**/
			if(!empty($emailPlaintext['db_items'])){
				$emailPlaintext['order']=$emailPlaintext['db_items'];
			}else{
				$emailPlaintext['order']=trim($this->orderItemsPlaintext);
			}

		}
		/***************************************************************
		*
		*
		*	using template builder
		*
		*
		***************************************************************/
		if($template_id>=0){

			/**
				only apply to templates >= 0 (i.e !=-1) as templates in template directory
				will have a conditional added instead
			**/
			$order_details=apply_filters('wppizza_filter_email_markup_order_details', $order_details, $type);


			/************************************************************************
			*
			*
			*	[always get plaintext output as we will need it in html emails too]
			*
			*
			************************************************************************/
			$template_details=$WPPIZZA_TEMPLATES->getTemplate($order_details, $type, $template_id, false);

			/*markup*/
			$email_markup['plaintext']=$template_details['plaintext'];
			$email_markup['html']=$template_details['html'];/*may be false/empty*/

			/**set plaintext to html too if webmail email address and plaintext template*/
			if($plaintext_to_webmail){
				$email_markup['html']=$this->wrap_plaintext_in_html($email_markup['plaintext']);
			}
			/**set values to be inserted into customer_details and order_details in db*/
			$emailPlaintext['customer_details']=$template_details['sections']['customer'];
			$emailPlaintext['order']=$template_details['sections']['order'];
			$emailPlaintext['order_summary']=$template_details['sections']['summary'];
		}

		/****************************************************************
			[customer and order details to be saved in db
			and displayed in history - taken from email send to shop only]
		****************************************************************/
		$this->set_order_details_db($template_id, $emailPlaintext);

		return $email_markup;
	}
	/***************************************************************



		[MISCELLANEOUS HELPERS]



	***************************************************************/
	/**********************************************
		update user profile at end of order if requested
	************************************************/
	function update_userprofile($order_id, $order, $cDetails){
		/* need to get order details first if using non-COD gateways */
		if($order_id){
			$order=$this->get_order_details(false, $order_id);
			$cDetails = $order['customer_ini'];
		}
		
		/* only if wpuser and set */
		if(!empty($order['wp_user_id']) && !empty($order['order_ini']['update_profile'])){
			$user_id = $order['wp_user_id'];
			$ff=$this->pluginOptions['order_form'];
    		$ff = apply_filters('wppizza_filter_formfields_update_profile', $ff);
			foreach( $ff as $field ) {
			if(!empty($field['enabled'])) {
				if( $field['type']!='select'){
					update_user_meta( $user_id, 'wppizza_'.$field['key'], wppizza_validate_string($cDetails[$field['key']]) );	/*we've validated already, but lets just be save*/
				}else{
					$selKey = array_search($cDetails[$field['key']], $field['value']);
					update_user_meta( $user_id, 'wppizza_'.$field['key'], $selKey );
				}
			}}
		}
	return;
	}
	/*********************************************************************************************************************
	* check if we are sending plaintext template to webmail (currently only checking for gmail)
	* returns true if mail type for template is plaintext AND any receiver is @gmail / @googlemail else false
	* so we force html email sending the plaintext output wrapped in <pre> tags as gmail does
	* alwasy display plaintext as html and monospacing fonts are not applied
	*********************************************************************************************************************/
	private function plaintext_to_webmail($template_id, $order_details, $order_details_html, $plugin_options){

		$send_plaintext_as_html=false;

		/*check we are not sending html anyway*/
		if(!$order_details_html){

			/**which webmails are we checking, currenlty google hotmail and yahoo only*/
			$check_for=array('@gmail.','@googlemail.','@outlook.','@yahoo.','@hotmail.');
			$check_for=apply_filters('wppizza_email_plaintext_to_webmail_domains', $check_for);

			/**get recipients that apply to this template id*/
			$template_recipients=$this->template_recipients($template_id, $order_details, $plugin_options);

			/*check if recipienst are webmail*/
			foreach($template_recipients as $recipient){
				foreach($check_for as $webmail_str){
					$pos = stripos($recipient, $webmail_str);
					if ($pos !== false) {
						$send_plaintext_as_html=true;
						/**just retur as soon as we have found one*/
						return $send_plaintext_as_html;
					}
				}
			}
		}

		return $send_plaintext_as_html;
	}


	private function template_recipients($template_id, $order_details, $plugin_options){
		$tpl_recipients=array();

		foreach($plugin_options['templates_apply']['emails']['recipients_default'] as $k=>$tpl_id){
			/*shop, get to and bcc's*/
			if($k=='email_shop' && $tpl_id=$template_id){
				/**get to address*/
				if(isset($plugin_options['order']['order_email_to']) && is_array($plugin_options['order']['order_email_to'])){
				foreach($plugin_options['order']['order_email_to'] as $recipient){
					$tpl_recipients[]=$recipient;
				}}
				/**get bcc address*/
				if(isset($plugin_options['order']['order_email_bcc']) && is_array($plugin_options['order']['order_email_bcc'])){
				foreach($plugin_options['order']['order_email_bcc'] as $recipient){
					$tpl_recipients[]=$recipient;
				}}
			}

			/*email_customer, if exists*/
			if($k=='email_customer' && $tpl_id=$template_id && !empty($order_details['customer']['post']['cemail']['value'])){
				$tpl_recipients[]=$order_details['customer']['post']['cemail']['value'];
			}
		}
		/**additional recipients*/
		if(isset($plugin_options['templates_apply']['emails']['recipients_additional']) && is_array($plugin_options['templates_apply']['emails']['recipients_additional'])){
		foreach($plugin_options['templates_apply']['emails']['recipients_additional'] as $tpl_id=>$recipients){
			if($template_id == $tpl_id){
				foreach($recipients as $recipient){
					$tpl_recipients[]=	$recipient;
				}
			}
		}}
		return $tpl_recipients;
	}


	private function wrap_plaintext_in_html($markup){
		$style='font-size:12px';
		/*allow filtering style of pre element*/
		$style=apply_filters('wppizza_email_plaintext_to_webmail_pre_style', $style);

		$html_markup='<pre style="'.$style.'">';
		$html_markup.=$markup;
		$html_markup.='</pre>';
		return $html_markup;
	}

	/*******************************************************
		[set email subject and allow filtering]
	********************************************************/
	private function wppizza_create_email_subject($tpl_id, $customer_data){

		$addToSubjectEnabled=wppizza_return_single_dimension_array($this->pluginOptions['order_form'], 'add_to_subject_line', 'key');

		/**make customer data into single dimension for ease of use*/
		$cData=array();
		$cDataAddToSubject=array();
		foreach($customer_data as $cdataKey=>$cDataVal){
			/*truncate textareas to single line - silly to use anyway*/
			if($cDataVal['type']=='textarea'){
				$cDataVal['value']=explode(PHP_EOL,$cDataVal['value']);
				$cData[$cdataKey]=trim($cDataVal['value'][0]);
			}else{
				$cData[$cdataKey]=trim($cDataVal['value']);
			}

			if(!empty($addToSubjectEnabled[$cdataKey])){/*only enabled ones for add to subject*/
				$cDataAddToSubject[$cdataKey]=$cData[$cdataKey];
			}
		}

		/*add string to subject line (if any)*/
		$cDataAddToSubject=trim(implode(' ',$cDataAddToSubject));
		/*check if bloginfo is empty*/
		$blogInfo=get_bloginfo();
		/*clear bloginfo from subject line you want or replace with something else*/
		$blogInfo=apply_filters('wppizza_email_subject_bloginfo', $blogInfo);
		/*format depending on whether additional customer info was added to the subject line*/
		$bloginfoStandard = empty($blogInfo) ? '' : $blogInfo.': ';
		$bloginfoWithAddedSubject = empty($blogInfo) ? '' : '['.$blogInfo.'] - ';


		$email_subject=array();
		$email_subject['prefix'] 	= (empty($cDataAddToSubject) || $cDataAddToSubject=='') ? $bloginfoStandard : $bloginfoWithAddedSubject . $cDataAddToSubject.': ' ;/*if we added customer data to subjct line, change format slightly*/
		$email_subject['main']		= ''.$this->pluginOptions['localization']['your_order']['lbl'].' ';
		$email_subject['suffix'] 	= ''.$this->currentTimeLocalized.'';

		/**make subject filterable**/
		$email_subject['prefix']	= apply_filters('wppizza_filter_email_subject_prefix', $email_subject['prefix'], $tpl_id, $cData, $this->currentTimeLocalized, $blogInfo);
		$email_subject['main']		= apply_filters('wppizza_filter_email_subject', $email_subject['main'], $tpl_id, $cData, $this->currentTimeLocalized, $blogInfo);
		$email_subject['suffix']	= apply_filters('wppizza_filter_email_subject_suffix', $email_subject['suffix'], $tpl_id, $cData, $this->currentTimeLocalized, $blogInfo);

		/**make sure subject is decoded**/
		$email_subject['prefix'] 	= wppizza_email_decode_entities($email_subject['prefix'], WPPIZZA_CHARSET);
		$email_subject['main']		= wppizza_email_decode_entities($email_subject['main'], WPPIZZA_CHARSET);
		$email_subject['suffix'] 	= wppizza_email_decode_entities($email_subject['suffix'], WPPIZZA_CHARSET);

		/*reorder - or even clear entirely -  the parts the subject line is made out of if you want. bit overkill as one can do the same with filters above, but why not*/
		$email_subject=apply_filters('wppizza_email_subject_reorder',$email_subject);

		$email_subject=trim(implode(' ',$email_subject));

		return $email_subject;

	}

	/*******************************************************
		[set customer and order details to be saved in db
		and displayed in history, using plaintext part
		of template set for shop]
	********************************************************/
	private function set_order_details_db($template_id, $plaintext_sections){
		if($this->pluginOptions['templates_apply']['emails']['recipients_default']['email_shop']==$template_id){
			$this->customerDetails = $plaintext_sections['customer_details'];
			$this->orderDetails=$plaintext_sections['order'].PHP_EOL.PHP_EOL.$plaintext_sections['order_summary'].PHP_EOL;
		}
	}
	/*****************************************
		[get the order by hash or id]
	*****************************************/
	private function get_order_details($hash=false,$id=false){
		global $wpdb;
		/*ini as false*/
		$order=false;

		if($hash){
			$get_order = $wpdb->get_row("SELECT wp_user_id, id, order_ini, customer_ini, payment_status FROM ".$wpdb->prefix . $this->pluginOrderTable." WHERE hash='".$hash."' ");//order_ini,
		}else{
			$get_order = $wpdb->get_row("SELECT wp_user_id, id, order_ini, customer_ini, payment_status FROM ".$wpdb->prefix . $this->pluginOrderTable." WHERE id='".$id."' ");//order_ini,
		}
		if ( !is_wp_error( $get_order ) && is_object($get_order)) {
			$order['wp_user_id']=$get_order->wp_user_id;
			$order['order_id']=$get_order->id;
			$order['order_ini']=maybe_unserialize($get_order->order_ini);
			$order['customer_ini']=maybe_unserialize($get_order->customer_ini);
			$order['order_status']=$get_order->payment_status;
		}

		return $order;
	}
	/******************************************************************************************************************************
		[update wppizza order table as required]
		$logtofile = if set to cdb olumn name, check
		if not empty after update but if so, log post vars to a file in /logs

		mainly used to get post vars of customers if - for some reason (sql errors)
		customer data does not get stored to be able to - hopefully - get the data nevertheless
		if something went wrong. bit of a hack for the moment, but might come in handy to find out why update failed
	******************************************************************************************************************************/
	function update_db($data, $where, $format = null, $where_format = null, $blog_id=false, $logEmptyColumnToFile=false){
		global $wpdb;

		/**select the right blog table if different from current */
		if($blog_id && is_int($blog_id) && $blog_id>1){
			$wpdb->prefix=$wpdb->base_prefix . $blog_id.'_';
		}
		/*set table*/
		$table					=	$wpdb->prefix . $this->pluginOrderTable;
		/*update*/
		$result=$wpdb->update( $table, $data, $where, $format = null, $where_format = null );

		/**log if not updated or empty ******/
		if($logEmptyColumnToFile){
			$check_order = $wpdb->get_row("SELECT ".$logEmptyColumnToFile." from ".$wpdb->prefix . $this->pluginOrderTable." WHERE id='".$where['id']."' ");//order_ini,
			$xCheckEmpty=explode(',',$logEmptyColumnToFile);
			$logToFile=false;
			foreach($xCheckEmpty as $checkEmpty){
				if($check_order->$checkEmpty==''){
					$logToFile=true;
					break;
				}
			}
			if($logToFile){
				$params = array();
				parse_str($_POST['vars']['data'], $params);
				file_put_contents(WPPIZZA_PATH.'logs/error-saving-'.str_replace(',','-',$logEmptyColumnToFile).'.log','id: '.$where['id'].' | postvars '.print_r($params,true).PHP_EOL, FILE_APPEND);
			}
		}

		return $result;
	}
	/*****************************************
	*
	*	[(mis-)use filter to set plaintext items markup]
	*
	*****************************************/
	/*get category print if enabled and add to string to save in db order_details field*/
	function items_to_db_print_category($item,$output){
		static $static=0;
		$output='';
		/**only print if not empty and enabled**/
		if(isset($item['itemCatHierarchy']) && $item['itemCatHierarchy']!=''){
			if($static>0){$output.=''.PHP_EOL;}/**skip topmost EOL*/
			$output.='['.$item['itemCatHierarchy'].']'.PHP_EOL;
			/*add to db input*/
			$this->orderItemsPlaintext.=$output;
			$static++;
		}
		/*passthrough $item as the above is only for current (legacy) plaintext template that will change soon*/
		return $item;
	}
	/*
		get item plaintext markup and add to string to save in db order_details field
		making sure we implode with spaces between item details
	*/
	function items_to_db($markup_array){
		$markup_array_2_str=implode(' ',$markup_array);
		$this->orderItemsPlaintext.=$markup_array_2_str;
		return $markup_array;
	}
	/*********************************************************************************
	*
	*	[SMTP : send emails by smtp according to settings set]
	*
	********************************************************************************/
	function wppizza_smtp_send($phpmailer){

		/*force smtp*/
		$phpmailer->isSMTP();
		/******************************************************************************************
			using admin test vars

			no doubt this could be done a lot more elegantly, but for the time being, this will do
			if we are testing smpt connections, add some settings
		*****************************************************************************************/
		if(!empty($_POST['vars']['field']) && $_POST['vars']['field']=='wppizza_smtp_test'){
		    $phpmailer->Timeout  	=   7;	/*test should really work with in 5-10 secs timeout*/
			$phpmailer->Host 		= $_POST['vars']['wppizza_smtp_test_param']['smtp_host'];
			$phpmailer->Port 		= $_POST['vars']['wppizza_smtp_test_param']['smtp_port'];
			$phpmailer->SMTPAuth 	= empty($_POST['vars']['wppizza_smtp_test_param']['smtp_authentication']) ? false : true;
			$phpmailer->SMTPSecure 	= (!empty($_POST['vars']['wppizza_smtp_test_param']['smtp_encryption'])) ? $_POST['vars']['wppizza_smtp_test_param']['smtp_encryption'] : false;
			$phpmailer->Username 	= $_POST['vars']['wppizza_smtp_test_param']['smtp_username'];
			$phpmailer->Password 	= (!empty($_POST['vars']['wppizza_smtp_test_param']['smtp_password'])) ? $_POST['vars']['wppizza_smtp_test_param']['smtp_password'] : wppizza_encrypt_decrypt($this->pluginOptions['plugin_data']['smtp_password'],false);
			$phpmailer->SMTPDebug 	= 3; //Set SMTPDebug distincly for testing
		}else{
			$phpmailer->Host 		= $this->pluginOptions['plugin_data']['smtp_host'];
			$phpmailer->Port 		= $this->pluginOptions['plugin_data']['smtp_port'];
			$phpmailer->SMTPAuth 	= empty($this->pluginOptions['plugin_data']['smtp_authentication']) ? false : true;
			$phpmailer->SMTPSecure  = (!empty($this->pluginOptions['plugin_data']['smtp_encryption'])) ? $this->pluginOptions['plugin_data']['smtp_encryption'] : false;
			$phpmailer->Username = $this->pluginOptions['plugin_data']['smtp_username'];
			$phpmailer->Password  = wppizza_encrypt_decrypt($this->pluginOptions['plugin_data']['smtp_password'],false);
			$phpmailer->SMTPDebug  = !empty($options['plugin_data']['smtp_debug']) ? 3 : false;
		}
		return $phpmailer;
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
}
?>