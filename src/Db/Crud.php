<?php
namespace Sy\Bootstrap\Db;

use \Sy\Bootstrap\Service\Container;
use Sy\Db\MySql\Gate;

class Crud extends \Sy\Db\MySql\Crud {

	/**
	 * @param string $table
	 * @param array $pk Optionnal primary key
	 */
	public function __construct($table, $pk = []) {
		parent::__construct($table, $pk);
		$service = Container::getInstance();
		$gate = new Gate(DATABASE_CONFIG);
		$gate->setLogger($service->debug->getLogger());
		$this->setDbGate($gate);
		$this->setCacheEngine($service->cache);
	}

}