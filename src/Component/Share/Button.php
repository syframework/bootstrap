<?php
namespace Sy\Bootstrap\Component\Share;

use Sy\Bootstrap\Component\Modal\Button as ModalButton;

class Button extends ModalButton {

	private $url;

	public function __construct($label, $url = '') {
		parent::__construct('shareModal', $label, 'share-alt');
		$this->url = $url;
	}

	public function __toString() {
		$this->init();
		return parent::__toString();
	}

	private function init() {
		$this->addTranslator(LANG_DIR);
		$dialog = $this->getDialog();
		$dialog->setBody(new Buttons($this->url));
		$this->addJsCode(__DIR__ . '/Button.js');
	}

}
