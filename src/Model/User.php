<?php
namespace Sy\Bootstrap\Model;

class User {

	/**
	 * @var array
	 */
	private $data;

	/**
	 * @var array
	 */
	private $permissions;

	/**
	 * @var array
	 */
	private $settings;

	public function __construct($id) {
		$service           = \Project\Service\Container::getInstance();
		$this->data        = $service->user->retrieve(['id' => $id]);
		$this->permissions = null;
		$this->settings    = null;
	}

	/**
	 * Check if user is connected
	 *
	 * @return boolean
	 */
	public function isConnected() {
		return ($this->data ? true : false);
	}

	/**
	 * Check if user a specific role
	 *
	 * @param  string $role
	 * @return boolean
	 */
	public function hasRole($role) {
		if (!$this->data) return false;
		return ($this->data['role'] === $role);
	}

	/**
	 * Get user permissions
	 *
	 * @return array
	 */
	public function getPermissions() {
		if (is_null($this->permissions)) {
			$service           = \Project\Service\Container::getInstance();
			$this->permissions = $service->user->getPermissions($this->data['id']);
		}
		return $this->permissions;
	}

	/**
	 * Check if user has a specific permission
	 *
	 * @param  string $permission
	 * @return boolean
	 */
	public function hasPermission($permission) {
		if (!$this->isConnected()) return false;
		return in_array($permission, $this->getPermissions());
	}

	/**
	 * Check if user has at least one permission among given permissions
	 *
	 * @param  array $permissions
	 * @return boolean
	 */
	public function hasPermissionAmong($permissions) {
		if (!$this->isConnected()) return false;
		$result = array_intersect($permissions, $this->getPermissions());
		return (count($result) > 0);
	}

	/**
	 * Get a user setting
	 *
	 * @param  string $key
	 * @return string
	 */
	public function getSetting($key) {
		if (!$this->data) return null;
		if (is_null($this->settings)) {
			$service        = \Project\Service\Container::getInstance();
			$this->settings = $service->user->getSettings($this->data['id']);
		}
		return (isset($this->settings[$key]) ? $this->settings[$key] : null);
	}

	/**
	 * Set a user setting
	 *
	 * @param  string $key
	 * @param  string $value
	 * @return void
	 */
	public function setSetting($key, $value) {
		$service = \Project\Service\Container::getInstance();
		$service->user->setSetting($this->data['id'], $key, $value);
	}

	public function __get($name) {
		if (empty($this->data)) return null;
		if (!array_key_exists($name, $this->data)) return null;
		return $this->data[$name];
	}

}