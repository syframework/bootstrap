<?php
namespace Sy\Bootstrap\Component\Message\Received;

class EditModal extends \Sy\Component\WebComponent {

	public function __toString() {
		$this->init();
		return parent::__toString();
	}

	private function init() {
		$this->addTranslator(LANG_DIR);
		$this->setTemplateFile(__DIR__ . '/EditModal.html');
		$this->addJsCode(__DIR__ . '/EditModal.js');
		$this->setComponent('EDIT_FORM', new EditForm());
	}

}