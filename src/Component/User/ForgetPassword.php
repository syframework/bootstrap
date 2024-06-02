<?php
namespace Sy\Bootstrap\Component\User;

class ForgetPassword extends \Sy\Bootstrap\Component\Form {

	public function init() {
		parent::init();

		// Anti spam and CSRF field
		$this->addAntiSpamField();
		$this->addCsrfField();

		// For the panel collapse
		$this->addHidden(['name' => 'panel', 'value' => 'forget']);

		$this->addEmail(
			[
				'id'       => 'forgetpwd-email',
				'name'     => 'email',
				'required' => 'required',
			],
			['label' => 'E-mail', 'floating-label' => true]
		);
		$this->addButton('Send', ['type' => 'submit', 'class' => 'w-100'], ['color' => 'primary', 'icon' => 'paper-plane']);
	}

	public function submitAction() {
		try {
			$this->validatePost();
			$service = \Project\Service\Container::getInstance();
			$service->user->forgetPassword($this->post('email'));
			return $this->jsonSuccess('An e-mail has been sent');
		} catch (\Sy\Component\Html\Form\Exception $e) {
			$this->logWarning($e);
			return $this->jsonError($this->getOption('error') ?? 'Please fill the form correctly');
		} catch (\Sy\Bootstrap\Service\User\ActivateAccountException $e) {
			$this->logWarning($e->getMessage());
			return $this->jsonError('Account not activated');
		} catch (\Sy\Bootstrap\Service\User\Exception $e) {
			$this->logWarning($e->getMessage());
			return $this->jsonError($e->getMessage());
		} catch (\Sy\Db\MySql\Exception $e) {
			$this->logWarning($e->getMessage());
			return $this->jsonError('Database error');
		}
	}

}