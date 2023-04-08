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
			$alias = [];
			foreach (LANGS as $lang => $label) {
				$loc = \Sy\Bootstrap\Lib\Url\AliasManager::retrieveAlias('page/' . $row['id'], $lang);
				if (is_null($loc)) continue;
				$alias[$lang] = PROJECT_URL . '/' . $loc;
			}

			if (count($alias) > 1) {
				foreach ($alias as $lang => $loc) {
					$urls[] = ['loc' => $loc, 'alternate' => $alias];
				}
			} elseif (count($alias) === 1) {
				$urls[] = ['loc' => array_pop($alias)];
			}
		});

		return $urls;
	}

}