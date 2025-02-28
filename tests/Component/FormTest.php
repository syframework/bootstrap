<?php
namespace Sy\Test\Component;

use PHPUnit\Framework\TestCase;
use Sy\Bootstrap\Component\Form;

class FormTest extends TestCase {

	/**
	 * @var Form
	 */
	private $form;

	public function testJsonSuccess() {
		$reflection = new \ReflectionObject($this->form);
		$jsonSuccess = $reflection->getMethod('jsonSuccess');
		$jsonSuccess->setAccessible(true);

		// No parameter
		$this->assertJsonStringEqualsJsonString(json_encode([
			'ok' => true,
		]), $jsonSuccess->invoke($this->form));

		// String data
		$this->assertJsonStringEqualsJsonString(json_encode([
			'ok' => true,
			'message' => 'Hello world',
		]), $jsonSuccess->invoke($this->form, 'Hello world'));

		// String data + options
		$this->assertJsonStringEqualsJsonString(json_encode([
			'ok' => true,
			'message' => 'Hello world',
			'one' => 'foo',
			'two' => 'bar',
		]), $jsonSuccess->invoke($this->form, 'Hello world', ['one' => 'foo', 'two' => 'bar']));

		// String data + options + custom
		$this->assertJsonStringEqualsJsonString(json_encode([
			'ok' => true,
			'message' => 'Hello world',
			'opt1' => 'optval1',
			'opt2' => 'optval2',
			'custom' => [
				'one' => 'foo',
				'two' => 'bar',
			],
		]), $jsonSuccess->invoke($this->form, 'Hello world', ['opt1' => 'optval1', 'opt2' => 'optval2'], ['one' => 'foo', 'two' => 'bar']));

		// Array data
		$this->assertJsonStringEqualsJsonString(json_encode([
			'ok' => true,
			'message' => ['title' => 'Success', 'body' => 'Hello world'],
			'opt1' => 'optval1',
			'opt2' => 'optval2',
			'custom' => [
				'one' => 'foo',
				'two' => 'bar',
			],
		]), $jsonSuccess->invoke($this->form, [
			'ok' => false,
			'message' => ['title' => 'Success', 'body' => 'Hello world'],
			'opt1' => 'optval1',
			'opt2' => 'optval2',
			'custom' => [
				'one' => 'foo',
				'two' => 'bar',
			],
		]));

		// Mixed
		$this->assertJsonStringEqualsJsonString(json_encode([
			'ok' => true,
			'message' => 'Hello world',
			'opt1' => 'foo',
			'opt2' => 'bar',
			'custom' => [
				'hello' => 'world',
			],
		]), $jsonSuccess->invoke($this->form, [
			'message' => 'Hello world',
		], ['opt1' => 'foo', 'opt2' => 'bar'], ['hello' => 'world']));
		$this->assertJsonStringEqualsJsonString(json_encode([
			'ok' => true,
			'message' => ['title' => 'Success', 'body' => 'Hello world'],
			'opt1' => 'foo',
			'opt2' => 'bar',
			'custom' => [
				'hello' => 'world',
			],
		]), $jsonSuccess->invoke($this->form, [
			'message' => ['title' => 'Success', 'body' => 'Hello world'],
		], ['opt1' => 'foo', 'opt2' => 'bar'], ['hello' => 'world']));
		$this->assertJsonStringEqualsJsonString(json_encode([
			'ok' => true,
			'message' => ['title' => 'Success', 'body' => 'Hello world'],
			'opt1' => 'optval1',
			'opt2' => 'optval2',
			'custom' => [
				'one' => 'foo',
				'two' => 'bar',
			],
		]), $jsonSuccess->invoke($this->form, [
			'ok' => false,
			'message' => ['title' => 'Success', 'body' => 'Hello world'],
			'opt1' => 'optval1',
			'opt2' => 'optval2',
			'custom' => [
				'one' => 'foo',
				'two' => 'bar',
			],
		], ['opt1' => 'foo', 'opt2' => 'bar'], ['hello' => 'world']));
	}

	public function testJsonError() {
		$reflection = new \ReflectionObject($this->form);
		$jsonSuccess = $reflection->getMethod('jsonError');
		$jsonSuccess->setAccessible(true);

		// No parameter
		$this->assertJsonStringEqualsJsonString(json_encode([
			'ok' => false,
		]), $jsonSuccess->invoke($this->form));

		// String data
		$this->assertJsonStringEqualsJsonString(json_encode([
			'ok' => false,
			'message' => 'Hello world',
		]), $jsonSuccess->invoke($this->form, 'Hello world'));

		// String data + options
		$this->assertJsonStringEqualsJsonString(json_encode([
			'ok' => false,
			'message' => 'Hello world',
			'one' => 'foo',
			'two' => 'bar',
		]), $jsonSuccess->invoke($this->form, 'Hello world', ['one' => 'foo', 'two' => 'bar']));

		// String data + options + custom
		$this->assertJsonStringEqualsJsonString(json_encode([
			'ok' => false,
			'message' => 'Hello world',
			'opt1' => 'optval1',
			'opt2' => 'optval2',
			'custom' => [
				'one' => 'foo',
				'two' => 'bar',
			],
		]), $jsonSuccess->invoke($this->form, 'Hello world', ['opt1' => 'optval1', 'opt2' => 'optval2'], ['one' => 'foo', 'two' => 'bar']));

		// Array data
		$this->assertJsonStringEqualsJsonString(json_encode([
			'ok' => false,
			'message' => ['title' => 'My title', 'body' => 'Hello world'],
			'opt1' => 'optval1',
			'opt2' => 'optval2',
			'custom' => [
				'one' => 'foo',
				'two' => 'bar',
			],
		]), $jsonSuccess->invoke($this->form, [
			'ok' => true,
			'message' => ['title' => 'My title', 'body' => 'Hello world'],
			'opt1' => 'optval1',
			'opt2' => 'optval2',
			'custom' => [
				'one' => 'foo',
				'two' => 'bar',
			],
		]));
	}

	public function testAddRange() {
		$range = $this->form->addRange([
			'name' => 'range',
			'min' => 0,
			'max' => 10,
			'step' => 1,
		]);
		$this->assertEquals('range', $range->getAttribute('name'));
		$this->assertEquals(0, $range->getAttribute('min'));
		$this->assertEquals(10, $range->getAttribute('max'));
		$this->assertEquals(1, $range->getAttribute('step'));
		$this->assertEquals('form-range', $range->getAttribute('class'));

		$rate = $this->form->addRange([
			'name' => 'rate',
			'class' => 'rating',
		]);
		$this->assertEquals('form-range rating', $rate->getAttribute('class'));
	}

	protected function setUp(): void {
		$this->form = $this->getMockBuilder(Form::class)
			->disableOriginalConstructor()
			->getMockForAbstractClass();
	}

}