<?php if ( ! defined( 'ABSPATH' ) ) exit;/*Exit if accessed directly*/ ?>
<?php
/****************************************************************************************
*
*
*	WPPizza - Category Loop Template
*	get/set variables
*
*
****************************************************************************************/
/******************************************
	if we are trying to get the loop from a shortcode/widget in another page
	not related to the wppizza custom post type we will have a different post type,
	so we explicitly set it here to generate the right classes etc
*******************************************/
	$post_type=WPPIZZA_POST_TYPE;

	/*********** plugin options **************/
	$currency=$options['order']['currency_symbol'];//put currency into a shorter variable. just easier to deal with further down
	$optionsDecimals=$options['layout']['hide_decimals'];
	$txt=$options['localization'];/**put localization vars into a shorter variable

	/**check if we have set the headers to be suppressed in wppizza->settings->layout**/
	if($options['layout']['suppress_loop_headers']){
		$noheader=1;
	}
	/*check if we are showing prices*/
	if($options['layout']['hide_prices']){$hidePrices=1;}
	/*check currency to left of price*/
	if($options['layout']['currency_symbol_left']){$currencyLeft=1;}
	/*check if we are hiding pricetiers if there's only one*/
	if($options['layout']['hide_single_pricetier']){$hidePricetier=1;}
	/*check if we are showing cart icon*/
	if($options['layout']['hide_cart_icon']){$hideCartIcon=' '.$post_type.'-no-cart';}else{$hideCartIcon='';}
	/*check if we are showing currency next to item*/
	if($options['layout']['hide_item_currency_symbol']){$hideCurrencySymbol=1;}
	/*check if online order has been enabled and if so , remove title and classes to disable adding to cart js*/
	if($options['layout']['disable_online_order']){$priceClass=''; $priceTitle='';}else{$priceClass=' '.$post_type.'-add-to-cart'; $priceTitle=' title="'.$txt['add_to_cart']['lbl'].'"';}
	/**add trigger adding to cart from title (h2) when there's only one pricetier***/
	if($options['layout']['add_to_cart_on_title_click']){$clickTrigger=1;}
	/**check if we want to sort and add by category to emails**/
	if($options['layout']['items_group_sort_print_by_category']){$groupItemByCat=1;}



/****************************************************************
	also, if we are retrieving the loop via a shortcode/widget
	not on a page with this custom post type
	we will have to set the query variable explicitly to this category
	otherwise just get the one we have and get term descriptions, id's etc
*****************************************************************/
if(!is_single()){

	if(isset($query_var)){
		/*
			bestsellers,single etc might have multiple cats
			so set termQuery, termSlug , cat id etc
			to something appropriate as much as we can
		*/
		if(count($query->term_id)>1){
			$termSlug=$query->name;
			$termQuery=$query_var;
			$classIdent=$query->name;
			$termDetails=$query;
			$categoryId=$query->category_id;
		}else{
			$termSlug=$query_var;
			$termQuery=$termSlug;
			$classIdent=$query_var;
			$termDetails=$query;
			$categoryId=$query->term_id;
		}
	}else{
		$termSlug=get_query_var( WPPIZZA_TAXONOMY );
		$termQuery=$termSlug;
		$classIdent=$termSlug;
		$catCount=1;//set as 1 category
		$loopCount=0;//set static as no template loop
		$termDetails = get_term_by( 'slug', $termSlug, WPPIZZA_TAXONOMY);
		$categoryId=0;/*ini in case someone uses shortcode with nonexistant category***/
		if(is_object($termDetails)){
			$categoryId=$termDetails->term_id;
		}
	}
}
/********************************************************
	single posts might be in more than one category,
	however if we  have no idea here which one it should be ,
	we just take the first one unless set by GET[c]
********************************************************/
if(is_single() && get_post_type()==$post_type){
	$termDetails = wp_get_post_terms( $post->ID, WPPIZZA_TAXONOMY);
	$termSlug='';/*ini as unknown*/
	$termQuery='';/*ini as unknown*/
	$categoryId=0;/*ini as unknown*/
	$classIdent='';/*ini as unknown*/
	$catCount=1;//set as 1 category
	$loopCount=0;//set static as no template loop

	if ($termDetails && ! is_wp_error($termDetails)){
		if(isset($_GET['c'])){/*if we know which category this is apply, otherwsise use first available**/
			$taxonomies = wp_list_pluck( $termDetails, 'term_id', 'slug');
			$termSlug=$_GET['c'];
			$termQuery=$termSlug;
			$classIdent=$termSlug;
			$categoryId=$taxonomies[$_GET['c']];
		}else{
			$termSlug=$termDetails[0]->slug;
			$termQuery=$termSlug;
			$classIdent=$termSlug;
			$categoryId=$termDetails[0]->term_id;
		}
	}
}
/**************************************************************************
	for people that cannot read or insist nevertheless on putting
	the shortcode on a post or other custom post type although
	- AS THE DOCUMENTAION SAYS - IT BELONGS ON PAGES ONLY


	NOTE: as we cannot use the current post id (as it's not a
	wppizza post) we'll do our best to get and set the appropriate vars


	ADVISE: THE BELOW IS REALLY NOT SUPPORTED AS SUCH.
	IT MIGHT OR MIGHT NOT WORK DEPENDING ON POST (TYPE) USED.
	just saying.....
**************************************************************************/
if(is_single() && get_post_type()!=$post_type){
	/*ignore all of the below if there's no query var to start off with*/
	if(isset($query_var)){

		$terms = get_terms(WPPIZZA_TAXONOMY);/**get all wppizza categories*/
		$taxonomies = wp_list_pluck( $terms, 'term_id', 'slug');/**array slug->id*/
		$termSlug='';/*ini as unknown*/
		$termQuery='';/*ini as unknown*/
		$categoryId=0;/*ini as unknown*/
		$termDetails=array();/*ini as unknown*/
		$classIdent='';/*ini as unknown*/


		/**lets see if we can find one**/
		if(isset($taxonomies[$query_var])){
			$termSlug=$query_var;
			$termQuery=$termSlug;
			$classIdent=$termSlug;
			$categoryId=$taxonomies[$query_var];

			$tDetails=array();
			foreach($terms as $term){
				if($term->term_id==$categoryId){
					$tDetails=$term;
					break;
				}
			}
			/*get details from cat id**/
			$termDetails[0] = $tDetails;
			$termDetails[0]->filter = 'raw';
		}
	}
}

/****************************************************************************************
*
*	[header classes]
*
****************************************************************************************/
/*header*/
$headerClass=array();
if($catCount<=1){
	$headerClass[]='page-header';//only if single
}
$headerClass[]='entry-header';
$headerClass[]=''.$post_type.'-header';
$headerClass[]=''.$post_type.'-header-'.$classIdent.'';
$headerClass=apply_filters('wppizza_filter_loop_header_class',$headerClass, $catCount, $loopCount);
$headerclasses=implode(" ",$headerClass);
/*h1*/
$headerClassH1=array();
if($catCount<=1){
	$headerClassH1[]='page-title';//only if single
}
$headerClassH1[]='entry-title';
$headerClassH1[]=''.$post_type.'-entry-title';
$headerClassH1=apply_filters('wppizza_filter_loop_header_class_h1',$headerClassH1, $catCount, $loopCount);
$headerclassesh1=implode(" ",$headerClassH1);

/*************************************************************
	build and run the query
*************************************************************/
	$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
	$args = array(
		'post_type' => ''.WPPIZZA_POST_TYPE.'',
		'posts_per_page' => $options['layout']['items_per_loop'],
		'paged' => $paged ,
		'post__not_in' => !empty($exclude) ? $exclude : '' , /*ADDED in v.2.7.3*/
		'post__in' => !empty($include) ? $include : '' , /*ADDED in v.2.8.9.7*/
		'tax_query' => array(
			array(
				'taxonomy' => ''.WPPIZZA_TAXONOMY.'',
				'field' => 'slug',
				'terms' => $termQuery,
				'include_children' => false
			)
		),
		'orderby'=>''.$options['layout']['items_sort_orderby'].'',
		'order'=>''.$options['layout']['items_sort_order'].''
	);

	/**new in version 2.5. currenly used to display single posts***/
	$args = apply_filters('wppizza_filter_loop', $args);
	/**execute query**/
	$the_query = new WP_Query( $args );


	/***********************************

		create (set add) required variables
		for grid based layout

	************************************/
	/***set/add grid variables**/
	if($options['layout']['style']==='grid'){

		/*columns per row in grid*/
		$options['grid']['columns']=$options['layout']['style_grid_columns'];

		/*required article dummies*/
		$dummies=$options['grid']['columns']-($the_query->post_count%$options['grid']['columns']);
		$options['grid']['dummies']=($dummies<$options['grid']['columns']) ? $dummies : 0 ;
		
		/*required sections*/
		$options['grid']['sections']=ceil($the_query->post_count/$options['grid']['columns'])-1;
	}
?>