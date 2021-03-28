<?php
namespace Sy\Bootstrap\Component\Modal;

class Dialog extends \Sy\Component\WebComponent {

	/**
	 * @var string
	 */
	private $id;

	/**
	 * @var string
	 */
	private $title;

	/**
	 * @var mixed String or WebComponent
	 */
	private $body;

	/**
	 * @var mixed String or WebCcomponent
	 */
	private $footer;

	/**
	 * @var string sm | lg | xl | fullscreen
	 */
	private $size;

	/**
	 * @param string $id
	 * @param string $title
	 * @param string|WebComponent $body
	 * @param string|WebComponent $footer
	 */
	public function __construct($id, $title = null, $body = null, $footer = null) {
		parent::__construct();
		$this->id     = $id;
		$this->title  = $title;
		$this->body   = $body;
		$this->footer = $footer;
		$this->size   = null;
	}

	public function __toString() {
		$this->init();
		return parent::__toString();
	}

	public function setTitle($title) {
		$this->title = $title;
	}

	public function setBody($body) {
		$this->body = $body;
	}

	public function setFooter($footer) {
		$this->footer = $footer;
	}

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
			if ($this->body instanceof \Sy\Component\WebComponent) {
				$this->setComponent('BODY', $this->body);
			} else {
				$this->setVar('BODY', $this->body);
			}
		}

		// Footer
		if (!is_null($this->footer)) {
			if ($this->body instanceof \Sy\Component\WebComponent) {
				$this->setComponent('BODY', $this->body);
			} else {
				$this->setVar('BODY', $this->body);
			}
			$this->setBlock('FOOTER_BLOCK');
		}

		// Size
		if (!is_null($this->size)) {
			$this->setVar('SIZE', 'modal-' . $this->size);
		}
	}

}