<?php
namespace Sy\Bootstrap\Component\Form\Crud;

class Create extends \Sy\Bootstrap\Component\Form\Crud {

	public function __construct($service) {
		parent::__construct($service);
	}

	protected function initButton() {
		$this->addButton('Create', ['type' => 'submit'], ['icon' => 'fas fa-plus', 'color' => 'primary']);
	}

	public function submitAction() {
		try {
			$this->validatePost();
			$this->getService()->create($_POST['form']);
			$this->setSuccess($this->_('Created successfully'));
		} catch(\Sy\Component\Html\Form\Exception $e) {
			$this->logWarning($e);
			$this->setError($this->_('Please fill the form correctly'));
			$this->fill($_POST);
		} catch(\Sy\Bootstrap\Lib\Crud\DuplicateEntryException $e) {
			$this->logWarning($e);
			$this->setError($this->_('Item already exists'));
			$this->fill($_POST);
		} catch(\Sy\Bootstrap\Lib\Crud\Exception $e) {
			$this->logWarning($e);
			$this->setError($this->_('Database error'));
			$this->fill($_POST);
		}
	}

}