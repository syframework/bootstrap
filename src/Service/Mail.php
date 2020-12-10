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

	public function sendReservationReplyNotification($email, $reservationId, $language) {
		$service = \Sy\Bootstrap\Service\Container::getInstance();
		$reservation = $service->reservation->retrieve(['id' => $reservationId]);
		$mail = new \Sy\Bootstrap\Lib\Mail\Notification($email, [
			'subject' => 'New message about a reservation',
			'message' => 'You received a new message about a reservation at %s',
			'message_args' => ['<strong>' . $reservation['place_title'] . '</strong>'],
			'link' => 'See reservation',
			'url' => PROJECT_URL . WEB_ROOT . \Sy\Bootstrap\Lib\Url::build('page', 'reservation', ['id' => $reservationId]),
		]);
		$mail->setLang($language);
		$mail->send();
	}

}