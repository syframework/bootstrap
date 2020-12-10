<?php
namespace Sy\Bootstrap\Service;

/**
 * A service where all methods always return null
 */
class NullService {

	public function __call($name, $arguments) {
		return null;
	}

}