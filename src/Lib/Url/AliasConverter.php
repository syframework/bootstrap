<?php
namespace Sy\Bootstrap\Lib\Url;

class AliasConverter implements IConverter {

	public function paramsToUrl(array $params) {
		if (empty($params[CONTROLLER_TRIGGER])) return null;
		$controller = $params[CONTROLLER_TRIGGER];
		unset($params[CONTROLLER_TRIGGER]);
		if (!empty($params[ACTION_TRIGGER])) {
			$action = $params[ACTION_TRIGGER];
			unset($params[ACTION_TRIGGER]);
		}
		$query = http_build_query($params);
		$path = "$controller";
		$lang = \Sy\Translate\LangDetector::getInstance(LANG)->getLang();
		if (isset($action)) $path .= "/$action";
		if (empty($query)) {
			$url = AliasManager::retrieveAlias($path, $lang);
		} else {
			$url = AliasManager::retrieveAlias("$path?$query", $lang);
			if (is_null($url)) {
				$url = AliasManager::retrieveAlias($path, $lang);
			} else {
				$query = '';
			}
		}
		if (is_null($url)) return null;
		return WEB_ROOT . '/' . $url . (empty($query) ? '' : '?' . $query);
	}

	public function urlToParams($url) {
		list($uri) = explode('?', $url, 2);
		$alias = trim(substr($uri, strlen(WEB_ROOT) + 1), '/');
		if (empty($alias)) return false;
		$path = AliasManager::retrievePath($alias);
		if (empty($path)) return false;
		$r = explode('?', $path, 2);
		if (!empty($r[1])) {
			parse_str($r[1], $output);
			foreach ($output as $key => $value) {
				$_REQUEST[$key] = $value;
				$_GET[$key] = $value;
			}
		}
		$p = explode('/', $r[0]);
		$_REQUEST[CONTROLLER_TRIGGER] = $p[0];
		$_GET[CONTROLLER_TRIGGER] = $p[0];
		if (!empty($p[1])) {
			$_REQUEST[ACTION_TRIGGER] = $p[1];
			$_GET[ACTION_TRIGGER] = $p[1];
		}
		return true;
	}

}