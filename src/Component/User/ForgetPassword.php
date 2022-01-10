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
		$this->addButton('Send', ['type' => 'submit', 'class' => 'w-100'], ['color' => 'primary', 'icon' => 'fas fa-paper-plane']);
	}

	public function submitAction() {
		try {
			$this->validatePost();
			$service = \Sy\Bootstrap\Service\Container::getInstance();
			$service->user->forgetPassword($this->post('email'));
			$this->setSuccess($this->_('An e-mail has been sent'));
		} catch (\Sy\Component\Html\Form\Exception $e) {
			$this->logWarning($e);
			$this->setError($this->_('Please fill the form correctly'));
		} catch(\Sy\Bootstrap\Service\User\ActivateAccountException $e) {
			$this->logWarning($e->getMessage());
			$this->setError($this->_('Account not activated'));
		} catch(\Sy\Bootstrap\Service\User\Exception $e) {
			$this->logWarning($e->getMessage());
			$this->setError($this->_('Error'));
		} catch(\Sy\Db\MySql\Exception $e) {
			$this->logWarning($e->getMessage());
			$this->setError($this->_('Database error'));
		}
	}

}