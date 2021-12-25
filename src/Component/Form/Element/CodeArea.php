<?php
namespace Sy\Bootstrap\Component\Form\Element;

class CodeArea extends \Sy\Component\Html\Form\Textarea {

	private $cdn = 'https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.13';

	private $theme = 'chrome';

	private $mode = 'text';

	public function __construct() {
		parent::__construct();
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

	private function preInit() {
		$this->setTemplateFile(__DIR__ . '/CodeArea/CodeArea.tpl', 'php');
		$cdn = $this->cdn . '/';
		$this->addJsLink($cdn . 'ace.js');
		$this->addJsLink($cdn . 'ext-language_tools.min.js');
		$this->addCssCode('
			pre.ace-editor {
				width: 100%;
				height: 100%;
				font-size: 16px;
			}
		');
	}

	private function postInit() {
		// id
		if (is_null($this->getAttribute('id'))) $this->setAttribute('id', uniqid());

		// Hide textarea
		$this->setAttribute('hidden', 'hidden');

		$codeAreaId = 'codearea_' . $this->getAttribute('id');
		$this->setVar('CODE_AREA_ID', $codeAreaId);

		// js code
		$js = new \Sy\Component();
		$js->setTemplateFile(__DIR__ . '/CodeArea/CodeArea.js');
		$js->setVars([
			'CODE_AREA_ID' => $codeAreaId,
			'TEXT_AREA_ID' => $this->getAttribute('id'),
			'THEME'        => $this->theme,
			'MODE'         => $this->mode,
			'PLACEHOLDER'  => $this->getAttribute('placeholder'),
		]);
		$this->addJsCode($js);
	}

}