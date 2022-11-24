<?php
namespace Sy\Bootstrap\Lib\Mail;

class Button extends \Sy\Component\WebComponent {

	private $label;

	private $url;

	public function __construct($label, $url) {
		parent::__construct();
		$this->label = $label;
		$this->url = $url;
	}

	private function init() {
		$this->addTranslator(LANG_DIR . '/mail');
		$this->setTemplateFile(__DIR__ . '/Button.html');
		$this->setVars([
			'URL'   => $this->url,
			'LABEL' => $this->_($this->label),
		]);
	}

	public function __toString() {
		$this->init();
		return parent::__toString();
	}

}