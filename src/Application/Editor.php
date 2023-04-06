<?php
namespace Sy\Bootstrap\Application;

class Editor extends \Sy\Bootstrap\Component\Api {

	use Editor\CkFile;

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
		// Check if a plugin editor class exists
		$class = 'Sy\\Bootstrap\\Application\\Editor\\' . $this->action;
		if (class_exists($class)) {
			$this->setVar('RESPONSE', new $class());
			return;
		}
		parent::dispatch();
	}

}