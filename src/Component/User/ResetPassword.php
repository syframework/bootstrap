<?php
namespace Sy\Bootstrap\Component\User;

class ResetPassword extends \Sy\Bootstrap\Component\Form {

	private $email;

	public function __construct($email) {
		$this->email = $email;
		parent::__construct();
	}

	public function init() {
		parent::init();

		$this->addPassword(
			[
				'name'     => 'new_password',
				'required' => 'required',
				'autocomplete' => 'new-password',
			],
			[
				'label'     => 'New password',
				'validator' => [$this, 'passwordValidator']
			]
		);
		$this->addPassword(
			[
				'name'     => 'new_password_bis',
				'required' => 'required',
				'autocomplete' => 'new-password',
			],
			[
				'label' => 'Confirm new password',
				'validator' => function($value) {
					$password = $this->post('new_password');
					if (!empty($value) and $value === $password) return true;
					$this->setError($this->_('Password error'));
					return false;
				}
			]
		);
		$this->addHidden(['name' => 'email', 'value' => $this->email]);
		$this->addButton('Save', ['type' => 'submit'], ['color' => 'primary', 'icon' => 'fas fa-save']);
	}

	public function submitAction() {
		try {
			$this->validatePost();
			$service = \Sy\Bootstrap\Service\Container::getInstance();
			$service->user->update(['email' => $this->post('email')], ['password' => password_hash($this->post('new_password'), PASSWORD_DEFAULT), 'token' => '']);
			$service->user->signIn($this->post('email'), $this->post('new_password'));
			$this->setSuccess($this->_('You are connected'), PROJECT_URL);
		} catch (\Sy\Component\Html\Form\Exception $e) {
			$this->logWarning($e);
			$this->setError(is_null($this->getOption('error')) ? $this->_('Please fill the form correctly') : $this->getOption('error'));
		} catch(\Sy\Bootstrap\Service\User\Exception $e) {
			$this->logWarning($e->getMessage());
			$this->setError($this->_('Error'));
		} catch (\Sy\Db\MySql\Exception $e) {
			$this->logWarning($e->getMessage());
			$this->setError($this->_('Database error'));
		}
	}

	public function passwordValidator($value) {
		if (strlen($value) < 6) {
			$this->setError($this->_('Password too short'));
			return false;
		}
		return true;
	}

}