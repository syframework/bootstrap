<?php
namespace Sy\Bootstrap\Component\Form;

class Crud extends \Sy\Bootstrap\Component\Form {

	/**
	 * @var string Service name
	 */
	private $service;

	/**
	 * @var array
	 */
	private $id;

	/**
	 * @var array
	 */
	private $fields;

	/**
	 * @var array
	 */
	private $item;

	public function __construct($service, array $id = []) {
		$s = \Sy\Bootstrap\Service\Container::getInstance();
		$this->service = $s->$service;
		$this->id = $id;
		$this->fields = [];
		parent::__construct();
	}

	public function init() {
		$this->initInputs();
		$this->initButton();
	}

	public function submitAction() {
		try {
			$this->validatePost();
			$this->updateRow();
			$this->setSuccess($this->_('Saved'));
		} catch(\Sy\Component\Html\Form\Exception $e) {
			$this->logWarning($e);
			if (is_null($this->getOption('error'))) {
				$this->setError($this->_('Please fill the form correctly'));
			}
			$this->fill($_POST);
		} catch(\Sy\Bootstrap\Lib\Crud\Exception $e) {
			$this->logWarning($e);
			$this->setError($this->_('Database error'));
			$this->fill($_POST);
		}
	}

	public function updateRow($fields = null) {
		if (is_null($fields)) {
			$this->validatePost();
			$fields = $this->post('form');
		}
		if (!empty($this->id) and $this->service->count($this->id) > 0) {
			// Remove the pk
			foreach ($this->id as $k => $v) {
				unset($fields[$k]);
			}
			$this->service->update($this->id, $fields);
		} else {
			$this->service->change($fields);
		}
	}

	public function initInputs() {
		parent::init();
		$this->addCsrfField();
		$rows = $this->service->getColumns();
		$item = $this->getItem();
		foreach ($rows as $row) {
			if ($row['Extra'] === 'auto_increment') continue;
			if ($row['Comment'] === 'none') continue;
			if ($row['Comment'] === 'hidden') {
				$field = $this->addHidden(['name'  => 'form[' . $row['Field'] . ']']);
				if (!empty($item)) {
					$field->setAttribute('value', $item[$row['Field']]);
				}
				$this->fields[$row['Field']] = $field;
				continue;
			} elseif ($row['Comment'] === 'readonly') {
				$field = $this->addTextInput(['disabled' => 'disabled'], ['label' => ucwords(strtolower($row['Field']))]);
				if (!empty($item)) {
					$field->setAttribute('value', $item[$row['Field']]);
				}
				$this->fields[$row['Field']] = $field;
				continue;
			} elseif ($row['Comment'] === 'select') {
				$field = $this->addSelect(['name' => 'form[' . $row['Field'] . ']'], ['label' => ucwords(strtolower($row['Field']))]);
				$this->fields[$row['Field']] = $field;
				continue;
			} elseif ($row['Comment'] === 'textarea') {
				$field = $this->addTextarea(['name' => 'form[' . $row['Field'] . ']'], ['label' => ucwords(strtolower($row['Field']))]);
			} else {
				$method = ($row['Type'] === 'text') ? 'addTextarea' : 'addTextInput';
				$field = $this->$method(['name' => 'form[' . $row['Field'] . ']'], ['label' => ucwords(strtolower($row['Field']))]);
			}
			if ($row['Key'] === 'PRI') {
				$field->setAttribute('required', 'required');
			}
			if (!empty($item)) {
				if ($row['Type'] === 'text' or $row['Comment'] === 'textarea') {
					$field->setContent([$item[$row['Field']]]);
				} else {
					$field->setAttribute('value', $item[$row['Field']]);
				}
			}
			$this->fields[$row['Field']] = $field;
		}
	}

	public function getService() {
		return $this->service;
	}

	public function getId() {
		return $this->id;
	}

	public function getItem() {
		if (!isset($this->item)) {
			$this->item = empty($this->id) ? [] : $this->service->retrieve($this->id);
		}
		return $this->item;
	}

	public function getField($name) {
		return $this->fields[$name];
	}

	protected function initButton() {
		$this->addButton('Save', ['type' => 'submit'], ['icon' => 'fas fa-save', 'color' => 'primary']);
	}

}