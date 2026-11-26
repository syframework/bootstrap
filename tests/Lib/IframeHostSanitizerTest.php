<?php
namespace Sy\Test\Lib;

use PHPUnit\Framework\TestCase;
use Sy\Bootstrap\Lib\HtmlSanitizer;
use Sy\Bootstrap\Lib\IframeHostSanitizer;

class IframeHostSanitizerTest extends TestCase {

	public function testDefaultHosts() {
		$hosts = HtmlSanitizer::iframeHosts();

		$this->assertContains('www.youtube-nocookie.com', $hosts);
		$this->assertContains('player.vimeo.com', $hosts);
		$this->assertNotContains('evil.example', $hosts);
	}

	/**
	 * @dataProvider supportedProvider
	 */
	public function testSupportsAllElementsButOnlySrc() {
		$sanitizer = new IframeHostSanitizer(['ok.example']);

		// null, not ['iframe']: the URL sanitizer must run first (see class doc)
		$this->assertNull($sanitizer->getSupportedElements());
		$this->assertSame(['src'], $sanitizer->getSupportedAttributes());
	}

	public static function supportedProvider() {
		return [['iframe']];
	}

	public function testNonIframeSrcIsUntouched() {
		$out = $this->sanitize('<img src="https://images.example/a.jpg" alt="a">');

		$this->assertStringContainsString('https://images.example/a.jpg', $out);
	}

	/**
	 * @dataProvider resolveProvider
	 */
	public function testHostResolution($src, $kept) {
		$out = $this->sanitize('<iframe src="' . $src . '"></iframe>');

		if ($kept) {
			$this->assertStringContainsString($src, $out);
		} else {
			$this->assertStringNotContainsString($src, $out);
			$this->assertStringNotContainsString('src=', $out);
		}
	}

	public static function resolveProvider() {
		return [
			'allowed host'        => ['https://www.youtube-nocookie.com/embed/x', true],
			'other allowed host'  => ['https://player.vimeo.com/video/1', true],
			// The port is not part of the host policy (same as allowMediaHosts).
			'allowed host with port' => ['https://www.youtube.com:8080/embed/x', true],
			'unknown host'        => ['https://evil.example/x', false],
			'http allowed host'   => ['http://www.youtube.com/embed/x', true],
			'relative same origin' => ['/embed/local', true],
			'protocol relative'   => ['//www.youtube.com/embed/x', true],
			'javascript scheme'   => ['javascript:alert(1)', false],
			'data scheme'         => ['data:text/html;base64,PHNjcmlwdD4=', false],
			'no host no scheme'   => ['embed/x', true],
		];
	}

	public function testHostMatchIsCaseInsensitive() {
		$sanitizer = new IframeHostSanitizer(['WWW.YouTube-Nocookie.com']);
		$out = $this->sanitize('<iframe src="https://www.youtube-nocookie.com/embed/x"></iframe>', $sanitizer);

		$this->assertStringContainsString('youtube-nocookie.com', $out);
	}

	/**
	 * @param string $html
	 * @param IframeHostSanitizer|null $sanitizer
	 * @return string
	 */
	private function sanitize($html, $sanitizer = null) {
		$config = $sanitizer === null
			? HtmlSanitizer::config()
			: HtmlSanitizer::config()->withAttributeSanitizer($sanitizer);

		return (new \Symfony\Component\HtmlSanitizer\HtmlSanitizer($config))->sanitize($html);
	}

}
