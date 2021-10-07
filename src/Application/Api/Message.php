<?php
namespace Sy\Bootstrap\Application\Api;

class Message extends \Sy\Bootstrap\Component\Api {

	public function security() {
		$service = \Sy\Bootstrap\Service\Container::getInstance();
		$user = $service->user->getCurrentUser();
		if ($user->isConnected()) return;
		$this->forbidden(['status' => 'ko', 'message' => $this->_('Session expired')]);
	}

	public function dispatch() {
		$method = \Sy\Bootstrap\Lib\Str::snakeToCaml($this->method);
		if (empty($method)) $this->requestError();
		$this->$method();
	}

	public function create() {
		$form = new \Sy\Bootstrap\Component\Message\Received\AddForm($this->post('item_id'), $this->post('item_type'));
		$form->submitAction();
	}

	public function update() {
		$form = new \Sy\Bootstrap\Component\Message\Received\EditForm();
		$form->submitAction();
	}

	public function delete() {
		$service = \Sy\Bootstrap\Service\Container::getInstance();
		$user = $service->user->getCurrentUser();
		$message = $service->messageReceived->retrieve(['id' => $this->post('message_id')]);
		if (!$user->hasPermission('message-delete') and $user->id !== $message['user_id']) $this->forbidden();
		$form = new \Sy\Bootstrap\Component\Message\Received\DeleteForm($this->post('message_id'));
		$form->submitAction();
	}

	public function createReply() {
		$form = new \Sy\Bootstrap\Component\Message\Reply\AddForm($this->post('message_id'));
		$form->submitAction();
	}

	public function deleteReply() {
		$service = \Sy\Bootstrap\Service\Container::getInstance();
		$user = $service->user->getCurrentUser();
		$message = $service->messageReply->retrieve(['id' => $this->post('message_id')]);
		if (!$user->hasPermission('message-delete') and $user->id !== $message['user_id']) $this->forbidden();
		$form = new \Sy\Bootstrap\Component\Message\Reply\DeleteForm($this->post('message_id'));
		$form->submitAction();
	}

}