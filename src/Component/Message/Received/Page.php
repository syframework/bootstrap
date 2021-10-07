<?php
namespace Sy\Bootstrap\Component\Message\Received;

class Page extends \Sy\Component\WebComponent {

	private $page;
	private $itemId;
	private $itemType;

	public function __construct($page, $itemId, $itemType) {
		parent::__construct();
		$this->page = $page;
		$this->itemId = $itemId;
		$this->itemType = $itemType;
	}

	public function __toString() {
		$this->init();
		return parent::__toString();
	}

	private function init() {
		$this->addTranslator(LANG_DIR);
		$this->setTemplateFile(__DIR__ . '/Page.html');
		$this->addJsCode(file_get_contents(__DIR__ . '/Item.js'));

		if (is_null($this->page)) {
			$item = new Item(null);
			$this->mergeCss($item);
			$this->mergeJs($item);
			$this->setVar('ITEM', '');
			$this->setBlock('MESSAGE_BLOCK');
			return;
		}

		$service = \Project\Service\Container::getInstance();
		try {
			$messages = $service->messageReceived->retrieveAll(['last' => $this->page, 'item_id' => $this->itemId, 'item_type' => $this->itemType]);
		} catch (\Sy\Bootstrap\Service\Crud\Exception $e) {
			$messages = [];
		}

		foreach ($messages as $message) {
			$this->setComponent('ITEM', new Item($message));
			$this->setBlock('MESSAGE_BLOCK');
		}
	}

}