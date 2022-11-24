<?php
namespace Sy\Bootstrap\Component\Form\Crud;

class Delete extends \Sy\Bootstrap\Component\Form\Crud {

	public function __construct($service, array $id) {
		parent::__construct($service, $id);
	}

	public function init() {
		$this->addTranslator(LANG_DIR);
		foreach ($this->getId() as $key => $value) {
			$this->addHidden(['name' => $key, 'value' => $value, 'required' => 'required']);
		}
		$this->addCsrfField();
		$this->initButton();
	}

	public function submitAction() {
		try {
			$this->validatePost();
			$id = [];
			foreach ($this->getId() as $key => $value) {
				$id[$key] = $this->post($key);
			}
			$this->getService()->delete($id);
			$this->setSuccess($this->_('Deleted successfully'));
		} catch (\Sy\Bootstrap\Component\Form\CsrfException $e) {
			$this->logWarning($e);
			$this->setDanger($e->getMessage());
			$this->fill($_POST);
		} catch (\Sy\Component\Html\Form\Exception $e) {
			$this->logWarning($e);
			$this->setDanger($this->_('Please fill the form correctly'));
			$this->fill($_POST);
		} catch (\Sy\Db\MySql\Exception $e) {
			$this->logWarning($e);
			$this->setDanger($this->_('Database error'));
			$this->fill($_POST);
		}
	}

	protected function initButton() {
		$this->addButton('Delete', ['type' => 'submit'], ['icon' => 'fas fa-trash-alt', 'color' => 'danger']);
	}

}