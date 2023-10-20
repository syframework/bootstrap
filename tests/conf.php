<?php
namespace Project\Service;
use Sy\Bootstrap\Service\NullService;

class Container extends \Sy\Bootstrap\Service\Container {

	public function __construct() {
		$this->user = function () {
			return new NullService();
		};
	}

}

namespace Project\Db;

class Container extends \Sy\Bootstrap\Db\Container {}

define('WEB_ROOT', '/webroot');
define('CONTROLLER_TRIGGER', 'controller');
define('ACTION_TRIGGER', 'action');
define('ACTION_PARAM', 'action_param');
define('LANG', 'fr');
define('LANGS', ['fr' => 'Fran&ccedil;ais', 'en' => 'English', 'es' => 'Espa&ntilde;ol', 'it' => 'Italiano']);
