<?php
namespace Sy\Bootstrap;

use Sy\Bootstrap\Lib\Str;
use Sy\Bootstrap\Lib\Url;

abstract class Application {

	/**
	 * @var \Sy\Component\WebComponent
	 */
	private $controller;

	abstract protected function initUrlConverter();

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
		$service->lang->getLang();

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
	 * Retrieve controller class
	 *
	 * @param  string $name
	 * @return string|null
	 */
	protected function controllerClass($name) {
		$class = get_class($this);
		$namespace = substr_replace($class, '', strrpos($class, '\\'));
		$class = $namespace . '\\Application\\' . ucfirst(Str::snakeToCaml($name));
		if (class_exists($class)) return $class;
		$class = __NAMESPACE__ . '\\Application\\' . ucfirst(Str::snakeToCaml($name));
		if (class_exists($class)) return $class;
		return null;
	}

	/**
	 * Return Application render
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->controller->__toString();
	}

}