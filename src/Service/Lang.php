<?php
namespace Sy\Bootstrap\Service;

use Sy\Http;
use Sy\Translate\LangDetector;

/**
 * Language detection service
 */
class Lang {

	/**
	 * @var string
	 */
	private $lang;

	/**
	 * Get the correct display language
	 *
	 * @return string
	 */
	public function getLang() {
		if (!isset($this->lang)) {
			$this->setLang($this->detect());
		}
		return $this->lang;
	}

	/**
	 * Set the correct display language
	 *
	 * @param string $lang
	 */
	public function setLang(string $lang) {
		if (!$this->isAvailable($lang)) return;
		setcookie('sy_language', $lang, time() + 60 * 60 * 24 * 365, WEB_ROOT . '/');
		$_COOKIE['sy_language'] = $lang;
		$_SESSION['sy_language'] = $lang;
		$_GET['sy_language'] = $lang;
		LangDetector::getInstance()->setLang($lang);
		LangDetector::getInstance(LANG)->setLang($lang);
		$this->lang = $lang;
	}

	/**
	 * Return if $lang is an available language listed in config
	 *
	 * @param  string $lang
	 * @return boolean
	 */
	public function isAvailable($lang) {
		if (empty($lang)) return false;
		return in_array($lang, array_keys(LANGS));
	}

	/**
	 * Detect the correct display language
	 *
	 * @return string
	 */
	private function detect() {
		// 1. The URL indicate which language to display
		$lang = Http::get('lang');
		if ($this->isAvailable($lang)) return $lang;

		// 2. User preferred language
		$service = Container::getInstance();
		$user = $service->user->getCurrentUser();

		// 2.1 Connected user language setting
		if ($user->isConnected()) {
			$lang = $user->language;
			if ($this->isAvailable($lang)) return $lang;
		}

		// 2.2 Preferred language previously set
		$lang = LangDetector::getInstance()->getLang();
		if ($this->isAvailable($lang)) return $lang;

		// 2.3 Browser language
		if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
			if ($this->isAvailable($lang)) return $lang;
		}

		// 3. Default language
		return LangDetector::getInstance(LANG)->getLang();
	}

}