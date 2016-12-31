<?php if ( ! defined( 'ABSPATH' ) ) exit;/*Exit if accessed directly*/ ?>
<?php


/*******************************************************************
*
*	[comparing floats rounding to precision of 4 
*	(that really should do the job for comparing prices in various places)- 
*	returns bool]
*******************************************************************/		
	function wppizza_floatcompare($a, $b, $operator){
		/* lets trim, cast to floats, and round to 4 decimals*/
		$a = round((float)trim($a),4);
		$b = round((float)trim($b),4);

		$bool = version_compare($a, $b , $operator);
		
	return $bool;		
	}

/*******************************************************************
*
*	[helper function to make and return a hash and original string to check against ]
*
*******************************************************************/
	function wppizza_mkHash($array){
		$tohash=serialize($array);
		/*try sha256 first if that's an error, use md5*/
		$hash=''.hash("sha256","".AUTH_SALT."".$tohash."".NONCE_SALT."").'';
		if(!$hash || $hash==false || strlen($hash)<64){
		$hash='['.md5("".AUTH_SALT."".$tohash."".NONCE_SALT."").']';
		}
		$ret['hash']=$hash;
		$ret['order_ini']=$tohash;
	return $ret;
	}
/*******************************************************************
*
*	[helper function to store/use smtp password if used]
*	NOTE: this is by no means perfect but a lot better than the SMTP
*	plugins that are around on wordpress that store this stuff in plaintext
*	teken from http://blog.turret.io/the-missing-php-aes-encryption-example/
*******************************************************************/
	function wppizza_encrypt_decrypt($string, $encrypt=true){

		/*if open ssl is not available, we'll just have to store it as plaintext i'm afraid*/
		if(function_exists('openssl_encrypt')){
			$cipher='aes-256-cbc';
			/*
				make sure encryption_key is 32 chars in case SECURE_AUTH_SALT ever changes.
				not sure if this is required though. distinct lack of documentation at php.net
				regarding openssl_encrypt
			*/
			$encryption_key = MD5(SECURE_AUTH_SALT);
			/*encrypting*/
			if($encrypt){
				$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));
				$encrypted = openssl_encrypt($string, $cipher, $encryption_key, 0, $iv);
				$encrypted = $encrypted . ':' . bin2hex($iv);
			return $encrypted;
			}

			/*de-crypting*/
			if(!$encrypt){
				$parts = explode(':', $string);
				$unhexIV = pack('H*', $parts[1]);
				$decrypted = openssl_decrypt($parts[0], $cipher, $encryption_key, 0, $unhexIV);
			return $decrypted;
			}

		}else{
			return $string;
		}
	}
/*******************************************************************
*
*	[helper function checking if debug is on and logging only ]
*
*******************************************************************/
function wppizza_debug(){
	$debug=false;
	if(defined('WP_DEBUG') && defined('WP_DEBUG_LOG') && defined('WP_DEBUG_DISPLAY') && WP_DEBUG && WP_DEBUG_LOG && !WP_DEBUG_DISPLAY){
		$debug=true;
	}
	return $debug;
}
/*******************************************************************
*
*	[always round up - $precision => no of decimals]
*
*******************************************************************/
function wppizza_round_up( $value, $precision = 2){
    $pow = pow ( 10, $precision );
    return ( ceil ( $pow * $value ) + ceil ( $pow * $value - ceil ( $pow * $value ) ) ) / $pow;
}

/*******************************************************************
*
*	[natural rounding]
*
*******************************************************************/
function wppizza_round_natural( $value, $precision = 2){
    $val = round( $value, $precision );
    
    return $val;
}

/*******************************************************************
*
*	[find serialization errors]
*
*******************************************************************/
function wppizza_serialization_errors($data1){
    $output='';
    //echo "<pre>";
    $data2 = preg_replace ( '!s:(\d+):"(.*?)";!e', "'s:'.strlen('$2').':\"$2\";'",$data1 );
    $max = (strlen ( $data1 ) > strlen ( $data2 )) ? strlen ( $data1 ) : strlen ( $data2 );

    $output.= $data1 . PHP_EOL;
    $output.= $data2 . PHP_EOL;

    for($i = 0; $i < $max; $i ++) {

        if (@$data1 {$i} !== @$data2 {$i}) {

            $output.= "Diffrence ". @$data1 {$i}. " != ". @$data2 {$i}. PHP_EOL;
            $output.= "\t-> ORD number ". ord ( @$data1 {$i} ). " != ". ord ( @$data2 {$i} ). PHP_EOL;
            $output.= "\t-> Line Number = $i" . PHP_EOL;

            $start = ($i - 20);
            $start = ($start < 0) ? 0 : $start;
            $length = 40;

            $point = $max - $i;
            if ($point < 20) {
                $rlength = 1;
                $rpoint = - $point;
            } else {
                $rpoint = $length - 20;
                $rlength = 1;
            }

            $output.= "\t-> Section Data1  = ". substr_replace ( substr ( $data1, $start, $length ). "<b style=\"color:green\">{$data1 {$i}}</b>", $rpoint, $rlength ). PHP_EOL;
            $output.= "\t-> Section Data2  = ". substr_replace ( substr ( $data2, $start, $length ). "<b style=\"color:red\">{$data2 {$i}}</b>", $rpoint, $rlength ). PHP_EOL;
        }

    }

	return $output;
}
/***********************************************************
	admin mail delivery options
***********************************************************/
function wppizza_admin_mail_delivery_options($options, $fieldname=false,$selected=false, $id='', $class=''){
	if(!$fieldname){
		$fieldname="".WPPIZZA_SLUG."[plugin_data][mail_type]";
	}
	if(!$selected){
		$selected=$options['plugin_data']['mail_type'];
	}
	/*return select*/
	$mail_options='';
	$mail_options.="<select id='".$id."' class='".$class."' name='".$fieldname."'>";
		$mail_options.="<option value='wp_mail' ".selected($selected,"wp_mail",false).">".__('Plaintext', 'wppizza-locale')."</option>";
		$mail_options.="<option value='phpmailer' ".selected($selected,"phpmailer",false).">".__('HTML', 'wppizza-locale')."</option>";
	$mail_options.= "</select>";

	return $mail_options;
}
/*************************************************************
*
*	email templates helper email_shop/email_customer
*	(for consistancy across all functions)
*
*************************************************************/
function wppizza_email_recipients($val=false){

	$default_recipients=array();

	$default_recipients['email_shop']['lbl']=__('shop and bccs','wppizza-locale');
	$default_recipients['email_shop']['ini_val']=-1;

	$default_recipients['email_customer']['lbl']=__('customer','wppizza-locale');
	$default_recipients['email_customer']['ini_val']=-1;


	$recipients=array();
	foreach($default_recipients as $rKey=>$rVal){
		/*by default, return labels, otherwise ini values (e.g on install)*/
		if(!$val){
			$recipients[$rKey]=$rVal['lbl'];
		}else{
			$recipients[$rKey]=$rVal['ini_val'];
		}
	}
	return $recipients;
}
/*************************************************************
	convert bytes to something more readable
*************************************************************/
function wppizza_convert_bytes($number){
    $len = strlen($number);
    if($len < 4){
        return sprintf("%d b", $number);
    }
    if($len >= 4 && $len <=6){
        return sprintf("%0.2f Kb", $number/1024);
    }
    if($len >= 7 && $len <=9){
        return sprintf("%0.2f Mb", $number/1024/1024);
    }
   return sprintf("%0.2f Gb", $number/1024/1024/1024);
}

/*************************************************************
	get mysql version if we can
*************************************************************/
function wppizza_get_mysql_version(){
	$mysql_info=array();
	$mysql_info['version']=false;
	$mysql_info['info']='';
	$mysql_info['extension']='unable to determine mysql extension';

	if(!function_exists('mysqli_connect')){
		$mysql_info['info']='mysqli is not available - it is highly recommended to enable it';
	}

	if(function_exists('mysqli_connect')){

		$mysql_info['extension']='mysqli';

		$host_port=explode(':',DB_HOST);
		if(count($host_port)==2){
			$wppizza_test_mysql=mysqli_connect($host_port[0], DB_USER, DB_PASSWORD, DB_NAME, $host_port[1]);
		}else{
			$wppizza_test_mysql=mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		}
		// Check connection
		if (mysqli_connect_errno()){
			$wppizza_test_mysql_error= mysqli_connect_error();
	 		$mysql_info['info']="Failed to connect to MySQL: " . print_r($wppizza_test_mysql_error,true);
		}else{
			$mysql_info['version']=mysqli_get_server_info($wppizza_test_mysql);
			$mysql_info['info']=mysqli_get_server_info($wppizza_test_mysql);
		}
		mysqli_close($wppizza_test_mysql);
	}

	/**try normal sql connection if we do not have mysqli**/
	if(!function_exists('mysqli_connect') && function_exists('mysql_connect') ){

		$mysql_info['extension']='mysql';

		$host_port=explode(':',DB_HOST);
		if(count($host_port)==2){
			$wppizza_test_mysql=mysql_connect($host_port[0], DB_USER, DB_PASSWORD, DB_NAME, $host_port[1]);
		}else{
			$wppizza_test_mysql=mysql_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		}
		// Check connection
		if (!$wppizza_test_mysql) {
			$wppizza_test_mysql_error= mysql_error();
	 		$mysql_info['info']="Failed to connect to MySQL: " . print_r($wppizza_test_mysql_error,true);
		}else{
			$mysql_info['version']=mysql_get_server_info($wppizza_test_mysql);
			$mysql_info['info']=mysql_get_server_info($wppizza_test_mysql);

		}
		mysql_close($wppizza_test_mysql);
	}

	return $mysql_info;
}

/******************************* find path to wp-load.php or any other wp file above current directory under document root****************************/
/** this is probably not very useful ever as one will need to find the path before this has been loaded****/
function wppizza_get_wp_config_path($file){
    $base = dirname(__FILE__);
    $base = str_replace("\\", "/", $base);
    $docRoot = str_replace("\\", "/", $_SERVER['DOCUMENT_ROOT']);
	$httpRoot= str_replace($docRoot, "", $base);
	$chunkPath=explode("/",$httpRoot);
	$wpbase=$docRoot;
	foreach($chunkPath as $k=>$v){
		$filePath=$wpbase.''.$file.'';
		if(file_exists($filePath)){
			return $filePath;
		}
	}
}
?>