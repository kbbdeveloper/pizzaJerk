<?php
/**************************************************************************************************************************************

	CLASS - WPPIZZA_USER_DETAILS

	get all wppizza order details of a particular user identified by his/her wordpress user id

	CURRENTLY THIS IS NOT IN USE AND WILL EXPAND OVER TIME but could be used if required

	if you require more functionality, let me know. I'll be happy to add things

	usage
	require_once(WPPIZZA_PATH.'classes/wppizza.user.details.inc.php');
	$userdetails=new WPPIZZA_USER_DETAILS();
	$userdetails->setUserId($id);


**************************************************************************************************************************************/
if (!class_exists( 'WPPizza' ) ) {return ;}

if (!class_exists('WPPIZZA_USER_DETAILS')) {
	class WPPIZZA_USER_DETAILS extends WPPIZZA {

	public $userId=false;
	public $blogId=false;
	public $paymentStatus=array('COMPLETED');


/**********************************************************************************************
*
*
*	[construct]
*
*
*********************************************************************************************/
		function __construct() {
			parent::__construct();
		}
/**********************************************************************************************
*
*
*	[public methods]
*
*
*********************************************************************************************/
		/*set  the user id we need to get values for*/
		function setUserId($userId=-1){
			$this->userId= $userId ;
		}
		/**set blog id (might be required in multisite setups )*/
		function setBlogId($blogid=false){
			$this->blogId=$blogid;
		}
		/**set payment status we are looking for. (COMPLETED , FAILED etc ) as array */
		function setPaymentStatus($paymentStatus=array('COMPLETED')){
			$this->paymentStatus=$paymentStatus;
		}
		/**********************************
		*
		*	get count of all orders for of this user
		*
		*	@return int
		**********************************/
		function getUserOrderCount(){
			global $wpdb;
			/**select the right blog table if set **/
			if($this->blogId!='' && $this->blogId && (int)$this->blogId>1){
				$wpdb->prefix=$wpdb->base_prefix . $this->blogId.'_';
			}
			$userOrderCount = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->prefix . $this->pluginOrderTable . " WHERE wp_user_id='".(int)$this->userId."' AND payment_status IN ('".implode("','",$this->paymentStatus)."') ");

			return $userOrderCount;
		}

		/**********************************
		*
		*	get total spent for this user
		*
		*	@return int
		**********************************/
		function getUserOrderTotal(){
			global $wpdb;
			/**select the right blog table if set **/
			if($this->blogId!='' && $this->blogId && (int)$this->blogId>1){
				$wpdb->prefix=$wpdb->base_prefix . $this->blogId.'_';
			}
			$total=0;
			$userOrders= $wpdb->get_results("SELECT order_ini FROM " . $wpdb->prefix . $this->pluginOrderTable . " WHERE wp_user_id='".(int)$this->userId."' AND payment_status = 'COMPLETED' ");
			if(is_array($userOrders)){
			foreach($userOrders as $data){
				$order=maybe_unserialize($data->order_ini);
				$total+=$order['total'];
			}}
			return round($total,2);
		}

		/**********************************
		*
		*	get all gateways used by this user for payment
		*
		*	@return array
		**********************************/
		function getUserOrderGateways(){
			global $wpdb;
			/**select the right blog table if set **/
			if($this->blogId!='' && $this->blogId && (int)$this->blogId>1){
				$wpdb->prefix=$wpdb->base_prefix . $this->blogId.'_';
			}
			$gws=array();
			$initiators= $wpdb->get_results("SELECT DISTINCT initiator FROM " . $wpdb->prefix . $this->pluginOrderTable . " WHERE wp_user_id='".(int)$this->userId."' AND payment_status = 'COMPLETED' ");
			if(is_array($initiators)){
			foreach($initiators as $data){
				$ini=strtoupper($data->initiator);
				$gws[$ini]=$ini;
			}}
			return $gws;
		}


	}
}
?>