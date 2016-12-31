<?php
if ( ! defined( 'ABSPATH' ) ) exit;/*Exit if accessed directly*/
/****************************************************************************************
*
*
*	WPPizza - Html Email Template
*	Note: do not use divs, stylesheets or  <style></style> declarations
*	many email clients either don't understand/render divs and/or strip any tags from the email
*	tables are definitely your best bet.....
*
****************************************************************************************/

/*
	to keep things consistant and easier to manage, let's put at least some colors etc into variables
	obviously you can change override things as required
	make sure you do not end up with values like background:; or any other invalid styles,
	as gmail - for example - will strip the whole style declaration

*/
$htmlEmailStyle['tableWidth']='500';
$htmlEmailStyle['pageBackgroundColor']='#FFFFFF';
$htmlEmailStyle['fontSize']='14px';
$htmlEmailStyle['fontFamily']='Verdana, Helvetica, Arial, sans-serif';
$htmlEmailStyle['textColour']='#444444';
$htmlEmailStyle['linkColor']='#21759B';
$htmlEmailStyle['aLinkColor']='#21759B';
$htmlEmailStyle['vLinkColor']='#21759B';
$htmlEmailStyle['categories']='padding:5px 0 0 5px;margin:0;text-decoration:underline';/**used when showing categories too*/
$htmlEmailStyle['mailBackgroundColor']='#F4F3F4';
$htmlEmailStyle['mailHeaderBackgroundColor']='#21759B';
$htmlEmailStyle['mailHeaderBackgroundImage']="";//something like: background:url('http://www.domain.com/logo.png') 10px 10px no-repeat; //do not omit the colon
$htmlEmailStyle['mailHeaderTextColour']='#FFFFFF';
$htmlEmailStyle['mailBorder']='border: 1px dotted #CECECE';
$htmlEmailStyle['mailDivider']='padding:0;border-top:1px dotted #CECECE;';
$htmlEmailStyle['tdRight']='text-align:right;padding:2px 15px;';
/*various padding vars**/
$htmlEmailStyle['mailPadding']['20x0x0x15']='padding:20px 0 0 15px';
$htmlEmailStyle['mailPadding']['2x15']='padding:2px 15px';
$htmlEmailStyle['mailPadding']['30']='padding:30px';
$htmlEmailStyle['mailPadding']['2x0x0x15']='padding:2px 0 0 15px';
$htmlEmailStyle['mailPadding']['0x15x15x30']='padding:0px 15px 15px 30px';
$htmlEmailStyle['mailPadding']['0x5']='padding:0 5px';
$htmlEmailStyle['mailPadding']['5']='padding:5px';
$htmlEmailStyle['mailPadding']['10x5']='padding:10px 5px';


/************************************************************************************
	new in version 2.8.9.2 allow filtering of styles
	through a filter rather than editing this template directly
***********************************************************************************/
$htmlEmailStyle = apply_filters('wppizza_filter_html_email_style', $htmlEmailStyle);

?>
<?php
/***********************************************

	[general header and opening tags]

************************************************/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title></title>
		<meta http-equiv="Content-Type" content="text/html;charset=<?php echo get_option('blog_charset'); ?>" />
	</head>
	<body style="margin: 0px; background-color:<?php echo $htmlEmailStyle['pageBackgroundColor'] ?>;" text="<?php echo $htmlEmailStyle['textColour'] ?>" bgcolor="<?php echo $htmlEmailStyle['pageBackgroundColor'] ?>" link="<?php echo $htmlEmailStyle['linkColor'] ?>" alink="<?php echo $htmlEmailStyle['aLinkColor'] ?>" vlink="<?php echo $htmlEmailStyle['vLinkColor'] ?>">
		<table cellpadding="0" cellspacing="0" width="100%" bgcolor="<?php echo $htmlEmailStyle['pageBackgroundColor'] ?>" border="0" style="margin:10px 0">
			<tr>
				<td>
					<center>
						<table width="<?php echo $htmlEmailStyle['tableWidth'] ?>" cellpadding="0" cellspacing="0" align="center" bgcolor="<?php echo $htmlEmailStyle['mailBackgroundColor'] ?>" style="<?php echo $htmlEmailStyle['mailBorder'] ?>;background:<?php echo $htmlEmailStyle['mailBackgroundColor'] ?>;font-size:<?php echo $htmlEmailStyle['fontSize'] ?>;color:<?php echo $htmlEmailStyle['textColour'] ?>;font-family:<?php echo $htmlEmailStyle['fontFamily'] ?>;">
<?php
/***********************************************

	[add some header]
	[allow filtering of output]
************************************************/
			/**tr**/
			$htmlEmailHead='<tr><td colspan="2" style="'.$htmlEmailStyle['mailPadding']['30'].';text-align:center;'.$htmlEmailStyle['mailHeaderBackgroundImage'].';background-color:'.$htmlEmailStyle['mailHeaderBackgroundColor'].';"><h1 style="font-size:160%;color:'. $htmlEmailStyle['mailHeaderTextColour'].';">'.get_bloginfo().'</h1></td></tr>';
			/*add filter**/
			$htmlEmailHead = apply_filters('wppizza_filter_htmlemail_head', $htmlEmailHead, $htmlEmailStyle, $orderLabel, $nowdate, $gatewayLabel, $transactionId);
			/*output**/
			echo $htmlEmailHead;
?>
<?php
/***********************************************

	[add label, date transactionid, gateway info]
	[allow filtering of output]
************************************************/
			/**trs**/
			$htmlEmailHeadInfo['orderlabel']='<tr><td colspan="2" style="'.$htmlEmailStyle['mailPadding']['20x0x0x15'].'">'.$orderLabel['order_details'].'</td></tr>';
			$htmlEmailHeadInfo['transactiondate']='<tr><td colspan="2" style="'.$htmlEmailStyle['mailPadding']['2x0x0x15'].'">'.$nowdate.'</td></tr>';
			$htmlEmailHeadInfo['paymentdetails']='<tr><td colspan="2" style="'.$htmlEmailStyle['mailPadding']['2x0x0x15'].'">'.$orderLabel['order_paid_by'].' '.$gatewayLabel.' ('.$transactionId.')</td></tr>';
			$htmlEmailHeadInfo['devider']='<tr><td colspan="2" style="'.$htmlEmailStyle['mailDivider'].'">&nbsp;</td></tr>';
			/*add filter**/
			$htmlEmailHeadInfo = apply_filters('wppizza_filter_htmlemail_head_info', $htmlEmailHeadInfo,  $htmlEmailStyle, $orderLabel, $nowdate, $gatewayLabel, $transactionId);
			/*output**/
			echo implode(PHP_EOL,$htmlEmailHeadInfo);
?>
<?php
/**********************************************************

	[customer details loop of all submitted fields]

***********************************************************/
?>
					<tr><td colspan="2">
						<table width="100%" cellpadding="0" cellspacing="0" >
<?php 	foreach($customer_details_array as $k=>$v){ ?>
							<tr><td style="<?php echo $htmlEmailStyle['mailPadding']['2x15'] ?>;vertical-align:top;white-space:nowrap"><?php echo $v['label']; ?></td><td style="<?php echo $htmlEmailStyle['tdRight'] ?>"><?php echo $v['value']; ?></td></tr>
<?php } ?>
						</table>
					</td></tr>
					<tr><td colspan="2" style="<?php echo $htmlEmailStyle['mailDivider'] ?>">&nbsp;</td></tr><?php /*add devider**/ ?>

<?php
/**********************************************************

	[order details loop name/price/additional info]

***********************************************************/
?>
<?php
		/***allow filtering of items (sort, add categories and whatnot)****/
		$order_items = apply_filters('wppizza_emailhtml_filter_items', $order_items, 'htmlemail');
		foreach($order_items as $itemKey=>$item){
		/***allow action per item - probably to use in conjunction with filter above****/
		do_action('wppizza_emailhtml_item', $item, $htmlEmailStyle);

			/**added 2.10.2*/
			/**construct the markup display of this item**/
			$itemMarkup=array();
			$itemMarkup['trtd']			=PHP_EOL.'<tr><td style="'.$htmlEmailStyle['mailPadding']['2x15'].'">';
				$itemMarkup['quantity']		=''.$item['quantity'].'x ';
				$itemMarkup['name']			=''.$item['name'].'';
				$itemMarkup['size']			=''.$item['size'].'';
				$itemMarkup['price']		='['.$item['price'].']';
			$itemMarkup['tdtd']			='</td><td style="'.$htmlEmailStyle['tdRight'].'">';
				$itemMarkup['price_total']	=$item['pricetotal'];
			$itemMarkup['tdtr']			='</td></tr>'.PHP_EOL;

			if($item['additional_info']!=''){
				$itemMarkup['additionalinfo']='<tr><td colspan="2" style="'.$htmlEmailStyle['mailPadding']['0x15x15x30'].';font-size:90%">'. $item['additional_info'].'</td></tr>'.PHP_EOL;
			}
			/**************************************************************************************************
				[added filter for customisation  v2.10.2]
				if you wish to customise the output, i would suggest you use the filter below in
				your functions.php instead of editing this file (or a copy thereof in your themes directory)
			/**************************************************************************************************/
			$itemMarkup = apply_filters('wppizza_filter_htmlemail_item_markup', $itemMarkup, $item, $itemKey, $options['order'], 'emails');
			/**output markup**/
			echo''.implode(" ",$itemMarkup).'';
		?>
<?php } ?>
							<tr><td colspan="2" style="<?php echo $htmlEmailStyle['mailDivider'] ?>">&nbsp;</td></tr><?php /*add devider**/ ?>

<?php
/**********************************************************

	[order summaries - sum of items, discount, delivery, total, tax, self pickup if selected]

***********************************************************/
?>
		<?php /**cart items summary**/ ?>
							<tr><td style="<?php echo $htmlEmailStyle['mailPadding']['0x5'] ?>"><?php echo $order_summary['cartitems']['label']; ?></td><td style="<?php echo $htmlEmailStyle['tdRight'] ?>"><?php echo $order_summary['cartitems']['price']; ?></td></tr>

		<?php /**if discounts applied**/ ?>
		<?php if(isset($order_summary['discount'])){ ?>
							<tr><td style="<?php echo $htmlEmailStyle['mailPadding']['0x5'] ?>"><?php echo $order_summary['discount']['label']; ?></td><td style="<?php echo $htmlEmailStyle['tdRight'] ?>">-<?php echo $order_summary['discount']['price']; ?></td></tr>
		<?php } ?>

		<?php /**if items are taxed -> NEW IN VERSION 2.0**/ ?>
		<?php if(isset($order_summary['item_tax'])){ ?>
							<tr><td style="<?php echo $htmlEmailStyle['mailPadding']['0x5'] ?>"><?php echo $order_summary['item_tax']['label']; ?></td><td style="<?php echo $htmlEmailStyle['tdRight'] ?>"><?php echo $order_summary['item_tax']['price']; ?></td></tr>
		<?php } ?>

		<?php /**delivery costs (if free or self pickup, price and currency will be empty)**/ ?>
		<?php if(isset($order_summary['delivery'])){ ?>
							<tr><td style="<?php echo $htmlEmailStyle['mailPadding']['0x5'] ?>"><?php echo $order_summary['delivery']['label']; ?></td><td style="<?php echo $htmlEmailStyle['tdRight'] ?>"><?php if($order_summary['delivery']['price']!=''){echo $order_summary['delivery']['price'];} ?></td></tr>
		<?php } ?>

		<?php /**handling charges if any**/ ?>
		<?php if(isset($order_summary['handling_charge'])){ ?>
							<tr><td style="<?php echo $htmlEmailStyle['mailPadding']['0x5'] ?>"><?php echo $order_summary['handling_charge']['label']; ?></td><td style="<?php echo $htmlEmailStyle['tdRight'] ?>"><?php echo $order_summary['handling_charge']['price']; ?></td></tr>
		<?php } ?>
		<?php /**tips if any**/ ?>
		<?php if(isset($order_summary['tips'])){ ?>
							<tr><td style="<?php echo $htmlEmailStyle['mailPadding']['0x5'] ?>"><?php echo $order_summary['tips']['label']; ?></td><td style="<?php echo $htmlEmailStyle['tdRight'] ?>"><?php echo $order_summary['tips']['price']; ?></td></tr>
		<?php } ?>

		<?php /**taxes included**/ ?>
		<?php if(isset($order_summary['taxes_included'])){ ?>
							<tr><td style="<?php echo $htmlEmailStyle['mailPadding']['0x5'] ?>"><?php echo $order_summary['taxes_included']['label']; ?></td><td style="<?php echo $htmlEmailStyle['tdRight'] ?>"><?php echo $order_summary['taxes_included']['price']; ?></td></tr>
		<?php } ?>

		<?php
			/**added 2.11.2.4*/
			do_action('wppizza_emailhtml_before_totals',$order_summary,$order_items,$htmlEmailStyle);
		?>

		<?php /*add devider**/ ?>
							<tr><td colspan="2" style="<?php echo $htmlEmailStyle['mailDivider'] ?>">&nbsp;</td></tr>

		<?php /**total**/ ?>
							<tr><td style="<?php echo $htmlEmailStyle['mailPadding']['5'] ?>;font-weight:600"><?php echo $order_summary['total']['label']; ?></td><td style="font-weight:600;<?php echo $htmlEmailStyle['tdRight'] ?>"><?php echo $order_summary['total']['price']; ?></td></tr>

		<?php
			/**added 2.11.2.4*/
			do_action('wppizza_emailhtml_after_totals',$order_summary,$order_items,$htmlEmailStyle);
		?>
		<?php /**self pickup if selected. NEW IN VERSION 1.4.1**/ ?>
		<?php if(isset($order_summary['self_pickup'])){?>
							<tr><td style="<?php echo $htmlEmailStyle['mailPadding']['10x5'] ?>;font-weight:600;color:#ff3333" colspan="2"><?php echo $order_summary['self_pickup']['label']; ?></td></tr>
		<?php } ?>
		
		<?php /**delivery note. NEW IN VERSION 2.16.11.3**/ ?>
		<?php if(isset($order_summary['delivery_note']) && $order_summary['delivery_note']['label'] !=''){?>
							<tr><td style="<?php echo $htmlEmailStyle['mailPadding']['10x5'] ?>;font-weight:600;color:#ff3333" colspan="2"><?php echo $order_summary['delivery_note']['label']; ?></td></tr>
		<?php } ?>

<?php
/**********************************************************

	[action hook to add things if required]

***********************************************************/
	do_action('wppizza_emailhtml_end', $order_summary, $order_items, $htmlEmailStyle, $orderLabel);
?>
<?php
/**********************************************************

	[some thank you text or whatever]

***********************************************************/
?>
<?php
if(trim($orderLabel['order_email_footer'])!=''){
?>
		<?php /*add devider**/ ?>
							<tr><td colspan="2" style="<?php echo $htmlEmailStyle['mailDivider'] ?>">&nbsp;</td></tr>
		<tr><td colspan="2" style="<?php echo $htmlEmailStyle['mailPadding']['2x15'] ?>; font-size:90%"><?php echo $orderLabel['order_email_footer']; ?></td></tr>
<?php } ?>
<?php
/**********************************************************

	[general body table,html closing tags]

***********************************************************/
?>

						</table>
					</center>
				</td>
			</tr>
		</table>
	</body>
</html>