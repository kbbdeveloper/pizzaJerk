<?php if ( ! defined( 'ABSPATH' ) ) exit;/*Exit if accessed directly*/ ?>
<?php
	function wppizza_report_dataset($options,$orderTable,$export=false){

		if( version_compare( PHP_VERSION, '5.3', '<' )) {
			print"<div style='text-align:center;margin:50px 0'>Sorry, reporting is only available with php >=5.3</div>";
			exit();
		}

		global $wpdb,$blog_id;

			$wpTime=current_time('timestamp');
			$reportCurrency=$options['order']['currency_symbol'];
			$reportCurrencyIso=$options['order']['currency'];
			$hideDecimals=$options['layout']['hide_decimals'];
			$dateformat=get_option('date_format');
			$processOrder=array();


			/************************************************************************
				get all wppizza menu items by id and size
			************************************************************************/
			$args = array('post_type' => ''.WPPIZZA_POST_TYPE.'','posts_per_page' => -1, 'orderby'=>'title' ,'order' => 'ASC');
			$getWppizzaMenuItems = new WP_Query( $args );
			wp_reset_query();
			$wppizzaMenuItems=array();
			if( $getWppizzaMenuItems->have_posts()){
				/*loop through items*/
				foreach($getWppizzaMenuItems->posts as $menuItem){
					$meta=get_post_meta($menuItem->ID, WPPIZZA_POST_TYPE, true );
					$sizes=$options['sizes'][$meta['sizes']];
					/*loop through sizes*/
					if(is_array($sizes)){
					foreach($sizes as $sizekey=>$size){
						/*make key from id and size*/
						$miKey=$menuItem->ID.'.'.$sizekey;
						$wppizzaMenuItems[$miKey]=array('ID'=>$menuItem->ID,'name'=>$menuItem->post_title,'sizekey'=>$sizekey,'size'=>$size['lbl']);
					}}
				}
			}
			/**for ease of use, store above as purchased menu items to unset if bought*/
			$unsoldMenuItems=$wppizzaMenuItems;

			/************************************************************************
				overview query. do not limit by date to get totals
				any other query, add date range to query
			************************************************************************/
			$reportTypes=array(
				'ytd'=>array('lbl'=>__('year to date','wppizza-locale')),
				'ly'=>array('lbl'=>__('last year','wppizza-locale')),
				'tm'=>array('lbl'=>__('this month','wppizza-locale')),
				'lm'=>array('lbl'=>__('last month','wppizza-locale')),
				'12m'=>array('lbl'=>__('last 12 month','wppizza-locale')),
				'7d'=>array('lbl'=>__('last 7 days','wppizza-locale')),
				'14d'=>array('lbl'=>__('last 14 days','wppizza-locale'))
			);
			
			$overview=empty($_GET['report']) || !in_array($_GET['report'],array_keys($reportTypes)) ? true : false;
			$customrange=!empty($_GET['from']) && !empty($_GET['to'])  ? true : false;


			/******************************
			*
			*	[overview]
			*
			******************************/
			if($overview && !$customrange){
				$granularity='Y-m-d';/*days*/
				$daysSelected=30;
				$xaxisFormat='D, d M';
				$serieslines='true';
				$seriesbars='false';
				$seriespoints='true';
				$hoverOffsetLeft=5;
				$hoverOffsetTop=15;
				$firstDateTimestamp=mktime(date('H',$wpTime),date('i',$wpTime),date('s',$wpTime),date('m',$wpTime),date('d',$wpTime)-$daysSelected+1,date('Y',$wpTime));
				$firstDateReport="".date('Y-m-d',$firstDateTimestamp)."";
				$lastDateReport="".date('Y',$wpTime)."-".date('m',$wpTime)."-".date('d',$wpTime)." 23:59:59";
				$oQuery='';
				/***graph label**/
				$graphLabel="".__('Details last 30 days','wppizza-locale')." : ";
			}

			/******************************
			*
			*	[custom range]
			*
			******************************/
			if($customrange){
					$selectedReport='customrange';
					$from=explode('-',$_GET['from']);
					$to=explode('-',$_GET['to']);

					$firstDateTs=mktime(0, 0, 0, $from[1], $from[2], $from[0]);
					$lastDateTs=mktime(23, 59, 59, $to[1], $to[2], $to[0]);
					/*invert dates if end<start**/
					if($firstDateTs>$lastDateTs){
						$firstDateTimestamp=$lastDateTs;
						$lastDateTimestamp=$firstDateTs;
					}else{
						$firstDateTimestamp=$firstDateTs;
						$lastDateTimestamp=$lastDateTs;
					}

					$firstDateReport="".date('Y-m-d',$firstDateTimestamp)."";
					$lastDateReport="".date('Y-m-d H:i:s',$lastDateTimestamp)."";
					/*override get vars**/
					$_GET['from']=$firstDateReport;
					$_GET['to']=date('Y-m-d',$lastDateTimestamp);
					/**from/to formatted**/
					$fromFormatted=date($dateformat,$firstDateTimestamp);
					$toFormatted=date($dateformat,$lastDateTimestamp);

					$oQuery="AND order_date >='".$firstDateReport."'  AND order_date <= '".$lastDateReport."' ";
					/***graph label**/
					$graphLabel="".$fromFormatted." - ".$toFormatted." : ";
			}
			/******************************
			*
			*	[predefined reports]
			*
			******************************/
			if(!$overview){
				$selectedReport=$_GET['report'];
				$oQuery='';

				/************************
					year to date
				************************/
				if($selectedReport=='ytd'){
					$firstDateTimestamp=mktime(0, 0, 0, 1, 1, date('Y',$wpTime));
					$firstDateReport="".date('Y-m-d',$firstDateTimestamp)."";
					$lastDateReport="".date('Y',$wpTime)."-".date('m',$wpTime)."-".date('d',$wpTime)." 23:59:59";
					$oQuery="AND order_date >='".$firstDateReport."'  AND order_date <= '".$lastDateReport."' ";
					/***graph label**/
					$graphLabel="".__('Year to date','wppizza-locale')." : ";
				}
				/************************
					last year
				************************/
				if($selectedReport=='ly'){
					$firstDateTimestamp=mktime(0, 0, 0, 1, 1, date('Y',$wpTime)-1);
					$firstDateReport="".date('Y-m-d',$firstDateTimestamp)."";
					$lastDateReport=date('Y-m-d H:i:s',mktime(23,59,59,12,31,date('Y',$wpTime)-1));
					$oQuery="AND order_date >='".$firstDateReport."'  AND order_date <= '".$lastDateReport."' ";
					/***graph label**/
					$graphLabel="".__('Last Year','wppizza-locale')." : ";
				}
				/************************
					this month
				************************/
				if($selectedReport=='tm'){
					$firstDateTimestamp=mktime(0, 0, 0, date('m',$wpTime), 1, date('Y',$wpTime));
					$firstDateReport="".date('Y-m-d',$firstDateTimestamp)."";
					$lastDateReport="".date('Y-m-d H:i:s',mktime(23, 59, 59, date('m',$wpTime)+1, 0, date('Y',$wpTime)))."";
					$oQuery="AND order_date >='".$firstDateReport."'  AND order_date <= '".$lastDateReport."' ";
					/***graph label**/
					$graphLabel="".__('This Month','wppizza-locale')." : ";
				}
				/************************
					last month
				************************/
				if($selectedReport=='lm'){
					$firstDateTimestamp=mktime(0, 0, 0, date('m',$wpTime)-1, 1, date('Y',$wpTime));
					$firstDateReport="".date('Y-m-d',$firstDateTimestamp)."";
					$lastDateReport=date('Y-m-d H:i:s',mktime(23,59,59,date('m',$wpTime),0,date('Y',$wpTime)));
					$oQuery="AND order_date >='".$firstDateReport."'  AND order_date <= '".$lastDateReport."' ";
					/***graph label**/
					$graphLabel="".__('Last Month','wppizza-locale')." : ";
				}

				/************************
					last 12month
				************************/
				if($selectedReport=='12m'){
					$firstDateTimestamp=mktime(0, 0, 0, date('m',$wpTime)-12, date('d',$wpTime)+1, date('Y',$wpTime));
					$firstDateReport="".date('Y-m-d',$firstDateTimestamp)."";
					$lastDateReport=date('Y-m-d H:i:s',mktime(23, 59, 59, date('m',$wpTime), date('d',$wpTime), date('Y',$wpTime)));
					$oQuery="AND order_date >='".$firstDateReport."'  AND order_date <= '".$lastDateReport."' ";
					/***graph label**/
					$graphLabel="".__('Last 12 Month','wppizza-locale')." : ";
				}
				/************************
					last 7 days
				************************/
				if($selectedReport=='7d'){
					$firstDateTimestamp=mktime(0, 0, 0, date('m',$wpTime), date('d',$wpTime)-6, date('Y',$wpTime));
					$firstDateReport="".date('Y-m-d',$firstDateTimestamp)."";
					$lastDateReport=date('Y-m-d H:i:s',mktime(23, 59, 59, date('m',$wpTime), date('d',$wpTime), date('Y',$wpTime)));
					$oQuery="AND order_date >='".$firstDateReport."'  AND order_date <= '".$lastDateReport."' ";
					/***graph label**/
					$graphLabel="".__('Last 7 days','wppizza-locale')." : ";
				}
				/************************
					last 14 days
				************************/
				if($selectedReport=='14d'){
					$firstDateTimestamp=mktime(0, 0, 0, date('m',$wpTime), date('d',$wpTime)-13, date('Y',$wpTime));
					$firstDateReport="".date('Y-m-d',$firstDateTimestamp)."";
					$lastDateReport=date('Y-m-d H:i:s',mktime(23, 59, 59, date('m',$wpTime), date('d',$wpTime), date('Y',$wpTime)));
					/***graph label**/
					$oQuery="AND order_date >='".$firstDateReport."'  AND order_date <= '".$lastDateReport."' ";
					$graphLabel="".__('Last 14 days','wppizza-locale')." : ";
				}

			}

			if(!$overview || $customrange){
				$firstDate = new DateTime($firstDateReport);
				$firstDateFormatted = $firstDate->format($dateformat);
				$lastDate = new DateTime($lastDateReport);
				$lastDateFormatted = $lastDate->format($dateformat);
				$dateDifference = $firstDate->diff($lastDate);
				$daysSelected=($dateDifference->days)+1;
				$monthAvgDivider=($dateDifference->m)+1;
				$monthsSelected=$dateDifference->m;
				$yearsSelected=$dateDifference->y;
				/*set granularity to months if months>0 or years>0*/
				if($monthsSelected>0 || $yearsSelected>0 ){
					$granularity='Y-m';/*months*/
					$xaxisFormat='M Y';
					$serieslines='false';
					$seriesbars='true';
					$seriespoints='false';
					$hoverOffsetLeft=-22;
					$hoverOffsetTop=2;
				}else{
					$granularity='Y-m-d';/*days*/
					$xaxisFormat='D, d M';
					$serieslines='true';
					$seriesbars='false';
					$seriespoints='true';
					$hoverOffsetLeft=5;
					$hoverOffsetTop=15;
				}
			}


			/************************************************************************
				multisite install
				all orders of all sites (blogs)
				but only for master blog and if enabled (settings)
			************************************************************************/
			if ( is_multisite() && $blog_id==BLOG_ID_CURRENT_SITE && $options['plugin_data']['wp_multisite_reports_all_sites']) {
				$ordersQueryRes=array();
		 	   	$blogs = $wpdb->get_results("SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A);
		 	   		if ($blogs) {
		        	foreach($blogs as $blog) {
		        		switch_to_blog($blog['blog_id']);
						/************************
							[make and run query]
						*************************/
						$ordersQuery=wppizza_report_mkquery($wpdb->prefix,$orderTable,$oQuery);
						$ordersQuery= $wpdb->get_results($ordersQuery);
						/**merge array**/
						$ordersQueryRes=array_merge($ordersQuery,$ordersQueryRes);
						restore_current_blog();
		        	}}
			}else{
				/************************
					[make and run query]
				*************************/
				$ordersQuery=wppizza_report_mkquery($wpdb->prefix,$orderTable,$oQuery);
				$ordersQueryRes = $wpdb->get_results($ordersQuery);
			}

			/**************************
				ini dates
			**************************/
			$graphDates=array();
			for($i=0;$i<$daysSelected;$i++){
				$dayFormatted=mktime(date('H',$firstDateTimestamp),date('i',$firstDateTimestamp),date('s',$firstDateTimestamp),date('m',$firstDateTimestamp),date('d',$firstDateTimestamp)+$i,date('Y',$firstDateTimestamp));
				$graphDates[]=date($granularity,$dayFormatted);
			}

			/******************************************************************************************************************************************************
			*
			*
			*
			*	[create dataset from orders]
			*
			*
			*
			******************************************************************************************************************************************************/
					/*********************************************
						only when exporting to file
						sum/count of the same item in period
					*********************************************/
					if($export){
						$itemsSummed=array();
						$gatewaysSummed=array();
						$orderStatusSummed=array();
					}

					/*ini tax*/
					$orderTaxTotals=array();
					$orderTaxTotals['included']=0;
					$orderTaxTotals['added']=0;

					/**********************************************
					*
					*	[get and tidy up order first]
					*
					**********************************************/
					foreach($ordersQueryRes as $qKey=>$order){
						if($order->order_ini!=''){
							$orderDetails=maybe_unserialize($order->order_ini);/**unserialize order details**/
							/**eliminate some notices**/
							$orderDetails['taxes_included']=!empty($orderDetails['taxes_included']) ? $orderDetails['taxes_included'] : 0;
							/*************************************************************************************
								some collations - especially if importing from other/older db's that were still
								ISO instead of UTF may get confused by the collation and throw serialization errors
								the following *trys* to fix this , but is not 100% guaranteed to work in all cases
								99% of the time though this won't happen anyway, as it should only ever
								possibly be the case with very early versions of wppizza or if importing from early
								versions that have a different charset.
								....worth a try though regardless
							************************************************************************************/
							if(!isset($orderDetails['total'])){
								//print"".PHP_EOL.$order->id." | ". $order->oDate." | ".$orderDetails['total'];
								$orderDetails=$order->order_ini;
								/**convert currency symbols individuallly first to UTF*/
								$convCurr=iconv("ISO-8859-1","UTF-8", $reportCurrency);
								$orderDetails=str_replace($reportCurrency,$convCurr,$orderDetails);
								/**convert to ISO **/
								$encoding   = mb_detect_encoding($orderDetails);
								$orderDetails=iconv($encoding,"ISO-8859-1//IGNORE", $orderDetails);
								/**unseralize**/
								$orderDetails=maybe_unserialize($orderDetails);
								/**if we still have unrescuable errors we *could*  catch them somewhere */
								if(!isset($orderDetails['total'])){
									//$encoding   = mb_detect_encoding($order->order_ini);
									//$errors=wppizza_serialization_errors($order->order_ini);
									//file_put_contents('','.$order->id.': ['.$encoding.'] '.print_r($order->order_ini,true).' '.print_r($errors,true).PHP_EOL.PHP_EOL,FILE_APPEND);
								}
							}

							if(isset($orderDetails['total'])){
								/**tidy up a bit and get rid of stuff we do not need**/
								unset($orderDetails['currencyiso']);
								unset($orderDetails['currency']);
								unset($orderDetails['discount']);
								unset($orderDetails['delivery_charges']);
								unset($orderDetails['tips']);
								unset($orderDetails['selfPickup']);
								unset($orderDetails['time']);
								/**add new**/
								$orderDetails['order_date']=substr($order->oDate,0,10);
								$orderDetails['order_date_formatted']=date($granularity,$order->order_date);
								$orderDetails['order_items_count']=0;

								/**add up tax*/
								$orderTaxTotals['included']+=!empty($orderDetails['taxes_included']) ? $orderDetails['taxes_included'] : 0;
								$orderTaxTotals['added']+=!empty($orderDetails['item_tax']) ? $orderDetails['item_tax'] : 0;

								/**sanitize the items**/
								$itemDetails=array();
								if(isset($orderDetails['item'])){
								foreach($orderDetails['item'] as $k=>$uniqueItems){
									//$itemDetails[$k]['postId']=$uniqueItems['postId'];
									$itemDetails[$k]['name']=$uniqueItems['name'];
									$itemDetails[$k]['size']=$uniqueItems['size'];
									$itemDetails[$k]['quantity']=$uniqueItems['quantity'];
									$itemDetails[$k]['price']=$uniqueItems['price'];
									$itemDetails[$k]['pricetotal']=$uniqueItems['pricetotal'];
									/**add count of items in this order**/
									$orderDetails['order_items_count']+=$uniqueItems['quantity'];

									/*sum/count of the same item in period . export only*/
									/*make unique by name too as it may have changed over time*/
									if($export){
										/* make a key consisting of id and size and md5 of name (as it may have changed over time) to sum it up*/
										$mkKey=''.$uniqueItems['postId'].'.'.$uniqueItems['size'].'.'.MD5($uniqueItems['name']);
										if(!isset($itemsSummed[$mkKey])){
											$itemsSummed[$mkKey]=array('quantity'=>$uniqueItems['quantity'], 'name'=>$uniqueItems['name'].' ['.$uniqueItems['size'].']', 'pricetotal'=>$uniqueItems['pricetotal']);
										}else{
											$itemsSummed[$mkKey]['quantity']+=$uniqueItems['quantity'];
											$itemsSummed[$mkKey]['pricetotal']+=$uniqueItems['pricetotal'];
										}
									}
								}}
								/**add relevant item info to array**/
								$orderDetails['item']=$itemDetails;


								$processOrder[]=$orderDetails;
							
								/*sum by gateway and order status*/
								if($export){
									/* per gateway */
									if(!isset($gatewaysSummed[$order->initiator])){
										$gatewaysSummed[$order->initiator]=$orderDetails['total'];
									}else{
										$gatewaysSummed[$order->initiator]+=$orderDetails['total'];
									}
									/* per order status */
									if(!isset($orderStatusSummed[$order->order_status])){
										$count[$order->order_status] = 1;
										$value[$order->order_status] = $orderDetails['total'];
										$orderStatusSummed[$order->order_status] = array('count' => $count[$order->order_status], 'value' => $value[$order->order_status]);
									}else{
										$count[$order->order_status] ++;
										$value[$order->order_status] += $orderDetails['total'];
										$orderStatusSummed[$order->order_status] = array('count' => $count[$order->order_status], 'value' => $value[$order->order_status]);
									}									
								}
							
							}
						}
					}

					/*sort distinct items - export only***/
					if($export && !empty($itemsSummed)){
						arsort($itemsSummed);
					}

					/**********************************************************************************
					*
					*
					*	lets do the calculations, to get the right dataset
					*
					*
					**********************************************************************************/

					/**************************************
						[initialize array and values]
					**************************************/
					$datasets=array();
					$datasets['sales_value_total']=0;/**total of sales/orders INCLUDING taxes, discounts, charges etc**/
					$datasets['sales_count_total']=0;/**total count of sales**/
					$datasets['sales_order_tax']=0;/**tax on order**/
					$datasets['items_value_total']=0;/**total of items EXLUDING taxes, discounts, charges etc**/
					$datasets['items_count_total']=0;/**total count of items**/


					$datasets['tax_total']=wppizza_output_format_price(($orderTaxTotals['included']+$orderTaxTotals['added']),$hideDecimals);/**total tax**/
					$datasets['sales']=array();/*holds data on a per day/month basis*/
					$datasets['bestsellers']=array('by_volume'=>array(),'by_value'=>array());
					
					if($export){
						/*per item*/
						$datasets['items_summary']=$itemsSummed;
						/*per gateway*/
						$datasets['gateways_summary']=$gatewaysSummed;
						/*per gateway*/
						$datasets['order_status_summary']=$orderStatusSummed;						
					}
					/**************************************
						[loop through orders and do things]
					**************************************/
					$j=1;
					foreach($processOrder as $k=>$order){

						/****************************************************
							if we are not setting a defined range
							like a whole month, week , or whatever
							(i.e in overview) lets get first and last day
							we have orders for to be able to calc averages
						****************************************************/
						if($j==1){$datasets['first_date']=$order['order_date'];}
						//if($j==count($processOrder)){$datasets['last_date']=$order['order_date'];}

						/****************************************************
							set garnularity (i.e by day, month or year)
						****************************************************/
						$dateResolution=$order['order_date_formatted'];/**set garnularity (i.e by day, month or year)**/

						/****************************************************
							[get/set totals]
						****************************************************/
						$datasets['sales_value_total']+=$order['total'];
						$datasets['sales_count_total']++;
						$datasets['sales_order_tax']+=$order['item_tax']+$order['taxes_included'];
						$datasets['items_value_total']+=$order['total_price_items'];
						$datasets['items_count_total']+=$order['order_items_count'];

						/****************************************************
							[get/set items to sort for bestsellers]
						****************************************************/
						foreach($order['item'] as $iK=>$oItems){
							$uniqueKeyX=explode('|',$iK);
							$category='';
							/**
								if grouped by category is/was set, $uniqueKeyX will have 4 int, concat by a period, where the 3rd denotes the cat id*/
							$kX=explode('.',$uniqueKeyX[0]);
							/*item id*/
							$menuItemId=$kX[0];
							/*size id*/
							$menuItemSize=$kX[1];

							if(count($kX)>3 && $options['layout']['items_group_sort_print_by_category']){
								$category = get_term_by( 'id', $kX[2], WPPIZZA_TAXONOMY);
								if(is_object($category)){
									$category=' - <em style="font-size:80%">'.$category->name.'</em>';
								}else{
									$category='';
								}
							}

							/**unset this bought item from the unsold menu items**/
							if(isset($unsoldMenuItems[$menuItemId.'.'.$menuItemSize])){
								unset($unsoldMenuItems[$menuItemId.'.'.$menuItemSize]);
							}

							/**make a unique key by id and name in case an items name was changed */
							/**note, unique keys will be different when grouped/display by category is set*/
							$uKey=MD5($uniqueKeyX[0].$oItems['name'].$oItems['size']);
							if(!isset($datasets['bestsellers']['by_volume'][$uKey])){
								/**lets do by volume and by value at the same time**/
								$datasets['bestsellers']['by_value'][$uKey]=array('price'=>$oItems['pricetotal'], 'single_price'=>$oItems['price'], 'quantity'=>$oItems['quantity'], 'name'=>''.$oItems['name'].' ['.$oItems['size'].']'.$category.'', 'min_price'=>$oItems['price'], 'max_price'=>$oItems['price']  );
								$datasets['bestsellers']['by_volume'][$uKey]=array('quantity'=>$oItems['quantity'], 'price'=>$oItems['pricetotal'], 'single_price'=>$oItems['price'], 'name'=>''.$oItems['name'].' ['.$oItems['size'].']'.$category.'');
							}else{
								/*sum up / set  by value*/
								$datasets['bestsellers']['by_value'][$uKey]['quantity']+=$oItems['quantity'];
								$datasets['bestsellers']['by_value'][$uKey]['price']+=$oItems['pricetotal'];
								/*set min and max price as they may have changed */
								if($oItems['price']>$datasets['bestsellers']['by_value'][$uKey]['max_price']){
									$datasets['bestsellers']['by_value'][$uKey]['max_price']=$oItems['price'];
								}
								if($oItems['price']<$datasets['bestsellers']['by_value'][$uKey]['min_price']){
									$datasets['bestsellers']['by_value'][$uKey]['min_price']=$oItems['price'];
								}

								/*sum up by volume*/
								$datasets['bestsellers']['by_volume'][$uKey]['quantity']+=$oItems['quantity'];
								$datasets['bestsellers']['by_volume'][$uKey]['price']+=$oItems['pricetotal'];
							}
						}

						/****************************************************
							[get/set totals [per granularity]
						****************************************************/
							/**initialize arrays**/
							if(!isset($datasets['sales'][$dateResolution])){
								$datasets['sales'][$dateResolution]['sales_value_total']=0;
								$datasets['sales'][$dateResolution]['sales_count_total']=0;
								$datasets['sales'][$dateResolution]['sales_order_tax']=0;
								$datasets['sales'][$dateResolution]['items_value_total']=0;
								$datasets['sales'][$dateResolution]['items_count_total']=0;
							}
							$datasets['sales'][$dateResolution]['sales_value_total']+=$order['total'];
							$datasets['sales'][$dateResolution]['sales_count_total']++;
							$datasets['sales'][$dateResolution]['sales_order_tax']+=$order['item_tax']+$order['taxes_included'];
							$datasets['sales'][$dateResolution]['items_value_total']+=$order['total_price_items'];
							$datasets['sales'][$dateResolution]['items_count_total']+=$order['order_items_count'];
					$j++;
				}

				/*******************************
					sort and splice bestsellers
				*******************************/
				arsort($datasets['bestsellers']['by_volume']);
				arsort($datasets['bestsellers']['by_value']);

				/*max display, could be made into a dropdown*/
				if(!isset($_GET['b'])){$bCount=10;}else{$bCount=abs((int)$_GET['b']);}

				/*slice worstsellers - currently not displayed*/
				$worstsellers['by_volume']=array_slice($datasets['bestsellers']['by_volume'],-$bCount);
				asort($worstsellers['by_volume']);
				$worstsellers['by_value']=array_slice($datasets['bestsellers']['by_value'],-$bCount);
				asort($worstsellers['by_value']);

				/*splice bestsellers*/
				array_splice($datasets['bestsellers']['by_volume'],$bCount);
				array_splice($datasets['bestsellers']['by_value'],$bCount);


				/************************************************************
					construct bestsellers html
				*************************************************************/
				$htmlBsVol='<ul id="wppizza-report-top10-volume-ul">';/*by volume*/
				foreach($datasets['bestsellers']['by_volume'] as $bsbv){
					$htmlBsVol.='<li>'.$bsbv['quantity'].' x '.$bsbv['name'].'</li>';
				}
				$htmlBsVol.='</ul>';

				$htmlBsVal='<ul id="wppizza-report-top10-value-ul">';/*by value*/
				foreach($datasets['bestsellers']['by_value'] as $bsbv){
					$priceRange=wppizza_output_format_price($bsbv['single_price'],$hideDecimals);
					/*show price range if prices were changed */
					if($bsbv['min_price']!=$bsbv['max_price']){
					$priceRange=''.wppizza_output_format_price($bsbv['min_price'],$hideDecimals).'-'.wppizza_output_format_price($bsbv['max_price'],$hideDecimals);
					}
					$htmlBsVal.='<li>'.$bsbv['name'].' <span>'.$reportCurrency.''.wppizza_output_format_price($bsbv['price'],$hideDecimals).'</span><br /> ['.$bsbv['quantity'].' x '.$reportCurrency.''.$priceRange.'] <span>'.round($bsbv['price']/$datasets['items_value_total']*100,2).'%</span></li>';
				}
				$htmlBsVal.='</ul>';


				/************************************************************
					construct worstsellers html - currently not displayed
				*************************************************************/
				$htmlWsVol='<ul id="wppizza-report-bottom10-volume-ul">';/*by volume*/
				foreach($worstsellers['by_volume'] as $bsbv){
					$htmlWsVol.='<li>'.$bsbv['quantity'].' x '.$bsbv['name'].'</li>';
				}
				$htmlWsVol.='</ul>';

				$htmlWsVal='<ul id="wppizza-report-bottom10-value-ul">';/*by value*/
				foreach($worstsellers['by_value'] as $bsbv){
					$priceRange=wppizza_output_format_price($bsbv['single_price'],$hideDecimals);
					/*show price range if prices were changed */
					if($bsbv['min_price']!=$bsbv['max_price']){
					$priceRange=''.wppizza_output_format_price($bsbv['min_price'],$hideDecimals).'-'.wppizza_output_format_price($bsbv['max_price'],$hideDecimals);
					}
					$htmlWsVal.='<li>'.$bsbv['name'].' <span>'.$reportCurrency.''.wppizza_output_format_price($bsbv['price'],$hideDecimals).'</span><br /> ['.$bsbv['quantity'].' x '.$reportCurrency.''.$priceRange.'] <span>'.round($bsbv['price']/$datasets['items_value_total']*100,2).'%</span></li>';
				}
				$htmlWsVal.='</ul>';

				$htmlNoSellers='<ul id="wppizza-report-nosellers-ul">';/*non sellers*/
				/*add unsold items*/
				foreach($unsoldMenuItems as $usKey=>$usVal){
					$htmlNoSellers.='<li>0 x '.$usVal['name'].' ['.$usVal['size'].']</li>';
				}
				$htmlNoSellers.='</ul>';


				/**********************************************************
					get number of months and days in results array
				***********************************************************/
				if($overview && !$customrange){
					/**in case we have an empty results set**/
					if(!isset($datasets['first_date'])){
						$datasets['first_date']="".date('Y',$wpTime)."-".date('m',$wpTime)."-".date('d',$wpTime)." 00:00:00";
					}
					$firstDate = new DateTime($datasets['first_date']);
					$firstDateFormatted = $firstDate->format($dateformat);
					$lastDate = new DateTime("".date('Y',$wpTime)."-".date('m',$wpTime)."-".date('d',$wpTime)." 23:59:59");
					$lastDateFormatted = $lastDate->format($dateformat);
					$dateDifference = $firstDate->diff($lastDate);
					$daysSelected=$dateDifference->days+1;
					$monthAvgDivider=($dateDifference->m)+1;
				}

				/*****************************************************************
					averages
				******************************************************************/
				/*per day*/
				$datasets['sales_count_average']=round($datasets['sales_count_total']/$daysSelected,2);
				$datasets['sales_item_average']=round($datasets['items_count_total']/$daysSelected,2);
				$datasets['sales_value_average']=round($datasets['sales_value_total']/$daysSelected,2);
				/*per month*/
				$datasets['sales_count_average_month']=round($datasets['sales_count_total']/$monthAvgDivider,2);
				$datasets['sales_item_average_month']=round($datasets['items_count_total']/$monthAvgDivider,2);
				$datasets['sales_value_average_month']=round($datasets['sales_value_total']/$monthAvgDivider,2);

			/******************************************************************************************************************************************************
			*
			*
			*	[sidebar boxes]
			*
			*
			******************************************************************************************************************************************************/
			$box=array();
			$boxrt=array();
			if($overview && !$customrange){
				/*boxes left*/
				$box[]=array('id'=>'wppizza-report-val-total', 'class'=>'', 'lbl'=>__('All Sales: Total','wppizza-locale'),'val'=>'<p>'.$reportCurrency.' '.wppizza_output_format_price($datasets['sales_value_total'],$hideDecimals).'<br /><span class="description">'.__('incl. taxes, charges and discounts','wppizza-locale').'</span></p>');
				$box[]=array('id'=>'wppizza-report-val-avg', 'class'=>'', 'lbl'=>__('All Sales: Averages','wppizza-locale'),'val'=>'<p>'.$reportCurrency.' '.wppizza_output_format_price($datasets['sales_value_average'],$hideDecimals).' '.__('per day','wppizza-locale').'<br />'.$reportCurrency.' '.wppizza_output_format_price($datasets['sales_value_average_month'],$hideDecimals).' '.__('per month','wppizza-locale').'</p>');
				$box[]=array('id'=>'wppizza-report-count-total', 'class'=>'', 'lbl'=>__('All Orders/Items: Total','wppizza-locale'),'val'=>'<p>'.$datasets['sales_count_total'].' '.__('Orders','wppizza-locale').': '.$reportCurrency.' '.$datasets['items_value_total'].'<br />('.$datasets['items_count_total'].' '.__('items','wppizza-locale').')<br /><span class="description">'.__('before taxes, charges and discounts','wppizza-locale').'</span></p>');
				$box[]=array('id'=>'wppizza-report-count-avg', 'class'=>'', 'lbl'=>__('All Orders/Items: Averages','wppizza-locale'),'val'=>'<p>'.$datasets['sales_count_average'].' '.__('Orders','wppizza-locale').' ('.$datasets['sales_item_average'].' '.__('items','wppizza-locale').') '.__('per day','wppizza-locale').'<br />'.$datasets['sales_count_average_month'].' '.__('Orders','wppizza-locale').' ('.$datasets['sales_item_average_month'].' items) '.__('per month','wppizza-locale').'</p>');
				$box[]=array('id'=>'wppizza-report-taxes', 'class'=>'', 'lbl'=>__('Total Tax on Orders','wppizza-locale'),'val'=>'<p>'.$datasets['tax_total'].'</p>');
				$box[]=array('id'=>'wppizza-report-info', 'class'=>'', 'lbl'=>__('Range','wppizza-locale'),'val'=>'<p>'.$firstDateFormatted.' - '.$lastDateFormatted.'<br />'.$daysSelected.' '.__('days','wppizza-locale').'<br />'.$monthAvgDivider.' '.__('months','wppizza-locale').'</p>');

				/*boxes right*/
				$boxrt[]=array('id'=>'wppizza-report-top10-volume', 'class'=>'', 'lbl'=>__('Best/Worst sellers by Volume - All','wppizza-locale'),'val'=>$htmlBsVol.$htmlWsVol);
				$boxrt[]=array('id'=>'wppizza-report-top10-value', 'class'=>'', 'lbl'=>__('Best/Worst sellers by Value - All (% of order total)','wppizza-locale'),'val'=>$htmlBsVal.$htmlWsVal);
				$boxrt[]=array('id'=>'wppizza-report-nonsellers', 'class'=>'', 'lbl'=>__('Non-Sellers - All','wppizza-locale'),'val'=>$htmlNoSellers);
			}
			if(!$overview || $customrange){
				/*boxes left*/
				$box[]=array('id'=>'wppizza-report-val-total', 'class'=>'', 'lbl'=>__('Sales Total [in range]','wppizza-locale'),'val'=>'<p>'.$reportCurrency.' '.wppizza_output_format_price($datasets['sales_value_total'],$hideDecimals).'<br /><span class="description">'.__('incl. taxes, charges and discounts','wppizza-locale').'</span></p>');
				$box[]=array('id'=>'wppizza-report-val-avg', 'class'=>'', 'lbl'=>__('Sales Averages [in range]','wppizza-locale'),'val'=>'<p>'.$reportCurrency.' '.wppizza_output_format_price($datasets['sales_value_average'],$hideDecimals).' '.__('per day','wppizza-locale').'<br />'.$reportCurrency.' '.wppizza_output_format_price($datasets['sales_value_average_month'],$hideDecimals).' '.__('per month','wppizza-locale').'</p>');
				$box[]=array('id'=>'wppizza-report-count-total', 'class'=>'', 'lbl'=>__('Orders/Items Total [in range]','wppizza-locale'),'val'=>'<p>'.$datasets['sales_count_total'].' '.__('Orders','wppizza-locale').': '.$reportCurrency.' '.$datasets['items_value_total'].'<br /> ('.$datasets['items_count_total'].' '.__('items','wppizza-locale').')<br /><span class="description">'.__('before taxes, charges and discounts','wppizza-locale').'</span></p>');
				$box[]=array('id'=>'wppizza-report-taxes', 'class'=>'', 'lbl'=>__('Total Tax on Orders [in range]','wppizza-locale'),'val'=>'<p>'.$datasets['tax_total'].'</p>');
				$box[]=array('id'=>'wppizza-report-count-avg', 'class'=>'', 'lbl'=>__('Orders/Items Averages [in range]','wppizza-locale'),'val'=>'<p>'.$datasets['sales_count_average'].' '.__('Orders','wppizza-locale').' ('.$datasets['sales_item_average'].' '.__('items','wppizza-locale').') '.__('per day','wppizza-locale').'<br />'.$datasets['sales_count_average_month'].' '.__('Orders','wppizza-locale').' ('.$datasets['sales_item_average_month'].' items) '.__('per month','wppizza-locale').'</p>');
				$box[]=array('id'=>'wppizza-report-info', 'class'=>'', 'lbl'=>__('Range','wppizza-locale'),'val'=>'<p>'.$firstDateFormatted.' - '.$lastDateFormatted.'<br />'.$daysSelected.' '.__('days','wppizza-locale').'<br />'.$monthAvgDivider.' '.__('months','wppizza-locale').'</p>');

				/*boxes right*/
				$boxrt[]=array('id'=>'wppizza-report-top10-volume', 'class'=>'', 'lbl'=>__('Best/Worst sellers by Volume [in range]','wppizza-locale'),'val'=>$htmlBsVol.$htmlWsVol);
				$boxrt[]=array('id'=>'wppizza-report-top10-value', 'class'=>'', 'lbl'=>__('Best/Worst sellers by Value [% of all orders in range]','wppizza-locale'),'val'=>$htmlBsVal.$htmlWsVal);
				$boxrt[]=array('id'=>'wppizza-report-nonsellers', 'class'=>'', 'lbl'=>__('Non-Sellers [in range]','wppizza-locale'),'val'=>$htmlNoSellers);

			}
			/**allow order change by filter**/
			$box=apply_filters('wppizza_reports_boxes_left',$box);
			$boxrt=apply_filters('wppizza_reports_boxes_right',$boxrt);
			/******************************************************************************************************************************************************
			*
			*
			*	[graph data]
			*
			*
			******************************************************************************************************************************************************/

				/***graph data sales value**/
				$grSalesValue=array();
				foreach($graphDates as $date){
					$str2t=strtotime($date.' 12:00:00');
					$xaxis=date($xaxisFormat,$str2t);
					$val=!empty($datasets['sales'][$date]['sales_value_total']) ? $datasets['sales'][$date]['sales_value_total'] : 0;
					$grSalesValue[]='["'.$xaxis.'",'.$val.']';
				}
				$graph['sales_value']='label:"'.__('sales value','wppizza-locale').'",data:['.implode(',',$grSalesValue).']';

				/***graph data sales count**/
				$grSalesCount=array();
				foreach($graphDates as $date){
					$str2t=strtotime($date.' 12:00:00');
					$xaxis=date($xaxisFormat,$str2t);
					$val=!empty($datasets['sales'][$date]['sales_count_total']) ? $datasets['sales'][$date]['sales_count_total'] : 0;
					$grSalesCount[]='["'.$xaxis.'",'.$val.']';
				}
				$graph['sales_count']='label:"'.__('number of sales','wppizza-locale').'",data:['.implode(',',$grSalesCount).'], yaxis: 2';

				/***graph data items count**/
				$grItemsCount=array();
				foreach($graphDates as $date){
					$str2t=strtotime($date.' 12:00:00');
					$xaxis=date($xaxisFormat,$str2t);
					$val=!empty($datasets['sales'][$date]['items_count_total']) ? $datasets['sales'][$date]['items_count_total'] : 0;
					$grItemsCount[]='["'.$xaxis.'",'.$val.']';
				}
				$graph['items_count']='label:"'.__('items sold','wppizza-locale').'",data:['.implode(',',$grItemsCount).'], yaxis: 3';

		/************************************
			make array to return
		*************************************/
		$data=array();
		$data['currency']=$reportCurrency;
		$data['dataset']=$datasets;
		$data['graphs']=array('data'=>$graph,'label'=>$graphLabel,'hoverOffsetTop'=>$hoverOffsetTop,'hoverOffsetLeft'=>$hoverOffsetLeft,'series'=>array('lines'=>$serieslines,'bars'=>$seriesbars,'points'=>$seriespoints));
		$data['boxes']=$box;
		$data['boxesrt']=$boxrt;
		$data['reportTypes']=$reportTypes;
		$data['view']=($overview && !$customrange) ? 'ini' : 'custom';

	return $data;
	}

	function wppizza_report_mkquery($wpdbPrefix,$orderTable,$oQuery){
		$ordersQuery="SELECT id,order_date as oDate ,UNIX_TIMESTAMP(order_date) as order_date, order_ini, initiator, order_status FROM ".$wpdbPrefix . $orderTable." WHERE payment_status IN ('COD','COMPLETED') ";//COD is really just for legacy as it should alwasy be COMPLETED these days
		$ordersQuery.= $oQuery;
		$ordersQuery.='ORDER BY order_date ASC';

		return $ordersQuery;
	}

	function wppizza_report_export($dataset){
		
		/* export your own report if you want */
		do_action('wppizza_custom_report', $dataset);
		
				
		$wpTime=current_time('timestamp');
		$filename[]=date('Y.m.d',$wpTime);
		/*add range**/
		if(isset($_GET['from']) && isset($_GET['to'])){
			$filename[]='-[';
			$filename[]=esc_sql(str_replace("-",".",$_GET['from']));
			$filename[]='-';
			$filename[]=esc_sql(str_replace("-",".",$_GET['to']));
			$filename[]=']';
		}else{
			if(isset($_GET['name'])){
				$filename[]='-'.esc_sql(str_replace(" ","_",$_GET['name']));
			}
		}
		/*filter if you want*/
		$filename = apply_filters('wppizza_filter_report_export_title', $filename);
		$filename=implode("",$filename);

		$delimiter=',';
		$encoding='base64';
		$mime='text/csv; charset='.WPPIZZA_CHARSET.'';
		$extension='.csv';

		/**get first and last date*/
		$d=0;
		foreach($dataset['sales'] as $date=>$order){
			if($d==0){
				$startdate=$date;
			}else{
				$enddate=$date;
			}
		$d++;
		}
		/**in case start and end are the same**/
		$enddate=empty($enddate) ? $startdate : $enddate;


		/**************************************************************************
			sales by date
		**************************************************************************/
		$result='"'.__('Range:','wppizza-locale').' '.$startdate.' - '.$enddate.'"'.PHP_EOL.PHP_EOL;
		$result.='"'.__('sales by dates','wppizza-locale').'"'.PHP_EOL;
		/*sales*/
		$result.='"'.__('date','wppizza-locale').'", "'.__('sales value(incl. taxes, charges and discounts)','wppizza-locale').'", "'.__('items order value','wppizza-locale').'", "'.__('number of sales','wppizza-locale').'", "'.__('number of items sold','wppizza-locale').'"  , "'.__('tax on order','wppizza-locale').'"  '.PHP_EOL;
		$d=0;
		/**sum it up*/
		$sales_value_total=0;
		$items_value_total=0;
		$sales_count_total=0;
		$items_count_total=0;
		$sales_order_tax=0;
		foreach($dataset['sales'] as $date=>$order){
			$result.=$date . $delimiter . $order['sales_value_total']  . $delimiter . $order['items_value_total'] . $delimiter . $order['sales_count_total'] . $delimiter . $order['items_count_total'] . $delimiter . $order['sales_order_tax'];
			$result.=PHP_EOL;

			/**add it up**/
			$sales_value_total+=$order['sales_value_total'];
			$items_value_total+=$order['items_value_total'];
			$sales_count_total+=$order['sales_count_total'];
			$items_count_total+=$order['items_count_total'];
			$sales_order_tax+=$order['sales_order_tax'];

		$d++;
		}
		/**sums of it all*/
		$result.='"", "'.__('total','wppizza-locale').'", "'.__('total','wppizza-locale').'", "'.__('total','wppizza-locale').'", "'.__('total','wppizza-locale').'", "'.__('total','wppizza-locale').'" '.PHP_EOL;
		$result.=''. $delimiter  . $sales_value_total  . $delimiter . $items_value_total . $delimiter . $sales_count_total . $delimiter . $items_count_total . $delimiter . $sales_order_tax;


		/**************************************************************************
			sales by item
		**************************************************************************/
		if(is_array($dataset['items_summary']) && count($dataset['items_summary'])>0){

			/*add some empty lines first*/
			$result.=PHP_EOL.PHP_EOL.PHP_EOL.PHP_EOL.PHP_EOL;
			$result.='"'.__('sales by item','wppizza-locale').'"'.PHP_EOL;
			/*items*/
			$result.='"'.__('quantity','wppizza-locale').'", "'.__('item','wppizza-locale').'", "'.__('total value','wppizza-locale').'"'.PHP_EOL;
			$totalNumberItems=0;
			$totalSalesItems=0;
			foreach($dataset['items_summary'] as $uniqueItem=>$itemDetails){
				$result.=$itemDetails['quantity']  . $delimiter . wppizza_report_decode_entities($itemDetails['name']) . $delimiter . $itemDetails['pricetotal'];
				$result.=PHP_EOL;
				/*add it up*/
				$totalNumberItems+=$itemDetails['quantity'];
				$totalSalesItems+=$itemDetails['pricetotal'];
			}

			/*add some empty lines */
			$result.=PHP_EOL;
			//irrelevant as already displayed
			//$result.='"total quantity all items", "",  "total value all items"'.PHP_EOL;
			//$result.=$totalNumberItems  . $delimiter . '' . $delimiter . $totalSalesItems;
		}

		/**************************************************************************
			sales value by gateway
		**************************************************************************/
		if(is_array($dataset['gateways_summary']) && count($dataset['gateways_summary'])>0 && !defined('WPPIZZA_OMIT_REPORT_GATEWAYS_SUMMARY')){
			$result.=PHP_EOL.'"'.__('payment type','wppizza-locale').'"'.PHP_EOL;	
			/*items*/
			$result.='"'.__('type','wppizza-locale').'", "'.__('total value','wppizza-locale').'"'.PHP_EOL;			
			foreach($dataset['gateways_summary'] as $uniqueGateway=>$gatewayValue){
				$result.=$uniqueGateway  . $delimiter . $gatewayValue;
				$result.=PHP_EOL;				
			}
			/*add some empty lines */
			$result.=PHP_EOL;
		}

		/**************************************************************************
			sales value by order status
		**************************************************************************/
		if(is_array($dataset['order_status_summary']) && count($dataset['order_status_summary'])>0 && !defined('WPPIZZA_OMIT_REPORT_ORDER_STATUS_SUMMARY')){
			$result.=PHP_EOL.'"'.__('order status','wppizza-locale').'"'.PHP_EOL;	
			/*items*/
			$result.='"'.__('status','wppizza-locale').'", "'.__('count','wppizza-locale').'", "'.__('total value','wppizza-locale').'"'.PHP_EOL;			
			foreach($dataset['order_status_summary'] as $uniqueStatus=>$statusValue){
				$result.=$uniqueStatus  . $delimiter . $statusValue['count'] . $delimiter . $statusValue['value'];
				$result.=PHP_EOL;				
			}
			/*add some empty lines */
			$result.=PHP_EOL;
		}


		/**************************************************************************
			write to file
		**************************************************************************/
		$filename = 'wppizza_report_'.$filename.''.$extension.'';
		header("Content-Encoding: ".WPPIZZA_CHARSET."");		
		header("Content-Type: ".$mime."");
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header("Content-Length: " . strlen($result));
		echo $result;
		exit();
	}

?>