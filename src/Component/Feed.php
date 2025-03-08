<?php
namespace Sy\Bootstrap\Component;

abstract class Feed extends \Sy\Component\WebComponent {

	private $start;

	private $last;

	private $direction;

	private $auto;

	/**
	 * Set to true to remove parent container <div class="sy-feed">
	 *
	 * @var bool
	 */
	private $flush;

	abstract public function getPage($n);

	abstract public function isLastPage($n);

	public function __construct($start = 0, $direction = 'DOWN', $auto = true) {
		parent::__construct();
		$this->start     = $start;
		$this->last      = $this->get('last');
		$this->direction = $direction;
		$this->auto      = $auto;
		$this->flush     = false;
		// pre init js
		$this->addJsCode(__DIR__ . '/Feed/Feed.js');

		$this->mount(fn () => $this->init());
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

	public function flush() {
		$this->flush = true;
	}

	public function init() {
		$service = \Project\Service\Container::getInstance();
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
			'LOCATION' => \Sy\Bootstrap\Lib\Url::build('api', 'feed', ['language' => $service->lang->getLang()]),
			'PARAMS'  => htmlspecialchars(json_encode($this->getParams(), JSON_FORCE_OBJECT), ENT_QUOTES, 'UTF-8'),
			'START'   => $this->start,
		]);
		$auto = $this->auto ? '_AUTO' : '';
		$this->setBlock($this->direction . '_MORE' . $auto . '_BLOCK');

		// Wrap with a div on initial render
		if (!$this->flush) {
			$this->setBlock('DIV_OPEN');
			$this->setBlock('DIV_CLOSE');
		}
	}

}