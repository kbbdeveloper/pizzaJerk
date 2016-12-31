<?php
if (!class_exists( 'WPPizza' ) ) {return ;}
	/**gateway classes MUST start with WPPIZZA_GATEWAY_ to be recognised by parent plugin as gateway class**/
	class WPPIZZA_GATEWAY_COD extends WPPIZZA_GATEWAYS {

		function __construct() {
			/**get vars from parent**/
			$this->gatewayName = __('Cash on Delivery','wppizza-locale');/*required gateway name*/
			$this->gatewayIdent = 'cod';/*required, must be unique for each gateway a-z 0-9 and underscores only please. no spaces, dots, hyphens etc*/
			$this->gatewayVersion =$this->pluginVersion;
			$this->gatewayDescription = '';/*required variable (although it can be empty)- additional description of gateway displayed in ADMIN area*/
			$this->gatewayAdditionalInfo = '';/* required variable (although it can be empty) default printed under gateway options FRONTEND - can be changed/localized/emptied in admin */
			$this->gatewayOptionsName = strtolower(get_class());/*required - name of option in option table*/
			$this->gatewayOptions = get_option($this->gatewayOptionsName,0);/**required**/

			$this->gatewayDiscountPercent = 'DiscountPc';/*set field name that corresponds to discount in percent so it can also be calculated in confirmation page (if used/enabled)*/
			$this->gatewayDiscountFixed = 'DiscountFixed';/* set field name that corresponds to fixed discount so it can also be calculated in confirmation page (if used/enabled)*/
			$this->gatewayDiscountMinOrderValue = 'MinOrderValue';/* set field name that corresponds to fixed discount so it can also be calculated in confirmation page (if used/enabled)*/
		}
		/**settings of gateway variables. required function, but can return empty array**/
		function gateway_settings($optionsOnly=false) {
				$gatewaySettings=array();


				$gatewaySettings[]=array(
					'key'=>'DiscountPc',
					'value'=>empty($this->gatewayOptions['DiscountPc']) ? 0 : $this->gatewayOptions['DiscountPc'],
					'type'=>'text',
					'options'=>false,
					'validateCallback'=>'wppizza_validate_float_only',
					'label'=>__('Discount (in %) when payment by COD','wppizza-locale'),
					'descr'=>'',
					'placeholder'=>false,
					'wpml'=>false
				);
				$gatewaySettings[]=array(
					'key'=>'DiscountFixed',
					'value'=>empty($this->gatewayOptions['DiscountFixed']) ? 0 : $this->gatewayOptions['DiscountFixed'],
					'type'=>'text',
					'options'=>false,
					'validateCallback'=>'wppizza_validate_float_only',
					'label'=>__('Fixed Discount when payment by COD ','wppizza-locale'),
					'descr'=>'',
					'placeholder'=>false,
					'wpml'=>false
				);

				$gatewaySettings[]=array(
					'key'=>'MinOrderValue',
					'value'=>empty($this->gatewayOptions['MinOrderValue']) ? 0 : $this->gatewayOptions['MinOrderValue'],
					'type'=>'text',
					'options'=>false,
					'validateCallback'=>'wppizza_validate_float_only',
					'label'=>__('Minimum Order Value for above discounts to be applied','wppizza-locale'),
					'descr'=>'',
					'placeholder'=>false,
					'wpml'=>false
				);

			return $gatewaySettings;
		}
	}
?>