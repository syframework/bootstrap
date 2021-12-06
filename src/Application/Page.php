<?php
namespace Sy\Bootstrap\Application;

use Sy\Bootstrap\Lib\HeadData;

abstract class Page extends \Sy\Component\Html\Page {

	private $body;

	/**
	 * @param string|null $pageId
	 */
	public function __construct($pageId = null) {
		parent::__construct();

		// Project page body
		$namespace = implode('\\', array_slice(explode('\\', get_class($this)), 0, -1));
		$body = $namespace . '\\Body';
		$this->body = new $body($pageId);
	}

	public function __toString() {
		$this->init();
		return parent::__toString();
	}

	private function init() {
		$this->addTranslator(LANG_DIR);

		$this->preInit();

		// Meta
		foreach (HeadData::getMeta() as $meta) {
			$this->setMeta($meta['name'], $meta['content'], $meta['http-equiv']);
		}

		// Canonical
		$canonical = HeadData::getCanonical();
		if (!empty($canonical)) {
			$this->addLink(['rel' => 'canonical', 'href' => $canonical]);
		}

		// Lang
		$lang = \Sy\Translate\LangDetector::getInstance(LANG)->getLang();
		HeadData::setHtmlAttribute('lang', $lang);

		// JSON-LD
		$this->setJsonLd(HeadData::getJsonLd());

		// Html & Body attributes
		$this->setHtmlAttributes(HeadData::getHtmlAttributes());
		$this->setBodyAttributes(HeadData::getBodyAttributes());

		// Title, description and body
		$this->setTitle(HeadData::getTitle() . ' - ' . PROJECT);
		$this->setDescription(HeadData::getDescription());
		$this->addBody($this->body);

		// Flash message created as soon as possible to handle clear request
		$flashMessage = new Component\FlashMessage();

		$this->body = new WebComponent();
		$this->body->addTranslator(LANG_DIR);
		$this->body->setTemplateFile(__DIR__ . '/Application.html');
		$this->body->setComponent('CONTENT', $controller);
		$this->body->setComponent('FLASH_MESSAGE', $flashMessage);

		$this->postInit();
	}

	abstract protected function preInit();

	abstract protected function postInit();

}