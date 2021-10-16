<?php
namespace Sy\Bootstrap\Application;

class User extends \Sy\Component\WebComponent {

	public function __construct() {
		parent::__construct();
		$this->addTranslator(LANG_DIR);
		$this->actionDispatch(ACTION_TRIGGER);
	}

	public function signOutAction() {
		$service = \Sy\Bootstrap\Service\Container::getInstance();
		$service->user->signOut();
		$url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : WEB_ROOT . '/';
		$this->redirect($url);
	}

	public function activateAction() {
		try {
			$service = \Sy\Bootstrap\Service\Container::getInstance();
			$service->user->activate($this->get('email'), $this->get('token'));
			$service->user->signIn($this->get('email'), $this->get('password'));

			// Clear place cache required if user activate his account after posting a review or a message
			$service->place->clearCache(['retrieve']);

			\Sy\Bootstrap\Lib\FlashMessage::setMessage($this->_('You are connected'));
		} catch(\Sy\Bootstrap\Service\User\ActivateAccountException $e) {
			$this->logWarning($e);
			\Sy\Bootstrap\Lib\FlashMessage::setError($this->_('Activation error'));
		} catch(\Sy\Db\MySql\Exception $e) {
			$this->logWarning($e);
			\Sy\Bootstrap\Lib\FlashMessage::setError($this->_('Database error'));
		} finally {
			$this->redirect(WEB_ROOT . '/');
		}
	}

	/**
	 * Report a unwanted sign up
	 */
	public function reportAction() {
		try {
			$service = \Sy\Bootstrap\Service\Container::getInstance();
			$service->user->report($this->get('email'), $this->get('token'));
			\Sy\Bootstrap\Lib\FlashMessage::setMessage($this->_('Thanks for your report'));
		} catch(\Sy\Bootstrap\Service\User\ActivateAccountException $e) {
			$this->logWarning($e);
			\Sy\Bootstrap\Lib\FlashMessage::setError($this->_('Report error'));
		} catch(\Sy\Db\MySql\Exception $e) {
			$this->logWarning($e);
			\Sy\Bootstrap\Lib\FlashMessage::setError($this->_('Database error'));
		} finally {
			$this->redirect(WEB_ROOT . '/');
		}
	}

}