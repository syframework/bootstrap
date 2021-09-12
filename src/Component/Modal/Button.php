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
	 * @param string $width Button width: auto 100 75 50 25
	 * @param string $size xs|sm|lg
	 */
	public function __construct($id, $label, $icon, $color = 'secondary', $width = '100', $size = '') {
		parent::__construct();
		$this->id = $id;
		$this->label = $label;
		$this->icon = $icon;
		$this->color = $color;
		$this->width = $width;
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

	/**
	 * Set the font awesome icon on the button
	 *
	 * @param string $icon 'fas fa-[ICON_NAME]'
	 * @return void
	 */
	public function setIcon($icon) {
		$this->icon = $icon;
	}

	private function init() {
		$this->addTranslator(LANG_DIR);
		$this->setTemplateFile(__DIR__ . '/Button.html');

		$this->setVars([
			'ID'    => $this->id,
			'LABEL' => $this->_($this->label),
			'ICON'  => $this->icon,
			'COLOR' => $this->color,
			'WIDTH' => empty($this->width) ? '' : 'w-' . $this->width,
			'SIZE'  => empty($this->size) ? '' : 'btn-' . $this->size,
		]);

		$this->setComponent('DIALOG', $this->dialog);
	}

}
