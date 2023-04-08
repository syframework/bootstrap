<?php
namespace Sy\Bootstrap\Application\Sitemap;

use Sy\Bootstrap\Lib\Url;

class Page implements \Sy\Bootstrap\Application\Sitemap\IProvider {

	/**
	 * Returns sitemap index urls
	 *
	 * @return array An array of URL string
	 */
	public function getIndexUrls() {
		return [PROJECT_URL . Url::build('sitemap', 'page')];
	}

	/**
	 * Returns sitemap urls
	 *
	 * @return array
	 */
	public function getUrls() {
		$urls = [];

		// Pages with alias
		$service = \Project\Service\Container::getInstance();
		$service->page->foreachRow(function($row) use(&$urls) {
			if (count(LANGS) > 1) {
				foreach (LANGS as $lang => $label) {
					$loc = \Sy\Bootstrap\Lib\Url\AliasManager::retrieveAlias('page/' . $row['id'], $lang);
					if (is_null($loc)) {
						continue;
					}

					$url['loc'] = PROJECT_URL . '/' . $loc;
					foreach (LANGS as $lang => $label) {
						$url['alternate'][$lang] = PROJECT_URL . '/' . \Sy\Bootstrap\Lib\Url\AliasManager::retrieveAlias('page/' . $row['id'], $lang);
					}
					$urls[] = $url;
				}
			} else {
				$url['loc'] = PROJECT_URL . Url::build('page', $row['id']);
				$urls[] = $url;
			}
		});

		return $urls;
	}

}