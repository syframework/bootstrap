<?php
namespace Sy\Bootstrap\Component\Share;

use Sy\Bootstrap\Component\Modal\Button as ModalButton;

class Button extends ModalButton {

	public function __construct($label = '', $url = '', $icon = 'share-alt', $color = 'secondary', $width = '100', $size = '', $title = '') {
		parent::__construct('shareModal', $label, $icon, $color, $width, $size, $title, ['data-url' => $url]);

		$dialog = $this->getDialog();
		$dialog->setBody(new Buttons(''));

		$this->mount(function () {
			$this->init();
		});
	}

	private function init() {
		$this->addJsCode(__DIR__ . '/Button.js');
	}

}