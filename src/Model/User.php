<?php
namespace Sy\Bootstrap\Model;

use Sy\Bootstrap\Service\Container;

class User {

	/**
	 * @var array
	 */
	private $data;

	/**
	 * @var array
	 */
	private $permissions;

	public function __construct($id) {
		$service           = Container::getInstance();
		$this->data        = $service->user->retrieve(['id' => $id]);
		$this->permissions = null;
	}

	public function isConnected() {
		return ($this->data ? true : false);
	}

	public function hasRole($role) {
		if (!$this->data) return false;
		return ($this->data['role'] === $role);
	}

	public function hasPermission($permission) {
		if (!$this->data) return false;
		if (is_null($this->permissions)) {
			$service           = Container::getInstance();
			$this->permissions = $service->user->getPermissions($this->data['id']);
		}
		return in_array($permission, $this->permissions);
	}

	public function __get($name) {
		if (empty($this->data)) return null;
		if (!array_key_exists($name, $this->data)) return null;
		return $this->data[$name];
	}

}