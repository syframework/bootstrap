<?php
namespace Sy\Bootstrap\Db;

use Sy\Db\Sql;

class User extends Crud {

	public function __construct() {
		parent::__construct('t_user', ['id']);
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

}