<?php
namespace Sy\Bootstrap\Component\Form;

class Avatar extends \Sy\Component\WebComponent {

	private $src;
	private $size;
	private $upload;

	/**
	 * @param string $src Image source
	 * @param string $upload Upload URL
	 * @param int $size Avatar size in pixel
	 */
	public function __construct($src, $upload, $size = 100) {
		parent::__construct();
		$this->src     = $src;
		$this->upload  = $upload;
		$this->size    = $size;
	}

	public function __toString() {
		$this->init();
		return parent::__toString();
	}

	private function init() {
		$this->addTranslator(LANG_DIR);
		$this->setTemplateFile(__DIR__ . '/Avatar.tpl');
		$this->addJsCode(__DIR__ . '/Avatar.js');

		// Include cropperjs
		$this->addJsLink(CROPPER_JS);
		$this->addCssLink(CROPPER_CSS);

		$service = \Sy\Bootstrap\Service\Container::getInstance();
		$this->setVars([
			'IMG_SRC'    => $this->src,
			'SIZE'       => $this->size,
			'CSRF_TOKEN' => $service->user->getCsrfToken(),
			'UPLOAD_URL' => $this->upload,
		]);
	}

}
