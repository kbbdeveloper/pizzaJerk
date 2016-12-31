<?php
/**
* WPPIZZA_SKU Class
*
* @package     WPPIZZA
* @subpackage  Classes/SKU
* @copyright   Copyright (c) 2015, Oliver Bach
* @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
* @since       2.16
*
* This class handles adding sku's to wppizza menu items, sizes, emails, order printing, order/confirmation/thank-you pages etc
*
*/
if ( ! defined( 'ABSPATH' ) ) exit;/*Exit if accessed directly*/

class WPPIZZA_SKU{


	/*wppizza set options*/
	public $wppizza_options;

	function __construct($wppizza_options) {

		/*wppizza set options*/
		$this->wppizza_options = $wppizza_options;

		//add_action( 'init', array( $this, 'sku_add_filters' ) );
		$sku_add_filters_admin=$this->sku_add_filters_admin();
		$sku_add_filters=$this->sku_add_filters();

	}



/************************************************************************************************************************************************
*
*
*
*	[SKU's] add filters as and where required
*
*
*
*************************************************************************************************************************************************/
	/**admin**/
	function sku_add_filters_admin() {

		/****************************************************
		*	filter sku: add to and validate default options
		*****************************************************/
		add_filter('wppizza_filter_setup_default_options', array( $this, 'wppizza_filter_setup_default_options_sku'));
		add_filter('wppizza_filter_options_validate', array( $this, 'wppizza_filter_options_validate_sku'), 10,2);
		add_filter('wppizza_filter_update_options', array( $this, 'wppizza_filter_update_options_sku'), 10,2);
		add_filter('wppizza_filter_settings_fields', array( $this, 'wppizza_filter_settings_fields_sku'));
		add_action('wppizza_action_echo_settings_field', array( $this, 'wppizza_action_echo_settings_field_sku'), 10,2);

		/*disable metabox inputs if sku_enable not enabled*/
		if(!empty($this->wppizza_options['plugin_data']['sku_enable'])){
						
			/****************************************************
			*	filter sku: add admin ajax
			*****************************************************/
			add_filter('wppizza_ajax_action_admin_sizeschanged', array( $this, 'wppizza_ajax_action_admin_sizeschanged_sku'), 10, 3);
			/****************************************************
			*	filter sku: add admin meta boxes
			*****************************************************/
			add_filter('wppizza_filter_admin_metaboxes',array( $this, 'wppizza_filter_admin_metaboxes_sku'), 10, 3);
			/****************************************************
			*	filter sku: save admin meta boxes values
			*****************************************************/
			add_filter('wppizza_filter_admin_save_metaboxes',array( $this, 'wppizza_filter_admin_save_metaboxes_sku'), 10, 2);
		}

	}
	/**frontend**/
	function sku_add_filters() {
		/*disable all frontend filters if sku's are not enabled to start off with*/
		if(!empty($this->wppizza_options['plugin_data']['sku_enable'])){
			/****************************************************
			*	filter sku: loop post title and price/sizes labels
			*****************************************************/
			add_filter('wppizza_filter_loop_title', array( $this, 'wppizza_filter_loop_title_sku'),10,2);/**add sku's to titles in loop*/
			add_filter('wppizza_filter_loop_meta', array( $this, 'wppizza_filter_loop_meta_sku'),10,2);/**add sku's to sizes in loop*/
			/****************************************************
			*	filter sku: add to session and return in summary items
			*	conditionally set in functions
			*****************************************************/
			add_filter('wppizza_filter_order_summary_session', array( $this, 'wppizza_filter_order_summary_session_sku'),10,2);/*add sku of item to session data when adding item to cart in wppizza_order_summary() */
			add_filter('wppizza_filter_order_summary_cart_items', array( $this, 'wppizza_filter_order_summary_items_sku'),10,3);/*add sku's to each looped item in ech group of wppizza_order_summary() */
			add_filter('wppizza_filter_order_summary_grouped_items', array( $this, 'wppizza_filter_order_summary_grouped_items_sku'),10,3);/*add sku's to each item in wppizza_order_summary() */
			add_filter('wppizza_filter_order_summary_items', array( $this, 'wppizza_filter_order_summary_items_sku'),10,3);/*add sku's to each GROUPED item in wppizza_order_summary() */
			add_filter('wppizza_filter_summary', array( $this, 'wppizza_filter_summary_sku'),10,2);/*set sku frontend output in wppizza_order_summary() */

			/****************************************************
			*	filter sku: confirmation page
			*****************************************************/
			add_filter('wppizza_filter_confirmation_item_header_markup',array( $this, 'wppizza_filter_template_header_sku'),10,3);/**add sku label in confirmation page*/
			add_filter('wppizza_filter_confirmation_item_markup',array( $this, 'wppizza_filter_template_row_sku'),10,4);/**add sku in row in confirmation page*/
			add_filter('wppizza_filter_confirmation_item_variables',array( $this, 'wppizza_filter_template_item_sku'),10,3);/**add sku to title (or replace size name with sku) in confirmation page*/

			/****************************************************
			*	filter sku: add sku to and retrieve from order_ini
			*****************************************************/
			add_filter('wppizza_filter_order_ini_items', array( $this, 'wppizza_filter_order_summary_items_sku'),10,3);/*add sku's to order ini to be able to get them again later*/
			add_filter('wppizza_filter_order_item_details', array( $this, 'wppizza_filter_order_summary_items_sku'),10,3); /**filter output of items items when pulling them out of the order_ini /  db in WPPIZZA_ORDER_DETAILS class*/

			/****************************************************
			*	filter sku: add sku to html email wppizza-order-email-html.php template
			*****************************************************/
			add_filter('wppizza_filter_htmlemail_item_markup',array( $this, 'wppizza_filter_append_sku'),10,5);
			add_filter('wppizza_filter_plaintextemail_item_markup',array( $this, 'wppizza_filter_append_plaintext_sku'),10,5);

			/****************************************************
			*	filter sku: add sku to html email/print (drag/drop) templates
			*****************************************************/
			add_filter('wppizza_filter_template_item_header_markup',array( $this, 'wppizza_filter_template_header_sku'),10,4);/**add sku label in drag/drop template*/
			add_filter('wppizza_filter_template_item_markup',array( $this, 'wppizza_filter_template_row_sku'),10,5);/**add sku in row in  drag/drop template*/
			add_filter('wppizza_filter_template_item_variables',array( $this, 'wppizza_filter_template_item_sku'),10,4);/**add sku to title (or replace size name with sku) in (drag/drop) templates */

			add_filter('wppizza_filter_template_item_header_plaintext_markup',array( $this, 'wppizza_filter_template_header_sku'),10,5);/**add sku label in (drag/drop) templates type:plaintext*/
			add_filter('wppizza_filter_template_item_plaintext_markup',array( $this, 'wppizza_filter_template_row_sku'),10,6);/**add sku in row in (drag/drop) templates templates type:plaintext*/
			add_filter('wppizza_filter_template_item_plaintext_variables',array( $this, 'wppizza_filter_template_item_sku'),10,4);/**add sku to title (or replace size name with sku) in (drag/drop) templates  templates type:plaintext*/

			/****************************************************
			*	filter sku: show_order (thank you) page
			*****************************************************/
			add_filter('wppizza_filter_show_order_item_markup',array( $this, 'wppizza_filter_append_sku'),10,5);/**add sku label in thank you page*/

			/****************************************************
			*	filter sku: admin print - standard template
			*****************************************************/
			add_filter('wppizza_filter_print_order_item_header',array( $this, 'wppizza_filter_template_header_sku'),10,3);/**add sku label in confirmation page*/
			add_filter('wppizza_filter_print_order_item_markup',array( $this, 'wppizza_filter_template_row_sku'),10,4);/**add sku in row in confirmation page*/
			add_filter('wppizza_filter_print_item_variables',array( $this, 'wppizza_filter_template_item_sku'),10,3);/**add sku to title (or replace size name with sku) in confirmation page*/

			/****************************************************
			*	filter sku: order history
			*****************************************************/
			add_filter('wppizza_filter_orderhistory_item_markup',array( $this, 'wppizza_filter_append_sku'),10,5);/**add sku label in order history*/


			/****************************************************
			*	filter sku: search widget. search for sku's too
			*****************************************************/
			add_filter('wppizza_filter_search',array( $this, 'wppizza_filter_search_sku'));/*search for sku's too in meta data*/
		}
	}

/***********************************************************************************************************************************************************************************
*
*
*
*	[ADMIN FILTERS/ACTIONS]
*
*
*
***********************************************************************************************************************************************************************************/
	/**
		add to default options
	**/
	function wppizza_filter_setup_default_options_sku($defaultOptions){
		/**add sku options -> global**/
		$defaultOptions['plugin_data']['sku_enable']=false;
		$defaultOptions['plugin_data']['sku_search']=false;
		$defaultOptions['plugin_data']['sku_search_partial']=false;

		/**add sku options -> layout**/
		$defaultOptions['layout']['sku_display']=array(
			'menu_listing_title' => 0,
			'menu_listing_size' => 0,
			'cart' => 0,
			'orderpage' => 0,
			'confirmation' => 0,
			'emails' => 0,
			'emails_template' => 0,
			'show_order' => 0,
			'orderhistory' => 0,
			'print_template' => 0
		);
		$defaultOptions['layout']['sku_replaces_size']=false;

		/**add sku options -> localization**/
		$defaultOptions['localization']['header_order_print_itemised_sku']=array(
			'descr'=>__('Print Order Admin - [itemised header]: article number [SKU] - if enabled', 'wppizza-locale'),
			'lbl'=>__('SKU', 'wppizza-locale')
		);
		$defaultOptions['localization']['templates_label_order_sku']=array(
			'descr'=>__('Template Label : "Order" - SKU Column (e.g Article No / SKU) - if enabled. Set column location in layout', 'wppizza-locale'),
			'lbl'=>__('SKU', 'wppizza-locale')
		);

		return $defaultOptions;
	}

	/**
		validate set options
	**/
	function wppizza_filter_options_validate_sku($options, $input){

		/*global vars**/
		if(isset($_POST[''.WPPIZZA_SLUG.'_global'])){
			$options['plugin_data']['sku_enable'] = !empty($input['plugin_data']['sku_enable']) ? true : false;
			$options['plugin_data']['sku_search'] = !empty($input['plugin_data']['sku_search']) ? true : false;
			$options['plugin_data']['sku_search_partial'] = !empty($input['plugin_data']['sku_search_partial']) ? true : false;
		}

		/*layout vars*/
		if(isset($_POST[''.WPPIZZA_SLUG.'_layout'])){
			/**sku positioning / module display**/
			$sku_display=array();
			if(!empty($input['layout']['sku_display'])){
				foreach($input['layout']['sku_display'] as $key=>$value){
					if($key=='confirmation' || $key=='emails_template' || $key=='print_template'){
						$sku_display[$key]=wppizza_validate_int_only($value);
					}else{
						$sku_display[$key]=wppizza_validate_string($value);
					}
				}
			}
			$options['layout']['sku_display'] = $sku_display;
			$options['layout']['sku_replaces_size'] = !empty($input['layout']['sku_replaces_size']) ? true : false;

		}
	return $options;
	}


	/**
		on plugin update, make sure we keep layout options
		(not sure if running this filter is strictly necessary,
		but it won't hurt)
	**/
	function wppizza_filter_update_options_sku($update_options, $options){
		if(isset($options['layout']['sku_display'])){
			$update_options['layout']['sku_display']=$options['layout']['sku_display'];
		}
		return $update_options;
	}

	/**
		add admin settings fields
	**/
	function wppizza_filter_settings_fields_sku($settings_fields){

		$fields=array();
		foreach($settings_fields as $settings_section_key=>$settings_field_section){
			foreach($settings_field_section as $settings_field_key=>$settings_field_array){

				/*currently existing*/
				$fields[$settings_section_key][$settings_field_key]=$settings_field_array;


				/**add after admin_order_history_polling_auto in global_miscellaneous section**/
				if($settings_section_key=='global_miscellaneous' && $settings_field_key=='admin_order_history_polling_auto'){
					$fields[$settings_section_key]['sku_enable'] = array('sku_enable', '<b>'.__('Enable setting of SKU\'s:', 'wppizza-locale').'</b>', $settings_section_key, $settings_section_key, 'sku_enable' );
					$fields[$settings_section_key]['sku_search'] = array('sku_search', '<b>'.__('Enable search by SKU:', 'wppizza-locale').'</b>', $settings_section_key, $settings_section_key, 'sku_search' );
					$fields[$settings_section_key]['sku_search_partial'] = array('sku_search_partial', '<b>'.__('Enable partial SKU search:', 'wppizza-locale').'</b>', $settings_section_key, $settings_section_key, 'sku_search_partial' );
				}

				/**add after opening_times_format in layout section**/
				if($settings_section_key=='layout' && $settings_field_key=='opening_times_format'){
					if( !empty($this->wppizza_options['plugin_data']['sku_enable'])){
						$fields[$settings_section_key]['sku_display'] = array('sku_display', '<b>'.__('SKU display:', 'wppizza-locale').'</b>', $settings_section_key, $settings_section_key, 'sku_display' );
					}
				}
			}
		}
		return $fields;
	}
	/**
		echo admin settings fields
	**/
	function wppizza_action_echo_settings_field_sku($field, $options){

		if($field=='sku_enable'){
			echo "<input id='".$field."' name='".WPPIZZA_SLUG."[plugin_data][".$field."]' type='checkbox'  ". checked($options['plugin_data'][$field],true,false)." value='1' />";
			echo" <span class='description'>".__('check to be able to set SKU\'s for menu items [more options will be become available in settings->layout]', 'wppizza-locale')."</span>";
		}

		if($field=='sku_search'){
			echo "<input id='".$field."' name='".WPPIZZA_SLUG."[plugin_data][".$field."]' type='checkbox'  ". checked($options['plugin_data'][$field],true,false)." value='1' />";
			echo" <span class='description'>".__('make SKU\'s searchable (through wppizza search widget/shortcode. menu item search must be enabled)', 'wppizza-locale')."</span>";
		}
		if($field=='sku_search_partial'){
			echo "<input id='".$field."' name='".WPPIZZA_SLUG."[plugin_data][".$field."]' type='checkbox'  ". checked($options['plugin_data'][$field],true,false)." value='1' />";
			echo" <span class='description'>".__('allow search to match partial SKU\'s (requires 5 or more characters in search)', 'wppizza-locale')."</span>";
		}


		if($field=='sku_display'){

			$display_locations=array(
				'menu_listing_title'=>array(
					'lbl'=>__('menu listings title','wppizza-locale'),
					'add_info'=>'',
					'type'=>'radio',
					'vals'=>array(
						'left'=>__('left','wppizza-locale'),
						'right'=>__('right','wppizza-locale')
					)
				),

				'menu_listing_size'=>array(
					'lbl'=>__('menu listings sizes','wppizza-locale'),
					'add_info'=>'',
					'type'=>'radio',
					'vals'=>array(
						'left'=>__('left','wppizza-locale'),
						'right'=>__('right','wppizza-locale')
					)
				),

				'cart'=>array(
					'lbl'=>__('cart','wppizza-locale'),
					'add_info'=>'',
					'type'=>'radio',
					'vals'=>array(
						'left'=>__('left','wppizza-locale'),
						'right'=>__('right','wppizza-locale')
					)
				),

				'orderpage'=>array(
					'lbl'=>__('order page','wppizza-locale'),
					'add_info'=>'',
					'type'=>'radio',
					'vals'=>array(
						'left'=>__('left','wppizza-locale'),
						'right'=>__('right','wppizza-locale')
					)
				),


				'confirmation'=>array(
					'lbl'=>__('confirmation page [if used]','wppizza-locale'),
					'add_info'=>__('column number [0= disabled, 1=first, 2=second etc]','wppizza-locale'),
					'type'=>'input',
					'vals'=>array(
						'column'=>array('label'=>__('column','wppizza-locale'),'value'=>empty($options['layout'][$field]['confirmation']) ? 0 : $options['layout'][$field]['confirmation']),
					)
				),

				'emails'=>array(
					'lbl'=>__('emails [standard]','wppizza-locale'),
					'add_info'=>'',
					'type'=>'radio',
					'vals'=>array(
						'left'=>__('left','wppizza-locale'),
						'right'=>__('right','wppizza-locale')
					)
				),

				'emails_template'=>array(
					'lbl'=>__('emails [drag/drop templates]','wppizza-locale'),
					'add_info'=>__('column number [0= disabled, 1=first, 2=second etc]','wppizza-locale'),
					'type'=>'input',
					'vals'=>array(
						'column'=>array('label'=>__('column','wppizza-locale'),'value'=>empty($options['layout'][$field]['emails_template']) ? 0 : $options['layout'][$field]['emails_template']),
					)
				),

				'show_order'=>array(
					'lbl'=>__('thank you page [if enabled in wppizza->gateways]','wppizza-locale'),
					'add_info'=>'',
					'type'=>'radio',
					'vals'=>array(
						'left'=>__('left','wppizza-locale'),
						'right'=>__('right','wppizza-locale')
					)
				),

				'orderhistory'=>array(
					'lbl'=>__('order history','wppizza-locale'),
					'add_info'=>'',
					'type'=>'radio',
					'vals'=>array(
						'left'=>__('left','wppizza-locale'),
						'right'=>__('right','wppizza-locale')
					)
				),
				'print_template'=>array(
					'lbl'=>__('admin print order','wppizza-locale'),
					'add_info'=>__('column number [0= disabled, 1=first, 2=second etc]','wppizza-locale'),
					'type'=>'input',
					'vals'=>array(
						'column'=>array('label'=>__('column','wppizza-locale'),'value'=>empty($options['layout'][$field]['print_template']) ? 0 : $options['layout'][$field]['print_template']),
					)
				),

			);

			echo "<table class='form-table wppizza_sku_display'>";
			echo "<thead><tr><th colspan='5'>";
			echo"".__('Where do you want to display the menu items SKU\'s [if set].', 'wppizza-locale')." ";
			echo"".__('If SKU of individual size is not set, main item sku will be used if available and set.', 'wppizza-locale')." ";
			echo "</th></tr></thead>";

			echo "<tbody>";

			echo "<tr>";
				echo "<td><b>".__('Replace size labels with SKU (if exist)','wppizza-locale')."</b>: </td>";
				echo "<td><input name='".WPPIZZA_SLUG."[layout][sku_replaces_size]' type='checkbox'  ". checked(!empty($options['layout']['sku_replaces_size']),true,false)." value='1' /></td>";
				echo "<td colspan='3'>".__('Except for menu listing title, enabling this option will render below left/right display positions irrelevant ()', 'wppizza-locale')."</td>";
			echo "</tr>";
			foreach($display_locations as $key=>$values){
				echo "<tr>";

					echo "<td>".$values['lbl'].": </td>";
					if($values['type']=='radio'){
						echo "<td>".__('off','wppizza-locale')." <input name='".WPPIZZA_SLUG."[layout][".$field."][".$key."]' type='radio'  ". checked(empty($options['layout'][$field][$key]),true,false)." value='0' /></td>";
					}else{
						//echo "<td>".__('off','wppizza-locale')." <input name='".WPPIZZA_SLUG."[layout][".$field."][".$key."][-1]' type='checkbox'  ". checked(empty($options['layout'][$field][$key]),true,false)." value='1' /></td>";
						//echo "<td></td>";
					}

					foreach($values['vals'] as $valKey=>$val){
						echo "<td>";
						if($values['type']=='radio'){
							echo" ".$val." <input name='".WPPIZZA_SLUG."[layout][".$field."][".$key."]' type='".$values['type']."'  ". checked($options['layout'][$field][$key],$valKey,false)." value='".$valKey."' />";
						}
						if($values['type']=='input'){
							echo" ".$val['label']." <input name='".WPPIZZA_SLUG."[layout][".$field."][".$key."]' size='3' type='".$values['type']."' value='".$val['value']."' />";
						}

						echo "</td>";
					}

					//if(!empty($values['add_info'])){
						$colspan=$values['type']=='radio' ? 1 : 2;
						echo "<td colspan='".$colspan."'><span class='description'>".$values['add_info']."</span></td>";
					//}
				//echo " ".__('left','wppizza-locale')." <input name='".WPPIZZA_SLUG."[layout][".$field."][".$key."]' type='radio'  ". checked($options['layout'][$field][$key],'left',false)." value='left' />";
				//echo " ".__('right','wppizza-locale')." <input name='".WPPIZZA_SLUG."[layout][".$field."][".$key."]' type='radio'  ". checked($options['layout'][$field][$key],'right',false)." value='right' />";
				echo "</tr>";
			}
			echo "</tbody></table>";
		}
	}


	/**
		add meta boxes to edit post pages
	**/
	function wppizza_filter_admin_metaboxes_sku($metaboxes, $meta_values, $optionsSizes){
		/****  SKU's - if enabled***/
		if(!empty($this->wppizza_options['plugin_data']['sku_enable'])){
			$meta_box['sku']='';
			$meta_box['sku'].="<div class='".WPPIZZA_SLUG."_option'>";
			$meta_box['sku'].="<div class='wppizza-meta-label'>".__('SKU\'s', 'wppizza-locale').":</div> ";

			$meta_box['sku'].="<span class='wppizza_sku'>";
				$val=!empty($meta_values['sku'][-1]) ? $meta_values['sku'][-1] : '';
				$meta_box['sku'].="".__('Menu Item','wppizza-locale').": <input name='".WPPIZZA_SLUG."[sku][-1]' size='10' type='text' value='".$val."' />";
				$meta_box['sku'].="<span class='wppizza_sku_sizes'>";
				foreach($meta_values['prices'] as $k=>$v){
					$ident=$this->wppizza_options['sizes'][$meta_values['sizes']][$k]['lbl'] ;
					$val=!empty($meta_values['sku'][$k]) ? $meta_values['sku'][$k] : '';
					$meta_box['sku'].=" ".$ident.": <input name='".WPPIZZA_SLUG."[sku][".$k."]' size='10' type='text' value='".$val."' />";
				}
			$meta_box['sku'].="</span>";
			$meta_box['sku'].="</span>";

			$meta_box['sku'].="</div>";

			/**append after prices**/
			$metaboxes['prices'].=$meta_box['sku'];

		}

		return $metaboxes;
	}
	/**
		save meta boxes sku values, adding to main wppizza meta key to simplify
		display in various places.
		also save as individual meta keys (lowercased) to enable meta search
	**/
	function wppizza_filter_admin_save_metaboxes_sku($itemMeta, $item_id){
		if(!empty($this->wppizza_options['plugin_data']['sku_enable'])){

			//**sku's**//
			$itemMeta['sku']=array();
		    if(isset($_POST[WPPIZZA_SLUG]['sku'])){
		    	/*as we might have different number of sizes, delete all old single sku meta keys for this item*/
		    	delete_post_meta($item_id, WPPIZZA_SLUG.'_sku');

		    	/**insert/add/edit current**/
		    	foreach($_POST[WPPIZZA_SLUG]['sku'] as $k=>$v){
		    		/**add to main wppizza meta data as serialized array*/
			    	$itemMeta['sku'][$k] = wppizza_validate_string($_POST[WPPIZZA_SLUG]['sku'][$k]);

			    	/**add individual sku keys if not empty. set to lowercase to make case insesitive searches*/
			    	if(!empty($itemMeta['sku'][$k])){
			    		$sku_val=($itemMeta['sku'][$k]);/*searches - according to Otto - are case insensitive. so no need to save as lowercase for example*/
			    		add_post_meta($item_id, WPPIZZA_SLUG.'_sku', $sku_val);
			    	}
		    	}

		    }
		}
		return $itemMeta;
	}
	/**
		ajax change sku metaboxes on price tier (sizes) change
	**/
	function wppizza_ajax_action_admin_sizeschanged_sku($obj, $set_size_id, $is_metabox){
		
		/*only if sku enabled and only on post/metaboxes page*/
		if(!empty($this->wppizza_options['plugin_data']['sku_enable']) && $is_metabox){
			$sku='';
			if(is_array($this->wppizza_options['sizes'][$set_size_id])){
				foreach($this->wppizza_options['sizes'][$set_size_id] as $a=>$b){
					$ident=$b['lbl'];
					$sku.=" ".$ident.": <input name='".WPPIZZA_SLUG."[sku][".$a."]' size='10' type='text' value='' />";
			}}
			$obj['inp']['sku']=$sku;
			$obj['element']['sku']='.wppizza_sku_sizes';/**html element empty and replace with new input boxes**/
		}
	return $obj;
	}

/***********************************************************************************************************************************************************************************
*
*
*
*	[FRONTEND FILTERS/ACTIONS]
*
*
*
***********************************************************************************************************************************************************************************/

	/***************************************************************************************************************************
	*
	*
	*	[SKU's] allow searching for sku's
	*
	*
	***************************************************************************************************************************/
	function wppizza_filter_search_sku($query){

		if(!empty($this->wppizza_options['plugin_data']['sku_enable']) && !empty($this->wppizza_options['plugin_data']['sku_search']) ){
			/**what did we search for*/
			$queryVar=($query->query_vars['s']);/*searches - according to Otto - are case insensitive anyway. no need to cast things to lowercase*/
			$queryLength=strlen($queryVar);

			/**search meta data for sku's*/
			$args = array(
			 'post_type'=>WPPIZZA_POST_TYPE,
			 'meta_query' => array(
					array (
						'key' => WPPIZZA_SLUG.'_sku',
						'value' => $queryVar,
						'compare' => '='
					)
				)
			);

			/**allow partial sku search, using integer of constant to define minimum required string length. minimum 3*/
			if(!empty($this->wppizza_options['plugin_data']['sku_search_partial'])){
				/**minimum of 5 chars to do a partial search unless overridden by constant**/
				$minQueryLength=5;
				if(defined(WPPIZZA_SKU_PARTIAL_SEARCH_LENGTH) && (int)WPPIZZA_SKU_PARTIAL_SEARCH_LENGTH>=1){
					$minQueryLength=(int)WPPIZZA_SKU_PARTIAL_SEARCH_LENGTH;
				}
				/**change comparisaon to LIKE if partial enabled and q str length>=$minQueryLength*/
				if($queryLength >= $minQueryLength){
					$args['meta_query'][0]['compare']='LIKE';
				}
			}


			$sku_query = new WP_Query( $args );
			/**
				if we have found posts with this sku, replace results to only show results with that sku
				by replacing s query and including  post__in instead
			**/
			if($sku_query->post_count>0){
				$sku_post_in=array();
				foreach($sku_query->posts as $key=>$post){
					$sku_post_in[$post->ID]=$post->ID;
				}
				$query->set('s','');//set search query to '' as it would not find meta seraches*/
				$query->set('post__in',$sku_post_in);

				/**
					as the search string (get_query_var( 's' )) "search for [x]" and searchbox prefill would normally be empty
					as we've unset it, use get_search_query filter to set to $_GET[s]
				**/
				add_filter('get_search_query',array($this,'wppizza_filter_set_search_query_sku'));
			}
		}

		return $query;
	}
	/********************************************************************
	*	set found SKU as search_query
	********************************************************************/
	function wppizza_filter_set_search_query_sku($query_var){

		$query_var=esc_html($_GET['s']);

		return $query_var;
	}

	/***************************************************************************************************************************
	*
	*
	*	[SKU's] apply filters adding sku's for emails, posts, pages, template etc as needed
	*
	*
	***************************************************************************************************************************/
	/***********************************************************************************************
	*	[add sku to session data for each item added to cart -
	*	only runs when item initially adding to cart . i.e when $module=='cartajax']
	*	ignored if sku_enable is off
	***********************************************************************************************/
	function wppizza_filter_order_summary_session_sku($session, $module){

		/*******add SKU's to session on cart ajax****/
		if (!empty($this->wppizza_options['plugin_data']['sku_enable']) && $module=='cartajax'){
			/**loop through items in cart and add SKU if there isnt one set yet**/
			foreach($session['items'] as $groupId=>$items){
				foreach($items as $itemKey=>$item){
					/*only add sku for newly added items to cart (as it will not have been added to the session yet)**/
					if(!isset($item['sku'])){
						/* get SKU meta data*/
						$post_meta = get_post_meta($item['id'], WPPIZZA_SLUG, true);

						/*check if we have size sku*/
						$size_sku=!empty($post_meta['sku'][$item['size']]) ? $post_meta['sku'][$item['size']] : false;

						/* if there's no size sku , get the main sku**/
						if(!$size_sku){
							$size_sku=!empty($post_meta['sku'][-1]) ? $post_meta['sku'][-1] : '';
						}

						/**add to session data as well to $session to return immediately*/
						$_SESSION[WPPIZZA_SESSION_IDENT]['items'][$groupId][$itemKey]['sku']=$size_sku;
						$session['items'][$groupId][$itemKey]['sku']=$size_sku;
					}
				}
			}
		}
		return $session;
	}
	/***********************************************************************************************
	*	[add sku to each item - runs 7x (not necessarily on the same page though))
	*	once before and once after grouping when adding to cart (ajax or non ajax)
	*	once before and once after grouping (getting the summary *before* adding to order_ini)
	*	once before and once after grouping on order page
	*	and once just before adding to order_ini]
	***********************************************************************************************/
	function wppizza_filter_order_summary_items_sku($cart_item, $item, $module){

		if(!empty($this->wppizza_options['plugin_data']['sku_enable'])){
		if($module=='cart' || $module=='cartajax' || $module=='orderpage' || $module=='confirmationpage' || $module=='order_session' || $module=='order_ini' || $module=='order_item_details'){
			if(!empty($item['sku'])){
				$cart_item['sku']=$item['sku'];
			}
		}}
		return $cart_item;
	}
	/***********************************************************************************************
	*	[add sku to grouped item - items = array of items in group (they will all be the same here)]
	***********************************************************************************************/
	function wppizza_filter_order_summary_grouped_items_sku($grouped_item, $items, $module){
		if(!empty($this->wppizza_options['plugin_data']['sku_enable'])){
		if($module=='cart' || $module=='cartajax' || $module=='orderpage' || $module=='confirmationpage' || $module=='order_session'){
			if(!empty($items[0]['sku'])){
				$grouped_item['sku']=$items[0]['sku'];
			}
		}}
		return $grouped_item;
	}

	/***********************************************************************************************
	*	[add sku to cart name/size output - bypassed when adding to order_ini]
	* 	[$id = template id passed from emaails, print - not used at the moment though ]
	* 	@ html => do we want to wrap th esku's in <spans> ?
	***********************************************************************************************/
	function wppizza_filter_summary_sku($summary, $module, $html=true){

		/**first of all, check if sku is enabled**/
		if(!empty($this->wppizza_options['plugin_data']['sku_enable'])){
			/**check we are doing frontend things (including frontend ajax)**/
			if(!is_admin() || ( is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX && isset($_POST['action']) && $_POST['action']=='wppizza_json')){
				$sku_show=false;
				$sku_inline_style_title=false;
				$sku_inline_style_size=false;

				/*depending on module, do add sku to title*/
				if(($module=='cart' || $module=='cartajax' )){
					if(!empty($this->wppizza_options['layout']['sku_display']['cart'])){
						$sku_show=true;
						$sku_position=$this->wppizza_options['layout']['sku_display']['cart'];
					}
				}
				if($module=='orderpage'){
					if(!empty($this->wppizza_options['layout']['sku_display']['orderpage'])){
						$sku_show=true;
						$sku_position=$this->wppizza_options['layout']['sku_display']['orderpage'];
					}
				}

				/**
					now lets add the sku to the title
					and / or size
				**/
				if($sku_show){
					/**loop through items in cart**/
					foreach($summary['items'] as $key=>$item){/*title*/
						//$summary['items'][$key]=$this->wppizza_show_sku($summary['items'][$key], $sku_position, $module, $html, $sku_inline_style_title);
						$summary['items'][$key]=$this->wppizza_filter_append_sku($summary['items'][$key], $summary['items'][$key], $key, $this->wppizza_options['order'] ,$module);
					}
				}
			}
		}
		return $summary;
	}

	/***************************************************************
	*
	*	add sku *header* to
	*	confirmation page
	*	email (drag/drop) templates (using inline style)
	*	admin print (drag/drop) templates
	*
	****************************************************************/
	function wppizza_filter_template_header_sku($markup, $txt ,$module ,$tpl_id=false, $plaintext=false){


		if(!empty($this->wppizza_options['plugin_data']['sku_enable']) && !empty($this->wppizza_options['layout']['sku_display'][$module])){

			$set_columns=array();/*new array*/
			$set_order=$this->wppizza_options['layout']['sku_display'][$module];
			$max=count($markup);

			/**if not plaintext add html tags and styles/classes */
			if(empty($plaintext)){
				/*emails should have inline styles applied as set in template id styles*/
				if($module=='emails_template'){

					/**get inline style as set in template editor concat td left and th settings*/
					$email_inline_style=$this->wppizza_options['templates']['emails'][$tpl_id]['style']['order']['td-lft'];
					$email_inline_style.=';'.$this->wppizza_options['templates']['emails'][$tpl_id]['style']['order']['th'];
					/**if we want to filter this*/
					//$email_inline_style=apply_filters('wppizza_filter_sku_htmlemail_inline_style', $email_inline_style, $module);
					$style='style="'.$email_inline_style.'"';

				}else{
					$style='class="wppizza-'.$module.'-item-sku-th"';
					/*print template does not need classes here really */
					if($module=='print_template'){
						$style='';
					}
				}

				$sku_th='<th '.$style.'>'.$txt['templates_label_order_sku'].'</th>';
			}
			/**if  plaintext only return text */
			if(!empty($plaintext)){

				$sku_th=$txt['templates_label_order_sku'];
			}


			$sort=1;
			foreach($markup as $muKey=>$muTh){
				/*before other*/
				if($sort==$set_order){
					$set_columns['sku']=$sku_th;
				}
				/*existing*/
				$set_columns[$muKey]=$muTh;

				/*if last*/
				if($set_order>=$max && $sort==$max){
					$set_columns['sku']=$sku_th;
				}

				$sort++;
			}
			return $set_columns;

		}

		return $markup;
	}
	/***************************************************************
	*
	*	add sku column to *item* on
	*	confirmation page
	*	email (drag/drop) templates
	*	admin print (drag/drop) templates
	****************************************************************/
	function wppizza_filter_template_row_sku($markup, $item, $itemKey, $module, $tpl_id=false, $plaintext=false){
		if(!empty($this->wppizza_options['plugin_data']['sku_enable']) && !empty($this->wppizza_options['layout']['sku_display'][$module])){


			$set_columns=array();/*new array*/
			$set_order=$this->wppizza_options['layout']['sku_display'][$module];
			$max=count($markup);
			$sort=1;
			$style='class="wppizza-'.$module.'-item-sku"';/**default, add class attribute, to be rplaced by inline style if email*/
			$item_sku=!empty($item['sku']) ? $item['sku'] : '';
			foreach($markup as $muKey=>$muTh){
				/*before other*/
				if($sort==$set_order){
					/**if not plaintext add html tags and styles/classes */
					if(empty($plaintext)){
						/*emails should have inline styles applied as set in template id styles*/
						if($module=='emails_template'){
							/**get inline style as set in template editor concat td left and th settings*/
							$email_inline_style=$this->wppizza_options['templates']['emails'][$tpl_id]['style']['order']['td-lft'];
							$style='style="'.$email_inline_style.'"';
						}
						$set_columns['sku']='<td '.$style.'>'.$item_sku.'</td>';
					}

					/**if  plaintext only return sku without tags */
					if(!empty($plaintext)){
						$set_columns['sku']=$item_sku;
					}
				}
				/*existing*/
				$set_columns[$muKey]=$muTh;

				/*if last*/
				if($set_order>=$max && $sort==$max){
					/**if not plaintext add html tags and styles/classes */
					if(empty($plaintext)){
						/*emails should have inline styles applied as set in template id styles*/
						if($module=='emails_template'){
							/**get inline style as set in template editor concat td left and th settings*/
							$email_inline_style=$this->wppizza_options['templates']['emails'][$tpl_id]['style']['order']['td-rgt'];
							$style='style="'.$email_inline_style.'"';
						}
						$set_columns['sku']='<td '.$style.'>'.$item_sku.'</td>';
					}

					/**if  plaintext only return sku without tags */
					if(!empty($plaintext)){
						$set_columns['sku']=$item_sku;
					}

				}

				$sort++;
			}
			return $set_columns;

		}

		return $markup;
	}
	/*********************************************************************************
	*
	*	replace menu item size with sku on
	*	confirmation page
	*	email (drag/drop) templates
	*	admin print (drag/drop) templates
	*********************************************************************************/
	function wppizza_filter_template_item_sku($item, $itemKey, $module, $tpl_id=false){	//$orderOptions,
		/*sku replace - replace size with sku, keep item name as is */
		if(!empty($this->wppizza_options['plugin_data']['sku_enable'])  && !empty($this->wppizza_options['layout']['sku_display'][$module]) && !empty($this->wppizza_options['layout']['sku_replaces_size']) && !empty($item['sku'])){

			$item['size']=$item['sku'];
		}

		return $item;
	}

	/*********************************************************************************
	*
	*	[adding SKU's as required - html]
	*
	*********************************************************************************/
	function wppizza_filter_append_sku($markup, $item, $itemKey, $orderOptions, $module){

		if(!empty($this->wppizza_options['plugin_data']['sku_enable']) && !empty($this->wppizza_options['layout']['sku_display'][$module]) && !empty($item['sku']) ){

			/*sku replace - replace size with sku, keep item name as is */
			if(!empty($this->wppizza_options['layout']['sku_replaces_size'])){
				$markup['size']=$item['sku'];
			}
			/*not replacing item size with sku, but adding before / after titel*/
			if(empty($this->wppizza_options['layout']['sku_replaces_size'])){
				$add_sku='';
				if($module=='emails'){/*html emails should have inline styles*/

					$elm='span';/*use spans in emails*/
					$email_inline_style="padding:0 3px;font-size:100%;";
					/**if we want to filter this*/
					$email_inline_style=apply_filters('wppizza_filter_sku_htmlemail_inline_style', $email_inline_style, $module);
					$style_elm='style="'.$email_inline_style.'"';
				}else{
					$elm='div';/*use div (set to inline-block as default) to not mess up >span floats)*/
					$style_elm='class="wppizza_sku wppizza_sku_'.$module.'"';
				}
				/*sku prepend*/
				if($this->wppizza_options['layout']['sku_display'][$module]=='left'){
					$add_sku.='<'.$elm.' '.$style_elm.'>'.$item['sku'].'</'.$elm.'> ';
				}

				/*current name*/
				$add_sku.=''.$item['name'].'';

				/*sku append*/
				if($this->wppizza_options['layout']['sku_display'][$module]=='right'){
					$add_sku.=' <'.$elm.' '.$style_elm.'>'.$item['sku'].'</'.$elm.'>';
				}

				/**new name*/
				$markup['name']=$add_sku;
			}
		}
		return $markup;
	}

	/*********************************************************************************
	*
	*	[adding SKU's as required - plaintext]
	*
	*********************************************************************************/
	function wppizza_filter_append_plaintext_sku($markup, $item, $itemKey, $orderOptions, $module){

		if(!empty($this->wppizza_options['plugin_data']['sku_enable']) && !empty($this->wppizza_options['layout']['sku_display'][$module]) && !empty($item['sku']) ){

			/*sku replace - replace size with sku, keep item name as is */
			if(!empty($this->wppizza_options['layout']['sku_replaces_size'])){
				$markup['size']=$item['sku'];
			}
			/*not replacing item size with sku, but adding before / after titel*/
			if(empty($this->wppizza_options['layout']['sku_replaces_size'])){
				$add_sku='';

				/*sku prepend*/
				if($this->wppizza_options['layout']['sku_display'][$module]=='left'){
					$add_sku.=''.$item['sku'].' ';
				}

				/*current name*/
				$add_sku.=''.$item['name'].'';

				/*sku append*/
				if($this->wppizza_options['layout']['sku_display'][$module]=='right'){
					$add_sku.=' '.$item['sku'].'';
				}

				/**new name*/
				$markup['name']=$add_sku;
			}
		}
		return $markup;
	}

	/*********************************************************************************
	*	[adding SKU's to post title in loop as  required]
	*********************************************************************************/
	function wppizza_filter_loop_title_sku($title, $post_id){/**in pages*/

		/**only show sku if enabled and for non admin or ajax requests with wppizza_json action**/
		if (
				!empty($this->wppizza_options['plugin_data']['sku_enable']) &&
				!empty($this->wppizza_options['layout']['sku_display']['menu_listing_title']) &&
				!is_admin()
				// is this needed ? (!is_admin() || ( is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX && ( isset($_POST['action']) && $_POST['action']=='wppizza_json')))
			){

			/*check post type*/
			$post_type=get_post_type( $post_id );

			/**apply if post type is wppizza **/
			if($post_type==WPPIZZA_POST_TYPE){
				/* get SKUs*/
				$post_meta = get_post_meta($post_id, WPPIZZA_SLUG, true);
				/*check if we have a main title sku*/
				$sku_menu_title=!empty($post_meta['sku'][-1]) ? $post_meta['sku'][-1] : false;
				/* if there's no main , get the frst size if we can**/
				if(!$sku_menu_title){
					$sku_menu_title=!empty($post_meta['sku'][0]) ? $post_meta['sku'][0] : false;
				}

				/**apply if SKU !=''**/
				if(!empty($sku_menu_title)){
					$new_title='';

					/*sku left*/
					if($this->wppizza_options['layout']['sku_display']['menu_listing_title']=='left'){
						$new_title.='<span class="wppizza_sku_title">'.$sku_menu_title.'</span>';
					}
					/*title*/
					$new_title.=''.$title.'';
					/*sku right*/
					if($this->wppizza_options['layout']['sku_display']['menu_listing_title']=='right'){
						$new_title.='<span class="wppizza_sku_title">'.$sku_menu_title.'</span>';
					}
					return $new_title;
				}
			}
		}
		return $title;
	}



	/*********************************************************************************
	*	[adding SKU's menu items sizes in loop as  required]
	*********************************************************************************/
	function wppizza_filter_loop_meta_sku($meta, $post_id){
		/**only show sku if enabled and for non admin or ajax requests with wppizza_json action**/
		if (
				!empty($this->wppizza_options['plugin_data']['sku_enable']) &&
				!empty($this->wppizza_options['layout']['sku_display']['menu_listing_size']) &&
				!is_admin()
				// is this needed ? (!is_admin() || ( is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX && ( isset($_POST['action']) && $_POST['action']=='wppizza_json')))
			){

				/**add size label to meta and pre or append sku as required**/
				foreach($this->wppizza_options['sizes'][$meta['sizes']] as $sizeKey=>$size){

					/*check if we have size sku*/
					$size_sku=!empty($meta['sku'][$sizeKey]) ? $meta['sku'][$sizeKey] : false;
					/* if there's no size main , get the main sku**/
					if(!$size_sku){
						$size_sku=!empty($meta['sku'][-1]) ? $meta['sku'][-1] : false;
					}

					$meta['size_label'][$sizeKey]='';
					/*prepend*/
					if(!empty($size_sku) && $this->wppizza_options['layout']['sku_display']['menu_listing_size']=='left'){
						$meta['size_label'][$sizeKey].='<span class="wppizza_sku">'.$size_sku.'</span>';
					}

					/**show if not replaced by sqk provided there is one*/
					if(empty($this->wppizza_options['layout']['sku_replaces_size']) || empty($size_sku)){
						$meta['size_label'][$sizeKey].=''.$size['lbl'];
					}

					/*append*/
					if(!empty($size_sku) && $this->wppizza_options['layout']['sku_display']['menu_listing_size']=='right'){
						$meta['size_label'][$sizeKey].='<span class="wppizza_sku">'.$size_sku.'</span>';
					}
				}
			return $meta;
		}

		return $meta;
	}
}
$WPPIZZA_SKU = new WPPIZZA_SKU($wppizza_options);
?>