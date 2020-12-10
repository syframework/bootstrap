<?php
namespace Sy\Bootstrap\Component\Form;

class Picture extends \Sy\Component\Html\Form\Element {

	private $options;

	public function __construct(array $options) {
		parent::__construct();
		$this->options = $options;
	}

	public function __toString() {
		$this->init();
		return parent::__toString();
	}

	private function init() {
		$this->addTranslator(LANG_DIR);
		$this->setTemplateFile(__DIR__ . '/Picture.tpl');
		$this->setVars([
			'NAME'  => isset($this->options['name'])  ? $this->options['name']            : 'picture',
			'CLASS' => isset($this->options['class']) ? $this->options['class']           : '',
			'COLOR' => isset($this->options['color']) ? $this->options['color']           : 'secondary',
			'SIZE'  => isset($this->options['size'])  ? $this->options['size']            : '',
			'ICON'  => isset($this->options['icon'])  ? $this->options['icon']            : 'camera',
			'LABEL' => isset($this->options['label']) ? $this->_($this->options['label']) : '',
			'TITLE' => isset($this->options['title']) ? $this->_($this->options['title']) : '',
			'PICTURE_ID' => uniqid('picture'),
		]);

		$js = new \Sy\Component\WebComponent();
		$js->setTemplateFile(__DIR__ . '/Picture.js');
		$js->setVars([
			'ALERT_IMAGE'     => json_encode($this->_('Selected file is not an image')),
			'ALERT_DIMENSION' => json_encode($this->_('Picture is too small')),
			'ALERT_COUNT'     => json_encode('20 ' . $this->_('pictures max')),
		]);
		$this->addJsCode($js);
	}

}