<?php
namespace Sy\Bootstrap;

use Composer\Script\Event;

class Plugin {

	public static function install(Event $event) {
		var_dump($event->getArguments());
	}

}