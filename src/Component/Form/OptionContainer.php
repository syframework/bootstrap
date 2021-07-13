<?php
namespace Sy\Bootstrap\Component\Form;

class OptionContainer extends \Sy\Component\Html\Form\OptionContainer {

	public function __construct($name = '') {
		parent::__construct($name);
		$this->addTranslator(LANG_DIR);
	}

	public function setError($error) {
		$this->getParent()->addClass('has-validation');
		$div = $this->getParent()->addElement(new \Sy\Component\Html\Form\Element('div'));
		$div->addClass('invalid-feedback');
		$div->addText($this->_($error));
		$this->addClass('is-invalid');
	}

}