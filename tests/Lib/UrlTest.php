<?php
namespace Sy\Test\Lib;

use PHPUnit\Framework\TestCase;
use Sy\Bootstrap\Lib\Url;

/**
 * @backupStaticAttributes enabled
 */
class UrlTest extends TestCase {

	public function testBuildWithoutConverter() {
		$_SERVER['PHP_SELF'] = WEB_ROOT . '/index.php';
		$this->assertEquals(
			WEB_ROOT . '/index.php?' . CONTROLLER_TRIGGER . '=controller',
			Url::build('controller')
		);
		$this->assertEquals(
			WEB_ROOT . '/index.php?' . CONTROLLER_TRIGGER . '=controller&' . ACTION_TRIGGER . '=action',
			Url::build('controller', 'action')
		);
		$this->assertEquals(
			WEB_ROOT . '/index.php?' . CONTROLLER_TRIGGER . '=controller&' . ACTION_TRIGGER . '=action&param1=value1&param2=value2',
			Url::build('controller', 'action', ['param1' => 'value1', 'param2' => 'value2'])
		);
		$this->assertEquals(
			WEB_ROOT . '/index.php?' . CONTROLLER_TRIGGER . '=controller&' . ACTION_TRIGGER . '=action&param1=value1&param2=value2#anchor',
			Url::build('controller', 'action', ['param1' => 'value1', 'param2' => 'value2'], 'anchor')
		);
		$this->assertEquals(
			WEB_ROOT . '/index.php?' . CONTROLLER_TRIGGER . '=controller&' . ACTION_TRIGGER . '=action&' . ACTION_PARAM . '%5B0%5D=method&' . ACTION_PARAM . '%5B1%5D=param1&' . ACTION_PARAM . '%5B2%5D=param2',
			Url::build('controller', ['action', 'method', 'param1', 'param2'])
		);
		$this->assertEquals(
			WEB_ROOT . '/index.php?' . CONTROLLER_TRIGGER . '=controller&' . ACTION_TRIGGER . '=action&' . ACTION_PARAM . '%5B0%5D=method&' . ACTION_PARAM . '%5B1%5D=param1&' . ACTION_PARAM . '%5B2%5D=param2',
			Url::build('controller', 'action/method/param1/param2')
		);
		$this->assertEquals(
			WEB_ROOT . '/index.php?' . CONTROLLER_TRIGGER . '=controller&' . ACTION_TRIGGER . '=action&' . ACTION_PARAM . '%5B0%5D=method&' . ACTION_PARAM . '%5B1%5D=param1&' . ACTION_PARAM . '%5B2%5D=param2&param1=value1&param2=value2#anchor',
			Url::build('controller', ['action', 'method', 'param1', 'param2'], ['param1' => 'value1', 'param2' => 'value2'], 'anchor')
		);
		$this->assertEquals(
			WEB_ROOT . '/index.php?' . CONTROLLER_TRIGGER . '=controller&' . ACTION_TRIGGER . '=action&' . ACTION_PARAM . '%5B0%5D=method&' . ACTION_PARAM . '%5B1%5D=param1&' . ACTION_PARAM . '%5B2%5D=param2&param1=value1&param2=value2#anchor',
			Url::build('controller', 'action/method/param1/param2', ['param1' => 'value1', 'param2' => 'value2'], 'anchor')
		);
	}

	public function testBuildWithControllerActionConverter() {
		Url::addConverter(new Url\ControllerActionConverter());
		$this->assertEquals(
			WEB_ROOT . '/controller',
			Url::build('controller')
		);
		$this->assertEquals(
			WEB_ROOT . '/controller/action',
			Url::build('controller', 'action')
		);
		$this->assertEquals(
			WEB_ROOT . '/controller/action?param1=value1&param2=value2',
			Url::build('controller', 'action', ['param1' => 'value1', 'param2' => 'value2'])
		);
		$this->assertEquals(
			WEB_ROOT . '/controller/action?param1=value1&param2=value2#anchor',
			Url::build('controller', 'action', ['param1' => 'value1', 'param2' => 'value2'], 'anchor')
		);
		$this->assertEquals(
			WEB_ROOT . '/controller/action/method/param1/param2',
			Url::build('controller', ['action', 'method', 'param1', 'param2'])
		);
		$this->assertEquals(
			WEB_ROOT . '/controller/action/method/param1/param2',
			Url::build('controller', 'action/method/param1/param2')
		);
		$this->assertEquals(
			WEB_ROOT . '/controller/action/method/param1/param2?param1=value1&param2=value2#anchor',
			Url::build('controller', ['action', 'method', 'param1', 'param2'], ['param1' => 'value1', 'param2' => 'value2'], 'anchor')
		);
		$this->assertEquals(
			WEB_ROOT . '/controller/action/method/param1/param2?param1=value1&param2=value2#anchor',
			Url::build('controller', 'action/method/param1/param2', ['param1' => 'value1', 'param2' => 'value2'], 'anchor')
		);
	}

	public function testBuildPageWithoutConverter() {
		$_SERVER['PHP_SELF'] = WEB_ROOT . '/index.php';
		$this->assertEquals(
			WEB_ROOT . '/index.php?' . CONTROLLER_TRIGGER . '=page&lang=' . LANG,
			Url::build('page')
		);
		$this->assertEquals(
			WEB_ROOT . '/index.php?' . CONTROLLER_TRIGGER . '=page&' . ACTION_TRIGGER . '=action&lang=' . LANG,
			Url::build('page', 'action')
		);
		$this->assertEquals(
			WEB_ROOT . '/index.php?' . CONTROLLER_TRIGGER . '=page&' . ACTION_TRIGGER . '=action&lang=' . LANG . '&param1=value1&param2=value2',
			Url::build('page', 'action', ['param1' => 'value1', 'param2' => 'value2'])
		);
		$this->assertEquals(
			WEB_ROOT . '/index.php?' . CONTROLLER_TRIGGER . '=page&' . ACTION_TRIGGER . '=action&lang=' . LANG . '&param1=value1&param2=value2#anchor',
			Url::build('page', 'action', ['param1' => 'value1', 'param2' => 'value2'], 'anchor')
		);
		$this->assertEquals(
			WEB_ROOT . '/index.php?' . CONTROLLER_TRIGGER . '=page&' . ACTION_TRIGGER . '=action&lang=' . LANG . '&' . ACTION_PARAM . '%5B0%5D=method&' . ACTION_PARAM . '%5B1%5D=param1&' . ACTION_PARAM . '%5B2%5D=param2',
			Url::build('page', ['action', 'method', 'param1', 'param2'])
		);
		$this->assertEquals(
			WEB_ROOT . '/index.php?' . CONTROLLER_TRIGGER . '=page&' . ACTION_TRIGGER . '=action&lang=' . LANG . '&' . ACTION_PARAM . '%5B0%5D=method&' . ACTION_PARAM . '%5B1%5D=param1&' . ACTION_PARAM . '%5B2%5D=param2',
			Url::build('page', 'action/method/param1/param2')
		);
		$this->assertEquals(
			WEB_ROOT . '/index.php?' . CONTROLLER_TRIGGER . '=page&' . ACTION_TRIGGER . '=action&lang=' . LANG . '&' . ACTION_PARAM . '%5B0%5D=method&' . ACTION_PARAM . '%5B1%5D=param1&' . ACTION_PARAM . '%5B2%5D=param2&param1=value1&param2=value2#anchor',
			Url::build('page', ['action', 'method', 'param1', 'param2'], ['param1' => 'value1', 'param2' => 'value2'], 'anchor')
		);
		$this->assertEquals(
			WEB_ROOT . '/index.php?' . CONTROLLER_TRIGGER . '=page&' . ACTION_TRIGGER . '=action&lang=' . LANG . '&' . ACTION_PARAM . '%5B0%5D=method&' . ACTION_PARAM . '%5B1%5D=param1&' . ACTION_PARAM . '%5B2%5D=param2&param1=value1&param2=value2#anchor',
			Url::build('page', 'action/method/param1/param2', ['param1' => 'value1', 'param2' => 'value2'], 'anchor')
		);
	}

	public function testBuildPageWithControllerActionConverter() {
		Url::addConverter(new Url\ControllerActionConverter());
		$this->assertEquals(
			WEB_ROOT . '/' . LANG . '/page',
			Url::build('page')
		);
		$this->assertEquals(
			WEB_ROOT . '/' . LANG . '/page/action',
			Url::build('page', 'action')
		);
		$this->assertEquals(
			WEB_ROOT . '/' . LANG . '/page/action?param1=value1&param2=value2',
			Url::build('page', 'action', ['param1' => 'value1', 'param2' => 'value2'])
		);
		$this->assertEquals(
			WEB_ROOT . '/' . LANG . '/page/action?param1=value1&param2=value2#anchor',
			Url::build('page', 'action', ['param1' => 'value1', 'param2' => 'value2'], 'anchor')
		);
		$this->assertEquals(
			WEB_ROOT . '/' . LANG . '/page/action/method/param1/param2',
			Url::build('page', ['action', 'method', 'param1', 'param2'])
		);
		$this->assertEquals(
			WEB_ROOT . '/' . LANG . '/page/action/method/param1/param2',
			Url::build('page', 'action/method/param1/param2')
		);
		$this->assertEquals(
			WEB_ROOT . '/' . LANG . '/page/action/method/param1/param2?param1=value1&param2=value2#anchor',
			Url::build('page', ['action', 'method', 'param1', 'param2'], ['param1' => 'value1', 'param2' => 'value2'], 'anchor')
		);
		$this->assertEquals(
			WEB_ROOT . '/' . LANG . '/page/action/method/param1/param2?param1=value1&param2=value2#anchor',
			Url::build('page', 'action/method/param1/param2', ['param1' => 'value1', 'param2' => 'value2'], 'anchor')
		);
	}

	public function testBuildWithAliasConverter() {
		Url\AliasManager::setAliasFile(__DIR__ . '/alias.php');
		Url::addConverter(new Url\AliasConverter('fr'));
		$this->assertEquals(
			WEB_ROOT . '/first/alias/in/french',
			Url::build('first', 'realpath')
		);
		$this->assertEquals(
			WEB_ROOT . '/first/alias/in/french?param1=value1&param2=value2',
			Url::build('first', 'realpath', ['param1' => 'value1', 'param2' => 'value2'])
		);
		$this->assertEquals(
			WEB_ROOT . '/third/alias/in/french',
			Url::build('third', 'realpath', ['hello' => 'world'])
		);
	}

}