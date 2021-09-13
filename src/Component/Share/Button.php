<?php
namespace Sy\Bootstrap\Component\Share;

use Sy\Bootstrap\Component\Modal\Button as ModalButton;

class Button extends ModalButton {

	public function __construct($label = '', $url = '', $icon = 'share-alt', $color = 'secondary', $width = '100', $size = '', $title = '') {
		parent::__construct('shareModal', $label, $icon, $color, $width, $size, $title, ['data-url' => $url]);
	}

	public function __toString() {
		$this->init();
		return parent::__toString();
	}

	private function init() {
		$this->addTranslator(LANG_DIR);
		$dialog = $this->getDialog();
		$dialog->setBody(new Buttons(''));
		$this->addJsCode(__DIR__ . '/Button.js');
	}

}
