<?php
namespace Sy\Bootstrap;

use Sy\Component\WebComponent;
use Sy\Component\Html\Page;
use Sy\Bootstrap\Lib\Url;

abstract class Application extends Page {

	/**
	 * @var WebComponent
	 */
	private $body;

	/**
	 * Application constructor
	 */
	public function __construct() {
		parent::__construct();
		// Set language
		$service = Service\Container::getInstance();
		$user = $service->user->getCurrentUser();
		if ($user->isConnected() and ($user->language !== $this->session('sy_language'))) {
			$service->user->setLanguage($user->language);
		}
		if (is_null($this->session('sy_language'))) {
			$l = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : LANG;
			$service->user->setLanguage($l);
		}
		if (URL_REWRITING) {
			$this->initUrlConverter();
			Url::analyse();
		}
		$this->actionDispatch(CONTROLLER_TRIGGER, 'index');
	}

	/**
	 * Default action
	 */
	public function indexAction() {
		$method = $this->get(CONTROLLER_TRIGGER, 'page');
		$class = $this->controllerClass($method);
		if (is_null($class)) {
			$this->pageNotFound();
		} else {
			$this->$method();
		}
	}

	/**
	 * Return Application render
	 *
	 * @return string
	 */
	public function __toString() {
		$this->init();
		$this->setTitle(Lib\HeadData::getTitle() . ' - ' . PROJECT);
		$this->setDescription(Lib\HeadData::getDescription());
		$this->addBody($this->body);
		return parent::__toString();
	}

	/**
	 * Initialize Application
	 */
	private function init() {
		$this->addTranslator(LANG_DIR);

		$this->preInit();

		// Meta
		foreach (Lib\HeadData::getMeta() as $meta) {
			$this->setMeta($meta['name'], $meta['content'], $meta['http-equiv']);
		}

		// Canonical
		$canonical = Lib\HeadData::getCanonical();
		if (!empty($canonical)) {
			$this->addLink(['rel' => 'canonical', 'href' => $canonical]);
		}

		// Lang
		$lang = \Sy\Translate\LangDetector::getInstance(LANG)->getLang();
		Lib\HeadData::setHtmlAttribute('lang', $lang);

		// Html & Body attributes
		$this->setHtmlAttributes(Lib\HeadData::getHtmlAttributes());
		$this->setBodyAttributes(Lib\HeadData::getBodyAttributes());

		$this->postInit();
	}

	/**
	 * Init controller
	 */
	public function __call($name, $arguments) {
		// Check if controller exists
		$class = $this->controllerClass($name);
		try {
			if (is_null($class)) throw new Application\PageNotFoundException();
			$controller = new $class();
		} catch(Application\PageNotFoundException $e) {
			header('HTTP/1.0 404 Not Found');
			$class = $this->controllerClass('page');
			$controller = new $class(404);
		}

		// Flash message created as soon as possible to handle clear request
		$flashMessage = new Component\FlashMessage();

		$this->body = new WebComponent();
		$this->body->addTranslator(LANG_DIR);
		$this->body->setTemplateFile(__DIR__ . '/Application.html');
		$this->body->setComponent('CONTENT', $controller);
		$this->body->setComponent('FLASH_MESSAGE', $flashMessage);
	}

	/**
	 * Retrieve controller class
	 *
	 * @param string $name
	 * @return string|null
	 */
	protected function controllerClass($name) {
		$namespace = implode('\\', array_slice(explode('\\', get_class($this)), 0, -1));
		$class = $namespace . '\\Application\\' . ucfirst($name);
		if (class_exists($class)) return $class;
		$class = __NAMESPACE__ . '\\Application\\' . ucfirst($name);
		if (class_exists($class)) return $class;
		return null;
	}

	abstract protected function initUrlConverter();

	abstract protected function preInit();

	abstract protected function postInit();

}

namespace Sy\Bootstrap\Application;

class Exception extends \Exception {}
class PageNotFoundException extends Exception {}