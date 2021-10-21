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
		$service = Container::getInstance();
		$this->db->setLogger($service->debug->getLogger());
		if (defined('DB_CRUD_CACHE') and DB_CRUD_CACHE) {
			$this->setCacheEngine($service->cache);
		}
	}

}