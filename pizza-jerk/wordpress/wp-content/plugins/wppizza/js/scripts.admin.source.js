/**only loaded when post_type == wppizza**/
jQuery(document).ready(function($){
	/*******************************************
	*	category edit page,
	*	make it sortable and update on new sort
	*******************************************/
	if(pagenow=='edit-wppizza_menu'){
		var wpPizzaCategories = $('#the-list');
		wpPizzaCategories.sortable({
			update: function(event, ui) {
				jQuery.post(ajaxurl , {action :'wppizza_admin_json',vars:{'field':'cat_sort', 'order': wpPizzaCategories.sortable('toArray').toString()}}, function(debug) {
					//console.log(debug);
				},'json').error(function(jqXHR, textStatus, errorThrown) {alert("error : " + errorThrown);});

			}
		});
	}
	/*******************************************
	*	[general/helper function]
	*	[create new key]
	********************************************/
	wpPizzaCreateNewKey = function(objId,btn){
		if(typeof btn!=='undefined'){
			btn.hide();/*disable*/
		}
		var self=$('#'+objId+' .wppizza-getkey');
		var currentInputs=self.get();
		/*make array if keys*/
		var keyIds = [];
			for (var i = 0; i < currentInputs.length; i++) {
				keyIds.push($(currentInputs[i]).attr("id").split("_").pop(-1));
			}
			var maxKey = Math.max.apply( null, keyIds );
			/*if none yet, start at zero**/
			if(maxKey<0){var newKey = 0;}else{var newKey = (maxKey+1);}
		return newKey;
	}
	/*******************************
	*	[time picker]
	*******************************/
    $('#wppizza-settings,.wppizza-settings').on('click', '.wppizza-time-select', function(e){
    	e.preventDefault();
    	$(this).timepicker({
    	hourText: 'Hour',
		minuteText: 'Min',
    	amPmText: ['', ''],
		hours: {
        starts: 0,                // First displayed hour
        ends: 23                  // Last displayed hour
    	},
    	minutes: {
    		starts: 0,                // First displayed minute
    		ends: 45,                 // Last displayed minute
    		interval: 15               // Interval of displayed minutes
		}}).timepicker( "show" );
    });
    /*******************************
	*	[date picker]
	*******************************/
    $('#wppizza-settings,.wppizza-settings').on('click', '.wppizza-date-select', function(e){
    	e.preventDefault();
    	$(this).datepicker({dateFormat : 'dd M yy'}).datepicker( "show" );
    });

    /*******************************
	*
	*
	*	[reports]
	*
	*
	*******************************/
    /*******************************
	*	[reports - date picker]
	*******************************/
	$(document).on('click', '#wppizza_reports_start_date,#wppizza_reports_end_date', function(e){
    	e.preventDefault();
    	$(this).datepicker({dateFormat : 'yy-mm-dd'}).datepicker( "show" );
    });
    /*******************************
	*	[reports - toggle best/worst]
	*******************************/
	$(document).on('click', '#wppizza-report-top10-volume>h3', function(e){
		$('#wppizza-report-top10-volume-ul').toggle();
		$('#wppizza-report-bottom10-volume-ul').toggle();
    });
	$(document).on('click', '#wppizza-report-top10-value>h3', function(e){
		$('#wppizza-report-top10-value-ul').toggle();
		$('#wppizza-report-bottom10-value-ul').toggle();
    });
	/******************************
	*	[reports - default options range select - onchange]
	******************************/
	$(document).on('change', '#wppizza-reports-set-range', function(e){
		var self=$(this);
		var selVal=self.val();
		var theUrl=window.location.href.split('?')[0];
		var redirUrl=theUrl+'?post_type=wppizza&page=wppizza-reports';
		if(selVal!=''){
			redirUrl+='&report=' + selVal;
		}
		window.location.href=redirUrl;
	});
	/******************************
	*	[reports - custom range]
	******************************/
	$(document).on('click', '#wppizza_reports_custom_range', function(e){
		var theUrl=window.location.href.split('?')[0];
		var redirUrl=theUrl+'?post_type=wppizza&page=wppizza-reports';
		var startDate=$('#wppizza_reports_start_date').val();
		var endDate=$('#wppizza_reports_end_date').val();
		if(startDate!='' && endDate!=''){
			redirUrl+='&from=' + startDate;
			redirUrl+='&to=' + endDate;
		}
		window.location.href=redirUrl;
	});
	/******************************
	*	[reports - export]
	******************************/
	$(document).on('click', '#wppizza_reports_export', function(e){
		var theUrl=window.location.href.split('?')[0];
		var redirUrl=theUrl+'?post_type=wppizza&page=wppizza-reports';
		var startDate=$('#wppizza_reports_start_date').val();
		var endDate=$('#wppizza_reports_end_date').val();
		var rangeTxt=$('#wppizza-reports-set-range :selected').text();
		var reportValue=$('#wppizza-reports-set-range').val();
		var rangeSet=false;
		if(startDate!='' && endDate!=''){
			rangeSet=true;
			redirUrl+='&from=' + startDate;
			redirUrl+='&to=' + endDate;
		}
		if(!rangeSet){
			redirUrl+='&name=' + encodeURIComponent(rangeTxt);
			redirUrl+='&report=' + encodeURIComponent(reportValue);
		}
			redirUrl+='&export=true';

		window.location.href=redirUrl;
	});
    /*******************************
	*
	*
	*	[reports - END]
	*
	*
	*******************************/
	/*******************************
		[opening times - add new]
	*******************************/
	$(document).on('click', '#wppizza_add_opening_times_custom', function(e){
		e.preventDefault();
		jQuery.post(ajaxurl , {action :'wppizza_admin_json',vars:{'field':'opening_times_custom'}}, function(response) {
			$('#wppizza_opening_times_custom_options').append(response);
		},'html').error(function(jqXHR, textStatus, errorThrown) {alert("error : " + errorThrown);});
	});
	/*******************************
		[times closed - add new]
	*******************************/
	$(document).on('click', '#wppizza_add_times_closed_standard', function(e){
		e.preventDefault();
		jQuery.post(ajaxurl , {action :'wppizza_admin_json',vars:{'field':'times_closed_standard'}}, function(response) {
			$('#wppizza_times_closed_standard_options').append(response);
		},'html').error(function(jqXHR, textStatus, errorThrown) {alert("error : " + errorThrown);});
	});
	/*******************************
	*	[size option - add new]
	*******************************/
	$(document).on('click', '#wppizza_add_sizes', function(e){
		e.preventDefault();var self=$(this);
		var newKey = wpPizzaCreateNewKey('wppizza_sizes_options',self);
			var newFields=parseInt($('#wppizza_add_sizes_fields').val());
			if(newFields>=1){
				jQuery.post(ajaxurl , {action :'wppizza_admin_json',vars:{'field':'sizes','id':newKey,'newFields':newFields}}, function(response) {
					var html=response;
					$('#wppizza_sizes_options').append(html);
					self.show();/*reenable add button*/
				},'html').error(function(jqXHR, textStatus, errorThrown) {alert("error : " + errorThrown);});
			}
	});
	/******************************
	*	[additives - add new]
	******************************/
	$(document).on('click', '#wppizza_add_additives', function(e){
		e.preventDefault();var self=$(this);
		var newKey = wpPizzaCreateNewKey('wppizza_additives_options',self);
		jQuery.post(ajaxurl , {action :'wppizza_admin_json',vars:{'field':'additives','id':newKey}}, function(response) {
			$('#wppizza_additives_options').append(response);
			self.show();/*reenable add button*/
		},'html').error(function(jqXHR, textStatus, errorThrown) {alert("error : " + errorThrown);});
	});
	/******************************
	*	[category - add new]
	*	[TO CHECK: i dont think this does anything as function called in request "wppizza_admin_section_category" does not seem to exist anywhere]
	******************************/
	$(document).on('click', '#wppizza_add_meals', function(e){
		e.preventDefault();var self=$(this);
		var newKey = wpPizzaCreateNewKey('wppizza_meals .wppizza_meals_category',self);
		jQuery.post(ajaxurl , {action :'wppizza_admin_json',vars:{'field':'meals','id':newKey}}, function(response) {
			var html='<span class="wppizza_option">';
			html+=response;
			html+='<div id="wppizza_category_items_'+newKey+'" class="wppizza_category_items"></div>';
			html+='</span>';
			$('#wppizza_meals_options').append(html);
			self.show();/*reenable add button*/
		},'html').error(function(jqXHR, textStatus, errorThrown) {alert("error : " + errorThrown);});
	});
	/******************************
	*	[menu/meal category - add new item to category]
	*	[TO CHECK: i dont think this does anything as function called in request "wppizza_admin_section_category" does not seem to exist anywhere]
	******************************/
	$(document).on('click', '.wppizza_add_meals_item', function(e){
		e.preventDefault();var self=$(this);
		var self=$(this);
		var CatId=self.attr('id').split("_").pop(-1);
		var newKey = wpPizzaCreateNewKey('wppizza_category_items_'+CatId+'',self);
		jQuery.post(ajaxurl , {action :'wppizza_admin_json',vars:{'field':'meals','item':1,'id':CatId,'newKey':newKey}}, function(response) {
			$('#wppizza_category_items_'+CatId+'').prepend(response);
			self.show();/*reenable add button*/
		},'html').error(function(jqXHR, textStatus, errorThrown) {alert("error : " + errorThrown);});
	});
	/******************************
	*	[pricetier select - onchange]
	******************************/
	$(document).on('change', '.wppizza_pricetier_select', function(e){
		var self=$(this);
		var selId=self.val();
		var fieldArray=self.attr('name').replace("[sizes]","");
		var classId=self.attr('class').split(" ").pop(-1);
		/**check if we are on menu item edit page with meta boxes**/
		var metabox=0;
		if(self.hasClass('wppizza_pricetier_select_meta')){
			metabox=1;
		}

		jQuery.post(ajaxurl , {action :'wppizza_admin_json',vars:{'field':'sizeschanged', 'id':selId, 'inpname':fieldArray, 'classId':classId, 'metabox':metabox}}, function(response) {
			$.each(response.element,function(e,v){
				if(typeof response.inp[e]!=='undefined'){
					findElementById=self.closest('#wppizza').find(v);
					if(findElementById.length>0){
						self.closest('#wppizza').find(v).empty().html(response.inp[e]);
					}else{
						self.closest('.wppizza_option').find('.wppizza_pricetiers').empty().html(response.inp[e]);
					}
				}
			});
		},'json').error(function(jqXHR, textStatus, errorThrown) {alert("error : " + errorThrown);});
	});

	/******************************
	*	[order form field type select - onchange]
	******************************/
	$(document).on('change', '.wppizza_order_form_type', function(e){
		var self=$(this);
		var id=self.attr('id').split("_").pop(-1);
		var val=self.val();
		//alert(val);
		self.closest('td').find('.wppizza_order_form_select input').val('');//empty value
		if(val=='select'){
			self.closest('td').find('.wppizza_order_form_select').css('display', 'block');
		}else{
			self.closest('td').find('.wppizza_order_form_select').css('display', 'none');

		}
	});

	/******************************
	*	[chaging to grid layout - onchange]
	******************************/
	$(document).on('change', '#wppizza_layout_style', function(e){
		var val=$(this).val();
		if(val=='grid'){
			$('#wppizza-style-grid').css('display', 'block');
		}else{
			$('#wppizza-style-grid').css('display', 'none');
		}
	});

	/*****************************
	*	[remove an option]
	*****************************/
	$(document).on('click', '.wppizza-delete', function(e){
		e.preventDefault();
		var self=$(this);
		/**we must have at least one size option**/
		if(self.hasClass('sizes')){
			var noOfSizes=$('#wppizza_sizes_options>span').length;
			if(noOfSizes<=1){
				alert('Sorry, at least one size option must be defined');
				return;
			}
		}
		/**allow to remove by class and not just span*/
		var remItem=$(this).closest('.wppizza-row-remove');
		if(remItem.length>0){
			remItem.remove();
		}else{
			$(this).closest('span').remove();
		}
	});
	/*****************************
	*	[show gateway settings option]
	*****************************/
	$(document).on('click', '.wppizza-gateway-show-options', function(e){
		//alert('alarm');
		var self=$(this);
		$('.wppizza-gateway-settings').slideUp();
		self.closest('.wppizza-gateway').find('.wppizza-gateway-settings').slideDown();
	});
	/*********************************************************
	*	[show order form tips/surcharges option - not yet in use]
	*********************************************************/
	$(document).on('click', '#wppizza-toggle-tgs', function(e){
		$('#wppizza_order_form tr.ctips,#wppizza_order_form tr.csurcharges').toggle("slow");
	});
	/*********************************************************
	*	[tools->get php settings]
	*********************************************************/
	$(document).on('click', '#wppizza_show_php_vars', function(e){
		var elm=$('#wppizza_php_info');
		jQuery.post(ajaxurl , {action :'wppizza_admin_json',vars:{'field':'get-php-vars'}}, function(res) {
			elm.html(res);
		},'html').error(function(jqXHR, textStatus, errorThrown) {alert("error : " + errorThrown);});
	});
    /*******************************
	*	[chosen]
	*******************************/
	$('.wppizza_delivery_calculation_exclude_item, .wppizza_delivery_calculation_exclude_cat, .wppizza_discount_calculation_exclude_item, .wppizza_discount_calculation_exclude_cat').chosen({inherit_select_classes:true,width:'95%'});

/****************************************
*
*
* 	order history
*
*
*****************************************/
	/****************************************
	* 	print order history
	*	using template
	*****************************************/
	$(document).on('click', '.wppizza-print-order', function(e){
		e.preventDefault();
		var key=$(this).attr('id').split("-").pop(-1);
		var orderId=$('#wppizza_order_id_'+key+'').val();
		var blogid='';
		var blogInp=$('#wppizza_order_blogid_'+key+'');
		if(blogInp.length>0){
			blogid=blogInp.val();
		}
		jQuery.post(ajaxurl , {action :'wppizza_admin_json',vars:{'field':'print-order','id':orderId,'blogid':blogid}}, function(output) {
            //Print Page : as Android doesnt understand this, let's open a window
            var wppizzaPrintOrder = window.open("","WppizzaOrder","width=750,height=550");

	        if (wppizzaPrintOrder == null || typeof(wppizzaPrintOrder)=='undefined'){
            alert("You must turn off your pop-up blocker to enable printing.\n\nPlease consult your device manufacturer about how to turn off pop-up blocking for this site.\n\n");
            return;
			}

			wppizzaPrintOrder.document.open("text/html", "replace");/*text/plain makes no difference....so wrap in <pre> instead*/
    		if(output['content-type']=='text/plain'){
    			var wpPizzaOrder=output['plaintext'];
    			wppizzaPrintOrder.document.write('<pre>'+wpPizzaOrder+'</pre>');
    		}else{
    			var wpPizzaOrder=output['html'];
    			wppizzaPrintOrder.document.write(wpPizzaOrder);
    		}

            wppizzaPrintOrder.focus();
			/*android doesnt understand .print() not my fault really*/
			wppizzaPrintOrder.print();
			/*output to console*/
			//console.log(output)

		},'json').error(function(jqXHR, textStatus, errorThrown) {alert("error : " + errorThrown);});
	});
	/******************************
	*	print order history -
	*	OLD/PREVIOUS VERSION
	*******************************/
	$(document).on('click touchstart', '.wppizza-print-order-prev', function(e){
			e.preventDefault();
			var ordId=$(this).attr('id').split("-").pop(-1);
            //Get the value of textareas
            var initiator=$('#wppizza_order_initiator_'+ordId+'').val();
            var transaction_id=$('#wppizza_order_transaction_id_'+ordId+'').val();

            //var order=$('#wppizza_order_details-'+ordId+'').val();
            var order=$('#wppizza_order_details-'+ordId+'').html();
            //var customer=$('#wppizza_order_customer_details-'+ordId+'').val();
            var customer=$('#wppizza_order_customer_details-'+ordId+'').html();

            var notes=$('#wppizza-order-notes-'+ordId+'').val();

            //store HTML of current whole page in variable
            var currentPage = document.body.innerHTML;

            //Re-create the page HTML with required info only
           var wpPizzaOrderHistory=
              "<html><head><title></title></head><body>" +
              (initiator) + "<br />" +
              (transaction_id)  + "<br /><br />" +
              (customer) + "<br />" +
              (order) + "<br /><br />" +
              wppizzaNl2br(notes) + "</body></html>";

            //Print Page : as Android doesnt understnd this, let's open a window
            var wpPizzaOrderHistoryWindow = window.open("","WppizzaOrder","width=750,height=550");
			wpPizzaOrderHistoryWindow.document.write(wpPizzaOrderHistory);

            wpPizzaOrderHistoryWindow.focus();
			/*android doesnt understand .print() not my fault really*/
			wpPizzaOrderHistoryWindow.print();

	});
	/**nl2br when printing*/
	var wppizzaNl2br =function(str, is_xhtml) {
		var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
		/**nl2br*/
		var printFormatted=(str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1'+ breakTag +'$2');
		/**format any 2 spaces as nbsp to keep formatting*/
		printFormatted=printFormatted.replace(/\s{2}/g, '&nbsp;&nbsp;');

		return printFormatted;
	}
	/*****************************
	*	[poll orders]
	*****************************/
	var pollObj=$('#history_orders_poll_enabled');
	if(pollObj.length>0){
		var pollingInterval=$('#history_orders_poll_interval').val();
		var pollOrdersInterval=setInterval(function(){pollOrders()},(pollingInterval*1000));
	}
	/*****************************
	*	[change poll interval]
	*****************************/
	$(document).on('change', '#history_orders_poll_interval', function(e){
		var pollingInterval=$(this).val();
		clearInterval(pollOrdersInterval);
		pollOrdersInterval=setInterval(function(){pollOrders()},(pollingInterval*1000));
	});
	/*****************************
	*	[do poll if enabled]
	*****************************/
	var pollOrders=function(){
	if($('#history_orders_poll_enabled').is(':checked')){
		$('#wppizza-orders-polling').addClass('wppizza-load');
		var triggerTarget=$('#history_get_orders');
		triggerTarget.trigger('click');
	}}
	/*****************************
	*	[update order status]
	*****************************/
	$(document).on('change', '.wppizza_order_status', function(e){
		var self=$(this);
		var key=self.attr('id').split("-").pop(-1);
		var orderId=$('#wppizza_order_id_'+key+'').val();
		var selVal=self.val();
		var initiator=$('#wppizza_order_initiator_ident_'+key+'').val();
		var selClass=selVal.toLowerCase();
		var blogid='';
		var blogInp=$('#wppizza_order_blogid_'+key+'');
		if(blogInp.length>0){
			blogid=blogInp.val();
		}
		/*update popup too*/
		$('#wppizza_order_status_popup-'+key+'').val(selVal);
			//console.log(selVal);
			//console.log(blogid);
		jQuery.post(ajaxurl , {action :'wppizza_admin_json',vars:{'field':'orderstatuschange','id':orderId,'blogid':blogid,'selVal':selVal,'initiator':initiator}}, function(response) {
			self.closest('tr').removeClass().addClass('wppizza-ord-status-'+selClass+'');
			$('#wppizza_order_update-'+key).html(response);
		},'html').error(function(jqXHR, textStatus, errorThrown) {alert("error : " + errorThrown);});
	});

	/*****************************
	*	[update order status from popup]
	*****************************/
	$(document).on('change', '.wppizza_order_status_popup', function(e){
		var self=$(this);
		var key=self.attr('id').split("-").pop(-1);
		var selVal=self.val();
		$('#wppizza_order_status-'+key+'').val(''+selVal+'');/*set value*/
		$('#wppizza_order_status-'+key+'').trigger('change');/*trigger change in main screen*/
	});
	/******************************
	*	[delete order]
	******************************/
	$(document).on('click', '.wppizza_order_delete', function(e){
		e.preventDefault();
		if(!confirm('are you sure ?')){ return false;}
		var self=$(this);
		var key=self.attr('id').split("-").pop(-1);
		var orderId=$('#wppizza_order_id_'+key+'').val();
		var blogid='';
		var blogInp=$('#wppizza_order_blogid_'+key+'');
		if(blogInp.length>0){
			blogid=blogInp.val();
		}
		jQuery.post(ajaxurl , {action :'wppizza_admin_json',vars:{'field':'delete_order','ordId':orderId,'blogid':blogid,}}, function(response) {
			alert(response);
			self.closest('tr').empty().remove();
			$('#wppizza-order-failed-'+key+'').empty().remove();
			$('#wppizza-order-notes-tr-'+key+'').empty().remove();
			$('#wppizza-order-has-notes-tr-'+key+'').empty().remove();
		},'html').error(function(jqXHR, textStatus, errorThrown) {alert("error : " + errorThrown);});
	});
	/*****************************
	*	[update order notes]
	*****************************/
	$(document).on('click', '.wppizza-order-add-notes', function(e){
		var self=$(this);
		var key=self.attr('id').split("-").pop(-1);
		$('#wppizza-order-notes-tr-'+key+',#wppizza-order-has-notes-tr-'+key+'').fadeIn("slow");
	});
	$(document).on('click', '.wppizza-order-do-notes', function(e){
		var self=$(this);
		var key=self.attr('id').split("-").pop(-1);
		var orderId=$('#wppizza_order_id_'+key+'').val();
		var blogid='';
		var blogInp=$('#wppizza_order_blogid_'+key+'');
		if(blogInp.length>0){
			blogid=blogInp.val();
		}
		var selVal=$('#wppizza-order-notes-'+key+'').val();
		jQuery.post(ajaxurl , {action :'wppizza_admin_json',vars:{'field':'ordernoteschange','id':orderId,'blogid':blogid,'selVal':selVal}}, function(response) {
			//$('#wppizza_order_update-'+selId).html(response);
			if(response<=0){
				self.closest('tr').fadeOut(250);
				$('#wppizza-order-add-notes-'+key+'').fadeIn(250,function(){alert('ok');});
			}else{
				$('#wppizza-order-add-notes-'+key+'').fadeOut(250,function(){alert('ok');});
			}
		},'text').error(function(jqXHR, textStatus, errorThrown) {alert("error : " + errorThrown);});
	});

	/******************************
	*	[show orders]
	******************************/
	var pollError=0;
	$(document).on('click', '#history_get_orders', function(e){
		e.preventDefault();
		var limit=$('#history_orders_limit').val();
		var orderstatus=$('#history_orders_status').val();
		var form_fields = $("#wppizza_history_search").find("[name]").serialize();
		jQuery.post(ajaxurl , {action :'wppizza_admin_json',vars:{'field':'get_orders','limit':limit,'orderstatus':orderstatus,'form_fields':form_fields}}, function(response) {
			$('#wppizza_history_orders').html(response.orders);
			$('#wppizza_history_totals').html(response.totals);
			$('#wppizza-orders-polling').removeClass();
			pollError=0;
		},'json').error(function(jqXHR, textStatus, errorThrown) {
			pollError++;
			if(pollError>=5){
				alert("error : " + errorThrown);
			}else{
				console.log("error : " + errorThrown);
			}
		});
	});
	/******************************
	*	[show orders on load too ]
	******************************/
	var triggerTarget=$('#history_get_orders');
	triggerTarget.trigger('click');

	/******************************
	*	[get total of all orders]
	******************************/
	$(document).on('click', '#wppizza_history_totals_getall', function(e){
		e.preventDefault();
		jQuery.post(ajaxurl , {action :'wppizza_admin_json',vars:{'field':'get_orders_total'}}, function(response) {
			$('#wppizza_history_totals').html(response.totals);
		},'json').error(function(jqXHR, textStatus, errorThrown) {alert("error : " + errorThrown);});
	});
	/******************************
	*	[delete abandoned orders]
	******************************/
	$(document).on('click', '#wppizza_order_abandoned_delete', function(e){
		e.preventDefault();
		if(!confirm('are you sure ?')){ return false;}
		var days=$('#wppizza_order_days_delete').val();
		var failed=$('#wppizza_order_failed_delete').is(':checked');
		jQuery.post(ajaxurl , {action :'wppizza_admin_json',vars:{'field':'delete_abandoned_orders','days':days,'failed':failed}}, function(response) {
			alert(response);
		},'html').error(function(jqXHR, textStatus, errorThrown) {alert("error : " + errorThrown);});
	});

	/*****************************
	*	[toggle failed order details order notes]
	*****************************/
	$(document).on('click', '.wppizza-order-failed-toggle-details', function(e){
		var self=$(this);
		var key=self.attr('id').split("-").pop(-1);
		$('#wppizza-order-failed-details-'+key+'').toggle();
	});

	/*****************************
	*	[show order details in thickbox]
	*****************************/
	$(document).on('click', '.wppizza_order_customer_details,.wppizza_order_details', function(e){
		var self=$(this);
		var closestTr=self.closest('tr');
		var key=self.attr('id').split("-").pop(-1);//unique key even if smae order id different site (multisite)
//		/*add to key if blogid set. lets use closest here to get the blog id*/
//		var blogIdVal=closestTr.find('.wppizza_order_blogid').val();
//		if(typeof blogIdVal!== 'undefined' && blogIdVal!='' && blogIdVal>1){
//			var key=blogIdVal+'_'+key;
//		}
		var txId=$('#wppizza_order_transaction_id_'+key+'').val()+' | '+$('#wppizza_order_date_'+key+'').val();
		var cId='wppizza_order_popup_'+key+'';
		/*automatically set to acknowledged if new*/
		var currStatus=$('#wppizza_order_status_popup-'+key+'').val();
		var setStatus='ACKNOWLEDGED';
		if(currStatus!=setStatus && currStatus=='NEW'){
			$('#wppizza_order_status_popup-'+key+'').val(''+setStatus+'');/*set popup*/
			$('#wppizza_order_status-'+key+'').val(''+setStatus+'');/*set main*/
			$('#wppizza_order_status-'+key+'').trigger('change');/*trigger change in main screen*/
		}
        tb_show(""+txId+"", "#TB_inline?width=580&height=520&inlineId="+cId+"");
        return false;
    });

	/****************************************
	*
	*
	* 	templates emails/print
	*
	*
	*****************************************/
	/**********************************
	*	[make template parts sortable]
	**********************************/
	var sortableParts=function(){
		var wpPizzaSortableTemplateParts = $('.wppizza-template-parts');
		if(typeof wpPizzaSortableTemplateParts!=='undefined'){
			wpPizzaSortableTemplateParts.sortable({
				handle: '.wppizza-template-sort-part',
				axis: 'x' ,
				delay: 150,
				distance: 10,
				update: function(event, ui) {sortableCallback(event, ui);}
			});
		}
	}
	sortableParts();
	/**********************************
	*	[make template parts variables sortable]
	**********************************/
	var sortableVars=function(){
		var wpPizzaSortablePartsVars = $('.wppizza-template-sort-vars');
		if(typeof wpPizzaSortablePartsVars!=='undefined'){
			wpPizzaSortablePartsVars.sortable({
				handle: '.wppizza-template-sort-var',
				axis: 'y' ,
				delay: 150,
				distance: 10,
				cancel: '.wppizza-template-sort-vars-addinfo', /*disable for add info*//*,.wppizza-template-sort-vars-pricetotal,.wppizza-template-sort-vars-quantity*/
				update: function(event, ui) {sortableCallback(event, ui);}
			});
		}
	}
	sortableVars();
	/**********************************
	*	save set template sortable sortorder
	* 	as json to be able to show admin
	*	section/values in right order again
	* 	regardless of whether a checkbox
	*	was checked or not
	**********************************/
	var sortableCallback=function(event, ui){
		var self =ui.item;
		var values = self.closest('tbody').find('.wppizza-values-order');
		var target = '';
		var sort_order = {};
		$.each(values,function(e,v){
			var elm_id = $(v).attr('id');
			var elm_id_values = elm_id.split('.');
			var part = elm_id_values[2];
			var value = elm_id_values[3];

			/* set hidden input target element id*/
			target = ''+elm_id_values[1]+'';

			if(typeof sort_order[part] === 'undefined'){
				sort_order[part] = {};
			}
			if(typeof sort_order[part][value] === 'undefined'){
				sort_order[part][value] = 1;
			}
		});
		/*
			set as value in hidden input so we can save the current sortorder
			to be displayed in admin again when returning
		*/
		var sortorder_json = JSON.stringify(sort_order);
		$('#wppizza-sortorder-'+target+'').val(sortorder_json);
	}

	/**********************************
	*	[toggle template style buttons and inputs on format change]
	*	print as well as emails
	**********************************/
	$(document).on('change', '.wppizza_template_mail_type', function(e){
		var self=$(this);
		var mailType=self.val();
		var split=self.attr("id").split("_");
		var id = split.pop(-1);
		var tpl = split.pop(-3);

		/**reset visibility of all first, regardless of selection**/
		$("#wppizza-template-body-"+id+", #wppizza-template-global-styles-"+id+"").fadeOut(250);

		/**html, enable css buttons and inputs etc**/
		if(mailType=='phpmailer'){
			 $('#wppizza-dashicons-template-'+tpl+'-media-code-'+id+'').removeClass('wppizza-dashicons-template-'+tpl+'-media-code-inactive').addClass('wppizza_template_style_toggle wppizza-dashicons-template-'+tpl+'-media-code');
		}
		/**plaintext, disable css buttons and inputs etc**/
		if(mailType=='wp_mail'){
			 $('#wppizza-dashicons-template-'+tpl+'-media-code-'+id+'').removeClass('wppizza-dashicons-template-'+tpl+'-media-code wppizza_template_style_toggle').addClass('wppizza-dashicons-template-'+tpl+'-media-code-inactive');

		}
	});

	/**********************************
	*	[toggle template details/values visibility]
	**********************************/
	var tplStyleOrValue='';/*to identify if we are currently delaing with style or values*/
	$(document).on('click', '.wppizza_template_details_toggle', function(e){
		var self=$(this);
		var split=self.attr("id").split("_");
		var id = split.pop(-1);
		var tpl = split.pop(-2);
		var tBody=$('#wppizza-template-body-'+id+'');
		var globStyles=$('#wppizza-template-global-styles-'+id+'');
		var valueElements=$('.wppizza-template-'+tpl+'-value-element-'+id+'');
		var styleElements=$('.wppizza-template-'+tpl+'-style-element-'+id+'');
		var detailsElements=$('.wppizza-template-'+tpl+'-value-element-only-'+id+'');

		/**hide if visible to toggle on repeated icon click**/
		if((tBody.is(":visible") || globStyles.is(":visible") ) && tplStyleOrValue=='details'){
			globStyles.hide();
			tBody.fadeOut();
		}else{
			globStyles.hide();
			styleElements.hide();
			tBody.fadeIn();
			valueElements.fadeIn();
			detailsElements.fadeIn();
		}
		/**set ident **/
		tplStyleOrValue='details';
	});
	/**********************************
	*	[toggle style/css inputs visibility]
	**********************************/
	$(document).on('click', '.wppizza_template_style_toggle', function(e){
		var self=$(this);
		var split=self.attr("id").split("-");
		var id = split[split.length-1];
		var tpl = split[split.length-4];
		var tBody=$('#wppizza-template-body-'+id+'');
		var globStyles=$('#wppizza-template-global-styles-'+id+'');
		var valueElements=$('.wppizza-template-'+tpl+'-value-element-'+id+'');
		var styleElements=$('.wppizza-template-'+tpl+'-style-element-'+id+'');
		var detailsElements=$('.wppizza-template-'+tpl+'-value-element-only-'+id+'');

		/**hide if visible to toggle on repeated icon click**/
		if((tBody.is(":visible") || globStyles.is(":visible") ) && tplStyleOrValue=='style'){
			globStyles.fadeOut();
			tBody.fadeOut();
		}else{
			valueElements.hide();
			detailsElements.hide();
			if(tpl!='print'){
				tBody.fadeIn();
			}
			globStyles.fadeIn();
			styleElements.fadeIn();
		}
		/**set ident **/
		tplStyleOrValue='style';

	});

	/**********************************
	*	[template - add new]
	**********************************/
	$(document).on('click', '.wppizza_add_templates', function(e){
		e.preventDefault();
		var self=$(this);
		var arrayKey = self.attr("id").split("_").pop(-1);/*email or print etc*/
		self.attr("disabled", "true");/*disable button*/
		var countNewKeys=$(".wppizza-template-new").length;
		jQuery.post(ajaxurl , {action :'wppizza_admin_json',vars:{'field':'add_template', 'arrayKey': arrayKey, 'countNewKeys':countNewKeys}}, function(response) {
			$('#wppizza_list_templates').prepend(response.markup);
			self.removeAttr("disabled");/*re-enable button*/
			/**reinitialise sortable*/
			sortableParts();
			sortableVars();
		},'json').error(function(jqXHR, textStatus, errorThrown) {alert("error : " + errorThrown);});
	});
	/**********************************
	*	[template - remove]
	**********************************/
	$(document).on('click', '.wppizza_template_delete', function(e){
		var self=$(this);
		var selId = self.attr("id").split("-").pop(-1);
		var arrayKey = self.attr("id").split("_")[2];/*email or print etc*/
		var elm=$('#wppizza-template-table-'+selId+'');
		elm.empty().remove();
		/**if print and to be deleted is selected, select default now*/
		if(arrayKey=='print'){
			if($('#wppizza_templates_print_print_id_'+selId+':checked')){
				$('#wppizza_templates_print_print_id_default').prop('checked',true);
			}
		}
		/**if emails and to be deleted is selected, select default now*/
		if(arrayKey=='emails'){
			if($('#wppizza_templates_emails_recipients_email_customer_'+selId+':checked')){
				$('#wppizza_templates_emails_recipients_default_email_customer').prop('checked',true);
			}
			if($('#wwppizza_templates_emails_recipients_email_shop_'+selId+':checked')){
				$('#wppizza_templates_emails_recipients_default_email_shop').prop('checked',true);
			}
		}
		/**add input field to mark for deletion if not a copy of something or new**/
		if(!elm.hasClass('wppizza-template-new')){
			$('#wppizza_list_templates').append('<input type="hidden" name="wppizza[template_remove]['+arrayKey+']['+selId+']" value="'+selId+'" />');
		}
	});
	/**********************************
	*	[template - style input - expand/contract textareas on focus/blur]
	**********************************/
	$(document).on('focus blur', '.wppizza-template-global-style,.wppizza-template-section-style,.wppizza-template-parts-style', function(e){
		var self=$(this);
		var focusType=e.type;

			if(self.hasClass('wppizza-template-print-global-style-body')){
				if(focusType == 'focusin'){
					$(this).animate({height:250},200);
				}
				if(focusType == 'focusout'){
					$(this).animate({height:50},200);
				}
			}else{
				if(focusType == 'focusin'){
					$(this).animate({height:100},200);
				}
				if(focusType == 'focusout'){
					$(this).animate({height:25},200);
				}
			}
	});
	/**********************************
	*	[template - preview]
	**********************************/
	$(document).on('click', '.wppizza_template_preview', function(e){
		var self=$(this);
		/*get id*/
		var selId = self.attr("id").split("-").pop(-1);
		/*ini data to send to ajax*/
		var data={};
		/*template id */
		data['tplId'] = selId;
		/*what kind of template - email or print etc */
		data['tplType'] = self.attr("id").split("_")[2];
		var mailType=data['tplType'];

		/*html y/n ?*/
		data['mail_type'] = $('#wppizza_template_mail_type_'+mailType+'_'+selId+'').val();

		/*set ajax type depending on type set*/
		var ajaxType='text';
		if(data['mail_type']=='phpmailer'){ajaxType='html';}
		/*checked template elements and style values*/
		var templateElms = $('#wppizza-template-body-'+selId+' :input[type="checkbox"]:checked, #wppizza-template-body-'+selId+' textarea, #wppizza-template-global-styles-'+selId+' textarea');
		/*encode selected parameters to pass on to ajax*/
		data['templateElms'] = $.param(templateElms);

		/**send to ajax to create preview*/
		jQuery.post(ajaxurl , {action :'wppizza_admin_json',vars:{'field':'preview_template', 'data': data}}, function(response) {

			/*open window in center*/
			var previewWidth=600;
			var previewHeight=600;
			var previewLeftPosition = (screen.width) ? (screen.width-previewWidth)/2 : 0;
			var previewTopPosition = (screen.height) ? (screen.height-previewHeight)/2 : 0;
			var previewSettings ='height='+previewHeight+',width='+previewWidth+',top='+previewTopPosition+',left='+previewLeftPosition+'';

			/**for true text , write into textarea*/
			var previewContent;
			if(response['content-type']=='text/html'){
				previewContent=response['html'];
			}else{
				previewContent='<textarea style="position:absolute;top:0;right:0;left:0;bottom:0;width:100%;min-width:600px">'+response['plaintext']+'</textarea>';
			}

			/*do preview*/
    		var previewWindow = window.open("", "WpPizzaPreviewWindow", previewSettings);
    		if(response['content-type']=='text/html'){
    			previewWindow.document.open("text/html", "replace");
    		}else{
    			previewWindow.document.open("text/plain", "replace");
    		}
    		previewWindow.document.write(previewContent);
    		previewWindow.focus();

		},'json').error(function(jqXHR, textStatus, errorThrown) {alert("error : " + errorThrown);});
	});
	/**********************************
	*	[toggle child parts depending on parent selection]
	**********************************/
	$(document).on('click', '.wppizza-template-part', function(e){
		var self=$(this);
		var selId = self.attr("id").split("-");
		var msgId=selId[3];
		var partId=selId[4];
		var target=$('.wppizza-template-input-var-'+msgId+'-'+partId+' input');
		if ( self.is( ":checked" ) ){
			target.prop('checked',true);
		}else{
			target.prop('checked',false);
		}
	});
	/**********************************
	*	[toggle parent part depending on child selection]
	**********************************/
	$(document).on('click', '.wppizza-template-input-var input', function(e){
		var self=$(this);
		var selId=$(this).closest('span').attr("id").split("-");
		var msgId=selId[4];
		var partId=selId[5];
		var target=$('#wppizza-template-part-'+msgId+'-'+partId+'');
		var vars=self.closest('.wppizza-template-sort-vars').find('input[type=checkbox]');
		var hasChecked=0;
		$.each(vars,function(e,v){
			if ( $(this).is( ":checked" ) ){
				hasChecked++;
			}
		});
		if(hasChecked==0){
			target.prop('checked',false);
		}
		else{
			target.prop('checked',true);
		}
	});

	/******************************
	*	[check smtp settings]
	******************************/
	$(document).on('click', '#wppizza_smtp_test', function(e){
		e.preventDefault();
		var formInputs=$(this).closest("form").serialize();
		/*make sure it's hidden and empty first*/
		$('#wppizza_smtp_test_results').fadeIn();
		$('#wppizza_smtp_test_results>pre').text('---one moment : testing smtp connection---');
		var param={};
		param.smtp_email=$('#wppizza_smtp_test_email').val();
		param.smtp_host=$('#wppizza_smtp_host').val();
		param.smtp_port=$('#wppizza_smtp_port').val();
		if($('#wppizza_smtp_authentication').is(':checked')){
		param.smtp_authentication=1;
		}
		param.smtp_encryption=$('#wppizza_smtp_encryption').val();
		param.smtp_username=$('#wppizza_smtp_username').val();
		param.smtp_password=$('#wppizza_smtp_password').val();
		/*we need an email*/
		if(param.smtp_email==''){
			$('#wppizza_smtp_test_results').hide();
			alert('please enter an email address');
			return;
		};
		jQuery.post(ajaxurl , {action :'wppizza_admin_json',vars:{'field':'wppizza_smtp_test','wppizza_smtp_test_param':param}}, function(response) {
			//$('#wppizza_smtp_test_results').fadeIn()
			$('#wppizza_smtp_test_results>pre').html(response);
		},'html').error(function(jqXHR, textStatus, errorThrown) {alert("error : " + errorThrown);});
	});

})