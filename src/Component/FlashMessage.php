<?php
namespace Sy\Bootstrap\Component;

class FlashMessage extends \Sy\Component\WebComponent {

	public function __construct() {
		parent::__construct();
		$this->actionDispatch('flash_message_action');
	}

	public function clearAction() {
		\Sy\Bootstrap\Lib\FlashMessage::clearMessage();
		exit();
	}

	private function init() {
		$this->setTemplateFile(__DIR__ . '/FlashMessage/FlashMessage.html');

		$js = new \Sy\Component\WebComponent();
		$js->setTemplateFile(__DIR__ . '/FlashMessage/FlashMessage.js');

		$message = \Sy\Bootstrap\Lib\FlashMessage::getMessage();
		if (!is_null($message)) {
			$title = $this->session('flash_message_title', '');
			$this->setVars([
				'MESSAGE' => $message,
				'TITLE'   => $title,
				'TYPE'    => $this->session('flash_message_type', 'success'),
			]);
			if (!empty($title)) $this->setBlock('TITLE_BLOCK');
			$timeout = $this->session('flash_message_timeout', 3500);
			if ($timeout > 0) {
				$js->setVar('TIMEOUT', $timeout);
				$js->setBlock('TIMEOUT_BLOCK');
			}
			$js->setBlock('SESSION_BLOCK');
		} else {
			$this->setVar('TYPE', 'success');
		}
		$this->addJsCode($js, ['type' => 'text/javascript']);
	}

	public function __toString() {
		$this->init();
		return parent::__toString();
	}

}