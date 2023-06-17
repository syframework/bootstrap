<?php
namespace Sy\Bootstrap\Component\Modal;

use Sy\Component\WebComponent;

class Dialog extends WebComponent {

	/**
	 * @var string
	 */
	private $id;

	/**
	 * @var string
	 */
	private $title;

	/**
	 * @var mixed String or WebComponent or Array of String or WebComponent
	 */
	private $body;

	/**
	 * @var mixed String or WebCcomponent or Array of String or WebComponent
	 */
	private $footer;

	/**
	 * @var string sm | lg | xl | fullscreen
	 */
	private $size;

	/**
	 * @param string $id
	 * @param string $title
	 * @param string|WebComponent|array $body
	 * @param string|WebComponent|array $footer
	 */
	public function __construct($id, $title = null, $body = null, $footer = null) {
		parent::__construct();
		$this->id     = $id;
		$this->title  = $title;
		$this->body   = $body;
		$this->footer = $footer;
		$this->size   = null;

		$this->mount(function () {
			$this->init();
		});
	}

	/**
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * @param mixed $body String or WebComponent or Array of String or WebComponent
	 */
	public function setBody(...$body) {
		// Flatten array
		$res = [];
		array_walk_recursive($body, function($a) use (&$res) {
			$res[] = $a;
		});
		$this->body = $res;
	}

	/**
	 * @param mixed $footer String or WebComponent or Array of String or WebComponent
	 */
	public function setFooter(...$footer) {
		// Flatten array
		$res = [];
		array_walk_recursive($footer, function($a) use (&$res) {
			$res[] = $a;
		});
		$this->footer = $res;
	}

	/**
	 * @param string $size sm | lg | xl | fullscreen
	 */
	public function setSize($size) {
		$this->size = $size;
	}

	private function init() {
		$this->addTranslator(LANG_DIR);
		$this->setTemplateFile(__DIR__ . '/Dialog.html');
		$this->addJsCode(__DIR__ . '/Dialog.js');

		// Id
		$this->setVar('ID', $this->id);

		// Title
		if (!is_null($this->title)) {
			$this->setVar('TITLE', $this->_($this->title));
			$this->setBlock('TITLE_BLOCK');
		}

		// Body
		if (!is_null($this->body)) {
			if (is_array($this->body)) {
				foreach ($this->body as $body) {
					$this->setVar('BODY', $body, true);
				}
			} else {
				$this->setVar('BODY', $this->body);
			}
		}

		// Footer
		if (!is_null($this->footer)) {
			if (is_array($this->footer)) {
				foreach ($this->footer as $footer) {
					$this->setVar('FOOTER', $footer, true);
				}
			} else {
				$this->setVar('FOOTER', $this->footer);
			}
			$this->setBlock('FOOTER_BLOCK');
		}

		// Size
		if (!is_null($this->size)) {
			$this->setVar('SIZE', 'modal-' . $this->size);
		}
	}

}