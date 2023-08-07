<?php
namespace Sy\Bootstrap\Lib;

class HeadData {

	private static $title = '';

	private static $description = '';

	private static $canonical = '';

	private static $base = [];

	private static $meta = [];

	private static $htmlAttributes = [];

	private static $bodyAttributes = [];

	private static $jsonLd = [];

	public static function getTitle() {
		return self::$title;
	}

	public static function setTitle($title) {
		if (empty($title)) return;
		self::$title = $title;
		self::addMeta('og:title', $title);
	}

	public static function getBase() {
		return self::$base;
	}

	public static function setBase($href = '', $target = '') {
		self::$base = array_filter(['href' => $href, 'target' => $target]);
	}

	public static function getDescription() {
		return self::$description;
	}

	public static function setDescription($description) {
		if (empty($description)) return;
		self::$description = $description;
		self::addMeta('og:description', $description);
	}

	public static function getCanonical() {
		return self::$canonical;
	}

	public static function setCanonical($url) {
		if (empty($url)) return;
		self::$canonical = $url;
		self::addMeta('og:url', $url);
	}

	public static function addMeta($name, $content, $httpEquiv = false) {
		static::$meta[] = ['name' => $name, 'content' => $content, 'http-equiv' => $httpEquiv];
	}

	public static function getMeta() {
		return self::$meta;
	}

	public static function setHtmlAttribute($name, $value) {
		static::$htmlAttributes[$name] = $value;
	}

	public static function getHtmlAttributes() {
		return self::$htmlAttributes;
	}

	public static function setBodyAttribute($name, $value) {
		static::$bodyAttributes[$name] = $value;
	}

	public static function getBodyAttributes() {
		return self::$bodyAttributes;
	}

	public static function addJsonLd(array $jsonLd) {
		static::$jsonLd[] = $jsonLd;
	}

	public static function getJsonLd() {
		return self::$jsonLd;
	}

}