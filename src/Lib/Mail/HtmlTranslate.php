<?php
namespace Sy\Bootstrap\Lib\Mail;

abstract class HtmlTranslate extends \Sy\Bootstrap\Lib\Mail {

	/**
	 * @var \Sy\Translate\PhpTranslator
	 */
	private $translator;

	/**
	 * @var string
	 */
	private $lang;

	/**
	 * @var string
	 */
	private $text;

	/**
	 * @var \Sy\Component\WebComponent
	 */
	private $htmlComponent;

	public function __construct($to = '', $from = '', $subject = '', $body = '') {
		parent::__construct($to, $from, $subject, $body);
		$this->lang = \Sy\Translate\LangDetector::getInstance(LANG)->getLang();
		$this->htmlComponent = new \Sy\Component\WebComponent();
		$this->htmlComponent->setTemplateFile(__DIR__ . '/HtmlTranslate.html');
	}

	public function setLang($lang) {
		$this->lang = $lang;
	}

	public function getTranslator() {
		if (!isset($this->translator)) {
			$this->translator = new \Sy\Translate\PhpTranslator();
			$this->translator->setTranslationLang($this->lang);
			$this->translator->setTranslationDir(LANG_DIR . '/mail');
			$this->translator->loadTranslationData();
		}
		return $this->translator;
	}

	public function translate($text) {
		$translated = $this->getTranslator()->translate($text);
		return empty($translated) ? $text : $translated;
	}

	public function getText() {
		return $this->text;
	}

	public function setText($text) {
		$this->text = $text;
	}

	public function getHtml() {
		return $this->htmlComponent->__toString();
	}

	/**
	 * @return \Sy\Component\WebComponent
	 */
	public function getHtmlComponent() {
		return $this->htmlComponent;
	}

	public function setHtml($html, $preheader = '') {
		$this->htmlComponent->setVars([
			'BODY'        => $html,
			'PREHEADER'   => $preheader,
			'TITLE'       => PROJECT,
			'PROJECT'     => PROJECT,
			'PROJECT_URL' => PROJECT_URL,
		]);
	}

	public function send() {
		$this->init();
		$company = explode('.', explode('@', $this->getTo())[1])[0];
		if (in_array($company, ['hotmail', 'live', 'outlook'])) {
			$this->mailjet();
		} else {
			$this->mail();
		}
	}

	private function mail() {
		$company = explode('.', explode('@', $this->getTo())[1])[0];
		if ($company === 'free') $this->setCc('nospam@nospam.proxad.net');
		//$this->setContentType('multipart/alternative; boundary=' . $this->separator);
		$this->addTextBody();
		$this->addHtmlBody();
		parent::send();
	}

	private function tipimail() {
		$tipimail = new \Tipimail\Tipimail(TIPIMAIL_USER, TIPIMAIL_KEY);
		$data = new \Tipimail\Messages\Message();
		$data->addTo($this->getTo(), null);
		$data->setFrom(TEAM_MAIL, PROJECT);
		$data->setSubject($this->getSubject());
		$data->setHtml($this->getHtml());
		$data->setText($this->getText());
		$tipimail->getMessagesService()->send($data);
	}

	private function mailjet() {
		$mj = new \Mailjet\Client(MJ_APIKEY_PUBLIC, MJ_APIKEY_PRIVATE);
		$body = [
			'FromEmail' => TEAM_MAIL,
			'FromName'  => PROJECT,
			'Subject'   => $this->getSubject(),
			'Text-part' => $this->getText(),
			'Html-part' => $this->getHtml(),
			'To'        => $this->getTo()
		];
		$response = $mj->post(\Mailjet\Resources::$Email, ['body' => $body]);
		if (!$response->success()) {
			$this->mail();
		}
	}

	private function addTextBody() {
		$this->addText($this->getText());
	}

	private function addHtmlBody() {
		$this->addBody($this->getHtml());
	}

	abstract protected function init();

}