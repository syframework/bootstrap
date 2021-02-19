<?php
namespace Sy\Bootstrap\Service;

use Sy\Debug\Debugger;
use Sy\Cache\SimpleCache;

/**
 * @method static Container getInstance()
 * @property-read Debugger $debug Debug and log service
 * @property-read User $user User service
 * @property-read Mail $mail Mail service
 * @property-read Crud $page Page service
 * @property-read Crud $pageHistory Page service
 * @property-read SimpleCache $cache Cache service
 */
class Container extends \Sy\Container {

	public function __construct() {
		parent::__construct();

		$this->debug = function () {
			return Debugger::getInstance();
		};
		$this->user = function () {
			return new User();
		};
		$this->mail = function () {
			return new Mail();
		};
		$this->page = function () {
			return new Crud('page');
		};
		$this->pageHistory = function () {
			return new Crud('pageHistory');
		};
		$this->cache = function() {
			return new SimpleCache(CACHE_DIR);
		};
	}

	public function get($id) {
		try {
			return parent::get($id);
		} catch(\Sy\Container\NotFoundException $e) {
			$class = 'Sy\\Bootstrap\\Service\\Container\\' . ucfirst($id);
			if (class_exists($class)) {
				$container = $class::getInstance();
				return $container->get($id);
			} else {
				throw new \Sy\Container\NotFoundException(sprintf('Identifier "%s" is not defined.', $id));
			}
		}
	}

}