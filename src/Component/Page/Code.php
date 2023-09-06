<?php
namespace Sy\Bootstrap\Component\Page;

use Sy\Bootstrap\Component\Form\Element\CodeArea;
use Sy\Bootstrap\Lib\Str;

class Code extends \Sy\Bootstrap\Component\Form {

	private $id;

	private $lang;

	/**
	 * @var CodeArea
	 */
	private $cssArea;

	/**
	 * @var CodeArea
	 */
	private $jsArea;

	public function __construct($id, $lang) {
		parent::__construct();
		$this->id = $id;
		$this->lang = $lang;
	}

	public function init() {
		$this->addCssCode('
			#sy-code-modal .ace_editor {font-size: 14px;}
			#sy-code-modal div.alert {display: none;}
		');

		$this->setAttributes([
			'id'    => 'code_form_' . $this->id,
			'class' => 'tab-content',
		]);

		// HTML
		$htmlArea = new CodeArea();
		$htmlArea->setAttributes([
			'name'        => 'html',
			'id'          => 'codearea_html_' . $this->id,
			'placeholder' => 'HTML Code here...',
		]);
		$htmlArea->setMode('php');
		$htmlArea->setTheme('monokai');

		$this->addDiv([
			'class'           => 'tab-pane fade show active',
			'id'              => 'sy-html-tab-content',
			'role'            => 'tabpanel',
			'aria-labelledby' => 'html-tab',
		])->addElement($htmlArea);

		// CSS
		$cssArea = new CodeArea();
		$cssArea->setAttributes([
			'name'        => 'css',
			'id'          => 'codearea_css_' . $this->id,
			'placeholder' => 'CSS Code here...',
		]);
		$cssArea->setMode('scss');
		$cssArea->setTheme('monokai');
		$this->cssArea = $cssArea;

		if (file_exists(TPL_DIR . "/Application/Page/css/$this->id.scss")) {
			$cssArea->addText(Str::escape(file_get_contents(TPL_DIR . "/Application/Page/css/$this->id.scss")));
		}

		$this->addDiv([
			'class'           => 'tab-pane fade',
			'id'              => 'sy-css-tab-content',
			'role'            => 'tabpanel',
			'aria-labelledby' => 'css-tab',
		])->addElement($cssArea);

		// JS
		$jsArea = new CodeArea();
		$jsArea->setAttributes([
			'name'        => 'js',
			'id'          => 'codearea_js_' . $this->id,
			'placeholder' => 'JS Code here...',
		]);
		$jsArea->setMode('javascript');
		$jsArea->setTheme('monokai');
		$this->jsArea = $jsArea;

		if (file_exists(TPL_DIR . "/Application/Page/js/$this->id.js")) {
			$jsArea->addText(Str::escape(file_get_contents(TPL_DIR . "/Application/Page/js/$this->id.js")));
		}

		$this->addDiv([
			'class'           => 'tab-pane fade',
			'id'              => 'sy-js-tab-content',
			'role'            => 'tabpanel',
			'aria-labelledby' => 'js-tab',
		])->addElement($jsArea);
	}

	public function submitAction() {
		try {
			$this->validatePost();

			// HTML
			if (!file_exists(TPL_DIR . "/Application/Page/content/$this->lang")) {
				mkdir(TPL_DIR . "/Application/Page/content/$this->lang");
			}
			if (!empty($this->post('html'))) {
				file_put_contents(TPL_DIR . "/Application/Page/content/$this->lang/$this->id.html", $this->post('html'));
			}

			// CSS
			if (!file_exists(TPL_DIR . '/Application/Page/css')) {
				mkdir(TPL_DIR . '/Application/Page/css');
			}
			if (!empty($this->post('css'))) {
				file_put_contents(TPL_DIR . "/Application/Page/css/$this->id.scss", $this->post('css'));
			}

			// JS
			if (!file_exists(TPL_DIR . '/Application/Page/js')) {
				mkdir(TPL_DIR . '/Application/Page/js');
			}
			if (!empty($this->post('js'))) {
				file_put_contents(TPL_DIR . "/Application/Page/js/$this->id.js", $this->post('js'));
			}

			$this->setSuccess($this->_('Source code updated successfully'));
		} catch (\Sy\Component\Html\Form\Exception $e) {
			$this->logWarning($e);
			$this->setError($this->_('Please fill the form correctly'));
			$this->fill($_POST);
		} catch (\Sy\Db\MySql\Exception $e) {
			$this->logWarning($e);
			$this->setError($this->_('Database error'));
			$this->fill($_POST);
		}
	}

}