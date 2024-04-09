<?php
namespace Sy\Bootstrap\Lib;

class FlashMessage {

	/**
	 * Set a session flash message
	 *
	 * @param string or array $message
	 * @param string $type 'success', 'info', 'warning', 'danger'
	 * @param int $timeout
	 */
	public static function setMessage($message, $type = 'success', $timeout = 3500) {
		if (!session_id()) session_start();
		if (is_array($message)) {
			$_SESSION['flash_message_title'] = $message['title'];
			$_SESSION['flash_message'] = $message['message'];
		} else {
			$_SESSION['flash_message_title'] = '';
			$_SESSION['flash_message'] = $message;
		}
		$_SESSION['flash_message_type'] = $type;
		$_SESSION['flash_message_timeout'] = $timeout;
	}

	/**
	 * Set a flash error message shortcut
	 *
	 * @param string $message
	 */
	public static function setError($message) {
		self::setMessage($message, 'danger');
	}

	/**
	 * Retrieve session flash message
	 *
	 * @return string
	 */
	public static function getMessage() {
		if (!session_id()) session_start();
		$message = isset($_SESSION['flash_message']) ? $_SESSION['flash_message'] : null;
		return $message;
	}

	/**
	 * Delete session flash message
	 */
	public static function clearMessage() {
		if (!session_id()) session_start();
		unset($_SESSION['flash_message']);
	}

}