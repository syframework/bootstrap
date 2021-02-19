<?php
namespace Sy\Bootstrap\Db;

/**
 * @method static Container getInstance()
 * @property-read \Sy\Bootstrap\Db\User $user User Db service
 */
class Container extends \Sy\Container {

	public function __construct() {
		parent::__construct();

		$this->user = function () {
			return new \Sy\Bootstrap\Db\User();
		};
	}

}