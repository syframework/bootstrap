<?php
namespace Sy\Bootstrap\Component\Form\Crud;

class Delete extends \Sy\Bootstrap\Component\Form\Crud {

	/**
	 * @param string $service Crud service name
	 * @param array $id Crud item id
	 * @param array $options Form options
	 * @param array $attributes Form attributes
	 */
	public function __construct($service, array $id, array $options = [], array $attributes = []) {
		parent::__construct($service, $id);
		$this->setOptions($options);
		$this->setAttributes($attributes);
		$this->addClass('syform-delete');

		$this->mount(function () {
			$this->addJsCode(__DIR__ . '/Delete.js');
			$this->setAttribute('data-confirm', $this->_($this->getOption('confirm')) ?? $this->_('Are you sure to delete?'));
		});
	}

	public function init() {
		parent::init();

		// Add selector option in a data attribute
		$this->setAttribute('data-selector', $this->getOption('selector') ?? '');
	}

	public function submitAction() {
		try {
			$this->validatePost();
			$id = [];
			foreach ($this->getId() as $key => $value) {
				$id[$key] = $this->post($key);
			}
			$this->getService()->delete($id);
			return $this->jsonSuccess(
				$this->getOption('flash-message') ?? [] + ['message' => 'Deleted successfully'],
				[
					'redirection' => $this->getOption('redirection'),
				]
			);
		} catch (\Sy\Bootstrap\Component\Form\CsrfException $e) {
			$this->logWarning($e);
			return $this->jsonError($e->getMessage());
		} catch (\Sy\Component\Html\Form\Exception $e) {
			$this->logWarning($e);
			return $this->jsonError('Please fill the form correctly');
		} catch (\Sy\Db\MySql\Exception $e) {
			$this->logWarning($e);
			return $this->jsonError('Database error');
		}
	}

	public function initInputs() {
		foreach ($this->getId() as $key => $value) {
			$this->addHidden(['name' => $key, 'value' => $value, 'required' => 'required']);
		}
		$this->addCsrfField();
	}

	protected function initButton() {
		$this->addButton(
			$this->getOption('button-label') ?? '',
			($this->getOption('button-attributes') ?? []) + ['type' => 'submit'],
			($this->getOption('button-options') ?? []) + ['icon' => 'trash', 'color' => 'danger']
		);
	}

}