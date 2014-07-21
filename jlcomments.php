<?php
/**
 * JLcomments
 *
 * @version 2.1
 * @author Kunitsyn Vadim (vadim@joomline.ru), Artem Jukov (artem@joomline.ru)
 * @copyright (C) 2011 by Kunitsyn Vadim(http://www.joomline.ru)
 * @license GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 **/

// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

class plgContentJlcomments extends JPlugin
{

	public function onContentPrepare($context, &$article, &$params, $page = 0){
		if($context == 'com_content.article'){
		if (strpos($article->text, '{jlcomments-off}') !== false) {
			$article->text = str_replace("{jlcomments-off}","",$article->text);
			return true;
		}

		if (strpos($article->text, '{jlcomments}') === false && !$this->params->def('autoAdd')) {
			return true;
		}

		$exceptcat = is_array($this->params->def('categories')) ? $this->params->def('categories') : array($this->params->def('categories'));
		if (!in_array($article->catid,$exceptcat)) {
			$view = JRequest::getCmd('view');
			if ($view == 'article') {
				$doc = JFactory::getDocument();
				$uri = JURI::getInstance();
				$base = $uri->toString(array('scheme', 'host', 'port'));
				$article_url = $base.JRoute::_(ContentHelperRoute::getArticleRoute($article->slug, $article->catid, $article->catslug));
				

				$apiId 			= $this->params->def('apiId');
				$width 			= $this->params->def('width');
				$comLimit 		= $this->params->def('comLimit');
				$attach 		= $this->params->def('attach');
				$autoPublish	= $this->params->def('autoPublish');
				$norealtime 	= $this->params->def('norealtime');
				$fbId 			= $this->params->def('fbId');
				$fbadmin 		= $this->params->def('fbadmin');
				$fb_lang 		= $this->params->def('fb_lang');
				$typeviewerjq 	= $this->params->def('typeviewerjq');
				$typeviewerbs 	= $this->params->def('typeviewerbs');
				$typeviewernojq = $this->params->def('typeviewernojq');
				$typeviewercss	= $this->params->def('typeviewercss');
				$link 			= $this->params->def('link');
				$colorscheme 	= $this->params->def('colorscheme');
				$order_by_fb	= $this->params->def('orderbyfb');
				$linknone		= '';
				$lang = JFactory::getLanguage();
				$lang->load('plg_content_jlcomments', JPATH_ADMINISTRATOR);
				$jlcomments_comments = JText::_('PLG_JLCOMMENTS_COMMENTS');
				$jlcomments_vk = JText::_('PLG_JLCOMMENTS_VK');
				$jlcomments_fb = JText::_('PLG_JLCOMMENTS_FB');
				$jlcomments_google = JText::_('PLG_JLCOMMENTS_GOOGLE');
				
				$script = "VK.init({apiId: $apiId, onlyWidgets: true});";
				$doc->addCustomTag('<meta property="fb:admins" content="'.$fbadmin.'"/>');
				$doc->addCustomTag('<meta property="fb:app_id" content="'.$fbId.'"/>');
				$doc->addScript("//vk.com/js/api/openapi.js?95");
				$doc->addCustomTag('<script src="https://apis.google.com/js/plusone.js" type="text/javascript">{lang: "'.$fb_lang.'"}</script>');
				$doc->addScriptDeclaration($script);
				$pagehash = $article->id;
				$orders = explode(",",$this->params->def('orders'));
				if (($this->params->def('googleid')!='')&&($this->params->def('googleplus'))){
					$doc->addCustomTag('<link rel="author" href="https://plus.google.com/'. $this->params->def('googleid') .'/">');
				}
				else {}
				
				if ($link==0){
					$linknone = 'display:none;';
					}
				else {}
				
				If ($typeviewerjq==1) {
					$doc->addCustomTag('<script src="http://yandex.st/jquery/1.9.1/jquery.min.js"></script>');
					}
				If ($typeviewercss==1) {
					$doc->addStyleSheet("/plugins/content/jlcomments/css/jlcomtabs.css");
					}
				If ($typeviewerbs==1) {
					$doc->addCustomTag('<script src="http://yandex.st/bootstrap/2.3.0/js/bootstrap.min.js"></script>');
					}
				If ($typeviewernojq==1) {
					$doc->addCustomTag ('<script type="text/javascript">var jqjlcomm = jQuery.noConflict();</script>');
					}
				else {}
				
                $comments = JPATH_SITE . '/components/com_jcomments/jcomments.php';
                  if (file_exists($comments)) {
                    require_once($comments);
                    $options['object_id'] = $pagehash;
                    $options['object_group'] = 'com_content';
                    $options['published'] = 1;
                    $count = JCommentsModel::getCommentsCount($options);
                  }
				
	$scriptPage = <<<HTML
		<br clear="all"><div id="jlcomments_container"><ul class="nav nav-tabs" id="plgjlcomments1">
HTML;



	foreach ($orders as $order) {
				switch($order) {
					case 1:	if ($this->params->def('showjcomments')) { $scriptPage .= <<<HTML
						<li style="list-style-type: none;" class="active"><a href="#jcommentscomm" data-toggle="tab"><i class="jlico-comments"></i> $jlcomments_comments ($count)</a></li>
HTML;
						} else {$scriptPage .='';$showjcomments='-1';} break;
					case 2:	if ($this->params->def('showvkontakte')) { $scriptPage .= <<<HTML
										<script type="text/javascript">
										jQuery(document).ready(function(){
											jQuery.ajax({
												url: "https://api.vk.com/method/widgets.getComments.json?widget_api_id=$apiId&page_id=$pagehash",
												dataType: 'jsonp',
												success: function(data){
													jQuery('#vk_count').text(data.response.count); 
												},
												error: function(data){
													console.log(data);
												}
											}); 
										});
										</script>
									<li style="list-style-type: none;"><a href="#vkcomm" data-toggle="tab"><i class="jlico-vk"></i> $jlcomments_vk (<span id="vk_count"></span>)</a></li>
HTML;
						} else {$scriptPage .='';} break;
					case 3:	if ($this->params->def('googleplus')) {
						
						$scriptPage .= <<<HTML
						
						<li style="list-style-type: none;"><a href="#googlecomm" data-toggle="tab"><i class="jlico-google"></i> $jlcomments_google</a></li>

HTML;
						} else {$scriptPage .='';} break;
					case 4:	if ($this->params->def('showfacebook')) { $scriptPage .= <<<HTML
					<style>
					.fb-comments, .fb-comments * {width:{$width}px !important;}
					</style>
				
						<li style="list-style-type: none;"><a href="#fbcomm" data-toggle="tab"><div><i class="jlico-facebook"></i> $jlcomments_fb (<fb:comments-count $article_url/> </fb:comments-count>)</div></a></li>
HTML;
						} else {$scriptPage .='';} break;


						}
						
					}
				
	$scriptPage .= <<<HTML
        </ul>
		<div class="tab-content">
HTML;
	foreach ($orders as $order) {		
			switch($order) {		
					case 1: if ($this->params->def('showjcomments')) { $scriptPage .= <<<HTML
								<div class="tab-pane active" id="jcommentscomm">
									{jcomments}
								</div>
HTML;
						} else {$scriptPage .='';$showjcomments='-1';} break;
					case 2: if ($this->params->def('showvkontakte')) { $scriptPage .= <<<HTML
														
								<div class="tab-pane" id="vkcomm">
									<div id='jlcomments'></div>
									<script type='text/javascript'>
									  VK.Widgets.Comments('jlcomments', {limit: $comLimit, width: '$width', attach: '$attach', autoPublish: $autoPublish, norealtime: $norealtime},$pagehash);
									</script>
								</div>
								
HTML;
						} else {$scriptPage .='';} break;	
					case 3: if ($this->params->def('googleplus')) { 
					$width_ = $width.'px';
					$scriptPage .= <<<HTML
					<script type="text/javascript">
						jQuery(function($){
							$('a[data-target="#googlecomm"]').click(function(){
								$('div#___comments_0').css('width', '$width_' );
								$('div#___comments_0').css('height', '100%' );
							});							
						});
					</script>
					<style>
						div#___comments_0 * {width:$width_ !important;min-height:600px !important;}
					</style>
					
					<div class="tab-pane" id="googlecomm">		
		
						<div class="g-comments"
							data-href="$article_url"
							data-width="$width"
							data-first_party_property="BLOGGER"
							data-view_type="FILTERED_POSTMOD">
						</div>
						</div>
HTML;
						} else {$scriptPage .='';} break;						
					case 4: if ($this->params->def('showfacebook')) { $scriptPage .= <<<HTML
                    <div class="tab-pane" id="fbcomm">
                   		<script>(function(d){
						var js, id = 'facebook-jssdk'; if (d.getElementById(id)) {return;}
						js = d.createElement('script'); js.id = id; js.async = true;
						js.src = "//connect.facebook.net/$fb_lang/all.js#xfbml=1";
						d.getElementsByTagName('head')[0].appendChild(js);
						}(document));</script>
					<div class="fb-comments" data-href="$article_url" data-num-posts="$comLimit" data-width="$width" data-colorscheme="$colorscheme" data-order-by="$order_by_fb"></div>
					
					</div>	
				
HTML;
					} else {$scriptPage .='';} break;

}

}
				 $scriptPage .= <<<HTML
				 </div>	
				 </div>
<script type="text/javascript">
jQuery(document).ready(function(){
jQuery('#plgjlcomments1 a:first').tab('show');
});
</script>
					<div style="text-align: right; $linknone;">
						<a style="text-decoration:none; color: #c0c0c0; font-family: arial,helvetica,sans-serif; font-size: 5pt; " target="_blank" href="http://www.38i.ru">www.38i.ru</a>
					</div>
HTML;

				if ($this->params->def('autoAdd') == 1) {
					$article->text .= $scriptPage;
				} else {
					$article->text = str_replace("{jlcomments}",$scriptPage,$article->text);

				}

			}
		} else {
			$article->text = str_replace("{jlcomments}","",$article->text);
		}

	}
 }
}
