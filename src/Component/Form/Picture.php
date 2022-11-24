<?php
namespace Sy\Bootstrap\Component\Form;

class Picture extends \Sy\Component\Html\Form\Element {

	/**
	 * @var string
	 */
	private $value;

	/**
	 * @var array
	 */
	private $options;

	/**
	 * @param array $options Options available :
	 * 'name'  => 'picture' by default (hidden input name)
	 * 'class' => '' by default (button class)
	 * 'color' => 'primary' | 'secondary' by default | 'info' | 'warning' | 'danger' (button color)
	 * 'size'  => '' by default | 'sm' | 'lg' (button size)
	 * 'icon'  => 'camera' by default (font awesome icon name)
	 * 'label' => '' by default (button label text)
	 * 'title' => '' by default (button title attribute)
	 * 'img-min-width'  => 50 by default (image minimum width)
	 * 'img-min-height' => 50 by default (image minimum width)
	 * 'img-max-width'  => 750 by default (image maximum width, will be resized if larger)
	 * 'img-max-height' => 750 by default (image maximum width, will be resized if larger)
	 * 'img-max-count'  => 20 by default (uploadable image number)
	 * 'img-quality'    => 0.7 by default (jpg image compression quality)
	 * 'required' => true | false by default
	 */
	public function __construct(array $options) {
		parent::__construct();
		$this->value   = '';
		$this->options = $options;
		$this->setTemplateFile(__DIR__ . '/Picture.tpl');
	}

	/**
	 * @param string $value JSON
	 * {
	 *     "id1": {"image": "...", "caption": "..."},
	 *     "id2": {"image": "...", "caption": "..."},
	 *     ...
	 * }
	 */
	public function setValue($value) {
		$this->value = $value;
	}

	private function init() {
		$this->addTranslator(LANG_DIR);

		$imgMinWidth = isset($this->options['img-min-width']) ? intval($this->options['img-min-width']) : 50;
		$imgMinWidth =  $imgMinWidth < 1 ? 1 : $imgMinWidth;
		$imgMinHeight = isset($this->options['img-min-height']) ? intval($this->options['img-min-height']) : 50;
		$imgMinHeight =  $imgMinHeight < 1 ? 1 : $imgMinHeight;
		$imgMaxWidth = isset($this->options['img-max-width']) ? intval($this->options['img-max-width']) : 750;
		$imgMaxWidth =  $imgMaxWidth < 1 ? 1 : $imgMaxWidth;
		$imgMaxHeight = isset($this->options['img-max-height']) ? intval($this->options['img-max-height']) : 750;
		$imgMaxHeight =  $imgMaxHeight < 1 ? 1 : $imgMaxHeight;
		$imgMaxCount = isset($this->options['img-max-count']) ? intval($this->options['img-max-count']) : 20;
		$imgMaxCount =  $imgMaxCount < 1 ? 1 : $imgMaxCount;
		$imgQuality = isset($this->options['img-quality']) ? floatval($this->options['img-quality']) : 0.7;
		$imgQuality =  $imgQuality < 0.1 ? 0.1 : $imgQuality;

		$this->setVars([
			'NAME'  => isset($this->options['name'])  ? $this->options['name']            : 'picture',
			'CLASS' => isset($this->options['class']) ? $this->options['class']           : '',
			'COLOR' => isset($this->options['color']) ? $this->options['color']           : 'secondary',
			'SIZE'  => isset($this->options['size'])  ? $this->options['size']            : '',
			'ICON'  => isset($this->options['icon'])  ? $this->options['icon']            : 'camera',
			'LABEL' => isset($this->options['label']) ? $this->_($this->options['label']) : '',
			'TITLE' => isset($this->options['title']) ? $this->_($this->options['title']) : '',
			'VALUE' => $this->value,
			'REQUIRED'       => (isset($this->options['required']) and $this->options['required']) ? 'required' : '',
			'MULTIPLE'       => $imgMaxCount > 1 ? 'multiple' : '',
			'IMG_MAX_COUNT'  => $imgMaxCount,
			'IMG_MIN_WIDTH'  => $imgMinWidth,
			'IMG_MAX_WIDTH'  => $imgMaxWidth,
			'IMG_MIN_HEIGHT' => $imgMinHeight,
			'IMG_MAX_HEIGHT' => $imgMaxHeight,
			'IMG_QUALITY'    => $imgQuality,
		]);

		$this->addJsCode(__DIR__ . '/Picture.js');
	}

	public function __toString() {
		$this->init();
		return parent::__toString();
	}

}