<?php
if ( ! defined( 'ABSPATH' ) ) exit;/*Exit if accessed directly*/
/**********************************************************************************************************************************************************************
*
*
*	WPPizza - Plaintext Template for Orders
*
*	[constructing sections and part separately, to allow easier filtering and sorting of sections and parts]
*
*
**********************************************************************************************************************************************************************/

	$messageSections=array();
	/***********************************************************************
	*
	*	[site details (if enabled) - no labels ]
	*
	***********************************************************************/
	$partsKey='site';
	if(!empty($template['values'][$partsKey])){
		$siteVars=array();

		/*label ?*/
		if(!empty($template['parts_label'][$partsKey])){
			$siteVars['parts_label'] =trim($this->pluginOptions['localization']['templates_label_'.$partsKey.'']['lbl']);
			$siteVars['parts_label'] = apply_filters('wppizza_filter_template_plaintext_padstring', $siteVars['parts_label'], $partsKey);
		}
		foreach($template['values'][$partsKey] as $valKey){
			$siteVars[$valKey] =trim($siteDetails[$valKey]);
			/*center these too*/
			$siteVars[$valKey] = apply_filters('wppizza_filter_template_plaintext_padstring', $siteVars[$valKey], $valKey);
			/**allow further filtering - unused in plugin**/
			$siteVars[$valKey] = apply_filters('wppizza_filter_template_plaintext_sitedetails', $siteVars[$valKey], $valKey);
		}
		$messageSections[$partsKey]=implode(PHP_EOL, $siteVars);
	}
	/***********************************************************************
	*
	*	[db/transaction field details - if enabled]
	*
	***********************************************************************/
	$partsKey='ordervars';
	if(!empty($template['values'][$partsKey])){
		$transactionVars=array();

		/*label ?*/
		if(!empty($template['parts_label'][$partsKey])){
			$transactionVars['parts_label'] =trim($this->pluginOptions['localization']['templates_label_'.$partsKey.'']['lbl']);
			$transactionVars['parts_label'] = apply_filters('wppizza_filter_template_plaintext_padstring', $transactionVars['parts_label'], $partsKey);
		}

		foreach($template['values'][$partsKey] as $valKey){
			/*split into left and right and implode again, inserting a filter to possibly use*/
			$lblval['label'] =trim($orderDetails[$valKey]['label']);
			$lblval['value'] =trim($orderDetails[$valKey]['value']);

			/**used to add consistent linespacing/length**/
			$lblval = apply_filters('wppizza_filter_template_plaintext_transaction_details', $lblval, 'transaction');

			$transactionVars[$valKey]=implode('',$lblval);
			/**allow filtering - unused in plugin**/
			$transactionVars[$valKey] = apply_filters('wppizza_filter_template_plaintext_transactiondetails', $transactionVars[$valKey], $valKey);
		}
		$messageSections[$partsKey]=implode(PHP_EOL, $transactionVars);
	}

	/***********************************************************************
	*
	*	[customer details - if enabled]
	*
	***********************************************************************/
	$partsKey='customer';
	if(!empty($template['values'][$partsKey])){
		$customerVars=array();
		/*label ?*/
		if(!empty($template['parts_label'][$partsKey])){
			$customerVars['parts_label'] =trim($this->pluginOptions['localization']['templates_label_'.$partsKey.'']['lbl']);
			$customerVars['parts_label'] = apply_filters('wppizza_filter_template_plaintext_padstring', $customerVars['parts_label'], $partsKey);
		}
		foreach($template['values'][$partsKey] as $valKey){
		if(!empty($customerDetails[$valKey])){
			/*split into left and right and implode again, inserting a filter to possibly use*/
			$lblval['label'] =trim($customerDetails[$valKey]['label']);
			$lblval['value'] =trim($customerDetails[$valKey]['value']);

			/**used to add consistent linespacing/length**/
			$lblval = apply_filters('wppizza_filter_template_plaintext_customer_detail', $lblval, 'customer');

			$customerVars[$valKey]=trim(implode('',$lblval));

			/**allow filtering - unused in plugin**/
			$customerVars[$valKey] = apply_filters('wppizza_filter_template_plaintext_customerdetails', $customerVars[$valKey], $valKey);
		}}
		$messageSections[$partsKey]=implode(PHP_EOL, $customerVars);
	}

	/***********************************************************************
	*
	*	[items/order details - if enabled. with header]
	*
	***********************************************************************/
	$partsKey='order';
	if(!empty($template['values'][$partsKey])){

		/*get enabled , to be shown vars**/
		$itemVars=array();
		foreach($template['values'][$partsKey] as $valKey){
			$itemVars[$valKey] =true;
		}

		/**create markup for all items looping through each and using/adding eneabled vars**/
		$cartItemMarkup=array();
		/*label ?*/
		if(!empty($template['parts_label'][$partsKey])){

				$headerMarkup['left']=''.$txt['templates_label_order_left'].'';
				$headerMarkup['center']=''.$txt['templates_label_order_center'].'';
				$headerMarkup['right']=''.$txt['templates_label_order_right'].'';

				/**filter header info if required*/
				$headerMarkup = apply_filters('wppizza_filter_template_item_header_plaintext_markup', $headerMarkup, $txt, $type.'_template', $template_id, true);

				/**concat into first and last to enable spacing between the two*/
				$hmIterator=1;
				$hmCount=count($headerMarkup);
				$hmLeft=array();
				$hmRight=array();
				foreach($headerMarkup as $hmKey=>$hmVal){
					if($hmIterator<$hmCount){
						$hmLeft[$hmKey]=$hmVal;
					}else{
						$hmRight[$hmKey]=$hmVal;
					}
				$hmIterator++;
				}
				$lblval['label']=implode(' ',$hmLeft).' ';
				$lblval['value']=' '.implode(' ',$hmRight);


				/**used to add consistent linespacing/length**/
				$spacer='-';/**space out with hyphen here*/
				$lblval = apply_filters('wppizza_filter_template_plaintext_cart_item_header', $lblval, $partsKey, $spacer );
				/*implode individual items vars */
				$cartItemMarkup['parts_label']=trim(implode('', $lblval));
		}


		/***allow filtering of items (sort, add categories and whatnot)****/
		/*filter to allow sorting by category for example (or whatever else one can think of) **/
		$cartitems = apply_filters('wppizza_filter_print_order_items', $cartitems, 'template-plaintext-order');
		foreach($cartitems as $itemKey=>$itemVal){


			/**allow filtering of item values*/
			$itemVal = apply_filters('wppizza_filter_template_item_plaintext_variables', $itemVal, $itemKey , $type.'_template', $template_id, true);

			$cartItemMarkup[$itemKey]='';

			/***allow action per item - probably to use in conjunction with filter above****/
			/**returns the category as ['.----this category name -----.']'. PHP_EOL'**/
			$cartItemMarkup[$itemKey] = apply_filters('wppizza_filter_print_order_single_item_category', $cartItemMarkup[$itemKey], $itemVal, array('[',']',PHP_EOL), array(PHP_EOL,':',PHP_EOL.PHP_EOL) );

			$thisItem=array();
			/**loop through individual to be shown vars as . skip addinfo to always be added to end */
			foreach($itemVars as $itemVarsKey=>$enabled){
				/*always skip addinfo here and add underneath*/
				if($itemVarsKey!='addinfo'){
					$thisItem[$itemVarsKey]=$itemVal[$itemVarsKey];
					/*get last key to define as value*/
					$lastKey=$itemVarsKey;
				}else{
					$addinfo=$itemVal['addinfo_plaintext'];
				}
			}

			$thisItem = apply_filters('wppizza_filter_template_item_plaintext_markup', $thisItem, $itemVal, $itemKey, $type.'_template', $template_id, true);


			/*split into label and value - whatever is last being value to allow formatting*/
			$lastValue=$thisItem[$lastKey];
			/*remove last value from label to use as value*/
			unset($thisItem[$lastKey]);

			/*split into left and right and implode again, inserting a filter to possibly use*/
			$lblval['label'] =trim(implode(' ',$thisItem));
			$lblval['value'] =trim($lastValue);


			/**allow filtering of article parts to be added below*/

			/**used to add consistent linespacing/length**/
			$lblval = apply_filters('wppizza_filter_template_plaintext_cart_item', $lblval, 'cart');

			/*implode individual items vars */
			$cartItemMarkup[$itemKey].=trim(implode('', $lblval));

			/****add add info to end - if exits**/
			if(!empty($addinfo)){
				/**used to add consistent wordwrap**/
				$addinfo = apply_filters('wppizza_filter_template_plaintext_cart_item_addinfo', $addinfo, 'addinfo');
				$cartItemMarkup[$itemKey].=PHP_EOL.$addinfo;
			}
			/**add spacer after every item**/
			$cartItemMarkup[$itemKey].=PHP_EOL;

		}

		/*implode all items by EOL*/
		$messageSections[$partsKey]=implode(PHP_EOL, $cartItemMarkup);
	}

	/***********************************************************************
	*
	*	[summary details - if enabled]
	*
	***********************************************************************/
	$partsKey='summary';
	if(!empty($template['values'][$partsKey])){
		$summaryVars=array();

		/*label ?*/
		if(!empty($template['parts_label'][$partsKey])){
			$summaryVars['parts_label'] =trim($this->pluginOptions['localization']['templates_label_'.$partsKey.'']['lbl']);
			$summaryVars['parts_label'] = apply_filters('wppizza_filter_template_plaintext_padstring', $summaryVars['parts_label'], $partsKey);
		}

		foreach($template['values'][$partsKey] as $valKey){
			if(!empty($orderSummary[$valKey]['label']) || !empty($orderSummary[$valKey]['value'])){//only omit totally empty ones

				/*split into left and right and implode again, inserting a filter to possibly use*/
				$lblval['label'] =trim($orderSummary[$valKey]['label']);
				$lblval['value'] =trim($orderSummary[$valKey]['value']);

				/**used to add consistent linespacing/length**/
				$lblval = apply_filters('wppizza_filter_template_plaintext_summary_detail', $lblval, 'summary');

				$summaryVars[$valKey]=trim(implode('',$lblval));
				/**for pickup/delicery note, add PHP_EOL**/
				if($valKey=='self_pickup' || $valKey=='delivery_note' ){
				$summaryVars[$valKey]=PHP_EOL.$summaryVars[$valKey].PHP_EOL;
				}

				/**allow filtering - unused in plugin**/
				$summaryVars[$valKey] = apply_filters('wppizza_filter_template_plaintext_summary', $summaryVars[$valKey], $valKey);
			}

		}
		$messageSections[$partsKey]=implode(PHP_EOL, $summaryVars);
	}
/**************************************************************************************************************************
*
*
*
*	[lets put the sections together in the chosen/right order]
*
*
*
**************************************************************************************************************************/
	$plaintext=array();
	foreach($template['values'] as $partsKey => $vars){
		$plaintext[$partsKey]=$messageSections[$partsKey];
	}

	/***********************************************************************
	*
	*	[add footer text after everything else - emails only]
	*
	***********************************************************************/
	if($type=='emails'){
		$partsKey='footer';
		if(!empty($this->pluginOptions['localization']['order_email_footer']['lbl'])){
			$footerVars=array();

			/*footer mssage*/
			$footerVars['order_email_footer'] =trim($this->pluginOptions['localization']['order_email_footer']['lbl']);
			$footerVars['order_email_footer'] = apply_filters('wppizza_filter_template_plaintext_padstring', $footerVars['order_email_footer'], $partsKey);
			/*add another linebreak before footer*/
			$plaintext[$partsKey]=PHP_EOL.implode(PHP_EOL, $footerVars);
		}
	}

/**************************************************************************************************************************
*
*
*
*	[allow filtering and return markup as well as individual sections]
*
*
*
**************************************************************************************************************************/
	$plaintext_sections = $plaintext;
	$plaintext = apply_filters('wppizza_filter_template_plaintext_message_markup', $plaintext);
	$plaintext=PHP_EOL.PHP_EOL.(implode(PHP_EOL.PHP_EOL, $plaintext)).PHP_EOL.PHP_EOL;
	do_action('wppizza_template_plaintext_message_markup', $plaintext, $template_id);
?>