<?php
namespace Sy\Bootstrap\Component\Share;

class Buttons extends \Sy\Component\WebComponent {

	private $url;

	public function __construct($url) {
		parent::__construct();
		$this->url = $url;

		$this->mount(function () {
			$this->init();
		});
	}

	private function init() {
		$this->addTranslator(LANG_DIR);
		$this->setTemplateFile(__DIR__ . '/Buttons.html');
		if (defined('CLIPBOARD_JS')) {
			$this->addJsLink(CLIPBOARD_JS);
		} else {
			$this->logError('Constant CLIPBOARD_JS need to be defined');
		}
		$this->addJsCode(__DIR__ . '/Buttons.js');
		$this->setVar('URL', $this->url);
	}

}