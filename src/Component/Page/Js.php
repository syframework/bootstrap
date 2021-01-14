<?php
namespace Sy\Bootstrap\Component\Page;

class Js extends \Sy\Bootstrap\Component\Form {

	private $id;

	public function __construct($id) {
		$this->id = $id;
		parent::__construct();
	}

	public function init() {
		parent::init();

		$this->setAttribute('id', 'form_js_' . $this->id);

		$codeArea = new \Sy\Bootstrap\Component\Form\Element\CodeArea();
		$codeArea->setAttributes([
			'name' => 'js',
			'id'   => 'codearea_js_' . $this->id,
			'placeholder' => 'JS Code here...'
		]);
		$codeArea->setMode('text/javascript');
		$codeArea->setParams([
			'addon/display/placeholder',
			'addon/fold/foldcode',
			'addon/fold/foldgutter',
			'addon/fold/brace-fold',
			'addon/fold/comment-fold',
			'addon/hint/show-hint',
			'addon/hint/javascript-hint',
			'addon/edit/closebrackets',
			'addon/edit/matchbrackets',
			'mode/javascript/javascript',
		]);

		// css hack
		$this->addCssCode('.CodeMirror-hints {z-index: 1051;}');

		if (file_exists(TPL_DIR . "/Application/Page/js/$this->id.js")) {
			$codeArea->addText(file_get_contents(TPL_DIR . "/Application/Page/js/$this->id.js"));
		}

		$this->addElement($codeArea);
	}

	public function submitAction() {
		try {
			$this->validatePost();
			if (!file_exists(TPL_DIR . '/Application/Page/js')) {
				mkdir(TPL_DIR . '/Application/Page/js');
			}
			file_put_contents(TPL_DIR . "/Application/Page/js/$this->id.js", $this->post('js'));
			$this->setSuccess($this->_('JS updated successfully'));
		} catch(\Sy\Component\Html\Form\Exception $e) {
			$this->logWarning($e);
			$this->setError($this->_('Please fill the form correctly'));
			$this->fill($_POST);
		} catch(\Sy\Bootstrap\Service\Crud\Exception $e) {
			$this->logWarning($e);
			$this->setError($this->_('Database error'));
			$this->fill($_POST);
		}
	}

}