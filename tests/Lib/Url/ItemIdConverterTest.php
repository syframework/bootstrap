<?php
namespace Sy\Test\Lib\Url;

use PHPUnit\Framework\TestCase;
use Sy\Bootstrap\Lib\Url\ItemIdConverter;

class ItemIdConverterTest extends TestCase {

	public function testParamsToUrl() {
		$converter = new ItemIdConverter('foo', 'boo');

		$params = [
			ACTION_TRIGGER => 'foo',
		];
		$this->assertEquals(false, $converter->paramsToUrl($params));

		$params = [
			CONTROLLER_TRIGGER => 'page',
		];
		$this->assertEquals(false, $converter->paramsToUrl($params));

		$params = [
			CONTROLLER_TRIGGER => 'page',
			ACTION_TRIGGER => 'foo',
		];
		$this->assertEquals(false, $converter->paramsToUrl($params));

		$params = [
			CONTROLLER_TRIGGER => 'page',
			ACTION_TRIGGER => 'foo',
			'id' => '123',
		];
		$this->assertEquals(WEB_ROOT . '/boo/123', $converter->paramsToUrl($params));

		$params = [
			CONTROLLER_TRIGGER => 'page',
			ACTION_TRIGGER => 'foo',
			'id' => '123',
			'p1' => 'hello',
		];
		$this->assertEquals(WEB_ROOT . '/boo/123?p1=hello', $converter->paramsToUrl($params));

		$params = [
			CONTROLLER_TRIGGER => 'page',
			ACTION_TRIGGER => 'foo',
			'id' => '123',
			'p1' => 'hello',
			'p2' => 'world',
		];
		$this->assertEquals(WEB_ROOT . '/boo/123?p1=hello&p2=world', $converter->paramsToUrl($params));

		$converter = new ItemIdConverter('foo');

		$params = [
			ACTION_TRIGGER => 'foo',
		];
		$this->assertEquals(false, $converter->paramsToUrl($params));

		$params = [
			CONTROLLER_TRIGGER => 'page',
		];
		$this->assertEquals(false, $converter->paramsToUrl($params));

		$params = [
			CONTROLLER_TRIGGER => 'page',
			ACTION_TRIGGER => 'foo',
		];
		$this->assertEquals(false, $converter->paramsToUrl($params));

		$params = [
			CONTROLLER_TRIGGER => 'page',
			ACTION_TRIGGER => 'foo',
			'id' => '123',
		];
		$this->assertEquals(WEB_ROOT . '/foo/123', $converter->paramsToUrl($params));

		$params = [
			CONTROLLER_TRIGGER => 'page',
			ACTION_TRIGGER => 'foo',
			'id' => '123',
			'p1' => 'hello',
		];
		$this->assertEquals(WEB_ROOT . '/foo/123?p1=hello', $converter->paramsToUrl($params));

		$params = [
			CONTROLLER_TRIGGER => 'page',
			ACTION_TRIGGER => 'foo',
			'id' => '123',
			'p1' => 'hello',
			'p2' => 'world',
		];
		$this->assertEquals(WEB_ROOT . '/foo/123?p1=hello&p2=world', $converter->paramsToUrl($params));
	}

	public function testUrlToParams() {
		$converter = new ItemIdConverter('foo', 'boo');

		$this->assertEquals(false, $converter->urlToParams(null));
		$this->assertEquals(false, $converter->urlToParams(''));
		$this->assertEquals(false, $converter->urlToParams(WEB_ROOT . '/'));
		$this->assertEquals(false, $converter->urlToParams(WEB_ROOT . '/page'));
		$this->assertEquals(false, $converter->urlToParams(WEB_ROOT . '/page/foo'));

		$params = [
			CONTROLLER_TRIGGER => 'page',
			ACTION_TRIGGER => 'foo',
			'id' => '123',
		];
		$this->assertEquals($params, $converter->urlToParams(WEB_ROOT . '/boo/123'));

		$params = [
			CONTROLLER_TRIGGER => 'page',
			ACTION_TRIGGER => 'foo',
			'id' => '123',
			'p1' => 'hello',
		];
		$this->assertEquals($params, $converter->urlToParams(WEB_ROOT . '/boo/123?p1=hello'));

		$params = [
			CONTROLLER_TRIGGER => 'page',
			ACTION_TRIGGER => 'foo',
			'id' => '123',
			'p1' => 'hello',
			'p2' => 'world',
		];
		$this->assertEquals($params, $converter->urlToParams(WEB_ROOT . '/boo/123?p1=hello&p2=world'));

		$converter = new ItemIdConverter('foo');

		$this->assertEquals(false, $converter->urlToParams(WEB_ROOT . '/'));
		$this->assertEquals(false, $converter->urlToParams(WEB_ROOT . '/page'));
		$this->assertEquals(false, $converter->urlToParams(WEB_ROOT . '/page/foo'));

		$params = [
			CONTROLLER_TRIGGER => 'page',
			ACTION_TRIGGER => 'foo',
			'id' => '123',
		];
		$this->assertEquals($params, $converter->urlToParams(WEB_ROOT . '/foo/123'));

		$params = [
			CONTROLLER_TRIGGER => 'page',
			ACTION_TRIGGER => 'foo',
			'id' => '123',
			'p1' => 'hello',
		];
		$this->assertEquals($params, $converter->urlToParams(WEB_ROOT . '/foo/123?p1=hello'));

		$params = [
			CONTROLLER_TRIGGER => 'page',
			ACTION_TRIGGER => 'foo',
			'id' => '123',
			'p1' => 'hello',
			'p2' => 'world',
		];
		$this->assertEquals($params, $converter->urlToParams(WEB_ROOT . '/foo/123?p1=hello&p2=world'));
	}

}