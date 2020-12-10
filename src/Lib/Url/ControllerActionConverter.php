<?php
namespace Sy\Bootstrap\Lib\Url;

class ControllerActionConverter implements IConverter {

	public function paramsToUrl(array $params) {
		if (empty($params[CONTROLLER_TRIGGER])) return null;
		$url = WEB_ROOT . '/' . $params[CONTROLLER_TRIGGER];
		unset($params[CONTROLLER_TRIGGER]);
		if (!empty($params[ACTION_TRIGGER])) {
			$url .= '/' . $params[ACTION_TRIGGER];
			unset($params[ACTION_TRIGGER]);
		}
		return $url . (empty($params) ? '' : '?' . http_build_query($params));
	}

	public function urlToParams($url) {
		list($uri) = explode('?', $url, 2);
		$path = substr($uri, strlen(WEB_ROOT) + 1);
		if (empty($path)) return false;
		$p = explode('/', $path);
		$_REQUEST[CONTROLLER_TRIGGER] = $p[0];
		$_GET[CONTROLLER_TRIGGER] = $p[0];
		if (!empty($p[1])) {
			$_REQUEST[ACTION_TRIGGER] = $p[1];
			$_GET[ACTION_TRIGGER] = $p[1];
		}
		return true;
	}

}