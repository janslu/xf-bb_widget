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
		'limit' => 50,
		'staffOnline' => true,
		'staffQuery' => false,
		'followedOnline' => true
   ];
   
	public function render() {
      $limit = $this->options['limit'];
        $key = $this->widgetConfig->widgetKey.''.$this->widgetConfig->widgetId;
        if ($cache = \XF::app()->cache()) {
            $content = $cache->fetch($key);
            if ($content) {
                \XF::dump('mam kontent');
                $content = $content.'<-- z cache -->';
            } else {
                \XF::dump('nie mam kontentu');
                $absoluteUrl = \XF::options()->JsBabyboomNewsUrl;
                $client = $this->app->http()->client();
                try {
                    $content = $client->get($absoluteUrl)->getBody()->getContents();
                    if ($cache) {
                        $expiryInSeconds = 10;
                        $cache->save($key, $content, $expiryInSeconds);
                    }
                    $content = $content.'<-- do cache -->';
                } catch(\GuzzleHttp\Exception\RequestException $e) {
                    \XF::dump($e);
                }
            }
        } else {
            $content = '';
        }

        // return $data;
        //\XF::dump($content);
		//$Content = $this->getPosts();
      	
		$viewParams = [
			'title' => \XF::options()->JsBabyboomNewsTytul,
            'widgetConfig' => $this->widgetConfig,
			'newsy' => $content,
		];
		\XF::dump($viewParams);
		return $this->renderer( 'widget_js_babyboom_news', $viewParams );
	}

   public function verifyOptions(\XF\Http\Request $request, array &$options, &$error = null)
	{
		$options = $request->filter([
			'limit' => 'uint',
			'staffOnline' => 'bool',
			'staffQuery' => 'bool',
			'followedOnline' => 'bool',
		]);
      return true;
   }

	public function getOptionsTemplate()
	{
		return null;
	}

	/**
	 * @return array|false
	 */
	public function getPosts() {

		$absoluteUrl = \XF::options()->JsBabyboomNewsUrl;
      $client = $this->app->http()->client();
      try {
          $response = $client->get($absoluteUrl)->getBody()->getContents();
      } catch(\GuzzleHttp\Exception\RequestException $e) {
		\XF::dump($e);
      }
       return $response;
	}

	/**
	 * @param array $LatestWPPosts
	 *
	 * @return array
	 */
	public function preparePosts( array $LatestWPPosts ) {
		$LatestPosts = array();
		foreach ( $LatestWPPosts as $LatestWPPost ) {
			$LatestPostTitle     = $LatestWPPost['post_title'];
			$LatestPostAuthorId  = $LatestWPPost['post_author'];
			$LatestPostAuthor    = \XF::app()->find('XF:User', $LatestPostAuthorId);
			$LatestPostAuthorUsername    = \XF::app()->find('XF:User', $LatestPostAuthorId)->username;
			$LatestPostDate      = $LatestWPPost['post_date_gmt'];

			// dump($LatestPostAuthor);

			// $LatestPostUser = \XF::app()->em()->find('XF:User', $LatestPostAuthorId);

			$LatestPostUrl       = esc_url( get_permalink( $LatestWPPost['ID'] ) );
			$LatestPostExcerpt   = $LatestWPPost['post_excerpt'];
			$LatestPostThumbnail = get_the_post_thumbnail_url( $LatestWPPost['ID'], array( 50, 50 ) );
			$LatestPosts[]       = array( 'postTitle'        => \XF::app()->stringFormatter()->wholeWordTrim($LatestPostTitle, '55' ),
			                              'postAuthor'       => $LatestPostAuthorUsername,
			                              'postAuthorUser'   => $LatestPostAuthor,
			                              'postDate'         => strtotime( $LatestPostDate ),
			                              'postUrl'          => $LatestPostUrl,
			                              'postExcerpt'      => $LatestPostExcerpt,
			                              'postThumbnailUrl' => $LatestPostThumbnail
			);
		}

		return $LatestPosts;
	}
}