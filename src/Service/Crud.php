<?php
namespace Sy\Bootstrap\Service;

/**
 * @method void transaction(callable $fn)
 */
class Crud {

	/**
	 * @var \Sy\Bootstrap\Db\Container
	 */
	private $dbContainer;

	/**
	 * @var string
	 */
	private $id;

	public function __construct($id) {
		$this->id = $id;
	}

	/**
	 * @return \Sy\Bootstrap\Db\Container
	 */
	public function getDbContainer() {
		if (!isset($this->dbContainer)) {
			$this->dbContainer = \Sy\Bootstrap\Db\Container::getInstance();
		}
		return $this->dbContainer;
	}

	/**
	 * @param \Sy\Bootstrap\Db\Container $dbContainer
	 */
	public function setDbContainer(\Sy\Bootstrap\Db\Container $dbContainer) {
		$this->dbContainer = $dbContainer;
	}

	/**
	 * @return \Sy\Bootstrap\Db\Crud
	 */
	public function getDbCrud() {
		$container = $this->getDbContainer();
		$id = $this->id;
		return $container->$id;
	}

	/**
	 * Add a row with specified data.
	 *
	 * @param array $fields Column-value pairs.
	 * @return int The number of affected rows.
	 */
	public function create(array $fields) {
		return $this->getDbCrud()->create($fields);
	}

	/**
	 * Add multiple rows with specified data.
	 *
	 * @param array $fields array of array column-value pairs.
	 * @return int The number of affected rows.
	 */
	public function createMany(array $data) {
		return $this->getDbCrud()->createMany($data);
	}

	/**
	 * Retrieve a row by primary key.
	 *
	 * @param array $pk Column-value pairs.
	 * @return array
	 */
	public function retrieve(array $pk) {
		return $this->getDbCrud()->retrieve($pk);
	}

	/**
	 * Update a row by primary key.
	 *
	 * @param array $pk Column-value pairs.
	 * @param array $bind Column-value pairs.
	 * @return int The number of affected rows.
	 */
	public function update(array $pk, array $bind) {
		return $this->getDbCrud()->update($pk, $bind);
	}

	/**
	 * Delete a row by primary key.
	 *
	 * @param array $pk Column-value pairs.
	 * @return int The number of affected rows.
	 */
	public function delete(array $pk) {
		return $this->getDbCrud()->delete($pk);
	}

	/**
	 * Retrieve many rows.
	 *
	 * @param array $parameters Select parameters like: WHERE, LIMIT, OFFSET...
	 */
	public function retrieveAll(array $parameters = []) {
		return $this->getDbCrud()->retrieveAll($parameters);
	}

	/**
	 * Foreach row, apply a function on it.
	 *
	 * @param callable $callback
	 * @param array $parameters Select parameters like: WHERE, LIMIT, OFFSET...
	 */
	public function foreachRow($callback, array $parameters = []) {
		$stmt = $this->getDbCrud()->retrieveStatement($parameters);
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			call_user_func($callback, $row);
		}
	}

	/**
	 * Insert or update a row with specified data.
	 *
	 * @param array $fields Column-value pairs.
	 * @param array $updates Column-value pairs.
	 * @return int The number of affected rows.
	 */
	public function change(array $fields, array $updates = []) {
		return $this->getDbCrud()->change($fields, $updates);
	}

	/**
	 * Return row count.
	 *
	 * @param mixed $where array or string.
	 * @return int
	 */
	public function count($where = null) {
		return $this->getDbCrud()->count($where);
	}

	/**
	 * Return columns informations.
	 *
	 * @return array
	 */
	public function getColumns() {
		return $this->getDbCrud()->getColumns();
	}

	public function __call($name, $arguments) {
		return call_user_func_array([$this->getDbCrud(), $name], $arguments);
	}

}