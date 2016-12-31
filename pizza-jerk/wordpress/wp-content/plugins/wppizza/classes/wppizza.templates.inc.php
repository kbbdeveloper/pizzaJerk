<?php
/**************************************************************************************************************************************

	CLASS - WPPIZZA_TEMPLATES


**************************************************************************************************************************************/
if (!class_exists( 'WPPizza' ) ) {return ;}

if (!class_exists('WPPIZZA_TEMPLATES')) {
	class WPPIZZA_TEMPLATES extends WPPIZZA {

	public $plaintext=false;
/**********************************************************************************************
*
*
*	[construct]
*
*
*********************************************************************************************/
		function __construct() {
			parent::__construct();
		}
/**********************************************************************************************
*
*
*	[public methods]
*	-> setters
*
*********************************************************************************************/
		/* some templates allow for plaintext (emails, history print)*/
		function setPlaintext($plaintext=false){
			$this->plaintext=$plaintext;
		}
/**********************************************************************************************
*
*
*	[public methods]
*	-> getters
*
*********************************************************************************************/

		/****************************************************************
		*
		*	get order details by id and blog id
		*
		*	@return array()
		*
		****************************************************************/
		function getOrderDetails($orderId, $blogId=1, $plaintext=false){
			require(WPPIZZA_PATH.'classes/wppizza.order.details.inc.php');
			$orderDetails=new WPPIZZA_ORDER_DETAILS();
			$orderDetails->setOrderId($orderId);
			$orderDetails->setBlogId($blogId);
			$orderDetails->setPlaintext($plaintext);
			$order=$orderDetails->getOrder();/**all order vars**/

			return $order;
		}
		/**************************************************************
		*
		*	return template output
		*
		*	@return array()
		*	[html] = html markup
		*	[plaintext] = plaintext string
		*	[content-type] = text/html or text/plain
		*
		*	force filter is a hack to avoid running the same filter 2x in certain
		*	scenarios. filter management will be entirely different in wppizza 3.x
		*
		**************************************************************/
		function getTemplate($order, $type, $template_id, $preview=false, $force_filter=false){
		static $static = 0; $static++;
			/********************************
				get markup, content type and values
			********************************/
			$markup=array();
			$markup['html']=false;
			$markup['content-type']='text/html';/*ini as html*/
			$markup['sections']=array();

			/********************************
			simplify vars to us in templates
			********************************/
			$siteDetails=$order['site'];
			$multiSite=$order['multisite'];
			$orderDetails=$order['ordervars'];
			$txt=$order['localization'];
			$customerDetails=$order['customer']['post'];//omit ['others'] here
			$cartitems=$order['items'];
			$orderSummary=$order['summary'];

			/********************************
			allow filtering of order vars -
			unused (yet) in plugin
			********************************/
			$siteDetails=apply_filters('wppizza_filter_template_site_details', $siteDetails, $type, $template_id );
			$multiSite=apply_filters('wppizza_filter_template_multi_site', $multiSite, $type, $template_id );
			$orderDetails=apply_filters('wppizza_filter_template_order_details', $orderDetails, $type, $template_id );
			$txt=apply_filters('wppizza_filter_template_order_txt', $txt, $type, $template_id );
			$customerDetails=apply_filters('wppizza_filter_template_customer_details', $customerDetails, $type, $template_id );
			$cartitems=apply_filters('wppizza_filter_template_cart_items', $cartitems, $type, $template_id );
			$orderSummary=apply_filters('wppizza_filter_template_order_summary', $orderSummary, $type, $template_id );

			/**
				using templates in template directory
			**/
			if($template_id==-1){
				/**print template**/
				if($type=='print'){
					/**get template**/
					if(file_exists( $this->pluginTemplateDir . '/wppizza-order-print.php')){
						ob_start();
						require_once($this->pluginTemplateDir.'/wppizza-order-print.php');
						$markup['html'] = ob_get_clean();
					}else{
						ob_start();
						require_once(WPPIZZA_PATH.'templates/wppizza-order-print.php');
						$markup['html'] = ob_get_clean();
					}
				}
			return $markup;
			}

			/**
				using created template or preview of the same
			**/
			if($template_id>=0 || $preview){
				/*standard*/
				if(!$preview){
					/*get template values*/
					$template=$this->pluginOptions['templates'][$type][$template_id];
					/*is  html ?*/
					$isPlaintext=($template['mail_type']=='phpmailer') ? false : true;
				}
				/*for preview use post vars**/
				if($preview){
					/*get template values*/
					$template=$preview;
					/*is  html ?*/
					$isPlaintext=($preview['mail_type']=='phpmailer') ? false : true;
				}


				/* 
					allow filtering of template values (to be used in conjunction with "wppizza_filter_template_site_details" etc above)
					to add some arbitrary additional rows/parameters/output if and where required  
				*/
				$template['values'] = apply_filters('wppizza_filter_template_values', $template['values'], $template_id, $type);

				/****************************************
					set content type / header
				****************************************/
				if($isPlaintext){
					$markup['content-type']='text/plain';
				}else{
					$markup['content-type']='text/html';
				}

				/****************************************
					always return plaintext [header and markup]
					as it will also be used in html emails
					as plaintext part

					copied TO do_emails to avoid running multiple times on emails
					however, runs here too on preview and print order !!

					definitely also something to refactor in wppizza 3.0

				****************************************/
				/**add filters for consistant spacing preview and print only**/
				if($preview || $type=='print'){

					/*a quick hack to make sure we only apply filters once. these filters will move entirely elsewhere in wppizza v3*/
					if($static==1 && ( $preview || $force_filter)){
						add_filter('wppizza_filter_template_plaintext_transaction_details',array($this,'wppizza_template_plaintext_linelength'));
						add_filter('wppizza_filter_template_plaintext_customer_detail',array($this,'wppizza_template_plaintext_linelength'));
						add_filter('wppizza_filter_template_plaintext_cart_item_header',array($this,'wppizza_template_plaintext_linelength'),10,3);
						add_filter('wppizza_filter_template_plaintext_cart_item',array($this,'wppizza_template_plaintext_linelength'));
						add_filter('wppizza_filter_template_plaintext_cart_item_addinfo',array($this,'wppizza_template_plaintext_linelength'));
						add_filter('wppizza_filter_template_plaintext_summary_detail',array($this,'wppizza_template_plaintext_linelength'));
						add_filter('wppizza_filter_template_plaintext_padstring',array($this,'wppizza_template_plaintext_linelength'));
					$static++;
					}
				}

				/*markup*/
				require(WPPIZZA_PATH.'inc/markup.order.plaintext.inc.php');
				$markup['plaintext']=$plaintext;
				$markup['sections']=$plaintext_sections;

				/****************************************
					as html [header and markup]
				****************************************/
				if(!$isPlaintext){
					/*markup*/
					require(WPPIZZA_PATH.'inc/markup.order.html.inc.php');
					$markup['html']=$html;
					//$markup['sections']=$html_sections;//not required in html
				}

			}
			return $markup;
		}

		/**********************************************************************************************
		*
		*
		*	[markup for admin settings sections template]
		*
		*
		*********************************************************************************************/
		function getTemplateSettings($field, $options){
			require(WPPIZZA_PATH .'inc/admin.echo.get_templates.inc.php');
		}
		/*************************************************************
		*
		*	get/set array key/values for new templates or default on install
		*
		*************************************************************/
		function getTemplateValues($msgKey, $templateKey, $tplVals=false, $ini=false){

			/**********************************
			*
			*	get all possible order variables
			*
			**********************************/
			require(WPPIZZA_PATH.'classes/wppizza.order.details.inc.php');
			$orderVars=new WPPIZZA_ORDER_DETAILS();
			$siteVars=$orderVars->getSiteVariables();
			$dbVars=$orderVars->getDbFields();
			$customerVars=$orderVars->getCustomerVariables();
			$itemVars=$orderVars->getItemVariables();//exclude unnecessary vars
			$summaryVars=$orderVars->getSummaryVariables();


			/***default recipients available/required irrelevant for print template **/
			if($templateKey!='print'){
				$template['recipients']=wppizza_email_recipients();
			}

			/***************************************
			*
			*
			*	global variables
			*
			*
			****************************************/
			/*global**/
			$title=__('new', 'wppizza-locale').' [ID:'.$msgKey.'] ';
			/*default on first install*/
			if($ini){
				$title=__('default', 'wppizza-locale');
			}
			$template[$templateKey]['title']	= !empty($tplVals['title']) ? $tplVals['title'] : $title;
			$template[$templateKey]['mail_type']	= (empty($tplVals['mail_type']) || !$tplVals) ? 'phpmailer' : $tplVals['mail_type'];
			$template[$templateKey]['omit_attachments']	= (empty($tplVals['omit_attachments']) || !$tplVals) ? false : true;
			/** admin sort in hidden input as json object */
			$template[$templateKey]['admin_sort']	= (empty($tplVals['admin_sort']) || !$tplVals) ? false : htmlspecialchars(json_encode($tplVals['admin_sort']), ENT_QUOTES, 'UTF-8');/* encode array to json again, escaping quotes ";
			/** admin sort array to use when displaying, as array*/
			$template[$templateKey]['admin_sort_array']	= (empty($tplVals['admin_sort']) || !$tplVals) ? false : $tplVals['admin_sort'];


			/**irrelevant for print template**/
			if($templateKey!='print'){
				$template[$templateKey]['recipients_additional']	= !empty($this->pluginOptions['templates_apply'][$templateKey]['recipients_additional'][$msgKey]) ? $this->pluginOptions['templates_apply'][$templateKey]['recipients_additional'][$msgKey] : array();
			}

			/**styles global**/
			if($templateKey=='emails'){
				$template[$templateKey]['style']['global']['body'] = isset($tplVals['style']['global']['body']) ? $tplVals['style']['global']['body'] : 'margin: 0px;background-color: #FFFFFF;font-size: 14px;  color: #444444;  font-family: Verdana, Helvetica, Arial, sans-serif;';
				$template[$templateKey]['style']['global']['wrapper'] = isset($tplVals['style']['global']['wrapper']) ? $tplVals['style']['global']['wrapper'] : 'margin: 10px 0;width:100%';
				$template[$templateKey]['style']['global']['table'] = isset($tplVals['style']['global']['table']) ? $tplVals['style']['global']['table'] : 'width:500px;margin : 0 auto;  border: 1px dotted #CECECE;  background: #F4F3F4;';
			}
			if($templateKey=='print'){
				$printCss='';
					$printCss.='html,body,table,tbody,tr,td,th,span{font-size:12px;font-family:Arial, Verdana, Helvetica, sans-serif;margin:0;padding:0;text-align:left;}';
					$printCss.='table{width:100%;margin:0 0 10px 0;}';
					$printCss.='th{padding:5px;}';
					$printCss.='td{padding:0 5px;vertical-align:top}';

					$printCss.='#header{margin:0;}';
					$printCss.='#header td{font-size:250%;text-align:center;}';
					$printCss.='#header #blogname td{white-space:nowrap;padding-bottom:5px;}';
					$printCss.='#header #address td{white-space:nowrap;font-size:130%;padding-bottom:5px;}';

					$printCss.='#multisite{margin:0}';
					$printCss.='#multisite tbody>tr>td{text-align:center}';


					$printCss.='#overview tbody>tr>td{width:50%;white-space:nowrap;}';
					$printCss.='#overview tbody>tr>td:first-child{text-align:right}';
					$printCss.='#overview tbody>tr>td:last-child{text-align:left}';
					$printCss.='#overview #order_date td{border-top:2px solid;border-bottom:2px solid;font-size:120%;text-align:center;padding:5px}';
					$printCss.='#overview #order_id td{font-size:180%}';
					$printCss.='#overview #payment_due td{font-size:180%}';
					$printCss.='#overview #pickup_delivery td{font-size:180%;}';

					$printCss.='#customer th{border-top:2px solid;border-bottom:2px solid;white-space:nowrap;font-size:120%;text-align:center}';

					$printCss.='#items th{border-top:2px solid;border-bottom:2px solid;white-space:nowrap;}';
					$printCss.='#items th:first-child,#items th:last-child{width:20px;white-space:nowrap;}';
					$printCss.='#items .item-blog td{padding:5px 2px 5px 2px; border-bottom:1px solid;font-weight:600;font-size:120%}';
					$printCss.='#items .item-category td{padding:5px 2px 2px 2px; border-bottom:1px dashed }';
					$printCss.='#items .item td{padding-top:5px;font-size:100%}';
					$printCss.='#items .item td:first-child{text-align:center;white-space:nowrap;}';
					$printCss.='#items .item td:last-child{text-align:right;white-space:nowrap;}';
					$printCss.='#items tbody > tr.divider > td > hr {border:none;border-top:1px dotted #AAAAAA;}';

					$printCss.='#summary {border-top:1px solid;border-bottom:1px solid;}';
					$printCss.='#summary tbody > tr > td{text-align:right}';
					$printCss.='#summary tbody > tr > td:last-child{width:100px}';

				$template[$templateKey]['style']['global']['body'] = isset($tplVals['style']['global']['body']) ? str_replace('}','}'.PHP_EOL.'',$tplVals['style']['global']['body']) : str_replace('}','}'.PHP_EOL.'',$printCss);
			}

			/***************************************
			*
			*
			*	site variables (blog id etc)
			*
			*
			****************************************/
			if(!$ini){/*skip for ini values*/
				$template[$templateKey]['all']['parts']['site'] = $this->pluginOptions['localization']['templates_label_site']['lbl'];
			}
			if(!empty($tplVals['enabled']['site']) || !$tplVals ) { $template[$templateKey]['enabled']['site'] = 'site'; }
			if(!empty($tplVals['parts_label']['site'])) { $template[$templateKey]['parts_label']['site'] = true; }
			$template[$templateKey]['values']['site'] = array();



			/*preselected key and css if new template*/
			$preselect['site']=array();
			if(!$tplVals){
				if($templateKey=='emails'){
					$preselect['site']['blogname']='font-size: 160%;font-weight:600';
					//$preselect['site']['siteurl']='';
					//$preselect['site']['header ']='';
					//$preselect['site']['address']='';
				}
				if($templateKey=='print'){
					$preselect['site']['blogname']='font-size: 160%;';
					//$preselect['site']['siteurl']='';
					//$preselect['site']['header ']='';
					$preselect['site']['address']='';
				}
			}
			foreach($siteVars as $k=>$v){
				if(!$ini){/*ini values only get enabled*/
					$template[$templateKey]['all']['values']['site'][$k] = $v['label'];
				}
				if(!empty($tplVals['values']['site'][$k]) || isset($preselect['site'][$k])){
					$template[$templateKey]['values']['site'][$k] = $k;
				}


				/*style variables td's emails only**/
				if($templateKey=='emails'){
					if(!$tplVals && isset($preselect['site'][$k])){
						$template[$templateKey]['style']['site'][''.$k.'-tdall'] = $preselect['site'][$k];
					}else{
						$template[$templateKey]['style']['site'][''.$k.'-tdall'] = isset($tplVals['style']['site'][''.$k.'-tdall']) ? $tplVals['style']['site'][''.$k.'-tdall'] : '';
					}
				}
			}

			/*style site table**/
			if($templateKey=='emails'){
				$template[$templateKey]['style']['site']['table'] = isset($tplVals['style']['site']['table']) ? $tplVals['style']['site']['table'] : 'padding:30px;text-align:center;background-color:#21759B;color:#FFFFFF;';
				$template[$templateKey]['style']['site']['th'] = isset($tplVals['style']['site']['th']) ? $tplVals['style']['site']['th'] : '';
				$template[$templateKey]['style']['site']['td-ctr'] = isset($tplVals['style']['site']['td-ctr']) ? $tplVals['style']['site']['td-ctr'] : 'text-align:center';
			}

			/***************************************
			*
			*
			*	miscellaneous order variables
			*
			*
			****************************************/
			if(!$ini){/*skip for ini values*/
				$template[$templateKey]['all']['parts']['ordervars'] =  $this->pluginOptions['localization']['templates_label_ordervars']['lbl'];
			}


			if(!empty($tplVals['enabled']['ordervars']) || !$tplVals ) { $template[$templateKey]['enabled']['ordervars'] = 'ordervars'; }
			if(!empty($tplVals['parts_label']['ordervars'])) { $template[$templateKey]['parts_label']['ordervars'] = true; }
			$template[$templateKey]['values']['ordervars'] = array();


			/*preselected key and css if new template - print or emails*/
			$preselect['ordervars']=array();
			if(!$tplVals){
				if($templateKey=='emails'){
					$preselect['ordervars']['order_id']='';
					$preselect['ordervars']['order_date']='text-align:center';
					$preselect['ordervars']['transaction_id']='';
					$preselect['ordervars']['payment_type']='';
					//$preselect['ordervars']['payment_method']='';
					$preselect['ordervars']['payment_due']='';
					$preselect['ordervars']['pickup_delivery']='';
					//$preselect['ordervars']['total']='';
				}
				if($templateKey=='print'){
					$preselect['ordervars']['order_id']='font-size:180%';
					$preselect['ordervars']['order_date']='text-align:center; border-top: 1px solid; border-bottom: 1px solid;font-size:120%';
					$preselect['ordervars']['transaction_id']='';
					$preselect['ordervars']['payment_type']='';
					//$preselect['ordervars']['payment_method']='';
					$preselect['ordervars']['payment_due']='font-size:180%';
					$preselect['ordervars']['pickup_delivery']='font-size:180%';
					//$preselect['ordervars']['total']='';
				}
			}
			foreach($dbVars as $k=>$v){
				if(!$ini){/*ini values only get enabled*/
					$template[$templateKey]['all']['values']['ordervars'][$k] = $v['label'];
				}
				if(!empty($tplVals['values']['ordervars'][$k]) || isset($preselect['ordervars'][$k])){
					$template[$templateKey]['values']['ordervars'][$k] = $k;
				}

				/*style variables td's emails only**/
				if($templateKey=='emails'){
					if(!$tplVals && isset($preselect['ordervars'][$k])){
						$template[$templateKey]['style']['ordervars'][''.$k.'-tdall'] = $preselect['ordervars'][$k];
					}else{
						$template[$templateKey]['style']['ordervars'][''.$k.'-tdall'] = isset($tplVals['style']['ordervars'][''.$k.'-tdall']) ? $tplVals['style']['ordervars'][''.$k.'-tdall'] : '';
					}
				}
			}
			/*style miscellaneous order variables table**/
			if($templateKey=='emails'){
				$template[$templateKey]['style']['ordervars']['table'] = isset($tplVals['style']['ordervars']['table']) ? $tplVals['style']['ordervars']['table'] : 'margin:5px 0 30px 0;border-bottom:1px dotted #cecece';
				$template[$templateKey]['style']['ordervars']['th'] = isset($tplVals['style']['ordervars']['th']) ? $tplVals['style']['ordervars']['th'] : '';
				$template[$templateKey]['style']['ordervars']['td-lft'] = isset($tplVals['style']['ordervars']['td-lft']) ? $tplVals['style']['ordervars']['td-lft'] : 'width:50%;display:inline-block;text-align:right;padding:2px';
				$template[$templateKey]['style']['ordervars']['td-rgt'] = isset($tplVals['style']['ordervars']['td-rgt']) ? $tplVals['style']['ordervars']['td-rgt'] : 'padding:2px';
			}

			/***************************************
			*
			*
			*	customer variables
			*
			*
			****************************************/
			if(!$ini){/*skip for ini values*/
				$template[$templateKey]['all']['parts']['customer'] =   $this->pluginOptions['localization']['templates_label_customer']['lbl'];
			}

			if(!empty($tplVals['enabled']['customer']) || !$tplVals ) { $template[$templateKey]['enabled']['customer'] = 'customer'; }
			if(!empty($tplVals['parts_label']['customer']) || !$tplVals ) { $template[$templateKey]['parts_label']['customer'] = true; }
			$template[$templateKey]['values']['customer'] = array();


			foreach($customerVars as $k=>$v){
				if(!$ini){/*ini values only get enabled*/
					$template[$templateKey]['all']['values']['customer'][$k] = $v['lbl'];
				}
				if(!empty($tplVals['values']['customer'][$k]) || !$tplVals ){
					$template[$templateKey]['values']['customer'][$k] = $k;
				}

				/*style variables td's emails only**/
				if($templateKey=='emails'){
					$template[$templateKey]['style']['customer'][''.$k.'-tdall'] = isset($tplVals['style']['customer'][''.$k.'-tdall']) ? $tplVals['style']['customer'][''.$k.'-tdall'] : '';
				}
			}
			/*style customer variables table**/
			if($templateKey=='emails'){
				$template[$templateKey]['style']['customer']['table'] = isset($tplVals['style']['customer']['table']) ? $tplVals['style']['customer']['table'] : 'margin:20px 0;';
				$template[$templateKey]['style']['customer']['th'] = isset($tplVals['style']['customer']['th']) ? $tplVals['style']['customer']['th'] : '';
				$template[$templateKey]['style']['customer']['td-lft'] = isset($tplVals['style']['customer']['td-lft']) ? $tplVals['style']['customer']['td-lft'] : 'text-align:left;padding:2px';
				$template[$templateKey]['style']['customer']['td-rgt'] = isset($tplVals['style']['customer']['td-rgt']) ? $tplVals['style']['customer']['td-rgt'] : 'text-align:right;padding:2px';
			}

			/***************************************
			*
			*
			*	order details
			*
			*
			****************************************/
			if(!$ini){/*skip for ini values*/
				$template[$templateKey]['all']['parts']['order'] =   __('Order Details', 'wppizza-locale');
			}

			if(!empty($tplVals['enabled']['order']) || !$tplVals ) { $template[$templateKey]['enabled']['order'] = 'order'; }
			if(!empty($tplVals['parts_label']['order']) || !$tplVals ) { $template[$templateKey]['parts_label']['order'] = true; }
			$template[$templateKey]['values']['order'] = array();


			foreach($itemVars as $k=>$v){
				if(!$ini){/*ini values only get enabled*/
					$template[$templateKey]['all']['values']['order'][$k] = $v['label'];
				}
				if(!empty($tplVals['values']['order'][$k]) || !$tplVals ){
					$template[$templateKey]['values']['order'][$k] = $k;
				}

				/*style variables td's emails only**/
				if($templateKey=='emails'){
					$template[$templateKey]['style']['order'][''.$k.'-tdall'] = isset($tplVals['style']['order'][''.$k.'-tdall']) ? $tplVals['style']['order'][''.$k.'-tdall'] : '';
				}
			}
			/*style order details table**/
			if($templateKey=='emails'){
				$template[$templateKey]['style']['order']['table'] = isset($tplVals['style']['order']['table']) ? $tplVals['style']['order']['table'] : 'margin:10px 0;';
				$template[$templateKey]['style']['order']['th'] = isset($tplVals['style']['order']['th']) ? $tplVals['style']['order']['th'] : 'font-weight:bold;white-space: nowrap;padding:5px 2px; border-bottom:1px solid;border-top: 1px solid;';

				/*if displaying blogname*/
				$template[$templateKey]['style']['order']['td-blogname'] = isset($tplVals['style']['order']['td-blogname']) ? $tplVals['style']['order']['td-blogname'] : 'font-size:120%;text-decoration:underline;padding:10px 2px;';
				/*if displaying cat name*/
				$template[$templateKey]['style']['order']['td-catname'] = isset($tplVals['style']['order']['td-catname']) ? $tplVals['style']['order']['td-catname'] : 'border-bottom:1px dotted #cecece; padding:7px 2px;';

				$template[$templateKey]['style']['order']['td-lft'] = isset($tplVals['style']['order']['td-lft']) ? $tplVals['style']['order']['td-lft'] : 'text-align:left;padding:2px;white-space: nowrap;';
				$template[$templateKey]['style']['order']['td-ctr'] = isset($tplVals['style']['order']['td-ctr']) ? $tplVals['style']['order']['td-ctr'] : 'text-align:left;padding:2px;';
				$template[$templateKey]['style']['order']['td-rgt'] = isset($tplVals['style']['order']['td-rgt']) ? $tplVals['style']['order']['td-rgt'] : 'text-align:right;padding:2px;white-space: nowrap;';
			}

			/***************************************
			*
			*
			*	summary details
			*
			*
			****************************************/
			if(!$ini){/*skip for ini values*/
				$template[$templateKey]['all']['parts']['summary'] =   $this->pluginOptions['localization']['templates_label_summary']['lbl'];
			}

			if(!empty($tplVals['enabled']['summary']) || !$tplVals ) { $template[$templateKey]['enabled']['summary'] = 'summary'; }
			if(!empty($tplVals['parts_label']['summary'])) { $template[$templateKey]['parts_label']['summary'] = true; }
			$template[$templateKey]['values']['summary'] = array();



			/*preselected key and css if new template*/
			$preselect['summary']=array();
			if(!$tplVals){
				$preselect['summary']['cartitems']='';
				$preselect['summary']['discount']='';
				$preselect['summary']['item_tax']='';
				$preselect['summary']['delivery']='';
				$preselect['summary']['handling_charge']='';
				$preselect['summary']['total']='font-weight:600;padding:10px 0;border-top:1px dotted #cecece';
				$preselect['summary']['self_pickup']='color:red';
				$preselect['summary']['delivery_note']='color:red';
			}

			foreach($summaryVars as $k=>$v){
				if(!$ini){/*ini values only get enabled*/
					$template[$templateKey]['all']['values']['summary'][$k] = $v['label'];
				}
				if(!empty($tplVals['values']['summary'][$k]) || isset($preselect['summary'][$k])){
					$template[$templateKey]['values']['summary'][$k] = $k;
				}


				/*style variables td's emails only**/
				if($templateKey=='emails'){
					if(!$tplVals && isset($preselect['summary'][$k])){
						$template[$templateKey]['style']['summary'][''.$k.'-tdall'] = $preselect['summary'][$k];
					}else{
						$template[$templateKey]['style']['summary'][''.$k.'-tdall'] = isset($tplVals['style']['summary'][''.$k.'-tdall']) ? $tplVals['style']['summary'][''.$k.'-tdall'] : '';
					}
				}
			}
			/*style summary details table**/
			if($templateKey=='emails'){
				$template[$templateKey]['style']['summary']['table'] = isset($tplVals['style']['summary']['table']) ? $tplVals['style']['summary']['table'] : 'margin:0 0 10px;border-top:1px dotted #cecece';
				$template[$templateKey]['style']['summary']['th'] = isset($tplVals['style']['summary']['th']) ? $tplVals['style']['summary']['th'] : '';
				$template[$templateKey]['style']['summary']['td-lft'] = isset($tplVals['style']['summary']['td-lft']) ? $tplVals['style']['summary']['td-lft'] : 'text-align:left;padding:2px';
				$template[$templateKey]['style']['summary']['td-rgt'] = isset($tplVals['style']['summary']['td-rgt']) ? $tplVals['style']['summary']['td-rgt'] : 'text-align:right;padding:2px';
			}

			/****************************************
				make the array to insert at install
				only runs to add default option/template
				if new install or the first time templates
				options are added to the plugin
			****************************************/
			if($ini){
				$default=array();
				$default[0]=$template[$templateKey];
				//$default[0]['values']=$template['Vars'];
				//$default[0]['style']=$template[$templateKey]['style'];

				return $default;
			}


			return $template;
		}
		/*************************************************************
		*
		*	create admin markup of email/print templates
		*
		*************************************************************/
		function getTemplateMarkupAdmin($msgKey, $templateKey, $tplVals=false, $arrayIdent='templates', $ident='template'){

					/*****************************************
						get set values of this template
						will return default values if new
					*****************************************/
					$tpl=$this->getTemplateValues($msgKey, $templateKey, $tplVals);
					$tplVars=$tpl[$templateKey];

					/******************************************
						use [admin_sort_array] for sorted display if exsist
						overriding  [all][parts] | [all][values]
					*******************************************/
					if(!empty($tplVars['admin_sort_array'])){
						$admin_parts = array_flip(array_keys($tplVars['admin_sort_array']));
						/** sorted parts and values */
						$admin_sort_parts = array();
						$admin_sort_values = array();
						foreach($admin_parts as $key=>$bool){
							/* parts */
							$admin_sort_parts[$key] = $tplVars['all']['parts'][$key];

							/* values */
							foreach($tplVars['admin_sort_array'][$key] as $val_key => $val){
								/* because we may have removed a form field for example we must check for isset */
								if(isset($tplVars['all']['values'][$key][$val_key])){
								$admin_sort_values[$key][$val_key] = $tplVars['all']['values'][$key][$val_key];
								}
							}
							/** add any newly added (newly enabled customer fields for example) **/
							$add_new_values = array_diff_key((array)$tplVars['all']['values'][$key], (array)$admin_sort_values[$key]);
							if(!empty($add_new_values) && is_array($add_new_values)){
								foreach($add_new_values as $aKey=>$aVal){
									$admin_sort_values[$key][$aKey] = $aVal;
								}
							}
						}
						/* show sorted parts in order as set */
						$tplVars['all']['parts'] = $admin_sort_parts;
						/* show sorted values in order as set */
						$tplVars['all']['values'] = $admin_sort_values;
					}
					/*****************************************
						new message add flag for counting purposes
					*****************************************/
					if(!$tplVals){
						$msgBodyDisplay='block';
						$newMsgClass=' '.$this->pluginSlug.'-'.$ident.'-new';
					}

					if($tplVals){
						$msgBodyDisplay='none';
						$newMsgClass='';
						$msgEditButtonDisplay='inline-block';
					}

					/**
						templates admin table markup
					**/
					$markup='';

					$markup.='<table id="'.$this->pluginSlug.'-'.$ident.'-table-'.$msgKey.'" class="'.$this->pluginSlug.'-'.$ident.'-table'.$newMsgClass.' widefat">';
							$markup.='<thead id="'.$this->pluginSlug.'-'.$ident.'-thead-'.$msgKey.'" class="'.$this->pluginSlug.'-'.$ident.'-thead">';
								$markup.='<tr >';
									$markup.='<th colspan="5">';
										/* hidden element to save sortorder regarless of whether checkbox is enabled or not */
										$markup.='<input id="'.$this->pluginSlug.'-sortorder-'.$templateKey.'-'.$msgKey.'" name="'.$this->pluginSlug.'['.$arrayIdent.']['.$templateKey.']['.$msgKey.'][admin_sort]" type="hidden" value="'.(!empty($tplVars['admin_sort']) ? $tplVars['admin_sort']: '' ).'" />';
										/***************************************
										*
										*	on/off, label, ashtml, recipients etc  left
										*
										****************************************/
										$markup.='<div>';

											/**is this the print template to use? */
											if($templateKey=='print'){
												$markup.='<label class="wppizza-dashicons wppizza-dashicons-radio">'.__('use ','wppizza-locale').'<input type="radio" id="'.$this->pluginSlug.'_'.$arrayIdent.'_'.$templateKey.'_print_id_'.$msgKey.'" name="'.$this->pluginSlug.'['.$arrayIdent.']['.$templateKey.'][print_id]" '.checked($this->pluginOptions['templates_apply'][$templateKey],$msgKey,false).' value="'.$msgKey.'" /></label>';
											}

											/*title/label internal*/
											$markup.='<label class="wppizza-template-label"><input name="'.$this->pluginSlug.'['.$arrayIdent.']['.$templateKey.']['.$msgKey.'][title]" type="text" value="'.$tplVars['title'].'" /></label>';

											/*mail type */
											$markup.='<label class="wppizza-template-mail_type">';
												$markup.=__('format', 'wppizza-locale');
													/*set values*/
													$mailFieldName=''.$this->pluginSlug.'['.$arrayIdent.']['.$templateKey.']['.$msgKey.'][mail_type]';
													$mailFieldSelected=$tplVars['mail_type'];
													$mailID=''.$this->pluginSlug.'_'.$ident.'_mail_type_'.$templateKey.'_'.$msgKey.'';
													$mailClass=''.$this->pluginSlug.'_'.$ident.'_mail_type';

												$markup.=wppizza_admin_mail_delivery_options($this->pluginOptions, $mailFieldName, $mailFieldSelected, $mailID, $mailClass);
											$markup.='</label>';


											/*if email , who does this apply to - irrelevant for print template*/
											if($templateKey=='emails'){
												$markup.='<label class="wppizza-template-recipients">'.__('email recipients','wppizza-locale').': </label>';
												foreach($tpl['recipients'] as $recKey=>$recTitle){
													$markup.='<label class="wppizza-template-recipients"><input type="radio" id="'.$this->pluginSlug.'_'.$arrayIdent.'_'.$templateKey.'_recipients_'.$recKey.'_'.$msgKey.'" name="'.$this->pluginSlug.'['.$arrayIdent.']['.$templateKey.'][recipients_default]['.$recKey.']" '.checked($this->pluginOptions['templates_apply'][$templateKey]['recipients_default'][$recKey],$msgKey,false).' value="'.$msgKey.'" />'.$recTitle.'</label>';
												}
												/**additional recipients**/
												$recipients_additional=!empty($tplVars['recipients_additional']) ? implode(',',$tplVars['recipients_additional']) : '' ;
												$markup.='<label class="wppizza-template-label wppizza-template-additional-recipients">'.__('additional recipients','wppizza-locale').' <span>'.__('(comma separated)','wppizza-locale').'</span><input type="text" name="'.$this->pluginSlug.'['.$arrayIdent.']['.$templateKey.']['.$msgKey.'][recipients_additional]" value="'.$recipients_additional.'" /></label>';

												/**omit attachments**/
												$markup.='<label class="wppizza-template-recipients"><input type="checkbox"  name="'.$this->pluginSlug.'['.$arrayIdent.']['.$templateKey.']['.$msgKey.'][omit_attachments]" '.checked($tplVars['omit_attachments'],true,false).' value="1" /> '.__('omit attachments (if any)','wppizza-locale').'</label>';

											}
										$markup.='</div>';

										/***************************************
										*
										*	edit, preview, locked etc  right
										*
										****************************************/
										$markup.='<div class="'.$this->pluginSlug.'-'.$ident.'-thead-utils" >';

											/*icon edit*/
											$markup.="<span id='".$this->pluginSlug."_".$ident."_toggle_".$templateKey."_".$msgKey."' class='".$this->pluginSlug."_".$ident."_details_toggle  wppizza-dashicons dashicons-edit' title='".__('edit', 'wppizza-locale')."'></span>";

											/*icon code edit (show css/style)*/
											$htmlactiveclass=($tplVars['mail_type']=='phpmailer') ? ''.$this->pluginSlug.'_'.$ident.'_style_toggle '.$this->pluginSlug.'-dashicons-'.$ident.'-'.$templateKey.'-media-code' : ' '.$this->pluginSlug.'-dashicons-'.$ident.'-'.$templateKey.'-media-code-inactive' ;
											$htmlactivetitle=($tplVars['mail_type']=='phpmailer') ? __('toggle style input','wppizza-locale') : __('N/A while plaintext template','wppizza-locale') ;


											$markup.='<span id="'.$this->pluginSlug.'-dashicons-'.$ident.'-'.$templateKey.'-media-code-'.$msgKey.'" class="wppizza-dashicons dashicons-media-code '.$htmlactiveclass.'" title="'.$htmlactivetitle.'"></span>';

											/*icon preview*/
											$markup.="<span  id='".$this->pluginSlug."_".$ident."_".$templateKey."_preview-".$msgKey."' class='".$this->pluginSlug."_".$ident."_preview wppizza-dashicons dashicons-visibility' title='".__('preview', 'wppizza-locale')."'></span>";

											/*icon delete*/
											$markup.="<span id='".$this->pluginSlug."_".$ident."_".$templateKey."_delete-".$msgKey."' class='".$this->pluginSlug."_".$ident."_delete wppizza-dashicons dashicons-trash' title='".__('delete', 'wppizza-locale')."'></span>";

										$markup.='</div>';

									$markup.='</th>';
								$markup.='</tr>';

								/**css/style global**/
								$markup.='<tr id="'.$this->pluginSlug.'-'.$ident.'-global-styles-'.$msgKey.'" class="'.$this->pluginSlug.'-'.$ident.'-global-styles">';
									$markup.='<th>';
											if($templateKey=='emails'){
												$markup.=''.__('Body','wppizza-locale').'';
											}
											if($templateKey=='print'){
												$markup.=''.__('CSS','wppizza-locale').'';
											}
											$markup.='<textarea  class="'.$this->pluginSlug.'-'.$ident.'-'.$templateKey.'-global-style-body '.$this->pluginSlug.'-'.$ident.'-global-style" name="'.$this->pluginSlug.'['.$arrayIdent.']['.$templateKey.']['.$msgKey.'][style][global][body]">'.$tplVars['style']['global']['body'].'</textarea>';
											if($templateKey=='emails'){
												$markup.=''.__('Wrapper','wppizza-locale').'<textarea  class="'.$this->pluginSlug.'-'.$ident.'-global-style-wrapper '.$this->pluginSlug.'-'.$ident.'-global-style" name="'.$this->pluginSlug.'['.$arrayIdent.']['.$templateKey.']['.$msgKey.'][style][global][wrapper]">'.$tplVars['style']['global']['wrapper'].'</textarea>';
												$markup.=''.__('Main Table','wppizza-locale').'<textarea  class="'.$this->pluginSlug.'-'.$ident.'-global-style-table '.$this->pluginSlug.'-'.$ident.'-global-style" name="'.$this->pluginSlug.'['.$arrayIdent.']['.$templateKey.']['.$msgKey.'][style][global][table]">'.$tplVars['style']['global']['table'].'</textarea>';
											}
									$markup.='</th>';
								$markup.='</tr>';


							$markup.='</thead>';

							/*add class for style input in print template  to be able to reshow/hide as required*/
							$addStyleClass='';
							if($templateKey=='print'){
								$addStyleClass=' '.$this->pluginSlug.'-'.$ident.'-'.$templateKey.'-value-element-only-'.$msgKey.'';
							}

							$markup.='<tbody id="'.$this->pluginSlug.'-'.$ident.'-body-'.$msgKey.'" class="'.$this->pluginSlug.'-'.$ident.'-body'.$addStyleClass.'" style="display:'.$msgBodyDisplay.'">';

								/**site,customer,order vars, order , summary**/
								$markup.='<tr class="'.$this->pluginSlug.'-'.$ident.'-parts">';
								foreach($tplVars['all']['parts'] as $sortKey=>$sortVal){

									$markup.='<td class="'.$this->pluginSlug.'-'.$ident.'-'.$templateKey.'-parts-'.$sortKey.'">';

										/*part on off and sort**/
										$markup.='<div id="'.$this->pluginSlug.'-'.$ident.'-'.$templateKey.'-section-'.$msgKey.'" class="'.$this->pluginSlug.'-'.$ident.'-'.$templateKey.'-section '.$this->pluginSlug.'-'.$ident.'-'.$templateKey.'-value-element-'.$msgKey.'">';

											$markup.='<span>';
												$markup.='<label class="button"><input id="'.$this->pluginSlug.'-'.$ident.'-part-'.$msgKey.'-'.$sortKey.'"
												class="'.$this->pluginSlug.'-'.$ident.'-part"
												name="'.$this->pluginSlug.'['.$arrayIdent.']['.$templateKey.']['.$msgKey.'][enabled]['.$sortKey.']"
												type="checkbox"
												'.checked(!empty($tplVars['enabled'][$sortKey]),true,false).' value="'.$sortKey.'" />
												'.$tplVars['all']['parts'][$sortKey].'</label>';
											$markup.='</span>';

											$markup.='<span class="wppizza-dashicons-leftright dashicons-leftright wppizza-'.$ident.'-sort-part" style="float:right" title="'.__('drag and drop to sort','wppizza-locale').'">';
												$markup.='<input name="'.$this->pluginSlug.'['.$arrayIdent.']['.$templateKey.']['.$msgKey.'][sort]['.$sortKey.']" type="hidden" value="'.$sortKey.'" />';
											$markup.='</span>';

										$markup.='</div>';

										/*section style table*/
										if($templateKey=='emails'){
											$markup.='<div id="'.$this->pluginSlug.'-'.$ident.'-section-table-styles-'.$msgKey.'" class="'.$this->pluginSlug.'-'.$ident.'-section-table-styles '.$this->pluginSlug.'-'.$ident.'-'.$templateKey.'-style-element-'.$msgKey.'">';
												$markup.='<div class="'.$this->pluginSlug.'-'.$ident.'-section-table-styles-label">';
												$markup.=''.$tplVars['all']['parts'][$sortKey].'';
												$markup.='</div>';
												$markup.=''.__('Table','wppizza-locale').'<br />';
												$markup.='<textarea  class="'.$this->pluginSlug.'-'.$ident.'-section-style '.$this->pluginSlug.'-'.$ident.'-section-style-table" name="'.$this->pluginSlug.'['.$arrayIdent.']['.$templateKey.']['.$msgKey.'][style]['.$sortKey.'][table]">'.$tplVars['style'][$sortKey]['table'].'</textarea>';
											$markup.='</div>';
										}


										/*include header on off**/
										$markup.='<div class="'.$this->pluginSlug.'-'.$ident.'-'.$templateKey.'-section-header">';
											$markup.='<span id="'.$this->pluginSlug.'-'.$ident.'-section-th-'.$msgKey.'" class="'.$this->pluginSlug.'-'.$ident.'-section-th '.$this->pluginSlug.'-'.$ident.'-'.$templateKey.'-value-element-'.$msgKey.'">';
												$markup.='<input id="'.$this->pluginSlug.'-'.$ident.'-label-'.$msgKey.'-'.$sortKey.'" class="'.$this->pluginSlug.'-'.$ident.'-label" name="'.$this->pluginSlug.'['.$arrayIdent.']['.$templateKey.']['.$msgKey.'][parts_label]['.$sortKey.']" type="checkbox" '.checked(!empty($tplVars['parts_label'][$sortKey]),true,false).' value="1" />';//'.checked($sortVal['parts_label'],true,false).'
												/*label for order is made up of 3 lables*/
												if($sortKey=='order'){
													$orderlbl=$this->pluginOptions['localization']['templates_label_order_left']['lbl'].' | '.$this->pluginOptions['localization']['templates_label_order_center']['lbl'].' | '.$this->pluginOptions['localization']['templates_label_order_right']['lbl'];
													$markup.=''.sprintf( __( 'Show "%1$s"', 'wppizza-locale' ), $orderlbl ).'';
												}else{
													$markup.=''.sprintf( __( 'Show "%1$s" label', 'wppizza-locale' ), $this->pluginOptions['localization']['templates_label_'.$sortKey.'']['lbl'] ).'';
												}
											$markup.='</span>';

											/*css section (i.e  label/headers)*/
											if($templateKey=='emails'){
												$markup.='<span id="'.$this->pluginSlug.'-'.$ident.'-section-th-styles-'.$msgKey.'" class="'.$this->pluginSlug.'-'.$ident.'-section-th-styles '.$this->pluginSlug.'-'.$ident.'-'.$templateKey.'-style-element-'.$msgKey.'">';
													$markup.='<div>';
														$markup.=''.__('Header/Label','wppizza-locale').'<br />';
														$markup.='<textarea  class="'.$this->pluginSlug.'-'.$ident.'-section-style '.$this->pluginSlug.'-'.$ident.'-section-style-th" name="'.$this->pluginSlug.'['.$arrayIdent.']['.$templateKey.']['.$msgKey.'][style]['.$sortKey.'][th]">'.$tplVars['style'][$sortKey]['th'].'</textarea>';
													$markup.='</div>';


												if($sortKey=='order'){
													$markup.='<div>';
														$markup.=''.__('Blogname','wppizza-locale').'<br />';
														$markup.='<textarea  class="'.$this->pluginSlug.'-'.$ident.'-parts-style '.$this->pluginSlug.'-'.$ident.'-parts-style-blogname" name="'.$this->pluginSlug.'['.$arrayIdent.']['.$templateKey.']['.$msgKey.'][style]['.$sortKey.'][td-blogname]">'.$tplVars['style'][$sortKey]['td-blogname'].'</textarea>';
													$markup.='</div>';


													$markup.='<div>';
														$markup.=''.__('Category','wppizza-locale').'<br />';
														$markup.='<textarea  class="'.$this->pluginSlug.'-'.$ident.'-parts-style '.$this->pluginSlug.'-'.$ident.'-parts-style-catname" name="'.$this->pluginSlug.'['.$arrayIdent.']['.$templateKey.']['.$msgKey.'][style]['.$sortKey.'][td-catname]">'.$tplVars['style'][$sortKey]['td-catname'].'</textarea>';
													$markup.='</div>';
												}

												$markup.='</span>';
											}

										$markup.='</div>';

										/*css settings whole part left/right/ctr for all td's in part**/
										if($templateKey=='emails'){
										$markup.='<div id="'.$this->pluginSlug.'-'.$ident.'-part-styles-common-'.$msgKey.'" class="'.$this->pluginSlug.'-'.$ident.'-part-styles-common '.$this->pluginSlug.'-'.$ident.'-'.$templateKey.'-style-element-'.$msgKey.'">';
											$markup.='<span>';

												/**tds*/
												/**site vars only have one td**/
												if($sortKey=='site'){
													$markup.=''.__('Column All','wppizza-locale').'<br />';
													$markup.='<textarea  class="'.$this->pluginSlug.'-'.$ident.'-parts-style '.$this->pluginSlug.'-'.$ident.'-parts-style-ctr" name="'.$this->pluginSlug.'['.$arrayIdent.']['.$templateKey.']['.$msgKey.'][style]['.$sortKey.'][td-ctr]">'.$tplVars['style'][$sortKey]['td-ctr'].'</textarea>';
												}else{
													$markup.='<div>';
														$markup.=''.__('Left Column All','wppizza-locale').'<br />';
														$markup.='<textarea  class="'.$this->pluginSlug.'-'.$ident.'-parts-style '.$this->pluginSlug.'-'.$ident.'-parts-style-lft" name="'.$this->pluginSlug.'['.$arrayIdent.']['.$templateKey.']['.$msgKey.'][style]['.$sortKey.'][td-lft]">'.$tplVars['style'][$sortKey]['td-lft'].'</textarea>';
													$markup.='</div>';
													/*order vars have 3 columns*/
													if($sortKey=='order'){
													$markup.='<div>';
														$markup.=''.__('Center Column All','wppizza-locale').'<br />';
														$markup.='<textarea  class="'.$this->pluginSlug.'-'.$ident.'-parts-style '.$this->pluginSlug.'-'.$ident.'-parts-style-ctr" name="'.$this->pluginSlug.'['.$arrayIdent.']['.$templateKey.']['.$msgKey.'][style]['.$sortKey.'][td-ctr]">'.$tplVars['style'][$sortKey]['td-ctr'].'</textarea>';
													$markup.='</div>';
													}
													$markup.='<div>';
														$markup.=''.__('Right Column All','wppizza-locale').'<br />';
														$markup.='<textarea  class="'.$this->pluginSlug.'-'.$ident.'-parts-style '.$this->pluginSlug.'-'.$ident.'-parts-style-rgt" name="'.$this->pluginSlug.'['.$arrayIdent.']['.$templateKey.']['.$msgKey.'][style]['.$sortKey.'][td-rgt]">'.$tplVars['style'][$sortKey]['td-rgt'].'</textarea>';
													$markup.='</div>';
												}
											$markup.='</span>';
										$markup.='</div>';
										}


										/*variables on/off and sort**/
										$markup.='<div class="wppizza-'.$ident.'-sort-vars">';
											if(is_array($tplVars['all']['values'][$sortKey])){
											foreach($tplVars['all']['values'][$sortKey] as $partsKey=>$partsValues){
													/*sorting disabled for addinfo as that should alwasy be last**/
													$partsStyle='';
													$useDashicon='dashicons-editor-ol';
													$useDashiconTitle=__('drag and drop to sort','wppizza-locale');


													/*add class for style input in "order" variables part to be able to reshow if hidden by jQuery*/
													$addStyleClass='';
													if($sortKey=='order' && $templateKey=='emails'){
														$addStyleClass=' '.$this->pluginSlug.'-'.$ident.'-'.$templateKey.'-value-element-only-'.$msgKey.'';
													}

													$markup.='<div class="wppizza-'.$ident.'-sort-vars-'.$partsKey.' '.$addStyleClass.'" '.$partsStyle.'>';

														$markup.='<span class="wppizza-dashicons-small '.$useDashicon.'  wppizza-'.$ident.'-sort-var '.$this->pluginSlug.'-'.$ident.'-'.$templateKey.'-value-element-'.$msgKey.'" title="'.$useDashiconTitle.'">';
															$markup.='<input id="'.$this->pluginSlug.'-values-order.'.$templateKey.'-'.$msgKey.'.'.$sortKey.'.'.$partsKey.'" class="'.$this->pluginSlug.'-values-order" name="'.$this->pluginSlug.'['.$arrayIdent.']['.$templateKey.']['.$msgKey.'][sort]['.$sortKey.']['.$partsKey.']" type="hidden" value="'.$partsKey.'" />';
														$markup.='</span>';


														$markup.='<span id="wppizza-'.$ident.'-input-var-'.$msgKey.'-'.$sortKey.'-'.$partsKey.'" class="wppizza-'.$ident.'-input-var-'.$msgKey.'-'.$sortKey.' wppizza-'.$ident.'-input-var '.$this->pluginSlug.'-'.$ident.'-'.$templateKey.'-value-element-'.$msgKey.'" title="'.__('show in template','wppizza-locale').'">';
															$markup.='<input name="'.$this->pluginSlug.'['.$arrayIdent.']['.$templateKey.']['.$msgKey.'][values]['.$sortKey.']['.$partsKey.']" '.checked(!empty($tplVars['values'][$sortKey][$partsKey]),true,false).' type="checkbox" value="'.$partsKey.'" />';
														$markup.='</span>';

														$markup.='<span class="wppizza-'.$ident.'-label-var">';
															$markup.=''.$tplVars['all']['values'][$sortKey][$partsKey].'';
														$markup.='</span>';

														/*emails only*/
														if($templateKey=='emails'){
															/*skip for order items*/
															if($sortKey!='order'){
																$markup.='<span id="'.$this->pluginSlug.'-'.$ident.'-part-styles-'.$msgKey.'" class="'.$this->pluginSlug.'-'.$ident.'-part-styles '.$this->pluginSlug.'-'.$ident.'-'.$templateKey.'-style-element-'.$msgKey.'">';
																	$markup.='<textarea class="'.$this->pluginSlug.'-'.$ident.'-parts-style" name="'.$this->pluginSlug.'['.$arrayIdent.']['.$templateKey.']['.$msgKey.'][style]['.$sortKey.']['.$partsKey.'-tdall]" >'.$tplVars['style'][$sortKey][''.$partsKey.'-tdall'].'</textarea>';
																$markup.='</span>';
															}
														}
													$markup.='</div>';
											}}
										$markup.='</div>';

									$markup.='</td>';
								}
								$markup.='</tr>';
						$markup.='</tbody>';
					$markup.='</table>';


			return	$markup;
		}
/******************************************************************************************************
*
*
*
*	helpers / filters
*
*
*
******************************************************************************************************/


		/***********************************************************
			set consistant line length in plaintext templates
			between left and right parts.


			@lblval - array of left and right text to concat with spacing
			@return array
		***********************************************************/
		function wppizza_template_plaintext_linelength($lblval, $ident='', $spacer=' ', $maxchar = WPPIZZA_PLAINTEXT_MAX_LINE_LENGTH){

			/**filter max chars if you need to based on $ident for example**/
			$maxchar=apply_filters('wppizza_template_plaintext_linelength_maxchar', $maxchar, $ident, $spacer);
			/*************************************************
				input is a simple string (add info for example)
				if has linebreaks already trim each line,
				wordwrap lines as required,
				if no linebreaks, pad either side
			*************************************************/
			if(is_string($lblval)){

				/*for plaintext, decode entities and strip stuff**/
				$lblval=wppizza_email_decode_entities($lblval,WPPIZZA_CHARSET);
				$lblval=wp_kses($lblval,array());

				/***********************************************************************
					if this string has html for some reason,
					convert br's to PHP_EOLs and remove all other tags
					should laso take into account centering in some circumstances
					let's leave this for another day at the moment
				***********************************************************************/
				//$lblval=str_ireplace(array('<br>','<br/>','<br />'),PHP_EOL,$lblval);
				$splitByLine=explode(PHP_EOL,$lblval);

				/*has line breaks*/
				if(count($splitByLine)>1){
					$lblval='';
					foreach($splitByLine as $l=>$line){
						if($l>0){$lblval.=PHP_EOL;}
						$lblval.=wordwrap(trim($line), $maxchar, PHP_EOL , true);
					}
				}

				/*no line breaks, center*/
				if(count($splitByLine)<=1){

					/*get length*/
					$strLength=strlen(utf8_decode(($lblval)));
					/*paddcount -2 to have one space either side of str */
					$pad=floor(($maxchar-$strLength-2)/2);
					/*skip padding if too long to start off with**/
					if($pad<=0){
						return $lblval;
					}
					$strPadded='';
					/*str_pad wont work as it will miscount multibytes*/
					for($c=0;$c<$pad;$c++){
						$strPadded.=$spacer;
					}
					/****keep one space either side****/
					$strPadded.=' '.$lblval.' ';

					/*str_pad wont work as it will miscount multibytes*/
					for($c=0;$c<$pad;$c++){
						$strPadded.=$spacer;
					}
					$lblval=$strPadded;
				}
			}
			/********************************************
				input as array. pad as required
				adding spaces between label and value
				padding<0 , just add as new line
			********************************************/
			if(is_array($lblval)){
				/*
					Note:
					utf8_decode() converts characters that are not in ISO-8859-1 to '?',
					which, for the purpose of counting, is quite alright.
				*/
				/*
					PERHAPS TODO
					if isset($lblval[type]) && $lblval[type]=='textarea'
					to space properly for textareas/fields with linebreaks

				*/

				/*for plaintext, decode any entities first and strip tags**/
				$lblval['label']=wppizza_email_decode_entities($lblval['label'],WPPIZZA_CHARSET);
				$lblval['label']=wp_kses($lblval['label'],array());
				$lblval['value']=wppizza_email_decode_entities($lblval['value'],WPPIZZA_CHARSET);
				$lblval['value']=wp_kses($lblval['value'],array());


				/*length parts  label*/
				$utf8_decode_label=utf8_decode($lblval['label']);
				$strLeft=strlen($utf8_decode_label);
				/**
					this belongs into a different function that deals with email temmplates in template directory
					as this function here doesl with selected drag drop template and tags are already stripped

					leave here for now - as example - to be moved elsewhere later

					as we may still have some html in the string,
					for example when adding a <span>title</span> to the menu item name
					strip tags, get leftover length and substract from label length
					so spacing will be ok again
				**/
				//$stripped=strip_tags($utf8_decode_label);/*strip tags*/
				//$stripped_length=strlen($stripped);/*get length after strip tags*/
				//$strip_length_difference=$strLeft-$stripped_length;/*get difference between before and after stripping tags*/
				//if($strip_length_difference>0){/*if >0 substract fromset length*/
				//	$strLeft-=$strip_length_difference;
				//}


				/*length value*/
				$strRight=strlen(utf8_decode($lblval['value']));
				/*spaces required*/
				$strPad=$maxchar-$strLeft-$strRight;
				/*if no space available, use linebreak instead*/
				if($strPad>0){
					$lblval['label']=$lblval['label'];
					/*str_pad wont work as it will miscount multibytes*/
					for($c=0;$c<$strPad;$c++){
					$lblval['label'].=$spacer;
					}
				}else{
					$lblval['label'].=PHP_EOL;
				}
			}
			return $lblval;
		}
}}
?>