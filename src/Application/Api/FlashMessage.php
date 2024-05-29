<?php
namespace Sy\Bootstrap\Application\Api;

class FlashMessage extends \Sy\Bootstrap\Component\Api {

	public function security() {

	}

	/**
	 * Retrieve the flash message saved in session
	 */
	public function getAction() {
		return $this->ok(\Sy\Bootstrap\Lib\FlashMessage::getMessage());
	}

	/**
	 * Delete the flash message saved in session
	 */
	public function deleteAction() {
		\Sy\Bootstrap\Lib\FlashMessage::clearMessage();
		return $this->ok();
	}

}