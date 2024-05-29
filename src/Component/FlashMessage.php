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
		$js->setVar('API_URL', Url::build('api', 'flash-message'));
		$this->addJsCode($js, ['type' => 'text/javascript']);
	}

}