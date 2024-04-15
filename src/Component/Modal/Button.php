<?php
namespace Sy\Bootstrap\Component\Modal;

use Sy\Bootstrap\Component\Icon;

class Button extends \Sy\Component\WebComponent {

	private $id;

	private $label;

	private $icon;

	private $color;

	private $width;

	private $size;

	private $title;

	private $attributes;

	private $dialog;

	/**
	 * @param string $id HTML element id attribute
	 * @param string $label The button label text
	 * @param string $icon Icon name
	 * @param string $color default|primary|info|warning|danger
	 * @param string $width Button width: auto 100 75 50 25
	 * @param string $size sm|lg
	 * @param string $title Button title
	 * @param array $attributes Additionnal button attributes
	 */
	public function __construct($id, $label = '', $icon = '', $color = 'secondary', $width = '100', $size = '', $title = '', $attributes = []) {
		parent::__construct();
		$this->id = $id;
		$this->label = $label;
		$this->icon = $icon;
		$this->color = $color;
		$this->width = $width;
		$this->size = $size;
		$this->title = $title;
		$this->attributes = $attributes;
		$this->dialog = new Dialog($id, '');

		$this->mount(function () {
			$this->init();
		});
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

		// Class attribute
		if (isset($this->attributes['class'])) {
			$this->setVar('CLASS', $this->attributes['class']);
			unset($this->attributes['class']);
		}

		$this->setVars([
			'ID'     => $this->id,
			'LABEL'  => $this->_($this->label),
			'ICON'   => empty($this->icon) ? '' : new Icon($this->icon),
			'COLOR'  => $this->color,
			'WIDTH'  => empty($this->width) ? '' : 'w-' . $this->width,
			'SIZE'   => empty($this->size) ? '' : 'btn-' . $this->size,
			'TITLE'  => empty($this->title) ? '' : 'title="' . $this->_($this->title) . '" data-bs-title="' . $this->_($this->title) . '"',
			'ATTR'   => empty($this->attributes) ? '' : implode(' ', array_map(fn($k, $v) => $k . '="' . $v . '"', array_keys($this->attributes), $this->attributes)),
			'DIALOG' => htmlentities(strval($this->dialog), ENT_QUOTES),
		]);

		$this->addJsCode(__DIR__ . '/Button.js');

		// Need to merge dialog javascript
		$this->mergeJs($this->dialog);
	}

}