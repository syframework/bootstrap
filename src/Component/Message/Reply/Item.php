<?php
namespace Sy\Bootstrap\Component\Message\Reply;

class Item extends \Sy\Component\WebComponent {

	private $message;

	public function __construct($message) {
		parent::__construct();
		$this->message = $message;
	}

	public function __toString() {
		$this->init();
		return parent::__toString();
	}

	private function init() {
		$this->addTranslator(LANG_DIR);
		$this->setTemplateFile(__DIR__ . '/Item.html');

		$message = $this->message;

		// Bubble style
		$service = \Project\Service\Container::getInstance();
		$sms = $service->messageReceived->retrieve(['id' => $message['message_id']]);
		switch ($message['user_id']) {
			case $sms['user_id']:
				$class = '';
				break;
			default:
				$class = 'reverse other';
				break;
		}

		$date = new \Sy\Bootstrap\Lib\Date($message['created_at']);
		$author = $this->_(\Sy\Bootstrap\Lib\Str::convertName($message['user_firstname'] . ' ' . $message['user_lastname']));
		$this->setComponent('PROFILE_IMG', new \Sy\Bootstrap\Component\User\ProfileImg($message['user_id']));
		$this->setVars([
			'ID'      => $message['id'],
			'MESSAGE' => \Sy\Bootstrap\Lib\Str::convert($message['message']),
			'DATE'    => $date->humanTimeDiff(),
			'DATETIME'=> $date->timestamp(),
			'CLASS'   => $class,
			'AUTHOR'  => $author,
			'URL'     => \Sy\Bootstrap\Lib\Url::build('page', 'user', ['id' => $message['user_id']]),
		]);
		if ($message['user_id'] === $sms['user_id']) {
			$this->setBlock('IMG_LEFT_BLOCK');
		} else {
			$this->setBlock('IMG_RIGHT_BLOCK');
		}

		// Delete button for administrator
		$user = $service->user->getCurrentUser();
		if ($user->hasPermission('message-delete') or $user->id === $message['user_id']) {
			$this->setComponent('DEL_BTN_FORM', new DeleteForm($message['id']));
		}
	}

}
