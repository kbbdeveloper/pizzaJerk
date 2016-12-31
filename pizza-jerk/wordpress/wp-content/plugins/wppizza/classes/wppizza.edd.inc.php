<?php
/******************************************************************************************************************
*
*	EDD_SL: http://wordpress.org/plugins/easy-digital-downloads/
*	Class to allow to integrate with EDD if used in additional extensions outside the WP repository.
*	Can be used to allow automatic update notifications of extensions to WPPizza in the WP Dashboard via EDD_SL.
*	Not used in the main WPPizza plugin (as any updates of WPPizza will automatically be available).
*	requires WPPIZZA_EDD_SL_PLUGIN_UPDATER in classes/wppizza.edd.plugin.updater.inc.php
*
******************************************************************************************************************/
if (!class_exists( 'WPPizza' ) ) {return ;}
class WPPIZZA_EDD_SL{
	function __construct() {

	}

	/********************************************************************************
	*
	*
	*	[EDD for Gateways]
	*
	*
	********************************************************************************/

	/***********************************************
	*
	*	[add edd sl updater class too gateways]
	*
	***********************************************/
	function gateway_edd_updater($gwEddLicenseKey, $gwEddUrl, $gwEddVersion, $gwEddName, $gatewaypluginpath=false, $author='ollybach'){
		/*include class*/
		if( !class_exists( 'WPPIZZA_EDD_SL_UPDATER' ) ) {
			require_once(WPPIZZA_PATH.'classes/wppizza.edd.plugin.updater.1.6.php');
		}

		/*******
		passing along array of options as opposed to single value (as it might still be undefined before saving the first time)
		also check that we are passing the whole options array for legacy / old gateways that only pass on GatewayEDDLicense array.
		although old gateways will still throw a (inconsequential) phpnotice before first save
		********/
		if(isset($gwEddLicenseKey['GatewayEDDLicense'])){
			$gwEddLicenseKey=$gwEddLicenseKey['GatewayEDDLicense'];
		}
		/*retrieve our license key from the DB*/
		$license_key=empty($gwEddLicenseKey) ? '' : $gwEddLicenseKey;

		/*****************
			fix pluginpath variable for EDD used in some gateways -
			WILL BE OBSOLETE ONCE ALL AFFECTED GATEWAYS HAVE BEEN UPDATED TO HAVE THIS PARAMETER
			(should be passed as __FILE__ in gateways)
		*******************/
		if(!$gatewaypluginpath){
			$gatewaypluginpath=__FILE__;//ini var (although __FILE__ is actually wrong here )

			/*make lowercase*/
			$gwname=strtolower($gwEddName);

			if (strpos($gwname,'paypal') !== false) {
				$gatewaypluginpath='wppizza-gateway-paypal/wppizza-gateway-paypal.php';
			}
			if (strpos($gwname,'stripe') !== false) {
				$gatewaypluginpath='wppizza-gateway-stripe/wppizza-gateway-stripe.php';
			}
			if (strpos($gwname,'2checkout') !== false) {
				$gatewaypluginpath='wppizza-gateway-2checkout/wppizza-gateway-2checkout.php';
			}
			if (strpos($gwname,'authorize') !== false) {
				$gatewaypluginpath='wppizza-gateway-authorize.net/wppizza-gateway-authorize.net.php';
			}
			if (strpos($gwname,'cardsave') !== false) {
				$gatewaypluginpath='wppizza-gateway-cardsave/wppizza-gateway-cardsave.php';
			}
			if (strpos($gwname,'epay') !== false) {
				$gatewaypluginpath='wppizza-gateway-epay.dk/wppizza-gateway-epay.dk.php';
			}
			if (strpos($gwname,'epdq') !== false) {
				$gatewaypluginpath='wppizza-gateway-epdq/wppizza-gateway-epdq.php';
			}
			if (strpos($gwname,'realex') !== false) {
				$gatewaypluginpath='wppizza-gateway-realex/wppizza-gateway-realex.php';
			}
			if (strpos($gwname,'saferpay') !== false) {
				$gatewaypluginpath='wppizza-gateway-saferpay/wppizza-gateway-saferpay.php';
			}
			if (strpos($gwname,'sagepay') !== false) {
				$gatewaypluginpath='wppizza-gateway-sagepay/wppizza-gateway-sagepay.php';
			}
			if (strpos($gwname,'sisow') !== false) {
				$gatewaypluginpath='wppizza-gateway-sisow/wppizza-gateway-sisow.php';
			}
			if (strpos($gwname,'sofort') !== false) {
				$gatewaypluginpath='wppizza-gateway-stripe/wppizza-gateway-stripe.php';
			}
			if (strpos($gwname,'payeezy') !== false) {/*only applicable to payeezu v1.0*/
				$gatewaypluginpath='wppizza-gateway-payeezye/wppizza-gateway-payeezy.php';
			}			
		}



		/* setup the updater */
		$edd_updater = new WPPIZZA_EDD_SL_UPDATER( $gwEddUrl, $gatewaypluginpath , array(
			'version'		=> $gwEddVersion, 		// current version number
			'license'		=> $license_key, 	// license key (used get_option above to retrieve from DB)
			'item_name'		=> $gwEddName, 	// name of this plugin
			'author'		=> $author,  // author of this plugin
			'url'           => home_url(),//added
			'plugin'        => $gatewaypluginpath //added to eliminate php notice on admin plugins screen if missing
			)
		);
	}

	/***********************************************
	*
	*	[toogle edd activation in gateways]
	*
	***********************************************/
	function gateway_edd_toggle($gatewayOptions,$gatewayOptionsName,$gwEddName,$gwEddUrl){//$this->gatewayOptions,$this->gatewayOptionsName
		global $pagenow;

		/*********update and (de)-activate license when saving*******/
		if($pagenow=='options.php' && isset($_POST['wppizza']['gateways'][$gatewayOptionsName]['GatewayEDDLicense'])){

			$licenseCurrent=!empty($gatewayOptions['GatewayEDDLicense']) ? $gatewayOptions['GatewayEDDLicense'] : '' ;/*current license number**/
			$licenseNew=trim(wppizza_validate_string($_POST['wppizza']['gateways'][$gatewayOptionsName]['GatewayEDDLicense']));/*posted license number**/
			/**defaults/previously set vars,  if no action taken**/
			$edd['error']=false;
			$edd['status']=!empty($gatewayOptions['GatewayEDDStatus']['lState']) ? $gatewayOptions['GatewayEDDStatus']['lState'] : '';/*current license status**/
			/***deactivate currently set license first, if it was not '' anyway and new one is different***/
			if($licenseCurrent!='' && $licenseNew!=$licenseCurrent && $edd['status']=='valid'){
				$edd=$this->edd_action('deactivate_license',$licenseCurrent,$gwEddName,$gwEddUrl);
			}

			/***if new different license has been set and we had no error (otherwise we'll just keep the original settings and desplay error****/
			if($licenseNew!='' && !$edd['error']){
				/**its a new key, so lets reset  the status***/
				if($licenseNew!=$licenseCurrent){
					$edd['status']='';
				}
				/**if we are activating**/
				if(isset($_POST['wppizza']['gateways'][$gatewayOptionsName]['GatewayLicenseActivate'])){
					$edd=$this->edd_action('activate_license',$licenseNew,$gwEddName,$gwEddUrl);
				}
				/**if we are de-activating**/
				if(isset($_POST['wppizza']['gateways'][$gatewayOptionsName]['GatewayLicenseDeactivate'])){
					$edd=$this->edd_action('deactivate_license',$licenseNew,$gwEddName,$gwEddUrl);
				}
			}

			/**set the new license option vars***/
			if(!$edd['error']){$edd['license']=$licenseNew;}else{$edd['license']=$licenseCurrent;}/*if there was an error, keep old license and display error message */
			$_POST['wppizza']['gateways'][$gatewayOptionsName]['GatewayEDDLicense']=$edd['license'];
			$_POST['wppizza']['gateways'][$gatewayOptionsName]['GatewayEDDStatus']=array('lState'=>$edd['status'],'eState'=>$edd['error']);
		}
	}


	/***********************************************
	*
	*	[add edd settings fields to gateways]
	*
	***********************************************/
	function gateway_edd_settings($gatewaySettings, $gatewayOptions,$gatewayOptionsName){


		$licenseState= !empty($gatewayOptions['GatewayEDDStatus']) && $gatewayOptions['GatewayEDDStatus']['lState']=='valid' ? array('key'=>'GatewayLicenseDeactivate','lbl'=>__('Deactivate License', 'wppizza-locale'),'colour'=>'green') : array('key'=>'GatewayLicenseActivate','lbl'=>__('Activate License', 'wppizza-locale'),'colour'=>'red');

		$licenseStatus="<p>&nbsp;<label class='button-secondary' style='padding-left:5px;'>";
		$licenseStatus.="<input name='".WPPIZZA_SLUG."[gateways][".$gatewayOptionsName."][".$licenseState['key']."]' type='checkbox' value='1' /> ".$licenseState['lbl']."";
		$licenseStatus.="</label>";
		if(!empty($gatewayOptions['GatewayEDDStatus']['lState'])){
			$licenseStatus.='<span style="color:'.$licenseState['colour'].'"> '.$gatewayOptions['GatewayEDDStatus']['lState'].'</span>';
		}
		$licenseStatus.="</p>";
		$licenseStatus.="<span class='description' style='display:block;clear:both;padding-top:5px'>".__('Please note: entering and activating the license is optional, but if you choose not to do so, you will not be informed of any future bugfixes and/or updates.', 'wppizza-locale')."</span>";

		/**connect error**/
		if(!empty($gatewayOptions['GatewayEDDStatus']['eState'])){
			$licenseStatus.='<p class="wppizza_license_connection_error" style="display:inline-block;color:red;font-size:120%;margin:10px 0;padding:10px;border:1px solid #000000">';
				$licenseStatus.=''.__('ERROR', 'wppizza-locale').':<br/>';
				$licenseStatus.=''. __('There was a connection error, when trying to check your license.<br />Please try again.', 'wppizza-locale').'';
			$licenseStatus.='</p>';
		}

			$gwSettings=$gatewaySettings;

			/**add edd fields to gateway edit screen*/
			$gwSettings[]=array(
				'key'=>'GatewayEDDLicense',
				'value'=>empty($gatewayOptions['GatewayEDDLicense']) ? '' : $gatewayOptions['GatewayEDDLicense'],
				'type'=>'text',
				'options'=>false,
				'validateCallback'=>'wppizza_validate_string',
				'label'=>__('License Key','wppizza-locale'),
				'descr'=>$licenseStatus,
				'placeholder'=>''.__('license key','wppizza-locale').''
			);

			$gwSettings[]=array(
				'key'=>'GatewayEDDStatus',
				'value'=>empty($gatewayOptions['GatewayEDDStatus']) ? '' : $gatewayOptions['GatewayEDDStatus'],
				'type'=>'norender',
				'options'=>false,
				'validateCallback'=>'',
				'label'=>'',
				'descr'=>'',
				'placeholder'=>''
			);

			return $gwSettings;
		}




	/********************************************************************************
	*
	*
	*	[EDD - for NON gateway plugins]
	*
	*
	********************************************************************************/

	/***********************************************
	*
	*	[add edd settings fields]
	*
	***********************************************/
	function echo_edd_settings($slug,$fieldName,$license,$status,$echo=true){
		$output='';
		$output.="<input name='".$fieldName."' type='text' placeholder='".__('Enter your license key')."' size='30' class='regular-text' value='".$license."' />";
		$output.=' '.__('License Key', 'wppizza-locale').'<br />';

		/**print activate or de-activate button**/
		if( $status !== false && $status == 'valid' ) {
			$output.="<label class='button-secondary'><input name='".$slug."[license][action]' type='checkbox' value='deactivate' /> ".__('De-Activate License', 'wppizza-locale')."</label>";
		}else{
			$output.="<label class='button-secondary'><input name='".$slug."[license][action]' type='checkbox' value='activate' /> ".__('Activate License', 'wppizza-locale')."</label>";
		}
		/**print status info**/
		if( $status !== false && $status == 'valid' ) {
			$output.='<span style="color:green;"> '. __('License active', 'wppizza-locale').'</span>';
		}
		if( $status !== false && $status !='' && $status != 'valid' ) {
			$output.='<span style="color:red;"> '. __('License in-active', 'wppizza-locale').' ['.$status.']</span>';
		}
		$output.='<br/>'.__('Please note: entering and activating the license is optional, but if you choose not to do so, you will not be informed of any future bugfixes and/or updates.', 'wppizza-locale').'<br />';

		if($echo){
			echo $output;
		}else{
			return $output;
		}
	}
	/***********************************************
	*
	*	[toogle edd activation in non gateways]
	*
	***********************************************/
	function edd_toggle($current,$licenseNew,$action,$eddName,$eddUrl){
		$licenseCurrent=$current['key'] ;/*current license number**/
		$statusCurrent=$current['status'] ;/*current status**/
		$errorCurrent=$current['error'] ;/*current / last error**/


		/**defaults/previously set vars,  if no action taken**/
		$edd['error']=false;
		$edd['status']=$statusCurrent;

		/***deactivate currently set license first, if it was not '' anyway and new one is different***/
		if($licenseCurrent!='' && $licenseNew!=$licenseCurrent && $statusCurrent=='valid'){
			$edd=$this->edd_action('deactivate_license',$licenseCurrent,$eddName,$eddUrl);
		}

		if($licenseNew==''){
			$edd['error']=false;
			$edd['status']='';
		}

		/***if new different license has been set and we had no error (otherwise we'll just keep the original settings and desplay error****/
		if($licenseNew!='' && !$edd['error']){
			/**its a new key, so lets reset  the status***/
			if($licenseNew!=$licenseCurrent){
				$edd['status']='';
			}
			/**if we are activating**/
			if($action=='activate'){
				$edd=$this->edd_action('activate_license',$licenseNew,$eddName,$eddUrl);
			}
			/**if we are de-activating**/
			if($action=='deactivate'){
				$edd=$this->edd_action('deactivate_license',$licenseNew,$eddName,$eddUrl);
			}
		}
		/**set the new license option vars***/
		if(!$edd['error']){$edd['key']=$licenseNew;}else{$edd['key']=$licenseCurrent;}

	return $edd;
	}


	/********************************************************************************
	*
	*
	*	[EDD General helper]
	*
	*
	********************************************************************************/
	function edd_action($action,$license,$eddName,$eddUrl){
		$api_params = array(
			'edd_action'=> $action,
			'license' 	=> $license,
			'item_name' => urlencode( $eddName ) // the name of our product in EDD
		);
		// Call the custom API.
		$response = wp_remote_get( add_query_arg( $api_params, $eddUrl ), array( 'timeout' => 15, 'sslverify' => false ) );

		// make sure the response came back okay
		$edd['error']=false;
		if ( is_wp_error( $response ) ){
				$edd['error']=true;
		}else{
			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
			$edd['status']=wppizza_validate_string($license_data->license);
		}
	return $edd;
	}
}
?>