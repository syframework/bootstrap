<?php
namespace Sy\Bootstrap\Component\Form;

class Textarea extends \Sy\Component\Html\Form\Textarea {

	public function setError($error) {
		parent::setError($this->_($error));
		$this->addClass('is-invalid');
	}

}