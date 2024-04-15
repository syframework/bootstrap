<?php
namespace Sy\Bootstrap\Lib\Icon;

class PolicyManager {

	private static $policies = [];

	/**
	 * Add an icon policy
	 *
	 * @param IPolicy $policy
	 */
	public static function addPolicy(IPolicy $policy) {
		self::$policies[] = $policy;
	}

	/**
	 * Return the matching policy
	 *
	 * @param  string $iconName
	 * @return IPolicy|null
	 */
	public static function retrievePolicy(string $iconName) {
		foreach (self::$policies as $policy) {
			if ($policy->match($iconName)) {
				return $policy;
			}
		}
		return null;
	}

}