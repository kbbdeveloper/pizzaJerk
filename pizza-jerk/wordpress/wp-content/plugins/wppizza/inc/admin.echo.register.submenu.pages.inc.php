<?php if ( ! defined( 'ABSPATH' ) ) exit;/*Exit if accessed directly*/ ?>
<?php
	/**********************************************************************
	*
	*
	*	register admin submenu pages
	*
	*
	**********************************************************************/
	$wppizza_smp_global 		=	add_submenu_page('edit.php?post_type='.$this->pluginSlug.'',$this->pluginName.' '.__('Manage Global Settings', 'wppizza-locale'),__('Settings', 'wppizza-locale'), 'wppizza_cap_settings', $this->pluginSlug.'-settings', array($this, 'admin_manage_settings'));
	$wppizza_smp_order_settings	=	add_submenu_page('edit.php?post_type='.$this->pluginSlug.'',$this->pluginName.' '.__('Manage Order Settings', 'wppizza-locale'),__('&middot; Order Settings', 'wppizza-locale'), 'wppizza_cap_order_settings',  $this->pluginSlug.'-order-settings', array($this, 'admin_manage_order_settings'));
	$wppizza_smp_order_form 	=	add_submenu_page('edit.php?post_type='.$this->pluginSlug.'',$this->pluginName.' '.__('Manage Order Form', 'wppizza-locale'),__('&middot; Order Form Settings', 'wppizza-locale'), 'wppizza_cap_order_form_settings',  $this->pluginSlug.'-order-form', array($this, 'admin_manage_order_form'));
	$wppizza_smp_opening_times 	=	add_submenu_page('edit.php?post_type='.$this->pluginSlug.'',$this->pluginName.' '.__('Manage Opening Times', 'wppizza-locale'),__('&middot; Opening Times', 'wppizza-locale'), 'wppizza_cap_opening_times', $this->pluginSlug.'-opening-times', array($this, 'admin_manage_opening_times'));
	$wppizza_smp_gateways 		=	add_submenu_page('edit.php?post_type='.$this->pluginSlug.'',$this->pluginName.' '.__('Manage Gateways', 'wppizza-locale'),__('&middot; Gateways', 'wppizza-locale'), 'wppizza_cap_gateways',  $this->pluginSlug.'-gateways', array($this, 'admin_manage_gateways'));
	$wppizza_smp_meal_sizes 	=	add_submenu_page('edit.php?post_type='.$this->pluginSlug.'',$this->pluginName.' '.__('Manage Meal Sizes', 'wppizza-locale'),__('&middot; Meal Sizes', 'wppizza-locale'), 'wppizza_cap_meal_sizes',  $this->pluginSlug.'-meal-sizes', array($this, 'admin_manage_meal_sizes'));
	$wppizza_smp_additives 		=	add_submenu_page('edit.php?post_type='.$this->pluginSlug.'',$this->pluginName.' '.__('Manage Additives', 'wppizza-locale'),__('&middot; Additives', 'wppizza-locale'), 'wppizza_cap_additives',  $this->pluginSlug.'-additives', array($this,'admin_manage_additives'));
	$wppizza_smp_layout 		=	add_submenu_page('edit.php?post_type='.$this->pluginSlug.'',$this->pluginName.' '.__('Manage Layout', 'wppizza-locale'),__('&middot; Layout', 'wppizza-locale'), 'wppizza_cap_layout', $this->pluginSlug.'-layout', array($this, 'admin_manage_layout'));
	$wppizza_smp_localization 	=	add_submenu_page('edit.php?post_type='.$this->pluginSlug.'',$this->pluginName.' '.__('Manage Localization', 'wppizza-locale'),__('&middot; Localization', 'wppizza-locale'), 'wppizza_cap_localization', $this->pluginSlug.'-localization', array($this, 'admin_manage_localization'));
	$wppizza_smp_order_history 	=	add_submenu_page('edit.php?post_type='.$this->pluginSlug.'',$this->pluginName.' '.__('Manage Order History', 'wppizza-locale'),__('&middot; Order History', 'wppizza-locale'), 'wppizza_cap_order_history',  $this->pluginSlug.'-order-history', array($this, 'admin_manage_order_history'));
	$wppizza_smp_templates 		=	add_submenu_page('edit.php?post_type='.$this->pluginSlug.'',$this->pluginName.' '.__('Manage Templates', 'wppizza-locale'),__('&middot; Templates', 'wppizza-locale'), 'wppizza_cap_templates',  $this->pluginSlug.'-templates', array($this, 'admin_manage_templates'));
	$wppizza_smp_reports 		=	add_submenu_page('edit.php?post_type='.$this->pluginSlug.'',$this->pluginName.' '.__('Reports', 'wppizza-locale'),__('&middot; Reports', 'wppizza-locale'), 'wppizza_cap_reports',  $this->pluginSlug.'-reports', array($this, 'admin_manage_reports'));
	$wppizza_smp_access 		=	add_submenu_page('edit.php?post_type='.$this->pluginSlug.'',$this->pluginName.' '.__('Manage Access Rights', 'wppizza-locale'),__('&middot; Access Rights', 'wppizza-locale'), 'wppizza_cap_access',  $this->pluginSlug.'-access-rights', array($this, 'admin_manage_access_rights'));
	$wppizza_smp_tools 			=	add_submenu_page('edit.php?post_type='.$this->pluginSlug.'',$this->pluginName.' '.__('Tools', 'wppizza-locale'),__('&middot; Tools', 'wppizza-locale'), 'wppizza_cap_tools',  $this->pluginSlug.'-tools', array($this, 'admin_manage_tools'));


	/**********************************************************************
	*
	*
	*	contextual help admin submenu pages
	*
	*
	**********************************************************************/
	/***********************
	*	templates
	***********************/
    add_action('load-'.$wppizza_smp_templates, 'wppizza_smp_templates_help');
	function wppizza_smp_templates_help () {
	    
	    $screen = get_current_screen();
		/**emails**/
	    $emailsHelpTabContent='';
	    $emailsHelpTabContent.='<div class="wppizza_help_tab_item">';
	    $emailsHelpTabContent.='<h3>use the options below to create/add/edit email templates you wish to send to selected recipients when an order completes</h3>';
	    $emailsHelpTabContent.='</div>';
	    
	    $emailsHelpTabContent.='<div class="wppizza_help_tab_item">';
	    $emailsHelpTabContent.='<b>standard template:</b> uses the template(s) in wppizza/templates (wppizza-order-email-plaintext.php or wppizza-order-email-html.php if format is set to HTML) for email to the selected recipients. if you have edited and copied the template to your theme directory, that version will be used';
	    $emailsHelpTabContent.='</div>';
	    
	    $emailsHelpTabContent.='<div class="wppizza_help_tab_item">';
	    $emailsHelpTabContent.='<b>recipients (shop and bccs, customer, additional recipients):</b> the first selected one will be the main recipient of any emails sent. all others will be in cc. (bccs set in wppizza order settings will of course still  be in bcc if set to "shop and bccs" )';
	    $emailsHelpTabContent.='<div style="margin-left:10px"><b>examples:</b><ul>';
	   	$emailsHelpTabContent.='<li>"shop and bccs" as well as  "customer" selected: shop as recipient, customer in cc (provided email was given), bccs as set in order settings</li>';
	   	$emailsHelpTabContent.='<li>"customer" and "additional recipients" selected: customer as recipient, additional recipients in cc </li>';
	   	$emailsHelpTabContent.='<li>"additional recipients" only: every individual additional recipient will receive *separate* emails using the selected template</li>';
	    $emailsHelpTabContent.='</ul>';	    
	    $emailsHelpTabContent.='</div>';
	    $emailsHelpTabContent.='</div>';

	    $emailsHelpTabContent.='<div class="wppizza_help_tab_item">';
	    $emailsHelpTabContent.='<b>omit attachments:</b> do not attach any files from "wppizza->order settings : Email Attachments" to the email. Default template will always include any attachments defined';
	    $emailsHelpTabContent.='</div>';	    
	    	    
	    $emailsHelpTabContent.='<div class="wppizza_help_tab_item">';
	    $emailsHelpTabContent.='<span class="wppizza_template_toggle  wppizza-dashicons dashicons-edit"></span>';
	    $emailsHelpTabContent.='<span class="wppizza_help_tab_info"><b>edit:</b> click to be able to  move entire sections such as "site details", "overview", "customer details" etc (drag/drop left/right) in your preferred order. Drag and drop (up/down) individual values into the order you prefer for output in that template. To enable or disable any particular value(s) enable or disable its checkbox.</span>';
	    $emailsHelpTabContent.='</div>';
	    
	    $emailsHelpTabContent.='<div class="wppizza_help_tab_item">';
	    $emailsHelpTabContent.='<span class="wppizza-dashicons dashicons-media-code  wppizza-dashicons-template-emails-media-code"></span>';
	    $emailsHelpTabContent.='<span class="wppizza_help_tab_info"><b>css/style (HTML format only):</b> if HTML as output format has been selected, this button becomes available which will let you edit the style declarations on individual sections and/or values. if you edit any declarations, make sure you preview your changes before saving</span>';	    
	    $emailsHelpTabContent.='</div>';
	    
	    $emailsHelpTabContent.='<div class="wppizza_help_tab_item">';
	    $emailsHelpTabContent.='<span class="wppizza_template_preview wppizza-dashicons dashicons-visibility" title="preview"></span>';
	    $emailsHelpTabContent.='<span class="wppizza_help_tab_info"><b>preview:</b> click for a preview of your current settings before committing/saving any changes</span>';	    
	    $emailsHelpTabContent.='</div>';
	    
	    $emailsHelpTabContent.='<div class="wppizza_help_tab_item">';
	    $emailsHelpTabContent.='<span class="wppizza_template_delete wppizza-dashicons dashicons-trash" title="delete"></span>';
	    $emailsHelpTabContent.='<span class="wppizza_help_tab_info"><b>remove:</b> click to remove template.</span>';
	    $emailsHelpTabContent.='</div>';
	    
	    $emailsHelpTabContent.='<div class="wppizza_help_tab_item">';
	    $emailsHelpTabContent.='<span class="wppizza_help_tab_info"><b>to commit your changes you must click on "save changes" to save any edits you may have made.</b></span>';
	    $emailsHelpTabContent.='</div>';

	    $screen->add_help_tab( array(
	        'id'	=> 'wppizza_templates_emails',
	        'title'	=> __('eMails','wppizza-locale'),
	        'content'	=> '' . __( $emailsHelpTabContent ,'wppizza-locale' ) . '',
	    ) );
	    
	    /**print**/
	    $printHelpTabContent='';
	    $printHelpTabContent.='<div class="wppizza_help_tab_item">';
	    $printHelpTabContent.='<h3>use the options below to create/add/edit the template you wish to use when printing from the order history screen</h3>';
	    $printHelpTabContent.='</div>';
	    
	    $printHelpTabContent.='<div class="wppizza_help_tab_item">';
	    $printHelpTabContent.='<b>default template:</b> uses the (HTML format) wppizza/templates/wppizza-order-print.php if you have edited and copied that template to your theme directory, that version will be used instead';
	    $printHelpTabContent.='</div>';

	    $printHelpTabContent.='<div class="wppizza_help_tab_item">';
	    $printHelpTabContent.='<b>format - if applicable:</b> select from plaintext or HTML format';
	    $printHelpTabContent.='</div>';

	    $printHelpTabContent.='<div class="wppizza_help_tab_item">';
		$printHelpTabContent.='<label class="wppizza-dashicons wppizza-dashicons-radio">use <input type="radio" checked="checked" value="1"></label>';
		$printHelpTabContent.='<span class="wppizza_help_tab_info">check to select that particular template when printing from the order history screen</span>';
	    $printHelpTabContent.='</div>';	    
	    
	    $printHelpTabContent.='<div class="wppizza_help_tab_item">';
	    $printHelpTabContent.='<span class="wppizza_template_toggle  wppizza-dashicons dashicons-edit"></span>';
	    $printHelpTabContent.='<span class="wppizza_help_tab_info"><b>edit:</b> click to be able to  move entire sections such as "site details", "overview", "customer details" etc (drag/drop left/right) in your preferred order. Drag and drop (up/down) individual values into the order you prefer for output in that template. To enable or disable any particular value(s) enable or disable its checkbox.</span>';
	    $printHelpTabContent.='</div>';
	    
	    $printHelpTabContent.='<div class="wppizza_help_tab_item">';
	    $printHelpTabContent.='<span class="wppizza-dashicons dashicons-media-code  wppizza-dashicons-template-emails-media-code"></span>';
	    $printHelpTabContent.='<span class="wppizza_help_tab_info"><b>css/style (HTML f-ormat only):</b> if HTML as output format has been selected, this button becomes available which will let you edit the css for that template. if you edit any declarations, make sure you preview your changes before saving.<br /><b>use the "preview" and your browsers element inspector in that preview to view all available classes and id\'s </b></span>';	    
	    $printHelpTabContent.='</div>';
	    
	    $printHelpTabContent.='<div class="wppizza_help_tab_item">';
	    $printHelpTabContent.='<span class="wppizza_template_preview wppizza-dashicons dashicons-visibility" title="preview"></span>';
	    $printHelpTabContent.='<span class="wppizza_help_tab_info"><b>preview:</b> click for a preview of your current settings before committing/saving any changes</span>';	    
	    $printHelpTabContent.='</div>';
	    
	    $printHelpTabContent.='<div class="wppizza_help_tab_item">';
	    $printHelpTabContent.='<span class="wppizza_template_delete wppizza-dashicons dashicons-trash" title="delete"></span>';
	    $printHelpTabContent.='<span class="wppizza_help_tab_info"><b>remove:</b> click to remove template.</span>';
	    $printHelpTabContent.='</div>';
	    
	    $printHelpTabContent.='<div class="wppizza_help_tab_item">';
	    $printHelpTabContent.='<span class="wppizza_help_tab_info"><b>to commit your changes you must click on "save changes" to save any edits you may have made.</b></span>';
	    $printHelpTabContent.='</div>';	    
	    $screen->add_help_tab( array(
	        'id'	=> 'wppizza_templates_print',
	        'title'	=> __('Print Order','wppizza-locale'),
	        'content'	=> '' . __( $printHelpTabContent ,'wppizza-locale' ) . '',
	    ) );
	}
?>