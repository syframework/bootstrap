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

	/**
	 * Return option value
	 *
	 * @param  string $optionName
	 * @return mixed
	 */
	public function getOption($optionName) {
		$res = isset($this->options[$optionName]) ? $this->options[$optionName] : '';
		return $res;
	}

	private function init() {
		$policy = \Sy\Bootstrap\Lib\Icon\PolicyManager::retrievePolicy($this->name);
		if (!$policy) return;
		$policy->apply($this);
	}

}