<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php
 /*
 *
 *	Template: WPPizza Gateway "Thank you" page Order Details
 *
 *	if enabled, this will display the order details on the thank you page
 *	after a transaction has been successfuly processed
 *	classes are the same as used in the order page (except trasaction id / time , as they doent exist in the order page before processing), so instead of using a custom version of this,
 *	MAYBE you want to just adjust the css so the layout is equal to the order page
 *
 *
 *	$order=>array(transaction_id,transaction_date_time,currency,currencyiso)
 *	$items=>array of tems ordered including (name,quantity,price,pricetotal,additionalInfo)
 *	$summary=>summary of all items (total_price_items,discount,delivery_charges,item_tax,total)
 *	$customer=> array customdetails
 *	$customerlbl=> array label for customer details
 *  $orderlbl=> array of localized vars
 */
?>
<?php
	/**do somthing if you want (like print order button or something)**/
	do_action('wppizza_show_order_before',$order,$summary);
?>
<div id="wppizza-cart-contents" class="wppizza-cart-thankyou">

<?php	/**header with time and transaction id**/ ?>
	<div id="wppizza-transaction-head"><?php echo $orderlbl['your_order'] ?>  <span id="wppizza-transaction-id"><?php echo $orderlbl['order_paid_by'] ?> <?php echo $order['gatewayLabel'] ?> [<?php echo $order['transaction_id'] ?>]</span><?php do_action('wppizza_show_order_head');/*do something*/ ?></div>
	<div id="wppizza-transaction-time"><?php echo $order['transaction_date_time'] ?> <br/><br/></div>

<?php
	/**do somthing if you want (like print order button or something)**/
	do_action('wppizza_show_order_before_customer_details',$order,$summary);
?>

<?php	/**customer details**/ 	?>
<?php if(isset($customer) && is_array($customer)){ ?>
	<ul id="wppizza-customer-details">
	<?php foreach($customer as $k=>$v){ if (isset($customerlbl[$k])) {?>
		<li><label><?php echo $customerlbl[$k] ?></label> <?php echo $v ?></li>
	<?php }} ?>
	</ul>
<?php } ?>
<?php
	/**do somthing if you want (like print order button or something)**/
	do_action('wppizza_show_order_after_customer_details',$order,$summary);
?>
<?php	/**order items details**/ 	?>
	<ul id="wppizza-item-details">
<?php
	/**do somthing if you want (like print order button or something)**/
	do_action('wppizza_show_order_form_first_item',$order,$summary);
?>
	<?php
		/***allow filtering of items (sort, add categories and whatnot)****/
		$items = apply_filters('wppizza_show_order_filter_items', $items, 'showorder');
		foreach($items as $k=>$item){
		/***allow action per item - probably to use in conjunction with filter above****/
		do_action('wppizza_show_order_item',$item);
	?>
	<li><?php
			/**added 2.10.2*/
			/**construct the markup display of this item**/
			$itemMarkup=array();
			$itemMarkup['quantity']		=''.$item['quantity'].'x ';
			$itemMarkup['name']			=''.$item['name'].'';
			$itemMarkup['size']			=''.$item['size'].'';
			$itemMarkup['price']		='<span class="wppizza-price-single">['.$item['price'].']</span>';
			$itemMarkup['price_total']	='<span class="wppizza-price">'.$item['pricetotal'].'</span>';
			if(isset($item['additionalInfo']) && $item['additionalInfo']!=''){
				$itemMarkup['additionalinfo']='<div class="wppizza-item-additionalinfo"><span>'.$item['additionalInfo'].'</span></div>';
			}
			/**************************************************************************************************
				[added filter for customisation  v2.10.2]
				if you wish to customise the output, i would suggest you use the filter below in
				your functions.php instead of editing this file (or a copy thereof in your themes directory)
			/**************************************************************************************************/
			$itemMarkup = apply_filters('wppizza_filter_show_order_item_markup', $itemMarkup, $item, $k, $options['order'], 'show_order');
			/**output markup**/
			echo''.implode(" ",$itemMarkup).'';
		?>
	</li>
	<?php } ?>
<?php
	/**do somthing if you want (like print order button or something)**/
	do_action('wppizza_show_order_form_last_item',$order,$summary,$items);
?>
	</ul>
<?php
	/**added 2.10.2*/
	do_action('wppizza_show_order_form_after_items',$order,$summary,$items);
?>
<?php	/**order summary**/ 	?>
	<ul id="wppizza-cart-subtotals">

			<li class="wppizza-order-total-items"><?php echo $orderlbl['order_items'] ?><span><?php echo $summary['total_price_items'] ?></span></li>

		<?php if(!empty($summary['discount'])){/*discount applies*/?>
			<li class="wppizza-order-discount"><?php echo $orderlbl['discount'] ?><span><span class="wppizza-minus"></span><?php echo $summary['discount'] ?></span></li>
		<?php } ?>

		<?php if(!empty($summary['item_tax']) && $summary['tax_applied']=='items_only' ){/*tax appplied to items only*/ ?>
			<li class="wppizza-order-item-tax"><?php echo $orderlbl['item_tax_total'] ?><span><?php echo $summary['item_tax'] ?></span></li>
		<?php } ?>

		<?php if(!isset($summary['selfPickup']) ||  $summary['selfPickup']==0){ /*no self pickup enabled or chosen :conditional  NEW IN VERSION  v1.4.1*/ ?>
			<?php if($summary['delivery_charges']!='' ){/*delivery charges if any*/?>
				<li class="wppizza-order-pickup"><?php echo $orderlbl['delivery_charges'] ?><span><?php echo $summary['delivery_charges'] ?></span></li>
			<?php }else{ ?>
				<li class="wppizza-order-pickup"><?php echo $orderlbl['delivery_charges'] ?><span><?php echo $orderlbl['free_delivery'] ?></span></li>
			<?php } ?>
		<?php } ?>

		<?php if(!empty($summary['item_tax']) && $summary['tax_applied']=='items_and_shipping' ){/*tax appplied to items_and_shipping */ ?>
			<li class="wppizza-order-item-tax"><?php echo $orderlbl['item_tax_total'] ?><span><?php echo $summary['item_tax'] ?></span></li>
		<?php } ?>

		<?php if(isset($summary['handling_charge']) && !empty($summary['handling_charge'])){/*handling charges (probably only used for cc's) */ ?>
			<li class="wppizza-order-item-handling"><?php echo $orderlbl['order_page_handling'] ?><span><?php echo $summary['handling_charge'] ?></span></li>
		<?php } ?>

		<?php if(isset($summary['tips']) && !empty($summary['tips'])){/*tips and gratuities */ ?>
			<li class="wppizza-order-item-tips"><?php echo $orderlbl['tips'] ?><span><?php echo $summary['tips'] ?></span></li>
		<?php } ?>

		<?php if(!empty($summary['taxes_included']) && $summary['tax_applied']=='taxes_included' ){/*taxes included NEW IN VERSION  v2.8.9.3*/ ?>
			<li class="wppizza-order-item-tax"><?php echo $orderlbl['taxes_included'] ?><span><?php echo $summary['taxes_included'] ?></span></li>
		<?php } ?>
		<?php
			/**added 2.11.2.4*/
			do_action('wppizza_show_order_before_totals',$order,$summary,$items);
		?>
			<li id="wppizza-cart-total"><?php echo $orderlbl['order_total'] ?><span><?php echo $summary['total'] ?></span></li>
		<?php
			/**added 2.11.2.4*/
			do_action('wppizza_show_order_after_totals',$order,$summary,$items);
		?>
		<?php if(isset($summary['selfPickup']) &&  $summary['selfPickup']==1 && $orderlbl['order_page_self_pickup']!=''){ /*self pickup conditional-> no delivery charges : NEW IN VERSION 1.4.1**/ ?>
			<li id="wppizza-self-pickup"><?php echo $orderlbl['order_page_self_pickup'] ?></li>
		<?php } ?>

		<?php if(isset($summary['selfPickup']) &&  $summary['selfPickup']==2 && $orderlbl['order_page_no_delivery']!=''){ /*no delivery offered : ADDED IN VERSION 2.8.6**/ ?>
			<li id="wppizza-self-pickup"><?php echo $orderlbl['order_page_no_delivery'] ?></li>
		<?php } ?>

		<?php if(empty($summary['selfPickup']) && $orderlbl['order_page_delivery']!=''){ /*delivery note : NEW IN VERSION 2.16.11.3**/ ?>
			<li id="wppizza-delivery"><?php echo $orderlbl['order_page_delivery'] ?></li>
		<?php } ?>

	</ul>
</div>
<?php
	/**do somthing if you want (like print order button or something)**/
	do_action('wppizza_show_order_after',$order,$summary,$items);
?>