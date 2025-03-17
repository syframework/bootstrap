<?php
namespace Sy\Test\Component;

use PHPUnit\Framework\TestCase;
use Sy\Bootstrap\Component\Api;
use Sy\Bootstrap\Component\Api\NotFoundException;

class MyTestApi extends Api {

	public function security() {

	}

	public function getAction() {
		$this->ok('get action ok');
	}

	public function postAction() {
		$this->ok('post action ok');
	}

	public function deleteAction() {
		$this->ok('delete action ok');
	}

}

class ApiTest extends TestCase {

	public function testVerbGet() {
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$api = new MyTestApi();
		$this->assertEquals('get action ok', strval($api));
	}

	public function testVerbPost() {
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$api = new MyTestApi();
		$this->assertEquals('post action ok', strval($api));
	}

	public function testVerbDelete() {
		$_SERVER['REQUEST_METHOD'] = 'DELETE';
		$api = new MyTestApi();
		$this->assertEquals('delete action ok', strval($api));
	}

	public function testNotFound() {
		$_REQUEST[ACTION_TRIGGER] = 'my-test-api';
		$_REQUEST[ACTION_PARAM] = ['foo', 'bar', 'baz'];
		$this->expectException(NotFoundException::class);
		$api = new MyTestApi();
		strval($api);
	}

}