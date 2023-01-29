<?php
namespace Sy\Bootstrap\Lib\Url;

class ControllerActionConverter implements IConverter {

	/**
	 * Example:
	 * $params = [
	 *     CONTROLLER_TRIGGER => 'foo',
	 *     ACTION_TRIGGER => 'bar',
	 *     ACTION_PARAM => ['one', 'two'],
	 *     'other' => 'baz',
	 * ];
	 * Will return '/foo/bar/one/two?other=baz'
	 *
	 * {@inheritDoc}
	 */
	public function paramsToUrl(array $params) {
		if (empty($params[CONTROLLER_TRIGGER])) return false;
		$url = WEB_ROOT . '/' . $params[CONTROLLER_TRIGGER];
		unset($params[CONTROLLER_TRIGGER]);
		if (!empty($params[ACTION_TRIGGER])) {
			$url .= '/' . $params[ACTION_TRIGGER];
			unset($params[ACTION_TRIGGER]);
			if (isset($params[ACTION_PARAM])) {
				foreach ($params[ACTION_PARAM] as $p) {
					$url .= '/' . $p;
				}
				unset($params[ACTION_PARAM]);
			}
		}
		return $url . (empty($params) ? '' : '?' . http_build_query($params));
	}

	/**
	 * Example:
	 * $url = '/foo/bar/one/two?other=baz';
	 * Will return [
	 *     CONTROLLER_TRIGGER => 'foo',
	 *     ACTION_TRIGGER => 'bar',
	 *     ACTION_PARAM => ['one', 'two'],
	 *     'other' => 'baz',
	 * ];
	 *
	 * {@inheritDoc}
	 */
	public function urlToParams($url) {
		$url = trim($url);
		if (empty($url)) return false;
		list($uri, $queryString) = array_pad(explode('?', $url, 2), 2, null);
		$path = substr($uri, strlen(WEB_ROOT) + 1);
		if (empty($path)) return false;
		$p = explode('/', $path);
		$c = array_shift($p);
		$params[CONTROLLER_TRIGGER] = $c;
		$queryParams = [];
		if (!is_null($queryString)) parse_str($queryString, $queryParams);
		if (empty($p)) return $params + $queryParams;
		$a = array_shift($p);
		$params[ACTION_TRIGGER] = $a;
		if (empty($p)) return $params + $queryParams;
		foreach ($p as $v) {
			$params[ACTION_PARAM][] = $v;
		}
		return $params + $queryParams;
	}

}