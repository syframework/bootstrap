<?php
namespace Sy\Bootstrap\Lib\Url;

class ItemIdConverter implements IConverter {

	private $pageId;

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

	public function paramsToUrl(array $params) {
		if (empty($params[CONTROLLER_TRIGGER])) return null;
		if ($params[CONTROLLER_TRIGGER] !== 'page') return null;
		unset($params[CONTROLLER_TRIGGER]);

		if (empty($params[ACTION_TRIGGER])) return null;
		if ($params[ACTION_TRIGGER] !== $this->pageId) return null;
		unset($params[ACTION_TRIGGER]);

		if (empty($params['id'])) return null;
		$id = $params['id'];
		unset($params['id']);

		return WEB_ROOT . '/' . $this->urlId . '/' . $id . (empty($params) ? '' : '?' . http_build_query($params));
	}

	public function urlToParams($url) {
		list($uri) = explode('?', $url, 2);
		list($id) = sscanf(substr($uri, strlen(WEB_ROOT) + 1), $this->urlId . '/%s');
		if (empty($id)) return false;

		$_REQUEST[CONTROLLER_TRIGGER] = 'page';
		$_GET[CONTROLLER_TRIGGER] = 'page';
		$_REQUEST[ACTION_TRIGGER] = $this->pageId;
		$_GET[ACTION_TRIGGER] = $this->pageId;
		$_REQUEST['id'] = $id;
		$_GET['id'] = $id;
		return true;
	}

}