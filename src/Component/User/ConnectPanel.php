<?php
namespace Sy\Bootstrap\Component\User;

use Sy\Bootstrap\Lib\Url;

class ConnectPanel extends \Sy\Component\WebComponent {

	public function __toString() {
		$this->init();
		return parent::__toString();
	}

	private function init() {
		$this->addTranslator(LANG_DIR);
		$service = \Sy\Bootstrap\Lib\ServiceContainer::getInstance();

		\Sy\Bootstrap\Lib\HeadData::setTitle($this->_('Sign In'));

		$this->setTemplateFile(__DIR__ . '/ConnectPanel.html');
		$this->setVars([
			'COLLAPSED_SIGNIN' => 'collapsed',
			'COLLAPSED_SIGNUP' => 'collapsed',
			'COLLAPSED_FORGET' => 'collapsed',
			'IN_SIGNIN' => '',
			'IN_SIGNUP' => '',
			'IN_FORGET' => '',
			'USE_URL' => Url::build('page', 'use'),
		]);
		switch ($this->request('panel')) {
			case 'signup':
				$this->setVars([
					'COLLAPSED_SIGNUP' => '',
					'IN_SIGNUP' => 'show',
				]);
				break;
			case 'forget':
				$this->setVars([
					'COLLAPSED_FORGET' => '',
					'IN_FORGET' => 'show',
				]);
				break;
			default:
				$this->setVars([
					'COLLAPSED_SIGNIN' => '',
					'IN_SIGNIN' => 'show',
				]);
				break;
		}
		$this->setComponent('SIGN_IN_FORM', new \Sy\Bootstrap\Component\User\SignIn());
		$this->setComponent('SIGN_UP_FORM', new \Sy\Bootstrap\Component\User\SignUp());
		$this->setComponent('FORGET_PASSWORD_FORM', new \Sy\Bootstrap\Component\User\ForgetPassword());
	}
}
