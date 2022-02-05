<?php
namespace Sy\Bootstrap\Lib\Url;

class AliasManager {

	/**
	 * @var array Lang => [Alias => Path]
	 */
	private static $alias = array();

	public static function setAliasFile($file) {
		if (!file_exists($file)) return;
		$data = include($file);
		if (!is_array($data)) return;
		self::$alias = $data;
	}

	public static function retrieveAlias($path, $lang) {
		if (empty(self::$alias[$lang])) return null;
		$alias = array_search($path, self::$alias[$lang]);
		return $alias === false ? null : $alias;
	}

	public static function retrievePath($alias) {
		$lang = \Sy\Translate\LangDetector::getInstance(LANG)->getLang();
		$path = isset(self::$alias[$lang]) ? self::$alias[$lang] : [];
		if (!array_key_exists($alias, $path)) {
			foreach (self::$alias as $l => $p) {
				if (array_key_exists($alias, $p)) {
					$lang = $l;
					$path = $p;
				}
			}
		}
		if (empty($path[$alias])) return null;
		$service = \Project\Service\Container::getInstance();
		$service->user->setLanguage($lang);
		return $path[$alias];
	}

}