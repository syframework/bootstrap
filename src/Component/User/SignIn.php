<?php
namespace Sy\Bootstrap\Component\User;

use Sy\Bootstrap\Lib\Url;

class SignIn extends \Sy\Bootstrap\Component\Form {

	public function init() {
		parent::init();

		// Redirection url
		Url::setReferer(Url::getReferer() ?? $_SERVER['HTTP_REFERER'] ?? WEB_ROOT . '/');

		// Anti spam and CSRF field
		$this->addAntiSpamField();
		$this->addCsrfField();

		$fieldset = $this->addFieldset();
		$this->addEmail(
			[
				'id'       => 'signin-email',
				'name'     => 'email',
				'required' => 'required',
			],
			[
				'label'          => 'E-mail',
				'floating-label' => true,
			],
			$fieldset
		);
		$this->addPassword(
			[
				'id'       => 'signin-password',
				'name'     => 'password',
				'required' => 'required',
			],
			[
				'label'          => 'Password',
				'floating-label' => true,
			],
			$fieldset
		);
		$this->addButton('Sign In', ['type' => 'submit', 'class' => 'w-100'], ['color' => 'primary', 'icon' => 'power'], $fieldset);
	}

	public function submitAction() {
		try {
			$this->validatePost();
			$redirection = Url::getReferer();
			$service = \Project\Service\Container::getInstance();
			$service->user->signIn($this->post('email'), $this->post('password'));
			$this->setSuccess($this->_('You are connected'), $redirection);
		} catch (\Sy\Component\Html\Form\Exception $e) {
			$this->logWarning($e);
			$this->setError($this->_('Please fill the form correctly'));
			$this->fill($_POST);
		} catch (\Sy\Bootstrap\Service\User\ActivateAccountException $e) {
			$this->logWarning($e);
			$this->setError($this->_('Account not activated'));
		} catch (\Sy\Bootstrap\Service\User\SignInException $e) {
			$this->logWarning($e);
			$this->setError($this->_('ID or password error'));
		} catch (\Sy\Db\MySql\Exception $e) {
			$this->logWarning($e);
			$this->setError($this->_('Error'));
		}
	}

}