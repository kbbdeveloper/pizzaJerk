<?php
if ( ! defined( 'ABSPATH' ) ) exit;/*Exit if accessed directly*/
/**********************************************************************************************************************************************************************
*
*
*
*	WPPizza - Print Order Template
*	template used when printing order from admin order history screen
*
*	WARNING: ALTHOUGH YOU COULD MOVE THS TEMPLATE TO YOUR THEME DIRECTORY AND EDIT IT THERE TO NOT LOOSE YOUR CHANGES ON PLUGIN UPDATES
*	YOU ARE STRONGLY ADVISED TO USE THE FILTERS PROVIDED INSTEAD, AS THIS FILE MAY CHANGE AT ANY TIME
*
*
*	for filter usage refer to the examples in each section.
*	if you think there is something you cannot do with the filters, contact me at dev[at]wp-pizza.com. I'd happily add some more if needed.
*
*	Note: To support as many printers as possible it is also deliberately using tables as opposed to divs (for now anyway)
*
*
**********************************************************************************************************************************************************************/
	/*************************
		misellaneous variables
		[not really in use yet]
	*************************/
	$vars['title']='';
	/*filter if required*/
	$vars = apply_filters('wppizza_filter_print_order_variables', $vars);
	/*#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#*
		to add your own title ( might not make too much sense when printing though):
		in your theme's functions.php:
		add_filter('wppizza_filter_print_order_variables','myprefix_add_title');
		function myprefix_add_title($vars){
			$vars['title']='my title';
			return $vars;
		}
	*#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#*/
	/**********************************************************
		css
	**********************************************************/
	/*globals*/
	$style['global']='html,body,table,tbody,tr,td,th,span{margin:0;padding:0;text-align:left;}';
	$style['global_font']='html,body,table,tbody,tr,td,th,span{font-size:12px;font-family:Arial, Verdana, Helvetica, sans-serif}';
	$style['table']='table{width:100%;margin:0 0 10px 0;}';
	$style['th']='th{padding:5px;}';
	$style['td']='td{padding:0 5px;vertical-align:top}';
	/*header*/
	$style['header']='#header{margin:0}';
	$style['header_td']='#header #head td{font-size:200%;text-align:center;}';
	$style['address']='#header #address td{white-space:nowrap;font-size:130%;text-align:center;padding-bottom:5px;}';
	/*multisite*/
	$style['multisite']='#multisite{margin:0}';
	$style['multisite_td']='#multisite tbody>tr>td{text-align:center}';
	/*overview*/
	$style['overview_th']='#overview{margin:0}';
	$style['overview_th']='#overview th{border-top:1px solid;border-bottom:1px solid;font-size:120%;text-align:center}';
	$style['overview_blogname']='#blogname {font-size:80%;font-weight:normal}';
	$style['overview_td']='#overview tbody>tr>td{width:50%;white-space:nowrap;}';
	$style['overview_td1']='#overview tbody>tr>td:first-child{text-align:right}';
	$style['overview_td2']='#overview tbody>tr>td:last-child{text-align:left}';

	$style['overview_order_id']='#overview #order_id td{font-size:180%}';
	$style['payment_due']='#overview #payment_due td{font-size:180%}';
	$style['pickup_delivery']='#overview #pickup_delivery td{font-size:180%;text-align:center}';

	/*customer*/
	$style['customer_th']='#customer th{border-top:1px solid;border-bottom:1px solid;white-space:nowrap;font-size:120%;text-align:center}';
	/*items*/
	$style['items_th']='#items th{border-top:1px solid;border-bottom:1px solid;white-space:nowrap;}';
	$style['items_th_widths']='#items th:first-child,#items th:last-child{width:20px}';
	/*blogname (if printed)*/
	$style['item_blogname']='#items .wppizza-item-blogname td{text-decoration:underline;font-size:120%;text-align:center;padding:7px 0}';
	/*categories (if printed)*/
	$style['item_category']='#items .item-category td{padding:5px 2px 2px 2px; border-bottom:1px dashed }';
	$style['items_tds']='#items .item td{padding-top:5px;}';
	$style['items_fontsize']='#items .item td{font-size:100%}';
	$style['items_td_1']='#items .item td:first-child{text-align:center}';
	$style['items_td_2']='#items .item td:last-child{text-align:right}';
	$style['items_size']='#items .size{}';
	$style['items_divider_hr']='#items tbody > tr.divider > td > hr {border:none;border-top:1px dotted #AAAAAA;}';
	/*summary*/
	$style['summary']='#summary {border-top:1px solid;border-bottom:1px solid;}';
	$style['summary_td']='#summary tbody > tr > td{text-align:right}';
	$style['summary_td_last']='#summary tbody > tr > td:last-child{width:100px}';

	/*filter css if required*/
	$style = apply_filters('wppizza_filter_print_order_css', $style);
	/*implode css for output */
	$style = implode('', $style);

	/*#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#*
		to add your own or change existing css:
		in your theme's functions.php:

		add_filter('wppizza_filter_print_order_css','myprefix_add_css');
		function myprefix_add_css($style){

			// *** to change overall font/fontsize (default being font-size:12px;font-family:Arial, Verdana, Helvetica, sans-serif)***  //
			$style['global_font']='html,body,table,tbody,tr,td,th,span{font-size:10px;font-family: Verdana, Helvetica, sans-serif}';

			// *** to change item font size (default being 100%)***  //
			$style['items_fontsize']='#items .item td{font-size:120%}';

			// *** to remove exiting ['table'] style for instance ***  //
			unset($style['table']);

			// *** to change exiting  ['table'] style for instance ***  //
			$style['table']='table{// use your own css declaration //}';

			// *** to add new declaraion***  //
			$style['custom']='// add some custom css declaration //';

			// *** to remove ALL css *** //
			$style=array();

			return $style;
		}
	*#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#*/
?>
<?php
/***********************************************************************************************************
*
*
*	[start output ->  doctype/html/title/styles/body etc]
*
*
************************************************************************************************************/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title><?php echo $vars['title'] ?></title>
		<meta http-equiv="Content-Type" content="text/html;charset=<?php echo get_option('blog_charset'); ?>" />
		<style type="text/css"><?php echo $style ?></style>
	</head>
<body>
<?php
/**********************************************************************************************************************
*
*
*
*	[header: restaurant name and address for example]
*
*
*
**********************************************************************************************************************/
	/***********************
	*
	*	[create output header]
	*
	***********************/
	$hTable['tableOpen']='<table id="header">';

		/*header*/
		$hTable['tableHeader']='';

		/*footer*/
		$hTable['tableFooter']='';

		/*body*/
		$hTable['tableBodyOpen']='<tbody>';
			$hTable['header']='<tr id="head"><td>'.$txt['header_order_print_header'].'</td></tr>';
			$hTable['address']='<tr id="address"><td>'.$txt['header_order_print_shop_address'].'</td></tr>';
		$hTable['tableBodyClose']='</tbody>';

	$hTable['tableClose']='</table>';
	/**************************
		allow filtering and
		implode for output
	***************************/
	$hTable = apply_filters('wppizza_filter_print_order_header_output', $hTable, $txt, $order);
	$hTable = implode(PHP_EOL, array_filter($hTable));

	/***********************
	*
	*	[add to output]
	*
	***********************/
	$output['header']=$hTable;

	/*#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#*
		to add, amend or delete header elements (examples) :
		in your theme's functions.php:
		add_filter('wppizza_filter_print_order_header_output','myprefix_amend_header_elements');
		function myprefix_amend_header_elements($elements){

			// to add something to footer
			$elements['tableFooter']='<tfoot><tr><td colspan="2">something</td></tr></tfoot>';

			// to remove address for example
			unset($elements['address']);


			return $elements;

		}
	*#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#*/

?>
<?php
/**********************************************************************************************************************
*
*
*
*	[multisite only]
*	if enabled, print name of site WHERE ORDER WAS MADE if different from site displayed in header above
*
*
*
**********************************************************************************************************************/
	if(is_multisite() && is_array($multiSite) && count($multiSite)>0){
		/***********************
		*
		*	[create output multisite info]
		*
		***********************/
		$mTable['tableOpen']='<table id="multisite">';

		/*header*/
		$mTable['tableHeader']='';

		/*footer*/
		$mTable['tableFooter']='';

		/*body*/
		$mTable['tableBodyOpen']='<tbody>';
			/*currently return site name [header_order_print_header](as set in localization of site) if different from the one printed above*/
			/*override with add_filter('wppizza_filter_order_details_multisite_print_order','my_function',10,4); as required**/
			foreach($multiSite as $key=>$item){
				$mTable[$key]='<tr id="'.$key.'"><td>'.$item.'</td></tr>';
			}
		$mTable['tableBodyClose']='</tbody>';

		$mTable['tableClose']='</table>';

		/**************************
			allow filtering and
			implode for output
		***************************/
		$mTable = apply_filters('wppizza_filter_print_order_multisite_output', $mTable, $order);
		$mTable = implode(PHP_EOL,$mTable);
		/***********************
		*
		*	[add to output]
		*
		***********************/
		$output['multisite']=$mTable;
	}
?>
<?php
/**********************************************************************************************************************
*
*
*
*	[overview of order details: date, transactionId , gateway used etc etc ]
*
*
*
**********************************************************************************************************************/
	/*********************************
	*
	*	[create output overview]
	*
	*********************************/
	$oTable['tableOpen']='<table id="overview">';

		/*header*/
		$oTable['tableHeader']='<thead><tr><th colspan="2">';
		$oTable['tableHeader'].=''.$orderDetails['order_date']['value'].'';
		$oTable['tableHeader'].='</th></tr></thead>';


		/*footer*/
		$oTable['tableFooter']='';

		/*body*/
		$oTable['tableBodyOpen']='<tbody>';

			$oTable['order_id']='<tr id="order_id"><td>'.$orderDetails['order_id']['label'].'</td><td>'.$orderDetails['order_id']['value'].'</td></tr>';
			$oTable['payment_due']='<tr id="payment_due"><td>'.$orderDetails['payment_due']['label'].'</td><td>'.$orderDetails['payment_due']['value'].'</td></tr>';
			$oTable['pickup_delivery']='<tr id="pickup_delivery"><td colspan="2">'.$orderDetails['pickup_delivery']['value'].'</td></tr>';

			$oTable['payment_type']='<tr id="payment_type"><td>'.$orderDetails['payment_type']['label'].'</td><td>'.$orderDetails['payment_type']['value'].'</td></tr>';
			$oTable['payment_method']='<tr id="payment_method"><td>'.$orderDetails['payment_method']['label'].'</td><td>'.$orderDetails['payment_method']['value'].'</td></tr>';
			$oTable['transaction_id']='<tr id="transaction_id"><td>'.$orderDetails['transaction_id']['label'].'</td><td>'.$orderDetails['transaction_id']['value'].'</td></tr>';

		$oTable['tableBodyClose']='</tbody>';

	$oTable['tableClose']='</table>';

	/**************************
		allow filtering and
		implode for output
	***************************/
	$oTable = apply_filters('wppizza_filter_print_order_overview_output', $oTable, $order);
	$oTable = implode(PHP_EOL,$oTable);


	/***********************
	*
	*	[add to output]
	*
	***********************/
	$output['overview']=$oTable;

	/*#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#*
		to add, amend or delete  different elements (examples) :
		in your theme's functions.php:
		add_filter('wppizza_filter_print_order_overview_output','myprefix_amend_overview_elements');
		function myprefix_amend_overview_elements($elements){

			// to add something to footer
			$elements['tableFooter']='<tfoot><tr><td colspan="2">something</td></tr></tfoot>';

			// to remove table header for example
			unset($elements['tableHeader']);

			// to remove transaction id line
			unset($elements['transaction_id']);


			// to add something after transaction_id
			$elements['transaction_id'].='<tr><td>something left</td><td>something right</td></tr>';

			// to move transaction_id before payment_type (.ie after delivery)
			$tid=$elements['transaction_id'];//store var
			unset($elements['transaction_id']);//remove original
			$elements['pickup_delivery'].=$tid;//append after delivery



			return $elements;

		}
	*#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#*/
?>
<?php
/**********************************************************************************************************************
*
*
*
*	[customer details: whatever fields where enabled on order page]
*
*
*
**********************************************************************************************************************/
	/***********************
	*
	*	[customer details]
	*
	***********************/
	$customer=array();
	foreach($customerDetails as $key=>$item){
		$customer[$key] ='<tr><td>'.$item['label'].'</td><td>'.$item['value'].'</td></tr>';
		/**allow filtering**/
		$customer[$key] = apply_filters('wppizza_filter_print_order_customer_detail', $customer[$key], $key, $item);

		/*#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#*
			[remove, or alter labels for example]
			add_filter('wppizza_filter_print_order_customer_detail','myprefix_amend_customer_data',10,3);
			function myprefix_amend_customer_data($detail,$key,$item){


				//* to remove all labels in front of all customer details**
				$detail='<tr><td colspan="2" >'.$item['value'].'</td></tr>';

				//* to remove only the label for email in front of all customer details**
				if($key=='cemail'){
					$detail='<tr><td colspan="2" >'.$item['value'].'</td></tr>';
				}
				//* to alter the label in front of emails details**
				if($key=='cemail'){
					$detail='<tr><td>- my new label--</td><td>'.$item['value'].'</td></tr>';
				}

				return $detail;
			}
		*#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#*/
	}
	/**allow filtering**/
	$customer = apply_filters('wppizza_filter_print_order_customer', $customer);
	/*implode for output below*/
	$customer = implode(PHP_EOL,$customer);

	/*#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#*
		to add, amend or delete  different customer data (examples) :
		in your theme's functions.php:
		add_filter('wppizza_filter_print_order_customer','myprefix_amend_customer_details');
		function myprefix_amend_customer_details($details){

			//	do a print_r($details); to get all keys - they will be something like [cname],[cemail],[caddress],[ctel] etc

			// so to remove the line containing the email address (key being [cemail]) do:
			unset($details['cemail']);

			return $details;
		}
	*#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#*/


	/***********************
	*
	*	[create output customer]
	*
	***********************/
	$cTable['tableOpen']='<table id="customer">';

		/*header*/
		$cTable['tableHeader']='<thead><tr><th colspan="2">'.$txt['header_order_print_customer_label'].'</th></tr></thead>';

		/*footer*/
		$cTable['tableFooter']='';

		/*body*/
		$cTable['tableBodyOpen']='<tbody>';
			$cTable['tableBodyCustomer']=''.$customer.'';
		$cTable['tableBodyClose']='</tbody>';

	$cTable['tableClose']='</table>';

	/**************************
		allow filtering and
		implode for output
	***************************/
	$cTable = apply_filters('wppizza_filter_print_order_customer_output', $cTable, $order);
	$cTable = implode(PHP_EOL,$cTable);

	/***********************
	*
	*	[add to output]
	*
	***********************/
	$output['customer']=$cTable;


	/*#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#*
		to add, amend or delete  different elements (examples) :
		in your theme's functions.php:
		add_filter('wppizza_filter_print_order_customer_output','myprefix_amend_customer_elements');
		function myprefix_amend_customer_elements($elements){

			// to add something to footer
			$elements['tableFooter']='<tfoot><tr><td colspan="2">something</td></tr></tfoot>';


			// to add something after items
			$elements['tableBodyItems'].='<tr><td>something </td><td>something </td><td>something </td></tr>';

			// to remove table header for example
			unset($elements['tableHeader']);

			return $elements;

		}
	*#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#*/
?>
<?php
/**********************************************************************************************************************
*
*
*
*	[order/item details]
*
*
*
**********************************************************************************************************************/
	/***********************
	*
	*	[items]
	*
	***********************/
	$items=array();
	/*filter to allow sorting by category for example (or whatever else one can think of) **/
	$cartitems = apply_filters('wppizza_filter_print_order_items', $cartitems, 'print-order');

	foreach($cartitems as $key=>$item){

		/**allow filtering of item values to be imploded below*/
		$item = apply_filters('wppizza_filter_print_item_variables', $item, $key, 'print_template');


		$items[$key]['category']='';
		/**returns the category as <tr class="item-category"><td colspan="3">'.----this category name -----.'</td></tr>'**/
		$items[$key]['category'] = apply_filters('wppizza_filter_print_order_single_item_category',$items[$key]['category'], $item , 'tr', 'tr', false, false);

		/**construct item <tr> by array to make it more easily filterable**/
		$items[$key]['tropen'] ='<tr class="item">';

			$articleMarkup=array();
			$articleMarkup['quantity'] ='<td>'.$item['quantity'].'</td>';
				/*concat name price , singleprice to -> article */
				$itemDetails['name'] =''.$item['name'].'';
				$itemDetails['size'] ='<span class="size"> '.$item['size'].'</span>';
				$itemDetails['pricesingle'] =' ['.$item['value'].']';

			$articleMarkup['article'] ='<td>'.implode('',$itemDetails).'</td>';

			$articleMarkup['pricetotal'] ='<td>'.$item['valuetotal'].'</td>';

		//todo
		$articleMarkup = apply_filters('wppizza_filter_print_order_item_markup', $articleMarkup, $item, $key , 'print_template');


		/**add article  markup**/
		$items[$key]['article']=''.implode("",$articleMarkup).'';


		$items[$key]['trclose'] ='</tr>';

		/**additional info other plugins might add, lets keep first td empty to add some sensible spacing**/
		$items[$key]['addinfo'] ='<tr class="itemaddinfo"><td></td><td colspan="'.(count($articleMarkup)-1).'">'.$item['addinfo'].'</td></tr>';

		/**a divider tr /  hr ****/
		$items[$key]['devider'] ='<tr class="divider"><td colspan="'.count($articleMarkup).'"><hr /></td></tr>';

		/**allow filtering individual item**/
		$items[$key] = apply_filters('wppizza_filter_print_order_single_item', $items[$key]);
		$items[$key] = implode(PHP_EOL,$items[$key]);

	}
	/*implode for output below*/
	$items = implode(PHP_EOL,$items);

	/*#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#*
		to delete the single item price for example in each element:
		in your theme's functions.php:
		add_filter('wppizza_filter_print_order_single_item','myprefix_amend_item');
		function myprefix_amend_item($item){

			// to remove pricesingle after item name/size
			unset($item['pricesingle']);

			//to remove category name
			//(only applicable if enabled in wppizza->layout Group, sort and display menu items by category)

			unset($item['category']);

			return $item;
		}
	*#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#*/


	/***********************
	*
	*	[create output items]
	*
	***********************/
	$iTable['tableOpen']='<table id="items">';

		/*header markup */
		$headerMarkup=array();
		$headerMarkup['tableHeadOpen']='<thead><tr>';
			$headerColumns=array();
			$headerColumns['quantity']='<th>'.$txt['header_order_print_itemised_quantity'].'</th>';
			$headerColumns['arcticle']='<th>'.$txt['header_order_print_itemised_article'].'</th>';
			$headerColumns['total']='<th>'.$txt['header_order_print_itemised_price'].' '.$orderDetails['currency']['value'].'</th>';
			/**filter if necessary*/
			$headerColumns = apply_filters('wppizza_filter_print_order_item_header', $headerColumns, $txt, 'print_template');
			/*implode and add to array*/
			$headerMarkup['columns']=''.implode("",$headerColumns).'';

		$headerMarkup['tableHeadClose']='</tr></thead>';

		/**add to array markup**/
		$iTable['tableHeader']=implode("",$headerMarkup).'';


		/*footer*/
		$iTable['tableFooter']='';

		/*body*/
		$iTable['tableBodyOpen']='<tbody>';
			$iTable['tableBodyItems']=''.$items.'';
		$iTable['tableBodyClose']='</tbody>';

	$iTable['tableClose']='</table>';
	/**************************
		allow filtering and
		implode for output
	***************************/
	$iTable = apply_filters('wppizza_filter_print_order_items_output', $iTable, $order);
	$iTable = implode(PHP_EOL,$iTable);

	/***********************
	*
	*	[add to output]
	*
	***********************/
	$output['items']=$iTable;

	/*#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#*
		to add, amend or delete  different elements (examples) :
		in your theme's functions.php:
		add_filter('wppizza_filter_print_order_items_output','myprefix_amend_items_elements');
		function myprefix_amend_items_elements($elements){

			// to add something to footer
			$elements['tableFooter']='<tfoot><tr><td colspan="2">something</td></tr></tfoot>';


			// to add something after items
			$elements['tableBodyItems'].='<tr><td>something </td><td>something </td><td>something </td></tr>';

			// to remove table header for example
			unset($elements['tableHeader']);

			return $elements;

		}
	*#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#*/
?>
<?php
/*********************************************************************************************************************
*
*
*
*	[order summary: price/tax/discount/delivery options etc]
*
*
*
*********************************************************************************************************************/
	/***********************
	*
	*	[details]
	*
	***********************/
	$summary=array();
	foreach($orderSummary as $key=>$item){
		$summary[$key] ='<tr><td>'.$item['label'].'</td><td>'.$item['value'].'</td></tr>';
		/**allow filtering per line**/
		$summary[$key] = apply_filters('wppizza_filter_print_order_summary_item', $summary[$key]);
	}
	/**allow filtering all summary items**/
	$summary = apply_filters('wppizza_filter_print_order_summary', $summary);
	/*implode for output below*/
	$summary = implode(PHP_EOL,$summary);

	/***********************
	*
	*	[create output summary]
	*
	***********************/
	$sTable['tableOpen']='<table id="summary">';

		/*header*/
		$sTable['tableHeader']='<thead><tr><th colspan="2"></th></tr></thead>';

		/*footer*/
		$sTable['tableFooter']='';

		/*body*/
		$sTable['tableBodyOpen']='<tbody>';
			$sTable['tableBodySummary']=''.$summary.'';
		$sTable['tableBodyClose']='</tbody>';

	$sTable['tableClose']='</table>';

	/**************************
		allow filtering and
		implode for output
	***************************/
	$sTable = apply_filters('wppizza_filter_print_order_summary_output', $sTable, $order);
	$sTable = implode(PHP_EOL,$sTable);

	/***********************
	*
	*	[add to output]
	*
	***********************/
	$output['summary']=$sTable;

	/*#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#*
		to add, amend or delete  different elements (examples) :
		in your theme's functions.php:
		add_filter('wppizza_filter_print_order_summary_output','myprefix_amend_summary_element');
		function myprefix_amend_summary_element($elements){

			// to add something to footer
			$elements['tableFooter']='<tfoot><tr><td colspan="2">something</td></tr></tfoot>';


			// to add something after summary
			$elements['tableBodySummary'].='<tr><td>something left</td><td>something right</td></tr>';

			// to remove table header for example
			unset($elements['tableHeader']);

			return $elements;

		}
	*#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#*/
?>
<?php
/**********************************************************************************************************************
*
*
*
*	[allow filter (to change order of blocks for example)
*	, then implode and actually output]
*
*
*
**********************************************************************************************************************/
	$output=apply_filters('wppizza_filter_print_order_output', $output, $order);
	$output=implode(PHP_EOL,$output);
	echo $output;

	/*#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#*
		to reorder the different parts (or omit one or more):
		in your theme's functions.php:
		add_filter('wppizza_filter_print_order_output','myprefix_amend_output');
		function myprefix_amend_output($parts){

			// to reorder just arrange the order as required below. for example, to have customer details last
			$newparts['header']=$parts['header'];
			$newparts['overview']=$parts['overview'];
			$newparts['items']=$parts['items'];
			$newparts['summary']=$parts['summary'];
			$newparts['customer']=$parts['customer'];

			return $newparts;

			//to - for example - omit the header entirely
			unset($parts['header']);

			return $parts;

		}
	*#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#*/
?>
<?php
/*********************************************************************************************************************
*
*	[end  -> close body/html]
*
**********************************************************************************************************************/
?>
</body></html>