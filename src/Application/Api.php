<?php
namespace Sy\Bootstrap\Application;

use Sy\Bootstrap\Lib\Str;
use Sy\Bootstrap\Service\Container;

class Api extends \Sy\Bootstrap\Component\Api {

	public function security() {
		$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : null;
		if (empty($origin) and isset($_SERVER['HTTP_REFERER'])) {
			$origin = $_SERVER['HTTP_REFERER'];
		}
		if (empty($origin)) {
			$this->forbidden();
		}
		if ($_SERVER['SERVER_NAME'] !== parse_url($origin)['host']) {
			$this->forbidden();
		}
	}

	public function dispatch() {
		$this->actionDispatch(ACTION_TRIGGER);

		// If no action method found, check if a plugin api class exists
		$c = $this->action;
		if (is_null($c)) $this->requestError();
		$class = 'Sy\\Bootstrap\\Application\\Api\\' . ucfirst(Str::snakeToCaml($c));
		if (class_exists($class)) new $class();
	}

	/**
	 * Upload avatar
	 */
	public function avatarAction() {
		$service = Container::getInstance();
		$id = $service->user->getCurrentUser()->id;

		// Csrf check
		if ($service->user->getCsrfToken() !== $this->post('__csrf')) exit;

		$fileName = AVATAR_DIR . '/' . "$id.png";
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
		$service = Container::getInstance();
		$this->ok([
			'csrf' => $service->user->getCsrfToken()
		]);
	}

}