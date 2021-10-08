<?php
namespace Sy\Bootstrap\Service\Message;

use Sy\Bootstrap\Lib\Upload;
use Sy\Event\Event;

class Reply extends \Sy\Bootstrap\Service\Crud {

	public function __construct() {
		parent::__construct('messageReply');
	}

	public function create(array $fields) {
		// Dispatch event before message reply creation
		$service = \Project\Service\Container::getInstance();
		$message = $service->messageReceived->retrieve(['id' => $fields['message_id']]);
		$service->event->dispatch(new Event('message.reply.create', [
			'itemId'    => $message['item_id'],
			'itemType'  => $message['item_type'],
		]));

		$id = parent::create($fields);

		// Dispatch event after message reply creation
		$service->event->dispatch(new Event('message.reply.created', [
			'repUserId' => $fields['user_id'],
			'msgId'     => $message['id'],
			'msgUserId' => $message['user_id'],
			'itemId'    => $message['item_id'],
			'itemType'  => $message['item_type'],
		]));

		return $id;
	}

	public function delete(array $pk) {
		try {
			$res = parent::retrieve($pk);
			$ret = parent::delete($pk);

			// Dispatch event after message reply deleted
			$service = \Project\Service\Container::getInstance();
			$service->event->dispatch(new Event('message.reply.deleted', [
				'repId' => $res['id'],
				'msgId' => $res['message_id'],
			]));

			// Delete pictures
			if (!empty($res['user_id'])) {
				$dir = UPLOAD_DIR . '/photo/message/' . $res['message_id'] . '/reply/' . $res['id'] . '/user/' . $res['user_id'];
				Upload::delete($dir);
			}

			return $ret;
		} catch (\Sy\Bootstrap\Service\Crud\Exception $e) {
			return 0;
		}
	}

}
