<?php
namespace Sy\Bootstrap\Component\Form;

class Contact extends \Sy\Bootstrap\Component\Form {

	/**
	 * Send to email address
	 *
	 * @var string
	 */
	private $email;

	/**
	 * @var \Sy\Component\Html\Form\Textarea
	 */
	private $textarea;

	/**
	 * @var string
	 */
	private $subject;

	/**
	 * @var string
	 */
	private $message;

	public function __construct($email, $subject = null, $message = null) {
		parent::__construct();
		$this->email = $email;
		$this->subject = $subject;
		$this->message = $message;
	}

	/**
	 * @return string
	 */
	public function getEmail() {
		return $this->email;
	}

	/**
	 * @return \Sy\Component\Html\Form\Textarea
	 */
	public function getTextarea() {
		return $this->textarea;
	}

	public function init() {
		// Email field
		$service = \Project\Service\Container::getInstance();
		$mail = '';
		if ($service->user->getCurrentUser()->isConnected()) {
			$mail = $service->user->getCurrentUser()->email;
		}
		$this->addEmail([
			'name'        => 'email',
			'required'    => 'required',
			'value'       => $mail,
			'placeholder' => 'Your e-mail',
		], [
			'label' => 'E-mail',
		]);

		// Anti spam field
		$this->addAntiSpamField();

		// Message field
		$this->textarea = $this->addTextarea([
			'name'        => 'message',
			'required'    => 'required',
			'placeholder' => 'Your message',
		], [
			'label' => 'Message',
		]);

		// Send button
		$div = $this->addDiv(['class' => 'text-right']);
		$this->addButton('Send', [], ['icon' => 'fas fa-paper-plane'], $div);
	}

	public function submitAction() {
		try {
			$this->validatePost();
			$message = nl2br($this->post('message'));
			$mail = new \Sy\Bootstrap\Lib\Mail(
				$this->email,
				PROJECT . ' <' . TEAM_MAIL . '>',
				empty($this->subject) ? $this->_('Message from') . ' ' . $this->post('email') : str_replace('%email%', $this->post('email'), $this->subject),
				empty($this->message) ? $message : str_replace(['%email%', '%message%'], [$this->post('email'), $message], $this->message)
			);
			$mail->setReplyTo($this->post('email'));
			$mail->send();
			$this->setSuccess($this->_('Message sent'));
		} catch (\Sy\Component\Html\Form\Exception $e) {
			$this->logWarning($e);
			$this->setError($this->_('Please fill the form correctly'));
			$this->fill($_POST);
		} catch (\Sy\Mail\Exception $e) {
			$this->logWarning($e);
			$this->setError($this->_('Message not sent'));
			$this->fill($_POST);
		}
	}

}