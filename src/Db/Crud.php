<?php
namespace Sy\Bootstrap\Db;

use \Sy\Bootstrap\Service\Container;

class Crud extends \Sy\Db\MySql\Crud {

	/**
	 * @param string $table
	 * @param array $pk Optionnal primary key
	 */
	public function __construct($table, $pk = []) {
		parent::__construct($table, $pk);
		$this->setConfig(DATABASE_CONFIG);
		$service = ServiceContainer::getInstance();
		$this->setLogger($service->debug->getLogger());
		$this->setCacheEngine($service->cache);
	}

}