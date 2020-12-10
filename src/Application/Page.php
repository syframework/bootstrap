<?php
namespace Sy\Bootstrap\Application;

class Page extends \Sy\Bootstrap\Component\Page {

	/**
	 * User connection page
	 */
	public function user_connection() {
		$service = \Sy\Bootstrap\Service\Container::getInstance();
		if ($service->user->getCurrentUser()->isConnected()) {
			$this->redirect(WEB_ROOT . '/');
		}
		$this->__call('user-connection', ['CONTENT' => [
			'CONNECT_PANEL' => new \Sy\Bootstrap\Component\User\ConnectPanel(),
		]]);
		Url::setReferer(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : WEB_ROOT . '/');
	}

	/**
	 * User reset password page
	 */
	public function user_password() {
		$service = \Sy\Bootstrap\Service\Container::getInstance();
		$user = $service->user->retrieve(['email' => $this->get('email')]);
		if (empty($user) or $user['status'] !== 'active' or $this->get('token') !== $user['token']) {
			$this->redirect(WEB_ROOT . '/');
		}
		$this->__call('user-password', ['CONTENT' => [
			'FORM' => new \Sy\Bootstrap\Component\User\ResetPassword($this->get('email'))
		]]);
	}

	/**
	 * Return navigation menu, can return null
	 *
	 * @return \Sy\Bootstrap\Component\Nav\Menu
	 */
	protected function _menu() {
		return null;
	}

}