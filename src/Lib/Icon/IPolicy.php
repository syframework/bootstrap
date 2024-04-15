<?php
namespace Sy\Bootstrap\Lib\Icon;

interface IPolicy {

	/**
	 * Return true if the icon name match to the icon policy
	 *
	 * @param  string $iconName
	 * @return bool
	 */
	public function match(string $iconName);

	/**
	 * Apply the icon policy
	 *
	 * @param \Sy\Bootstrap\Component\Icon $icon
	 */
	public function apply(\Sy\Bootstrap\Component\Icon $icon);

}