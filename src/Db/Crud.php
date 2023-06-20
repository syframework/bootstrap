<?php
namespace Sy\Bootstrap\Db;

class Crud extends \Sy\Db\MySql\Crud {

	/**
	 * @param string $table
	 * @param array $pk Optionnal primary key
	 */
	public function __construct($table, $pk = []) {
		parent::__construct($table);
		if (defined('DATABASE_CONFIG')) {
			$this->setConfig(DATABASE_CONFIG);
		}
		$service = \Project\Service\Container::getInstance();
		$this->db->setLogger($service->debug->getLogger());
		if (defined('DB_CRUD_CACHE') and DB_CRUD_CACHE) {
			$this->setCacheEngine($service->cache);
		}
	}

}