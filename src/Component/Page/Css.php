<?php
namespace Sy\Bootstrap\Component\Page;

class Css extends \Sy\Bootstrap\Component\Form {

	private $id;

	public function __construct($id) {
		$this->id = $id;
		parent::__construct();
	}

	public function init() {
		parent::init();

		$this->setAttribute('id', 'form_css_' . $this->id);

		$codeArea = new \Sy\Bootstrap\Component\Form\Element\CodeArea();
		$codeArea->setAttributes([
			'name' => 'css',
			'id'   => 'codearea_css_' . $this->id,
			'placeholder' => 'CSS Code here...'
		]);
		$codeArea->setMode('text/css');
		$codeArea->setParams([
			'addon/display/placeholder',
			'addon/fold/foldcode',
			'addon/fold/foldgutter',
			'addon/fold/brace-fold',
			'addon/fold/comment-fold',
			'addon/hint/show-hint',
			'addon/hint/css-hint',
			'addon/edit/closebrackets',
			'addon/edit/matchbrackets',
			'mode/css/css'
		]);

		// css hack
		$this->addCssCode('.CodeMirror-hints {z-index: 1051;}');

		if (file_exists(TPL_DIR . "/Application/Page/css/$this->id.css")) {
			$codeArea->addText(file_get_contents(TPL_DIR . "/Application/Page/css/$this->id.css"));
		}

		$this->addElement($codeArea);
	}

	public function submitAction() {
		try {
			$this->validatePost();
			if (!file_exists(TPL_DIR . '/Application/Page/css')) {
				mkdir(TPL_DIR . '/Application/Page/css');
			}
			file_put_contents(TPL_DIR . "/Application/Page/css/$this->id.css", $this->post('css'));
			$this->setSuccess($this->_('CSS updated successfully'));
		} catch(\Sy\Component\Html\Form\Exception $e) {
			$this->logWarning($e);
			$this->setError($this->_('Please fill the form correctly'));
			$this->fill($_POST);
		} catch(\Sy\Db\MySql\Exception $e) {
			$this->logWarning($e);
			$this->setError($this->_('Database error'));
			$this->fill($_POST);
		}
	}

}