<?php
namespace Sy\Bootstrap\Component\Modal;

class Button extends \Sy\Component\WebComponent {

	private $id;
	private $label;
	private $icon;
	private $color;
	private $block;
	private $size;
	private $dialog;

	/**
	 * @param string $id HTML element id attribute
	 * @param string $label The button label text
	 * @param string $icon Font Awesome icon name
	 * @param string $color default|primary|info|warning|danger
	 * @param boolean $block Button block or not
	 * @param string $size xs|sm|lg
	 */
	public function __construct($id, $label, $icon, $color = 'secondary', $block = true, $size = '') {
		parent::__construct();
		$this->id = $id;
		$this->label = $label;
		$this->icon = $icon;
		$this->color = $color;
		$this->block = $block;
		$this->size = $size;
		$this->dialog = new Dialog($id, '');
	}

	public function __toString() {
		$this->init();
		return parent::__toString();
	}

	/**
	 * @return Dialog
	 */
	public function getDialog() {
		return $this->dialog;
	}

	private function init() {
		$this->addTranslator(LANG_DIR);
		$this->setTemplateFile(__DIR__ . '/Button.html');

		$this->setVars([
			'ID'    => $this->id,
			'LABEL' => $this->_($this->label),
			'ICON'  => $this->icon,
			'COLOR' => $this->color,
			'BLOCK' => $this->block ? 'w-100' : '',
			'SIZE'  => empty($this->size) ? '' : 'btn-' . $this->size,
		]);

		$this->setComponent('DIALOG', $this->dialog);
	}

}
