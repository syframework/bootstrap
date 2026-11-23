<?php
namespace Sy\Bootstrap\Lib;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer as SymfonyHtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Single HTML policy of the framework, built on symfony/html-sanitizer.
 *
 * There is no option and no per-consumer variant: three consumers share one
 * policy, and a need for a broader policy is a review, not a runtime flag.
 *
 * The policy is: no script, no style (element or attribute), no SVG, no data:
 * URL, iframes restricted to an explicit host list. See PLAN-v3.5 §2.9.
 */
final class HtmlSanitizer {

	/**
	 * Maximum accepted input size, in bytes.
	 */
	public const MAX_LENGTH = 1048576; // 1 MiB

	/**
	 * @var SymfonyHtmlSanitizer|null
	 */
	private static $sanitizer = null;

	/**
	 * Sanitize an HTML fragment.
	 *
	 * @param string $html
	 * @return string
	 * @throws \LengthException when the input exceeds MAX_LENGTH
	 * @throws \InvalidArgumentException when the input is not valid UTF-8
	 */
	public static function sanitize($html) {
		if (!is_string($html) or $html === '') return '';

		// Never let the library truncate silently at 20 000 bytes
		if (strlen($html) > self::MAX_LENGTH) {
			throw new \LengthException('Content exceeds ' . self::MAX_LENGTH . ' bytes');
		}

		// The library returns an empty string on invalid UTF-8: refuse instead,
		// so the caller can answer 400 instead of storing nothing
		if (preg_match('//u', $html) !== 1) {
			throw new \InvalidArgumentException('Content is not valid UTF-8');
		}

		return self::sanitizer()->sanitize($html);
	}

	/**
	 * @return HtmlSanitizerConfig
	 */
	public static function config() {
		return (new HtmlSanitizerConfig())
			// 1. W3C safe baseline: no script, style, iframe, svg, form
			->allowSafeElements()
			// 2. iframe: explicitly unblocked, attributes listed one by one
			->allowElement('iframe', [
				'src', 'width', 'height', 'allow', 'allowfullscreen',
				'loading', 'referrerpolicy', 'title', 'sandbox',
			])
			// 3. Order matters: allowAttribute(..., '*') only applies to the
			//    elements allowed at that point, and is subtractive
			->allowAttribute('class', '*')
			->allowAttribute('data-sycomponent', '*')
			->allowAttribute('data-sycomponent-args', '*')
			->allowAttribute('data-sycomponent-slot', '*')
			->allowAttribute('data-sylock', '*')
			->allowAttribute('data-sylock-attributes', '*')
			->allowAttribute('data-component', '*')
			->allowAttribute('data-component-args', '*')
			->allowAttribute('data-component-slot', '*')
			// 4. URLs: no data:, relative URLs kept
			->allowLinkSchemes(['http', 'https', 'mailto', 'tel'])
			->allowMediaSchemes(['http', 'https'])
			->allowRelativeLinks(true)
			->allowRelativeMedias(true)
			// 5. Never truncate silently (default is 20 000 bytes)
			->withMaxInputLength(-1)
			// 6. Media hosts are global, so iframes get their own host check
			->withAttributeSanitizer(new IframeHostSanitizer(self::iframeHosts()));
	}

	/**
	 * Hosts allowed as iframe sources.
	 *
	 * @return string[]
	 */
	public static function iframeHosts() {
		return [
			'www.youtube-nocookie.com',
			'www.youtube.com',
			'player.vimeo.com',
			'www.google.com',
			'maps.google.com',
		];
	}

	/**
	 * @return SymfonyHtmlSanitizer
	 */
	private static function sanitizer() {
		if (self::$sanitizer === null) {
			self::$sanitizer = new SymfonyHtmlSanitizer(self::config());
		}
		return self::$sanitizer;
	}

}
