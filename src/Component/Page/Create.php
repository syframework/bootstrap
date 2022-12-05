<?php
namespace Sy\Bootstrap\Component\Page;

class Create extends \Sy\Bootstrap\Component\Form\Crud\Create {

	public function __construct() {
		parent::__construct('page');
	}

	public function init() {
		parent::init();

		// Id
		$this->getField('id')->setAttribute('placeholder', $this->_('Authorized characters:') . ' a-z 0-9');
		$this->getField('id')->addValidator(function($value) {
			if (strlen($value) <= 24) return true;
			$this->setError(sprintf($this->_("%d characters max"), 24));
			return false;
		});
		$this->getField('id')->addValidator(function($value) {
			if (preg_match('/^[a-z0-9\-]*$/', $value) === 1) return true;
			$this->setError($this->_('Unauthorized character in the id'));
			return false;
		});

		// Title
		$this->getField('title')->setAttribute('maxlength', '128');
		$this->getField('title')->addValidator(function($value) {
			if (strlen($value) <= 128) return true;
			$this->setError($this->_('128 characters max for title'));
			return false;
		});

		// Description
		$this->getField('description')->setAttribute('maxlength', '512');
		$this->getField('description')->addValidator(function($value) {
			if (strlen($value) <= 512) return true;
			$this->setError($this->_('512 characters max for description'));
			return false;
		});
	}

	public function submitAction() {
		try {
			$this->validatePost();
			$fields = $this->post('form');
			if (!file_exists(TPL_DIR . '/Application/Page/content/' . $fields['lang'] . '/' . $fields['id'] . '.html')) {
				if (!file_exists(TPL_DIR . '/Application/Page/content/' . $fields['lang'])) {
					mkdir(TPL_DIR . '/Application/Page/content/' . $fields['lang'], 0666, true);
				}
				$content = new Content();
				if (file_exists(TPL_DIR . '/Application/Page/content/_default.html')) {
					$content->setTemplateFile(TPL_DIR . '/Application/Page/content/_default.html');
				}
				$file = TPL_DIR . '/Application/Page/content/' . $fields['lang'] . '/' . $fields['id'] . '.html';
				file_put_contents($file, strval($content));
				chmod($file, 0666);
			}
			$this->getService()->create($fields);
			$this->setSuccess($this->_('Page created successfully'), \Sy\Bootstrap\Lib\Url::build('page', $fields['id']));
		} catch (\Sy\Component\Html\Form\Exception $e) {
			$this->logWarning($e);
			if (is_null($this->getOption('error'))) {
				$this->setError($this->_('Please fill the form correctly'));
			}
			$this->fill($_POST);
		} catch (\Sy\Db\MySql\DuplicateEntryException $e) {
			$this->logWarning($e);
			$this->setError($this->_('Page id already exists'));
			$this->fill($_POST);
		} catch (\Sy\Db\MySql\Exception $e) {
			$this->logWarning($e);
			$this->setError($this->_('Database error'));
			$this->fill($_POST);
		}
	}

}