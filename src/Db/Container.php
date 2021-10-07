<?php
namespace Sy\Bootstrap\Db;

use Sy\Bootstrap\Lib\Str;

/**
 * @method static Container getInstance()
 * @property-read \Sy\Bootstrap\Db\User $user User Db service
 * @property-read \Sy\Bootstrap\Db\Message\Received $messageReceived Message Db service
 * @property-read \Sy\Bootstrap\Db\Message\Reply $messageReply Message reply Db service
 */
class Container extends \Sy\Container {

	public function __construct() {
		parent::__construct();

		$this->user = function () {
			return new \Sy\Bootstrap\Db\User();
		};

		$this->messageReceived = function () {
			return new \Sy\Bootstrap\Db\Message\Received();
		};

		$this->messageReply = function () {
			return new \Sy\Bootstrap\Db\Message\Reply();
		};
	}

	public function get($id) {
		try {
			return parent::get($id);
		} catch(\Sy\Container\NotFoundException $e) {
			$class = 'Sy\\Bootstrap\\Db\\' . ucfirst($id);
			if (class_exists($class)) {
				$this->$id = function () use($class) {
					return new $class();
				};
			} else {
				$table = 't_' . Str::camlToSnake($id);
				$this->$id = function () use ($table) {
					return new \Sy\Bootstrap\Db\Crud($table);
				};
			}
			return $this->get($id);
		}
	}

}