<?php
namespace Sy\Bootstrap\Component;

use Sy\Component\WebComponent;

class Icon extends WebComponent {

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var array
	 */
	private $options;

	public function __construct(string $name, array $options = []) {
		$this->name = $name;
		$this->options = $options;

		$this->mount(function () {
			$this->init();
		});
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return array
	 */
	public function getOptions() {
		return $this->options;
	}

	private function init() {
		$policy = \Sy\Bootstrap\Lib\Icon\PolicyManager::retrievePolicy($this->name);
		if (!$policy) return;
		$policy->apply($this);
	}

}