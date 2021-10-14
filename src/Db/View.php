<?php
namespace Sy\Bootstrap\Db;

class View {

	/**
	 * @var Crud
	 */
	private $crud;

	public function __construct($table) {
		$this->crud = new Crud($table);
	}

	public function retrieve(array $pk) {
		return $this->crud->retrieve($pk);
	}

	public function retrieveAll(array $parameters = []) {
		return $this->crud->retrieveAll($parameters);
	}

}