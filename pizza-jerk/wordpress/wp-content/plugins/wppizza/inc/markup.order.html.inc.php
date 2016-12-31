<?php
if ( ! defined( 'ABSPATH' ) ) exit;/*Exit if accessed directly*/
/**********************************************************************************************************************************************************************
*
*
*	WPPizza - Html Template for Orders
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
			$siteVars['parts_label'] = apply_filters('wppizza_filter_template_html_padstring', $siteVars['parts_label'], $partsKey);
			/***wrap in tr/th, omit style declarations on print **/
			$thStyle=($type=='print') ? '' : ' style="'.$template['style'][$partsKey]['th'].'"';
			$siteVars['parts_label'] ='<thead>'.PHP_EOL.'<tr>'.PHP_EOL.'<th '.$thStyle.'>'.PHP_EOL.''.$siteVars['parts_label'].''.PHP_EOL.'</th>'.PHP_EOL.'</tr>'.PHP_EOL.'</thead>'.PHP_EOL;

		}
		/***wrap in tbody****/
		$siteVars['tbody_open'] ='<tbody>'.PHP_EOL;

		foreach($template['values'][$partsKey] as $valKey){
			$siteVars[$valKey] =trim($siteDetails[$valKey]);
			if($siteVars[$valKey]!=''){
				/**allow filtering - unused in plugin**/
				$siteVars[$valKey] = apply_filters('wppizza_filter_template_html_sitedetails', $siteVars[$valKey], $valKey, $partsKey);
				/***wrap in tr/td**/
				if($type=='emails'){
					$siteVars[$valKey] ='<tr><td style="'.$template['style'][$partsKey]['td-ctr'].';'.$template['style'][$partsKey][$valKey.'-tdall'].'">'.$siteVars[$valKey].'</td></tr>'.PHP_EOL;
				}
				if($type=='print'){
					$siteVars[$valKey] ='<tr id="'.$valKey.'"><td>'.$siteVars[$valKey].'</td></tr>'.PHP_EOL;
				}
			}
		}
		/***wrap in tbody****/
		$siteVars['tbody_close'] ='</tbody>'.PHP_EOL;

		/**implode it all**/
		$messageSections[$partsKey]=implode(PHP_EOL, $siteVars);


		/***wrap in table**/
		if($type=='emails'){
			$messageSections[$partsKey]='<table style="width:100%;'.$template['style'][$partsKey]['table'].'">'.PHP_EOL.''.$messageSections[$partsKey].'</table>'.PHP_EOL.'';
		}
		if($type=='print'){
			$messageSections[$partsKey]='<table id="header">'.PHP_EOL.''.$messageSections[$partsKey].'</table>'.PHP_EOL.'';
		}
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
			$transactionVars['parts_label'] = apply_filters('wppizza_filter_template_html_padstring', $transactionVars['parts_label'], $partsKey);
			/***wrap in tr/th, omit style declarations on print **/
			$thStyle=($type=='print') ? '' : ' style="'.$template['style'][$partsKey]['th'].'"';
			$transactionVars['parts_label'] ='<thead>'.PHP_EOL.'<tr>'.PHP_EOL.'<th colspan="2" '.$thStyle.'>'.PHP_EOL.''.$transactionVars['parts_label'].''.PHP_EOL.'</th>'.PHP_EOL.'</tr>'.PHP_EOL.'</thead>'.PHP_EOL;

		}
		/***wrap in tbody****/
		$transactionVars['tbody_open'] ='<tbody>'.PHP_EOL;
		if($type=='emails'){
			$transactionVars['tbody_ul_open'] ='<tr><td><ul style="list-style-type:none;padding:0;margin:0">'.PHP_EOL;
		}

		foreach($template['values'][$partsKey] as $valKey){
			/*split into left and right and implode again, inserting a filter to possibly use*/
			$lblval['label'] =trim($orderDetails[$valKey]['label']);
			$lblval['value'] =trim($orderDetails[$valKey]['value']);

			/**used to add consistent linespacing/length**/
			$lblval = apply_filters('wppizza_filter_template_html_transaction_details', $lblval, 'transaction');

			/**date needs no label**/
			if($valKey=='order_date'){
				if($type=='emails'){
					$transactionVars[$valKey]='<li style="'.$template['style'][$partsKey][$valKey.'-tdall'].'">'.$lblval['value'].'</li>'.PHP_EOL;
				}
				if($type=='print'){
					$transactionVars[$valKey]='<tr id="'.$valKey.'"><td colspan="2">'.$lblval['value'].'</td></tr>'.PHP_EOL;
				}
			}else{
				if($type=='emails'){
					$transactionVars[$valKey]='<li><span style="'.$template['style'][$partsKey]['td-lft'].';'.$template['style'][$partsKey][$valKey.'-tdall'].'">'.implode('</span><span style="'.$template['style'][$partsKey]['td-rgt'].';'.$template['style'][$partsKey][$valKey.'-tdall'].'">',$lblval).'</span></li>'.PHP_EOL;
				}
				if($type=='print'){
					$transactionVars[$valKey]='<tr id="'.$valKey.'"><td>'.implode('</td><td>',$lblval).'</td></tr>'.PHP_EOL;
				}
			}

			/**allow filtering - unused in plugin**/
			$transactionVars[$valKey] = apply_filters('wppizza_filter_template_html_transactiondetails', $transactionVars[$valKey], $valKey, $partsKey);
		}
		if($type=='emails'){
			$transactionVars['tbody_ul_close'] ='</ul></td></tr>';
		}
		/***wrap in tbody****/
		$transactionVars['tbody_close'] ='</tbody>'.PHP_EOL;

		$messageSections[$partsKey]=implode(PHP_EOL, $transactionVars);
		/***wrap in table**/
		if($type=='emails'){
			$messageSections[$partsKey]='<table  style="width:100%;'.$template['style'][$partsKey]['table'].'">'.PHP_EOL.''.$messageSections[$partsKey].'</table>'.PHP_EOL.'';
		}
		if($type=='print'){
			$messageSections[$partsKey]='<table id="overview">'.PHP_EOL.''.$messageSections[$partsKey].'</table>'.PHP_EOL.'';
		}
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
			$customerVars['parts_label'] = apply_filters('wppizza_filter_template_html_padstring', $customerVars['parts_label'], $partsKey);
			/***wrap in tr/th, omit style declarations on print **/
			$thStyle=($type=='print') ? '' : ' style="'.$template['style'][$partsKey]['th'].'"';
			$customerVars['parts_label'] ='<thead>'.PHP_EOL.'<tr>'.PHP_EOL.'<th colspan="2" '.$thStyle.'>'.PHP_EOL.''.$customerVars['parts_label'].''.PHP_EOL.'</th>'.PHP_EOL.'</tr>'.PHP_EOL.'</thead>'.PHP_EOL;
		}

		/***wrap in tbody****/
		$customerVars['tbody_open'] ='<tbody>'.PHP_EOL;

		foreach($template['values'][$partsKey] as $valKey){
		if(!empty($customerDetails[$valKey])){
			/*split into left and right and implode again, inserting a filter to possibly use*/
			$lblval['label'] =trim($customerDetails[$valKey]['label']);
			$lblval['value'] =trim($customerDetails[$valKey]['value']);

			/**used to add consistent linespacing/length**/
			$lblval = apply_filters('wppizza_filter_template_html_customer_detail', $lblval, 'customer');

			/**use style declarations in emails, id's in print*/
			if($type=='emails'){
				$customerVars[$valKey]='<tr><td style="'.$template['style'][$partsKey]['td-lft'].';'.$template['style'][$partsKey][$valKey.'-tdall'].'">'.implode('</td><td style="'.$template['style'][$partsKey]['td-rgt'].';'.$template['style'][$partsKey][$valKey.'-tdall'].'">',$lblval).'</td></tr>'.PHP_EOL;
			}
			if($type=='print'){
				$customerVars[$valKey]='<tr id="'.$valKey.'"><td>'.implode('</td><td>',$lblval).'</td></tr>'.PHP_EOL;
			}

			/**allow filtering - unused in plugin**/
			$customerVars[$valKey] = apply_filters('wppizza_filter_template_html_customerdetails', $customerVars[$valKey], $valKey, $partsKey);
		}}
		/***wrap in tbody****/
		$customerVars['tbody_close'] ='</tbody>'.PHP_EOL;

		$messageSections[$partsKey]=implode(PHP_EOL, $customerVars);

		/***wrap in table**/
		if($type=='emails'){
			$messageSections[$partsKey]='<table style="width:100%;'.$template['style'][$partsKey]['table'].'">'.PHP_EOL.''.$messageSections[$partsKey].'</table>'.PHP_EOL.'';
		}
		if($type=='print'){
			$messageSections[$partsKey]='<table id="customer">'.PHP_EOL.''.$messageSections[$partsKey].'</table>'.PHP_EOL.'';
		}
	}

	/***********************************************************************
	*
	*	[items/order details - if enabled]
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

			/*label markup */
			$headerMarkup=array();
			/***wrap in tr/th, omit style declarations on print as added to <style> in header**/
			$thStyleLeft=($type=='print') ? '' : ' style="'.$template['style'][$partsKey]['td-lft'].';'.$template['style'][$partsKey]['th'].'"';
			$thStyleCenter=($type=='print') ? '' : ' style="'.$template['style'][$partsKey]['td-ctr'].';'.$template['style'][$partsKey]['th'].'"';
			$thStyleRight=($type=='print') ? '' : ' style="'.$template['style'][$partsKey]['td-rgt'].';'.$template['style'][$partsKey]['th'].'"';


			$headerMarkup['tableHeadOpen']='<thead><tr>';

				/**array of header columns to allow filtering*/
				$headerMarkupTh['left']='<th'.$thStyleLeft.'>'.$txt['templates_label_order_left'].'</th>';
				$headerMarkupTh['center']='<th'.$thStyleCenter.'>'.$txt['templates_label_order_center'].'</th>';
				$headerMarkupTh['right']='<th'.$thStyleRight.'>'.$txt['templates_label_order_right'].'</th>';//'.$orderDetails['currency']['value'].'

				/**filter header th if required*/
				$headerMarkupTh = apply_filters('wppizza_filter_template_item_header_markup', $headerMarkupTh, $txt, $type.'_template', $template_id);
				$headerMarkup['th'] = implode(PHP_EOL,$headerMarkupTh);


			$headerMarkup['tableHeadClose']='</tr></thead>';
			/*implode*/
			$cartItemMarkup['parts_label'] =implode(PHP_EOL,$headerMarkup);
		}

		/***wrap in tbody****/
		$cartItemMarkup['tbody_open'] ='<tbody>'.PHP_EOL;


		/*filter to allow sorting by category for example (or whatever else one can think of) **/
		$cartitems = apply_filters('wppizza_filter_print_order_items', $cartitems, 'template-html-order');


		foreach($cartitems as $itemKey=>$itemVal){

			/**allow filtering of item values*/
			$itemVal = apply_filters('wppizza_filter_template_item_variables', $itemVal, $itemKey , $type.'_template', $template_id);

			$cartItemMarkup[$itemKey]='';

			/**returns the category as <tr class="item-category"><td colspan="3">'.----this category name -----.'</td></tr>'**/
			$styleBlogname=false;
			$styleCatname=false;
			if($type=='emails'){
				$styleBlogname=$template['style'][$partsKey]['td-blogname'];
				$styleCatname=$template['style'][$partsKey]['td-catname'];
			}
			$cartItemMarkup[$itemKey] = apply_filters('wppizza_filter_print_order_single_item_category', $cartItemMarkup[$itemKey], $itemVal , 'tr', 'tr', $styleBlogname, $styleCatname);


			$thisItem=array();
			/**loop through individual to be shown vars as . skip addinfo to always be added to end */
			$i=0;
			foreach($itemVars as $itemVarsKey=>$enabled){

				/*always skip addinfo here and add underneath*/
				if($itemVarsKey!='addinfo'){
					$thisItem[$itemVarsKey]=$itemVal[$itemVarsKey];
					/**get first (quantity)*/
					if($i==0){
					$firstKey=$itemVarsKey;
					}
					/*get last key to define as value*/
					$lastKey=$itemVarsKey;

				}else{
					$addinfo=$itemVal['addinfo_html'];/**get html add info output*/
				}
			$i++;
			}


			/*split into label and value - whatever is last being value to allow formatting*/
			$firstValue=$thisItem[$firstKey];
			/*split into label and value - whatever is last being value to allow formatting*/
			$lastValue=$thisItem[$lastKey];
			/*remove first and last value from label to use as value*/
			unset($thisItem[$firstKey]);
			unset($thisItem[$lastKey]);

			$inline_style_td_left='';
			$inline_style_td_right='';
			/*inline styles for eamisl*/
            /*inline styles for emails*/
            if($type=='emails'){
				$inline_style_td_left='style="'.$template['style'][$partsKey]['td-lft'].'"';
				$inline_style_td_ctr='style="'.$template['style'][$partsKey]['td-ctr'].'"';
				$inline_style_td_right='style="'.$template['style'][$partsKey]['td-rgt'].'"';
            } 

			/*split into left and right and implode again, inserting a filter to possibly use*/
			$itemsLblVal=array();
			$itemsLblVal['td-lft'] ='<td '.$inline_style_td_left.'>'.trim($firstValue).'</td>';
			$itemsLblVal['center'] ='<td '.$inline_style_td_ctr.'><span>'.trim(implode('</span> <span>',$thisItem)).'</span></td>';
			$itemsLblVal['td-rgt'] ='<td '.$inline_style_td_right.'>'.trim($lastValue).'</td>';

			/**allow filtering of article parts to be added below*/
			$itemsLblVal = apply_filters('wppizza_filter_template_item_markup', $itemsLblVal, $itemVal, $itemKey , $type.'_template', $template_id);

			/*add individual items vars as tr's*/
			$addTrClass='';
			if($type=='print'){
				$addTrClass='class="item"';
			}

			$cartItemMarkup[$itemKey].='<tr '.$addTrClass.'>';
			/**set / add (perhaps filtered) parts setting style as appropriate**/
			foreach($itemsLblVal as $itemPartKey=>$itemPartVal){
				$cartItemMarkup[$itemKey].=''.$itemPartVal.'';
			}
			$cartItemMarkup[$itemKey].='</tr>'.PHP_EOL;

			/****add add info to end - if exits**/
			if(!empty($addinfo)){
				/**used to add consistent wordwrap**/
				$addinfo = apply_filters('wppizza_filter_template_html_cart_item_addinfo', $addinfo, 'addinfo');
				$cartItemMarkup[$itemKey].='<tr><td></td><td class="item-add-info" colspan="'.(count($itemsLblVal)-1).'">'.$addinfo.'</td></tr>'.PHP_EOL;
			}
		}
		/***wrap in tbody****/
		$cartItemMarkup['tbody_close'] ='</tbody>'.PHP_EOL;


		/*implode all items by EOL*/
		$messageSections[$partsKey]=implode(PHP_EOL, $cartItemMarkup);

		/***wrap in table**/
		if($type=='emails'){
			$messageSections[$partsKey]='<table style="width:100%;border-collapse:collapse;'.$template['style'][$partsKey]['table'].'">'.PHP_EOL.''.$messageSections[$partsKey].'</table>'.PHP_EOL.'';
		}
		if($type=='print'){
			$messageSections[$partsKey]='<table id="items">'.PHP_EOL.''.$messageSections[$partsKey].'</table>'.PHP_EOL.'';
		}
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
			$summaryVars['parts_label'] = apply_filters('wppizza_filter_template_html_padstring', $summaryVars['parts_label'], $partsKey);
			/***wrap in tr/th**/
			/***wrap in tr/th, omit style declarations on print as added to <style> in header**/
			$thStyle=($type=='print') ? '' : ' style="'.$template['style'][$partsKey]['th'].'"';
			$summaryVars['parts_label'] ='<thead>'.PHP_EOL.'<tr>'.PHP_EOL.'<th colspan="2" '.$thStyle.'>'.PHP_EOL.''.$summaryVars['parts_label'].''.PHP_EOL.'</th>'.PHP_EOL.'</tr>'.PHP_EOL.'</thead>'.PHP_EOL;
		}
		/***wrap in tbody****/
		$summaryVars['tbody_open'] ='<tbody>'.PHP_EOL;


		foreach($template['values'][$partsKey] as $valKey){
			if(!empty($orderSummary[$valKey]['label']) || !empty($orderSummary[$valKey]['value'])){//only omit totally empty ones

				/*split into left and right and implode again, inserting a filter to possibly use*/
				$lblval['label'] =trim($orderSummary[$valKey]['label']);
				$lblval['value'] =trim($orderSummary[$valKey]['value']);

				/**lets check if we should be using a colspan of 2 as only left or right value exists**/
				$colspanLabel=false;
				$colspanValue=false;
				if($lblval['label']=='' || $lblval['value']==''){
					if($lblval['label']!=''){
						$colspanLabel=true;
					}
					if($lblval['value']!=''){
						$colspanValue=true;
					}
				}

				/**used to add consistent linespacing/length**/
				$lblval = apply_filters('wppizza_filter_template_html_summary_detail', $lblval, 'summary');

				/**if only label or value are present, set colspan=2*/
				if($colspanLabel || $colspanValue){

					if($type=='emails'){
						if($colspanLabel){
							$summaryVars[$valKey]='<tr><td colspan="2" style="'.$template['style'][$partsKey]['td-lft'].';'.$template['style'][$partsKey][$valKey.'-tdall'].'">'.$lblval['label'].'</td></tr>'.PHP_EOL;
						}else{
							$summaryVars[$valKey]='<tr><td colspan="2" style="'.$template['style'][$partsKey]['td-rgt'].';'.$template['style'][$partsKey][$valKey.'-tdall'].'">'.$lblval['value'].'</td></tr>'.PHP_EOL;
						}
					}
					if($type=='print'){
						$summaryVars[$valKey]='<tr id="'.$valKey.'"><td>'.$lblval['label'].'</td><td>'.$lblval['value'].'</td></tr>'.PHP_EOL;
					}
				}else{
					if($type=='emails'){
						$summaryVars[$valKey]='<tr><td style="'.$template['style'][$partsKey]['td-lft'].';'.$template['style'][$partsKey][$valKey.'-tdall'].'">'.trim(implode('</td><td style="'.$template['style'][$partsKey]['td-rgt'].';'.$template['style'][$partsKey][$valKey.'-tdall'].'">',$lblval)).'</td></tr>'.PHP_EOL;
					}
					if($type=='print'){
						$summaryVars[$valKey]='<tr id="'.$valKey.'"><td>'.trim(implode('</td><td>',$lblval)).'</td></tr>'.PHP_EOL;
					}
				}

				/**allow filtering - unused in plugin**/
				$summaryVars[$valKey] = apply_filters('wppizza_filter_template_html_summary', $summaryVars[$valKey], $valKey, $partsKey);
			}

		}
		/***wrap in tbody****/
		$summaryVars['tbody_close'] ='</tbody>'.PHP_EOL;

		$messageSections[$partsKey]=implode(PHP_EOL, $summaryVars);


		/***wrap in table**/
		if($type=='emails'){
			$messageSections[$partsKey]='<table style="width:100%;'.$template['style'][$partsKey]['table'].'">'.PHP_EOL.''.$messageSections[$partsKey].'</table>'.PHP_EOL.'';
		}
		if($type=='print'){
			$messageSections[$partsKey]='<table id="summary">'.PHP_EOL.''.$messageSections[$partsKey].'</table>'.PHP_EOL.'';
		}
	}
/**************************************************************************************************************************
*
*
*
*	[lets put the sections together in the right order]
*
*
*
**************************************************************************************************************************/
	$templateMarkup=array();
	foreach($template['values'] as $partsKey => $vars){
		$templateMarkup[$partsKey]=$messageSections[$partsKey];
	}

	/***********************************************************************
	*
	*	[add footer text in emails after everything else]
	*
	***********************************************************************/
	if($type=='emails'){
	$partsKey='footer';
	if(!empty($this->pluginOptions['localization']['order_email_footer']['lbl'])){
		$footerVars=array();

		/*footer mssage*/
		$footerVars['order_email_footer'] =trim($this->pluginOptions['localization']['order_email_footer']['lbl']);
		$footerVars['order_email_footer'] = apply_filters('wppizza_filter_template_html_padstring', $footerVars['order_email_footer'], $partsKey);
		$footerVars['order_email_footer']='<tr id="order_email_footer"><td>'.$footerVars['order_email_footer'].'</td></tr>'.PHP_EOL;

		$templateMarkup[$partsKey]=PHP_EOL.implode(PHP_EOL, $footerVars);

		/***wrap in table**/
		$templateMarkup[$partsKey]='<table style="width:100%;text-align:center">'.$templateMarkup[$partsKey].'</table>';
	}}


/**************************************************************************************************************************
*
*
*
*	[allow filtering and return markup]
*
*
*
**************************************************************************************************************************/
	$templateMarkup = apply_filters('wppizza_filter_template_html_message_markup', $templateMarkup);
	$templateMarkup=trim(implode(PHP_EOL.PHP_EOL, $templateMarkup)).PHP_EOL;
	do_action('wppizza_template_html_message_markup', $templateMarkup, $template_id);
/**************************************************************************************************************************
*
*
*
*	[html elements wrapper to insert it into]
*
*
*
**************************************************************************************************************************/
	$html='';
	$html.='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'.PHP_EOL;
	$html.='<html xmlns="http://www.w3.org/1999/xhtml">'.PHP_EOL;
	$html.='<head>'.PHP_EOL;
		$html.='<title></title>'.PHP_EOL;
		$html.='<meta http-equiv="Content-Type" content="text/html;charset='.get_option('blog_charset').'" />'.PHP_EOL;
		if($type=='print'){
			$html.='<style>'.$template['style']['global']['body'].'</style>';
		}
	$html.='</head>'.PHP_EOL;
	if($type=='emails'){
		$html.='<body style="'.$template['style']['global']['body'].'">'.PHP_EOL;
	}
	if($type=='print'){
		$html.='<body>'.PHP_EOL;
	}
		/*only wrap for emails*/
		if($type=='emails'){
			$html.='<table style="border-collapse:collapse;'.$template['style']['global']['wrapper'].'">'.PHP_EOL;
				$html.='<tr>'.PHP_EOL;
					$html.='<td>'.PHP_EOL;
						$html.='<center>'.PHP_EOL;
							$html.='<table style="border-collapse:collapse;'.$template['style']['global']['table'].'">'.PHP_EOL;
								$html.='<tr>'.PHP_EOL;
									$html.='<td>'.PHP_EOL;
		}

		/**
			insert content into body
		**/
		$html.=$templateMarkup.PHP_EOL;
		/**
			close tags
		**/

		/*only wrap for emails*/
		if($type=='emails'){
										$html.='</td>'.PHP_EOL;
								$html.='</tr>'.PHP_EOL;
							$html.='</table>'.PHP_EOL;
						$html.='</center>'.PHP_EOL;
					$html.='</td>'.PHP_EOL;
				$html.='</tr>'.PHP_EOL;
			$html.='</table>'.PHP_EOL;
		}
	$html.='</body>'.PHP_EOL;
	$html.='</html>'.PHP_EOL;
?>