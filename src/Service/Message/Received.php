<?php
namespace Sy\Bootstrap\Service\Message;

use Sy\Bootstrap\Lib\Upload;
use Sy\Event\Event;

class Received extends \Sy\Bootstrap\Service\Crud {

	public function __construct() {
		parent::__construct('messageReceived');
	}

	public function create(array $fields) {
		try {
			$service = \Project\Service\Container::getInstance();

			// Dispatch an event before message creation
			$service->event->dispatch(new Event('message.create', [
				'itemId'   => $fields['item_id'],
				'itemType' => $fields['item_type'],
				'userId'   => $fields['user_id']
			]));

			// Check fields
			if (!isset($fields['user_id'])) $fields['user_id'] = $service->user->getCurrentUser()->id;
			if (!isset($fields['ip'])) $fields['ip'] = sprintf("%u", ip2long($_SERVER['REMOTE_ADDR']));

			$id = parent::create($fields);

			// Dispatch an event after message creation
			$service->event->dispatch(new Event('message.created', [
				'msgId'    => $id,
				'itemId'   => $fields['item_id'],
				'itemType' => $fields['item_type'],
				'userId'   => $fields['user_id']
			]));

			return $id;
		} catch(\Sy\Db\Exception $e) {
			$this->logWarning($e);
			if ($e instanceof \Sy\Db\IntegrityConstraintViolationException) {
				throw new \Sy\Bootstrap\Service\Crud\DuplicateEntryException('Integrity constraint violation');
			} else {
				throw new \Sy\Bootstrap\Service\Crud\Exception('Create error');
			}
		}
	}

	public function retrieve(array $pk) {
		try {
			$res = parent::retrieve($pk);
			if (empty($res)) return [];

			// Dispatch an event before message retrieve
			$service = \Project\Service\Container::getInstance();
			$service->event->dispatch(new Event('message.read', [
				'itemId'   => $res['item_id'],
				'itemType' => $res['item_type'],
				'userId'   => $res['user_id']
			]));

			return $res;
		} catch (\Sy\Bootstrap\Service\Crud\Exception $e) {
			return [];
		}
	}

	public function retrieveAll(array $parameters = array()) {
		try {
			// Dispatch an event before message retrieve
			$service = \Project\Service\Container::getInstance();
			$service->event->dispatch(new Event('message.read', [
				'itemId'   => $parameters['item_id'],
				'itemType' => $parameters['item_type']
			]));

			return parent::retrieveAll($parameters);
		} catch (\Sy\Bootstrap\Service\Crud\Exception $e) {
			return [];
		}
	}

	public function delete(array $pk) {
		try {
			$res = parent::retrieve($pk);

			// Dispatch an event before message delete
			$service = \Project\Service\Container::getInstance();
			$service->event->dispatch(new Event('message.delete', [
				'msgId'    => $res['id'],
				'itemId'   => $res['item_id'],
				'itemType' => $res['item_type'],
				'userId'   => $res['user_id']
			]));

			$ret = parent::delete($pk);

			// Dispatch an event after message delete
			$service->event->dispatch(new Event('message.deleted', [
				'msgId'    => $res['id'],
				'itemId'   => $res['item_id'],
				'itemType' => $res['item_type'],
				'userId'   => $res['user_id']
			]));

			// Delete pictures
			if (!empty($res['id'])) {
				$dir = UPLOAD_DIR . '/photo/message/' . $res['id'];
				Upload::delete($dir);
			}

			return $ret;
		} catch (\Sy\Bootstrap\Service\Crud\Exception $e) {
			return 0;
		}
	}

}
