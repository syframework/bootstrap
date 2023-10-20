<?php
namespace Sy\Bootstrap\Lib\Url;

class AliasConverter implements IConverter {

	/**
	 * @var string
	 */
	private $lang;

	/**
	 * @param string|null $lang
	 */
	public function __construct($lang = null) {
		$this->lang = $lang;
	}

	/**
	 * Example:
	 * 'en' => [
	 *     'my-alias' => 'page/foo',
	 * ];
	 * $params = [
	 *     CONTROLLER_TRIGGER => 'page',
	 *     ACTION_TRIGGER => 'foo',
	 * ];
	 * Will return '/my-alias'
	 *
	 * {@inheritDoc}
	 */
	public function paramsToUrl(array $params) {
		if (empty($params[CONTROLLER_TRIGGER])) return false;
		$controller = $params[CONTROLLER_TRIGGER];
		unset($params[CONTROLLER_TRIGGER]);
		if (!empty($params[ACTION_TRIGGER])) {
			$action = $params[ACTION_TRIGGER];
			unset($params[ACTION_TRIGGER]);
		}
		$lang = $params['lang'] ?? $this->getLang();
		unset($params['lang']);
		$query = http_build_query($params);
		$path = "$controller" . (isset($action) ? "/$action" : '');
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
		if (is_null($url)) return false;
		return WEB_ROOT . '/' . $url . (empty($query) ? '' : '?' . $query);
	}

	/**
	 * Example:
	 * 'en' => [
	 *     'my-alias' => 'page/foo',
	 * ];
	 * $url = '/my-alias';
	 * Will return [
	 *     CONTROLLER_TRIGGER => 'page',
	 *     ACTION_TRIGGER => 'foo',
	 * ];
	 *
	 * {@inheritDoc}
	 */
	public function urlToParams($url) {
		if (is_null($url)) return false;
		$uri = parse_url($url, PHP_URL_PATH);
		$queryString = parse_url($url, PHP_URL_QUERY);
		$alias = trim(substr($uri, strlen(WEB_ROOT) + 1), '/');
		if (empty($alias)) return false;
		list($path, $lang) = AliasManager::retrievePath($alias);
		if (empty($path)) return false;
		$r = explode('?', $path, 2);

		$p = explode('/', $r[0]);
		$params[CONTROLLER_TRIGGER] = $p[0];
		if (!empty($p[1])) {
			$params[ACTION_TRIGGER] = $p[1];
		}

		if (!empty($r[1])) {
			parse_str($r[1], $output);
			foreach ($output as $key => $value) {
				$params[$key] = $value;
			}
		}

		$params['lang'] = $lang;
		$this->lang = $lang;

		$queryParams = [];
		if (!is_null($queryString)) parse_str($queryString, $queryParams);

		return $params + $queryParams;
	}

	private function getLang() {
		if (empty($this->lang)) {
			$service = \Project\Service\Container::getInstance();
			$this->lang = $service->lang->getLang();
		}
		return $this->lang;
	}

}