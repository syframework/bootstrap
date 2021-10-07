<?php
namespace Sy\Bootstrap\Db\Message;

use Sy\Db\Sql;

class Reply extends \Sy\Bootstrap\Db\Crud {

	public function __construct() {
		parent::__construct('t_message_reply');
	}

	public function count($where = null) {
		$params = [':id' => $where['message_id']];
		$w  = '';
		if (!empty($where['last'])) {
			$params[':last'] = $where['last'];
			$w = 'AND t_message_reply.id > :last';
		}
		$sql = new Sql("
			SELECT count(*)
			FROM t_message_reply
			LEFT JOIN t_user ON t_user.id = t_message_reply.user_id
			WHERE
				t_user.status = 'active' AND t_user.role <> 'blacklisted'
				AND message_id = :id $w
		", $params);
		$res = $this->db->queryOne($sql);
		return $res[0];
	}

	public function retrieveReplies($messageId, $last, $limit) {
		$params = [':id' => $messageId];
		$where  = '';
		if (!empty($last)) {
			$params[':last'] = $last;
			$where = 'AND t_message_reply.id > :last';
		}
		$sql = new Sql("
			SELECT t_message_reply.*, t_user.firstname AS user_firstname, t_user.lastname AS user_lastname
			FROM t_message_reply
			LEFT JOIN t_user ON t_user.id = t_message_reply.user_id
			WHERE
				t_user.status = 'active' AND t_user.role <> 'blacklisted'
				AND message_id = :id $where
			ORDER BY created_at ASC
			LIMIT $limit
		", $params);
		return $this->db->queryAll($sql, \PDO::FETCH_ASSOC);
	}

	public function retrieveParticipants($messageId) {
		$sql = new Sql("
			SELECT DISTINCT(user_id)
			FROM t_message_reply
			WHERE message_id = :id
		", [':id' => $messageId]);
		return $this->db->queryAll($sql, \PDO::FETCH_ASSOC);
	}

}