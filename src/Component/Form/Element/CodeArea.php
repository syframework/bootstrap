<?php
namespace Sy\Bootstrap\Component\Form\Element;

use Sy\Component\WebComponent;

class CodeArea extends \Sy\Component\Html\Form\Textarea {

	private $cdn = 'https://cdn.jsdelivr.net/npm/ace-builds/src-min-noconflict';

	private $theme = 'chrome';

	private $mode = 'text';

	public function __construct() {
		parent::__construct();
		$this->preInit();
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
		$this->addJsLink($cdn . 'ace.min.js', ['position' => WebComponent::JS_TOP]);
		$this->addJsLink($cdn . 'ext-beautify.js', ['position' => WebComponent::JS_TOP]);
		$this->addJsLink($cdn . 'ext-language_tools.min.js', ['position' => WebComponent::JS_TOP]);
		$this->addJsLink($cdn . 'ext-emmet.min.js', ['position' => WebComponent::JS_TOP]);
		$this->addJsLink('https://cloud9ide.github.io/emmet-core/emmet.js');
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
		$this->addJsCode($js, ['position' => WebComponent::JS_TOP]);
	}

	public function __toString() {
		$this->postInit();
		return parent::__toString();
	}

}