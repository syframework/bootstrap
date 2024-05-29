<?php
namespace Sy\Bootstrap\Component;

use Sy\Bootstrap\Lib\Str;

abstract class Api extends \Sy\Component\WebComponent {

	protected $action;

	protected $method;

	protected $verb;

	protected $param;

	/**
	 * Throw an exception if do not pass security check
	 *
	 * @throws \Throwable
	 */
	abstract public function security();

	public function __construct() {
		try {
			parent::__construct();
			$this->setTemplateContent('{RESPONSE}');
			$this->action = ucfirst(Str::snakeToCaml($this->request(ACTION_TRIGGER, '')));
			$param = $this->request(ACTION_PARAM, ['']);
			$this->method = array_shift($param);
			$this->param = $param;

			// HTTP method
			$this->verb = isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : '';

			$this->addTranslator(LANG_DIR);
			$this->security();
			$this->dispatch();
		} catch (Api\NotFoundException $e) {
			$this->notFound(['message' => $e->getMessage()]);
		} catch (Api\ForbiddenException $e) {
			$this->forbidden(['message' => $e->getMessage()]);
		} catch (Api\RequestErrorException $e) {
			$this->requestError(['message' => $e->getMessage()]);
		} catch (\Throwable $e) {
			$this->serverError(['message' => $e->getMessage()]);
		}
	}

	public function dispatch() {
		// 1. We are in the short named class [$this->action]. Call method [$this->method]().
		if (((new \ReflectionClass($this))->getShortName() === $this->action) and !empty($this->method)) {
			$method = Str::snakeToCaml($this->method);
			if (!method_exists($this, $method)) {
				throw new Api\NotFoundException('Method ' . $method . ' not found in class ' . get_class($this));
			}
			return $this->$method();
		}

		// 2. Call method [$this->action]Action()
		if (!empty($this->action)) {
			$method = Str::snakeToCaml($this->action) . 'Action';
			if (method_exists($this, $method)) {
				return $this->$method();
			}
		}

		// 3. Call [$this->verb]Action() where verb is HTTP method: get, post etc...
		if (empty($this->verb)) throw new Api\Exception('No HTTP method defined');
		$method = $this->verb . 'Action';
		if (method_exists($this, $method)) {
			return $this->$method();
		}
		throw new Api\NotFoundException('No action method found');
	}

	/**
	 * Set the API response
	 *
	 * @param int $code
	 * @param array|string|\Stringable
	 */
	public function response($code, $data = null) {
		http_response_code($code);
		if (!empty($data) and is_array($data)) {
			header('Content-Type: application/json');
			$this->setVar('RESPONSE', json_encode($data));
			return;
		}
		if (!empty($data)) {
			$this->setVar('RESPONSE', $data);
			return;
		}
		$this->setVar('RESPONSE', '{}');
	}

	/**
	 * @param array|string|\Stringable
	 */
	public function requestError($data = null) {
		$this->response(400, $data);
	}

	/**
	 * @param array|string|\Stringable
	 */
	public function serverError($data = null) {
		$this->response(500, $data);
	}

	/**
	 * @param array|string|\Stringable
	 */
	public function notFound($data = null) {
		$this->response(404, $data);
	}

	/**
	 * @param array|string|\Stringable
	 */
	public function forbidden($data = null) {
		$this->response(403, $data);
	}

	/**
	 * @param array|string|\Stringable
	 */
	public function ok($data = null) {
		$this->response(200, $data);
	}

	public function __call($name, $arguments) {
		$this->notFound();
	}

}