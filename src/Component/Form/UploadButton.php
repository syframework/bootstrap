<?php
namespace Sy\Bootstrap\Component\Form;

use Sy\Bootstrap\Component\Icon;

class UploadButton extends \Sy\Component\Html\Form\Element {

	/**
	 * @var Icon|string
	 */
	private $icon;

	/**
	 * @var string
	 */
	private $accept;

	/**
	 * @param string $label
	 * @param Icon|string $icon
	 * @param string $accept
	 */
	public function __construct($label, $icon = 'upload', $accept = '') {
		parent::__construct();
		$this->addTranslator(LANG_DIR);
		$this->setTemplateFile(__DIR__ . '/UploadButton.tpl');
		$maxSize = \Sy\Bootstrap\Lib\Upload::getMaxFileSize();
		$this->setVars([
			'MAX_UPLOAD_SIZE' => $maxSize,
			'MAX_SIZE'        => $maxSize / (1024 * 1024),
			'LABEL'           => $label,
		]);
		$this->icon   = $icon;
		$this->accept = $accept;

		$this->mount(function () {
			$this->init();
		});
	}

	/**
	 * @param Icon|string $icon
	 */
	public function setIcon($icon) {
		if (is_string($icon)) {
			$icon = new Icon($icon);
		}
		$this->icon = $icon;
	}

	/**
	 * @param string $accept
	 */
	public function setAccept($accept) {
		$this->accept = $accept;
	}

	private function init() {
		$this->setVars([
			'ICON'   => $this->icon,
			'ACCEPT' => $this->accept,
		]);
	}

}