<?php
namespace Sy\Bootstrap\Application\Sitemap;

class Page implements \Sy\Bootstrap\Application\Sitemap\IProvider {

	/**
	 * Returns sitemap index urls
	 *
	 * @return array An array of URL string
	 */
	public function getIndexUrls() {
		return [
			PROJECT_URL . \Sy\Bootstrap\Lib\Url::build('sitemap', 'page'),
		];
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
			$loc = \Sy\Bootstrap\Lib\Url\AliasManager::retrieveAlias('page/' . $row['id'], $row['lang']);
			if (is_null($loc)) return;

			$url['loc'] = PROJECT_URL . '/' . $loc;

			$alt = json_decode($row['alternate'], true);
			if (count($alt) > 1) {
				foreach ($alt as $lang) {
					$url['alternate'][$lang] = PROJECT_URL . '/' . \Sy\Bootstrap\Lib\Url\AliasManager::retrieveAlias('page/' . $row['id'], $lang);
				}
			}

			$urls[] = $url;
		}, [
			'SELECT'   => "t_page.*, CONCAT('[', GROUP_CONCAT(CONCAT('\"', b.lang, '\"')), ']') AS 'alternate'",
			'JOIN'     => 'LEFT JOIN t_page b ON t_page.id = b.id',
			'GROUP BY' => 't_page.id, t_page.lang',
		]);

		return $urls;
	}

}