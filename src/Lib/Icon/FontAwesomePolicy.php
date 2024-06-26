<?php
namespace Sy\Bootstrap\Lib\Icon;

class FontAwesomePolicy implements IPolicy {

	/**
	 * @var string
	 */
	private $cdnJsUrl;

	public function __construct(string $cdnJsUrl) {
		$this->cdnJsUrl = $cdnJsUrl;
	}

	/**
	 * @inheritDoc
	 */
	public function match(string $iconName) {
		return str_starts_with($iconName, 'fa-');
	}

	/**
	 * @inheritDoc
	 */
	public function apply(\Sy\Bootstrap\Component\Icon $icon) {
		$icon->setTemplateContent('<span class="fa-solid {NAME} {CLASS}"></span>');
		$icon->setVars([
			'NAME'  => $icon->getName(),
			'CLASS' => $icon->getOption('class'),
		]);
		$icon->addJsLink($this->cdnJsUrl);
	}

}