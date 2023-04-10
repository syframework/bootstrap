<?php
namespace Sy\Bootstrap\Lib\Url;

/**
 * Alias file example:
 * return [
 *     'en' => [
 *         'first/alias/in/english'  => 'first/realpath',
 *         'second/alias/in/english' => 'second/realpath',
 *     ],
 *     'fr' => [
 *         'first/alias/in/french'  => 'first/realpath',
 *         'second/alias/in/french' => 'second/realpath',
 *     ],
 * ];
 */
class AliasManager {

	/**
	 * @var array Lang => [Alias => Path]
	 */
	private static $alias = array();

	/**
	 * @param string $file Alias file
	 */
	public static function setAliasFile($file) {
		if (!file_exists($file)) return;
		$data = include($file);
		if (!is_array($data)) return;
		self::$alias = $data;
	}

	/**
	 * Find an alias using its path and language
	 * Return null if nothing found
	 *
	 * @param  string $path
	 * @param  string $lang
	 * @return string|null
	 */
	public static function retrieveAlias($path, $lang) {
		if (empty(self::$alias[$lang])) return null;
		$alias = array_search($path, self::$alias[$lang]);
		return $alias === false ? null : $alias;
	}

	/**
	 * Find a path and language using its alias
	 * Return an array like ['path', 'lang']
	 * Return [null, null] if alias is not found
	 *
	 * @param  string $alias
	 * @return array
	 */
	public static function retrievePath($alias) {
		foreach (self::$alias as $l => $p) {
			if (array_key_exists($alias, $p)) {
				$lang = $l;
				$path = $p;
				break;
			}
		}
		if (empty($path[$alias])) return [null, null];
		return [$path[$alias], $lang];
	}

}