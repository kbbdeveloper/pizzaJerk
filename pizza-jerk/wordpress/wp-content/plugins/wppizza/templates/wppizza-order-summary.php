<?php
if ( ! defined( 'ABSPATH' ) ) exit;/*Exit if accessed directly*/
/**********************************************************************************************************************************************************************
*
*
*
*	WPPizza - Summary / Thank you page Order Template
*
*
*	NOT YET IN USE /  FUTURE USE-----------------
*
*
*
*
*
*
*
*
*
**********************************************************************************************************************************************************************/
?>
<div id="wppizza-cart-contents" class="wppizza-cart-thankyou">

<?php	/**header with time and transaction id**/ ?>
	<div id="wppizza-transaction-head"><?php echo $orderlbl['your_order'] ?>  <span id="wppizza-transaction-id"><?php echo $orderlbl['order_paid_by'] ?> <?php echo $order['gatewayLabel']; ?> [<?php echo $order['transaction_id'] ?>]</span><?php do_action('wppizza_show_order_head');/*do something*/ ?></div>
	<div id="wppizza-transaction-time"><?php echo $order['transaction_date_time'] ?> <br/><br/></div>

<?php
	/**do somthing if you want (like print order button or something)**/
	do_action('wppizza_show_order_before_customer_details',$order,$summary);
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
		$customer[$key] ='<li><label>'.$item['label'].'</label>'.$item['value'].'</li>';
		/**allow filtering**/
		$customer[$key] = apply_filters('wppizza_filter_ordersummary_customer_detail', $customer[$key], $key, $item);

		/*#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#*
			[remove, or alter labels for example]
			add_filter('wppizza_filter_ordersummary_customer_detail','myprefix_amend_customer_data',10,3);
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
	$customer = apply_filters('wppizza_filter_ordersummary_customer', $customer);
	/*implode for output below*/
	$customer = implode(PHP_EOL,$customer);

	/*#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#*
		to add, amend or delete  different customer data (examples) :
		in your theme's functions.php:
		add_filter('wppizza_filter_ordersummary_customer','myprefix_amend_customer_details');
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
	$cList=array();
	if($customer!=''){
		$cList['cUlOpen']='<ul id="wppizza-customer-details">';
		$cList['cLis']=''.$customer.'';
		$cList['cUlClose']='</ul>';
	}

	/**************************
		allow filtering and
		implode for output
	***************************/
	$cList = apply_filters('wppizza_filter_ordersummary_customer_output', $cList, $order);
	$cList = implode(PHP_EOL,$cList);

	/***********************
	*
	*	[add to output]
	*
	***********************/
	$output['customer']=$cList;


	/*#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#*
		to add, amend or delete  different elements (examples) :
		in your theme's functions.php:
		add_filter('wppizza_filter_ordersummary_customer_output','myprefix_amend_customer_elements');
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
	/**do somthing if you want (like print order button or something)**/
	do_action('wppizza_show_order_after_customer_details',$order,$summary);
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
	$cartitems = apply_filters('wppizza_filter_ordersummary_items', $cartitems, 'print-order');

	foreach($cartitems as $key=>$item){
		/**returns the category as <tr class="item-category"><td colspan="3">'.----this category name -----.'</td></tr>'**/
		$items[$key]['category'] = apply_filters('wppizza_filter_ordersummary_single_item_category', $item , 'tr', 'tr', false, false);

		/**construct item <tr> by array to make it more easily filterable**/
		$items[$key]['tropen'] ='<tr class="item">';

			$items[$key]['td1open'] ='<td>';
				$items[$key]['quantity'] =''.$item['quantity'].'';
			$items[$key]['td1close'] ='</td>';

			$items[$key]['td2open'] ='<td>';
				$items[$key]['name'] =''.$item['name'].'';
				$items[$key]['size'] ='<span class="size"> '.$item['size'].'</span>';
				$items[$key]['pricesingle'] =' ['.$item['value'].']';
			$items[$key]['td2close'] ='</td>';

			$items[$key]['td3open'] ='<td>';
				$items[$key]['pricetotal'] =''.$item['valuetotal'].'';
			$items[$key]['td3close'] ='</td>';

		$items[$key]['trclose'] ='</tr>';

		/**additional info other plugins might add**/
		$items[$key]['addinfo'] ='<tr class="itemaddinfo"><td></td><td>'.$item['addinfo'].'</td><td></td></tr>';

		/**a divider tr /  hr ****/
		$items[$key]['devider'] ='<tr class="divider"><td colspan="3"><hr /></td></tr>';

		/**allow filtering individual item**/
		$items[$key] = apply_filters('wppizza_filter_ordersummary_single_item', $items[$key]);
		$items[$key] = implode(PHP_EOL,$items[$key]);

	}
	/*implode for output below*/
	$items = implode(PHP_EOL,$items);

	/*#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#*
		to delete the single item price for example in each element:
		in your theme's functions.php:
		add_filter('wppizza_filter_ordersummary_single_item','myprefix_amend_item');
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
	$iTable['iUlOpen']='<ul id="wppizza-item-details">';

//		/*header markup */
//		$headerMarkup=array();
//		$headerMarkup['tableHeadOpen']='<thead><tr>';
//			$headerMarkup['quantity']='<th>'.$txt['header_order_print_itemised_quantity'].'</th>';
//			$headerMarkup['arcticle']='<th>'.$txt['header_order_print_itemised_article'].'</th>';
//			$headerMarkup['total']='<th>'.$txt['header_order_print_itemised_price'].' '.$orderDetails['currency']['value'].'</th>';
//		$headerMarkup['tableHeadClose']='</tr></thead>';
//		/**filter if necessary*/
//		$headerMarkup = apply_filters('wppizza_filter_ordersummary_item_header', $headerMarkup, $txt);
//
//		/**add to array markup**/
//		$iTable['tableHeader']=implode("",$headerMarkup).'';
//
//
//		/*footer*/
//		$iTable['tableFooter']='';
//
//		/*body*/
//		$iTable['tableBodyOpen']='<tbody>';
//			$iTable['tableBodyItems']=''.$items.'';
//		$iTable['tableBodyClose']='</tbody>';
//
	$iTable['iUlClose']='</ul>';
	/**************************
		allow filtering and
		implode for output
	***************************/
	$iTable = apply_filters('wppizza_filter_ordersummary_items_output', $iTable, $order);
	$iTable = implode(PHP_EOL,$iTable);

	/***********************
	*
	*	[add to output]
	*
	***********************/
//	$output['items']=$iTable;

	/*#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#*
		to add, amend or delete  different elements (examples) :
		in your theme's functions.php:
		add_filter('wppizza_filter_ordersummary_items_output','myprefix_amend_items_elements');
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

</div>
<?php
	/**do somthing if you want (like print order button or something)**/
	do_action('wppizza_show_order_after',$order,$summary,$items);
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
		$summary[$key] = apply_filters('wppizza_filter_ordersummary_summary_item', $summary[$key]);
	}
	/**allow filtering all summary items**/
	$summary = apply_filters('wppizza_filter_ordersummary_summary', $summary);
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
	$sTable = apply_filters('wppizza_filter_ordersummary_summary_output', $sTable, $order);
	$sTable = implode(PHP_EOL,$sTable);

	/***********************
	*
	*	[add to output]
	*
	***********************/
//	$output['summary']=$sTable;

	/*#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#*
		to add, amend or delete  different elements (examples) :
		in your theme's functions.php:
		add_filter('wppizza_filter_ordersummary_summary_output','myprefix_amend_summary_element');
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
	$output=apply_filters('wppizza_filter_ordersummary_output', $output, $order);
	$output=implode(PHP_EOL,$output);
	echo $output;

	/*#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#**#*#*#*
		to reorder the different parts (or omit one or more):
		in your theme's functions.php:
		add_filter('wppizza_filter_ordersummary_output','myprefix_amend_output');
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