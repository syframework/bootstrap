<?php
namespace Sy\Bootstrap\Component;

use Sy\Bootstrap\Component\Api\ForbiddenException;
use Sy\Bootstrap\Lib\Str;

abstract class Api extends \Sy\Component\WebComponent {

	protected $action;

	protected $method;

	protected $param;

	public function __construct() {
		try {
			parent::__construct();
			$this->setTemplateContent('{RESPONSE}');
			$this->action = $this->request(ACTION_TRIGGER);
			$param = $this->request(ACTION_PARAM, ['']);
			$this->method = array_shift($param);
			$this->param = $param;
			$this->addTranslator(LANG_DIR);
			$this->security();
			$this->dispatch();
		} catch(ForbiddenException $e) {
			$this->forbidden(['message' => $e->getMessage()]);
		} catch(\Throwable $e) {
			$this->serverError(['message' => $e->getMessage()]);
		}
	}

	abstract public function security();

	public function dispatch() {
		// 1. User xxx() where xxx is the method attribute
		if (!empty($this->method)) {
			$method = Str::snakeToCaml($this->method);
			if (!method_exists($this, $method)) {
				return $this->notFound();
			}
			return $this->$method();
		}

		// 2. Use xxxAction() where xxx is the action attribute
		if (!empty($this->action)) {
			$method = Str::snakeToCaml($this->action) . 'Action';
			if (method_exists($this, $method)) {
				return $this->$method();
			}
		}

		// 3. Use xxxAction() where xxx is HTTP method: get, post etc...
		$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null;
		if (empty($method)) $this->notFound();
		$method .= 'Action';
		if (method_exists($this, $method)) {
			return $this->$method();
		}
		$this->notFound();
	}

	public function response($code, $data = array()) {
		http_response_code($code);
		if (!empty($data)) {
			header('Content-Type: application/json');
			$this->setVar('RESPONSE', json_encode($data));
		}
	}

	public function requestError($data = array()) {
		$this->response(400, $data);
	}

	public function serverError($data = array()) {
		$this->response(500, $data);
	}

	public function notFound($data = array()) {
		$this->response(404, $data);
	}

	public function forbidden($data = array()) {
		$this->response(403, $data);
	}

	public function ok($data = array()) {
		$this->response(200, $data);
	}

	public function __call($name, $arguments) {
		$this->notFound();
	}

}

namespace Sy\Bootstrap\Component\Api;

class Exception extends \Exception {}

class ForbiddenException extends Exception {}