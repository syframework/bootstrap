<?php
namespace Sy\Bootstrap\Component\User;

class ResetPassword extends \Sy\Bootstrap\Component\Form {

	private $email;

	public function __construct($email) {
		parent::__construct();
		$this->email = $email;
	}

	public function init() {
		parent::init();

		$this->addPassword(
			[
				'name'         => 'new_password',
				'required'     => 'required',
				'autocomplete' => 'new-password',
				'minlength'    => 8,
			],
			[
				'label'          => 'New password',
				'floating-label' => true,
				'validator'      => [$this, 'passwordValidator'],
			]
		);
		$this->addPassword(
			[
				'name'         => 'new_password_bis',
				'required'     => 'required',
				'autocomplete' => 'new-password',
				'minlength'    => 8,
			],
			[
				'label'          => 'Confirm new password',
				'floating-label' => true,
				'validator'      => function($value) {
					$password = $this->post('new_password');
					if (!empty($value) and $value === $password) return true;
					$this->setError($this->_('Password error'));
					return false;
				},
			]
		);
		$this->addHidden(['name' => 'email', 'value' => $this->email]);
		$this->addButton('Save', ['type' => 'submit'], ['color' => 'primary', 'icon' => 'save']);
	}

	public function submitAction() {
		try {
			$this->validatePost();
			$service = \Project\Service\Container::getInstance();
			$service->user->update(['email' => $this->post('email')], ['password' => password_hash($this->post('new_password'), PASSWORD_DEFAULT), 'token' => '']);
			$service->user->signIn($this->post('email'), $this->post('new_password'));
			return $this->jsonSuccess('You are connected', ['redirection' => PROJECT_URL]);
		} catch (\Sy\Component\Html\Form\Exception $e) {
			$this->logWarning($e);
			return $this->jsonError($this->getOption('error') ?? 'Please fill the form correctly');
		} catch (\Sy\Bootstrap\Service\User\Exception $e) {
			$this->logWarning($e->getMessage());
			return $this->jsonError($e->getMessage());
		} catch (\Sy\Db\MySql\Exception $e) {
			$this->logWarning($e->getMessage());
			return $this->jsonError('Database error');
		}
	}

	public function passwordValidator($value) {
		if (strlen($value) < 8) {
			$this->setError($this->_('Password too short'));
			return false;
		}
		return true;
	}

}