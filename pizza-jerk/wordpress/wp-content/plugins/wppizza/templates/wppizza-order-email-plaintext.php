<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php
/****************************************************************************************************************
*
*	WPPizza - Plaintext Email Template
*
*	Note: do not use html tags. it will not work . Know what you are doing.
*
*
****************************************************************************************************************/
?>
<?php
/****************************************************************************
*
*	[header: date and time of order,gateway used,transactionid  etc]
*
****************************************************************************/
?>
<?php echo $orderLabel['order_details'] ?>


<?php echo $nowdate ?>

<?php echo $orderLabel['order_paid_by'] ?> <?php echo $gatewayLabel ?> (<?php echo $transactionId ?>)



<?php
/****************************************************************************
*
*	[customer details: whatever fields where enabled on order page]
*
****************************************************************************/
echo $emailPlaintext['customer_details'];
?>

<?php
/****************************************************************************
*
*	[order items: list of items ordered]
*	to make thing reasonably pretty in plaintext emails , we pad with spaces
*	as required as tabs do not seem to want to work
*
****************************************************************************/
$output='';

/***allow filtering of items (sort, add categories and whatnot)****/
//note to self: in wppizza 3.0 - for consistancey, this should be 'emails', true (to indicate plaintext) instead, (make sure wppizza_orderhistory_filter_items parameters passed match)*/
$emailPlaintext['items'] = apply_filters('wppizza_emailplaintext_filter_items', $emailPlaintext['items'], 'plaintextemail');
foreach($emailPlaintext['items'] as $itemKey=>$item){

	/**construct the markup display of this item**/
	$itemMarkup=array();

	/***allow action per item - probably to use in conjunction with filter above****/
	/*
		THE BELOW FILTER WILL BE DEPRECIATED SOON IN FAVOUR OF SOMETHING LIKE
		$itemMarkup = apply_filters('wppizza_emailplaintext_item', $itemMarkup, $item);
		or similar plus various other changes to make this more consistant and managable
		going forward.
		any questions, just shout.
	*/
	$output = apply_filters('wppizza_emailplaintext_item', $item, $output);

	/**added 2.10.2*/
	$itemMarkup['quantity']		=''.$item['quantity'].'x';
	$itemMarkup['name']			=''.$item['name'].'';
	$itemMarkup['size']			=''.$item['size'].'';
	$itemMarkup['price']		='['.$item['price'].']';
	/**total**/
	$price_total	=$item['pricetotal'];

	/**try to add some even spaces between things**/
	$strlenRight=wppizza_strlen($price_total);
	$strlenLeft=wppizza_strlen(implode(" ",$itemMarkup));
	$spaces=(WPPIZZA_PLAINTEXT_MAX_LINE_LENGTH - $strlenRight - $strlenLeft - 2);/* - 2 as we will implode array by space again with filter below*/
	$itemMarkup['spacer']=str_pad('', $spaces);

	/**output total*/
	$itemMarkup['price_total']	=$price_total;

	/*add linebreak after each item*/
	$itemMarkup['linebreak']=PHP_EOL;

	/*add additional info, if any*/
	if(isset($item['additional_info']) && trim($item['additional_info'])!=''){
		$itemMarkup['additionalinfo']=''.$item['additional_info'].''.PHP_EOL.'';
	}

	/**************************************************************************************************
		[added filter for customisation  v2.10.2]
		if you wish to customise the output, i would suggest you use the filter below in
		your functions.php instead of editing this file (or a copy thereof in your themes directory)
	/**************************************************************************************************/
	$itemMarkup = apply_filters('wppizza_filter_plaintextemail_item_markup', $itemMarkup, $item, $itemKey, $pOptions['order'], 'emails', true);//true to indicate plaintext if needed additionally somewhere
	/**output markup**/
	$output.=implode(" ",$itemMarkup);
}
/* print it */
echo''.$output.'';
?>

<?php
/************************************************************************************************
*
*	[order summary: price/tax/discount/delivery options etc]
*
************************************************************************************************/
echo $emailPlaintext['order_summary'];
?>
<?php
/**********************************************************

	[action hook to add things if required]

***********************************************************/
	do_action('wppizza_emailplaintext_end', $orderLabel);
?>
<?php
/****************************************************************************
*
*	[footer]
*
****************************************************************************/
?>
==========================================================================

<?php
	echo $orderLabel['order_email_footer'];
?>