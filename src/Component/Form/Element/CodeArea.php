<?php
namespace Sy\Bootstrap\Component\Form\Element;

use Sy\Component\WebComponent;

class CodeArea extends \Sy\Component\Html\Form\Textarea {

	/**
	 * @var string
	 */
	private $cdn = 'https://cdn.jsdelivr.net/npm/ace-builds/src-min-noconflict';

	/**
	 * @var string
	 */
	private $theme;

	/**
	 * @var string
	 */
	private $mode;

	/**
	 * @var int|string
	 */
	private $fontSize;

	public function __construct() {
		parent::__construct();

		$this->theme = 'chrome';
		$this->mode = 'text';
		$this->fontSize = 14;

		$this->preInit();
		$this->mount(fn () => $this->postInit());
	}

	/**
	 * Set the editor theme.
	 * See all themes available: https://github.com/ajaxorg/ace/tree/master/src/theme
	 *
	 * @param string $theme Theme name: 'monokai', 'tomorrow_night', 'dracula' etc. Default: 'chrome'
	 */
	public function setTheme($theme) {
		$this->theme = $theme;
	}

	/**
	 * Set the editor mode.
	 * See all modes available: https://github.com/ajaxorg/ace/tree/master/src/mode
	 *
	 * @param string $mode Mode name: 'html', 'css', 'php' etc. Default: 'text'
	 */
	public function setMode($mode) {
		$this->mode = $mode;
	}

	/**
	 * Set the editor font size.
	 *
	 * @param int|string $fontSize Px number or css font-size string
	 */
	public function setFontSize($fontSize) {
		$this->fontSize = $fontSize;
	}

	private function preInit() {
		$this->setTemplateFile(__DIR__ . '/CodeArea/CodeArea.tpl', 'php');
		$cdn = $this->cdn . '/';
		$this->addJsLink($cdn . 'ace.min.js', ['position' => WebComponent::JS_TOP]);
		$this->addJsLink($cdn . 'ext-beautify.min.js', ['position' => WebComponent::JS_TOP]);
		$this->addJsLink($cdn . 'ext-language_tools.min.js', ['position' => WebComponent::JS_TOP]);
		$this->addJsLink($cdn . 'ext-emmet.min.js', ['position' => WebComponent::JS_TOP]);
		$this->addJsLink('https://cdn.jsdelivr.net/npm/emmet-core/emmet.min.js', ['position' => WebComponent::JS_TOP]);
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
			'FONT_SIZE'    => is_int($this->fontSize) ? $this->fontSize . 'px' : $this->fontSize,
		]);
		$this->addJsCode($js, ['position' => WebComponent::JS_TOP]);
	}

}