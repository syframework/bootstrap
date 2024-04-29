<?php
namespace Sy\Bootstrap\Component\Form;

class Crud extends \Sy\Bootstrap\Component\Form {

	/**
	 * @var \Sy\Bootstrap\Service\Container
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

	/**
	 * @param string $service
	 * @param array $id
	 */
	public function __construct($service, array $id = []) {
		parent::__construct();
		$this->service = $service;
		$this->id = $id;
		$this->fields = [];
	}

	/**
	 * @inheritDoc
	 */
	public function initialize($preInit = null, $postInit = null) {
		parent::initialize(function () use ($preInit) {
			$this->initInputs();
			if (is_callable($preInit)) $preInit();
		}, function () use($postInit) {
			$this->initButton();
			if (is_callable($postInit)) $postInit();
		});
	}

	/**
	 * @inheritDoc
	 */
	public function init() {}

	/**
	 * @inheritDoc
	 */
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

	/**
	 * @param array|null $fields
	 */
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

	/**
	 * Add form inputs
	 */
	public function initInputs() {
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
	 * @return \Sy\Bootstrap\Service\Container
	 */
	public function getServiceContainer() {
		if (empty($this->serviceContainer)) {
			$this->serviceContainer = \Project\Service\Container::getInstance();
		}
		return $this->serviceContainer;
	}

	/**
	 * @param \Sy\Bootstrap\Service\Container $serviceContainer
	 */
	public function setServiceContainer($serviceContainer) {
		$this->serviceContainer = $serviceContainer;
	}

	/**
	 * @return \Sy\Bootstrap\Service\Crud
	 */
	public function getService() {
		$service = $this->service;
		return $this->getServiceContainer()->$service;
	}

	/**
	 * @return array
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return array
	 */
	public function getItem() {
		if (!isset($this->item)) {
			$this->item = empty($this->id) ? [] : $this->getService()->retrieve($this->id);
		}
		return $this->item;
	}

	/**
	 * @param  string $name
	 * @return \Sy\Component\Html\Form\Element
	 */
	public function getField($name) {
		return $this->fields[$name];
	}

	/**
	 * Add submit button
	 */
	protected function initButton() {
		$this->addButton('Save', ['type' => 'submit'], ['icon' => 'save', 'color' => 'primary']);
	}

	/**
	 * Convert row field name to form input label
	 *
	 * @param  string $name
	 * @return string
	 */
	protected function fieldLabel($name) {
		return str_replace('_', ' ', ucwords(strtolower($name)));
	}

}