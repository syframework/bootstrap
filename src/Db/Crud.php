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
		$this->setDbGate(new Gate(DATABASE_CONFIG));
		$service = Container::getInstance();
		$this->setLogger($service->debug->getLogger());
		$this->setCacheEngine($service->cache);
	}

}