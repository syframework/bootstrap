<?php
namespace Sy\Test\Lib\Url;

use PHPUnit\Framework\TestCase;
use Sy\Bootstrap\Lib\Url\AliasConverter;
use Sy\Bootstrap\Lib\Url\AliasManager;

class AliasConverterTest extends TestCase {

	public function testParamsToUrl() {
		$converter = new AliasConverter('fr');

		$params = [
			ACTION_TRIGGER => 'realpath',
		];
		$this->assertEquals(false, $converter->paramsToUrl($params));

		$params = [
			CONTROLLER_TRIGGER => 'first',
		];
		$this->assertEquals(false, $converter->paramsToUrl($params));

		$params = [
			CONTROLLER_TRIGGER => 'first',
			ACTION_TRIGGER => 'realpath',
		];
		$this->assertEquals(WEB_ROOT . '/first/alias/in/french', $converter->paramsToUrl($params));

		$params = [
			CONTROLLER_TRIGGER => 'first',
			ACTION_TRIGGER => 'realpath',
			'lang' => 'en',
		];
		$this->assertEquals(WEB_ROOT . '/first/alias/in/english', $converter->paramsToUrl($params));

		$params = [
			CONTROLLER_TRIGGER => 'first',
			ACTION_TRIGGER => 'realpath',
			'foo' => 'bar',
		];
		$this->assertEquals(WEB_ROOT . '/first/alias/in/french?foo=bar', $converter->paramsToUrl($params));

		$params = [
			CONTROLLER_TRIGGER => 'first',
			ACTION_TRIGGER => 'realpath',
			'foo' => 'bar',
			'lang' => 'en',
		];
		$this->assertEquals(WEB_ROOT . '/first/alias/in/english?foo=bar', $converter->paramsToUrl($params));

		$params = [
			CONTROLLER_TRIGGER => 'third',
			ACTION_TRIGGER => 'realpath',
			'hello' => 'world',
		];
		$this->assertEquals(WEB_ROOT . '/third/alias/in/french', $converter->paramsToUrl($params));

		$params = [
			CONTROLLER_TRIGGER => 'third',
			ACTION_TRIGGER => 'realpath',
			'hello' => 'world',
			'lang' => 'en',
		];
		$this->assertEquals(WEB_ROOT . '/third/alias/in/english', $converter->paramsToUrl($params));

		$params = [
			CONTROLLER_TRIGGER => 'third',
			ACTION_TRIGGER => 'realpath',
			'hello' => 'world',
			'foo' => 'bar',
		];
		$this->assertEquals(false, $converter->paramsToUrl($params));
	}

	public function testUrlToParams() {
		$converter = new AliasConverter('fr');

		$this->assertEquals(false, $converter->urlToParams(null));
		$this->assertEquals(false, $converter->urlToParams(''));
		$this->assertEquals(false, $converter->urlToParams(WEB_ROOT . '/'));
		$this->assertEquals(false, $converter->urlToParams(WEB_ROOT . '/first/alias/in/foo'));

		$params = [
			CONTROLLER_TRIGGER => 'first',
			ACTION_TRIGGER => 'realpath',
			'lang' => 'fr',
		];
		$this->assertEquals($params, $converter->urlToParams(WEB_ROOT . '/first/alias/in/french'));

		$params = [
			CONTROLLER_TRIGGER => 'first',
			ACTION_TRIGGER => 'realpath',
			'lang' => 'fr',
		];
		$this->assertEquals($params, $converter->urlToParams(WEB_ROOT . '/first/alias/in/french/'));

		$params = [
			CONTROLLER_TRIGGER => 'first',
			ACTION_TRIGGER => 'realpath',
			'foo' => 'bar',
			'lang' => 'fr',
		];
		$this->assertEquals($params, $converter->urlToParams(WEB_ROOT . '/first/alias/in/french?foo=bar'));

		$params = [
			CONTROLLER_TRIGGER => 'first',
			ACTION_TRIGGER => 'realpath',
			'foo' => 'bar',
			'lang' => 'fr',
		];
		$this->assertEquals($params, $converter->urlToParams(WEB_ROOT . '/first/alias/in/french/?foo=bar'));

		$params = [
			CONTROLLER_TRIGGER => 'third',
			ACTION_TRIGGER => 'realpath',
			'hello' => 'world',
			'lang' => 'fr',
		];
		$this->assertEquals($params, $converter->urlToParams(WEB_ROOT . '/third/alias/in/french'));
	}

	protected function setUp(): void {
		AliasManager::setAliasFile(__DIR__ . '/alias.php');
	}

}