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

	abstract protected function init();

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
		$this->mail();
	}

	private function mail() {
		$this->addTextBody();
		$this->addHtmlBody();
		parent::send();
	}

	private function addTextBody() {
		$this->addText($this->getText());
	}

	private function addHtmlBody() {
		$this->addBody($this->getHtml());
	}

}