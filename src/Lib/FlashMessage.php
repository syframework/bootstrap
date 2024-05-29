<?php
namespace Sy\Bootstrap\Lib;

class FlashMessage {

	/**
	 * Set a session flash message
	 *
	 * @param string|array $message String or an associative array with 2 keys: 'title' and 'body'
	 * @param string $color 'success', 'info', 'warning', 'danger'
	 * @param boolean $autohide
	 */
	public static function setMessage($message, $color = 'success', $autohide = true) {
		if (!session_id()) session_start();
		$_SESSION['flash_message'] = [
			'message'  => $message,
			'color'    => $color,
			'autohide' => $autohide,
		];
	}

	/**
	 * Set a flash error message shortcut
	 *
	 * @param string|array $message
	 */
	public static function setError($message) {
		self::setMessage($message, 'danger');
	}

	/**
	 * Retrieve session flash message
	 *
	 * @return array
	 */
	public static function getMessage() {
		if (!session_id()) session_start();
		return $_SESSION['flash_message'] ?? [];
	}

	/**
	 * Delete session flash message
	 */
	public static function clearMessage() {
		if (!session_id()) session_start();
		unset($_SESSION['flash_message']);
	}

}