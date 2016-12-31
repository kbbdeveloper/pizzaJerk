<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<?php

    $instance = wp_parse_args(
        (array)$instance,$this->wppizza_default_widget_settings()
    );
    $title = isset($instance['title']) ? esc_attr($instance['title']) : '';
    //$suppresstitle = $instance['suppresstitle'] ? 'checked="checked"' : '';
    $suppresstitle = checked($instance['suppresstitle'],true,false);


    //$noheader = $instance['noheader'] ? 'checked="checked"' : '';
    $noheader = checked($instance['noheader'],true,false);
    $type  = isset($instance['type']) ? esc_attr($instance['type']) : '';

    $showadditives  = isset($instance['showadditives']) ? esc_attr($instance['showadditives']) : '';



    $type  = isset($instance['type']) ? esc_attr($instance['type']) : '';
  	$term  = isset($instance['term']) ? esc_attr($instance['term']) : '';
  	$navterm  = isset($instance['navterm']) ? esc_attr($instance['navterm']) : '';
  	$openingtimes = checked($instance['openingtimes'],true,false);
  	$stickycart = !empty($instance['stickycart']) ? true : false;
  	$minicart = !empty($instance['minicart']) ? true : false;
  	$orderinfo = checked($instance['orderinfo'],true,false);
  	$width = $instance['width'] ?  esc_attr($instance['width']) : '';
  	$height = $instance['height'] ?  absint($instance['height']) : '';


  	$posttypewppizza = !empty($instance['wppizza']) ? true : false;
  	$posttypepost = !empty($instance['post']) ? true : false;
  	$posttypepage = !empty($instance['page']) ? true : false;
  	$loggedinonly = !empty($instance['loggedinonly']) ? true : false;

?>
<div id="<?php echo $this->id; ?>" class="<?php echo $this->pluginSlug; ?>">

	    <p>
	    	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e("Widget Title", 'wppizza-locale'); ?>:</label>
	    	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
	    	<br/>
	    	<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('suppresstitle'); ?>" name="<?php echo $this->get_field_name('suppresstitle'); ?>" <?php echo $suppresstitle; ?> value="1" />
	    	<label for="<?php echo $this->get_field_id( 'suppresstitle' ); ?>"><?php _e("Suppress Title ?", 'wppizza-locale'); ?></label>

	    </p>

	    <p class="<?php echo $this->pluginSlug; ?>-type">
	    	<label for="<?php echo $this->get_field_id( 'type' ); ?>"><?php _e("Widget Type", 'wppizza-locale'); ?>:</label>
	        <select id="<?php echo $this->get_field_id( 'type' ); ?>" class="widefat <?php echo $this->pluginSlug; ?>-select" name="<?php echo $this->get_field_name( 'type' ); ?>">
	        <?php foreach($this->wppizza_type_options() as $key => $val){ ?>
	        	<option value="<?php echo $key; ?>" <?php selected($key,$type,true) ?>><?php echo $val; ?></option>
	        <?php } ?>
	        </select>
	    </p>
		<div id="<?php echo $this->pluginSlug; ?>-selected-<?php echo $this->number; ?>" class="<?php echo $this->pluginSlug; ?>-selected">

		    <p class="<?php echo $this->pluginSlug; ?>-selected-navigation" <?php if($type=='navigation'){echo "style='display:block'";}else{echo "style='display:none'";} ?>>
				<?php
				$allterms = get_terms( WPPIZZA_TAXONOMY, array('hide_empty' => 0) );
				?>
		        <select id="<?php echo $this->get_field_id( 'navterm' ); ?>" class="widefat" name="<?php echo $this->get_field_name( 'navterm' ); ?>">
		        	<option value="" <?php selected('',$navterm,true) ?>><?php _e("All Categories [default]", 'wppizza-locale'); ?></option>
		        <?php foreach($allterms as $theterm){ ?>
		        	<option value="<?php echo $theterm->slug; ?>" <?php selected($theterm->slug,$navterm,true) ?>><?php echo $theterm->name; ?></option>
		        <?php } ?>
		        </select><br/>
		        <small style="color:blue"><?php _e("Please refer to <a href='http://wordpress.org/extend/plugins/wppizza/faq/' target='_blank'>FAQ</a> when using the widget (or shortcode) to display the navigation", 'wppizza-locale'); ?></small>
			</p>

		    <p class="<?php echo $this->pluginSlug; ?>-selected-orderpage" <?php if($type=='orderpage'){echo "style='display:block'";}else{echo "style='display:none'";} ?>>
		        <small style="color:blue"><?php _e("You probably want to create a dedicated orderpage with the following shortcode instead [wppizza type='orderpage'].", 'wppizza-locale'); ?></small>
			</p>

		    <p class="<?php echo $this->pluginSlug; ?>-selected-openingtimes" <?php if($type=='openingtimes'){echo "style='display:block'";}else{echo "style='display:none'";} ?>>
		        <small style="color:blue"><?php _e("Displays openingtimes set in wppizza->settings->openingtimes. shortcode [wppizza type='openingtimes']", 'wppizza-locale'); ?></small>
			</p>

		    <p class="<?php echo $this->pluginSlug; ?>-selected-category" <?php if($type=='category'){echo "style='display:block'";}else{echo "style='display:none'";} ?>>
				<?php
				$allterms = get_terms( WPPIZZA_TAXONOMY, array('hide_empty' => 0) );
				?>
		        <select id="<?php echo $this->get_field_id( 'term' ); ?>" class="widefat" name="<?php echo $this->get_field_name( 'term' ); ?>">
		        	<option value="" <?php selected('',$term,true) ?>><?php _e("First Category [default]", 'wppizza-locale'); ?></option>
		        <?php foreach($allterms as $theterm){ ?>
		        	<option value="<?php echo $theterm->slug; ?>" <?php selected($theterm->slug,$term,true) ?>><?php echo $theterm->name; ?></option>
		        <?php } ?>
		        </select><br/>

		        <small style="color:blue"><?php _e("Show Additives List at bottom of page ?", 'wppizza-locale'); ?></small><br/>
		        <select id="<?php echo $this->get_field_id( 'showadditives' ); ?>" class="widefat" name="<?php echo $this->get_field_name( 'showadditives' ); ?>">
		        	<option value="" <?php selected('',$showadditives,true) ?>><?php _e("Auto [default]", 'wppizza-locale'); ?></option>
		        	<option value="0" <?php selected('0',$showadditives,true) ?>><?php _e("Force Hide", 'wppizza-locale'); ?></option>
		        	<option value="1" <?php selected('1',$showadditives,true) ?>><?php _e("Force Show", 'wppizza-locale'); ?></option>
		        </select><br/>

		    	<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('noheader'); ?>" name="<?php echo $this->get_field_name('noheader'); ?>" <?php echo $noheader; ?> value="1" />
		    	<label for="<?php echo $this->get_field_id( 'noheader' ); ?>"><?php _e("Suppress Category Header ?", 'wppizza-locale'); ?></label>

			</p>

			<p class="<?php echo $this->pluginSlug; ?>-selected-cart" <?php if($type=='cart'){echo "style='display:block'";}else{echo "style='display:none'";} ?>>
		    	<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('openingtimes'); ?>" name="<?php echo $this->get_field_name('openingtimes'); ?>" <?php echo $openingtimes; ?> value="1" />
		    	<label for="<?php echo $this->get_field_id( 'openingtimes' ); ?>"><?php _e("Display Openingtimes ?", 'wppizza-locale'); ?></label><br/>

		    	<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('orderinfo'); ?>" name="<?php echo $this->get_field_name('orderinfo'); ?>" <?php echo $orderinfo; ?> value="1" />
		    	<label for="<?php echo $this->get_field_id( 'orderinfo' ); ?>"><?php _e("Display Order Info ?", 'wppizza-locale'); ?></label><br/>

				<input id="<?php echo $this->get_field_id( 'width' ); ?>" name="<?php echo $this->get_field_name( 'width' ); ?>" type="text" size="2" value="<?php echo $width; ?>" />
		    	<label for="<?php echo $this->get_field_id( 'width' ); ?>"><?php _e("Width [&#37; or px]", 'wppizza-locale'); ?></label>
		    	<br/><small style="margin-left:10px"><?php _e("i.e. 200px or 85% - defaults to 100% if left blank", 'wppizza-locale'); ?></small>
		    	<br />

		    	<input id="<?php echo $this->get_field_id( 'height' ); ?>" name="<?php echo $this->get_field_name( 'height' ); ?>" type="text" size="2" value="<?php echo $height; ?>" />
		    	<label for="<?php echo $this->get_field_id( 'height' ); ?>"><?php _e("Height [Integer]", 'wppizza-locale'); ?></label>
		    	<br/><small style="margin-left:10px"><?php _e("css defaults to 250px if left blank", 'wppizza-locale'); ?></small>
		    	<br />

				<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('stickycart'); ?>" name="<?php echo $this->get_field_name('stickycart'); ?>" <?php checked($stickycart,true,true) ?> value="1" />
		    	<label for="<?php echo $this->get_field_id( 'stickycart' ); ?>"><?php _e("Cart visible on page when scrolling ?", 'wppizza-locale'); ?></label><br/>
		    	<small style="margin-left:10px"><?php _e("See also WPPizza->Layout for additional options", 'wppizza-locale'); ?></small>

				<br />
				<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('minicart'); ?>" name="<?php echo $this->get_field_name('minicart'); ?>" <?php checked($minicart,true,true) ?> value="1" />
		    	<label for="<?php echo $this->get_field_id( 'minicart' ); ?>"><?php _e("Add small cart to top of screen if main cart not in view", 'wppizza-locale'); ?></label><br/>
		    	<small style="margin-left:10px"><?php _e("See also WPPizza->Layout->minicart for additional options", 'wppizza-locale'); ?></small>
			</p>

			<p class="<?php echo $this->pluginSlug; ?>-selected-search" <?php if($type=='search'){echo "style='display:block'";}else{echo "style='display:none'";} ?>>
		    	<label><?php _e("include the following in search results:", 'wppizza-locale'); ?></label><br />

		    	<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('wppizza'); ?>" name="<?php echo $this->get_field_name('wppizza'); ?>" <?php checked($posttypewppizza,true,true) ?> value="1" />
		    	<label for="<?php echo $this->get_field_id( 'wppizza' ); ?>"><?php _e("wppizza menu items", 'wppizza-locale'); ?></label><br/>

		    	<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('post'); ?>" name="<?php echo $this->get_field_name('post'); ?>" <?php checked($posttypepost,true,true) ?> value="1" />
		    	<label for="<?php echo $this->get_field_id( 'post' ); ?>"><?php _e("blog posts", 'wppizza-locale'); ?></label><br/>

		    	<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('page'); ?>" name="<?php echo $this->get_field_name('page'); ?>" <?php checked($posttypepage,true,true) ?> value="1" />
		    	<label for="<?php echo $this->get_field_id( 'page' ); ?>"><?php _e("pages", 'wppizza-locale'); ?></label><br/>

		    	<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id('loggedinonly'); ?>" name="<?php echo $this->get_field_name('loggedinonly'); ?>" <?php checked($loggedinonly,true,true) ?> value="1" />
		    	<label for="<?php echo $this->get_field_id( 'loggedinonly' ); ?>"><?php _e("show only for logged in users ?", 'wppizza-locale'); ?></label><br/>

			</p>
		</div>
</div>