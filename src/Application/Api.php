<?php
namespace Sy\Bootstrap\Application;

use Sy\Bootstrap\Lib\Str;

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
		$class = 'Sy\\Bootstrap\\Application\\Api\\' . ucfirst(Str::snakeToCaml($this->action));
		if (class_exists($class)) new $class();

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
		if ($service->user->getCsrfToken() !== $this->post('__csrf')) exit;

		$fileName = AVATAR_DIR . '/' . "$md5.png";
		\Sy\Bootstrap\Lib\Upload::proceed($fileName, 'file', '\Sy\Bootstrap\Lib\Image::isImage');
		\Sy\Bootstrap\Lib\Image::resize($fileName, 200, 200, 'png');
		exit;
	}

	/**
	 * Retrieve feed next page
	 */
	public function feedAction() {
		$class = $this->get('class');
		if (is_null($class)) exit;
		$feed = new $class();
		if (!$feed instanceof \Sy\Bootstrap\Component\Feed) exit;
		echo $feed;
		exit;
	}

	/**
	 * For refreshing the csrf form input
	 */
	public function csrfAction() {
		$service = \Project\Service\Container::getInstance();
		$this->ok([
			'csrf' => $service->user->getCsrfToken()
		]);
	}

}