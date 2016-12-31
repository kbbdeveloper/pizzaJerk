var wppizzaClickEvent='click';/*leave for legacy reasons*/
var wppizzaShortcodeTotals = function(){};

jQuery(document).ready(function($){

	/****************************************************************************************************************************************************************************************************
	*
	*	[keep cart static on page when scrolling]
	*	[always adds wppizza-cart-fixed class when scrolling is relevant so we can set things if needed]
	*	[will either be executed on page load or after the cart has been created if a cache plugin is used]
	****************************************************************************************************************************************************************************************************/
	var wppizzaCartStickyLoad= function(){
		var wppizzaCartStickyElm=$('.wppizza-cart-sticky');/**get all elements*/
		var wppizzaCartStickyScrollTimeout;/**ini timeout*/
		/**********************
			[get bottom limit]
		***********************/
		var wppizzaCartStickyLimitBottomElm=false
		/**set bottom scroll limit by div id**/
		if(typeof wppizza.crt.lmtb !=='undefined' && $('#'+wppizza.crt.lmtb+'').length>0){
			wppizzaCartStickyLimitBottomElm=$('#'+wppizza.crt.lmtb+'');/*set element*/
			var wppizzaCartStickyLimitOffset=0;/**set limit offset to be substracted from wppizza.crt.mt  if required **/
			var wppizzaCartStickyLimitBottom=wppizzaCartStickyLimitBottomElm.offset().top;/*get element top*/
		}
		var wppizzaCartStickyScrollTop = $(window).scrollTop()+wppizza.crt.mt;/*get top poxition where state toggle (browser + set margin)*/


		/**initialize a couple of vars vor the elements*/
		var wppizzaCartStickySelf = [];
		var wppizzaCartStickyVars = [];
		var wppizzaCartStickyParent = [];


		var wppizzaCartStickyAnimation = false;
		if(wppizza.crt.anim>0 && wppizza.crt.fx!=''){
			wppizzaCartStickyAnimation = true;
		}
		/**get all aplicable elements and their variables**/
		if(wppizzaCartStickyElm.length>0){
			$.each(wppizzaCartStickyElm,function(e,v){
				/***get the element object and add vars as required**/
				wppizzaCartStickySelf[e]=$(this);

				/**wrap in wraper div which is then set to height of cart to stop things jumping around**/
				wppizzaCartStickySelf[e].wrap( "<div class='wppizza-cart-wrap'></div>" );

				wppizzaCartStickyVars[e]=wppizzaCartStickySelf[e].css(["backgroundColor"]);
				wppizzaCartStickyVars[e]['offset-top']= wppizzaCartStickySelf[e].offset().top;/*offset from top of page**/
				wppizzaCartStickyVars[e]['state']='';/**initialize state so - when set below - we dont ever need do the same thing multiple times**/
				wppizzaCartStickyVars[e]['height-int']=wppizzaCartStickySelf[e].height();/*make sure we also have height an integer and call it height-int instead of just height or jQuery 1.8.3 gets confused when SETTING height*/
				wppizzaCartStickyVars[e]['width-int']=wppizzaCartStickySelf[e].width();/*make sure we also have width an integer and call it width-int instead of just width or jQuery 1.8.3 gets confused when SETTING width*/

				/**set limit bottom**/
				if(wppizzaCartStickyLimitBottomElm && wppizzaCartStickyLimitBottom>(wppizzaCartStickyVars[e]['offset-top']+wppizzaCartStickyVars[e]['height-int'])){
					wppizzaCartStickyVars[e]['limit-bottom']=Math.floor(wppizzaCartStickyLimitBottom-wppizzaCartStickyVars[e]['height-int']);
				}

				/*get parent element so we can set height on it*/
				wppizzaCartStickyParent[e] = wppizzaCartStickySelf[e].parent();

				/*set distinct width of element so we dont have to set it all the time when scrolling or setting fixed position*/
				wppizzaCartStickySelf[e].width(wppizzaCartStickyVars[e]['width-int']);
				wppizzaCartStickyParent[e].height(wppizzaCartStickyVars[e]['height-int']);
			});
		}

		/***********************************************
		*	[we have set an element limit past which the
		*	sticky cart should not scroll,
		*	lets calculate the (negative) offset here]
		/***********************************************/
		var wppizzaCartStickyMaxOffset = function(elm,top,limitElm){
			var val=0;
			if(wppizzaCartStickyLimitBottomElm){
				var limit=limitElm.offset().top;/*get limit element top*/
				var elmOffset=Math.floor(limit-top-elm['height-int']);/*if negative we use it**/
				if(elmOffset<0){
					val=elmOffset;
				}
			}
			return val;
		};
		/**********no animation, just add/remove class, top and bg colour*******************************************************************************************************/
		if(!wppizzaCartStickyAnimation){
			/*let's rock n' scroll.....( oh dear )*/
			$(window).scroll(function () {
				var wppizzaCartStickyScrollTop = ($(window).scrollTop()+wppizza.crt.mt);
				$.each(wppizzaCartStickySelf,function(e,v){

					/**calcuate needed offset if we are limiting the scroll by a set element below cart***/
					var wppizzaCartStickyLimitOffset=wppizzaCartStickyMaxOffset(wppizzaCartStickyVars[e],wppizzaCartStickyScrollTop,wppizzaCartStickyLimitBottomElm);

					/**leave it in place**/
					if (wppizzaCartStickyVars[e]['offset-top']>=wppizzaCartStickyScrollTop) {
						wppizzaCartStickySelf[e].removeClass('wppizza-cart-fixed').css({'top':'','background-color':''+wppizzaCartStickyVars[e]['backgroundColor']+''});
					}
					/**set to fixed**/
					if (wppizzaCartStickyVars[e]['offset-top']<wppizzaCartStickyScrollTop) {
						wppizzaCartStickySelf[e].addClass('wppizza-cart-fixed').css({'top':''+(wppizza.crt.mt+wppizzaCartStickyLimitOffset)+'px','background-color':''+wppizza.crt.bg+''});
					}
				});
			});
		}

		/**********with animation, *******************************************************************************************************************************************/
		if(wppizzaCartStickyAnimation){
		var wppizzaCartStickyAnimIni = true;/*set load flag*/

			/***********initialize on load***********/
			setTimeout(function(){/*a little timeout to give the page time to render*/
				$.each(wppizzaCartStickySelf,function(e,v){

					/**calcuate needed offset if we are limiting the scroll by a set element below cart***/
					var wppizzaCartStickyLimitOffset=wppizzaCartStickyMaxOffset(wppizzaCartStickyVars[e],wppizzaCartStickyScrollTop,wppizzaCartStickyLimitBottomElm);

					/**leave it in place**/
					if (wppizzaCartStickyVars[e]['offset-top']>=wppizzaCartStickyScrollTop) {
						wppizzaCartStickyVars[e]['state']='relative';/*set state flag so we dont do the same thing  multiple times**/
					}
					/**move to sticky/fixed**/
					if (wppizzaCartStickyVars[e]['offset-top']<wppizzaCartStickyScrollTop) {
						wppizzaCartStickySelf[e].addClass('wppizza-cart-fixed').css({'background-color':''+wppizza.crt.bg+'','top':'0'});
						wppizzaCartStickySelf[e].animate({'top':''+(wppizza.crt.mt+wppizzaCartStickyLimitOffset)+'px'},wppizza.crt.anim,''+wppizza.crt.fx+'',function(){});
						wppizzaCartStickyVars[e]['state']='fixed';/*set state flag so we dont do the same thing  multiple times**/
					}
				});
				wppizzaCartStickyAnimIni=false;/*unset previously set load flag*/
			},200);


			/*********on scroll after load *************/
			$(window).scroll(function () {
				if(!wppizzaCartStickyAnimIni){/*only react to scrolling after initial load*/

					var wppizzaCartStickyScrollTop = $(window).scrollTop()+wppizza.crt.mt;/*find out if we need fixed or relative*/

						clearTimeout(wppizzaCartStickyScrollTimeout);
						wppizzaCartStickyScrollTimeout = setTimeout(function(){/*a little timeout to not go mad*/
							/*iterate through elements*/
							$.each(wppizzaCartStickySelf,function(e,v){

								/**calcuate needed offset if we are limiting the scroll by a set element below cart***/
								var wppizzaCartStickyLimitOffset=wppizzaCartStickyMaxOffset(wppizzaCartStickyVars[e],wppizzaCartStickyScrollTop,wppizzaCartStickyLimitBottomElm);

								/**put back in its place if state has changed, otherwise just leave in peace**/
								if (wppizzaCartStickyVars[e]['offset-top']>=wppizzaCartStickyScrollTop && wppizzaCartStickyVars[e]['state']!='relative') {
									wppizzaCartStickyVars[e]['state']='relative';/*set state flag so we dont do the same thing  multiple times**/

									wppizzaCartStickySelf[e].removeClass('wppizza-cart-fixed');
									wppizzaCartStickySelf[e].animate({'top':''},wppizza.crt.anim,''+wppizza.crt.fx+'',function(){
										wppizzaCartStickySelf[e].css({'background-color':''+wppizzaCartStickyVars[e]['backgroundColor']+''});
									});
									// if we do not want to animate when returning to relative state , use this instead of the above.
									//wppizzaCartStickySelf[e].removeClass('wppizza-cart-fixed').css({'top':'','background-color':''+wppizzaCartStickyVars[e]['backgroundColor']+''});
								}
								/**move to sticky/fixed if state has changed or we have a limit set , otherwise just leave in peace**/
								if (wppizzaCartStickyVars[e]['offset-top']<wppizzaCartStickyScrollTop && (wppizzaCartStickyVars[e]['state']!='fixed' || wppizzaCartStickyLimitBottomElm)) {

									wppizzaCartStickyVars[e]['state']='fixed';/*set state flag so we dont do the same thing  multiple times**/
									wppizzaCartStickySelf[e].addClass('wppizza-cart-fixed').css({'background-color':''+wppizza.crt.bg+''});
									wppizzaCartStickySelf[e].animate({'top':''+(wppizza.crt.mt+wppizzaCartStickyLimitOffset)+'px'},wppizza.crt.anim,''+wppizza.crt.fx+'',function(){});
								}
							});
					},100);
				}
			});
		}
	}
	/*******************************************************
	*
	*	[add spinner to order page for increase/decrease of items if enabled]
	*
	*******************************************************/
  	if(typeof wppizza.ofqc!=='undefined'){
  		var spinnerElm=$( "#wppizza-send-order .wppizza-item-quantity" );
       	spinnerElm.spinner({ min: 0});/*set min var*/

		/*restrict scrollwheel to be >=0*/
		spinnerElm.on( 'DOMMouseScroll mousewheel', function ( event ) {
		  if( event.originalEvent.detail > 0 || event.originalEvent.wheelDelta < 0 ) { //alternative options for wheelData: wheelDeltaX & wheelDeltaY
		    // down
		    if (parseInt(this.value) > 0) {
		    	this.value = parseInt(this.value, 10) - 1;
		    }
		  } else {
		  	// up
		  	this.value = parseInt(this.value, 10) + 1;
		  }
		  //prevent page from scrolling
		  return false;
		});


		/**stop submitting if we are hitting enter after changing quantities and update those instead**/
		spinnerElm.keydown(function(event) {
			if(event.which == 13 || event.which == 35){
				event.preventDefault();
				$('.wppizza-update-order').trigger('click');
			return false;
			}
		});

       /**do the update**/
       $(document).on('click', '.wppizza-update-order', function(e){
       	/*get the elemenst and create a key value array to send to ajax**/
       	var wppizzaCartCurrElms=$(".wppizza-item-quantity");
       	var updtElms={};
       	$.each(wppizzaCartCurrElms,function(e,v){
       		var self=$(this);
       		var id=$(this).attr('id').split('-');
       		var val=$(this).val();
       		updtElms[id[2]]=val;
       	});
       	$('html').css({'position':'relative'});/*stretch html to make loading cover whole page*/
     	$('body').prepend('<div id="wppizza-loading" style="opacity:0.8"></div>');
     	/**now send ajax to update**/
		jQuery.post(wppizza.ajaxurl , {action :'wppizza_json',vars:{'type':'wppizza-update-order','data':updtElms}}, function(response) {
			window.location.href=window.location.href;/*make sure page gest reloaded without confirm*/
			return;
		},'json').error(function(jqXHR, textStatus, errorThrown) {	$('body>#wppizza-loading').remove(); console.log("error : " + errorThrown);console.log(jqXHR.responseText);});
       });
  	}
	/*******************************
	*	[add to cart / remove from cart]
	*******************************/
	/**only allow integers in cart increase/decrease**/
	$(document).on('keyup', '.wppizza-cart-incr', function(e){
		this.value = this.value.replace(/[^0-9]/g,'');
		/**when using textbox in cart to incr/decr allow enter/end (iphone) as well as clicking on button */
		if(e.keyCode == 13 || e.keyCode == 35){
			$(this).closest('li').find('.wppizza-cart-increment').trigger('click');
		}
	});

	var wppizza_current_item_count_val='';
	/**get current value for cart increase/decrease first**/
	$(document).on('focus', '.wppizza-cart-incr', function(e){
		wppizza_current_item_count_val = this.value.replace(/[^0-9]/g,'');
	});
	/*execute on blur too if value is different*/
	$(document).on('blur', '.wppizza-cart-incr', function(e){
		this.value = this.value.replace(/[^0-9]/g,'');
		/**when using textbox in cart to incr/decr allow enter/end (iphone) as well as clicking on button */
		/*only run if value has changed*/
		if(wppizza_current_item_count_val != this.value){
			$(this).closest('li').find('.wppizza-cart-increment').trigger('click');
		}
	});


	/**run defined functions after cart refresh**/
	var wppizzaCartRefreshed = (function(functionArray, res) {
		if(functionArray.length>0){
			for(i=0;i<functionArray.length;i++){
				var func = new Function("term", "return " + functionArray[i] + "(term);");
				func(res);
			}
		}
	});

	$(document).on('click', '.wppizza-add-to-cart,.wppizza-remove-from-cart,.wppizza-cart-refresh,#wppizza-force-refresh,.wppizza-cart-increment,.wppizza-empty-cart-button', function(e){
		if ($(".wppizza-open").length > 0){//first check if shopping cart exists on page and that we are open
			e.preventDefault();
			e.stopPropagation();

		/**if on orderpage, cover whole page**/
		if(typeof wppizza.isCheckout!=='undefined'){
       		$('html').css({'position':'relative'});/*stretch html to make loading cover whole page*/
     		$('body').prepend('<div id="wppizza-loading" style="opacity:0.8"></div>');
			$('.wppizza-ordernow').attr("disabled", "true");//disable send order button
		}else{
			$('.wppizza-order').prepend('<div id="wppizza-loading"></div>');
		}


		var self=$(this);
		var selfId=self.attr('id');
		var cartButton=$('.wppizza-cart-button input,.wppizza-cart-button>a,.wppizza-empty-cart-button');
		cartButton.attr("disabled", "true");/*disable place order button to stop trying to order whilst stuff is being added to the cart*/
		/**feedback on item add enabled ? - always skip if triggered from add_to_cart_button shortcode*/
		var fbatc=false;
		if(typeof wppizza.itm!=='undefined' && typeof wppizza.itm.fbatc!=='undefined' && !self.hasClass('wppizza-add-to-cart-btn')){
		 fbatc=true;
		}

		var itemCount=1;
		/**get cat id**/
		var catId='';
		if(self.hasClass('wppizza-add-to-cart')){
			type='add';
			var postCatId = selfId.split('-');
			var catdata = self.closest('article').find('#wppizza-category-'+postCatId[1]+'').val();
			if(typeof catdata!=='undefined'){/*some customised templates may not have catid added, so check first*/
				catId=catdata;
			}
		}
		if(self.hasClass('wppizza-remove-from-cart')){type='remove';}
		if(self.hasClass('wppizza-empty-cart-button')){type='removeall';selfId=0;}
		if(self.hasClass('wppizza-cart-refresh') || selfId=='wppizza-force-refresh'){type='refresh';}
		if(self.hasClass('wppizza-cart-increment')){
			var itemCount=self.closest('li').find('.wppizza-cart-incr').val();
			if(itemCount==0){
				type='remove';
			}else{
				type='increment';
			}
		}
			if(type!='removeall' && type!='add' ){
				self.fadeOut(100).fadeIn(400);
			}
			if(!fbatc && type=='add'){/*make this dedicated for add*/
				self.fadeOut(100).fadeIn(400);
			}
			if(fbatc && type=='add'){
				var currentHtml=self.html();
				self.fadeOut(100, function(){
					self.html( "<div class='wppizza-item-added-feedback'>"+wppizza.itm.fbatc+"</div>" ).fadeIn(400).delay(wppizza.itm.fbatcms).fadeOut(400,function(){
						self.html(currentHtml).fadeIn(100);
					});
				});
			}

			jQuery.post(wppizza.ajaxurl , {action :'wppizza_json',vars:{'type':type,'id':selfId,'itemCount':itemCount,'catId':catId}}, function(response) {
				/**if on orderpage, just reload**/
				if(typeof wppizza.isCheckout!=='undefined'){
					window.location.href=window.location.href;/*make sure page gest reloaded without confirm*/
					window.location.reload(true);
					return;
				}

				/*show items in cart*/
				$('.wppizza-order').html(response.itemsajax);
				/*button*/
				$('.wppizza-cart-button').html(response.button);

				/*minimum order not reached*/
				$('.wppizza-cart-nocheckout').html(response.nocheckout);
				/*order summary*/
				$('.wppizza-cart-total-items-label').html(response.order_value.total_price_items.lbl);
				$('.wppizza-cart-total-items-value').html(response.currency_left+''+response.order_value.total_price_items.val+''+response.currency_right);
				if(response.nocheckout==''){
					$('.wppizza-cart-discount-label').html(response.order_value.discount.lbl);

					/*addcurrency if discount applies**/
					if(response.order_value.discount.val!=''){
						$('.wppizza-cart-discount-value').html('<span class="wppizza-minus"></span>'+response.currency_left+''+response.order_value.discount.val+''+response.currency_right);
					}else{
						$('.wppizza-cart-discount-value').html(response.order_value.discount.val);
					}
					$('.wppizza-cart-delivery-charges-label').html(response.order_value.delivery_charges.lbl);
					/*addcurrency if its not free delivery**/
					if(response.order_value.delivery_charges.val!=''){
					$('.wppizza-cart-delivery-charges-value').html(response.currency_left+''+response.order_value.delivery_charges.val+''+response.currency_right);
					}else{
					$('.wppizza-cart-delivery-charges-value').html(response.order_value.delivery_charges.val);
					}
					/**tax**/
					$('.wppizza-cart-tax-label').html(response.order_value.item_tax.lbl);
					$('.wppizza-cart-tax-value').html(response.order_value.item_tax.formatted);

					/**tax included**/
					$('.wppizza-cart-tax-included-label').html(response.order_value.taxes_included.lbl);
					$('.wppizza-cart-tax-included-value').html(response.currency_left+''+response.order_value.taxes_included.val+''+response.currency_right);

					$('.wppizza-cart-total-label').html(response.order_value.total.lbl);
					$('.wppizza-cart-total-value').html(response.currency_left+''+response.order_value.total.val+''+response.currency_right);
				}
				if(response.nocheckout!='' || response.items.length==0){
					$('.wppizza-cart-discount-label').html('');
					$('.wppizza-cart-discount-value').html('');
					$('.wppizza-cart-delivery-charges-label').html('');
					$('.wppizza-cart-delivery-charges-value').html('');
					$('.wppizza-cart-total-label').html('');
					$('.wppizza-cart-total-value').html('');
					$('.wppizza-cart-tax-label').html('');
					$('.wppizza-cart-tax-value').html('');
					$('.wppizza-cart-tax-included-label').html('');
					$('.wppizza-cart-tax-included-value').html('');

				}
				if(response.items.length==0){
					$('.wppizza-cart-total-items-label').html('');
					$('.wppizza-cart-total-items-value').html('');
				}

				cartButton.removeAttr("disabled");/*re-enable place order button*/

				wppizzaCartRefreshed(wppizza.funcCartRefr,response);

			},'json').error(function(jqXHR, textStatus, errorThrown) {console.log("error : " + errorThrown);console.log(jqXHR.responseText);$('.wppizza-order #wppizza-loading').remove();});
		}});

	/***********************************************
	*
	*	[if there's a shopping cart on the page
	*	but we are currently closed, display alert]
	*
	***********************************************/
	$(document).on('click', '.wppizza-add-to-cart', function(e){
		if ($(".wppizza-open").length == 0 &&  $(".wppizza-cart").length > 0){
			alert(wppizza.msg.closed);
	}});
	/***********************************************
	*
	*	[customer selects self pickup , session gets set via ajax
	*	reload page to reflect delivery charges....
	*	only relevant if there's a shoppingcart or orderpage on page]
	*	[as it's an input element always use click instead of touchstart, cause iStuff is stupid]
	***********************************************/
	$(document).on('click', '#wppizza-order-pickup-sel, #wppizza-order-pickup-js', function(e){
		if (($(".wppizza-open").length > 0 &&  $(".wppizza-cart").length > 0) || $("#wppizza-send-order").length>0){
			var self=$(this);
			self.attr("disabled", true);/*disable checkbox to give ajax time to do things*/
			var selfValue=self.is(':checked');
			/*js alert if enabled, only on enablig checkbox*/
			if(selfValue==true){
				if(self.attr('id')=='wppizza-order-pickup-js'){
					/**make user confirm. only on checking it **/
					if(typeof wppizza.opt!=='undefined' && typeof wppizza.opt.pickupConfirm!=='undefined'){
						if(confirm(wppizza.msg.pickup)){
						//just continue
						}else{
							if(selfValue){
								self.attr('checked',false);//restore cheked attribute
							}else{
								self.attr('checked',true);//restore cheked attribute
							}
							self.attr("disabled", false);//make it selectable again
						return;
						}
					}else{
						alert(wppizza.msg.pickup);
					}
				}
			}else{
				/** if switching back , just add loading and reload. no need for yet another alert. let's assume at least a minimum of brains */
				/**cover whole page**/
		       	$('html').css({'position':'relative'});/*stretch html to make loading cover whole page*/
		     	$('body').prepend('<div id="wppizza-loading" style="opacity:0.8"></div>');
				$('.wppizza-ordernow').attr("disabled", "true");//disable send order button - if there is one
			}
			jQuery.post(wppizza.ajaxurl , {action :'wppizza_json',vars:{'type':'order-pickup','value':selfValue,'data':$('#wppizza-send-order').serialize(),'locHref':location.href,'urlGetVars':location.search}}, function(res) {
				var anchor='';
				if(typeof res.anchor!=='undefined'){
					anchor=res.anchor;
				}
				window.location.href=res.location+anchor;/*make sure page gest reloaded without confirm*/
				window.location.reload(true)
				return;
			},'json').error(function(jqXHR, textStatus, errorThrown) {console.log("error : " + errorThrown);console.log(jqXHR.responseText);});
	}});

	/******************************************************
	*
	*	[changing gateways, re-calculate handling charges
	*	if any are >0 which will in turn add the hidden field
	*	'#wppizza_calc_handling' we are checking first ]
	******************************************************/
	if($('#wppizza_calc_handling').length>0){
		var wppizzaGatewaySelected = $("input[name='wppizza-gateway']");
		if(wppizzaGatewaySelected.length==0){
			wppizzaGatewaySelected = $("select[name='wppizza-gateway']");
		}
		wppizzaGatewaySelected.change(function(e){
			$('#wppizza-send-order').prepend('<div id="wppizza-loading"></div>');
			if(wppizzaGatewaySelected.is(':radio')){
				var selectedGateway = $("input[name='wppizza-gateway']:checked").val();
			}else{
				var selectedGateway = $("select[name='wppizza-gateway']").val();
			}
			jQuery.post(wppizza.ajaxurl , {action :'wppizza_json',vars:{'type':'wppizza-select-gateway','data':$('#wppizza-send-order').serialize(),'selgw':selectedGateway}}, function(res) {
				window.location.href=window.location.href;/*make sure page gest reloaded without confirm*/
				//window.location.reload(true);
				return;
			},'json').error(function(jqXHR, textStatus, errorThrown) {	$('#wppizza-send-order #wppizza-loading').remove(); console.log("error : " + errorThrown);console.log(jqXHR.responseText);});
		});
	}
	/***********************************************
	*
	*	[if we are trying to add to cart by clicking on the title
	*	but there's more than one size to choose from, display alert]
	*	[provided  there's a cart on page and we are open]
	***********************************************/
	/*more than one size->choose alert*/
	$(document).on('click', '.wppizza-trigger-choose', function(e){
		if ($(".wppizza-open").length > 0 &&  $(".wppizza-cart").length > 0){
			alert(wppizza.msg.choosesize);
	}});
	/*only one size, trigger click*/
	$(document).on('click', '.wppizza-trigger-click', function(e){
		if ($(".wppizza-open").length > 0 &&  $(".wppizza-cart").length > 0){
			/*just loose wppizza-article- from id*/
			 var ArticleId=this.id.split("-");
			ArticleId=ArticleId.splice(2);
			ArticleId = ArticleId.join("-");
			/**make target id*/
			target=$('#wppizza-'+ArticleId+'');
			/*trigger*/
			target.trigger('click');
	}});

	/***********************************************
	*
	*	[order form: login or continue as guest]
	*
	***********************************************/
	$(document).on('click', '#wppizza-login,#wppizza-login-cancel', function(e){
		$("#wppizza-user-login-action").toggle(300);
		$("#wppizza-user-login-option>span>a").toggle();
	});
	$(document).on('click', '#wppizza_btn_login', function(e){/**changed to click so iphone understands it too*/
		$("#wppizza-user-login-action").append('<div id="wppizza-loading"></div>');
	});
	$(document).on('change', '#wppizza_account', function(e){
		$("#wppizza-user-register-info" ).toggle(200);
		$(".wppizza-login-error").remove();

	});
	/****insert nonce too via js *******/
	if($("#wppizza-send-order").length>0){
		jQuery.post(wppizza.ajaxurl , {action :'wppizza_json',vars:{'type':'nonce','val':'register'}}, function(nonce) {
			$('#wppizza-create-account').append(nonce);
		},'html').error(function(jqXHR, textStatus, errorThrown) {console.log("error : " + errorThrown);console.log(jqXHR.responseText);});
	}


	/*******************************************************************
	*	[validate and submit order page]
	*	gateway could be either by dropdown,
	*	radio, or if only one, hidden elm
	*******************************************************************/
	$(document).on('click', '.wppizza-ordernow', function(e){
		$('#wppizza-send-order').validate().settings.ignore = "";
	});
	/*******************************
	*	[validate tips/gratuities]
	*******************************/
	/**current tip value set **/
	var wppizzaTipsField=$("#wppizza-send-order #ctips");
	var wppizzaCTipsCurr=wppizzaTipsField.val();
	/**stop submitting form if we are hitting enter on tip field and just apply tip**/
	wppizzaTipsField.keydown(function(event) {
		if(event.which == 13 || event.which == 35){
			event.preventDefault();
			$('#wppizza-ctips-btn').trigger('click');
		return false;
		}
	});

	/**click should work here even on iStupid as it's a button **/
	$(document).on('click', '#wppizza-ctips-btn', function(e){
		/*we only want to validate the tips, so lets igmore everythig else*/
		$('#wppizza-send-order').validate().settings.ignore = "#wppizza-send-order>fieldset>input,#wppizza-send-order>fieldset>textarea,#wppizza-send-order>fieldset>select";
		var isValid=$("#wppizza-send-order").valid();
		if(isValid){
			var wppizzaCTipsNew=$("#wppizza-send-order #ctips").val();
			/**only update/refresh if the value has actually changed**/
	  		if(wppizzaCTipsCurr!=wppizzaCTipsNew){
	  			$("#wppizza-send-order").prepend('<div id="wppizza-loading" style="opacity:0.8"></div>');
				jQuery.post(wppizza.ajaxurl , {action :'wppizza_json',vars:{'type':'add_tips','data':$('#wppizza-send-order').serialize(),'locHref':location.href,'urlGetVars':location.search}}, function(res) {
				window.location.href=res.location;/*make sure page gest reloaded without confirm*/
				return;
				},'json');
	  		}
		}
	});
	/*******************************************
	*	[validation login]
	*******************************************/
	$("#wppizza-login-frm").validate({});
	/*******************************************
	*	[ini validation]
	*******************************************/
	$("#wppizza-send-order").validate({
			rules: {
	   			ctips: {
	      			number: true
	    		}
	  		},
	  		invalidHandler: function(form, validator) {

		        if (!validator.numberOfInvalids()){
		            return;
		        }
		        /**check if element is in view*/
  				var errorElem = $(validator.errorList[0].element);
    			var currentWindow = $(window);

    			var docViewTop = currentWindow.scrollTop();
    			var docViewBottom = docViewTop + currentWindow.height();

			    var elemTop = errorElem.offset().top;
    			var elemBottom = elemTop + errorElem.height();

		        var inView= ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));

		        /**scroll into view if needed - +50px to allow for minicart for example and to generally not make it that close to the top*/
		        if(!inView){
		        	$('html, body').animate({
			            scrollTop: errorElem.offset().top-50
		        	}, 300);
		        }
  			},
			submitHandler: function(form) {
				$('.wppizza-ordernow').attr('disabled', 'true');//stop double clicks
				var hasClassAjax=false;
				var hasClassCustom=false;
				if($("input[name='wppizza-gateway']").length>0){
					var elm = $("input[name='wppizza-gateway']");
					if(elm.is(':radio')){
						var selected = $("input[name='wppizza-gateway']:checked");
					}else{
						var selected = elm;
					}
					hasClassAjax=selected.hasClass("wppizzaGwAjaxSubmit");
					hasClassCustom=selected.hasClass("wppizzaGwCustom");
				}else{
					var selected = $("select[name='wppizza-gateway']");
					hasClassAjax=$("select[name='wppizza-gateway'] option:selected").hasClass("wppizzaGwAjaxSubmit");
					hasClassCustom=$("select[name='wppizza-gateway'] option:selected").hasClass("wppizzaGwCustom");
				}

				var self=$('#wppizza-send-order');
				var currVal = selected.val();
				var profileUpdate=$("#wppizza_profile_update").is(':checked');
				if(profileUpdate==true){
					jQuery.post(wppizza.ajaxurl , {action :'wppizza_json',vars:{'type':'profile_update','data':self.serialize()}}, function(response) {
						//console.log(response);
					},'html');
				}

				/***if we want to also register account check this first**/
				var wppizzaLoginElm=$("#wppizza-user-login");
				var wppizzaLoginErr=$(".wppizza-login-error");/*remove any previous errors*/
				if(wppizzaLoginErr.length>0){
					wppizzaLoginErr.remove();
				}

				var wppizzaLoginSelect=$("input[type=radio][name='wppizza_account']:checked");

				wppizzaLoginElm.hide();
				if(typeof wppizzaLoginSelect!=='undefined' && wppizzaLoginSelect.val()=='register'){
					self.prepend('<div id="wppizza-loading"></div>');
					jQuery.post(wppizza.ajaxurl , {action :'wppizza_json',vars:{'type':'new-account','data':self.serialize()}}, function(response) {
						/**account/email exists**/
						if(typeof response.error!=='undefined'){
							$('#wppizza-user-register-info').append(response.error);
							$('#wppizza-send-order #wppizza-loading').remove();
							wppizzaLoginElm.show();
							return;
						}
						/***all is well. go ahead with stuff**/
						if(typeof response.error==='undefined'){
							wppizzaSelectSubmitType(self,currVal,hasClassAjax,hasClassCustom,form);
						}
					},'json');
					return;
				}else{
					/**we are not registering a new account, so just submit as planned**/
					wppizzaSelectSubmitType(self,currVal,hasClassAjax,hasClassCustom,form);
				}
			}
		});


	/******************************
	* submit via ajax or send form
	*******************************/
	var wppizzaSelectSubmitType=function(self,currVal,hasClassAjax,hasClassCustom,form){
		/*****confirmation page enabled*****/
		if(typeof wppizza.cfrm!=='undefined' && !self.hasClass('wppizza-confirm-order')){
			$('#wppizza-user-login').empty().remove();
			self.prepend('<div id="wppizza-loading"></div>');
			jQuery.post(wppizza.ajaxurl , {action :'wppizza_json',vars:{'type':'confirmorder','data':self.serialize(),'hasClassAjax':hasClassAjax,'hasClassCustom':hasClassCustom}}, function(response) {
						self.html(response);//replace the form contents
						self.addClass('wppizza-confirm-order');/*set class so we dont do this again**/
						$('#wppizza-send-order #wppizza-loading').remove();
			},'html').error(function(jqXHR, textStatus, errorThrown) {$('#wppizza-send-order #wppizza-loading').remove();console.log("error : " + errorThrown);console.log(jqXHR.responseText);});
			return;
		}

		/**first make sure we are still open, as a customer may have been staying on th eoreder page for ages and shop is now closed**/
		jQuery.post(wppizza.ajaxurl , {action :'wppizza_json',vars:{'type':'checkifopen'}}, function(response) {
			if(typeof response.isclosed!=='undefined'){
				alert(response.isclosed);
				window.location.href=window.location.href;/*make sure page gest reloaded without confirm*/
				window.location.reload(true);
				return;
			}

			/**customised submit/payment via js window/overlay for example - will have to provide its own script**/
			if(hasClassCustom){
				/**custom js of gateway*/
				window['wppizza' + currVal + 'payment']();
				/***also save set customr data input fields in session data and update db customer_ini**/
				jQuery.post(wppizza.ajaxurl , {action :'wppizza_json',vars:{'type':'wppizza-set-userdata','data':$('#wppizza-send-order').serialize(),'hash': $('#wppizza_hash').val()}}, function(res) {
					//console.log(res);
				},'json').error(function(jqXHR, textStatus, errorThrown) {	$('#wppizza-send-order #wppizza-loading').remove(); console.log("error : " + errorThrown);console.log(jqXHR.responseText);});

				return;
			}
			/**cod->transmit form via ajax if cod or forced by gw settings (i.e $this->gatewayTypeSubmit = 'ajax')*/
			if(currVal=='cod' || hasClassAjax){
				self.prepend('<div id="wppizza-loading"></div>');
				$('#wppizza-user-login').empty().remove();
				jQuery.post(wppizza.ajaxurl , {action :'wppizza_json',vars:{'type':'sendorder','data':self.serialize()}}, function(response) {
					$('html, body').animate({scrollTop : 0},300);/*scroll to top*/
					$('#wppizza-send-order #wppizza-loading').remove();
					self.html('<div id="wppizza-order-received">'+response+'</div>');
				},'html').error(function(jqXHR, textStatus, errorThrown) {$('#wppizza-send-order #wppizza-loading').remove();console.log("error : " + errorThrown);console.log(jqXHR.responseText);});
			}else{
				self.prepend('<div id="wppizza-loading" style="opacity:0.8;"></div>');
				form.submit();
				return;
			}

		},'json').error(function(jqXHR, textStatus, errorThrown) {$('#wppizza-send-order #wppizza-loading').remove();console.log("error : " + errorThrown);console.log(jqXHR.responseText);});

	};
	/******************************
	* set error messages
	*******************************/
	jQuery.extend(jQuery.validator.messages, {
    	required: wppizza.validate_error.required,
    	email: wppizza.validate_error.email,
    	number: wppizza.validate_error.decimal
	});
	/**allow for commas in number validation but no negatives**/
	$.validator.methods.number = function (value, element) {
	    return this.optional(element) || /^(?:\d+|\d{1,3}(?:[\s\.,]\d{3})+)(?:[\.,]\d+)?$/.test(value);
	    //return this.optional(element) || /^-?(?:\d+|\d{1,3}(?:[\s\.,]\d{3})+)(?:[\.,]\d+)?$/.test(value);//this would allow negatives too
	}
	/******************************
	* Let's make IE7 IE8 happy and stop submitting while other stuff is going on such as adding items etc
	*******************************/
	$(document).on('click', '.wppizza-cart-button>a', function(e){
		e.preventDefault(); e.stopPropagation();
		var attr = $(this).attr('disabled');
		if (typeof attr !== 'undefined' && attr !== false){}else{
        	var url=jQuery(this).attr("href");
	        window.location.href = url;
			return;
		}
	});
	/***********************************************
	*
	*	[using cache plugin, load cart dynamically]
	*	[as the cart does not exist onload we will also
	*	have to execute the sticky cart function after it has been created]
	***********************************************/
	if(typeof wppizza.usingCache!=='undefined'){
		var wppizzaNoCacheAttr=$('#wppizza-cart-nocache-attributes').val();
		jQuery.post(wppizza.ajaxurl , {action :'wppizza_json',vars:{'type':'hasCachePlugin','attributes':wppizzaNoCacheAttr}}, function(response) {
			$('.wppizza-cart-nocache').html(response.markup);
			wppizzaCartRefreshed(wppizza.funcCartRefr,response.cart);/**also run any cart refreshed functions**/
		},'json').complete(
			function(){wppizzaCartStickyLoad();}/*on complete, exec sticky cart if enabled*/
		).error(function(jqXHR, textStatus, errorThrown) {console.log("error : " + errorThrown);console.log(jqXHR.responseText);});
	}else{
		/*if no cache, just exec sticky cart function*/
		wppizzaCartStickyLoad();
	}

	/***********************************************
	*
	*	hijacking other cpts (or just a button with dropdown elsewhere)
	*	adding an add to cart button linked to a specific menu item
	*	selecting from dropdown
	***********************************************/
	/*set id on trigger element when dropdown changes*/
	$(document).on('change', '.wppizza-add-to-cart-size', function(e){
       	var self=$(this);
       	/**add class*/
       	//self.addClass('wppizza-add-to-cart');
		/*get id*/
       	var id=$(this).attr('id');
       	var postid=id.split('-').pop(-1);
       	var selVal=$('#wppizza-add-to-cart-size-'+postid+'').val();
		/*set id on element to trigger*/
		var elm=self.closest('span').find('.wppizza-add-to-cart').prop('id', 'wppizza-'+selVal+'');
	});
	/**trigger click on element when button clicked*/
	$(document).on('click', '.wppizza-add-to-cart-select', function(e){
		var self=$(this);
		var triggerElm=self.next();
		triggerElm.trigger('click');
	});
	/***********************************************
	*
	*	[using totals shortcode,load via js]
	*
	***********************************************/
	wppizzaShortcodeTotals = function(){
		if ($(".wppizza-totals").length > 0){
			jQuery.post(wppizza.ajaxurl , {action :'wppizza_json',vars:{'type':'gettotals'}}, function(res) {
				var curr=$(".wppizza-totals-currency");
				var total=$(".wppizza-total");
				var itemcount=$(".wppizza-totals-itemcount");
				var button=$(".wppizza-totals-checkout-button");
				var viewcart=$(".wppizza-totals-viewcart");

				/**no items in cart**/
				if(res.items.length<=0){
					curr.html(res.currency);
					total.html(res.order_value.total_price_items.val);
					if (itemcount.length > 0){
						itemcount.html('&nbsp;');//empty if 0
					}
					if (viewcart.length > 0){
						viewcart.html('&nbsp;');//empty if 0
					}
				}else{
					curr.html(res.currency);
					if ($(".wppizza-total-items").length > 0){
						total.html(res.order_value.total_price_items.val);
					}else{
						total.html(res.order_value.total.val);
					}
					/**item count*/
					if (itemcount.length > 0){
						itemcount.html(res.itemcount);
					}
					/**viewcart */
					if (viewcart.length > 0){
						viewcart.html(res.viewcart);
					}
				}
				/*print button (will be emoty if no item in cart**/
				button.html(res.button);

			},'json').error(function(jqXHR, textStatus, errorThrown) {$('#wppizza-send-order #wppizza-loading').remove();console.log("error : " + errorThrown);console.log(jqXHR.responseText);});
		}
	}
	wppizzaShortcodeTotals();

	/***********************************************
	*
	*	[show minicart if main cart is not in view]
	*	(provided it's enabled of course)
	***********************************************/
	wppizzaMiniCart = function(){
		var miniCartElm=$("#wppizza-mini-cart");
		var wppizzaCartCached=$(".wppizza-cart-nocache");

		if (miniCartElm.length > 0){

			/**current window**/
			var currentWindow = $(window);
			/**current elm top padding**/
			//var elmPaddingTop=$("body").css("padding-top");

    		/**set padding to element if set**/
    		if(typeof wppizza.crt.mCartPadTop !=='undefined'){
    			var addElmPaddingTop=wppizza.crt.mCartPadTop;
				/**add padding to set elements**/
				if(typeof wppizza.crt.mCartPadElm!=='undefined'){
					var elmToPad=$(''+wppizza.crt.mCartPadElm+'');
				}else{
    				var elmToPad=$('body');
				}
    		}

			/**get the element in use*/
			if(wppizzaCartCached.length>0){
				/**using cart with cache**/
				var mainCartElm = wppizzaCartCached;
			}else{
				var mainCartElm = $('.wppizza-cart');
			}
			/*add to id.class instead of body if set*/
			if(typeof wppizza.crt.mCartElm !=='undefined'){
				miniCartElm.prependTo(''+wppizza.crt.mCartElm+'');
			}
			/*always shown, display and skip everything after*/
			if(typeof wppizza.crt.mCartStatic!=='undefined'){

				/**add padding to set elements**/
				if(typeof elmToPad!=='undefined'){
					elmToPad.css({'padding-top': '+='+addElmPaddingTop+'px'});
				}
				return;
			}

			var miniCartIni=true;

			/**on initial load**/
		    setTimeout(function(){
		    	wppizzaMiniCartDo(currentWindow, miniCartElm, mainCartElm, addElmPaddingTop, elmToPad);
		    	miniCartIni=false;
		    },500);

		    /**on scroll**/
		    var showMiniCart;
			$(window).scroll(function () {
				/**only on subsequent scrolls not when page is already scrolled on load*/
				if(!miniCartIni){
					clearTimeout(showMiniCart);
					showMiniCart=setTimeout(function(){
						wppizzaMiniCartDo(currentWindow, miniCartElm, mainCartElm,addElmPaddingTop, elmToPad);
					},300);
				}
			});
		    /**on resize**/
		    $(window).resize(function() {
				/**only on subsequent scrolls not when page is already scrolled on load*/
				if(!miniCartIni){
					clearTimeout(showMiniCart);
					showMiniCart=setTimeout(function(){
						wppizzaMiniCartDo(currentWindow, miniCartElm, mainCartElm,addElmPaddingTop, elmToPad);
					},300);
				}
			});
		}
	}
	wppizzaMiniCart();

	var wppizzaMiniCartDo = function(currentWindow, miniCartElm, mainCartElm, addElmPaddingTop, elmToPad){

		/*get width**/
    	var docViewWidth = currentWindow.width();
    	/*max width**/
    	if(typeof wppizza.crt.mCartMaxWidth !=='undefined'){
    		var docWidthLimit=wppizza.crt.mCartMaxWidth;
    	}

		/**skip if wider than max width set or on oderpage**/
		if((typeof docWidthLimit !=='undefined' && docViewWidth>docWidthLimit) || typeof wppizza.isCheckout!=='undefined'){
			/*in case its still visible*/
			if(miniCartElm.is(':visible')){
				miniCartElm.fadeOut(250);
			}
			return;
		}

    	var docViewTop = currentWindow.scrollTop();
    	var docViewBottom = docViewTop + currentWindow.height();
		var elemTop = mainCartElm.offset().top;
		var elemBottom = elemTop + mainCartElm.height();
		var notInView= (elemBottom<=docViewTop || elemTop>=docViewBottom);

		/*fade in minicart if needed**/
		if(notInView && miniCartElm.is(':hidden')){
			/*add padding if set **/
			if(typeof elmToPad !=='undefined'){
				elmToPad.animate({'padding-top': '+='+addElmPaddingTop+'px'},250);
			}
			miniCartElm.fadeIn(250);
		}

		if(!notInView && miniCartElm.is(':visible')){
			/*reset padding if required **/
			if(typeof elmToPad !=='undefined'){
				elmToPad.animate({'padding-top': '-='+addElmPaddingTop+'px'},250);
			}
			miniCartElm.fadeOut(250);
		}
	};
	/***********************************************
	*
	*	[show/scroll to cart from minicart viewcart button]
	*
	***********************************************/
	$(document).on('click', '.wppizza-totals-viewcart', function(e){
		var mainCartElm=$(".wppizza-cart");
		if(mainCartElm.length>0){
			var miniCartElm=$("#wppizza-mini-cart");
			var miniCartElmHeight=miniCartElm.outerHeight()
			var elmBottom = miniCartElm.position().top + miniCartElmHeight;
			/*add 10 px for prettyness*/
			var srollToPosition=mainCartElm.offset().top-(elmBottom+10)
			$('html, body').animate({
				/*scroll it to bottom of minicart element +10. rounded in case outerHeight throws fractions*/
    			scrollTop: Math.round(srollToPosition)
			}, 100);
		}
	});

});