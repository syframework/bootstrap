<?php
namespace Sy\Bootstrap\Component\Form;

class UploadButton extends \Sy\Component\Html\Form\Element {

	private $icon;
	private $accept;

	public function __construct($label, $icon = 'upload', $accept = '') {
		parent::__construct();
		$this->addTranslator(LANG_DIR);
		$this->setTemplateFile(__DIR__ . '/UploadButton.tpl');
		$maxSize = \Sy\Bootstrap\Lib\Upload::getMaxFileSize();
		$this->setVars([
			'MAX_UPLOAD_SIZE' => $maxSize,
			'MAX_SIZE'        => $maxSize/(1024*1024),
			'LABEL'           => $label,
		]);
		$this->icon   = $icon;
		$this->accept = $accept;
	}

	public function setIcon($icon) {
		$this->icon = $icon;
	}

	public function setAccept($accept) {
		$this->accept = $accept;
	}

	public function __toString() {
		$this->setVars([
			'ICON'   => $this->icon,
			'ACCEPT' => $this->accept,
		]);
		return parent::__toString();
	}

}