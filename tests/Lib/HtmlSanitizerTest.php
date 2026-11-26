<?php
namespace Sy\Test\Lib;

use PHPUnit\Framework\TestCase;
use Sy\Bootstrap\Lib\HtmlSanitizer;

class HtmlSanitizerTest extends TestCase {

	public function testEmptyInput() {
		$this->assertSame('', HtmlSanitizer::sanitize(''));
	}

	public function testScriptIsRemoved() {
		$this->assertSame('<p>a</p>', HtmlSanitizer::sanitize('<p>a</p><script>alert(1)</script>'));
	}

	public function testEventHandlersAreRemoved() {
		$this->assertStringNotContainsString('onerror', HtmlSanitizer::sanitize('<img src="/u/a.jpg" onerror="alert(1)">'));
		$this->assertStringNotContainsString('onclick', HtmlSanitizer::sanitize('<p onclick="alert(1)">x</p>'));
	}

	public function testDangerousSchemesAreRemoved() {
		$this->assertStringNotContainsString('javascript:', HtmlSanitizer::sanitize('<a href="javascript:alert(1)">x</a>'));
		$this->assertStringNotContainsString('vbscript:', HtmlSanitizer::sanitize('<a href="vbscript:msgbox(1)">x</a>'));
		$this->assertStringNotContainsString('data:text/html', HtmlSanitizer::sanitize('<iframe src="data:text/html;base64,PHN2Zz4="></iframe>'));
		$this->assertStringNotContainsString('srcdoc', HtmlSanitizer::sanitize('<iframe srcdoc="<script>alert(1)</script>"></iframe>'));
	}

	public function testDataImagesAreRemoved() {
		$this->assertStringNotContainsString('data:', HtmlSanitizer::sanitize('<img src="data:image/png;base64,AAA">'));
	}

	/**
	 * @dataProvider classProvider
	 */
	public function testClassIsPreserved($html, $expected) {
		$this->assertSame($expected, HtmlSanitizer::sanitize($html));
	}

	public static function classProvider() {
		return [
			'p'      => ['<p class="text-center lead">x</p>', '<p class="text-center lead">x</p>'],
			'div'    => ['<div class="row g-3">x</div>', '<div class="row g-3">x</div>'],
			'span'   => ['<p><span class="badge bg-primary">x</span></p>', '<p><span class="badge bg-primary">x</span></p>'],
			'img'    => ['<img class="img-fluid" src="/u/a.jpg" alt="a" />', '<img class="img-fluid" src="/u/a.jpg" alt="a" />'],
			'table'  => ['<table class="table"><tbody><tr><td>x</td></tr></tbody></table>', '<table class="table"><tbody><tr><td>x</td></tr></tbody></table>'],
			'iframe' => ['<iframe class="ratio" src="https://www.youtube-nocookie.com/embed/x"></iframe>', '<iframe class="ratio" src="https://www.youtube-nocookie.com/embed/x"></iframe>'],
		];
	}

	/**
	 * @dataProvider componentAttributeProvider
	 */
	public function testComponentAttributesArePreserved($attribute, $value) {
		$html = '<div ' . $attribute . '="' . $value . '">x</div>';
		$this->assertStringContainsString($attribute . '="' . $value . '"', HtmlSanitizer::sanitize($html));
	}

	public static function componentAttributeProvider() {
		return [
			'sycomponent'      => ['data-sycomponent', 'Sy\\Bootstrap\\Component\\Place\\Map'],
			'sycomponent-args' => ['data-sycomponent-args', '[]'],
			'sycomponent-slot' => ['data-sycomponent-slot', 'slot-1'],
			'sylock'           => ['data-sylock', 'mail'],
			'sylock-attr'      => ['data-sylock-attributes', 'href'],
		];
	}

	public function testStyleAttributeIsRemoved() {
		$this->assertSame('<p>x</p>', HtmlSanitizer::sanitize('<p style="text-align:center">x</p>'));
		$this->assertSame('<p>x</p>', HtmlSanitizer::sanitize('<p style="color:#ff0000">x</p>'));
	}

	public function testSvgIsRemovedWithChildren() {
		$html = '<p>a</p><svg viewBox="0 0 10 10"><path d="M0 0"></path><text>logo</text></svg>';
		$out  = HtmlSanitizer::sanitize($html);

		$this->assertStringNotContainsString('<svg', $out);
		$this->assertStringNotContainsString('logo', $out);
		$this->assertStringContainsString('<p>a</p>', $out);
	}

	public function testIframeSameOriginIsKept() {
		$this->assertStringContainsString('src="/embed/local"', HtmlSanitizer::sanitize('<iframe src="/embed/local"></iframe>'));
	}

	public function testIframeAllowedHostIsKept() {
		$out = HtmlSanitizer::sanitize('<iframe src="https://www.youtube-nocookie.com/embed/abc"></iframe>');
		$this->assertStringContainsString('www.youtube-nocookie.com/embed/abc', $out);
	}

	public function testIframeUnknownHostIsDropped() {
		$out = HtmlSanitizer::sanitize('<iframe src="https://evil.example/payload"></iframe>');
		$this->assertStringNotContainsString('evil.example', $out);
		$this->assertSame('<iframe></iframe>', $out);
	}

	public function testIframeFrameborderIsDropped() {
		$out = HtmlSanitizer::sanitize('<iframe src="/embed/a" frameborder="0"></iframe>');
		$this->assertStringNotContainsString('frameborder', $out);
	}

	public function testRelativeUrlsAreKept() {
		$this->assertStringContainsString('src="/upload/img.jpg"', HtmlSanitizer::sanitize('<img src="/upload/img.jpg" alt="a">'));
		$this->assertStringContainsString('href="/page"', HtmlSanitizer::sanitize('<a href="/page">a</a>'));
	}

	public function testEmptyParagraphsAreKept() {
		// Empty block normalization belongs to the engine (normalize-empty-blocks)
		$this->assertSame("<p> </p><p>\u{00A0}</p>", HtmlSanitizer::sanitize('<p> </p><p>&nbsp;</p>'));
	}

	public function testTooLongInputThrows() {
		$this->expectException(\LengthException::class);
		HtmlSanitizer::sanitize(str_repeat('a', HtmlSanitizer::MAX_LENGTH + 1));
	}

	public function testInvalidUtf8Throws() {
		$this->expectException(\InvalidArgumentException::class);
		HtmlSanitizer::sanitize("<p>\xC3\x28</p>");
	}

	public function testInputAtLimitIsAccepted() {
		$html = '<p>' . str_repeat('a', HtmlSanitizer::MAX_LENGTH - 7) . '</p>';
		$this->assertSame(strlen($html), HtmlSanitizer::MAX_LENGTH);
		$this->assertStringStartsWith('<p>', HtmlSanitizer::sanitize($html));
	}

	public function testIdempotence() {
		$html = '<p class="a">x</p><iframe src="https://evil.example/x"></iframe><svg><path d="M0 0"></path></svg>'
			. '<img src="data:image/png;base64,AAA"><div data-sycomponent="A\\B">y</div>';
		$once = HtmlSanitizer::sanitize($html);

		$this->assertSame($once, HtmlSanitizer::sanitize($once));
	}

	public function testFragmentDoesNotAddHtmlOrBody() {
		$out = HtmlSanitizer::sanitize('<p>hello</p>');

		$this->assertStringNotContainsString('<html', $out);
		$this->assertStringNotContainsString('<body', $out);
	}

	/**
	 * The 10 traps of PLAN-v3.5 §2.9, one assertion each.
	 */
	public function testTrapClassIsUnsafeByDefault() {
		$this->assertStringContainsString('class="a"', HtmlSanitizer::sanitize('<p class="a">x</p>'));
	}

	public function testTrapSvgIsNotInSafeList() {
		$this->assertStringNotContainsString('svg', HtmlSanitizer::sanitize('<svg><circle r="1"></circle></svg>'));
	}

	public function testTrapWildcardIsNotAllowedForAttributes() {
		$this->assertStringNotContainsString('style', HtmlSanitizer::sanitize('<p style="color:red">x</p>'));
		$this->assertStringNotContainsString('srcdoc', HtmlSanitizer::sanitize('<iframe srcdoc="x"></iframe>'));
	}

	public function testTrapMaxInputLengthDoesNotTruncate() {
		$html = '<p>' . str_repeat('b', 30000) . '</p>';
		$out  = HtmlSanitizer::sanitize($html);

		$this->assertSame(substr_count($html, 'b'), substr_count($out, 'b'));
	}

	public function testTrapInvalidUtf8IsRefused() {
		$this->expectException(\InvalidArgumentException::class);
		HtmlSanitizer::sanitize("\xFF\xFE");
	}

	public function testTrapRelativeUrlsNeedOptIn() {
		$this->assertStringContainsString('src="/a.jpg"', HtmlSanitizer::sanitize('<img src="/a.jpg" alt="a">'));
	}

	public function testTrapMediaHostsAreGlobalForIframes() {
		// Not using allowMediaHosts(): images from any host survive
		$this->assertStringContainsString('src="https://images.example/a.jpg"', HtmlSanitizer::sanitize('<img src="https://images.example/a.jpg" alt="a">'));
	}

	public function testTrapDataSchemeIsExcluded() {
		$this->assertStringNotContainsString('data:', HtmlSanitizer::sanitize('<img src="data:image/gif;base64,R0lGOD">'));
	}

	public function testTrapFragmentContext() {
		$this->assertSame('<p>x</p>', HtmlSanitizer::sanitize('<p>x</p>'));
	}

	public function testTrapDataAttributesMustBeEnumerated() {
		$this->assertStringNotContainsString('data-unknown', HtmlSanitizer::sanitize('<p data-unknown="1">x</p>'));
	}

	/**
	 * Real samples: the loss must stay inside the documented consequences.
	 *
	 * @dataProvider fixtureProvider
	 */
	public function testFixtureLosesOnlyDocumentedElements($file) {
		$html = file_get_contents(__DIR__ . '/../fixtures/' . $file);
		$out  = HtmlSanitizer::sanitize($html);

		// Documented losses
		$this->assertStringNotContainsString('<script', $out);
		$this->assertStringNotContainsString('<svg', $out);
		$this->assertStringNotContainsString('style=', $out);
		$this->assertStringNotContainsString('data:', $out);
		$this->assertStringNotContainsString('srcdoc', $out);
		$this->assertStringNotContainsString('frameborder', $out);
		$this->assertStringNotContainsString('evil.example', $out);
		$this->assertStringNotContainsString('javascript:', $out);

		// Preserved semantics
		$this->assertStringContainsString('class="', $out);
		$this->assertStringContainsString('/upload/', $out);
		$this->assertStringContainsString('<p', $out);
	}

	public static function fixtureProvider() {
		return [
			'article' => ['article.html'],
			'cms'     => ['cms.html'],
			'place'   => ['place.html'],
		];
	}

	public function testCmsFixtureKeepsComponents() {
		$out = HtmlSanitizer::sanitize(file_get_contents(__DIR__ . '/../fixtures/cms.html'));

		$this->assertStringContainsString('data-sycomponent="Sy\\Bootstrap\\Component\\Place\\Map"', $out);
		$this->assertStringContainsString('data-sylock="mail"', $out);
		$this->assertStringContainsString('data-sylock-attributes="href"', $out);
		$this->assertStringContainsString('player.vimeo.com', $out);
	}

	public function testPlaceFixtureKeepsComponents() {
		$out = HtmlSanitizer::sanitize(file_get_contents(__DIR__ . '/../fixtures/place.html'));

		$this->assertStringContainsString('data-sycomponent="MAP"', $out);
		$this->assertStringContainsString('data-sycomponent="TWFEED"', $out);
		$this->assertStringContainsString('www.google.com/maps/embed', $out);
	}

	public function testArticleFixtureKeepsAllowedIframe() {
		$out = HtmlSanitizer::sanitize(file_get_contents(__DIR__ . '/../fixtures/article.html'));

		$this->assertStringContainsString('www.youtube-nocookie.com/embed/abc123', $out);
		// The hostile iframe is kept but inert: no src
		$this->assertSame(2, substr_count($out, '<iframe'));
		$this->assertSame(1, substr_count($out, '<iframe src='));
	}

}
