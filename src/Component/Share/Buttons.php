<?php
namespace Sy\Bootstrap\Component\Share;

class Buttons extends \Sy\Component\WebComponent {

	private $url;

	public function __construct($url) {
		parent::__construct();
		$this->url = $url;
	}

	public function __toString() {
		$this->init();
		return parent::__toString();
	}

	private function init() {
		$this->addTranslator(LANG_DIR);
		$this->setTemplateFile(__DIR__ . '/Buttons.html');
		$this->addJsLink(CLIPBOARD_JS);
		$this->addJsCode(file_get_contents(__DIR__ . '/Buttons.js'));
		$this->setVar('URL', $this->url);
	}

}
