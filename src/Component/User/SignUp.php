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
				'id'       => 'signup-email',
				'name'     => 'email',
				'required' => 'required',
			],
			[
				'label'          => 'E-mail',
				'floating-label' => true
			]
		);

		$this->addButton('Sign Up', ['class' => 'w-100'], ['color' => 'primary', 'icon' => 'fas fa-check']);
	}

	public function submitAction() {
		try {
			$this->validatePost();
			$service = \Sy\Bootstrap\Service\Container::getInstance();
			$service->user->signUp(strtolower(trim($this->post('email'))));
			$this->setSuccess($this->_('Account created successfully'), null, 0);
		} catch (\Sy\Component\Html\Form\Exception $e) {
			$this->logWarning($e);
			$this->setError($this->_('Please fill the form correctly'));
			$this->fill($_POST);
		} catch (\Sy\Db\MySql\DuplicateEntryException $e) {
			$this->logWarning($e->getMessage());
			$this->fill($_POST);
			$this->setError($this->_('Account already exists'));
		} catch (\Sy\Bootstrap\Service\User\SignUpException $e) {
			$this->logWarning($e->getMessage());
			$this->fill($_POST);
			$this->setError($this->_('An error occured'));
		}
	}

}