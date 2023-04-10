<?php
namespace Sy\Test\Lib\Url;

use PHPUnit\Framework\TestCase;
use Sy\Bootstrap\Lib\Url\AliasManager;

class AliasManagerTest extends TestCase {

	public function testRetrieveAlias() {
		$this->assertEquals('first/alias/in/french', AliasManager::retrieveAlias('first/realpath', 'fr'));
		$this->assertEquals(null, AliasManager::retrieveAlias('not/exist', 'fr'));
	}

	public function testRetrievePath() {
		$this->assertEquals(['first/realpath', 'fr'], AliasManager::retrievePath('first/alias/in/french'));
		$this->assertEquals([null, null], AliasManager::retrievePath('not/exist'));
	}

	protected function setUp(): void {
		AliasManager::setAliasFile(__DIR__ . '/alias.php');
	}

}