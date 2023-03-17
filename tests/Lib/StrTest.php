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

}