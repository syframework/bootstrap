<?php
namespace Sy\Bootstrap\Component\Message\Reply;

class Feed extends \Sy\Bootstrap\Component\Feed {

	private $messageId;

	public function __construct($messageId = null) {
		parent::__construct();
		$this->messageId = is_null($messageId) ? $this->get('message_id', 0) : $messageId;
	}

	public function getPage($n) {
		if (is_null($n)) $n = 0;
		return new Page($this->messageId, $n, $this->getLimit($n));
	}

	public function isLastPage($n) {
		if (is_null($n)) $n = 0;
		$service = \Project\Service\Container::getInstance();
		$nb = $service->messageReply->count(['message_id' => $this->messageId, 'last' => $n]);
		return $nb <= $this->getLimit($n);
	}

	public function getParams() {
		return ['message_id' => $this->messageId];
	}

	private function getLimit($n) {
		return ($n === 0) ? 100 : 10;
	}

}