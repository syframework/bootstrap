<?php
namespace Sy\Bootstrap\Component\Message\Reply;

class Page extends \Sy\Component\WebComponent {

	private $messageId;

	private $page;

	private $limit;

	public function __construct($messageId, $page, $limit) {
		parent::__construct();
		$this->messageId = $messageId;
		$this->page      = $page;
		$this->limit     = $limit;
	}

	public function __toString() {
		$this->init();
		return parent::__toString();
	}

	private function init() {
		$this->addTranslator(LANG_DIR);
		$this->setTemplateFile(__DIR__ . '/Page.html');

		if (is_null($this->page)) return;

		$service = \Project\Service\Container::getInstance();
		$messages = $service->messageReply->retrieveReplies($this->messageId, $this->page, $this->limit);

		foreach ($messages as $message) {
			$this->setComponent('ITEM', new Item($message));
			$this->setBlock('MESSAGE_BLOCK');
		}
	}

}