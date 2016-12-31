<?php if ( ! defined( 'ABSPATH' ) ) exit;/*Exit if accessed directly*/ ?>
<?php

			$arrayIdent='templates';
			/***set depending on current tab set to switch bewteen print and emails etc**/
			$templateKey=(empty($_GET['tab']) || !in_array($_GET['tab'],array('emails','print'))) ? 'emails' : $_GET['tab'];

			/*************************************
			*
			* 	[get no of pages,offset etc
			*	and do pagination]
			*
			*************************************/
			$list=!empty($options['templates'][$templateKey]) ? $options['templates'][$templateKey] : array();
			
			
			if(isset($list) && is_array($list)){
				/**get parameter to use*****/
				$getParam='paged';
				/*maximum discounts per page**/
				$maxPerPage=!defined('WPPIZZA_MAX_TEMPLATESPP') ? 5 : WPPIZZA_MAX_TEMPLATESPP ;
				/**total number of messages**/
				$totalListCount=count($list);
				/**number of pages**/
				$totalPages=ceil($totalListCount/$maxPerPage);
				/*current page */
				$currentPage=1;
				if(isset($_GET[$getParam]) && (int)$_GET[$getParam]>1 && (int)$_GET[$getParam]<=$totalPages){
					$currentPage=(int)$_GET[$getParam];
				}
				/*current offset*/
				$offset=($currentPage-1)*$maxPerPage;

				/**onpage. quick and dirty*/
				if($currentPage==1){
					$onpage=$currentPage.'-'.$maxPerPage;
				}
				if($currentPage!=1 && $currentPage!=$totalPages){
					$onpage=(($currentPage-1)*$maxPerPage+1).'-'.($currentPage*$maxPerPage);
				}
				if($currentPage==$totalPages){
					$onpage=(($currentPage-1)*$maxPerPage+1).'-'.$totalListCount;
				}
				/**get the pagination***/
				$pagination=$this->wppizza_admin_pagination($currentPage, $totalPages, $getParam);
				/***************************************
					sort and slice
				***************************************/
				krsort($list);/*sort by key in reverse, chances are the last one added is the most useful*/
				$list=array_slice($list, $offset, $maxPerPage, true);
			}

			/**add new template**/
			echo"<span id='".$this->pluginSlug."-".$field."-add'>";
			echo "<a href='javascript:void(0)' id='".$this->pluginSlug."_add_".$field."_".$templateKey."' class='button ".$this->pluginSlug."_add_".$field."  ".$this->pluginSlug."-".$field."-add-button'>".__('add template', 'wppizza-locale')."</a>";
			echo"</span>";

			/***********************************
			*
			*	defaults
			*
			************************************/
			echo'<table class="wppizza-template-table widefat">';
				echo'<thead class="wppizza-template-thead">';
					echo'<tr>';
						echo'<th>';
							echo'<div>';
							/*if email , who does this apply to - irrelevant for print template*/
							if($templateKey=='emails'){
								/***default recipients available/required**/
								$recipients=wppizza_email_recipients();							
									echo'<label class="wppizza-template-recipients-default-label">'.__('standard template','wppizza-locale').': </label>';
									foreach($recipients as $recKey=>$recTitle){
										echo'<label class="wppizza-template-recipients"><input type="radio" id="'.$this->pluginSlug.'_'.$arrayIdent.'_'.$templateKey.'_recipients_default_'.$recKey.'" name="'.$this->pluginSlug.'['.$arrayIdent.']['.$templateKey.'][recipients_default]['.$recKey.']" '.checked($options['templates_apply'][$templateKey]['recipients_default'][$recKey],-1,false).' value="-1"/>'.$recTitle.'</label>';
									}
									/**additional recipients**/
									$recipients_additional=!empty($this->pluginOptions['templates_apply'][$templateKey]['recipients_additional'][-1]) ? implode(',',$this->pluginOptions['templates_apply'][$templateKey]['recipients_additional'][-1]) : '' ;
									echo'<label class="wppizza-template-recipients">'.__('additional recipients','wppizza-locale').' <span>'.__('(comma separated)','wppizza-locale').'</span><input type="text" name="'.$this->pluginSlug.'['.$arrayIdent.']['.$templateKey.'][recipients_additional][-1]" value="'.$recipients_additional.'" /></label>';
							
									/**mail delivery**/
									echo'<label class="wppizza-template-maildelivery-default-label">'.__('format','wppizza-locale').':';
										echo wppizza_admin_mail_delivery_options($this->pluginOptions);
									echo "</label>";
							
							}
							if($templateKey=='print'){
								/**is this the print template to use? */
								echo'<label class="wppizza-dashicons wppizza-dashicons-radio">'.__('use','wppizza-locale').' <input type="radio"  id="'.$this->pluginSlug.'_'.$arrayIdent.'_'.$templateKey.'_print_id_default"  name="'.$this->pluginSlug.'['.$arrayIdent.']['.$templateKey.'][print_id]" '.checked($options['templates_apply'][$templateKey],-1,false).' value="-1" /></label>';
								echo'<label class="wppizza-template-print-default-label">'.__('default template','wppizza-locale').'</label>';
							}
							echo'</div>';
						echo'</th>';
					echo'</tr>';
				echo'</thead>';
			echo'</table>';

			/***********************************
			*
			*	templates paginated
			*
			************************************/
			echo"<span id='".$this->pluginSlug."_list_".$field."'>";
			/**table**/
			echo'<table id="'.$this->pluginSlug.'_'.$field.'_table" class="widefat">';
				echo'<thead>';
					echo'<tr class="'.$this->pluginSlug.'-pagination">';
						echo'<th>'.$onpage.' '.__('of','wppizza-locale').' '.$totalListCount.'</th>';
						echo'<th>'.$pagination.'</th>';
					echo'</tr>';
				echo'</thead>';
				echo'<tfoot>';
					echo'<tr class="'.$this->pluginSlug.'-pagination">';
						echo'<th>'.$onpage.' '.__('of','wppizza-locale').' '.$totalListCount.'</th>';
						echo'<th>'.$pagination.'</td>';
					echo'</tr>';
				echo'</tfoot>';
				echo'<tbody>';
					echo'<tr>';
						echo'<td colspan="2">';
							/**loop**/
							foreach($list as $msgKey=>$msgVals){
								$tpl=$this->getTemplateMarkupAdmin($msgKey, $templateKey, $msgVals);  
								echo $tpl;
							}
						echo'</td>';
					echo'</tr>';
				echo'</tbody>';
			echo'</table>';
			echo"</span>";
?>