<?php if ( ! defined( 'ABSPATH' ) ) exit;/*Exit if accessed directly*/ ?>
<?php
		/**only run on first install**/
		if(isset($install_options) && $install_options==1){
				/*some lorem ipsum to insert as default description for items**/
				$loremIpsum[0]='Praesent ut massa dolor. Aenean pharetra quam at risus aliquet laoreet posuere ipsum porta.' ;
				$loremIpsum[1]='Integer id lacus sapien, eu porta lectus. Vestibulum justo elit, rutrum a pharetra id, ornare ac est. ' ;
				$loremIpsum[2]='Sed commodo scelerisque magna, eu tempus ante faucibus vitae. Nulla tempus varius ornare. ' ;
				$loremIpsum[3]='Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. ' ;
				$loremIpsum[4]='Praesent non pulvinar neque. Donec ut ante tortor. Fusce sit amet velit eget arcu lobortis imperdiet.' ;
				$loremIpsum[5]='Nunc odio libero, tempor quis mollis eu, gravida vel augue. Aliquam erat volutpat.' ;
				$loremIpsum[6]='Sed neque metus, tincidunt quis fermentum id, rhoncus ut neque. Fusce non metus enim.' ;
				$loremIpsum[7]='Aliquam nec turpis est, id consequat dolor. Etiam rhoncus elementum cursus.' ;
				$loremIpsum[8]='Etiam et dolor turpis, id gravida eros. Ut eu orci nulla. Fusce porta porttitor arcu sed sollicitudin.' ;
				$loremIpsum[9]='Quisque a augue dui, quis venenatis leo. Curabitur bibendum faucibus neque at vehicula. ' ;
				$loremIpsum[10]='Donec feugiat metus vel metus gravida et accumsan tellus pretium. Phasellus tortor sapien, aliquam convallis faucibus non.' ;
				$loremIpsum[11]='Suspendisse potenti. Sed feugiat lectus et odio dignissim at congue libero fermentum.' ;
				$loremIpsum[12]='Sed sodales felis lorem. Nullam eleifend magna eget turpis rutrum ac auctor mauris pharetra.' ;
				$loremIpsum[13]='Aliquam convallis lacinia suscipit. Mauris ac diam enim. Nullam quis lacus odio, et sagittis sem.' ;
				$loremIpsum[14]='Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.' ;
				$loremIpsum[15]='Suspendisse potenti. Pellentesque habitant morbi tristique senectus et netus.' ;
				$loremIpsum[16]='Aenean vitae est arcu, ut ullamcorper dolor.' ;
				$loremIpsum[17]='Sed at tellus quam, in vulputate sem. Ut eu orci nulla. Fusce porta porttitor arcu sed.';
				$loremIpsum[18]='Mauris gravida, nisl a mollis lobortis.';
				$loremIpsum[19]='Phasellus molestie mauris nec sem malesuada rhoncus. Donec volutpat interdum elit.';
				$loremIpsum[20]='Vivamus nisi enim, faucibus ut auctor nec, vulputate vitae nibh. Maecenas scelerisque malesuada risus, sit.';


				/*default sizeoptions/tiers and associated prices**/
				$defaultSizes=array(
					0=>array(
						0=>array('lbl'=>__('regular', 'wppizza-locale'),'price'=>'5.99')
					),
					1=>array(
						0=>array('lbl'=>__('small', 'wppizza-locale'),'price'=>'4.95'),
						1=>array('lbl'=>__('large', 'wppizza-locale'),'price'=>'9.95')
					),
					2=>array(
						0=>array('lbl'=>__('small', 'wppizza-locale'),'price'=>'4.95'),
						1=>array('lbl'=>__('medium', 'wppizza-locale'),'price'=>'7.45'),
						2=>array('lbl'=>__('large', 'wppizza-locale'),'price'=>'9.95')
					),
					3=>array(
						0=>array('lbl'=>__('small', 'wppizza-locale'),'price'=>'4.95'),
						1=>array('lbl'=>__('medium', 'wppizza-locale'),'price'=>'7.45'),
						2=>array('lbl'=>__('large', 'wppizza-locale'),'price'=>'9.95'),
						3=>array('lbl'=>__('xxl', 'wppizza-locale'),'price'=>'14.99')
					),
					4=>array(
						0=>array('lbl'=>__('0.25l', 'wppizza-locale'),'price'=>'0.99'),
						1=>array('lbl'=>__('0.33l', 'wppizza-locale'),'price'=>'1.25'),
						2=>array('lbl'=>__('0.75l', 'wppizza-locale'),'price'=>'1.99'),
						3=>array('lbl'=>__('1.00l', 'wppizza-locale'),'price'=>'2.25'),
						4=>array('lbl'=>__('1.50l', 'wppizza-locale'),'price'=>'2.99'),
					)
				);
				$defaultPrices=array();
				foreach($defaultSizes as $k=>$v){
					foreach($v as $l=>$m){
						$defaultPrices[$k][$l]=$m['price'];
					}
				}

				/*default additives**/
				$defaultAdditives=array(
					0=>array('sort'=>1,'name'=>__('Food coloring', 'wppizza-locale')),
					1=>array('sort'=>2,'name'=>__('Flavor enhancers', 'wppizza-locale')),
					2=>array('sort'=>3,'name'=>__('Preservatives', 'wppizza-locale')),
					3=>array('sort'=>4,'name'=>__('Stabilizers', 'wppizza-locale')),
					4=>array('sort'=>5,'name'=>__('Sweeteners', 'wppizza-locale'))
				);

				/********************************************************************************************
				*
				*	[insert default categories and menu items]
				*
				*********************************************************************************************/
							/*************************************
								[categories]
							/*************************************/
							$defaultCategories = array(
								0=>__('Special Offers', 'wppizza-locale'),
								1=>__('Pizza', 'wppizza-locale'),
								2=>__('Pasta', 'wppizza-locale'),
								3=>__('Salads', 'wppizza-locale'),
								4=>__('Desserts', 'wppizza-locale'),
								5=>__('Beverages', 'wppizza-locale'),
								6=>__('Snacks', 'wppizza-locale')
							);
							/*************************************
								[additional pages]
							/*************************************/
							$defaultMainPages = array(
								0=>array('title'=>__('Our Menu', 'wppizza-locale'),'shortcode'=>'['.WPPIZZA_SLUG.' noheader="1"]'),
								1=>array('title'=>__('Orders', 'wppizza-locale'),'shortcode'=>'['.WPPIZZA_SLUG.' type="orderpage"]'),
								2=>array('title'=>__('Purchase History', 'wppizza-locale'),'shortcode'=>'['.WPPIZZA_SLUG.' type="orderhistory"]')
							);

							/*array to cach/initialize sortorder of categories [inserted into default options below]**/
							$category_sort=array();

							/*************************************
								[add item to categories [linked by key]]
							/*************************************/
							$defaultItems[0] = array(
								array('title'=>__('Special Pizza', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(),'sizes'=>3,'prices'=>$defaultPrices[3]),'featuredimage'=>'pizza-64.png'),
								array('title'=>__('Great Steak', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(),'sizes'=>3,'prices'=>$defaultPrices[3]),'featuredimage'=>'steak-64.png'),
								array('title'=>__('Yummy Pudding', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(),'sizes'=>3,'prices'=>$defaultPrices[3]),'featuredimage'=>'cake-64.png')
							);
							$defaultItems[1] = array(
								array('title'=>__('Pizza A', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(),'sizes'=>2,'prices'=>$defaultPrices[2]),'featuredimage'=>'pizza-64.png'),
								array('title'=>__('Pizza B', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(),'sizes'=>2,'prices'=>$defaultPrices[2]),'featuredimage'=>'pizza-64.png'),
								array('title'=>__('Pizza C', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(),'sizes'=>2,'prices'=>$defaultPrices[2]),'featuredimage'=>'pizza-64.png'),
								array('title'=>__('Pizza D', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(1,2),'sizes'=>2,'prices'=>$defaultPrices[2]),'featuredimage'=>''),
								array('title'=>__('Pizza E', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(),'sizes'=>2,'prices'=>$defaultPrices[2]),'featuredimage'=>''),
								array('title'=>__('Pizza F', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(2,3,4),'sizes'=>2,'prices'=>$defaultPrices[2]),'featuredimage'=>'')
							);
							$defaultItems[2] = array(
								array('title'=>__('Pasta A', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(),'sizes'=>1,'prices'=>$defaultPrices[1]),'featuredimage'=>''),
								array('title'=>__('Pasta B', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(),'sizes'=>1,'prices'=>$defaultPrices[1]),'featuredimage'=>''),
								array('title'=>__('Pasta C', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(),'sizes'=>1,'prices'=>$defaultPrices[1]),'featuredimage'=>''),
								array('title'=>__('Pasta D', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(),'sizes'=>1,'prices'=>$defaultPrices[1]),'featuredimage'=>''),
								array('title'=>__('Pasta E', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(),'sizes'=>1,'prices'=>$defaultPrices[1]),'featuredimage'=>''),
								array('title'=>__('Pasta F', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(),'sizes'=>1,'prices'=>$defaultPrices[1]),'featuredimage'=>'')
							);
							$defaultItems[3] = array(
								array('title'=>__('Salad A', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(),'sizes'=>1,'prices'=>$defaultPrices[1]),'featuredimage'=>''),
								array('title'=>__('Salad B', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(),'sizes'=>1,'prices'=>$defaultPrices[1]),'featuredimage'=>''),
								array('title'=>__('Salad C', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(),'sizes'=>1,'prices'=>$defaultPrices[1]),'featuredimage'=>''),
								array('title'=>__('Salad D', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(),'sizes'=>1,'prices'=>$defaultPrices[1]),'featuredimage'=>''),
								array('title'=>__('Salad E', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(),'sizes'=>1,'prices'=>$defaultPrices[1]),'featuredimage'=>''),
								array('title'=>__('Salad F', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(),'sizes'=>1,'prices'=>$defaultPrices[1]),'featuredimage'=>'')
							);
							$defaultItems[4] = array(
								array('title'=>__('Dessert A', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(1,2),'sizes'=>0,'prices'=>$defaultPrices[0]),'featuredimage'=>'cake-64.png'),
								array('title'=>__('Dessert B', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(),'sizes'=>0,'prices'=>$defaultPrices[0]),'featuredimage'=>''),
								array('title'=>__('Dessert C', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(1,4),'sizes'=>0,'prices'=>$defaultPrices[0]),'featuredimage'=>''),
								array('title'=>__('Dessert D', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(),'sizes'=>0,'prices'=>$defaultPrices[0]),'featuredimage'=>''),
								array('title'=>__('Dessert E', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(2,3,4),'sizes'=>0,'prices'=>$defaultPrices[0]),'featuredimage'=>''),
								array('title'=>__('Dessert F', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(),'sizes'=>0,'prices'=>$defaultPrices[0]),'featuredimage'=>'')
							);
							$defaultItems[5] = array(
								array('title'=>__('Drink A', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(1),'sizes'=>4,'prices'=>$defaultPrices[4]),'featuredimage'=>''),
								array('title'=>__('Drink B', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(1),'sizes'=>4,'prices'=>$defaultPrices[4]),'featuredimage'=>''),
								array('title'=>__('Drink C', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(1),'sizes'=>4,'prices'=>$defaultPrices[4]),'featuredimage'=>''),
								array('title'=>__('Drink D', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(1),'sizes'=>4,'prices'=>$defaultPrices[4]),'featuredimage'=>''),
								array('title'=>__('Drink E', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(1),'sizes'=>4,'prices'=>$defaultPrices[4]),'featuredimage'=>''),
								array('title'=>__('Drink F', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(1),'sizes'=>4,'prices'=>$defaultPrices[4]),'featuredimage'=>'')
							);

							$defaultItems[6] = array(
								array('title'=>__('Snack A', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(0,1),'sizes'=>1,'prices'=>$defaultPrices[1]),'featuredimage'=>''),
								array('title'=>__('Snack B', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(0,1),'sizes'=>1,'prices'=>$defaultPrices[1]),'featuredimage'=>''),
								array('title'=>__('Snack C', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(0,1),'sizes'=>1,'prices'=>$defaultPrices[1]),'featuredimage'=>''),
								array('title'=>__('Snack D', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(0,1),'sizes'=>1,'prices'=>$defaultPrices[1]),'featuredimage'=>''),
								array('title'=>__('Snack E', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(0,1),'sizes'=>1,'prices'=>$defaultPrices[1]),'featuredimage'=>''),
								array('title'=>__('Snack F', 'wppizza-locale'),'descr'=>array_rand($loremIpsum,1),'meta'=>array('add_ingredients'=>false,'additives'=>array(0,1),'sizes'=>1,'prices'=>$defaultPrices[1]),'featuredimage'=>'')
							);


						/**********************************************
						*
						*	[constant defined to not install any defaults]
						*
						**********************************************/
						if(defined('WPPIZZA_NO_DEFAULT_ITEMS') || defined('WPPIZZA_NO_DEFAULTS') ){
							$defaultCategories=array();

						}
						if(defined('WPPIZZA_NO_DEFAULT_PAGES') || defined('WPPIZZA_NO_DEFAULTS') ){
							/*skip pages other than order and orderhistory page*/
							unset($defaultMainPages[0]);
						}

						/**********************************************
						*
						*	[now insert categories and items]
						*
						**********************************************/
							$parent_term = term_exists(''.$this->pluginSlug.''); // array is returned if taxonomy is given
							$parent_term_id = $parent_term['term_id']; // get numeric term id
							$upload_dir = wp_upload_dir();//err, upload dir . doh
							$i=0;
							foreach($defaultCategories as $k=>$v){
								/*insert category*/
								$term=wp_insert_term(
								  ''.$v.'',
								  ''.WPPIZZA_TAXONOMY.'',
								  array(
								    'description'=> ''.__('Description of', 'wppizza-locale').' '.$v.'',
								    'slug' => sanitize_title($v),
								    'parent'=> $parent_term_id
								  )
								);

								if ( is_wp_error($term) ) {
									echo $term->get_error_message();
								}else{
									/*insert item into category*/
									$j=0;
									foreach($defaultItems[$k] as $iKey=>$items){
										$item = array(
									  	'post_title'    	=> wp_strip_all_tags( $items['title'] ),
									  	'post_content'  	=> $loremIpsum[$items['descr']],
									  	'post_type'     	=> $this->pluginSlug,
									  	'post_status'   	=> 'publish',
									  	'menu_order'	  	=> $j,
									  	'comment_status'	=> 'closed',
									  	'ping_status'		=> 'closed',
									  	'post_category' 	=> array($term['term_id']),
									  	'tax_input'      	=> array(''.WPPIZZA_TAXONOMY.'' => array($term['term_id']))
										);
										//					  'post_author'   => 1, ? needed ?
										$post_id=wp_insert_post($item);
										/**add meta boxes values**/
										$metaId=update_post_meta($post_id, ''.$this->pluginSlug.'', $items['meta']) ;

										/*add thumbnail/featured image if set and available**/
										if($items['featuredimage']!='' && is_file(WPPIZZA_PATH.'img/'.$items['featuredimage'].'')){
											$image_data = file_get_contents(WPPIZZA_URL.'img/'.$items['featuredimage']);
											$filename = basename($items['featuredimage']);

											if(wp_mkdir_p($upload_dir['path'])){
					    						$file = $upload_dir['path'] . '/' . $filename;
											}else{
					    						$file = $upload_dir['basedir'] . '/' . $filename;
											}
											file_put_contents($file, $image_data);
											$wp_filetype = wp_check_filetype($filename, null );
											$attachment = array(
											   	'post_mime_type' => $wp_filetype['type'],
										    	'guid' => $upload_dir['url'] . '/' .  $filename ,
										    	'post_title' => sanitize_file_name($filename),
										    	'post_content' => '',
										    	'post_status' => 'inherit'
											);
											$attach_id = wp_insert_attachment( $attachment, $file, $post_id );
											require_once(ABSPATH . 'wp-admin/includes/image.php');
											$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
											wp_update_attachment_metadata( $attach_id, $attach_data );

											set_post_thumbnail( $post_id, $attach_id );
										}

									$j++;
									}
									/*add term id to category sort array to be inserted into options table below*/
									$category_sort[$term['term_id']]=$i;
								$i++;
							}}


						/**********************************************
						*
						*	[insert main category pages and order page
						*	and get their corresponding ids to use further down]
						*
						**********************************************/
						foreach($defaultMainPages as $iKey=>$items){
							$item = array(
							  'post_title'    	=> wp_strip_all_tags( $items['title']),
							  'post_content'  	=> $items['shortcode'],
							  'post_name'  		=> sanitize_title_with_dashes($items['title']),
							  'post_type'     	=> 'page',
							  'post_status'   	=> 'publish',
							  'menu_order'	  	=> 0,
							  'post_parent'	  	=> 0,
							  'comment_status'	=> 'closed',
							  'ping_status'		=> 'closed'
							);
							if($iKey==0){
								$postParent=wp_insert_post($item);
							}
							if($iKey==1){
								$orderPageId=wp_insert_post($item);
							}
						}

						/**********************************************
						*
						*	[insert menu category pages]
						*
						**********************************************/
						$i=0;
						foreach($defaultCategories as $iKey=>$items){
							$item = array(
							  'post_title'    	=> wp_strip_all_tags( $items),
							  'post_content'  	=> '['.WPPIZZA_SLUG.' category="'.sanitize_title_with_dashes($items).'" noheader="1"]',
							  'post_name'  		=> sanitize_title_with_dashes($items),
							  'post_type'     	=> 'page',
							  'post_status'   	=> 'publish',
							  'menu_order'	  	=> $iKey,
							  'post_parent'	  	=> $postParent,
							  'comment_status'	=> 'closed',
							  'ping_status'		=> 'closed'
							);
							$post_id=wp_insert_post($item);
						}

		}else{
			/******************************************************************************************
				[as we are updating the pugin, we use the options in table
				as we are not adding any new pages and categories above]
			**************************************************************************************/
			$category_sort=$options['layout']['category_sort'];
			$category_sort_hierarchy=$options['layout']['category_sort_hierarchy'];
			$defaultSizes=$options['sizes'];
			if(!isset($options['additives'])){$options['additives']=array();}
			$defaultAdditives=$options['additives'];
			$orderPageId=$options['order']['orderpage'];
		}

	/****************************************************
	*
	*	[include some required classes]
	*
	*****************************************************/
	require_once(WPPIZZA_PATH.'classes/wppizza.templates.inc.php');
	$templates=new WPPIZZA_TEMPLATES();

	/****************************************************
	*
	*	[insert default options into options table]
	*
	*****************************************************/
		$defaultOptions = array();

			if(!isset($includeDefaultOptions) || isset($includeDefaultOptions['plugin_data'])){
				$defaultOptions['plugin_data']=array(
					'version' => $this->pluginVersion,
					'mysql_version_ok' => false,
					'js_in_footer' => false,
					'ssl_on_checkout'=>false,
					'mail_type' => 'wp_mail',
					'dequeue_scripts' => '',
					'search_include' => false,
					'post_single_template' => array(),
					'category_parent_page' => array(),
					'single_item_permalink_rewrite'=>'',
					'wp_multisite_session_per_site' => true,
					'wp_multisite_reports_all_sites' => false,
					'wp_multisite_order_history_all_sites' => false,
					'wp_multisite_order_history_print' => array('header_from_child'=>false,'multisite_info'=>false),
					'using_cache_plugin' => false,
					'use_old_admin_order_print' => false,
					'experimental_js' => false,
					'always_load_all_scripts_and_styles' => false,
					'admin_order_history_max_results' => 20,
					'admin_order_history_include_failed' => false,
					'admin_order_history_polling_auto' => false,
					'admin_order_history_polling_time' => 30,
					'nag_notice' => $this->pluginNagNotice,
					'smtp_enable' => false,
					'smtp_host' => '',
					'smtp_port' => '',
					'smtp_encryption' => '',
					'smtp_authentication' => false,
					'smtp_username' => '',
					'smtp_password' => '',
					'smtp_debug' => false
				);
			}


			if(!isset($includeDefaultOptions) || isset($includeDefaultOptions['tools'])){
				$defaultOptions['tools']=array(
					'debug'=>false,
					'disable_emails'=>false
				);
			}


			if(!isset($includeDefaultOptions) || isset($includeDefaultOptions['cron'])){
				$defaultOptions['cron']=array(
					'days_delete'=>7,
					'failed_delete'=>false,
					'schedule'=>false
				);
			}


			if(!isset($includeDefaultOptions) || isset($includeDefaultOptions['templates'])){
				$defaultOptions['templates']=array(
					'emails'=>esc_sql($templates->getTemplateValues(0, 'emails', false, true)),/*add a default email template on install*/
					'print'=>($templates->getTemplateValues(0, 'print', false, true))/*add a default print template on install*/
				);
			}

			if(!isset($includeDefaultOptions) || isset($includeDefaultOptions['templates_apply'])){
				$defaultOptions['templates_apply']=array(
					'emails'=>array(
						'recipients_default'=> wppizza_email_recipients(true),/*default to original editable templates*/
						'recipients_additional'=>array()/*default to no additional recipients*/
					),
					'print'=>'-1'
				);
			}

			if(!isset($includeDefaultOptions) || isset($includeDefaultOptions['layout'])){
				$defaultOptions['layout']=array(
					'category_sort' => $category_sort,
					'category_sort_hierarchy' => empty($category_sort_hierarchy) ? $category_sort : $category_sort_hierarchy,//on install use default category_sort, else keep previously set
					'include_css' => true,
					'css_priority' => 11,
					'style' => 'default',
					'style_grid_columns' => 3,
					'style_grid_margins' => 1.5,
					'style_grid_full_width' => 480,
					'placeholder_img' => true,
					'items_per_loop' => '-1',
					'suppress_loop_headers' => false,
					'hide_cart_icon' => false,
					'hide_prices' => false,
					'hide_item_currency_symbol' => false,
					'hide_single_pricetier' => false,
					'disable_online_order' => false,
					'add_to_cart_on_title_click' => false,
					'hide_decimals' => false,
					'show_currency_with_price' => 0,
					'currency_symbol_left' => false,
					'currency_symbol_position' => 'left',
					'cart_increase' => false,
					'order_page_quantity_change' => false,
					'order_page_quantity_change_left' => false,
					'order_page_quantity_change_style' =>'smoothness',
					'empty_cart_button' => false,
					'prettyPhoto' => false,
					'prettyPhotoStyle' => 'pp_default',
					'items_sort_orderby' => 'menu_order',
					'items_sort_order' => 'ASC',
					'items_group_sort_print_by_category' => false,
					'items_category_hierarchy' => 'full',
					'items_blog_hierarchy' => false,
					'items_category_hierarchy_cart' => 'parent',
					'items_blog_hierarchy_cart' => false,
					'items_category_separator' => ' &raquo; ',
					'sticky_cart_animation' =>450,
					'minicart_max_width_active'=>0,
					'minicart_elm_padding_top'=>0,
					'minicart_elm_padding_selector'=>'',
					'minicart_add_to_element'=>'',
					'minicart_always_shown'=>false,
					'minicart_viewcart'=>false,
					'sticky_cart_animation_style' =>'',
					'sticky_cart_margin_top' =>20,
					'sticky_cart_background' =>'inherit',
					'sticky_cart_limit_bottom_elm_id' =>'',
					'jquery_fb_add_to_cart' =>false,
					'jquery_fb_add_to_cart_ms' =>1000,
					'element_name_refresh_page' =>''
				);
			}

			if(!isset($includeDefaultOptions) || isset($includeDefaultOptions['globals']['close_shop_now'])){
				$defaultOptions['globals']['close_shop_now']=false;
			}
			
			if(!isset($includeDefaultOptions) || isset($includeDefaultOptions['opening_times_standard'])){
				$defaultOptions['opening_times_standard']=array(
					0=>array('open'=>'14:30','close'=>'01:00'),
					1=>array('open'=>'09:30','close'=>'02:00'),
					2=>array('open'=>'09:30','close'=>'02:00'),
					3=>array('open'=>'09:30','close'=>'02:00'),
					4=>array('open'=>'09:30','close'=>'02:00'),
					5=>array('open'=>'09:30','close'=>'02:00'),
					6=>array('open'=>'09:30','close'=>'02:00')
				);
			}


			if(!isset($includeDefaultOptions) || isset($includeDefaultOptions['opening_times_custom'])){
				$defaultOptions['opening_times_custom']=array(
					'date'=>array(''.date("Y").'-12-25',''.(date("Y")+1).'-01-01'),
					'open'=>array('17:00','17:00'),
					'close'=>array('01:00','01:00')
				);
			}


			if(!isset($includeDefaultOptions) || isset($includeDefaultOptions['opening_times_format'])){
				$defaultOptions['opening_times_format']=array(
					'hour'=>'G',
					'separator'=>':',
					'minute'=>'i',
					'ampm'=>''
				);
			}

			if(!isset($includeDefaultOptions) || isset($includeDefaultOptions['times_closed_standard'])){
				$defaultOptions['times_closed_standard']=array();
			}


			if(!isset($includeDefaultOptions) || isset($includeDefaultOptions['order'])){
				$defaultOptions['order']=array(
					'currency'=>'GBP',
					'currency_symbol'=>'£',
					'orderpage'=>$orderPageId,
					'orderpage_exclude'=>true,
					'delivery_selected'=>'minimum_total',
					'discount_selected'=>'none',
					'order_pickup' => false,
					'order_pickup_alert' => false,
					'order_pickup_alert_confirm' => false,
					'order_pickup_as_default' => false,							
					'order_pickup_discount' => 0,
					'order_min_for_delivery' => 0,
					'order_min_for_pickup' => !empty($options['order']['order_min_for_delivery']) ? $options['order']['order_min_for_delivery'] : 0 ,/**on updates, use order_min_for_delivery settings so as to not modify th ebehaviousr in the frontend**/
					'order_min_on_totals' => false,
					'order_pickup_display_location' => 1,
					'delivery'=>array(
						'no_delivery'=>'',
						'minimum_total'=>array('min_total'=>'7.5','deliver_below_total'=>true,'deliverycharges_below_total'=>'0'),
						'standard'=>array('delivery_charge'=>'7.5'),
						'per_item'=>array('delivery_charge_per_item'=>'0','delivery_per_item_free'=>'50')
					),
					'delivery_calculation_exclude_item'=>array(),
					'delivery_calculation_exclude_cat'=>array(),
					'discounts'=>array(
						'none'=>array(),
						'percentage'=>array(
							'discounts'=>array(
								0=>array('min_total'=>'20','discount'=>'5'),
								1=>array('min_total'=>'50','discount'=>'10')
							)
						),
						'standard'=>array(
							'discounts'=>array(
								0=>array('min_total'=>'20','discount'=>'5'),
								1=>array('min_total'=>'50','discount'=>'10')
							)
						)
					),
					'discount_calculation_exclude_item'=>array(),
					'discount_calculation_exclude_cat'=>array(),
					'item_tax'=>0,
					'item_tax_alt'=>0,
					'taxes_included'=>false,
					'taxes_round_natural'=>false,						
					'shipping_tax'=>false,
					'shipping_tax_rate' => !empty($options['order']['item_tax']) ? $options['order']['item_tax'] : 0 ,/**on updates, use item_tax settings so as to not modify the initial behaviour in the frontend if enabled**/
					'append_internal_id_to_transaction_id'=>false,
					'order_email_to'=>array(''.get_option('admin_email').''),
					'order_email_bcc'=>array(),
					'order_email_attachments'=>array(),
					'order_email_from'=>'',
					'order_email_from_name'=>'',
					'order_sms'=>'',
					'dmarc_nag_off' => false
				);
			}


			if(!isset($includeDefaultOptions) || isset($includeDefaultOptions['order_form'])){
				$defaultOptions['order_form']=array(
					0=>array('sort'=>0,'key'=>'cname','lbl'=>__('Name :', 'wppizza-locale'),'value'=>array(),'type'=>'text','enabled'=>true,'required'=>false,'required_on_pickup'=>false,'prefill'=>true,'onregister'=>false,'add_to_subject_line'=>true,'placeholder'=>false),
					1=>array('sort'=>1,'key'=>'cemail','lbl'=>__('Email :', 'wppizza-locale'),'value'=>array(),'type'=>'email','enabled'=>true,'required'=>true,'required_on_pickup'=>true,'prefill'=>true,'onregister'=>false,'add_to_subject_line'=>false,'placeholder'=>false),
					2=>array('sort'=>2,'key'=>'caddress','lbl'=>__('Address :', 'wppizza-locale'),'value'=>array(),'type'=>'textarea','enabled'=>true,'required'=>true,'required_on_pickup'=>false,'prefill'=>true,'onregister'=>false,'add_to_subject_line'=>false,'placeholder'=>false),
					3=>array('sort'=>3,'key'=>'ctel','lbl'=>__('Telephone :', 'wppizza-locale'),'value'=>array(),'type'=>'text','enabled'=>true,'required'=>true,'required_on_pickup'=>true,'prefill'=>true,'onregister'=>false,'add_to_subject_line'=>false,'placeholder'=>false),
					4=>array('sort'=>4,'key'=>'ccomments','lbl'=>__('Comments :', 'wppizza-locale'),'value'=>array(),'type'=>'textarea','enabled'=>true,'required'=>false,'required_on_pickup'=>false,'prefill'=>false,'onregister'=>false,'add_to_subject_line'=>false,'placeholder'=>false),
					5=>array('sort'=>5,'key'=>'ccustom1','lbl'=>__('Custom Field 1 :', 'wppizza-locale'),'value'=>array(),'type'=>'text','enabled'=>false,'required'=>false,'required_on_pickup'=>false,'prefill'=>false,'onregister'=>false,'add_to_subject_line'=>false,'placeholder'=>false),
					6=>array('sort'=>6,'key'=>'ccustom2','lbl'=>__('Custom Field 2 :', 'wppizza-locale'),'value'=>array(),'type'=>'text','enabled'=>false,'required'=>false,'required_on_pickup'=>false,'prefill'=>false,'onregister'=>false,'add_to_subject_line'=>false,'placeholder'=>false),
					7=>array('sort'=>7,'key'=>'ccustom3','lbl'=>__('Custom Field 3 :', 'wppizza-locale'),'value'=>array(),'type'=>'text','enabled'=>false,'required'=>false,'required_on_pickup'=>false,'prefill'=>false,'onregister'=>false,'add_to_subject_line'=>false,'placeholder'=>false),
					8=>array('sort'=>8,'key'=>'ccustom4','lbl'=>__('Custom Field 4 :', 'wppizza-locale'),'value'=>array(),'type'=>'text','enabled'=>false,'required'=>false,'required_on_pickup'=>false,'prefill'=>false,'onregister'=>false,'add_to_subject_line'=>false,'placeholder'=>false),
					9=>array('sort'=>9,'key'=>'ccustom5','lbl'=>__('Custom Field 5 :', 'wppizza-locale'),'value'=>array(),'type'=>'text','enabled'=>false,'required'=>false,'required_on_pickup'=>false,'prefill'=>false,'onregister'=>false,'add_to_subject_line'=>false,'placeholder'=>false),
					10=>array('sort'=>10,'key'=>'ccustom6','lbl'=>__('Custom Field 6 :', 'wppizza-locale'),'value'=>array(),'type'=>'text','enabled'=>false,'required'=>false,'required_on_pickup'=>false,'prefill'=>false,'onregister'=>false,'add_to_subject_line'=>false,'placeholder'=>false),
					11=>array('sort'=>11,'key'=>'ctips','lbl'=>__('Tips/Gratuities :', 'wppizza-locale'),'value'=>array(),'type'=>'tips','enabled'=>false,'required'=>false,'required_on_pickup'=>false,'prefill'=>false,'onregister'=>false,'add_to_subject_line'=>false,'placeholder'=>false)
				);
			}

			if(!isset($includeDefaultOptions) || isset($includeDefaultOptions['confirmation_form_enabled'])){
				$defaultOptions['confirmation_form_enabled']=false;
			}


			if(!isset($includeDefaultOptions) || isset($includeDefaultOptions['confirmation_form_amend_order_link'])){
				$defaultOptions['confirmation_form_amend_order_link']='';
			}

			if(!isset($includeDefaultOptions) || isset($includeDefaultOptions['confirmation_form'])){
				$defaultOptions['confirmation_form']=array(
					0=>array('sort'=>0,'key'=>'wpppizza_confirm_1','lbl'=>__('Accept Terms and Conditions', 'wppizza-locale'),'value'=>array(),'type'=>'checkbox','enabled'=>false,'required'=>false),
					1=>array('sort'=>1,'key'=>'wpppizza_confirm_2','lbl'=>__('Distance Selling Regulations ', 'wppizza-locale'),'value'=>array(),'type'=>'checkbox','enabled'=>false,'required'=>false),
					2=>array('sort'=>2,'key'=>'wpppizza_confirm_3','lbl'=>__('Other', 'wppizza-locale'),'value'=>array(),'type'=>'checkbox','enabled'=>false,'required'=>false),
					3=>array('sort'=>3,'key'=>'wpppizza_confirm_4','lbl'=>__('Other', 'wppizza-locale'),'value'=>array(),'type'=>'checkbox','enabled'=>false,'required'=>false)
				);
			}


			if(!isset($includeDefaultOptions) || isset($includeDefaultOptions['localization_confirmation_form'])){
				$defaultOptions['localization_confirmation_form']=array(/**make sure keys are NOT used in "normal" localization vars too, as we are merging those two arrays to use in confirmation page */
					'change_user_details'=>array(
						'descr'=>__('Confirmation Form - [labels]: link text to use for link to return to previous page for changing personal details', 'wppizza-locale'),
						'lbl'=>__('change', 'wppizza-locale')
					),
					'change_order_details'=>array(
						'descr'=>__('Confirmation Form - [labels]: text and associated link to use to direct customer to a page where he/she can amend the order.', 'wppizza-locale'),
						'lbl'=>__('amend order', 'wppizza-locale')
					),
					'payment_method'=>array(
						'descr'=>__('Confirmation Form - [labels]: label for payment method used', 'wppizza-locale'),
						'lbl'=>__('selected payment method :', 'wppizza-locale')
					),
					'legend_legal'=>array(
						'descr'=>__('Confirmation Form - [section header]: legal aspects', 'wppizza-locale'),
						'lbl'=>__('legal aspects', 'wppizza-locale')
					),
					'legend_personal'=>array(
						'descr'=>__('Confirmation Form - [section header]: personal details', 'wppizza-locale'),
						'lbl'=>__('personal information', 'wppizza-locale')
					),
					'legend_payment_method'=>array(
						'descr'=>__('Confirmation Form - [section header]: payment method', 'wppizza-locale'),
						'lbl'=>__('payment method', 'wppizza-locale')
					),
					'legend_order_details'=>array(
						'descr'=>__('Confirmation Form - [section header]: order details', 'wppizza-locale'),
						'lbl'=>__('order details', 'wppizza-locale')
					),
					'confirm_now_button'=>array(
						'descr'=>__('Confirmation Form - [labels]: label buy now button', 'wppizza-locale'),
						'lbl'=>__('buy now (legally binding)', 'wppizza-locale')
					),
					'header_itemised_article'=>array(
						'descr'=>__('Confirmation Form - [itemised header]: article', 'wppizza-locale'),
						'lbl'=>__('article', 'wppizza-locale')
					),
					'header_itemised_price_single'=>array(
						'descr'=>__('Confirmation Form - [itemised header]: single price', 'wppizza-locale'),
						'lbl'=>__('single price', 'wppizza-locale')
					),
					'header_itemised_quantity'=>array(
						'descr'=>__('Confirmation Form - [itemised header]: quantity', 'wppizza-locale'),
						'lbl'=>__('quantity', 'wppizza-locale')
					),
					'header_itemised_price'=>array(
						'descr'=>__('Confirmation Form - [itemised header]: price', 'wppizza-locale'),
						'lbl'=>__('price', 'wppizza-locale')
					),
					'subtotals_after_additional_info'=>array(
						'descr'=>__('Confirmation Form - [miscellaneous]: additional/optional info/text to display after (sub)totals', 'wppizza-locale'),
						'lbl'=>''
					)
				);
			}

			if(!isset($includeDefaultOptions) || isset($includeDefaultOptions['gateways'])){
				$defaultOptions['gateways']=array(
					'gateway_selected'=>array('COD'=>true),
					'gateway_select_as_dropdown'=>false,
					'gateway_select_label'=>__('Please select your payment method:', 'wppizza-locale'),
					'gateway_showorder_on_thankyou'=>false
				);
			}

			if(!isset($includeDefaultOptions) || isset($includeDefaultOptions['sizes'])){
				$defaultOptions['sizes']=$defaultSizes;
			}

			if(!isset($includeDefaultOptions) || isset($includeDefaultOptions['additives'])){
				$defaultOptions['additives']=$defaultAdditives;
			}

			if(!isset($includeDefaultOptions) || isset($includeDefaultOptions['localization'])){
				$defaultOptions['localization']=array(
					'contains_additives'=>array(
						'descr'=>__('Menu Item: label when hovering over additives (if set)', 'wppizza-locale'),
						'lbl'=>__('contains additives', 'wppizza-locale')
					),
					'add_to_cart'=>array(
						'descr'=>__('Menu Item: text to display when hovering over prices', 'wppizza-locale'),
						'lbl'=>__('add to cart', 'wppizza-locale')
					),
					'alert_closed'=>array(
						'descr'=>__('Menu Item: alert when trying to add to cart but shop is closed (only displayed when shoppingcart is displayed on page)', 'wppizza-locale'),
						'lbl'=>__('sorry, we are currently closed', 'wppizza-locale')
					),
					'alert_choose_size'=>array(
						'descr'=>__('Menu Item: alert when adding to cart by clicking on menu name but more than one size is available. (Only relevant if "Add item to cart on click of *item title* " is enabled)', 'wppizza-locale'),
						'lbl'=>__('please choose a size', 'wppizza-locale')
					),
					'jquery_fb_add_to_cart_info'=>array(
						'descr'=>__('Menu Item: text that briefly replaces selected item price when adding item to cart [html allowed]. (Only relevant if "Briefly replace item price with customised text" in WPPizza->Layout is enabled. CSS Class: "wppizza-item-added-feedback")', 'wppizza-locale'),
						'lbl'=>__('<div>&#10004;</div>item added', 'wppizza-locale')
					),
					'previous'=>array(
						'descr'=>__('Menu Pagination : previous page', 'wppizza-locale'),
						'lbl'=>__('< previous', 'wppizza-locale')
					),
					'next'=>array(
						'descr'=>__('Menu Pagination : next page', 'wppizza-locale'),
						'lbl'=>__('next >', 'wppizza-locale')
					),
					'closed'=>array(
						'descr'=>__('Shoppingcart: text to display when shop closed ', 'wppizza-locale'),
						'lbl'=>__('currently closed', 'wppizza-locale')
					),
					'empty_cart'=>array(
						'descr'=>__('Shoppingcart: empty cart button text', 'wppizza-locale'),
						'lbl'=>__('empty cart', 'wppizza-locale')
					),
					'view_cart'=>array(
						'descr'=>__('Shoppingcart: view cart button text', 'wppizza-locale'),
						'lbl'=>__('view cart', 'wppizza-locale')
					),
					'cart_is_empty'=>array(
						'descr'=>__('Shoppingcart: text to display when cart is empty', 'wppizza-locale'),
						'lbl'=>__('your cart is empty', 'wppizza-locale')
					),
					'remove_from_cart'=>array(
						'descr'=>__('Shoppingcart: text to display when hovering over remove from cart icon', 'wppizza-locale'),
						'lbl'=>__('remove from cart', 'wppizza-locale')
					),
					'place_your_order'=>array(
						'descr'=>__('Shoppingcart: text of button in cart to proceed to order page', 'wppizza-locale'),
						'lbl'=>__('place your order', 'wppizza-locale')
					),
					'order_self_pickup'=>array(
						'descr'=>__('Shoppingcart - Self Pickup: text next to self pickup checkbox (if enabled)', 'wppizza-locale'),
						'lbl'=>__('I would like to pickup the order myself', 'wppizza-locale')
					),
					'order_request_delivery'=>array(
						'descr'=>__('Shoppingcart - Delivery: text next to checkbox under cart / on order page (if pickup is set to be the default)', 'wppizza-locale'),
						'lbl'=>__('I would like my order to be delivered', 'wppizza-locale')
					),						
					'order_self_pickup_cart'=>array(
						'descr'=>__('Shoppingcart - Self Pickup: text under total value (if selected by customer)', 'wppizza-locale'),
						'lbl'=>__('Delivery: pickup', 'wppizza-locale')
					),
					'order_self_pickup_cart_js'=>array(
						'descr'=>__('Shoppingcart - Self Pickup: javascript alert when customer selects self pickup (if enabled)', 'wppizza-locale'),
						'lbl'=>__('You have chosen to pickup the order yourself. This order will not be delivered. Please allow 30 min. for us to prepare your order.', 'wppizza-locale')
					),
					'order_delivery_cart_js'=>array(
						'descr'=>__('Shoppingcart - Delivery: javascript alert when customer selects delivery (if enabled)', 'wppizza-locale'),
						'lbl'=>__('Please allow 45 min. for us to deliver your order.', 'wppizza-locale')
					),						
					'history_no_previous_orders'=>array(
						'descr'=>__('History Page: Text to display when the user has not had any previous orders', 'wppizza-locale'),
						'lbl'=>__('you have no previous orders', 'wppizza-locale')
					),
					'your_order'=>array(
						'descr'=>__('Order Page: label above itemised order', 'wppizza-locale'),
						'lbl'=>__('your order', 'wppizza-locale')
					),
					'send_order'=>array(
						'descr'=>__('Order Page: button label for sending order', 'wppizza-locale'),
						'lbl'=>__('send order', 'wppizza-locale')
					),
					'update_order'=>array(
						'descr'=>__('Order Page: button label for updating order [if enabled]', 'wppizza-locale'),
						'lbl'=>__('update order', 'wppizza-locale')
					),
					'order_form_legend'=>array(
						'descr'=>__('Order Page: label above personal info', 'wppizza-locale'),
						'lbl'=>__('please enter the required information below', 'wppizza-locale')
					),
					'order_page_self_pickup'=>array(
						'descr'=>__('Order Page - Self Pickup: text on order page / email to highlight self pickup (if applicable)', 'wppizza-locale'),
						'lbl'=>__('Note: you have chosen to pickup the order yourself. This order will not be delivered. Please allow 30 min. for us to prepare your order.', 'wppizza-locale')
					),
					'order_page_delivery'=>array(
						'descr'=>__('Order Page - Delivery: text on order page / email to highlight delivery (if applicable)', 'wppizza-locale'),
						'lbl'=>''
					),						
					'order_page_no_delivery'=>array(
						'descr'=>__('Order Page - No Delivery Offered / Pickup Only: text on order page / email if delivery is not being offered (if applicable)', 'wppizza-locale'),
						'lbl'=>__('Please collect your order at the store.', 'wppizza-locale')
					),
					'order_page_handling'=>array(
						'descr'=>__('Order Page [Handling Charges]: text on order page if a handling charge for payment processing has been made (if applicable)', 'wppizza-locale'),
						'lbl'=>__('handling charge', 'wppizza-locale')
					),
					'order_page_handling_oncheckout'=>array(
						'descr'=>__('Order Page [Handling Charges]: text on order page if any handling charge will be calculated on checkout by a/the gateway itself', 'wppizza-locale'),
						'lbl'=>__('calculated on checkout', 'wppizza-locale')
					),
					'required_field'=>array(
						'descr'=>__('Order Page: message when required field is missing', 'wppizza-locale'),
						'lbl'=>__('this is a required field', 'wppizza-locale')
					),
					'required_field_email'=>array(
						'descr'=>__('Order Page: message when email address is invalid', 'wppizza-locale'),
						'lbl'=>__('invalid email address', 'wppizza-locale')
					),
					'required_field_decimal'=>array(
						'descr'=>__('Order Page: message when field should be a decimal number', 'wppizza-locale'),
						'lbl'=>__('decimal numbers only please', 'wppizza-locale')
					),
					'thank_you'=>array(
						'descr'=>__('Order Page: label of thank you page after order has been sent', 'wppizza-locale'),
						'lbl'=>__('thank you', 'wppizza-locale')
					),
					'thank_you_p'=>array(
						'descr'=>__('Order Page: text of thank you page after order has been successfully sent', 'wppizza-locale'),
						'lbl'=>__('thank you, we have received your order', 'wppizza-locale')
					),
					'thank_you_error'=>array(
						'descr'=>__('Order Page: text on "thank you" page if there was an *error* sending order emails ', 'wppizza-locale'),
						'lbl'=>__('Apologies. There was an error receiving your order. Please try again.', 'wppizza-locale')
					),
					'order_ini_additional_info'=>array(
						'descr'=>__('Order Page: text optional - additional info on order page [above all other details. only displays before submitting]', 'wppizza-locale'),
						'lbl'=>''
					),
					'update_profile'=>array(
						'descr'=>__('Order Page: label next to checkbox text to allow user to update profile', 'wppizza-locale'),
						'lbl'=>__('update my user data with the details above', 'wppizza-locale')
					),
					'tips'=>array(
						'descr'=>__('Order Page: Tips/Gratuities', 'wppizza-locale'),
						'lbl'=>__('tips/gratuities', 'wppizza-locale')
					),
					'tips_ok'=>array(
						'descr'=>__('Order Page: Tips/Gratuities confirm button', 'wppizza-locale'),
						'lbl'=>__('ok', 'wppizza-locale')
					),
					'loginout_have_account'=>array(
						'descr'=>__('Order Page [login/logout]: text before login link', 'wppizza-locale'),
						'lbl'=>__('already registered ?', 'wppizza-locale')
					),
					'register_option_label'=>array(
						'descr'=>__('Order Page [register]: text label register or continue as guest', 'wppizza-locale'),
						'lbl'=>__('continue as :', 'wppizza-locale')
					),
					'register_option_guest'=>array(
						'descr'=>__('Order Page [register]: register option -> as guest', 'wppizza-locale'),
						'lbl'=>__('guest', 'wppizza-locale')
					),
					'register_option_create_account'=>array(
						'descr'=>__('Order Page [register]: register option -> create account', 'wppizza-locale'),
						'lbl'=>__('create account', 'wppizza-locale')
					),
					'register_option_create_account_info'=>array(
						'descr'=>__('Order Page [register]: additional info when create account option is chosen [html allowed]', 'wppizza-locale'),
						'lbl'=>__('Please ensure your email address is correct. A password will be emailed to you.', 'wppizza-locale')
					),
					'register_option_create_account_error'=>array(
						'descr'=>__('Order Page [register]: error if email was already registered [html allowed]', 'wppizza-locale'),
						'lbl'=>__('This email address has already been registered. Please either <a href="#login">login</a>, use a different email address or continue as guest.', 'wppizza-locale')
					),
					'order_details'=>array(
						'descr'=>__('Order Email: label for order details', 'wppizza-locale'),
						'lbl'=>__('order details', 'wppizza-locale')
					),
					'order_paid_by'=>array(
						'descr'=>__('Order Email: label to identify how the order was paid for', 'wppizza-locale'),
						'lbl'=>__('Paid By:', 'wppizza-locale')
					),
					'order_email_footer'=>array(
						'descr'=>__('Order Email: Text you would like to display at the end of emails after everything else.', 'wppizza-locale'),
						'lbl'=>''
					),
					'spend'=>array(
						'descr'=>__('Label Discount (Spend): i.e "spend" 50.00 save 10.00', 'wppizza-locale'),
						'lbl'=>__('spend', 'wppizza-locale')
					),
					'save'=>array(
						'descr'=>__('Label Discount (Save): i.e spend 50.00 "save" 10.00', 'wppizza-locale'),
						'lbl'=>__('save', 'wppizza-locale')
					),
					'free_delivery_for_orders_of'=>array(
						'descr'=>__('Label Info: i.e. "free delivery for orders over"...', 'wppizza-locale'),
						'lbl'=>__('free delivery for orders over', 'wppizza-locale')
					),
					'minimum_order'=>array(
						'descr'=>__('Label Info: required minimum order value (displayed if applicable)', 'wppizza-locale'),
						'lbl'=>__('minimum order', 'wppizza-locale')
					),
					'minimum_order_delivery'=>array(
						'descr'=>__('Label Info: required minimum order value for delivery (displayed if applicable)', 'wppizza-locale'),
						'lbl'=>__('minimum order for delivery', 'wppizza-locale')
					),
					'free_delivery'=>array(
						'descr'=>__('Price Labels (Sub)Totals: text to display when free delivery applies', 'wppizza-locale'),
						'lbl'=>__('Free Delivery', 'wppizza-locale')
					),
					'delivery_charges'=>array(
						'descr'=>__('Price Labels (Sub)Totals: text delivery charges - when set to "Fixed" or "Free delivery over" (if applicable)', 'wppizza-locale'),
						'lbl'=>__('Delivery Charges', 'wppizza-locale')
					),
					'delivery_charges_per_item'=>array(
						'descr'=>__('Price Labels (Sub)Totals: text delivery when set to "Delivery Charges per item" (if applicable)', 'wppizza-locale'),
						'lbl'=>__('Delivery Charges Per Item', 'wppizza-locale')
					),
					'discount'=>array(
						'descr'=>__('Price Labels (Sub)Totals: text before sum of discounts applied(if any)', 'wppizza-locale'),
						'lbl'=>__('Discount', 'wppizza-locale')
					),
					'item_tax_total'=>array(
						'descr'=>__('Price Labels (Sub)Totals: text before sum of tax applied to all items(if > 0)', 'wppizza-locale'),
						'lbl'=>__('Sales Tax', 'wppizza-locale')
					),
					'taxes_included'=>array(
						'descr'=>__('Price Labels (Sub)Totals: text before sum of tax applied if prices have been entered *inclusive* of tax (if > 0) [%s%% will be replaced by main taxrate applied - if you are using different taxrates, enter text as appropriate]', 'wppizza-locale'),
						'lbl'=>__('Incl. Tax at %s%%', 'wppizza-locale')
					),
					'order_total'=>array(
						'descr'=>__('Price Labels (Sub)Totals: text before total sum of ORDER', 'wppizza-locale'),
						'lbl'=>__('Total', 'wppizza-locale')
					),
					'order_items'=>array(
						'descr'=>__('Price Labels (Sub)Totals: text before total sum of ITEMS in cart', 'wppizza-locale'),
						'lbl'=>__('Your Items', 'wppizza-locale')
					),
					'openinghours_closed'=>array(
						'descr'=>__('Openinghours: text to display when shop is closed that day ', 'wppizza-locale'),
						'lbl'=>__('closed', 'wppizza-locale')
					),
					'openinghours_24hrs'=>array(
						'descr'=>__('Openinghours: text to display when shop is open the whole day ', 'wppizza-locale'),
						'lbl'=>__('all day', 'wppizza-locale')
					),
					'header_order_print_header'=>array(
						'descr'=>__('Print Order Admin - [Header]: optional - for example your shops name', 'wppizza-locale'),
						'lbl'=>''.get_bloginfo('name').''
					),
					'header_order_print_shop_address'=>array(
						'descr'=>__('Print Order Admin - [Address]: replace with your shop\'s address [html allowed]', 'wppizza-locale'),
						'lbl'=>''.get_bloginfo('name').''
					),
					'header_order_print_customer_label'=>array(
						'descr'=>__('Print Order Admin - [Label]: customer details', 'wppizza-locale'),
						'lbl'=>__('Customer Details / Delivery Address', 'wppizza-locale')
					),
					'header_order_print_overview_label'=>array(
						'descr'=>__('Print Order Admin - [Label]: order overview', 'wppizza-locale'),
						'lbl'=>__('Order', 'wppizza-locale')
					),
					'header_order_print_itemised_article'=>array(
						'descr'=>__('Print Order Admin - [itemised header]: article', 'wppizza-locale'),
						'lbl'=>__('Article', 'wppizza-locale')
					),
					'header_order_print_itemised_price'=>array(
						'descr'=>__('Print Order Admin - [itemised header]: price', 'wppizza-locale'),
						'lbl'=>__('Price', 'wppizza-locale')
					),
					'header_order_print_itemised_quantity'=>array(
						'descr'=>__('Print Order Admin - [itemised header]: quantity', 'wppizza-locale'),
						'lbl'=>__('Qty', 'wppizza-locale')
					),
					'common_value_order_delivery'=>array(
						'descr'=>__('Common [Order Values] : delivery', 'wppizza-locale').' (currently only used when printing order in order history)',
						'lbl'=>__('For Delivery', 'wppizza-locale')
					),
					'common_value_order_pickup'=>array(
						'descr'=>__('Common [Order Values] : pickup', 'wppizza-locale').' (currently only used when printing order in order history)',
						'lbl'=>__('For Pickup', 'wppizza-locale')
					),
					'common_value_order_cash'=>array(
						'descr'=>__('Common [Order Values] : cash', 'wppizza-locale').' (currently only used when printing order in order history)',
						'lbl'=>__('Cash', 'wppizza-locale')
					),
					'common_value_order_credit_card'=>array(
						'descr'=>__('Common [Order Values] : credit card', 'wppizza-locale').' (currently only used when printing order in order history)',
						'lbl'=>__('Credit Card', 'wppizza-locale')
					),
					'common_label_order_delivery_type'=>array(
						'descr'=>__('Common [Order Labels] : delivery type', 'wppizza-locale').' (currently only used when printing order in order history)',
						'lbl'=>__('Delivery Type :', 'wppizza-locale')
					),
					'common_label_order_wp_user_id'=>array(
						'descr'=>__('Common [Order Labels] : user id', 'wppizza-locale').' (currently unused)',
						'lbl'=>__('User ID :', 'wppizza-locale')
					),
					'common_label_order_order_id'=>array(
						'descr'=>__('Common [Order Labels] : order id', 'wppizza-locale').'  (currently only used when printing order in order history)',
						'lbl'=>__('Order ID :', 'wppizza-locale')
					),
					'common_label_order_currency'=>array(
						'descr'=>__('Common [Order Labels] : currency', 'wppizza-locale').'  (currently unused)',
						'lbl'=>__('Currency :', 'wppizza-locale')
					),
					'common_label_order_payment_type'=>array(
						'descr'=>__('Common [Order Labels] : payment type', 'wppizza-locale').' (currently only used when printing order in order history)',
						'lbl'=>__('Payment Type :', 'wppizza-locale')
					),
					'common_label_order_payment_method'=>array(
						'descr'=>__('Common [Order Labels] : payment method', 'wppizza-locale').' (currently only used when printing order in order history)',
						'lbl'=>__('Payment Method :', 'wppizza-locale')
					),
					'common_label_order_order_date'=>array(
						'descr'=>__('Common [Order Labels] : order date', 'wppizza-locale').' (currently only used when printing order in order history)',
						'lbl'=>__('Order Date :', 'wppizza-locale')
					),
					'common_label_order_transaction_id'=>array(
						'descr'=>__('Common [Order Labels] : transaction id', 'wppizza-locale').' (currently only used when printing order in order history)',
						'lbl'=>__('Transaction Id :', 'wppizza-locale')
					),
					'common_label_order_payment_outstanding'=>array(
						'descr'=>__('Common [Order Labels] : payment due', 'wppizza-locale').' (currently only used when printing order in order history)',
						'lbl'=>__('Payment Due :', 'wppizza-locale')
					),
					'templates_label_site'=>array(
						'descr'=>__('Template Label : "Site Details"', 'wppizza-locale'),
						'lbl'=>__('Site Details', 'wppizza-locale')
					),
					'templates_label_ordervars'=>array(
						'descr'=>__('Template Label : "Overview"', 'wppizza-locale'),
						'lbl'=>__('Overview', 'wppizza-locale')
					),
					'templates_label_customer'=>array(
						'descr'=>__('Template Label : "Customer Details"', 'wppizza-locale'),
						'lbl'=>__('Customer Details', 'wppizza-locale')
					),
					'templates_label_order_left'=>array(
						'descr'=>__('Template Label : "Order" - [1] First Column (e.g Quantity)', 'wppizza-locale'),
						'lbl'=>__('Qty', 'wppizza-locale')
					),
					'templates_label_order_center'=>array(
						'descr'=>__('Template Label : "Order" - [2] Second Column (e.g Article)', 'wppizza-locale'),
						'lbl'=>__('Article', 'wppizza-locale')
					),
					'templates_label_order_right'=>array(
						'descr'=>__('Template Label : "Order" - [3] Third Column (e.g Price)', 'wppizza-locale'),
						'lbl'=>__('Price', 'wppizza-locale')
					),
					'templates_label_summary'=>array(
						'descr'=>__('Template Label : "Summary"', 'wppizza-locale'),
						'lbl'=>__('Summary', 'wppizza-locale')
					),
					'user_profile_label_additional_info'=>array(
						'descr'=>__('User Profile : Title above additional fields added/enabled', 'wppizza-locale').'',
						'lbl'=>__('Additional Information', 'wppizza-locale')
					)
				);
			}


/********************************************************
	[set initial admin access to plugin pages/tabs]
********************************************************/
	/**********************
		if the default cap vars have never been set before, do it now (one time only)
		essentially, every user role that has manage_options caps will get all cpas for this
		plugin added to start off with, after which they can be edited in the acees rights tab
		(provided the user has access to that tab of course)
	**********************/
	if(!isset($options['admin_access_caps'])){
		global $wp_roles;
		$wppizzaCaps=$this->wppizza_set_capabilities();

		/*get all roles that have manage_options capabilities**/
		$defaultAdmins=array();
		foreach($wp_roles->roles as $rName=>$rVal){
			if(isset($rVal['capabilities']['manage_options'])){
				$defaultAdmins[$rName]=$rName;
			}
		}
		/**foreach of these, add all capabilities**/
		$setCaps=array();
		foreach($defaultAdmins as $k=>$roleName){
			$userRole = get_role($roleName);
			foreach($wppizzaCaps as $akey=>$aVal){
				$setCaps[$k][]=$aVal['cap'];
				$userRole->add_cap( ''.$aVal['cap'].'' );
			}
		}
		/**set a variable so we do not overwrite it in future updates*/
		/*might as well save the role->caps array. might come in handy one day**/
		$defaultOptions['admin_access_caps']=$setCaps;
	}else{
		global $wp_roles;
		/******************************************
			[check for newly added capabilities
			end enable for roles that have
			ALL previous caps set]
		******************************************/
		$wppizzaCaps=$this->wppizza_set_capabilities();

		$capsAvailable=array();
		foreach($wppizzaCaps as $caps){
			$capsAvailable[]=$caps['cap'];
		}
		/**make an array with all unique roles**/
		$previousCaps=array();
		foreach($options['admin_access_caps'] as $rName=>$rVal){
			foreach($rVal as $cap){
				$previousCaps[$cap]=$cap;
			}
		}
		/**count number of prev caps**/
		$prevCapsCount=count($previousCaps);

		/**get newly added caps**/
		$newCaps=array_diff($capsAvailable,$previousCaps);

		/**if there are new caps add them**/
		if(is_array($newCaps) && count($newCaps)>0){
			/*get all roles that had ALL previous caps enabled and add this new one**/
			foreach($wp_roles->roles as $rName=>$rVal){
				$userRole = get_role($rName);
				$capsCount=0;
				foreach($previousCaps as $pCaps){
					if(isset($rVal['capabilities'][$pCaps])){
						$capsCount++;
					}
				}
				/***role has ALL previous caps, add new ones***/
				if($capsCount==$prevCapsCount){
					foreach($newCaps as $nCap){
						$userRole->add_cap($nCap);
						/***add to options too**/
						$options['admin_access_caps'][$rName][]=$nCap;
					}
				}
			}
		}
		$defaultOptions['admin_access_caps']=$options['admin_access_caps'];
	}



	/**apply filters to add options as required*/
	if(!empty($defaultOptions)){
		$defaultOptions=apply_filters('wppizza_filter_setup_default_options', $defaultOptions);
	}
?>