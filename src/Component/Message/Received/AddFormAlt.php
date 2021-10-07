<?php
namespace Sy\Bootstrap\Component\Message\Received;

class AddFormAlt extends \Sy\Bootstrap\Component\Form {

	private $itemId;
	private $itemType;

	/**
	 * @var array
	 */
	private $div;

	public function __construct($itemId, $itemType) {
		$this->itemId = $itemId;
		$this->itemType = $itemType;
		$this->div = [];
		parent::__construct();
	}

	public function init() {
		parent::init();

		$this->setOption('error-class', 'alert alert-danger mt-1');

		$this->addHidden(['name' => 'item_id', 'value' => $this->itemId]);
		$this->addHidden(['name' => 'item_type', 'value' => $this->itemType]);

		// Anti spam check
		$this->addAntiSpamField();

		// Crsf check
		$this->addCsrfField();

		// Message textarea
		$this->addTextarea([
			'name'        => 'message',
			'required'    => 'required',
			'maxlength'   => 2048,
			'placeholder' => 'Envoyer un message'
		], [
			'validator' => function($value) {
				$l = strlen($value);
				if ($l >= 2 and $l <= 2048) return true;
				$m = 'max';
				$n = 2048;
				if ($l <= 2048) {
					$m = 'min';
					$n = 2;
				}
				$this->setError(sprintf($this->_("Text $m length of %d characters"), $n));
				return false;
			}
		]);

		// Email
		$email = $this->addEmail([
			'name'        => 'email',
			'required'    => 'required',
			'placeholder' => 'Your e-mail'
		])->getParent();
		$d = $email->addDiv(['class' => 'account-ok']);
		$d->addDiv(['class' => 'help-block'])->addText(
			'<small>' . $this->_('By sending you accept our') . ' <a href="' . \Sy\Bootstrap\Lib\Url::build('page', 'use') . '" target="_blank">' . $this->_('Conditions of use') . '</a></small>'
		);

		// Password
		$password = $this->addPassword([
			'name'        => 'password',
			'placeholder' => 'Your password',
		], [
			'help' => '<a href="' . \Sy\Bootstrap\Lib\Url::build('page', 'user-connection', ['panel' => 'forget']) . '" target="_blank">' . $this->_('Forget password') . '</a>',
		])->getParent();
		$password->setAttribute('style', 'display:none');
		$password->addClass('account-ok');

		// Checkbox
		$this->addCheckbox([
			'class' => 'account-checkbox',
			'name'  => 'account',
			'value' => 'yes'
		], [
			'label' => 'I already have an account'
		]);

		// Submit button
		$div = $this->addDiv(['class' => 'clearfix']);
		$d1 = $div->addDiv(['class' => 'account-ok']);
		$d2 = $div->addDiv(['class' => 'account-ok', 'style' => 'display:none']);
		$this->div['visible'] = [$d, $d1];
		$this->div['hidden'] = [$password, $d2];
		$this->addButton(
			'Sign up and send',
			[
				'type' => 'submit',
				'class'=> 'float-end'
			],
			[
				'size'  => 'sm',
				'color' => 'primary',
				'icon'  => 'fas fa-paper-plane',
			], $d1
		);
		$this->addButton(
			'Sign in and send',
			[
				'type'  => 'submit',
				'class' => 'float-end',
			],
			[
				'size'  => 'sm',
				'color' => 'primary',
				'icon'  => 'fas fa-paper-plane',
			], $d2
		);

		// JS
		$this->addJsCode(file_get_contents(__DIR__ . '/AddFormAlt.js'));
	}

	public function submitAction() {
		try {
			$this->validatePost();

			$service = \Project\Service\Container::getInstance();

			$email = strtolower(trim($this->post('email')));

			// Sign up
			if (is_null($this->post('account'))) {
				$service->user->signUp($email);
				$success = ['title' => $this->_('Message not published yet'), 'message' => $this->_('Please activate your account to publish your message')];
				$timeout = 0;
			// Sign in
			} else {
				$service->user->signIn($email, $this->post('password'));
				$success = $this->_('You are connected and your message has been posted');
				$timeout = 3500;
			}

			// Create message
			$user = $service->user->retrieve(['email' => $email]);
			$service->messageReceived->create([
				'user_id'   => $user['id'],
				'item_id'   => $this->itemId,
				'item_type' => $this->itemType,
				'message'   => $this->post('message'),
				'ip'        => sprintf("%u", ip2long($_SERVER['REMOTE_ADDR']))
			]);
			$this->setSuccess($success, null, $timeout);
		} catch(\Sy\Bootstrap\Component\Form\CsrfException $e) {
			$this->logWarning($e);
			$this->setError($e->getMessage());
			$this->fillOnError();
		} catch(\Sy\Component\Html\Form\Exception $e) {
			$this->logWarning($e);
			$this->setError(is_null($this->getOption('error')) ? $this->_('Please fill the form correctly') : $this->getOption('error'));
			$this->fillOnError();
		} catch(\Sy\Bootstrap\Service\Crud\Exception $e) {
			$this->logWarning($e);
			$this->setError($this->_('Error'));
			$this->fillOnError();
		} catch (\Sy\Bootstrap\Service\User\ActivateAccountException $e) {
			$this->logWarning($e);
			$this->setError($this->_('Account not activated'));
			$this->fillOnError();
		} catch (\Sy\Bootstrap\Service\User\SignInException $e) {
			$this->logWarning($e);
			$this->setError($this->_('ID or password error'));
			$this->fillOnError();
		} catch (\Sy\Bootstrap\Lib\Crud\DuplicateEntryException $e) {
			$this->logWarning($e);
			$this->setError($this->_('Account already exists'));
			$_POST['account'] = 'yes';
			$this->fillOnError();
		} catch (\Sy\Bootstrap\Service\User\SignUpException $e) {
			$this->logWarning($e);
			$this->setError($this->_('An error occured'));
			$this->fillOnError();
		}
	}

	private function fillOnError() {
		if ($this->post('account') === 'yes') {
			$this->div['visible'][0]->setAttribute('hidden', 'hidden');
			$this->div['visible'][1]->setAttribute('hidden', 'hidden');
			$this->div['hidden'][0]->unsetAttribute('hidden');
			$this->div['hidden'][1]->unsetAttribute('hidden');
		}
		$this->fill($_POST);
	}

}
