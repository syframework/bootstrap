<?php
namespace Sy\Bootstrap\Lib;

class HeadData {

	private static $title = '';

	private static $description = '';

	private static $canonical = '';

	private static $meta = [];

	private static $htmlAttributes = [];

	private static $bodyAttributes = [];

	public static function getTitle() {
		return self::$title;
	}

	public static function setTitle($title) {
		self::$title = $title;
		self::addMeta('og:title', $title);
	}

	public static function getDescription() {
		return self::$description;
	}

	public static function setDescription($description) {
		self::$description = $description;
		self::addMeta('og:description', $description);
	}

	public static function getCanonical() {
		return self::$canonical;
	}

	public static function setCanonical($url) {
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

}