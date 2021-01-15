<?php
namespace Sy\Bootstrap\Service;

class Mail {

	public function sendWelcome($email, $password, $token) {
		$mail = new \Sy\Bootstrap\Lib\Mail\Welcome($email, $password, $token);
		$mail->send();
	}

	public function sendForgetPassword($email, $token) {
		$mail = new \Sy\Bootstrap\Lib\Mail\ForgetPassword($email, $token);
		$mail->send();
	}

}