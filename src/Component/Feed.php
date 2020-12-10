<?php
namespace Sy\Bootstrap\Component;

abstract class Feed extends \Sy\Component\WebComponent {

	private $start;

	private $last;

	private $direction;

	private $auto;

	public function __construct($start = 0, $direction = 'DOWN', $auto = true) {
		parent::__construct();
		$this->start     = $start;
		$this->last      = $this->get('last');
		$this->direction = $direction;
		$this->auto      = $auto;
		// pre init js
		$this->addJsCode(__DIR__ . '/Feed/Feed.js');
	}

	public function __toString() {
		$this->init();
		return parent::__toString();
	}

	public function getTemplate() {
		return __DIR__ . '/Feed/Feed.html';
	}

	public function getParams() {
		return [];
	}

	public function setLast($last) {
		$this->last = $last;
	}

	abstract public function getPage($n);

	abstract public function isLastPage($n);

	public function init() {
		$this->addTranslator(LANG_DIR);
		$this->setTemplateFile($this->getTemplate());

		// body
		$this->setComponent('BODY', $this->getPage($this->last));

		// next button
		if ($this->isLastPage($this->last)) {
			$this->setVar('HIDDEN', 'd-none');
		}
		$this->setVars([
			'CLASS'   => get_class($this),
			'LOCATION'=> \Sy\Bootstrap\Lib\Url::build('api', 'feed', ['sy_language' => \Sy\Translate\LangDetector::getInstance()->getLang()]),
			'PARAMS'  => htmlspecialchars(json_encode($this->getParams(), JSON_FORCE_OBJECT), ENT_QUOTES, 'UTF-8'),
			'START'   => $this->start
		]);
		$auto = $this->auto ? '_AUTO' : '';
		$this->setBlock($this->direction . '_MORE' . $auto . '_BLOCK');
	}

}