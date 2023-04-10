<?php
namespace Sy\Test\Lib\Url;

use PHPUnit\Framework\TestCase;
use Sy\Bootstrap\Lib\Url\ControllerActionConverter;

class ControllerActionConverterTest extends TestCase {

	public function testParamsToUrl() {
		$converter = new ControllerActionConverter();

		$params = [
			ACTION_TRIGGER => 'foo',
		];
		$this->assertEquals(false, $converter->paramsToUrl($params));

		$params = [
			CONTROLLER_TRIGGER => 'foo',
		];
		$this->assertEquals(WEB_ROOT . '/foo', $converter->paramsToUrl($params));

		$params = [
			CONTROLLER_TRIGGER => 'foo',
			ACTION_TRIGGER => 'bar',
		];
		$this->assertEquals(WEB_ROOT . '/foo/bar', $converter->paramsToUrl($params));

		$params = [
			CONTROLLER_TRIGGER => 'foo',
			ACTION_TRIGGER => 'bar',
			'other' => 'baz',
		];
		$this->assertEquals(WEB_ROOT . '/foo/bar?other=baz', $converter->paramsToUrl($params));

		$params = [
			CONTROLLER_TRIGGER => 'foo',
			ACTION_TRIGGER => 'bar',
			'p1' => 'one',
			'p2' => 'two',
		];
		$this->assertEquals(WEB_ROOT . '/foo/bar?p1=one&p2=two', $converter->paramsToUrl($params));

		$params = [
			CONTROLLER_TRIGGER => 'foo',
			ACTION_TRIGGER => 'bar',
			ACTION_PARAM => ['hello', 'world'],
			'p1' => 'one',
			'p2' => 'two',
		];
		$this->assertEquals(WEB_ROOT . '/foo/bar/hello/world?p1=one&p2=two', $converter->paramsToUrl($params));

		$params = [
			CONTROLLER_TRIGGER => 'foo',
			ACTION_TRIGGER => 'bar/hello/world',
			'p1' => 'one',
			'p2' => 'two',
		];
		$this->assertEquals(WEB_ROOT . '/foo/bar/hello/world?p1=one&p2=two', $converter->paramsToUrl($params));
	}

	public function testUrlToParams() {
		$converter = new ControllerActionConverter();

		$this->assertEquals(false, $converter->urlToParams(null));
		$this->assertEquals(false, $converter->urlToParams(''));
		$this->assertEquals(false, $converter->urlToParams(WEB_ROOT . '/'));

		$params = [
			CONTROLLER_TRIGGER => 'foo',
		];
		$this->assertEquals($params, $converter->urlToParams(WEB_ROOT . '/foo'));

		$params = [
			CONTROLLER_TRIGGER => 'foo',
			ACTION_TRIGGER => 'bar',
		];
		$this->assertEquals($params, $converter->urlToParams(WEB_ROOT . '/foo/bar'));

		$params = [
			CONTROLLER_TRIGGER => 'foo',
			ACTION_TRIGGER => 'bar',
			'other' => 'baz',
		];
		$this->assertEquals($params, $converter->urlToParams(WEB_ROOT . '/foo/bar?other=baz'));

		$params = [
			CONTROLLER_TRIGGER => 'foo',
			ACTION_TRIGGER => 'bar',
			'p1' => 'one',
			'p2' => 'two',
		];
		$this->assertEquals($params, $converter->urlToParams(WEB_ROOT . '/foo/bar?p1=one&p2=two'));

		$params = [
			CONTROLLER_TRIGGER => 'foo',
			ACTION_TRIGGER => 'bar',
			ACTION_PARAM => ['hello', 'world'],
			'p1' => 'one',
			'p2' => 'two',
		];
		$this->assertEquals($params, $converter->urlToParams(WEB_ROOT . '/foo/bar/hello/world?p1=one&p2=two'));
	}

}