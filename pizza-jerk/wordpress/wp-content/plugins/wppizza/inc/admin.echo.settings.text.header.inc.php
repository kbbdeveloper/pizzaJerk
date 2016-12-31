<?php if ( ! defined( 'ABSPATH' ) ) exit;/*Exit if accessed directly*/ ?>
<?php settings_errors(); ?>
<?php
		if($v['id']=='global'){
			echo '<h4>'.__('Set Options as required', 'wppizza-locale').'</h4>';
		}
		if($v['id']=='layout'){
			echo '<h4>'.__('Set Options as required', 'wppizza-locale').'</h4>';
		}
		if($v['id']=='opening_times'){
			echo '<h4>'.__('Set your opening times. It will not be possible to place an order outside these times - USE 24 HOUR CLOCK.', 'wppizza-locale').'<br/>'.__('If you are closed on a given day set both times to be the same, if you are open 24 hours set times from 0:00 to 24:00', 'wppizza-locale').'<br/>'.__('Ensure that the Wordpress timezone setting in Settings->Timezone is correct', 'wppizza-locale').'</h4>';
		}
		if($v['id']=='order'){
			echo '<h4>'.__('Set currency, minimum delivery prices and email addresses', 'wppizza-locale').'</h4>';
		}
		if($v['id']=='order_form'){
			echo '<h4>'.__('Set the form fields you would like to show when a customer places an order', 'wppizza-locale').'</h4>';
			echo"<span class='description'>".__('<b>Note:</b> only the email can and should be used to send email notifications of the order to the customer (if enabled).', 'wppizza-locale')."</span>";
			echo"<br /><span class='description'><strong>".__('Additionally, if you want to offer customers to be able to register a new account on the order page - provided they are not logged in already - the email field must be set to be "enabled" and "required". Furthermore "Anyone can register" in "Settings->General" has to be enabled too.', 'wppizza-locale')."</strong></span>";

		}
		if($v['id']=='sizes'){
			echo '<h4>'.__('Define a selection of sizes that might be available per item.', 'wppizza-locale').'</h4>';
			echo"<span class='description'>".__('As meals and beverages can come in different sizes, please add/edit the options you want to offer your customers. You will then be able to offer these options on a per item basis:', 'wppizza-locale')."</span>";
			echo"<br /><span style='color:red'>".__('for your own sanity and easier managability now and in the future, I would also suggest to define separate, distinct options for different types of dishes *even if they have the same sizes/labels*. (use the "Admin Screen Label" for easier identification)', 'wppizza-locale')."</span>";
		}
		if($v['id']=='additives'){
			echo '<h4>'.__('Some meals and beverages may contain additives. Add any possible additives here and select them at any meal/beravage that contains these additives. This in turn will add a footnote to pages denoting which item contains what additives', 'wppizza-locale').'</h4>';
			echo '<h4>'.__('Add any additives (or other notes) that a meal may have and tick the relevant box(es) of any meal that contains these additives ', 'wppizza-locale').'</h4>';
			echo"<p class='description'>".__('by default, additives will be sorted alphabetically.<br/>However, you can use the "sort" field to customise the sortorder. If you do, your choosen sort id will be used to identify your choosen additives in the frontend so you want to make sure to have unique identifiers/sort id\'s', 'wppizza-locale')."</p>";
		}
		if($v['id']=='access'){
			echo"<h4>".__('Set the roles that are allowed to access these pages', 'wppizza-locale')."</h4>";
			echo"<span class='description'>".__('Menu Items and Categories are accessible just like "normal" posts', 'wppizza-locale')."</span>";
		}
		if($v['id']=='reports'){
			//currently not in use
			//echo '<h4>'.__('', 'wppizza-locale').'</h4>';
		}
		if($v['id']=='localization'){
			//currently not in use
			//echo '<h4>'.__('', 'wppizza-locale').'</h4>';
		}
		if($v['id']=='gateways'){
			//currently not in use
			//echo '<h4>'.__('', 'wppizza-locale').'</h4>';
		}
		if($v['id']=='history'){
			//currently not in use
			//echo '<h4>'.__('', 'wppizza-locale').'</h4>';
		}
		if($v['id']=='templates'){
			//currently not in use
			//echo '<h4>'.__('', 'wppizza-locale').'</h4>';
		}
?>