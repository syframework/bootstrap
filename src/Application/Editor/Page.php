<?php
namespace Sy\Bootstrap\Application\Editor;

class Page extends \Sy\Bootstrap\Component\Api {

	use CkFile;

	public function security() {
		$service = \Project\Service\Container::getInstance();
		if (!$service->user->getCurrentUser()->hasPermission('page-update')) {
			throw new \Sy\Bootstrap\Component\Api\ForbiddenException('Permission denied');
		}
	}

}