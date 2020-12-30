<?php
namespace Sy\Bootstrap\Component\User;

class SignUp extends \Sy\Bootstrap\Component\Form {

	public function init() {
		parent::init();

		// Anti spam and CSRF field
		$this->addAntiSpamField();
		$this->addCsrfField();

		// For the panel collapse
		$this->addHidden(['name' => 'panel', 'value' => 'signup']);

		// E-mail field
		$this->addEmail(
			[
				'name'     => 'email',
				'required' => 'required',
			],
			[
				'label' => 'E-mail'
			]
		);

		$this->addButton('Sign Up', [], ['color' => 'primary', 'icon' => 'fas fa-check', 'size' => 'block']);
	}

	public function submitAction() {
		try {
			$this->validatePost();
			$service = \Sy\Bootstrap\Lib\ServiceContainer::getInstance();
			$service->user->signUp(strtolower(trim($this->post('email'))));
			$this->setSuccess($this->_('Account created successfully'), null, 0);
		} catch (\Sy\Component\Html\Form\Exception $e) {
			$this->logWarning($e);
			$this->setError($this->_('Please fill the form correctly'));
			$this->fill($_POST);
		} catch (\Sy\Bootstrap\Lib\Crud\DuplicateEntryException $e) {
			$this->logWarning($e->getMessage());
			$this->fill($_POST);
			$this->setError($this->_('Account already exists'));
		} catch (\Sy\Bootstrap\Lib\Service\User\SignUpException $e) {
			$this->logWarning($e->getMessage());
			$this->fill($_POST);
			$this->setError($this->_('An error occured'));
		}
	}

}