<?php
namespace Sy\Bootstrap\Application\Sitemap;

interface IProvider {

	/**
	 * Returns sitemap index urls
	 *
	 * @return array An array of URL string
	 */
	public function getIndexUrls();

	/**
	 * Returns sitemap urls
	 *
	 * Example:
	 * [
	 *     [
	 *         'loc' => 'https://url'
	 *         'alternate' => ['en' => 'https://url', 'fr' => 'https://url'], // (Optional)
	 *         'lastmod' => 'YYYY-MM-DD' // (Optional)
	 *     ],
	 *     [
	 *         ...
	 *     ],
	 *     ...
	 * ]
	 *
	 * @return array
	 */
	public function getUrls();

}