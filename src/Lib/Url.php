<?php
namespace Sy\Bootstrap\Lib;

class Url {

	private static $converters = array();

	public static function analyse() {
		foreach (self::$converters as $converter) {
			if ($converter->urlToParams($_SERVER['REQUEST_URI'])) return;
		}
	}

	public static function addConverter(Url\IConverter $converter) {
		self::$converters[] = $converter;
	}

	/**
	 * Build an URL. See examples: https://syframework.alwaysdata.net/url-build
	 *
	 * @param string $controller Controller name.
	 * @param string|array $action Action name. Can be a string like 'a/b/c' or an array like ['a', 'b', 'c']
	 * @param array $parameters Associative array representing URL parameters
	 * @param string $anchor URL anchor.
	 * @return string
	 */
	public static function build($controller, $action = null, array $parameters = array(), $anchor = null) {
		$params = $parameters;
		$params[CONTROLLER_TRIGGER] = $controller;
		if (!is_null($action)) {
			$params[ACTION_TRIGGER] = is_array($action) ? implode('/', $action) : $action;
		}
		foreach (self::$converters as $converter) {
			$url = $converter->paramsToUrl($params);
			if (!is_null($url)) return $url . (empty($anchor) ? '' : "#$anchor");
		}
		if (!is_null($action)) {
			$action = is_array($action) ? $action : explode('/', $action);
			$a = array_shift($action);
			$params[ACTION_TRIGGER] = $a;
			$params[ACTION_PARAM] = $action;
		}
		return $_SERVER['PHP_SELF'] . (empty($params) ? '' : '?' . http_build_query($params)) . (empty($anchor) ? '' : "#$anchor");
	}

	public static function setReferer($referer) {
		if (!session_id()) session_start();
		$_SESSION['http_referer'] = $referer;
	}

	public static function getReferer() {
		if (!session_id()) session_start();
		$referer = isset($_SESSION['http_referer']) ? $_SESSION['http_referer'] : null;
		unset($_SESSION['http_referer']);
		return $referer;
	}

	/**
	 * Get the avatar url
	 *
	 * @param int $id the user id
	 */
	public static function avatar($id) {
		if (file_exists(AVATAR_DIR . "/$id.png")) {
			return PROJECT_URL . AVATAR_ROOT . "/$id.png";
		} else {
			$n = count(glob(DEFAULT_AVATAR_DIR . '/*.svg'));
			return DEFAULT_AVATAR_ROOT . '/' . (($id % $n) + 1) . '.svg';
		}
	}

	public static function adfly($url, $type = 'int') {
		if (!defined('ADFLY_KEY') or !defined('ADFLY_UID') or !defined('ADFLY_DOMAIN')) return $url;
		return file_get_contents('http://api.adf.ly/api.php?key=' . ADFLY_KEY . '&uid=' . ADFLY_UID . '&advert_type=' . $type . '&domain=' . ADFLY_DOMAIN . '&url=' . urlencode(trim($url, '/')));
	}

}