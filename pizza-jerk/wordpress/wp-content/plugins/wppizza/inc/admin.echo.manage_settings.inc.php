<?php if ( ! defined( 'ABSPATH' ) ) exit;/*Exit if accessed directly*/ ?>
<?php
		echo'<div id="'.$this->pluginSlug.'-settings" class="wrap wppizza-settings-wrap">';
		echo"<h2>". $this->pluginName." ".__('Global Settings', 'wppizza-locale')."</h2>";
		echo'<form action="options.php" method="post">';
		echo'<input type="hidden" name="'.$this->pluginSlug.'_global" value="1" />';
			settings_fields($this->pluginSlug);

			echo"<h3>".__('General', 'wppizza-locale')."</h3>";
			do_settings_sections('global');

			echo"<h3>".__('Permalinks', 'wppizza-locale')."</h3>";
			do_settings_sections('permalinks');

			/**only make this available in multisite installs**/
			if ( is_multisite()){
				echo"<h3>".__('Multisite', 'wppizza-locale')."</h3>";
				do_settings_sections('multisite');
			}

			echo"<h3>".__('Miscellaneous', 'wppizza-locale')."</h3>";
			do_settings_sections('global_miscellaneous');

			echo"<h3>".__('use SMTP for sending WPPizza related emails', 'wppizza-locale')."</h3>";
			echo"<span style='color:blue'>".__('new addition: let me know if there are any issues if you enable/use this', 'wppizza-locale')."</span>";
			do_settings_sections('global_use_smtp');


			submit_button( __('Save Changes', 'wppizza-locale') );
		echo'</form>';
		echo'</div>';

?>