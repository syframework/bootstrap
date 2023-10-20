<?php
namespace Sy\Test\Service;

use PHPUnit\Framework\TestCase;
use Sy\Bootstrap\Service\Lang;

class LangTest extends TestCase {

	public function testGetLang() {
		$lang = new Lang();
		$this->assertEquals('fr', $lang->getLang());
	}

}