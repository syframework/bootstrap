<?php
namespace Sy\Test\Lib;

use PHPUnit\Framework\TestCase;
use Sy\Bootstrap\Lib\Str;

/**
 * @backupStaticAttributes enabled
 */
class StrTest extends TestCase {

	public function testConvertYoutube() {
		// Long URL
		$text = "Some text before... https://www.youtube.com/watch?v=abc-def_123 some text after...";
		$this->assertEquals(
			"Some text before... <span class=\"ratio ratio-16x9\"><iframe src=\"https://www.youtube-nocookie.com/embed/abc-def_123?start=\" allowfullscreen></iframe></span> some text after...",
			Str::convertYoutube($text)
		);

		// Long URL with time code
		$text = "Some text before... https://www.youtube.com/watch?v=abc-def_123&t=42 some text after...";
		$this->assertEquals(
			"Some text before... <span class=\"ratio ratio-16x9\"><iframe src=\"https://www.youtube-nocookie.com/embed/abc-def_123?start=42\" allowfullscreen></iframe></span> some text after...",
			Str::convertYoutube($text)
		);

		// Short URL
		$text = "Some text before... https://youtu.be/abc-def_123 some text after...";
		$this->assertEquals(
			"Some text before... <span class=\"ratio ratio-16x9\"><iframe src=\"https://www.youtube-nocookie.com/embed/abc-def_123?start=\" allowfullscreen></iframe></span> some text after...",
			Str::convertYoutube($text)
		);

		// Short URL with time code
		$text = "Some text before... https://youtu.be/abc-def_123?t=42 some text after...";
		$this->assertEquals(
			"Some text before... <span class=\"ratio ratio-16x9\"><iframe src=\"https://www.youtube-nocookie.com/embed/abc-def_123?start=42\" allowfullscreen></iframe></span> some text after...",
			Str::convertYoutube($text)
		);

		// Mobile URL
		$text = "Some text before... https://m.youtube.com/watch?v=abc-def_123 some text after...";
		$this->assertEquals(
			"Some text before... <span class=\"ratio ratio-16x9\"><iframe src=\"https://www.youtube-nocookie.com/embed/abc-def_123?start=\" allowfullscreen></iframe></span> some text after...",
			Str::convertYoutube($text)
		);

		// With other parameters in URL
		$text = "Some text before... https://www.youtube.com/watch?v=abc-def_123&param=other some text after...";
		$this->assertEquals(
			"Some text before... <span class=\"ratio ratio-16x9\"><iframe src=\"https://www.youtube-nocookie.com/embed/abc-def_123?start=\" allowfullscreen></iframe></span> some text after...",
			Str::convertYoutube($text)
		);
	}

	public function testConvertDailymotion() {
		// Long URL
		$text = "Some text before... https://www.dailymotion.com/video/abc-def_123 some text after...";
		$this->assertEquals(
			"Some text before... <span class=\"ratio ratio-16x9\"><iframe src=\"https://www.dailymotion.com/embed/video/abc-def_123\" allowfullscreen></iframe></span> some text after...",
			Str::convertDailymotion($text)
		);

		// Short URL
		$text = "Some text before... https://dai.ly/abc-def_123 some text after...";
		$this->assertEquals(
			"Some text before... <span class=\"ratio ratio-16x9\"><iframe src=\"https://www.dailymotion.com/embed/video/abc-def_123\" allowfullscreen></iframe></span> some text after...",
			Str::convertDailymotion($text)
		);

		// With other parameters in URL
		$text = "Some text before... https://www.dailymotion.com/video/abc-def_123&param=other some text after...";
		$this->assertEquals(
			"Some text before... <span class=\"ratio ratio-16x9\"><iframe src=\"https://www.dailymotion.com/embed/video/abc-def_123\" allowfullscreen></iframe></span> some text after...",
			Str::convertDailymotion($text)
		);
	}

	public function testCamlToSnake() {
		$this->assertEquals('simple_test', Str::camlToSnake('simpleTest'));
		$this->assertEquals('easy', Str::camlToSnake('easy'));
		$this->assertEquals('html', Str::camlToSnake('HTML'));
		$this->assertEquals('simple_xml', Str::camlToSnake('simpleXML'));
		$this->assertEquals('start_middle_end', Str::camlToSnake('startMIDDLEEnd'));
		$this->assertEquals('a_string', Str::camlToSnake('AString'));
		$this->assertEquals('some4_numbers234', Str::camlToSnake('Some4Numbers234'));
		$this->assertEquals('test123_string', Str::camlToSnake('TEST123String'));
		$this->assertEquals('hello_world', Str::camlToSnake('hello_world'));
		$this->assertEquals('hello___world', Str::camlToSnake('hello___world'));
		$this->assertEquals('_hello_world_', Str::camlToSnake('_hello_world_'));
		$this->assertEquals('hello_world', Str::camlToSnake('HelloWorld'));
		$this->assertEquals('hello_world_foo', Str::camlToSnake('HelloWorldFoo'));
		$this->assertEquals('hello_world', Str::camlToSnake('hello_World'));
		$this->assertEquals('hello-world', Str::camlToSnake('hello-world'));
		$this->assertEquals('my_html_fi_le', Str::camlToSnake('myHTMLFiLe'));
		$this->assertEquals('a_ba_ba_b', Str::camlToSnake('aBaBaB'));
		$this->assertEquals('ba_ba_ba', Str::camlToSnake('BaBaBa'));
		$this->assertEquals('lib_c', Str::camlToSnake('libC'));
	}

}