<?php
namespace Sy\Bootstrap\Component\Message;

class Component extends \Sy\Component\WebComponent {

	private $itemId;
	private $itemType;

	public function __construct($itemId, $itemType) {
		parent::__construct();
		$this->itemId = $itemId;
		$this->itemType = $itemType;
	}

	public function __toString() {
		$this->init();
		return parent::__toString();
	}

	private function init() {
		$this->addTranslator(LANG_DIR);

		$this->setTemplateFile(__DIR__ . '/Component.html');

		$js = new \Sy\Component\WebComponent();
		$js->setTemplateFile(__DIR__ . '/Component.js');

		$service = \Project\Service\Container::getInstance();
		$this->setComponent('FEED', new Received\Feed($this->itemId, $this->itemType));
		if ($service->user->getCurrentUser()->isConnected()) {
			$js->setVar('FEED_URL', \Sy\Bootstrap\Lib\Url::build('api', 'feed', [
				'message_item_id'   => $this->itemId,
				'message_item_type' => $this->itemType,
				'last'        => 0,
				'class'       => '\Sy\Bootstrap\Component\Message\Received\Feed',
				'sy_language' => \Sy\Translate\LangDetector::getInstance()->getLang()
			]));
			$js->setBlock('NEW_BLOCK');
			$this->setComponent('ADD_FORM', new \Sy\Bootstrap\Component\Message\Received\AddForm($this->itemId, $this->itemType));
			$this->setComponent('EDIT_MSG_MODAL', new \Sy\Bootstrap\Component\Message\Received\EditModal());
		} else {
			$this->setComponent('ADD_FORM', new Received\AddFormAlt($this->itemId, $this->itemType));
		}

		$this->addJsCode($js);
	}

}
