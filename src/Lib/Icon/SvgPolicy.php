<?php
namespace Sy\Bootstrap\Lib\Icon;

class SvgPolicy implements IPolicy {

	/**
	 * @var string
	 */
	private $directory;

	public function __construct(string $directory) {
		$this->directory = $directory;
	}

	/**
	 * @inheritDoc
	 */
	public function match(string $iconName) {
		return (count(glob($this->directory . '/' . $iconName . '.svg')) > 0);
	}

	/**
	 * @inheritDoc
	 */
	public function apply(\Sy\Bootstrap\Component\Icon $icon) {
		$icon->setTemplateFile($this->directory . '/' . $icon->getName() . '.svg');
	}

}