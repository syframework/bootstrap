<?php
namespace Sy\Bootstrap\Component\Message\Received;

class Feed extends \Sy\Bootstrap\Component\Feed {

	private $itemId;
	private $itemType;

	public function __construct($itemId = null, $itemType = null) {
		parent::__construct();
		$this->itemId = is_null($itemId) ? $this->get('message_item_id', '') : $itemId;
		$this->itemType = is_null($itemType) ? $this->get('message_item_type', '') : $itemType;
		// Update time periodically
		$this->addJsLink(MOMENT_JS);
		$this->addJsLink(WEB_ROOT . '/assets/js/time.js');
		$this->addJsCode('$("body").on("feed-loaded.messagereceived", updateTime);');
	}

	public function getPage($n) {
		if (is_null($n)) $n = 0;
		return new Page($n, $this->itemId, $this->itemType);
	}

	public function isLastPage($n) {
		try {
			if (is_null($n)) $n = 0;
			$service = \Project\Service\Container::getInstance();
			$nb = $service->messageReceived->count(['last' => $n, 'item_id' => $this->itemId, 'item_type' => $this->itemType]);
			return $nb <= 10;
		} catch (\Sy\Bootstrap\Service\Crud\Exception $e) {
			$this->logError('SQL Error');
			return true;
		}
	}

	public function getParams() {
		return [
			'message_item_id' => $this->itemId,
			'message_item_type' => $this->itemType,
		];
	}

}