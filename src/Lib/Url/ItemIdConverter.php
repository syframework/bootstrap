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

		return WEB_ROOT . '/' . $this->urlId . '/' . $id . (empty($params) ? '' : '?' . http_build_query($params));
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
		$url = trim($url);
		if (empty($url)) return false;
		list($uri, $queryString) = array_pad(explode('?', $url, 2), 2, null);
		list($id) = sscanf(substr($uri, strlen(WEB_ROOT) + 1), $this->urlId . '/%s');
		if (empty($id)) return false;

		$params[CONTROLLER_TRIGGER] = 'page';
		$params[ACTION_TRIGGER] = $this->pageId;
		$params['id'] = $id;

		$queryParams = [];
		if (!is_null($queryString)) parse_str($queryString, $queryParams);

		return $params + $queryParams;
	}

}