<?php
namespace Sy\Bootstrap\Db;

use Sy\Db\MySql\Select;
use Sy\Db\Sql;

class User extends Crud {

	/**
	 * @var Crud
	 */
	private $settings;

	public function __construct() {
		parent::__construct('t_user', ['id']);
		$this->settings = new Crud('t_user_setting', ['user_id', 'key']);
	}

	public function getPermissions($id) {
		$sql = new Sql("
			(SELECT t_user_role_has_permission.id_permission AS permission
			FROM t_user
			INNER JOIN t_user_role_has_permission ON t_user.role = t_user_role_has_permission.id_role
			WHERE t_user.id = :id)
			UNION
			(SELECT permission
			FROM t_user_has_permission
			WHERE id = :id)
		", [':id' => $id]);
		$rows = $this->queryAll($sql, \PDO::FETCH_ASSOC);
		$permissions = [];
		foreach ($rows as $row) {
			$permissions[] = $row['permission'];
		}
		return $permissions;
	}

	public function getSettings($id) {
		$sql = new Select(['FROM' => 't_user_setting', 'WHERE' => ['user_id' => $id]]);
		return $this->queryAll($sql, \PDO::FETCH_ASSOC);
	}

	public function setSetting($id, $key, $value) {
		$this->settings->change(['user_id' => $id, 'key' => $key, 'value' => $value], ['value' => $value]);
	}

}