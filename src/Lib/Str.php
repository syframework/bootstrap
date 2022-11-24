<?php
namespace Sy\Bootstrap\Lib;

class Str {

	private static $c = [];

	private static $v = [];

	private static $cc = [];

	private static $vv = [];

	public static function generateName() {
		$t = [
			['v', 'cc', 'vv'],
			['c', 'vv', 'cc', 'v'],
			['c', 'vv', 'cc', 'vv'],
			['v', 'cc', 'vv', 'c'],
		];
		$n = '';
		foreach ($t[array_rand($t, 1)] as $method) {
			$n .= self::$method();
		}
		return ucfirst($n);
	}

	public static function generateNickname() {
		return self::generateName() . ' ' . self::generateName();
	}

	public static function generateNicknameFromEmail($email) {
		return ucwords(trim(preg_replace('/[^a-z]/', ' ', strtolower(explode('@', $email)[0]))));
	}

	public static function generatePassword() {
		return str_shuffle(implode(array_map(function ($a, $b) {
			return substr(str_shuffle($a), 0, $b);
		}, ['abcdefghijklmnopqrstuvwxyz', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', '0123456789', '!@#$%&*_:+-()[]'], [6, 4, 3, 2])));
	}

	public static function truncateUrl($url) {
		return (strlen($url) > 45) ? substr($url, 0, 30) . '[ ... ]' . substr($url, -15) : $url;
	}

	public static function convertName($name) {
		$name = trim($name);
		if (empty($name)) {
			return 'Someone';
		} else {
			return htmlentities($name, ENT_QUOTES, 'UTF-8');
		}
	}

	public static function convertAdfly($string) {
		if (!defined('ADFLY_KEY') or !defined('ADFLY_UID') or !defined('ADFLY_DOMAIN')) return $string;
		return preg_replace_callback('@href="((https?:)?//[^\s/$.?#].[^\s]*)"@i', function($matches) {
			return 'href="' . file_get_contents('http://api.adf.ly/api.php?key=' . ADFLY_KEY . '&uid=' . ADFLY_UID . '&advert_type=int&domain=' . ADFLY_DOMAIN . '&url=' . urlencode(trim($matches[1], '/'))) . '"';
		}, $string);
	}

	public static function convertLink($string, $dofollow = false) {
		return preg_replace_callback('@((https?|ftp)://[^\s/$.?#].[^\s]*)@i', function($matches) use($dofollow) {
			return '<a href="' . $matches[1] . '" target="_blank"' . ($dofollow ? '' : ' rel="nofollow"') . '>' . self::truncateUrl($matches[1]) . '</a>';
		}, $string);
	}

	public static function convertSimpleImg($string) {
		return preg_replace('/https?:\/\/(\S*)\.(jpg|jpeg|gif|png)(\?(\S*))?(?=\s|$|\pP)(\s\[(.*?)\])?/i', '<figure class="figure"><img class="figure-img img-fluid rounded" src="//$1.$2$3" alt="$6" /><figcaption class="figure-caption text-center">$6</figcaption></figure>', $string);
	}

	public static function convertImg($string) {
		return preg_replace('/https?:\/\/(\S*)\.(jpg|jpeg|gif|png)(\?(\S*))?(?=\s|$|\pP)(\s\[(.*?)\])?/i', '<figure class="figure"><a href="//$1.$2$3" target="_blank"><img class="figure-img img-fluid rounded" src="//$1.$2$3" alt="$6" /></a><figcaption class="figure-caption text-center">$6</figcaption></figure>', $string);
	}

	/**
	 * Extract image URL from a given string
	 *
	 * @param  string $string
	 * @return array
	 */
	public static function extractImgUrl($string) {
		preg_match_all('/https?:\/\/(\S*)\.(jpg|jpeg|gif|png)(\?(\S*))?(?=\s|$|\pP)/i', $string, $matches);
		return $matches[0];
	}

	public static function convertYoutube($string) {
		return preg_replace(
			"/\s*[a-zA-Z\/\/:\.]*youtu(be.com\/watch\?v=|.be\/)([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i",
			" <span class=\"embed-responsive embed-responsive-16by9\"><iframe class=\"embed-responsive-item\" src=\"//www.youtube-nocookie.com/embed/$2\" allowfullscreen></iframe></span> ",
			$string
		);
	}

	public static function convertDailymotion($string) {
		return preg_replace(
			"/\s*[a-zA-Z\/\/:\.]*dai(lymotion.com\/video\/|.ly\/)([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i",
			" <span class=\"embed-responsive embed-responsive-16by9\"><iframe class=\"embed-responsive-item\" src=\"//www.dailymotion.com/embed/video/$2\" allowfullscreen></iframe></span> ",
			$string
		);
	}

	public static function convert($string, $dofollow = true) {
		return str_replace(
			["\r\n", "\r", "\n"],
			" <br />",
			self::convertAdfly(
				self::convertLink(
					self::convertImg(
						self::convertDailymotion(
							self::convertYoutube(
								htmlentities($string, ENT_QUOTES, 'UTF-8')
							)
						)
					),
					$dofollow
				)
			)
		);
	}

	/**
	 * Remove all accent character from a string
	 *
	 * @param  string $string
	 * @return string
	 */
	public static function removeAccent($string) {
		$str = htmlentities($string, ENT_NOQUOTES, 'utf-8');
		$str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
		$str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str);
		$str = preg_replace('#&[^;]+;#', ' ', $str);
		return $str;
	}

	/**
	 * Return a slug version of a string
	 *
	 * @param  string $string
	 * @return string
	 */
	public static function slugify($string) {
		$str = self::removeAccent($string);
		$str = preg_replace("/[^a-zA-Z 0-9\-]+/", '', $str);
		$str = strtolower($str);
		$pieces = explode(' ', trim($str));
		$pieces = array_filter($pieces);
		return implode('-', $pieces);
	}

	/**
	 * Converts a CamlCase string to snake_case string
	 *
	 * @param  string $string
	 * @return string
	 */
	public static function camlToSnake($string) {
		return strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1_$2', $string));
	}

	/**
	 * Converts a snake_case or dash-case string to camlCase
	 *
	 * @param  string $string
	 * @return string
	 */
	public static function snakeToCaml($string) {
		return lcfirst(str_replace('_', '', ucwords(str_replace('-', '_', $string), '_')));
	}

	private static function c() {
		if (empty(self::$c)) {
			$c = [
				'b' => 8,
				'c' => 8,
				'd' => 8,
				'f' => 8,
				'g' => 3,
				'h' => 3,
				'j' => 2,
				'k' => 3,
				'l' => 8,
				'm' => 8,
				'n' => 8,
				'p' => 3,
				'q' => 1,
				'r' => 8,
				's' => 8,
				't' => 8,
				'v' => 8,
				'w' => 1,
				'x' => 1,
				'z' => 1,
			];
			foreach ($c as $l => $n) {
				self::$c = array_merge(self::$c, array_fill(0, $n, $l));
			}
		}
		return self::$c[array_rand(self::$c, 1)];
	}

	private static function v() {
		if (empty(self::$v)) {
			$v = ['a' => 30, 'e' => 30, 'i' => 15, 'o' => 15, 'u' => 9, 'y' => 1];
			foreach ($v as $l => $n) {
				self::$v = array_merge(self::$v, array_fill(0, $n, $l));
			}
		}
		return self::$v[array_rand(self::$v, 1)];
	}

	private static function cc() {
		if (empty(self::$cc)) {
			$c = [
				'b' => 7,
				'c' => 7,
				'd' => 7,
				'f' => 7,
				'ff' => 1,
				'g' => 4,
				'gn' => 1,
				'h' => 3,
				'j' => 2,
				'k' => 4,
				'l' => 6,
				'll' => 2,
				'm' => 6,
				'mm' => 2,
				'n' => 6,
				'nn' => 2,
				'p' => 4,
				'q' => 1,
				'r' => 6,
				'rr' => 2,
				's' => 6,
				'ss' => 2,
				't' => 7,
				'th' => 1,
				'v' => 7,
				'w' => 1,
				'x' => 1,
				'z' => 1,
			];
			foreach ($c as $l => $n) {
				self::$cc = array_merge(self::$cc, array_fill(0, $n, $l));
			}
		}
		return self::$cc[array_rand(self::$cc, 1)];
	}

	private static function vv() {
		if (empty(self::$vv)) {
			$v = ['a' => 25, 'e' => 25, 'ea' => 3, 'ee' => 3, 'au' => 2, 'eu' => 3, 'ei' => 1, 'i' => 14, 'o' => 14, 'oo' => 1, 'u' => 9, 'y' => 1];
			foreach ($v as $l => $n) {
				self::$vv = array_merge(self::$vv, array_fill(0, $n, $l));
			}
		}
		return self::$vv[array_rand(self::$vv, 1)];
	}

}