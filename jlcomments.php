<?php
/**
 * JLcomments
 *
 * @version 1.0
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
				$doc = &JFactory::getDocument();
				$uri = JURI::getInstance();
				$base = $uri->toString(array('scheme', 'host', 'port'));
				$article_url = $base.JRoute::_(ContentHelperRoute::getArticleRoute($article->slug, $article->catid, $article->catslug));
				

				$apiId = $this->params->def('apiId');
				$width = $this->params->def('width');
				$comLimit = $this->params->def('comLimit');
				$attach = $this->params->def('attach');
				$autoPublish = $this->params->def('autoPublish');
				$norealtime = $this->params->def('norealtime');
				$fbId = $this->params->def('fbId');
				$fbadmin = $this->params->def('fbadmin');
				$fb_lang = $this->params->def('fb_lang');
				
				$script = "VK.init({apiId: $apiId, onlyWidgets: true});";
				$doc->addCustomTag('<meta property="fb:admins" content="'.$fbadmin.'"/>');
				$doc->addCustomTag('<meta property="fb:app_id" content="'.$fbId.'"/>');
				$doc->addScript("http://vkontakte.ru/js/api/openapi.js?22");
				$doc->addScript("/plugins/content/jlcomments/js/tabPane.js");
				$doc->addScript("/plugins/content/jlcomments/js/demo.js");
				$doc->addStyleSheet("/plugins/content/jlcomments/css/styles.css");
				$doc->addScriptDeclaration($script);
				$pagehash = $article->id;
				$orders = explode(",",$this->params->def('orders'));
			
				
				$scriptPage = <<<HTML

                    <span id="demo_small">
                    <ul class="tabs">
HTML;
	foreach ($orders as $order) {
				switch($order) {
					case 1:	if ($this->params->def('showjcomments')) { $scriptPage .= <<<HTML
									<li class="tab">jcomments</li>
HTML;
						} else {$scriptPage .='';$showjcomments='-1';} break;
					case 2:	if ($this->params->def('showvkontakte')) { $scriptPage .= <<<HTML
									<li class="tab">Вконтакте</li>
HTML;
						} else {$scriptPage .='';} break;
					case 3:	if ($this->params->def('showfacebook')) { $scriptPage .= <<<HTML
									<li class="tab">Facebook</li>
HTML;
						} else {$scriptPage .='';} break;
						}
						
					}
				
			$scriptPage .= <<<HTML
                    </ul>
HTML;
			foreach ($orders as $order) {		
			switch($order) {		
					case 1: if ($this->params->def('showjcomments')) { $scriptPage .= <<<HTML
								<div class="content">
									{jcomments}
								</div>
HTML;
						} else {$scriptPage .='';$showjcomments='-1';} break;
					case 2: if ($this->params->def('showvkontakte')) { $scriptPage .= <<<HTML
								<div class="content">
									<div id='jlcomments'></div>
									<script type='text/javascript'>
									  VK.Widgets.Comments('jlcomments', {limit: $comLimit, width: '$width', attach: '$attach', autoPublish: $autoPublish, norealtime: $norealtime},$pagehash);
									</script>
								</div>
HTML;
						} else {$scriptPage .='';} break;					  
					case 3: if ($this->params->def('showfacebook')) { $scriptPage .= <<<HTML
                    <div class="content">
                   		<script>(function(d){
						var js, id = 'facebook-jssdk'; if (d.getElementById(id)) {return;}
						js = d.createElement('script'); js.id = id; js.async = true;
						js.src = "//connect.facebook.net/$fb_lang/all.js#xfbml=1";
						d.getElementsByTagName('head')[0].appendChild(js);
						}(document));</script>
					<div class="fb-comments" data-href="$article_url" data-num-posts="$comLimit" data-width="$width"></div>
					</span>
					</div>
					  					    


HTML;
					} else {$scriptPage .='';} break;

}

}
				 $scriptPage .= <<<HTML
								<div style="text-align: right;">
									<a style="text-decoration:none; color: #c0c0c0; font-family: arial,helvetica,sans-serif; font-size: 5pt; " target="_blank" href="http://joomline.ru/rasshirenija/plugin/jlcomments.html">Социальные комментарии joomla</a>
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
