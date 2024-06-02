<?php
namespace Sy\Bootstrap\Component;

use Sy\Bootstrap\Lib\Url;

class FlashMessage extends \Sy\Component\WebComponent {

	public function __construct() {
		parent::__construct();
		$this->setTemplateFile(__DIR__ . '/FlashMessage/FlashMessage.html');

		$this->mount(function () {
			$this->init();
		});
	}

	private function init() {
		$js = new \Sy\Component();
		$js->setTemplateFile(__DIR__ . '/FlashMessage/FlashMessage.js');

		$data = \Sy\Bootstrap\Lib\FlashMessage::getMessage();
		if (!empty($data)) {
			$js->setBlock('SESSION_BLOCK', [
				'API_URL' => Url::build('api', 'flash-message'),
				'DATA'    => json_encode($data),
			]);
		}

		$this->addJsCode($js, ['type' => 'text/javascript']);
	}

}