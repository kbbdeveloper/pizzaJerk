<?php if ( ! defined( 'ABSPATH' ) ) exit;/*Exit if accessed directly*/ ?>
<?php

		echo'<div id="'.$this->pluginSlug.'-settings" class="wrap wppizza-additives-wrap">';
		echo"<h2>". $this->pluginName." ".__('Additives', 'wppizza-locale')."</h2>";
		echo'<form action="options.php" method="post">';
		echo'<input type="hidden" name="'.$this->pluginSlug.'_additives" value="1" />';
			settings_fields($this->pluginSlug);
			do_settings_sections('additives');
			submit_button( __('Save Changes', 'wppizza-locale') );
		echo'</form>';
		echo'</div>';
?>