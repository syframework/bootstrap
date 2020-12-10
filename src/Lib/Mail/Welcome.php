<?php
namespace Sy\Bootstrap\Lib\Mail;

class Welcome extends HtmlTranslate {

	/**
	 * @var string
	 */
	private $token;

	/**
	 * @var string
	 */
	private $password;

	public function __construct($to, $password, $token) {
		parent::__construct();
		$this->token    = $token;
		$this->password = $password;
		$this->setTo($to);
		if (explode('@', $to)[1] === 'free.fr') $this->setCc('nospam@nospam.proxad.net');
	}

	protected function init() {
		// From
		$this->setFrom(PROJECT . ' <' . TEAM_MAIL . '>');

		// Subject
		$this->setSubject($this->translate('Welcome'));

		// Body
		$activateUrl = PROJECT_URL . \Sy\Bootstrap\Lib\Url::build('user', 'activate', [
			'email'    => $this->getTo(),
			'password' => $this->password,
			'token'    => $this->token
		]);
		$reportUrl = PROJECT_URL . \Sy\Bootstrap\Lib\Url::build('user', 'report', [
			'email' => $this->getTo(),
			'token' => $this->token
		]);
		$txt = new \Sy\Component\WebComponent();
		$txt->setTemplateFile(__DIR__ . '/Welcome.txt');
		$txt->addTranslator(LANG_DIR . '/mail');
		$txt->setVars([
			'ACTIVATE_URL' => $activateUrl,
			'REPORT_URL' => $reportUrl,
			'PASSWORD' => $this->password,
		]);

		$html = new \Sy\Component\WebComponent();
		$html->setTemplateFile(__DIR__ . '/Welcome.html');
		$html->addTranslator(LANG_DIR . '/mail');
		$html->setVars([
			'PASSWORD' => $this->password
		]);
		$html->setComponent('BUTTON', new Button('Activate my account and connect me', $activateUrl));

		$this->setText($txt->__toString());
		$this->setHtml($html->__toString(), $this->translate('Activate my account and connect me'));

		// Footer
		$this->getHtmlComponent()->setVars([
			'FOOTER_LINK_MESSAGE' => $this->translate('If you have not ask to receive this mail please report by clicking on'),
			'FOOTER_LINK_LABEL'   => $this->translate('Report this error'),
			'FOOTER_LINK_URL'     => $reportUrl,
		]);
		$this->getHtmlComponent()->setBlock('FOOTER_LINK_BLOCK');
	}

}