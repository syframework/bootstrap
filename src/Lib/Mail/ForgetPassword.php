<?php
namespace Sy\Bootstrap\Lib\Mail;

class ForgetPassword extends \Sy\Bootstrap\Lib\Mail\HtmlTranslate {

	/**
	 * @var string
	 */
	private $token;

	public function __construct($to, $token) {
		parent::__construct($to);
		$this->token = $token;
		$this->setTo($to);
	}

	protected function init() {
		// From
		$this->setFrom(PROJECT . ' <' . TEAM_MAIL . '>');

		// Subject
		$this->setSubject($this->translate('Forget password'));

		// Body
		$url = PROJECT_URL . \Sy\Bootstrap\Lib\Url::build('page', 'user-password', [
			'email' => $this->getTo(),
			'token' => $this->token
		]);

		$txt = new \Sy\Component\WebComponent();
		$txt->setTemplateFile(__DIR__ . '/ForgetPassword.txt');
		$txt->addTranslator(LANG_DIR . '/mail');
		$txt->setVars([
			'RESET_LINK' => $url,
		]);
		$this->setText($txt->__toString());

		$html = new \Sy\Component\WebComponent();
		$html->setTemplateFile(__DIR__ . '/ForgetPassword.html');
		$html->addTranslator(LANG_DIR . '/mail');
		$html->setComponent('BUTTON', new Button('Choose my new password', $url));
		$this->setHtml($html->__toString(), $this->translate('Choose my new password'));
	}

}