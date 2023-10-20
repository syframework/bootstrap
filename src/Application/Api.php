<?php
namespace Sy\Bootstrap\Application;

class Api extends \Sy\Bootstrap\Component\Api {

	public function security() {
		$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : null;
		if (empty($origin) and isset($_SERVER['HTTP_REFERER'])) {
			$origin = $_SERVER['HTTP_REFERER'];
		}
		if (empty($origin)) {
			throw new \Sy\Bootstrap\Component\Api\ForbiddenException('No HTTP origin found');
		}
		if ($_SERVER['SERVER_NAME'] !== parse_url($origin)['host']) {
			throw new \Sy\Bootstrap\Component\Api\ForbiddenException('Server name do not match with HTTP origin');
		}
	}

	public function dispatch() {
		// Check if a plugin api class exists
		$class = 'Sy\\Bootstrap\\Application\\Api\\' . $this->action;
		if (class_exists($class)) {
			$this->setVar('RESPONSE', new $class());
			return;
		}
		parent::dispatch();
	}

	/**
	 * Upload avatar
	 */
	public function avatarAction() {
		$service = \Project\Service\Container::getInstance();
		$email = $service->user->getCurrentUser()->email;
		$md5 = md5(strtolower(trim($email)));

		// Csrf check
		if ($service->user->getCsrfToken() !== $this->post('__csrf')) {
			return $this->forbidden(['message' => 'CSRF token error']);
		}

		$fileName = AVATAR_DIR . '/' . "$md5.png";
		try {
			\Sy\Bootstrap\Lib\Upload::proceed($fileName, 'file', '\Sy\Bootstrap\Lib\Image::isImage');
			\Sy\Bootstrap\Lib\Image::resize($fileName, 200, 200, 'png');
			return $this->ok(['message' => 'Upload complete']);
		} catch (\Sy\Bootstrap\Lib\Upload\Exception $e) {
			return $this->serverError(['message' => $e->getMessage()]);
		};
	}

	/**
	 * Retrieve feed next page
	 */
	public function feedAction() {
		$class = $this->get('class');
		if (is_null($class)) {
			return $this->requestError(['message' => 'Missing class parameter']);
		}
		$service = \Project\Service\Container::getInstance();
		$service->lang->setLang($this->get('language'));
		$feed = new $class();
		if (!$feed instanceof \Sy\Bootstrap\Component\Feed) {
			return $this->requestError(['message' => "$class is not an instance of Feed"]);
		}
		return $this->ok($feed);
	}

	/**
	 * For refreshing the csrf form input
	 */
	public function csrfAction() {
		$service = \Project\Service\Container::getInstance();
		$this->ok([
			'csrf' => $service->user->getCsrfToken(),
		]);
	}

}