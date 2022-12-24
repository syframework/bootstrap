<?php
namespace Sy\Bootstrap\Component\Form;

class Crud extends \Sy\Bootstrap\Component\Form {

	/**
	 * @var Container
	 */
	private $serviceContainer;

	/**
	 * @var string
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
		$this->service = $service;
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
		} catch (\Sy\Component\Html\Form\Exception $e) {
			$this->logWarning($e);
			if (is_null($this->getOption('error'))) {
				$this->setError($this->_('Please fill the form correctly'));
			}
			$this->fill($_POST);
		} catch (\Sy\Db\MySql\Exception $e) {
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
		if (!empty($this->id) and $this->getService()->count($this->id) > 0) {
			// Remove the pk
			foreach ($this->id as $k => $v) {
				unset($fields[$k]);
			}
			$this->getService()->update($this->id, $fields);
		} else {
			$this->getService()->change($fields);
		}
	}

	public function initInputs() {
		parent::init();
		$this->addCsrfField();
		$rows = $this->getService()->getColumns();
		$item = $this->getItem();
		foreach ($rows as $row) {
			if ($row['Extra'] === 'auto_increment') continue;
			if ($row['Comment'] === 'none') continue;
			if ($row['Comment'] === 'hidden') {
				$field = $this->addHidden(['name'  => 'form[' . $row['Field'] . ']']);
				if (!empty($item)) {
					$field->setAttribute('value', $item[$row['Field']] ?? '');
				}
				$this->fields[$row['Field']] = $field;
				continue;
			} elseif ($row['Comment'] === 'readonly') {
				$field = $this->addTextInput(['disabled' => 'disabled'], ['label' => $this->fieldLabel($row['Field'])]);
				if (!empty($item)) {
					$field->setAttribute('value', $item[$row['Field']] ?? '');
				}
				$this->fields[$row['Field']] = $field;
				continue;
			} elseif ($row['Comment'] === 'select') {
				$field = $this->addSelect(['name' => 'form[' . $row['Field'] . ']', 'id' => $row['Field'] . '-' . uniqid()], ['label' => $this->fieldLabel($row['Field'])]);
				$this->fields[$row['Field']] = $field;
				continue;
			} elseif ($row['Comment'] === 'tel') {
				$field = $this->addTel(['name' => 'form[' . $row['Field'] . ']', 'id' => $row['Field'] . '-' . uniqid()], ['label' => $this->fieldLabel($row['Field'])]);
			} elseif ($row['Comment'] === 'url') {
				$field = $this->addUrl(['name' => 'form[' . $row['Field'] . ']', 'id' => $row['Field'] . '-' . uniqid()], ['label' => $this->fieldLabel($row['Field'])]);
			} elseif ($row['Comment'] === 'textarea') {
				$field = $this->addTextarea(['name' => 'form[' . $row['Field'] . ']', 'id' => $row['Field'] . '-' . uniqid()], ['label' => $this->fieldLabel($row['Field'])]);
			} else {
				$method = ($row['Type'] === 'text') ? 'addTextarea' : 'addTextInput';
				$field = $this->$method(['name' => 'form[' . $row['Field'] . ']', 'id' => $row['Field'] . '-' . uniqid()], ['label' => $this->fieldLabel($row['Field'])]);
			}
			if ($row['Key'] === 'PRI') {
				$field->setAttribute('required', 'required');
			}
			if (!empty($item)) {
				if ($row['Type'] === 'text' or $row['Comment'] === 'textarea') {
					$field->setContent($item[$row['Field']]);
				} else {
					$field->setAttribute('value', $item[$row['Field']] ?? '');
				}
			}
			$this->fields[$row['Field']] = $field;
		}
	}

	/**
	 * @return Container
	 */
	public function getServiceContainer() {
		if (empty($this->serviceContainer)) {
			$this->serviceContainer = \Project\Service\Container::getInstance();
		}
		return $this->serviceContainer;
	}

	public function setServiceContainer($serviceContainer) {
		$this->serviceContainer = $serviceContainer;
	}

	/**
	 * @return \Sy\Bootstrap\Service\Container\Crud
	 */
	public function getService() {
		$service = $this->service;
		return $this->getServiceContainer()->$service;
	}

	public function getId() {
		return $this->id;
	}

	public function getItem() {
		if (!isset($this->item)) {
			$this->item = empty($this->id) ? [] : $this->getService()->retrieve($this->id);
		}
		return $this->item;
	}

	public function getField($name) {
		return $this->fields[$name];
	}

	protected function initButton() {
		$this->addButton('Save', ['type' => 'submit'], ['icon' => 'fas fa-save', 'color' => 'primary']);
	}

	protected function fieldLabel($name) {
		return str_replace('_', ' ', ucwords(strtolower($name)));
	}

}