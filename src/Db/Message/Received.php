<?php
namespace Sy\Bootstrap\Db\Message;

use Sy\Db\Sql;

class Received extends \Sy\Bootstrap\Db\Crud {

	public function __construct() {
		parent::__construct('t_message_received');
	}

	public function retrieve(array $pk) {
		return $this->db->queryOne(new \Sy\Db\MySql\Select([
			'FROM'  => 'v_message_received',
			'WHERE' => $pk,
		]), \PDO::FETCH_ASSOC);
	}

	public function count($where = null) {
		list($where, $params)  = $this->where($where);
		$sql = new Sql("
			SELECT count(*)
			FROM t_message_received
			LEFT JOIN t_user user ON t_message_received.user_id = user.id
			$where
		", $params);
		$res = $this->db->queryOne($sql);
		return $res[0];
	}

	public function retrieveAll(array $parameters = []) {
		list($where, $params)  = $this->where($parameters);
		$sql = new Sql("
			SELECT
				message.*,
				COUNT(reply.id) AS nb_reply
			FROM (
				SELECT t_message_received.*, user.firstname AS user_firstname, user.lastname AS user_lastname
				FROM t_message_received
				LEFT JOIN t_user user ON t_message_received.user_id = user.id
				$where
				ORDER BY created_at DESC
				LIMIT 10
			) AS message
			LEFT JOIN t_message_reply reply ON message.id = reply.message_id
			GROUP BY message.id
			ORDER BY NULL
		", $params);
		return $this->db->queryAll($sql, \PDO::FETCH_ASSOC);
	}

	private function where($parameters) {
		$params = [];
		$where  = ["user.status = 'active'", "user.role <> 'blacklisted'"];
		if (!empty($parameters['last'])) {
			$params[':last'] = $parameters['last'];
			$where[] = 't_message_received.id < :last';
		}
		if (!empty($parameters['item_id'])) {
			$params[':item_id'] = $parameters['item_id'];
			$where[] = 't_message_received.item_id = :item_id';
		}
		if (!empty($parameters['item_type'])) {
			$params[':item_type'] = $parameters['item_type'];
			$where[] = 't_message_received.item_type = :item_type';
		}
		return ['WHERE ' . implode(' AND ', $where), $params];
	}

}