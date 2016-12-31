<?php
if ( ! defined( 'ABSPATH' ) ) exit;/*Exit if accessed directly*/
/*************************************************************************************************
*
*	WPPizza PHPMailer Settings
*
*	to use smpt use an action in your theme's functions.php like so
*
*
*	add_action( 'wppizza_phpmailer_smtp', 'mywppizza_phpmailer_smtp' );
*	function mywppizza_phpmailer_smtp($phpmailer){
* 		$phpmailer->Host       = "mail.yourdomain.com"; // SMTP server
*  		$phpmailer->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)
*  		$phpmailer->SMTPAuth   = true;                  // enable SMTP authentication
*  		$phpmailer->SMTPSecure = "ssl";                 // ssl or tls
*  		$phpmailer->Host       = "smtp.gmail.com";      // sets GMAIL as the SMTP server
*  		$phpmailer->Port       = 465;                   // set the SMTP port
*  		$phpmailer->Username   = "yourusername@gmail.com";  // GMAIL username
*  		$phpmailer->Password   = "yourpassword";            // GMAIL password
*	}
*
*	as of v.2.12:
*	to add additional settings use an action in your theme's functions.php like so
*
*
*	add_action( 'wppizza_phpmailer_settings', 'mywppizza_phpmailer_settings' );
*	function mywppizza_phpmailer_settings($phpmailer){
*		$phpmailer->Priority = 1;//Priority : For most clients expecting the Priority header: 1 = High, 2 = Medium, 3 = Low
*		$phpmailer->AddCustomHeader("X-MSMail-Priority: High"); //MS Outlook custom header May set to "Urgent" or "Highest" rather than "High"
*		$phpmailer->AddCustomHeader("X-MimeOLE" , "Produced By Microsoft MimeOLE V6.00.2800.1441");
*		$phpmailer->AddCustomHeader("X-Mailer" , "Microsoft Office Outlook, Build 11.0.5510"); // Faking Outlook Header
*	}
*
*
**************************************************************************************************/
if(has_action('wppizza_phpmailer_smtp')){
	$phpmailer->IsSMTP();
}
try {
	$phpmailer->CharSet = WPPIZZA_CHARSET;

	/**add some smtp settings*/
	do_action('wppizza_phpmailer_smtp', $phpmailer, $email_markup, $template_id);

	/**add some additional phpmailer settings*/
	do_action('wppizza_phpmailer_settings', $phpmailer, $email_markup, $template_id);


	/************************************************
		from
		by default, these are the customer submitted name/email.
		However these might also be statically set in order settings
	************************************************/
	if($orderFromEmail!=''){
	$phpmailer->SetFrom(''.$orderFromEmail.'',''.$orderFromName.'');
	}

	/************************************************
		the subject
		as set/filtered
	************************************************/
	$phpmailer->Subject = '' . $email_subject . '';


	/************************************************
		who to send the order to
		$email_recipients = array()
	*************************************************/
	if(!empty($email_recipients)){
	foreach($email_recipients as $k=>$recp){
		$phpmailer->AddAddress(''.$recp.'', '');
	}}

	/************************************************
		any cc's
		$email_recipients_cc = array()
	************************************************/
	if(!empty($email_recipients_cc) && count($email_recipients_cc)>0){
	foreach($email_recipients_cc as $k=>$cc){
		/**lets try and make a name*/
		$ccName=explode('@',$cc);
		$ccName=str_replace('.',' ',$ccName[0]);/*worth a go*/
		$ccName=apply_filters('wppizza_phpmailer_cc_name',$ccName);
		$phpmailer->AddCC(''.$cc.'',''.$ccName.'');
	}}

	/************************************************
		any bcc's
		(only really applicable for emails to shop)
	************************************************/
	if(!empty($email_recipients_bcc) && count($email_recipients_bcc)>0){
	foreach($email_recipients_bcc as $bcc){
			$phpmailer->AddBCC("".$bcc."");
	}}

	/************************************************
		any reply to's
		$email_reply_to = string (email address)
	************************************************/
	if(!empty($email_reply_to)){
		/**lets try and make a name*/
		$rtName=explode('@',$email_reply_to);
		$rtName=str_replace('.',' ',$rtName[0]);/*worth a go*/
		$rtName=apply_filters('wppizza_phpmailer_replyto_name',$rtName);
		$phpmailer->AddReplyTo(''.$email_reply_to.'', ''.$rtName.'');
	}

	/************************************************

		any attachments set in options

	************************************************/
	if(!empty($email_attachments)){
	foreach($email_attachments as $attachment){
		$phpmailer->AddAttachment($attachment);
	}}

	/***********************************************
		the html/plaintext
		as returned by templates
	************************************************/
	$phpmailer->MsgHTML($email_markup['html']);
	$phpmailer->AltBody = $email_markup['plaintext']; // optional - MsgHTML will create an alternate automatically, however this has been prettied up a little for this plugin. If you must, feel free to comment this line out though


	/***********************************************
		allow filtering if needs be -
		a bit overkill, but maybe someone needs it
		to - for example - unset the AltBody
	************************************************/
	$phpmailer = apply_filters('wppizza_phpmailer_filter_mailobject', $phpmailer);

	/************************************************

		send the mail

	************************************************/
	/*dont actually send if disable_emails is set*/
	if(empty($options['tools']['disable_emails'])){
		$phpmailer->Send();
	}


} catch (phpmailerException $e) {
	$phpmailerError=$e->errorMessage();//Pretty error messages from PHPMailer
	$phpmailer_mail_results['status']=false;
	$phpmailer_mail_results['error']=$phpmailerError .''. PHP_EOL;// 'full debug: '.print_r($e,true);
	$error_get_last=error_get_last();
	if(!empty($error_get_last)){
		$phpmailer_mail_results['error'].=' | '.print_r($error_get_last,true);/**sometimes there's somthing in that variable too*/
	}



} catch (Exception $e) {
	$phpmailerError=$e->getMessage();//Boring error messages from anything else!
	$phpmailer_mail_results['status']=false;
	$phpmailer_mail_results['error']=$phpmailerError .''. PHP_EOL;//'full debug: '.print_r($e,true);
	$error_get_last=error_get_last();
	if(!empty($error_get_last)){
		$phpmailer_mail_results['error'].=' | '.print_r($error_get_last,true);/**sometimes there's somthing in that variable too*/
	}
}

	/**
		THE ACTION BELOW WILL SOON BE DEPRECIATED IN FAVOUR OF wppizza_on_order_executed
		some action hook, do with  this what  you will like so in your function.php:
		add_action('wppizza_phpmailer_sent', 'my_custom_phpmailer',10,2);
		function my_custom_phpmailer($phpmailer,$mailsent){
			if($mailsent){
			//**do something with the $mail object (like curl post or whatnot)
			}
		}
	**/
	do_action('wppizza_phpmailer_sent',$phpmailer, $phpmailer_mail_results['status']);
?>