<?php
namespace Sy\Bootstrap\Component;

use Sy\Bootstrap\Lib\HeadData;

abstract class Page extends \Sy\Component\Html\Page {

	private $body;

	abstract protected function preInit();

	abstract protected function postInit();

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

		// Html & Body attributes
		$this->setHtmlAttributes(HeadData::getHtmlAttributes());
		$this->setBodyAttributes(HeadData::getBodyAttributes());

		$this->postInit();
	}

	public function __toString() {
		$this->init();
		$this->setTitle(HeadData::getTitle() . ' - ' . PROJECT);
		$this->setDescription(HeadData::getDescription());
		$this->addBody($this->body);
		return parent::__toString();
	}

}