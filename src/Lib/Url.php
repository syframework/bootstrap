<?php
namespace Sy\Bootstrap\Lib;

class Url {

	/**
	 * @var Url\IConverter[]
	 */
	private static $converters = array();

	/**
	 * Analyse the request URI with converters.
	 * Fill $_REQUEST and $_GET with the first converter match.
	 */
	public static function analyse() {
		$params = self::convertToParams($_SERVER['REQUEST_URI']);
		if (!$params) return;
		foreach ($params as $k => $v) {
			$_REQUEST[$k] = $v;
			$_GET[$k] = $v;
		}
	}

	/**
	 * Try to match a converter pattern and return the parameters array
	 * Return false if no pattern match found
	 *
	 * @param  string $url
	 * @return array|false
	 */
	public static function convertToParams($url) {
		foreach (self::$converters as $converter) {
			$params = $converter->urlToParams($url);
			if (is_array($params)) return $params;
		}
		return false;
	}

	/**
	 * Try to match a converter pattern and return the URL
	 * Return false if no pattern match found
	 *
	 * @param  array $params
	 * @return string|false
	 */
	public static function convertToUrl(array $params) {
		foreach (self::$converters as $converter) {
			$url = $converter->paramsToUrl($params);
			if (is_string($url)) return $url;
		}
		return false;
	}

	/**
	 * Add a converter in the converter array
	 *
	 * @param Url\IConverter $converter
	 */
	public static function addConverter(Url\IConverter $converter) {
		self::$converters[] = $converter;
	}

	/**
	 * Build an URL. See examples: https://syframework.alwaysdata.net/url-build
	 *
	 * @param  string $controller Controller name.
	 * @param  string|array $action Action name. Can be a string like 'a/b/c' or an array like ['a', 'b', 'c']
	 * @param  array $parameters Associative array representing URL parameters
	 * @param  string $anchor URL anchor.
	 * @return string
	 */
	public static function build($controller, $action = null, array $parameters = array(), $anchor = null) {
		$params = [];
		$params[CONTROLLER_TRIGGER] = $controller;
		if (!is_null($action)) {
			$params[ACTION_TRIGGER] = is_array($action) ? implode('/', $action) : $action;
		}

		// Lang parameter
		if ($controller === 'page' and empty($parameters['lang'])) {
			$service = \Project\Service\Container::getInstance();
			$params['lang'] = $service->lang->getLang();
		}

		$url = self::convertToUrl($params + $parameters);
		if (is_string($url)) return $url . (empty($anchor) ? '' : "#$anchor");
		if (!is_null($action)) {
			$action = is_array($action) ? $action : explode('/', $action);
			$a = array_shift($action);
			$params[ACTION_TRIGGER] = $a;
			$params[ACTION_PARAM] = $action;
		}
		return $_SERVER['PHP_SELF'] . '?' . http_build_query($params + $parameters) . (empty($anchor) ? '' : "#$anchor");
	}

	/**
	 * Create a referer in session
	 *
	 * @param string $referer
	 */
	public static function setReferer($referer) {
		if (!session_id()) session_start();
		$_SESSION['http_referer'] = $referer;
	}

	/**
	 * Retrive the referer from session
	 *
	 * @return string
	 */
	public static function getReferer() {
		if (!session_id()) session_start();
		$referer = isset($_SESSION['http_referer']) ? $_SESSION['http_referer'] : null;
		unset($_SESSION['http_referer']);
		return $referer;
	}

	/**
	 * Get the avatar url
	 *
	 * @param string $email the user email
	 */
	public static function avatar($email) {
		if (empty($email)) return 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgZmlsbD0iY3VycmVudENvbG9yIiBjbGFzcz0iYmkgYmktcGVyc29uLWNpcmNsZSIgdmlld0JveD0iMCAwIDE2IDE2Ij4KICA8cGF0aCBkPSJNMTEgNmEzIDMgMCAxIDEtNiAwIDMgMyAwIDAgMSA2IDB6Ii8+CiAgPHBhdGggZmlsbC1ydWxlPSJldmVub2RkIiBkPSJNMCA4YTggOCAwIDEgMSAxNiAwQTggOCAwIDAgMSAwIDh6bTgtN2E3IDcgMCAwIDAtNS40NjggMTEuMzdDMy4yNDIgMTEuMjI2IDQuODA1IDEwIDggMTBzNC43NTcgMS4yMjUgNS40NjggMi4zN0E3IDcgMCAwIDAgOCAxeiIvPgo8L3N2Zz4=';
		$md5 = md5(strtolower(trim($email)));
		if (file_exists(AVATAR_DIR . "/$md5.webp")) {
			return PROJECT_URL . AVATAR_ROOT . "/$md5.webp";
		} else {
			// TODO: libravatar federated servers
			return "https://seccdn.libravatar.org/avatar/$md5?d=" . urlencode("https://api.dicebear.com/8.x/avataaars/svg?seed=$md5");
		}
	}

}