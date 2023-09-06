<?php
namespace Sy\Bootstrap\Component\Page;

class Update extends \Sy\Bootstrap\Component\Form\Crud {

	private $id;

	public function __construct($id) {
		parent::__construct('page', ['id' => $id]);
		$this->id = $id;
	}

	public function init() {
		parent::init();
		$this->getField('id')->setAttribute('readonly', 'readonly');

		// Title
		$this->getField('title')->setAttribute('maxlength', '128');

		// Description
		$this->getField('description')->setAttribute('maxlength', '256');
	}

}