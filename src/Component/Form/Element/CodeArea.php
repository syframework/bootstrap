<?php
namespace Sy\Bootstrap\Component\Form\Element;

class CodeArea extends \Sy\Component\Html\Form\Textarea {

	private $cdn = '//cdnjs.cloudflare.com/ajax/libs/codemirror/5.42.2';

	private $theme = 'default';

	private $mode;

	private $params;

	public function __construct() {
		parent::__construct();
		$this->params = [];
		$this->preInit();
	}

	public function __toString() {
		$this->postInit();
		return parent::__toString();
	}

	public function setTheme($theme) {
		$this->theme = $theme;
	}

	public function setMode($mode) {
		$this->mode = $mode;
	}

	public function setParams($params) {
		$this->params = $params;
	}

	private function preInit() {
		$cdn = $this->cdn . '/';
		$this->addCssLink($cdn . 'codemirror.min.css');
		$this->addCssLink($cdn . 'addon/hint/show-hint.min.css');
		$this->addCssLink($cdn . 'addon/fold/foldgutter.min.css');
		$this->addJsLink($cdn . 'codemirror.min.js');
	}

	private function postInit() {
		// id
		if (is_null($this->getAttribute('id'))) $this->setAttribute('id', uniqid());

		// parameters
		$cdn = $this->cdn . '/';
		if ($this->theme !== 'default') $this->addCssLink($cdn . 'theme/' . $this->theme . '.min.css');
		foreach ($this->params as $param) {
			$this->addJsLink($cdn . $param . '.min.js');
		}

		// js code
		$js = new \Sy\Component();
		$js->setTemplateFile(__DIR__ . '/CodeArea/CodeArea.js');
		$js->setVars([
			'CODE_AREA_ID' => $this->getAttribute('id'),
			'THEME'        => $this->theme
		]);
		if (!empty($this->mode)) {
			$js->setVar('MODE', $this->mode);
			$js->setBlock('MODE_BLOCK');
		}
		$this->addJsCode($js);
	}

}