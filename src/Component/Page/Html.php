<?php
namespace Sy\Bootstrap\Component\Page;

class Html extends \Sy\Bootstrap\Component\Form {

	private $id;
	private $lang;

	public function __construct($id, $lang) {
		$this->id = $id;
		$this->lang = $lang;
		parent::__construct();
	}

	public function init() {
		parent::init();

		$this->setAttribute('id', 'form_html_' . $this->id);

		$codeArea = new \Sy\Bootstrap\Component\Form\Element\CodeArea();
		$codeArea->setAttributes([
			'name' => 'content',
			'id'   => 'codearea_html_' . $this->id,
			'placeholder' => 'HTML Code here...'
		]);
		$codeArea->setMode('text/html');
		$codeArea->setParams([
			'addon/display/placeholder',
			'addon/fold/foldcode',
			'addon/fold/foldgutter',
			'addon/fold/xml-fold',
			'addon/fold/comment-fold',
			'addon/edit/matchtags',
			'addon/edit/closetag',
			'addon/hint/show-hint',
			'addon/hint/xml-hint',
			'addon/hint/html-hint',
			'mode/xml/xml',
			'mode/javascript/javascript',
			'mode/css/css',
			'mode/htmlmixed/htmlmixed'
		]);

		// css hack
		$this->addCssCode('.CodeMirror-hints {z-index: 1051;}');

		$this->addElement($codeArea);
	}

	public function submitAction() {
		try {
			$this->validatePost();
			file_put_contents(TPL_DIR . "/Application/Page/content/$this->lang/$this->id.html", $this->post('content'));
			$this->setSuccess($this->_('HTML updated successfully'));
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