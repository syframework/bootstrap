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

	public function getProfile($userId) {
		$sql = new Sql("
			SELECT
				u.id,
				u.firstname,
				u.lastname,
				u.description,
				(SELECT COUNT(DISTINCT item_id) FROM t_rate WHERE user_id = :user_id AND item_type = 'place') AS 'place_reviewed',
				(SELECT COUNT(*) FROM `t_favorite` WHERE item_id = :user_id AND item_type = 'user') AS 'followers',
				(SELECT COUNT(*) FROM `t_favorite` WHERE user_id = :user_id AND item_type = 'user') AS 'following',
				(SELECT COUNT(*) FROM t_list WHERE user_id = :user_id AND type = 'place') AS 'place_lists',
				IFNULL(SUM(r.reaction1), 0) AS reaction1,
				IFNULL(SUM(r.reaction2), 0) AS reaction2,
				IFNULL(SUM(r.reaction3), 0) AS reaction3,
				IFNULL(SUM(r.reaction4), 0) AS reaction4,
				IFNULL(SUM(r.reaction5), 0) AS reaction5
			FROM
				t_user AS u
			LEFT JOIN
				t_rate AS r ON u.id = r.user_id
			WHERE
				u.id = :user_id
		", [':user_id' => $userId]);
		return $this->queryOne($sql, \PDO::FETCH_ASSOC);
	}

	public function retrieveFollowing($userId, $offset) {
		$sql = new Sql("
			SELECT users.*, COUNT(DISTINCT t_rate.item_id) AS nb_rate FROM (
				SELECT users.*, COUNT(t_favorite.id) AS nb_follower FROM (
					SELECT t_user.* FROM `t_favorite`
					LEFT JOIN t_user ON t_user.id = t_favorite.item_id
					WHERE user_id = ? AND item_type = 'user'
				) as users
				LEFT JOIN t_favorite ON users.id = t_favorite.item_id
				WHERE item_type = 'user' OR item_type IS NULL
				GROUP BY users.id
			) as users
			LEFT JOIN t_rate ON users.id = t_rate.user_id
			GROUP BY users.id
			ORDER BY nb_rate DESC, nb_follower DESC, id
			LIMIT 10 OFFSET $offset
		", [$userId]);
		return $this->queryAll($sql, \PDO::FETCH_ASSOC);
	}

	public function retrieveFollower($userId, $offset) {
		$sql = new Sql("
			SELECT users.*, COUNT(DISTINCT t_rate.item_id) AS nb_rate FROM (
				SELECT users.*, COUNT(t_favorite.id) AS nb_follower FROM (
					SELECT t_user.* FROM `t_favorite`
					LEFT JOIN t_user ON t_user.id = t_favorite.user_id
					WHERE item_id = ? AND item_type = 'user'
				) as users
				LEFT JOIN t_favorite ON users.id = t_favorite.item_id
				WHERE item_type = 'user' OR item_type IS NULL
				GROUP BY users.id
			) as users
			LEFT JOIN t_rate ON users.id = t_rate.user_id
			GROUP BY users.id
			ORDER BY nb_rate DESC, nb_follower DESC, id
			LIMIT 10 OFFSET $offset
		", [$userId]);
		return $this->queryAll($sql, \PDO::FETCH_ASSOC);
	}

	public function retrieveAllFollowersId($userId) {
		$sql = new Sql("
			SELECT user_id
			FROM `t_favorite`
			WHERE item_id = ? AND item_type = 'user'
		", [$userId]);
		return $this->queryAll($sql, \PDO::FETCH_ASSOC);
	}

	public function retrieveSuggestion($lat, $lng, $userId) {
		$params = [];
		$userTable = "(SELECT * FROM t_user WHERE status = 'active')";
		$locationCondition = '';
		if (!is_null($userId)) {
			$userTable = "(
				SELECT *
				FROM t_user AS users
				WHERE users.id <> :user_id
				AND users.status = 'active'
				AND users.id NOT IN (
					SELECT item_id FROM t_favorite WHERE user_id = :user_id AND item_type = 'user'
				)
			)";
			$params[':user_id'] = $userId;
		}
		if (!empty($lat) and !empty($lat)) {
			$distance = 5;
			$lat1 = $lat - $distance / 111;
			$lat2 = $lat + $distance / 111;
			$lng1 = $lng - $distance / abs(cos(deg2rad($lat)) * 111);
			$lng2 = $lng + $distance / abs(cos(deg2rad($lat)) * 111);
			$params[':lat1'] = $lat1;
			$params[':lng1'] = $lng1;
			$params[':lat2'] = $lat2;
			$params[':lng2'] = $lng2;
			$locationCondition = "AND t_place.lat > :lat1 AND t_place.lat < :lat2 AND t_place.lng > :lng1 AND t_place.lng < :lng2";
		}
		$sql = new Sql("
			SELECT users.*, COUNT(t_favorite.id) AS nb_follower
			FROM (
				SELECT users.*, COUNT(DISTINCT v_rate.item_id) AS nb_rate
				FROM $userTable AS users
				LEFT JOIN v_rate ON users.id = v_rate.user_id
				LEFT JOIN t_place ON v_rate.item_id = t_place.id
				WHERE v_rate.item_type = 'place' $locationCondition
				GROUP BY users.id
			) AS users
			LEFT JOIN t_favorite ON users.id = t_favorite.item_id
			WHERE t_favorite.item_type = 'user' OR t_favorite.item_type IS NULL
			GROUP BY users.id
			ORDER BY nb_follower DESC, nb_rate DESC
			LIMIT 5
		", $params);
		return $this->queryAll($sql, \PDO::FETCH_ASSOC);
	}

}