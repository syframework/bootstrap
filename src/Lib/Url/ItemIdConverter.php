<?php
namespace Sy\Bootstrap\Lib\Url;

class ItemIdConverter implements IConverter {

	/**
	 * @var string
	 */
	private $pageId;

	/**
	 * @var string
	 */
	private $urlId;

	/**
	 * Standard URL converter
	 * /page/[$pageId]?id=[ID] <=> /[$urlId]/[ID]
	 *
	 * @param string $pageId
	 * @param string $urlId
	 */
	public function __construct($pageId, $urlId = '') {
		$this->pageId = $pageId;
		$this->urlId = empty($urlId) ? $pageId : $urlId;
	}

	/**
	 * Example:
	 * $pageId = 'foo';
	 * $urlId = 'boo';
	 * $params = [
	 *     CONTROLLER_TRIGGER => 'page',
	 *     ACTION_TRIGGER => 'foo',
	 *     'id' => '123',
	 * ];
	 * Will return '/boo/123'
	 *
	 * {@inheritDoc}
	 */
	public function paramsToUrl(array $params) {
		if (empty($params[CONTROLLER_TRIGGER])) return false;
		if ($params[CONTROLLER_TRIGGER] !== 'page') return false;
		unset($params[CONTROLLER_TRIGGER]);

		if (empty($params[ACTION_TRIGGER])) return false;
		if ($params[ACTION_TRIGGER] !== $this->pageId) return false;
		unset($params[ACTION_TRIGGER]);

		if (empty($params['id'])) return false;
		$id = $params['id'];
		unset($params['id']);

		$url = WEB_ROOT . '/';
		$service = \Project\Service\Container::getInstance();
		if (!empty($params['lang']) and $service->lang->isAvailable($params['lang'])) {
			$url .= $params['lang'] . '/';
			unset($params['lang']);
		}

		return $url . $this->urlId . '/' . $id . (empty($params) ? '' : '?' . http_build_query($params));
	}

	/**
	 * Example:
	 * $pageId = 'foo';
	 * $urlId = 'boo';
	 * $url = '/boo/123';
	 * Will return [
	 *     CONTROLLER_TRIGGER => 'page',
	 *     ACTION_TRIGGER => 'foo',
	 *     'id' => '123',
	 * ];
	 *
	 * {@inheritDoc}
	 */
	public function urlToParams($url) {
		if (is_null($url)) return false;
		$url = trim($url);
		if (empty($url)) return false;
		$uri = parse_url($url, PHP_URL_PATH);
		$uri = substr($uri, strlen(WEB_ROOT) + 1);
		$queryString = parse_url($url, PHP_URL_QUERY);

		// Check if there is the lang parameter
		$parts = explode('/', $uri);
		$service = \Project\Service\Container::getInstance();
		if ($service->lang->isAvailable($parts[0])) {
			$params['lang'] = $parts[0];
			$uri = implode('/', array_slice($parts, 1));
		}

		list($id) = sscanf($uri, $this->urlId . '/%s');
		if (empty($id)) return false;

		$params[CONTROLLER_TRIGGER] = 'page';
		$params[ACTION_TRIGGER] = $this->pageId;
		$params['id'] = $id;

		$queryParams = [];
		if (!is_null($queryString)) parse_str($queryString, $queryParams);

		return $params + $queryParams;
	}

}