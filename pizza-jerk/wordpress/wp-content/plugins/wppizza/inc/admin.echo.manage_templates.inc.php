<?php if ( ! defined( 'ABSPATH' ) ) exit;/*Exit if accessed directly*/ ?>
<?php
		echo'<div id="'.$this->pluginSlug.'-settings" class="wrap wppizza-templates-wrap">';
		/*ident*/
		$txtIdent=__('eMails', 'wppizza-locale');
		if(isset($_GET['tab']) && $_GET['tab']=='print'){
			$txtIdent=__('Order History Print', 'wppizza-locale');
		}
		echo"<h2>". $this->pluginName ." ".__('Templates', 'wppizza-locale')." - ".$txtIdent."</h2>";
		echo"<p style='color:#ff6666;font-weight:600'>Note: Templates are a new addition. If you experience any issues or have any questions, please let me know via the <a href='https://www.wp-pizza.com/support/general-support/' target='_blank'>support forum</a> or <a href='https://www.wp-pizza.com/contact/' target='_blank'>contact form</a>,<br />For basic info please also <span style='color:#0073aa;'>read the help</span> (see top right of your screen)</p>";
		echo'<form action="options.php" method="post">';
		echo'<input type="hidden" name="'.$this->pluginSlug.'_templates" value="1" />';
			settings_fields($this->pluginSlug);
			do_settings_sections('templates');
			submit_button( __('Save Changes', 'wppizza-locale') );
		echo'</form>';
		echo'</div>';
?>