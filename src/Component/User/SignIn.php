<?php
namespace Sy\Bootstrap\Component\User;

class SignIn extends \Sy\Bootstrap\Component\Form {

	public function init() {
		parent::init();

		// Anti spam and CSRF field
		$this->addAntiSpamField();
		$this->addCsrfField();

		$f = $this->addFieldset();
		$this->addEmail(
			[
				'name'     => 'email',
				'required' => 'required',
			],
			['label' => $this->_('E-mail')], $f
		);
		$this->addPassword(
			[
				'name'     => 'password',
				'required' => 'required',
			],
			['label' => $this->_('Password')], $f
		);
		$this->addButton('Sign In', ['type' => 'submit'], ['color' => 'primary', 'icon' => 'fas fa-power-off', 'size' => 'block'], $f);
	}

	public function submitAction() {
		try {
			$this->validatePost();
			$service = \Sy\Bootstrap\Service\Container::getInstance();
			$service->user->signIn($this->post('email'), $this->post('password'));
			$this->setSuccess($this->_('You are connected'), \Sy\Bootstrap\Lib\Url::getReferer());
		} catch (\Sy\Component\Html\Form\Exception $e) {
			$this->logWarning($e);
			$this->setError($this->_('Please fill the form correctly'));
			$this->fill($_POST);
		} catch (\Sy\Bootstrap\Lib\Service\User\ActivateAccountException $e) {
			$this->logWarning($e);
			$this->setError($this->_('Account not activated'));
		} catch (\Sy\Bootstrap\Lib\Service\User\SignInException $e) {
			$this->logWarning($e);
			$this->setError($this->_('ID or password error'));
		} catch (\Sy\Bootstrap\Lib\Crud\Exception $e) {
			$this->logWarning($e);
			$this->setError($this->_('Error'));
		}
	}

}