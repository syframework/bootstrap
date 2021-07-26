<?php
namespace Sy\Bootstrap\Component;

abstract class Api extends \Sy\Component\WebComponent {

	protected $action;

	protected $method;

	protected $param;

	public function __construct() {
		try {
			parent::__construct();
			$this->action = $this->request(ACTION_TRIGGER);
			$param = $this->request(ACTION_PARAM, ['']);
			$this->method = array_shift($param);
			$this->param = $param;
			$this->addTranslator(LANG_DIR);
			$this->security();
			$this->dispatch();
		} catch(\Throwable $e) {
			$this->serverError(['message' => $e->getMessage()]);
		}
	}

	abstract public function security();

	public function dispatch() {
		$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null;
		if (empty($method)) $this->notFound();
		$method .= 'Action';
		$this->$method();
		$this->notFound();
	}

	public function response($code, $data = array()) {
		http_response_code($code);
		if (!empty($data)) {
			header('Content-Type: application/json');
			echo json_encode($data);
		}
		exit;
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
		$this->forbidden();
	}

}