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
      //cache in minutes
      $cachetimeout = $this->options['cachetimeout'] * 60;

      //if title is not set - we don't render h3 wrapper in template
      $title = null;

      //key is being used for cache purposes
      $key = $this->widgetConfig->widgetKey.''.$this->widgetConfig->widgetId;

      if ($cache = \XF::app()->cache()) {
         $content = $cache->fetch($key);
         if (!$content) {
               //url from settings
               $absoluteUrl = $this->options['url'];

               //gazzle client provided by XF
               $client = $this->app->http()->client();
               try {
                  $content = $client->get($absoluteUrl)->getBody()->getContents();
                  if ($cache) {
                     $cache->save($key, $content, $cachetimeout);
                  }
                  $content = $content.'<-- do cache -->';
               } catch(\GuzzleHttp\Exception\RequestException $e) {
                  //\XF::dump($e);
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