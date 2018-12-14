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

	public function render() {

		$LatestWPPosts = $this->getPosts();

		$LatestPosts = $this->preparePosts( $LatestWPPosts );

		// dump($posts);

		$viewParams = [
			'title' => \XF::options()->JsBabyboomNewsTytul,
			'latestposts' => $LatestPosts,
		];

		return $this->renderer( 'widget_js_babyboom_news', $viewParams );
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
      $response = null;
      try {
          $response = $client->get($absoluteUrl);
      } catch (\Exception $e) {

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