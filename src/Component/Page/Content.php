<?php
namespace Sy\Bootstrap\Component\Page;

class Content extends \Sy\Component\WebComponent {

	public function __construct() {
		parent::__construct();
		$this->setTemplateFile(__DIR__ . '/Content.html');
	}

	private function init() {
		$this->addTranslator(LANG_DIR);
	}

	public function __toString() {
		$this->init();
		return parent::__toString();
	}

}