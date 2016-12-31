<?php
/*
Plugin Name: WPPizza
Description: Maintain your restaurant menu online and accept cash on delivery orders. Set categories, multiple prices per item and descriptions. Conceived for Pizza Delivery Businesses, but flexible enough to serve any type of restaurant.
Author: ollybach
Plugin URI: http://wordpress.org/extend/plugins/wppizza/
Author URI: https://www.wp-pizza.com
Version: 2.16.11.15
License:

  Copyright 2012 ollybach (dev@wp-pizza.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


/*******************************************************************************************

	constants that can be changed be definig them in the wp-config.php

*******************************************************************************************/
if(!defined('WPPIZZA_NAME')){
	define('WPPIZZA_NAME', 'WPPizza');/*allow change of name in admin, just set define('WPPIZZA_NAME', 'New Name') in the wp-config.php*/
}
/*
to save us having to mess around with templates for single items (when linked from search results for example) set an identifier in permalinks
to change the variable (in case there are namespace clashes or just if one prefers another var,  set define('WPPIZZA_SINGLE_VAR', 'new-var') in the wp-config.php (lowercase , no spaces)
*/
if(!defined('WPPIZZA_SINGLE_PERMALINK_VAR')){
	define('WPPIZZA_SINGLE_PERMALINK_VAR', 'menu_item');
}
/**set max line length for any plaintext emails/templates etc*/
if(!defined('WPPIZZA_PLAINTEXT_MAX_LINE_LENGTH')){
	define('WPPIZZA_PLAINTEXT_MAX_LINE_LENGTH', 74);
}
/**allow for some leeway for comments etc*/
if(!defined('WPPIZZA_PLAINTEXT_MAX_LINE_LENGTH_WORDWRAP')){
	define('WPPIZZA_PLAINTEXT_MAX_LINE_LENGTH_WORDWRAP', 80);
}
/*******************************************************************************************

	DO NOT EVEN THINK ABOUT CHANGING/EDITING ANY OF THE CONSTANTS BELOW

*******************************************************************************************/
define('WPPIZZA_VERSION', '2.16.11.15');
define('WPPIZZA_CLASS', 'WPPizza');
define('WPPIZZA_SLUG', 'wppizza');
define('WPPIZZA_POST_TYPE', ''.WPPIZZA_SLUG.'');
define('WPPIZZA_TAXONOMY', ''.WPPIZZA_POST_TYPE.'_menu');

/*******************************************************************************************

	some constants set for convenience

*******************************************************************************************/
define('WPPIZZA_PATH', plugin_dir_path(__FILE__) );
define('WPPIZZA_URL', plugin_dir_url(__FILE__) );
define('WPPIZZA_CHARSET',get_bloginfo('charset'));


/***************************************************************
*
*	[init plugin]
*
***************************************************************/
add_action('widgets_init', create_function('', 'register_widget("'.WPPIZZA_CLASS.'");'));
/***************************************************************
*
*	[uninstall]
*
***************************************************************/
register_uninstall_hook( __FILE__, 'wppizza_uninstall' );
/***************************************************************
*
*	[deactivate]
*
***************************************************************/
register_deactivation_hook( __FILE__, 'wppizza_deactivate' );
/***remove cronjobs****/
function wppizza_deactivate() {
	wp_clear_scheduled_hook( 'wppizza_cron' );
}

/***************************************************************
*
*	[CLASS]
*
***************************************************************/

if ( ! class_exists( ''.WPPIZZA_CLASS.'' ) ) {
class WPPizza extends WP_Widget {

	public $pluginVersion;
	protected $pluginSlug;
	protected $pluginLocale;
	public $pluginOptions;
	public $pluginSession;
	protected $pluginName;
	protected $pluginNagNotice;
	protected $pluginGateways;
	protected $pluginUrl;
	public $pluginOrderTable;


/********************************************************
*
*
*	[Constructor]
*
*
********************************************************/
 function __construct() {

	/**init constants***/
	$this->pluginVersion=''.WPPIZZA_VERSION.'';//increment in line with stable tag in readme and version above
	$this->pluginMinMysqlVersionRequired='5.5';
 	$this->pluginName="".WPPIZZA_NAME."";
 	$this->pluginSlug="".WPPIZZA_SLUG."";//set also in uninstall when deleting options
	$this->pluginOrderTable="".WPPIZZA_SLUG."_orders";
	$this->pluginLocale="wppizza-locale";/*leave this here for legacy reasons in other plugins*/
	$this->pluginOptions = get_option(WPPIZZA_SLUG,0);
	$this->pluginNagNotice=0;//default off->for use in updates to this plugin
	$this->pluginPath=__FILE__;
	/**to get the template paths, uri's and possible subdir and set vars accordingly**/
	$pathDirUri=$this->wppizza_template_paths();
	$this->pluginTemplateDir=$pathDirUri['template_dir'];/**to amend get_stylesheet_directory() according to whether wppizza subdir exists*/
	$this->pluginTemplateUri=$pathDirUri['template_uri'];/**to amend get_stylesheet_directory_uri() according to whether wppizza subdir exists*/
	$this->pluginLocateDir=$pathDirUri['locate_dir'];/**to add relevant subdir - if exists - to locate_template*/
	/*get WP timezone times**/
	$this->currentTime= current_time('timestamp');
	$this->currentTimezoneDate=date('Y-m-d H:i:s',$this->currentTime);/*for db input*/
	$this->currentTimeLocalized ="".date_i18n(get_option('date_format'),$this->currentTime)." ".date_i18n(get_option('time_format'),$this->currentTime)."";/*localized*/


	/********************************************************************************************
		set session per blogid when multisite and enabled to avoid having same cart
		contents between different network sites (unless we want this)
	*********************************************************************************************/
	if(is_multisite() ){
		$multisession=true;
		/*get settings from parent blog for  this**/
		switch_to_blog(BLOG_ID_CURRENT_SITE);
			$wppOptions=get_option('wppizza');
			if(!$wppOptions['plugin_data']['wp_multisite_session_per_site']){
				$multisession=false;
			}
		restore_current_blog();
		global $blog_id;
		if($multisession){
			$this->pluginSession=$this->pluginSlug.''.$blog_id;
		}else{
			$this->pluginSession=$this->pluginSlug;
		}
	}else{
		$this->pluginSession=$this->pluginSlug;
	}

	/*****************************************
		define session ident as constant if not
		yet defined
	*****************************************/
	if(!defined('WPPIZZA_SESSION_IDENT')){
		define('WPPIZZA_SESSION_IDENT', $this->pluginSession);
	}


	/**session name for user data for example such as address etc that keeps it's values across multisites**/
		$this->pluginSessionGlobal=$this->pluginSlug.'Global';


	/***************************************
		classname and description
	***************************************/
    $widget_opts = array (
        'classname' => WPPIZZA_CLASS,
        'description' => __('A Pizza Restaurant Plugin', 'wppizza-locale')
    );
	parent::__construct(false, WPPIZZA_NAME, $widget_opts );


    add_action('init', array($this, 'wppizza_load_plugin_textdomain'));

    /**allow overwriting of pluginVars in seperate class*/
    add_action('init', array( $this, 'wppizza_extend'),1);

	/**add wpml . must run front and backend (ajax request)***/
	add_action('init', array( $this, 'wppizza_wpml_localization_frontend'),99);

}

/*****************************************************************************************************************
*
*
*	[widget functions - apparently these have to be in main plugin when calling "extends WP_Widget"/"widgets_init"]
*	[althoug one can probably load them via includes - one day]
*
*
******************************************************************************************************************/
    /*****************************************************
     * load text domain on init.
     ******************************************************/
  	public function wppizza_load_plugin_textdomain(){
        load_plugin_textdomain('wppizza-locale', false, dirname(plugin_basename( __FILE__ ) ) . '/lang' );
    }
    /*****************************************************
     * Generates the administration form for the widget.
     * @instance    The array of keys and values for the widget.
     ******************************************************/
	function form($instance) {
    	include(WPPIZZA_PATH.'views/widget-admin.php');
    }
    /*******************************************************
     * Outputs the content of the widget.
     * @args            The array of form elements
     * @instance
     ******************************************************/
    function widget($args, $instance) {
		require(WPPIZZA_PATH.'views/widget.php');
    }
    /*******************************************************
     *
     * set default and return options for widget
     *
     ******************************************************/
	private function wppizza_default_widget_settings(){
		 $defaults=array(
            'title' => __("Shoppingcart", 'wppizza-locale'),
            'type' => 'cart',
            'suppresstitle' => '',
            'noheader' => '',
            'width' => '',
            'height' => '',
            'openingtimes' => 'checked="checked"',
            'orderinfo' => 'checked="checked"'
        );
		return $defaults;
	}
    /*******************************************************
     *
     * available main options to choose from in widget
     *
     ******************************************************/
	private function wppizza_type_options(){
			$items['category']=__('Category Page', 'wppizza-locale');
			$items['navigation']=__('Navigation', 'wppizza-locale');
			$items['cart']=__('Cart', 'wppizza-locale');
			$items['orderpage']=__('Orderpage', 'wppizza-locale');
			$items['openingtimes']=__('Openingtimes', 'wppizza-locale');
			$items['search']=__('Search', 'wppizza-locale');
		return $items;
	}
	/****************************************************************
	*
	*	[get/set Template Directories/Uri's. also check for subdir 'wppizza']
	*
	***************************************************************/
	function wppizza_template_paths(){
		$paths['template_dir']='';
		$paths['template_uri']='';
		$paths['locate_dir']='';
		$dir=get_stylesheet_directory();
		$uri=get_stylesheet_directory_uri();

		if(is_dir($dir.'/'.WPPIZZA_SLUG)){
			$paths['template_dir']=$dir.'/'.WPPIZZA_SLUG;
			$paths['template_uri']=$uri.'/'.WPPIZZA_SLUG;
			$paths['locate_dir']=WPPIZZA_SLUG.'/';
		}else{
			$paths['template_dir']=$dir;
			$paths['template_uri']=$uri;
			$paths['locate_dir']='';
		}

		return $paths;
	}

	/*******************************************************
	*
	*
	*	[set/save submitted user post data in session, exclude tips though ]
	*	[moved from actions to be available throughout]
	*
	******************************************************/
	function wppizza_sessionise_userdata($postUserData,$orderFormOptions) {
			if (!session_id()) {session_start();}
			$params = array();
			parse_str($postUserData, $params);

			/**selects are zero indexed*/
			foreach($orderFormOptions as $elmKey=>$elm){
				if($elm['type']=='select' && isset($params[$elm['key']])){
					foreach($elm['value'] as $a=>$b){
						if($params[$elm['key']]==$b){
							$params[$elm['key']]=''.stripslashes($a).'';
						}
					}
				}
				if($elm['type']!='select' && isset($params[$elm['key']])){
					$params[$elm['key']]=''.stripslashes($params[$elm['key']]).'';
				}
			}
			/******************************************
				[get entered data to re-populate input fields but loose irrelevant vars
			********************************************/
			/**empty first and start over**/
			if(isset($_SESSION[$this->pluginSessionGlobal]['userdata'])){
				unset($_SESSION[$this->pluginSessionGlobal]['userdata']);
			}
			foreach($orderFormOptions as $oForm){
				if($oForm['key']!='ctips'){/**tips should not be in the global user session**/
					if(isset($params[$oForm['key']])){
						/*make sure we entities it again for sessions to reuse in input fields or quotes etc will screw up markup*/
						$_SESSION[$this->pluginSessionGlobal]['userdata'][$oForm['key']]= wppizza_email_html_entities($params[$oForm['key']]);
					}
				}
			}
			/***eliminate notice of undefined index userdata**/
			if(!isset($_SESSION[$this->pluginSessionGlobal]['userdata'])){$_SESSION[$this->pluginSessionGlobal]['userdata']=array();}


			/*also keep selected gateway in session*/
			if(isset($_SESSION[$this->pluginSessionGlobal]['userdata']['gateway'])){
				/**store previously selected in case we need to fall back to it**/
				//$prevGwFallback=$_SESSION[$this->pluginSessionGlobal]['userdata']['gateway'];
				/*unset session*/
				unset($_SESSION[$this->pluginSessionGlobal]['userdata']['gateway']);
			}
			$selectedGateway=!empty($params['wppizza-gateway']) ? strtoupper(wppizza_validate_string($params['wppizza-gateway'])) : '';

			/*reset session if not empty*/
			if($selectedGateway!=''){
				$_SESSION[$this->pluginSessionGlobal]['userdata']['gateway']=$selectedGateway;
			}


			/**allow filtering of session data**/
			$_SESSION[$this->pluginSessionGlobal]['userdata'] = apply_filters('wppizza_filter_sessionise_userdata', $_SESSION[$this->pluginSessionGlobal]['userdata'],$params);

		return $params;
	}
	/*********************************************************************************
	*
	*	[WPML : frontend strings and order page]
	*
	********************************************************************************/
	function wppizza_wpml_localization_frontend(){
		require(WPPIZZA_PATH .'inc/wpml.inc.php');
	}
	function wppizza_wpml_localization_gateways(){
		require(WPPIZZA_PATH .'inc/wpml.gateways.inc.php');
	}
	/*************************************************************************
    *
    *	[GENERAL HELPERS : methods we want to be able to use everywhere]
    *
	************************************************************************/
	/***********************************************************
		admin pagination
	***********************************************************/
	function wppizza_admin_pagination($current,$total,$getParam){
		$args = array(
			'base'         => '%_%',
			'format'       => '?'.$getParam.'=%#%',
			'total'        => $total,
			'current' 	   => $current,
			'show_all'     => False,
			'end_size'     => 1,
			'mid_size'     => 2,
			'prev_next'    => True,
			'prev_text'    => __('&#171; Previous'),
			'next_text'    => __('Next &#187;'),
			'type'         => 'plain',
			'add_args'     => False,
			'add_fragment' => '',
			'before_page_number' => '',
			'after_page_number' => ''
		);
		$pagination=paginate_links($args);
			return $pagination;
	}
	/*******************************************************
		[empty cart session]
	******************************************************/
	function wppizza_unset_cart() {
	 	if (!session_id()) {session_start();}

	 	$disableclear=false;
	 	if(defined('WPPIZZA_DISABLE_CLEAR_CART')){
			$disableclear=true;
	 	}
		if($disableclear){return;}

	    /*holds items in cart*/
	    $_SESSION[$this->pluginSession]['items']=array();
	    /*gross sum of all items in cart,before discounts etc*/
	    $_SESSION[$this->pluginSession]['total_price_items']=0;
	    /**gratuities**/
	    if(isset($_SESSION[$this->pluginSession]['tips'])){
	    	unset($_SESSION[$this->pluginSession]['tips']);
	    }
	}
	/*************************************************************************
     *
     *	[EXTEND : class must start with WPPIZZA_EXTEND_]
     *
     ************************************************************************/
	function wppizza_extend() {
		$allClasses=get_declared_classes();
		$wppizzaExtend=array();
		foreach ($allClasses AS $oe=>$class){
			$chkStr=substr($class,0,15);
			if($chkStr=='WPPIZZA_EXTEND_'){
				$wppizzaExtend[$oe]=new $class;
				foreach($wppizzaExtend[$oe] as $k=>$v){
					$this->$k=$v;
				}
			}
		}
	}
}
/*=======================================================================================*/
/*=========================load actions and required classes===============================*/
/*=======================================================================================*/
add_action('plugins_loaded', 'wppizza_all_actions');
function wppizza_all_actions() {
	require_once(WPPIZZA_PATH .'classes/wppizza.actions.inc.php');
	$WPPIZZA_ACTIONS=new WPPIZZA_ACTIONS();
}
add_action('plugins_loaded', 'wppizza_get_gateways');
function wppizza_get_gateways() {
	require_once(WPPIZZA_PATH .'classes/wppizza.gateways.inc.php');
	$WPPIZZA_GATEWAYS=new WPPIZZA_GATEWAYS();
}
add_action('plugins_loaded', 'wppizza_add_sku');
function wppizza_add_sku() {
	$wppizza_options = get_option(WPPIZZA_SLUG,0);
	require_once(WPPIZZA_PATH .'classes/class.wppizza.sku.php');
}
/*=======================================================================================*/
/*=======================================================================================*/
/*=======================================================================================*/
}
?>