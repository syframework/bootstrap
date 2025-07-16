<?php
namespace Sy\Bootstrap\Application;

use Sy\Bootstrap\Component\Api\ForbiddenException;
use Sy\Bootstrap\Component\Api\NotFoundException;
use Sy\Bootstrap\Lib\Str;

class Api extends \Sy\Bootstrap\Component\Api {

	public function security() {
		$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : null;
		if (empty($origin) and isset($_SERVER['HTTP_REFERER'])) {
			$origin = $_SERVER['HTTP_REFERER'];
		}
		if (empty($origin)) {
			throw new ForbiddenException('No HTTP origin found');
		}
		if ($_SERVER['SERVER_NAME'] !== parse_url($origin)['host']) {
			throw new ForbiddenException('Server name do not match with HTTP origin');
		}
	}

	public function dispatch() {
		$this->security();

		// 1. Method attribute is empty, check if [$this->action]Action method exist
		if (empty($this->method)) {
			$method = Str::snakeToCaml($this->action) . 'Action';
			if (method_exists($this, $method)) {
				return $this->$method();
			}
		}

		$findAction = function ($class, $callback) {
			try {
				if (class_exists($class)) {
					$this->setVar('RESPONSE', new $class());
					return;
				}
				$callback();
			} catch (NotFoundException $e) {
				$callback();
			}
		};

		// 2. Check if a project api class exists
		$findAction('Project\\Application\\Api\\' . $this->action, function () use ($findAction) {
			// 3. Check if a plugin api class exists
			$findAction('Sy\\Bootstrap\\Application\\Api\\' . $this->action, function () {
				throw new NotFoundException('No action method found');
			});
		});
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

		$fileName = AVATAR_DIR . '/' . "$md5.webp";
		try {
			\Sy\Bootstrap\Lib\Upload::proceed($fileName, 'file', '\Sy\Bootstrap\Lib\Image::isImage');
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
		$feed->flush();
		return $this->ok($feed);
	}

	/**
	 * For refreshing the csrf form input
	 */
	public function csrfAction() {
		$service = \Project\Service\Container::getInstance();
		return $this->ok([
			'csrf' => $service->user->getCsrfToken(),
		]);
	}

}