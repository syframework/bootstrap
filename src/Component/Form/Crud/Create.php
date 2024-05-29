<?php
namespace Sy\Bootstrap\Component\Form\Crud;

class Create extends \Sy\Bootstrap\Component\Form\Crud {

	/**
	 * @param string $service Crud service name
	 */
	public function __construct($service) {
		parent::__construct($service);
	}

	public function submitAction() {
		try {
			$this->validatePost();
			$this->getService()->create($this->post('form'));
			return $this->jsonSuccess('Created successfully');
		} catch (\Sy\Component\Html\Form\Exception $e) {
			$this->logWarning($e);
			return $this->jsonError('Please fill the form correctly');
		} catch (\Sy\Db\MySql\DuplicateEntryException $e) {
			$this->logWarning($e);
			return $this->jsonError('Item already exists');
		} catch (\Sy\Db\MySql\Exception $e) {
			$this->logWarning($e);
			return $this->jsonError('Database error');
		}
	}

	protected function initButton() {
		$this->addButton('Create', ['type' => 'submit'], ['icon' => 'plus', 'color' => 'primary']);
	}

}