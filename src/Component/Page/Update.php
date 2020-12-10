<?php
namespace Sy\Bootstrap\Component\Page;

class Update extends \Sy\Bootstrap\Component\Form\Crud {

	private $id;
	private $lang;

	public function __construct($id, $lang) {
		$this->id = $id;
		$this->lang = $lang;
		parent::__construct('page', ['id' => $id, 'lang' => $lang]);
	}

	public function init() {
		parent::init();
		$this->getField('id')->setAttribute('readonly', 'readonly');

		// Title
		$this->getField('title')->setAttribute('maxlength', '128');
		$this->getField('title')->addValidator(function($value) {
			if (strlen($value) <= 128) return true;
			$this->setError($this->_('128 characters max for title'));
			return false;
		});

		// Description
		$this->getField('description')->setAttribute('maxlength', '256');
		$this->getField('description')->addValidator(function($value) {
			if (strlen($value) <= 512) return true;
			$this->setError($this->_('512 characters max for description'));
			return false;
		});
	}

}