<?php

/**
 * Filename: LatestWordPressPost.php
 * User: LPH
 * Date: 7/7/18
 * Time: 9:30 PM
 */

namespace JS\BabyBoom\XF\Widget;

use XF\Widget\AbstractWidget;

class NewsyBabyboom extends AbstractWidget {
	protected $defaultOptions = [
      'cachetimeout' => 0,
      'url' => null,
      'wrapbody' => true
   ];
   
	public function render() {
      $cachetimeout = $this->options['cachetimeout'];
      $title = null;
      $key = $this->widgetConfig->widgetKey.''.$this->widgetConfig->widgetId;
      if ($cache = \XF::app()->cache()) {
         $content = $cache->fetch($key);
         if ($content) {
               $content = $content.'<-- z cache -->';
         } else {
               $absoluteUrl = $this->options['url'];
               $client = $this->app->http()->client();
               try {
                  $content = $client->get($absoluteUrl)->getBody()->getContents();
                  if ($cache) {
                     $cache->save($key, $content, $cachetimeout);
                  }
                  $content = $content.'<-- do cache -->';
               } catch(\GuzzleHttp\Exception\RequestException $e) {
                  \XF::dump($e);
               }
         }
      } else {
         $content = '';
      }

		$viewParams = [
			'title' => $this->getTitle() ?: $title,
         'wrapbody' => $this->options['wrapbody'],
			'newsy' => $content,
      ];
      //\XF::dump($viewParams);
		return $this->renderer( 'widget_js_babyboom_news', $viewParams );
	}

   public function verifyOptions(\XF\Http\Request $request, array &$options, &$error = null)
	{
		$options = $request->filter([
         'cachetimeout' => 'uint',
         'url' => 'string',
         'wrapbody' => 'bool',
		]);
      return true;
   }
}