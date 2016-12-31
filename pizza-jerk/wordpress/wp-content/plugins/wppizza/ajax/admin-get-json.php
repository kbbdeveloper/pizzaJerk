<?php
if(!defined('DOING_AJAX') || !DOING_AJAX){
	header('HTTP/1.0 400 Bad Request', true, 400);
	print"you cannot call this script directly";
  exit; //just for good measure
}
/**testing variables ****************************/
//sleep(2);//when testing jquery fadeins etc
/******************************************/
/**********get options********************/
$options=$this->pluginOptions;
/******************************************
	[supress errors unless debug]
******************************************/
$wppizzaDebug=wppizza_debug();
if(!$wppizzaDebug){
	error_reporting(0);
}

// removed as i dont think this is actually in use anywhere here ?!
//$optionSizes=wppizza_sizes_available($options['sizes']);//outputs an array $arr=array(['lbl']=>array(),['prices']=>array());

$output='';

/*****************************************************************************************************************
*
*
*
*
*
*****************************************************************************************************************/
	/*****************************************************
		[adding new additive]
	*****************************************************/
	if($_POST['vars']['field']=='additives' && $_POST['vars']['id']>=0){
		$output=$this->wppizza_admin_section_additives($_POST['vars']['field'],$_POST['vars']['id'],'');
	}
	/*****************************************************
		[adding new custom opening time]
	*****************************************************/
	if($_POST['vars']['field']=='opening_times_custom'){
		$output=$this->wppizza_admin_section_opening_times_custom($_POST['vars']['field']);
	}
	/*****************************************************
		[adding new times closed]
	*****************************************************/
	if($_POST['vars']['field']=='times_closed_standard'){
		$output=$this->wppizza_admin_section_times_closed_standard($_POST['vars']['field']);
	}
	/*****************************************************
		[adding new size selection options]
	*****************************************************/
	if($_POST['vars']['field']=='sizes' && $_POST['vars']['id']>=0 && isset($_POST['vars']['newFields']) && $_POST['vars']['newFields']>0){
		$output=$this->wppizza_admin_section_sizes($_POST['vars']['field'],$_POST['vars']['id'],$_POST['vars']['newFields']);
	}
	/*****************************************************
		[order history -> delete abandoned orders]
	*****************************************************/
	if($_POST['vars']['field']=='delete_abandoned_orders'){
		global $wpdb;
		$days=0;
		if((int)$_POST['vars']['days']>=1){
			$days=(int)$_POST['vars']['days'];
		}
		/**do or dont delete all non completed orders**/
			$pStatusQuery=" IN ('INITIALIZED','CANCELLED')";
		if($_POST['vars']['failed']=='true'){
			$pStatusQuery=" NOT IN ('COMPLETED','PENDING','REFUNDED','CAPTURED','COD','AUTHORIZED')";
		}
		$sql="DELETE FROM ".$wpdb->prefix . $this->pluginOrderTable." WHERE order_date < TIMESTAMPADD(DAY, %d, NOW()) AND payment_status ".$pStatusQuery." ";
		$res=$wpdb->query( $wpdb->prepare($sql, -$days));


		$output.="".__('Done', 'wppizza-locale')."";
	}
	/******************************************************
		[save sorted categories]
	******************************************************/
	if($_POST['vars']['field']=='cat_sort'){
		$debug = array();

		/*
			WP might show more categories on a page than set in "Screen options: Number of items per page"
			when there are nested subcategories. So, when paging, we need to chunk it into 3 chunks: before,
			current and after taking into account categories visible, to set sortorder appropriately
		*/
		/*current category sort order*/
		$currrent_sort_hierarchy=$options['layout']['category_sort_hierarchy'];


		/*if we do not have one yet, use default sort order*/
		if(count($currrent_sort_hierarchy)<=0){
			$currrent_sort_hierarchy=$this->wppizza_get_cats_hierarchy();
		}

		/**currently displayed category id's on that particular page (this might only be a part of all cats if paged)*/
		$order = explode(',', $_POST['vars']['order']);
		$debug['order'] = $order;
		$debug['currrent_sort_hierarchy'] = $currrent_sort_hierarchy;

		$order_chunk=array();
		$last_element_in_chunk_id=-1;/*we need to account for new categories being added to a paged page and then drag and drop resorted*/
		foreach ($order as $sort=>$id) {
			$catid=(int)str_replace("tag-","",$id);
			$order_chunk[$catid]=$sort;
			$last_element_in_chunk_sort_id=$currrent_sort_hierarchy[$catid];
		}

		/**chunk into before, to sort and after */
		$category_chunks=array();
		foreach($currrent_sort_hierarchy as $key=>$sort){
			if(isset($order_chunk[$key])){
				$category_chunks['sort'][$key]=$order_chunk[$key];
			}
			if(!isset($order_chunk[$key])){
				/*
					accounting for newly added categories on a paged page
					where the actual sortorder would be in a paged page
					BEFORE the current one
				*/
				if($sort<$last_element_in_chunk_sort_id){
					$category_chunks['before'][$key]=$sort;
				}
				/*
					accounting for newly added categories on a paged page
					where the actual sortorder would be in a paged page
					AFTER the current one
				*/
				if($sort>$last_element_in_chunk_sort_id){
					$category_chunks['after'][$key]=$sort;
				}
			}
		}

		/***************loop through chunks, only re-sorting cats on current page****************************/
		$new_cat_sort=array();
		$sorter=0;
		/*before current*/
		if(isset($category_chunks['before']) && is_array($category_chunks['before'])){
			//$new_cat_sort+=$category_chunks['before'];
			//$sorter+=count($category_chunks['before']);
			/*safer to loop to make sure we do not have non identical sort ids*/
			foreach($category_chunks['before'] as $catId=>$true){
				$new_cat_sort[$catId]=$sorter;
				$sorter++;
			}
		}
		/*current, to sort*/
		if(isset($category_chunks['sort']) && is_array($category_chunks['sort'])){
			asort($category_chunks['sort']);
			foreach($category_chunks['sort'] as $catId=>$true){
				$new_cat_sort[$catId]=$sorter;
				$sorter++;
			}
		}

		/*after current*/
		if(isset($category_chunks['after']) && is_array($category_chunks['after'])){
			//$new_cat_sort+=$category_chunks['after'];
			//$sorter+=count($category_chunks['after']);
			/*safer to loop to make sure we do not have non identical sort ids*/
			foreach($category_chunks['after'] as $catId=>$true){
				$new_cat_sort[$catId]=$sorter;
				$sorter++;
			}
		}

		$newOptions['layout']['category_sort_hierarchy']=$new_cat_sort;
		$newOptions['layout']['category_sort']=$new_cat_sort;/*legacy*/

		/*allow other plugins to hook into this**/
		$newOptions['layout']=apply_filters('wppizza_on_category_sort', $newOptions['layout']);

		update_option( $this->pluginSlug, $newOptions );

		/** output to console if we want */
		print"".json_encode($debug)."";

	die(1);
	}
	/******************************************************
		[adding a new meal category->add column selection]
		[TO CHECK: i dont think this does anything as function wppizza_admin_section_category does not seem to exist anywhere]
	******************************************************/
	if($_POST['vars']['field']=='meals' && !isset($_POST['vars']['item']) && $_POST['vars']['id']>=0){
		$output=$this->wppizza_admin_section_category($_POST['vars']['field'],$_POST['vars']['id']);
	}
	/******************************************************
		[adding a new meal to category]
		[TO CHECK: i dont think this does anything as function wppizza_admin_section_category does not seem to exist anywhere]
	******************************************************/
	if($_POST['vars']['field']=='meals' && isset($_POST['vars']['item']) && $_POST['vars']['id']>=0 && $_POST['vars']['newKey']>=0){
		$output=$this->wppizza_admin_section_category_item($_POST['vars']['field'],$_POST['vars']['id'],false,$_POST['vars']['newKey'],false,$options);
	}
	/******************************************************
		[prize tier selection has been changed->add relevant price options (and sku if applicable) input fields]
	******************************************************/
	if($_POST['vars']['field']=='sizeschanged' && $_POST['vars']['id']!='' && isset($_POST['vars']['inpname']) &&  $_POST['vars']['inpname']!=''){
		$set_size_id=(int)$_POST['vars']['id'];
		$is_metabox=($_POST['vars']['metabox']==1) ? true : false;

		/**sizes**/
		$sizes='';
		if(is_array($options['sizes'][$set_size_id])){
			foreach($options['sizes'][$set_size_id] as $a=>$b){
				/*if we change the ingredient pricetier, do not use default prices , but just empty ??? what is this doing and why is it here ??? need to find out at some point**/
				if(isset($_POST['vars']['classId']) && $_POST['vars']['classId']=='ingredients'){$price='';}else{$price=$b['price'];}
				$sizes.="<input name='".$_POST['vars']['inpname']."[prices][]' type='text' size='5' value='".$price."'>";
		}}
		$obj['inp']['sizes']=$sizes;
		$obj['element']['sizes']='.wppizza_pricetiers';/**html element empty and replace with new input boxes**/

		/**allow other meta boxes to hook into - currenlty sku's */
		$obj=apply_filters('wppizza_ajax_action_admin_sizeschanged', $obj, $set_size_id, $is_metabox);

		print"".json_encode($obj)."";
		exit();
	}
	/******************************************************
		[tools - get php info]
	******************************************************/
	if($_POST['vars']['field']=='get-php-vars'){
		ob_start();
		phpinfo(INFO_CONFIGURATION);
		//phpinfo(INFO_GENERAL);
		//phpinfo(INFO_ENVIRONMENT);
		//phpinfo(INFO_VARIABLES);

		preg_match ('%<style type="text/css">(.*?)</style>.*?(<body>.*</body>)%s', ob_get_clean(), $matches);

		# $matches [1]; # Style information
		# $matches [2]; # Body information

		echo "<div class='phpinfodisplay'><style type='text/css'>\n",
		    join( "\n",
		        array_map(
		            create_function(
		                '$i',
		                'return ".phpinfodisplay " . preg_replace( "/,/", ",.phpinfodisplay ", $i );'
		                ),
		            preg_split( '/\n/', $matches[1] )
		            )
		        ),
		    "</style>\n",
		    $matches[2],
		    "\n</div>\n";
		exit();
	}
/********************************************************************************************************************************************************************
*
*
*
*
*	[order history things]
*
*
*
*
********************************************************************************************************************************************************************/

	/*************************************************************************************
	*
	*
	*
	*	[show/get orders wppizza->order history]
	*	TODO - USE order details class
	*
	*
	***********************************************************************************/
	if($_POST['vars']['field']=='get_orders'){
		/*get some global wp vars**/
		global $wpdb,$blog_id;
		/*ini markup string*/
		$markup='';
		/**ini output array*/
		$output=array();
		/**what status are we returning**/
		$inStatus=array();
		$inStatus[]='COD';
		$inStatus[]='COMPLETED';
		$inStatus[]='REFUNDED';
		/*include failed orders*/
		if(!empty($this->pluginOptions['plugin_data']['admin_order_history_include_failed'])){
			$inStatus[]='FAILED';
		}
		$inStatus="'".implode("','",$inStatus)."'";


		/*ini total price*/
		$totalPriceOfShown=0;
		/*get selected limit*/
		if($_POST['vars']['limit']>0){$limit=' limit 0,'.(int)$_POST['vars']['limit'].'';}else{$limit='';}
		/*get selected order status to show */
		if($_POST['vars']['orderstatus']!=''){$orderstatus=' AND order_status="'.$_POST['vars']['orderstatus'].'" ';}else{$orderstatus='';}


		/****************************************************
		*
		*	[multisite only and if enabled in wppizza->settings]
		*	get *all* subsites orders (in parent site only)]
		*
		***************************************************/
		if ( is_multisite() && $blog_id==BLOG_ID_CURRENT_SITE && $options['plugin_data']['wp_multisite_order_history_all_sites']) {
			/*ini array*/
			$allOrders=array();

	 	   	/*get all and loop through blogs*/
	 	   	$blogs = $wpdb->get_results("SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A);


	 	   		if ($blogs) {
	        	foreach($blogs as $blog) {
	        		switch_to_blog($blog['blog_id']);
	        			/*make sure plugin is active*/
	        			if(is_plugin_active('wppizza/wppizza.php')){
	        				/*
	        					get blogid and name to add to object
	        					if blogid==1 omit to be able to select right table
	        					as it won't have the 1_ prefix
	        				*/
							$blogId=($blog['blog_id']==1) ? '' : $blog['blog_id'] ;
							$blogName=get_bloginfo('name');
							/************************
								[make and run query]
								dont bother with "order by" here
								as we have to resort on order date anyway
							*************************/
							/*set order for easier sorting*/
							$select=array();
							$select['order_date']='order_date';
							$select['id']='id';
							$select['wp_user_id']='wp_user_id';
							$select['order_update']='order_update';
							$select['customer_details']='customer_details';
							$select['order_details']='order_details';
							$select['order_status']='order_status';
							$select['hash']='hash';
							$select['order_ini']='order_ini';
							$select['customer_ini']='customer_ini';
							$select['payment_status']='payment_status';
							$select['transaction_id']='transaction_id';
							$select['transaction_details']='transaction_details';
							$select['transaction_errors']='transaction_errors';
							$select['initiator']='initiator';
							$select['mail_sent']='mail_sent';
							$select['mail_error']='mail_error';
							$select['notes']='notes';
							$select['user_data']='user_data';
							$setSelect=implode(',',$select);

							$allOrdersQuery = "SELECT ".$setSelect." FROM ".$wpdb->prefix . $this->pluginOrderTable." WHERE payment_status IN (".$inStatus.") ".$orderstatus." ORDER BY order_date DESC ".$limit." ";


							/*filter if you want*/
							$allOrdersQuery = apply_filters('wppizza_filter_orderhistory_ordersquery', $allOrdersQuery );

							$theseOrders = $wpdb->get_results($allOrdersQuery);




							$blogOrders=array();
							if(is_array($theseOrders)){
							foreach($theseOrders as $key=>$order){
								$blogOrders[$key]=array();
								//$blogOrders[$key]->date_sort=$order->order_date;/*add date furst for sorting*/
								$blogOrders[$key]=$order;
								/**add blog id and blog name to object*/
								$blogOrders[$key]->blogId=$blogId;
								$blogOrders[$key]->blogName=$blogName;
							}}

							/**merge array**/
							$allOrders=array_merge($allOrders,$blogOrders);

	        			}
	        		restore_current_blog();
	        	}}


				/**sort by date in reverse (by order date) and truncate to $limit set*/
				arsort($allOrders);

				$allOrders = array_slice($allOrders, 0, (int)$_POST['vars']['limit']);


		}
		/****************************************************
		*
		*	[standard, single site or if multisite has NOT enabled
		*	"History all subsites" in wppizza->settings]
		*
		***************************************************/
		else{
			$allOrdersQuery = "SELECT * FROM ".$wpdb->prefix . $this->pluginOrderTable." WHERE payment_status IN (".$inStatus.") ".$orderstatus." ORDER BY order_date DESC ".$limit." ";
			/*filter if you want*/
			$allOrdersQuery= apply_filters('wppizza_filter_orderhistory_ordersquery', $allOrdersQuery );

			$allOrders = $wpdb->get_results($allOrdersQuery);
		}
		/***allow filtering of results too***/
		$allOrders = apply_filters('wppizza_filter_orderhistory_orders', $allOrders );


		/****************************************************
		*
		*	check if there are orders and if so do loop
		*
		***************************************************/
		/********************
			if we have orders
		********************/
		if(is_array($allOrders) && count($allOrders)>0){


			/*get any -perhaps filtered - customised order status */
			$customOrderStatus=wppizza_custom_order_status();
			$customOrderStatusGetTxt=wppizza_order_status_default();


			/*admin only notice if able to delete order*/
			if (current_user_can('wppizza_cap_delete_order')){
				$output['notice_delete']="<div>".__('Note: deleting an order will <b>ONLY</b> delete it from the database table. It will <b>NOT</b> issue any refunds, cancel the order, send emails etc.', 'wppizza-locale')."</div>";
			}
			/*notice regarding status change*/
			$output['notice_info']="<div style='color:red'>".__('"Status" is solely for your internal reference. Updating/changing the value will have no other effects but might help you to identify which orders have not been processed.', 'wppizza-locale')."</div>";



			/********************************************************************************************
			*
			*	[TABLE OPEN]
			*
			********************************************************************************************/
			$output['table_open']="<table>";

				/****************************************************************************
					[header row]
				****************************************************************************/
				$output['header']="<tr class='wppizza-orders-head'>";

					$header['column_order']="<td>";
						$header['column_order'].="".__('Order', 'wppizza-locale')."";
					$header['column_order'].="</td>";

					$header['column_customer']="<td>";
						$header['column_customer'].="".__('Customer Details', 'wppizza-locale')."";
					$header['column_customer'].="</td>";

					$header['column_details']="<td>";
						$header['column_details'].="".__('Order Details', 'wppizza-locale')."";
					$header['column_details'].="</td>";

					$header['column_empty']="<td>";
						$header['column_empty'].="&nbsp;";
					$header['column_empty'].="</td>";

					/**allow header filtering**/
					$header= apply_filters('wppizza_filter_orderhistory_header', $header );
					$output['header'].=implode('',$header);

				$output['header'].="</tr>";

				/********************
					loop
				********************/
				foreach ( $allOrders as $oKey=>$orders ){
					/**ini array*/
					$thisOrder=array();
					/**ini popup parts*/
					$thisOrderPopup=array();
					/*unserialized customer data*/
					$customerDet=maybe_unserialize($orders->customer_ini);
					/*unserialized order data*/
					$orderDet=maybe_unserialize($orders->order_ini);
					/*order status*/
					$orderStatus=strtolower($orders->order_status);
					/*order status*/
					$paymentStatus=strtolower($orders->payment_status);
					/**add to total ordered amount of shown items**/
					$totalPriceOfShown+=(float)$orderDet['total'];
					/**
						create unique id/key (if using multisite)
						as id's could be the same if pulled from 2 blogs
					**/
					$uoKey='';
					$blogid='';
					if(isset($orders->blogId) && $orders->blogId!=''){
						$blogid=$orders->blogId;
						$uoKey.=''.$orders->blogId.'_';
					}
					$uoKey.=$orders->id;


					/***************************************************************
					*
					*	payment faild (if enabled in tools)
					*
					****************************************************************/
					if($paymentStatus=='failed'){
						$errors_transaction_details='<strong>'.__('transaction details (if available) :','wppizza-locale').'</strong><br/><br/>'.nl2br(print_r(maybe_unserialize($orders->transaction_details),true));
						$errors_transaction_errors='<strong>'.__('transaction errors (if any) :','wppizza-locale').'</strong><br/><br/>'.nl2br(print_r(maybe_unserialize($orders->transaction_errors),true));
						$errors_mail_error='<strong>'.__('mail errors (if any) :','wppizza-locale').'</strong><br/><br/>'.nl2br(print_r(maybe_unserialize($orders->mail_error),true));
						$errors_user_data='<strong>'.__('user data (if available):','wppizza-locale').'</strong><br/><br/>'.nl2br(print_r(maybe_unserialize($orders->user_data),true));


						$thisOrder['paymentfailed']="<tr id='wppizza-order-".$paymentStatus."-".$uoKey."' class='wppizza-order-".$paymentStatus."'>";
							$thisOrder['paymentfailed'].="<td colspan='4'>";
								$thisOrder['paymentfailed'].="<h4 id='wppizza-order-".$paymentStatus."-toggle-details-".$uoKey."' class='wppizza-order-".$paymentStatus."-toggle-details'>". $orders->transaction_id . " : ".__('transaction failed! click here for details','wppizza-locale')."</h4>";
								$thisOrder['paymentfailed'].="<div id='wppizza-order-".$paymentStatus."-details-".$uoKey."' class='wppizza-order-".$paymentStatus."-details'>";
									$thisOrder['paymentfailed'].="<div class='wppizza_order_".$paymentStatus."_details'>". $errors_transaction_details ."</div>";
									$thisOrder['paymentfailed'].="<div class='wppizza_order_".$paymentStatus."_details'>". $errors_transaction_errors ."</div>";
									$thisOrder['paymentfailed'].="<div class='wppizza_order_".$paymentStatus."_details'>". $errors_mail_error ."</div>";
									$thisOrder['paymentfailed'].="<div class='wppizza_order_".$paymentStatus."_details'>". $errors_user_data ."</div>";
								$thisOrder['paymentfailed'].="</div>";
							$thisOrder['paymentfailed'].="</td>";
						$thisOrder['paymentfailed'].="</tr>";
					}

					/****************************************************************************
					*
					*	[start first row -> regular order info]
					*
					****************************************************************************/

					$thisOrder['main_tr_open']="<tr  id='wppizza-order-tr-".$uoKey."' class='wppizza-order-tr wppizza-ord-status-".$orderStatus."'>";

						/***************************************************************
						*
						*	first row, first column,
						*	order info (id, transaction id etc)
						*
						****************************************************************/
						$orderinfo=array();/*reset*/
						$orderinfo['tdopen']="<td style='white-space:nowrap'>";

							/************************
							*
							*	add some hidden inputs to be able to correctly
							*	identify id's etc via js if required
							*
							************************/
								/**order id**/
								$orderinfo['hiddeninput_orderid']="<input type='hidden' id='wppizza_order_id_".$uoKey."' value='".$orders->id ."' />";
								/**order date**/
								$orderinfo['hiddeninput_orderdate']="<input type='hidden' id='wppizza_order_date_".$uoKey."'  class='wppizza_order_date' value='".date("d-M-Y H:i:s",strtotime($orders->order_date)) ."' />";
								/**blog id, blog name**/
								if(is_multisite() && isset($orders->blogName)){
									$orderinfo['hiddeninput_blogid']="<input type='hidden' id='wppizza_order_blogid_".$uoKey."' class='wppizza_order_blogid' value='".$orders->blogId ."' />";
									$orderinfo['hiddeninput_blogname']="<input type='hidden' id='wppizza_order_blogname_".$uoKey."' value='".$orders->blogName ."' />";
								}else{
									$orderinfo['hiddeninput_blogid']="<input type='hidden' id='wppizza_order_blogid_".$uoKey."'  class='wppizza_order_blogid' value='".get_current_blog_id() ."' />";
									$orderinfo['hiddeninput_blogname']="<input type='hidden' id='wppizza_order_blogname_".$uoKey."' value='".get_bloginfo('name') ."' />";
								}

							/************************
							*
							*	output
							*
							************************/
								/**multisite, blog info if exists, appropriate*/
								if(isset($orders->blogName)){
									$orderinfo['blogname']= '<b>'.$orders->blogName.'</b><br />';
								}

								/** order date**/
									$orderinfo['date']= date("d-M-Y H:i:s",strtotime($orders->order_date));

								/**
									get used gateway label
									hidden variables only used for old style
									order printing
								**/
								if($orders->initiator!=''){
									/**get label from gateway class**/
									$gwIdent=$orders->initiator;
									$gatewayClassname='WPPIZZA_GATEWAY_'.$orders->initiator;
									if (class_exists(''.$gatewayClassname.'')) {
										$gw=new $gatewayClassname;
										if($gw->gatewayOptions['gateway_label']!=''){
										$gwIdent=$gw->gatewayOptions['gateway_label'];
										}
									}
									/*old style order printing**/
									if($options['plugin_data']['use_old_admin_order_print']){
										$orderinfo['hiddeninput_payment']="<input type='hidden' id='wppizza_order_initiator_".$uoKey."' value='".__('Payment By', 'wppizza-locale').": ". $gwIdent ."' />";
										$orderinfo['hiddeninput_payment'].="<input type='hidden' id='wppizza_order_initiator_ident_".$uoKey."' value='". $gwIdent ."' />";
									}
									$orderinfo['payment']="<br />".__('Payment By', 'wppizza-locale').": ". $gwIdent ."";
								}

								/**
									print transaction id
									hidden variables
								**/
								if($orders->transaction_id!=''){
									$orders->transaction_id = apply_filters('wppizza_filter_transaction_id', $orders->transaction_id, $orders->id );
									$orderinfo['hiddeninput_txid']="<input type='hidden' id='wppizza_order_transaction_id_".$uoKey."' class='wppizza_order_transaction_id' value='ID: ". $orders->transaction_id ."' />";
									$orderinfo['transaction_id']="<br /><span>".$orders->transaction_id."</span>";
								}

								/**
									print order status dropdown
								**/
								$orderinfo['status']="<br />";
								$orderinfo['status'].="<label>".__('Status', 'wppizza-locale')."";
								$orderinfo['status'].="<select id='wppizza_order_status-".$uoKey."' name='wppizza_order_status-".$uoKey."' class='wppizza_order_status'>";
								foreach($customOrderStatus as $s){
									if(isset($customOrderStatusGetTxt[$s])){/*get translation if we have any*/
										$lbl=$customOrderStatusGetTxt[$s];
									}else{
										$lbl=$s;
									}
									$orderinfo['status'].="<option value='".$s."' ".selected($orders->order_status,$s,false).">".$lbl."</option>".PHP_EOL;
								}
								$orderinfo['status'].="</select>";
								$orderinfo['status'].="</label>";

								/**
									print last update
									or order date if not set
								**/
								$orderinfo['last_update']="<br />";
								$orderinfo['last_update'].="".__('Last Status Update', 'wppizza-locale').":<br />";
								$orderinfo['last_update'].="<span id='wppizza_order_update-".$uoKey."'>";
								if($orders->order_update!='0000-00-00 00:00:00'){
									$orderinfo['last_update'].= date("d-M-Y H:i:s",strtotime($orders->order_update));
								}else{
									$orderinfo['last_update'].= date("d-M-Y H:i:s",strtotime($orders->order_date));
								}
								$orderinfo['last_update'].="</span>";



								/**
									popup customer details
								**/
								/*open pre*/
								$thisOrderPopup['pre_details']='<pre class="wppizza_order_popup">';
								/**popup customer details**/
								//$thisOrderPopup['customer_details']='<span>'.str_replace(PHP_EOL,'</span>'.PHP_EOL.'<span>',$orders->customer_details).'</span>';//if we want to use spans ..hmm.maybe
								$thisOrderPopup['customer_details']=$orders->customer_details;
								/**popup order details**/
								$thisOrderPopup['order_details']=$orders->order_details;
								/**popup notes details**/
								if(trim($orders->notes)!=''){
									$thisOrderPopup['notes']=$orders->notes;
								}
								/*close pre*/
								$thisOrderPopup['post_details']='</pre>';

								/**add buttons/dropdown*/
								$thisOrderPopup['status']='<div class="wppizza_order_status_popup_wrap">';
								$thisOrderPopup['status'].="<label>".__('Status', 'wppizza-locale')." ";
								$thisOrderPopup['status'].="<select id='wppizza_order_status_popup-".$uoKey."' name='wppizza_order_status_popup-".$uoKey."' class='wppizza_order_status_popup'>";
								foreach($customOrderStatus as $s){
									if(isset($customOrderStatusGetTxt[$s])){/*get translation if we have any*/
										$lbl=$customOrderStatusGetTxt[$s];
									}else{
										$lbl=$s;
									}
									$thisOrderPopup['status'].="<option value='".$s."' ".selected($orders->order_status,$s,false).">".$lbl."</option>".PHP_EOL;
								}
								$thisOrderPopup['status'].="</select></label>";
								$thisOrderPopup['status'].="</div>";


								/*filter of you want*/
								$thisOrderPopup = apply_filters('wppizza_filter_order_popup', $thisOrderPopup, $orders, $blogid, $orders->order_date);
								/*implode for output*/
								$thisOrderPopup=implode(PHP_EOL.PHP_EOL,$thisOrderPopup);
								$orderinfo['popup']="<div style='display:none' id='wppizza_order_popup_".$uoKey."'>".$this->wppizza_force_plaintext_wordwrap($thisOrderPopup) ."</div>";



						$orderinfo['tdclose']="</td>";

						/**allow filtering**/
						$orderinfo= apply_filters('wppizza_filter_orderhistory_order_info', $orderinfo, $orders->id, $customerDet, $orderDet, $blogid, $orderStatus, $orders->order_date);
						$thisOrder['orderinfo']=implode('',$orderinfo);


						/***************************************************************
							first row, second column,
							customer details
						****************************************************************/
						$customer_details=array();/*reset*/
						$customer_details[]="<td>";
							$customer_details[]="<div id='wppizza_order_customer_details-".$uoKey."' class='wppizza_order_customer_details'><pre>". $this->wppizza_force_plaintext_wordwrap($orders->customer_details) ."</pre></div>";


							/**use media queries for smaller screens  display this below customer details*/
							$customer_details[]="<div id='wppizza_order_details_post_customer-".$uoKey."' class='wppizza_order_details_post_customer' ><pre>". $this->wppizza_force_plaintext_wordwrap($orders->order_details) ."</pre></div>";




						$customer_details[]="</td>";
						/**allow filtering**/
						$customer_details= apply_filters('wppizza_filter_orderhistory_customer_details', $customer_details, $orders->id, $customerDet, $orderDet, $blogid, $orderStatus, $orders->order_date);
						$thisOrder['customer_details']=implode('',$customer_details);

						/***************************************************************
							first row, third column,
							order details
						****************************************************************/
						$order_details=array();/*reset*/
						$order_details[]="<td>";
							$order_details[]="<div id='wppizza_order_details-".$uoKey."' class='wppizza_order_details' ><pre>". $this->wppizza_force_plaintext_wordwrap($orders->order_details) ."</pre></div>";
						$order_details[]="</td>";
						/**allow filtering**/
						$order_details= apply_filters('wppizza_filter_orderhistory_order_details', $order_details, $orders->id, $customerDet, $orderDet, $blogid, $orderStatus, $orders->order_date);
						$thisOrder['order_details']=implode('',$order_details);


						/***************************************************************
							first row, fourth column,
							delete, print, add notes
						****************************************************************/
						$actions=array();/*reset*/
						$actions['tdopen']="<td>";


							/******************************
							*
							*	delete order button [admin only]
							*
							******************************/
							if (current_user_can('wppizza_cap_delete_order')){
								$actions['delete']="<a href='#' id='wppizza-delete-order-".$uoKey."' class='wppizza_order_delete'>".__('delete', 'wppizza-locale')."</a>";
								$actions['deletebr']="<br/>";
							}
							/************************
							*
							*	print order button
							*
							************************/
							/*current version*/
							if(!$options['plugin_data']['use_old_admin_order_print']){
								$actions['print']="<a href='javascript:void(0);'  id='wppizza-print-order-".$uoKey."' class='wppizza-print-order button'>".__('print order', 'wppizza-locale')."</a>";
							}
							/*old style order printing using just the fields/textareas shown*/
							if($options['plugin_data']['use_old_admin_order_print']){
								$actions['print']="<a href='javascript:void(0);'  id='wppizza-print-order-".$uoKey."' class='wppizza-print-order-prev button'>".__('print order', 'wppizza-locale')."</a>";
							}

							/************************
							*
							*	add/edit notes button
							*
							************************/
								$actions['printbr']="<br />";
								/*set visibility*/
								if(trim($orders->notes)==''){$notesBtnSty='block;';}else{$notesBtnSty='none';}

								$actions['notes']="<a href='javascript:void(0);'  id='wppizza-order-add-notes-".$uoKey."' class='wppizza-order-add-notes button' style='display:".$notesBtnSty."'>".__('add notes', 'wppizza-locale')."</a>";

						$actions['tdclose']="</td>";
						/**allow filtering**/
						$actions= apply_filters('wppizza_filter_orderhistory_actions', $actions, $orders->id, $customerDet, $orderDet, $blogid, $orderStatus,  $orders->order_date );
						$thisOrder['actions']=implode('',$actions);

					$thisOrder['main_tr_close']="</tr>";


					/****************************************************************************
					*
					*
					*	[do second row -> order notes]
					*
					*
					****************************************************************************/
					$notes=array();/*reset*/
					/*set appropriate class*/
					if(trim($orders->notes)==''){$nbtrClass='wppizza-order-notes-tr';}else{$nbtrClass='wppizza-order-has-notes-tr';}

					$notes['tropen']="<tr id='".$nbtrClass."-".$uoKey."' class='".$nbtrClass."'>";
						$notes['tdopen']="<td colspan='4'>";
							$notes['textarea_notes']="<textarea id='wppizza-order-notes-".$uoKey."' class='wppizza-order-notes' placeholder='".__('notes:', 'wppizza-locale')."'>".$orders->notes."</textarea>";
							$notes['textarea_notes_ok']="<a href='javascript:void(0);'  id='wppizza-order-do-notes-".$uoKey."' class='wppizza-order-do-notes button'>".__('ok', 'wppizza-locale')."</a>";


							/***add hidden text id for popup*/

						$notes['tdclose']="</td>";
					$notes['trclose']="</tr>";

					/**allow filtering of notes **/
					$notes= apply_filters('wppizza_filter_orderhistory_notes', $notes, $orders->id, $customerDet, $orderDet, $blogid, $orderStatus,  $orders->order_date);
					/**add notes tr to output**/
					$thisOrder['notes']=implode('',$notes);


					/**********************************************
						allow filter of output parts
						in loop
					**********************************************/
					$thisOrder= apply_filters('wppizza_filter_orderhistory_loop_parts', $thisOrder , $orders->id, $customerDet, $orderDet, $blogid, $orderStatus,  $orders->order_date  );
					$output['order_'.$uoKey]=implode('',$thisOrder);

				}
				/***********************************************************************************
				*
				*	[END LOOP]
				*
				***********************************************************************************/

			$output['table_close']="</table>";



			/******************************************
				allow filter of output all output parts
			********************************************/
			$output= apply_filters('wppizza_filter_orderhistory_table', $output);
			$output=implode('',$output);
			/**add to markup*/
			$markup.=$output;

			/********************************************************************************************
			*
			*	[TABLE CLOSE]
			*
			********************************************************************************************/
		}
		/**we have no orders to display*/
		else{
			$markup.="<h1 style='text-align:center'>".__('no orders yet :(', 'wppizza-locale')."</h1>";
		}

		/*************************************************************************************************
		*
		*	[array of vars to return to js
		*
		*************************************************************************************************/
		/*orders html*/
		$obj['orders']=$markup;
		/*total value of DISPLAYED orders*/
		$obj['totals']=__('Total of shown orders', 'wppizza-locale').': '.$this->pluginOptions['order']['currency_symbol'].' '.wppizza_output_format_price($totalPriceOfShown).'';
		$obj['totals'].='<br /><a href="javascript:void(0)" id="wppizza_history_totals_getall">'.__('show total of all orders', 'wppizza-locale').'</a>';

		print"".json_encode($obj)."";
	exit();
	}
/*************************************************************************************************
*
*
*	[order history get totals ALL orders
*	(not just displayed ones)]
*
*
*************************************************************************************************/
	/**show get orders**/
	if($_POST['vars']['field']=='get_orders_total'){
		$totalPriceAll=0;
		global $wpdb;
		global $blog_id;
		/************************************************************************
			multisite install
			all orders of all sites (blogs)
			but only for master blog and if enabled (settings)
		************************************************************************/
		if ( is_multisite() && $blog_id==BLOG_ID_CURRENT_SITE && $options['plugin_data']['wp_multisite_order_history_all_sites']) {
			$allOrders=array();
	 	   	$blogs = $wpdb->get_results("SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A);
	 	   		if ($blogs) {
	        	foreach($blogs as $blog) {
	        		switch_to_blog($blog['blog_id']);
	        			/*make sure plugin is active*/
	        			if(is_plugin_active('wppizza/wppizza.php')){
							/************************
								[make and run query]
							*************************/
							$allOrdersQuery = $wpdb->get_results("SELECT order_ini FROM ".$wpdb->prefix . $this->pluginOrderTable." WHERE payment_status='COMPLETED' ");
							/**merge array**/
							$allOrders=array_merge($allOrdersQuery,$allOrders);
	        			}
					restore_current_blog();
	        	}}
		}else{
			$allOrders = $wpdb->get_results("SELECT order_ini FROM ".$wpdb->prefix . $this->pluginOrderTable." WHERE payment_status='COMPLETED' ");
		}

		if(is_array($allOrders) && count($allOrders)>0){
			foreach ( $allOrders as $orders ){
				/**add to total ordered amount of shown items**/
				$orderDet=maybe_unserialize($orders->order_ini);
				$totalPriceAll+=(float)$orderDet['total'];
				/*******************************************/
			}
		}

		$obj['totals']=__('total all orders', 'wppizza-locale').': '.$this->pluginOptions['order']['currency_symbol'].' '.wppizza_output_format_price($totalPriceAll).'';

		print"".json_encode($obj)."";
	exit();
	}
/********************************************
*
*
*	[order history -> update order status]
*
*
********************************************/
	if($_POST['vars']['field']=='orderstatuschange' && isset($_POST['vars']['id']) && $_POST['vars']['id']>=0){
		global $wpdb;
		$currentBlogId=get_current_blog_id();
		$selectedBlogId=!empty($_POST['vars']['blogid'] ) ? (int)$_POST['vars']['blogid'] : '';

		/**distinct blogid set and > 1 and not already same id/set **/
		if($selectedBlogId!='' && $selectedBlogId>1 && $currentBlogId!=$selectedBlogId){
			$wpdb->prefix=$wpdb->prefix.$_POST['vars']['blogid'].'_';
		}
		/****oder status***/
		$order_status=esc_sql($_POST['vars']['selVal']);
		/****update payment status too if set to refunded***/
		if($order_status=='REFUNDED'){
			$payment_status='REFUNDED';
		}else{
			/**set back payment status if not refunded to what it was**/
			$payment_status='COMPLETED';
		}

		/**
			update db including setting current time as order_update

			specifically set timestamp for people that can't ( or can't be bothered)
			to set the right timezone in the php.ini
		**/
		$ts=current_time('timestamp');
		$updateTimeStamp=date("Y-m-d H:i:s",$ts);

		$res=$wpdb->query("UPDATE ".$wpdb->prefix . $this->pluginOrderTable." SET order_status='".$order_status."', payment_status='".$payment_status."', order_update='".$updateTimeStamp."' WHERE id=".(int)$_POST['vars']['id']." ");

		$output= date("d-M-Y H:i:s",$ts);

		print"".$output."";
		exit();
	}
/*****************************************************
*
*
*	[order history -> delete order]
*
*
*****************************************************/
	if($_POST['vars']['field']=='delete_order'){
		global $wpdb;
		/**distinct blogid set and > 1 and not current blog**/
		if($_POST['vars']['blogid']!='' && $_POST['vars']['blogid']>1 && $_POST['vars']['blogid']!=get_current_blog_id()){
			$wpdb->prefix=$wpdb->prefix.$_POST['vars']['blogid'].'_';
		}
		$res=$wpdb->query( $wpdb->prepare( "DELETE FROM ".$wpdb->prefix . $this->pluginOrderTable." WHERE id=%s ",(int)$_POST['vars']['ordId']));
		$output.="".__('order deleted', 'wppizza-locale')."";
	}
/********************************************
*
*
*		[order history -> update notes]
*
*
********************************************/
	if($_POST['vars']['field']=='ordernoteschange' && isset($_POST['vars']['id']) && $_POST['vars']['id']>=0){
		global $wpdb;
		/**distinct blogid set and > 1 and not current blog**/
		if($_POST['vars']['blogid']!='' && $_POST['vars']['blogid']>1 && $_POST['vars']['blogid']!=get_current_blog_id()){
			$wpdb->prefix=$wpdb->prefix.$_POST['vars']['blogid'].'_';
		}
		/*add notes to db*/
		$notes=wppizza_validate_string($_POST['vars']['selVal']);
		$res=$wpdb->query("UPDATE ".$wpdb->prefix . $this->pluginOrderTable." SET notes='".$notes."' WHERE id=".(int)$_POST['vars']['id']." ");
		$output=strlen($notes);

		print"".$output."";
		exit();
	}
/*************************************************************************************
*
*
*	[order history -> print order]
*
*
*************************************************************************************/
	if($_POST['vars']['field']=='print-order' && $_POST['vars']['id']>=0){

		$orderId=(int)$_POST['vars']['id'];
		/*should never happen really*/
		if($orderId<=0){
			print"ERROR [ADMIN 1001]: invalid order id";
			exit();
		}
		require_once(WPPIZZA_PATH.'classes/wppizza.templates.inc.php');
		$templates=new WPPIZZA_TEMPLATES();
		$order=$templates->getOrderDetails($orderId, $_POST['vars']['blogid']);
		$type='print';
		$tplId=$options['templates_apply'][$type];

		/*get markup*/
		$markup=$templates->getTemplate($order, $type, $tplId, false, true);

		print"".json_encode($markup)."";
		exit();
	}
/*************************************************************************************
*
*
*	[templates]
*
*
*************************************************************************************/
	/*****************************************************
			[preview templates output]
	*****************************************************/
	if($_POST['vars']['field']=='preview_template'){

		/****************************************
			get the last completed order
			to use as preview
		****************************************/
		global $wpdb;
		$lastOrder = $wpdb->get_row("SELECT id FROM ".$wpdb->prefix . $this->pluginOrderTable." WHERE payment_status='COMPLETED' ORDER BY id DESC LIMIT 0,1 ");

		/****************************************
			no order exists that could be used
			as preview
		****************************************/
		if(!is_object($lastOrder) || $lastOrder->id <= 0){
			$markup['str']="".__('[ADMIN 1002] Preview Error: Sorry, you must have at least one completed order for the preview to work.','wppizza-locale');
			print"".json_encode($markup)."";
			exit();
		}

		/****************************************
			parse preview
			elements into array
		****************************************/
		/*type ? (email|print)*/
		$template_type=$_POST['vars']['data']['tplType'];
		/*id of template*/
		$template_id=$_POST['vars']['data']['tplId'];
		/*parse elements */
		$preview = array();
		parse_str($_POST['vars']['data']['templateElms'], $preview);//['templateElms']
		$preview=$preview[WPPIZZA_SLUG]['templates'][$template_type][$template_id];
		$preview['mail_type']=$_POST['vars']['data']['mail_type'];


		/*plaintext ?*/
		$plaintext=false;
		if($preview['mail_type']!='phpmailer'){
			$plaintext=true;
		}

		/******************************************
			let's check if we have anything selected
			to start off with. if not display that fact
		*****************************************/
		if(empty($preview['values'])){
			$markup['str']=__('you did not select anything to display ?!','wppizza-locale');
		}else{
			require_once(WPPIZZA_PATH.'classes/wppizza.templates.inc.php');
			$templates=new WPPIZZA_TEMPLATES();
			$order=$templates->getOrderDetails($lastOrder->id, get_current_blog_id(), $plaintext);
			$markup=$templates->getTemplate($order, $template_type, $template_id, $preview);
		}

		print"".json_encode($markup)."";
		exit();
	}
	/*****************************************************
			[adding a new message/template]
	*****************************************************/
	if($_POST['vars']['field']=='add_template'){
		/**********set header********************/
		header('Content-type: application/json');
		/*emails or print ?*/
		$template_type=$_POST['vars']['arrayKey'];

		/*get all current templates that already exist*/
		$nextKey=0;
		if(!empty($this->pluginOptions['templates'][$template_type])){
			$templates=$this->pluginOptions['templates'][$template_type];
			krsort($templates);//sort by key reverse
			reset($templates);
			$highestSetKey = key($templates);
			$nextKey=$highestSetKey+1+(int)$_POST['vars']['countNewKeys'];/*if we are adding more than one new one at the time, count them and add those to key id*/
		}

		require_once(WPPIZZA_PATH.'classes/wppizza.templates.inc.php');
		$templates=new WPPIZZA_TEMPLATES();
		$obj['markup']=$templates->getTemplateMarkupAdmin($nextKey, $template_type);

		print"".json_encode($obj)."";
		exit();
	}

	/*****************************************************
			[test smtp settings]
	*****************************************************/
	if($_POST['vars']['field']=='wppizza_smtp_test'){

		global $phpmailer; // define the global variable


		require_once(WPPIZZA_PATH.'classes/wppizza.send-emails.inc.php');
		$WPPIZZA_SEND_EMAILS=new WPPIZZA_SEND_EMAILS();
		/**attempt to send using entered smtp settings*/
		add_action('phpmailer_init', array($WPPIZZA_SEND_EMAILS, 'wppizza_smtp_send'));

		// Set up the mail variables
		$email=$_POST['vars']['wppizza_smtp_test_param']['smtp_email'];
		$subject = 'WPPizza SMTP Test: ' . __('Test mail to ', 'wppizza-locale') . $email;
		$message = __('WPPizza SMTP test email message', 'wppizza-locale');


		/**Start output buffering  for debugging output**/
		ob_start();

		/**Send test mail**/
		$mailResult = (wp_mail($email,$subject,$message)) ? true : false ;


		if(!$mailResult){
			$result['error'] = '<b style="color:red">FAIL - check details below</b>'.PHP_EOL;
			/*error info*/
			$result['error-info'] = array();

			/*last error*/
			$phpMailerLastError=error_get_last();
			if(!empty($phpMailerLastError)){
				$result['error-info']['last-error'] = $phpMailerLastError;
			}

			/*phpmailer errors*/
			$phpMailerError=$phpmailer->ErrorInfo;//. ' | '.$phpmailer->errorMessage;
			if(!empty($phpMailerError)){
				$result['error-info']['phpmailer']=''.print_r($phpMailerError,true).'';/**sometimes there's somthing in that variable too*/
			}
		}else{
			$result['success'] = '<b style="color:blue">SUCCESS - email sent. Check inbox of '.$email.' and do not forget to "Save Changes"</b>'.PHP_EOL;
		}

		/**smtp debugging output**/
		$result['debug'] = ob_get_clean();

		/**return array for display**/
		print"".print_r($result,true)."";
		exit();
	}
	/************************************************************************************************
	*
	*	[in case one wants to do/add more things in functions.php]
	*
	************************************************************************************************/
	do_action('wppizza_ajax_action_admin',$_POST);


print"".$output."";
exit();
?>