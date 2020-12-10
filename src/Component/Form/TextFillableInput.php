<?php
namespace Sy\Bootstrap\Component\Form;

class TextFillableInput extends \Sy\Component\Html\Form\TextFillableInput {

	public function __construct($type) {
		parent::__construct($type);
		$this->addTranslator(LANG_DIR);
	}

	public function setError($error) {
		parent::setError($this->_($error));
		$this->getParent()->addClass('has-error');
	}

}