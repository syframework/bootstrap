<?php
namespace Sy\Bootstrap;

use Sy\Bootstrap\Lib\Str;
use Sy\Bootstrap\Lib\Url;

class Application extends \Sy\Component {

	public function __construct() {
		$this->mount(function () {
			$this->init();
		});
	}

	protected function init() {
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
			$controller = new $page(404);
		} else {
			$controller = new $class();
		}

		$this->setTemplateContent('{APP}');
		$this->setVar('APP', $controller);
	}

	protected function initUrlConverter() {}

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

}