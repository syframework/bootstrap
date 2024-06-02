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
				'floating-label' => true,
			]
		);

		$this->addButton('Sign Up', ['class' => 'w-100'], ['color' => 'primary', 'icon' => 'check']);
	}

	public function submitAction() {
		try {
			$this->validatePost();
			$service = \Project\Service\Container::getInstance();
			$service->user->signUp(strtolower(trim($this->post('email'))));
			return $this->jsonSuccess('Account created successfully', ['autohide' => false]);
		} catch (\Sy\Component\Html\Form\Exception $e) {
			$this->logWarning($e);
			return $this->jsonError($this->getOption('error') ?? 'Please fill the form correctly');
		} catch (\Sy\Bootstrap\Service\User\Exception $e) {
			$this->logWarning($e->getMessage());
			return $this->jsonError($e->getMessage());
		}
	}

}