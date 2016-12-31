<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php
extract(shortcode_atts(array('type' => ''), $atts));

/**********************************************
	[default page]
		possible attributes:
		category='pizza' 		(optional: '[category-slug]')
		noheader='1' 			(optional: 'anything')
		showadditives='1' 		(optional[bool]: 0 or 1)
		exclude='6,5,8' 		(optional [comma separated menu item id's]): exclude some id's
		include='6,5,8' 		(optional [comma separated menu item id's]): include only these id's (overrides exclude)
		bestsellers='11' 		(optional: integer - shows n number of bestsellers, sorted by number of purchases desc)
	example: 		[wppizza category='pizza' noheader='1' exclude='6,7,8']
	or
	example: 		[wppizza category='pizza' noheader='1' include='6,7,8']
**********************************************/
if($type==''){
	ob_start();
	$this->wppizza_include_shortcode_template('category',$atts);
	$markup = ob_get_clean();
return $markup;
}
/**********************************************
	[single]
		possible attributes:
		single='11' 		(required [str] single id of menu item)
	example: 		[wppizza single='11' ]
**********************************************/
if($type=='single'){
	ob_start();
	$this->wppizza_include_shortcode_template($type,$atts);
	$markup = ob_get_clean();
return $markup;
}
/**********************************************
	[navigation]
		possible attributes:
		type='navigation' 		(required [str])
	 	title='some title' 		(optional[str]: will render as h2 as first element in cart elemnt if set)
	 	parent='slug-name' 		(optionsl [str]): only show child categories of this slug
	 	exclude='6,5,8' 		(optional [comma separated category id's]): exclude some id's
	example: 		[wppizza type='navigation' title='some title' parent='slug-name' exclude='6,5,8']
**********************************************/
if($type=='navigation'){
	ob_start();
	$this->wppizza_include_shortcode_template($type,$atts);
	$markup = ob_get_clean();
return $markup;
}
/**********************************************
	[cart]
	possible attributes:
		type='cart' 			(required [str])
 		openingtimes='1' 		(optional[bool]: anything. if its defined it gets displayed)
 		orderinfo=1				(optional[bool]: anything. if its defined it gets displayed)
 		stickycart=1			(optional[bool]: anything. if its defined it scrolls)
 		minicart=1				(optional[bool]: anything. if its defined it gets displayed)
 		width='200px' 			(optional[str]: value in px or % ) (although under 150px is probably bad)
 		height='200' 			(optional[str]: value in px )
	example: 		[wppizza type='cart']
**********************************************/
if($type=='cart'){
	/*disable shoppingcart when disable_online_order is set */
	if(isset($this->pluginOptions['layout']['disable_online_order']) && $this->pluginOptions['layout']['disable_online_order']==1){
		$markup='';
		return $markup;
	}else{
		/**caching plugin enabled->insert empty div to be filled with cart by ajax request**/
		if($this->pluginOptions['plugin_data']['using_cache_plugin'] && !isset($atts['request'])){
			$isOpen=wpizza_are_we_open($this->pluginOptions['opening_times_standard'],$this->pluginOptions['opening_times_custom'],$this->pluginOptions['times_closed_standard']);
			$markup="<div class='wppizza-cart-nocache'><div class='wppizza-cart-nocacheinner'>";
			$markup.="<div id='wppizza-loading'></div>";
				/**we need to pass on the attributes too*/
				$markup.="<input type='hidden' id='wppizza-cart-nocache-attributes' name='wppizza-cart-nocache-attributes' value='".json_encode($atts)."' />";
			if($isOpen==1){/*we need this to not break other wppizza extensions**/
				$markup.="<input type='hidden' class='wppizza-open' name='wppizza-open' />";
			}
			$markup.="</div></div>";
		}else{
			ob_start();
			$this->wppizza_include_shortcode_template($type,$atts);
			$markup = ob_get_clean();
		}
		$markup=apply_filters('wppizza_after_cart_markup',$markup);

		/**show mini cart if main cart out of view**/
		if(isset($atts['minicart'])){
			/**add minicart to show if main cart is out of view**/
			add_action('wp_footer', array( $this, 'wppizza_mini_cart_if_cart_invisible'),99);
		}

	return $markup;
	}
}
/**********************************************
	[orderpage]
	possible attributes:
		type='orderpage' 			(required [str])
	example: 		[wppizza type='orderpage']
**********************************************/
if($type=='orderpage'){
	/*disable orderpage when disable_online_order is set */
	if(isset($this->pluginOptions['layout']['disable_online_order']) && $this->pluginOptions['layout']['disable_online_order']==1){
		$markup='';
		return $markup;
	}else{
		ob_start();
		$this->wppizza_include_shortcode_template($type);
		$markup = ob_get_clean();
	return $markup;
	}
}
/**********************************************
	[orderhistory]
	possible attributes:
		type='orderhistory' 			(required [str])
		multisite='1' 					(optional [str])
	example: 		[wppizza type='orderhistory']
**********************************************/
if($type=='orderhistory'){
	/*disable orderpage when disable_online_order is set */
	if(isset($this->pluginOptions['layout']['disable_online_order']) && $this->pluginOptions['layout']['disable_online_order']==1){
		$markup='';
		return $markup;
	}else{
		ob_start();
		$this->wppizza_include_shortcode_template($type,$atts);
		$markup = ob_get_clean();
	return $markup;
	}
}
/**********************************************
	[openingtimes]
	possible attributes:
	type='openingtimes' (required [str])
	example: 		[wppizza type='openingtimes']
	returns grouped opening times in a string
**********************************************/
if($type=='openingtimes'){
	$options = $this->pluginOptions;
	$markup = wppizza_frontendOpeningTimes($options);
}
/**********************************************
	[additives]
	possible attributes:
	type='additives' (required [str])
	example: 		[wppizza type='additives']
	returns additives in a html string
**********************************************/
if($type=='additives'){
	ob_start();
	$this->wppizza_include_shortcode_template($type,$atts);
	$markup = ob_get_clean();
	return $markup;
}
/**********************************************
	[totals]
	possible attributes:
	type='totals' (required [str])
	value='items' (optional[str]) - if used , only displays value of items as ooposed to totals including delivery etc
	itemcount='left|right'  (optional [str]) - if used , count of item will be displayed left or right of the total
	checkout='bool | button' (optional. mixed) - set to 'button' will display a button to go to order page, anything else will wrap the whole thing into a link instead
	example: 		[wppizza type='totals']
	returns div that with current cart totals (loaded via js)
**********************************************/
if($type=='totals'){
	$options = $this->pluginOptions;
	$markup = wppizza_frontendTotals($options,$atts);
}
/**********************************************
	[searchbox]
		possible attributes:
		type='search' 		(required [str])
	 	include='wppizza,post,page,attachment,revision,nav_menu_item' (optional[str]: include menu items, posts, pages and/or other cpts respectively)
	 	loggedinonly='1' (optional[bool]: anything. if defined searchform only gets displayed for logged in users)
	example: 		[wppizza type='search'  include='wppizza,post,page' loggedinonly='1']
**********************************************/
if($type=='search'){
	ob_start();
	$this->wppizza_include_shortcode_template($type,$atts);
	$markup = ob_get_clean();
return $markup;
}
/**********************************************
	[add_item_to_cart_button]
	required attributes:
	type='add_to_cart_button' 	(required [str])
	id='6' 						(required [int]) - id of wppizza menu item
	size='0'  					(optional [int]) - id (zero indexed) of wppizza menu size for that item
	single='1'  				(optional [bool]) - if set will only show single button without dropdown
	example: 					[wppizza type='add_item_to_cart_button' id='6' size='0']
	returns a button (with or without dropdown) to add a menu item to cart
**********************************************/
if($type=='add_item_to_cart_button'){
	$markup = $this->wppizza_add_item_to_cart_button($atts, $this->pluginOptions);
}
?>