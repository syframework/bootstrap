<?php
namespace Sy\Test\Lib;

use PHPUnit\Framework\TestCase;
use Sy\Bootstrap\Lib\Str;

/**
 * @backupStaticAttributes enabled
 */
class StrTest extends TestCase {

	public function testConvertLineBreaks() {
		$this->assertEquals('foo<br />bar<br />baz<br />', Str::convertLineBreaks("foo\nbar\rbaz\r\n"));
	}

	public function testEscapeHtmlTags() {
		$this->assertEquals('Hello <strong&gt;world</strong&gt;', Str::escapeHtmlTags('Hello <strong>world</strong>'));
	}

	public function testEscapeTemplateSlots() {
		$this->assertEquals('hello {SLOT&rcurb;', Str::escapeTemplateSlots('hello {SLOT}'));
	}

	public function testConvertName() {
		$this->assertEquals('Someone', Str::convertName(''));
		$this->assertEquals('John Doe', Str::convertName('John Doe'));
		$this->assertEquals('John <b&gt;Doe</b&gt;', Str::convertName('John <b>Doe</b>'));
	}

	public function testTruncateUrl() {
		$this->assertEquals(
			'https://example.com/hello-worl[ ... ]r/baz/very/long',
			Str::truncateUrl('https://example.com/hello-world/foo/bar/baz/very/long')
		);
		$this->assertEquals(
			'https://example.com/hello-world',
			Str::truncateUrl('https://example.com/hello-world')
		);
	}

	public function testConvertLink() {
		$this->assertEquals(
			'hello world <a href="https://example.com/hello-world" target="_blank" rel="nofollow">https://example.com/hello-world</a> foo bar baz',
			Str::convertLink('hello world https://example.com/hello-world foo bar baz')
		);
		$this->assertEquals(
			'hello world <a href="https://example.com/hello-world" target="_blank">https://example.com/hello-world</a> foo bar baz',
			Str::convertLink('hello world https://example.com/hello-world foo bar baz', true)
		);
	}

	public function testConvertSimpleImg() {
		$this->assertEquals(
			'hello world <figure class="figure"><img class="figure-img img-fluid rounded" src="https://example.com/image.png" alt="Alt text" /><figcaption class="figure-caption text-center">Alt text</figcaption></figure>',
			Str::convertSimpleImg('hello world https://example.com/image.png [Alt text]')
		);
	}

	public function testConvertImg() {
		$this->assertEquals(
			'hello world <figure class="figure"><a href="https://example.com/image.png" target="_blank"><img class="figure-img img-fluid rounded" src="https://example.com/image.png" alt="Alt text" /></a><figcaption class="figure-caption text-center">Alt text</figcaption></figure>',
			Str::convertImg('hello world https://example.com/image.png [Alt text]')
		);
	}

	public function testExtractImgUrl() {
		$this->assertEquals(
			['https://example.com/image.png', 'https://example.com/image.jpg'],
			Str::extractImgUrl('hello world https://example.com/image.png foo bar baz https://example.com/image.jpg')
		);
	}

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

	public function testConvert() {
		$this->assertEquals(
			'image: <figure class="figure"><a href="<a href="https://example.com/image.png"" target="_blank">https://example.com/image.png"</a> target="_blank"><img class="figure-img img-fluid rounded" src="<a href="https://example.com/image.png"" target="_blank">https://example.com/image.png"</a> alt="" /></a><figcaption class="figure-caption text-center"></figcaption></figure> link: <a href="https://foo.com/bar" target="_blank">https://foo.com/bar</a>',
			Str::convert('image: https://example.com/image.png link: https://foo.com/bar')
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

	public function testSnakeToCaml() {
		$this->assertEquals('simpleTest', Str::snakeToCaml('simple_test'));
		$this->assertEquals('easy', Str::snakeToCaml('easy'));
		$this->assertEquals('simpleXml', Str::snakeToCaml('simple_xml'));
		$this->assertEquals('startMiddleEnd', Str::snakeToCaml('start_middle_end'));
		$this->assertEquals('aString', Str::snakeToCaml('a_string'));
		$this->assertEquals('some4Numbers234', Str::snakeToCaml('some4_numbers234'));
		$this->assertEquals('test123String', Str::snakeToCaml('test123_string'));
		$this->assertEquals('helloWorld', Str::snakeToCaml('hello_world'));
		$this->assertEquals('helloWorld', Str::snakeToCaml('hello___world'));
		$this->assertEquals('helloWorld', Str::snakeToCaml('_hello_world_'));
		$this->assertEquals('helloWorldFoo', Str::snakeToCaml('hello_world_foo'));
		$this->assertEquals('helloWorld', Str::snakeToCaml('hello-world'));
		$this->assertEquals('myHtmlFiLe', Str::snakeToCaml('my_html_fi_le'));
		$this->assertEquals('aBaBaB', Str::snakeToCaml('a_ba_ba_b'));
		$this->assertEquals('baBaBa', Str::snakeToCaml('ba_ba_ba'));
		$this->assertEquals('libC', Str::snakeToCaml('lib_c'));
	}

	public function testSlugify() {
		$this->assertEquals('hello-world', Str::slugify('Hello World'));
		$this->assertEquals('hello-world', Str::slugify('  Hello   World  '));
		$this->assertEquals('lapostrophe', Str::slugify("l'apostrophe"));
	}

	public function testRemoveAccent() {
		$this->assertEquals('episode', Str::removeAccent('épisode'));
		$this->assertEquals('a la gare', Str::removeAccent('à la gare'));
	}

}