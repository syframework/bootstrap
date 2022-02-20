<?php
namespace Sy\Bootstrap;

use Sy\Bootstrap\Lib\Str;
use Sy\Bootstrap\Lib\Url;

abstract class Application {

	/**
	 * @var \Sy\Component\WebComponent
	 */
	private $controller;

	/**
	 * Application constructor
	 */
	public function __construct() {
		// Extract url data
		if (URL_REWRITING) {
			$this->initUrlConverter();
			Url::analyse();
		}

		// Set language
		$service = \Project\Service\Container::getInstance();
		$user = $service->user->getCurrentUser();
		if ($user->isConnected() and ($user->language !== \Sy\Http::session('sy_language'))) {
			$service->user->setLanguage($user->language);
		}
		if (is_null(\Sy\Http::session('sy_language'))) {
			$l = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : LANG;
			$service->user->setLanguage($l);
		}

		// Find controller class
		$class = $this->controllerClass(\Sy\Http::get(CONTROLLER_TRIGGER, 'page'));
		if (is_null($class)) {
			$page = $this->controllerClass('page');
			$this->controller = new $page(404);
		} else {
			$this->controller = new $class();
		}
	}

	/**
	 * Return Application render
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->controller->__toString();
	}

	/**
	 * Retrieve controller class
	 *
	 * @param string $name
	 * @return string|null
	 */
	protected function controllerClass($name) {
		$namespace = implode('\\', array_slice(explode('\\', get_class($this)), 0, -1));
		$class = $namespace . '\\Application\\' . ucfirst(Str::snakeToCaml($name));
		if (class_exists($class)) return $class;
		$class = __NAMESPACE__ . '\\Application\\' . ucfirst(Str::snakeToCaml($name));
		if (class_exists($class)) return $class;
		return null;
	}

	abstract protected function initUrlConverter();

}