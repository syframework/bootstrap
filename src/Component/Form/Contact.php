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

	/**
	 * @param string $email
	 * @param string|null $subject
	 * @param string|null $message
	 */
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

		// Security fields
		$this->addAntiSpamField();
		$this->addCsrfField();

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
		$this->addButton(label: 'Send', options: ['icon' => 'send'], container: $div);
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
			return $this->jsonSuccess('Message sent');
		} catch (\Sy\Component\Html\Form\Exception $e) {
			$this->logWarning($e);
			return $this->jsonError('Please fill the form correctly');
		} catch (\Sy\Mail\Exception $e) {
			$this->logWarning($e);
			return $this->jsonError('Message not sent');
		}
	}

}