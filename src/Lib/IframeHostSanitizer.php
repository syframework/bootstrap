<?php
namespace Sy\Bootstrap\Lib;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\HtmlSanitizer\Visitor\AttributeSanitizer\AttributeSanitizerInterface;

/**
 * Restricts the iframe[src] host to an explicit allowlist.
 *
 * symfony/html-sanitizer treats iframe[src] as a media URL, and media hosts are
 * a single global setting: using allowMediaHosts() to restrict iframes would
 * also restrict images, videos and audio. This sanitizer therefore applies the
 * host policy to iframes only.
 *
 * It must run AFTER the built-in URL sanitizer, which is why
 * getSupportedElements() returns null ("all elements") instead of ['iframe']:
 * element-specific sanitizers run first in the chain, and returning null while
 * the URL sanitizer has not run yet raises a TypeError in 6.4.
 *
 * The sanitizer never parses HTML: it only inspects an already extracted URL.
 */
final class IframeHostSanitizer implements AttributeSanitizerInterface {

	/**
	 * @var string[]
	 */
	private $hosts;

	/**
	 * @param string[] $hosts
	 */
	public function __construct(array $hosts) {
		$this->hosts = array_map(function ($host) {
			return strtolower($host);
		}, $hosts);
	}

	public function getSupportedElements(): ?array {
		return null;
	}

	public function getSupportedAttributes(): ?array {
		return ['src'];
	}

	/**
	 * @param string               $element
	 * @param string               $attribute
	 * @param string|null          $value  null when a previous sanitizer dropped it
	 * @param HtmlSanitizerConfig  $config
	 * @return string|null  null removes the attribute
	 */
	public function sanitizeAttribute(string $element, string $attribute, ?string $value, HtmlSanitizerConfig $config): ?string {
		if ($value === null or $element !== 'iframe') return $value;

		$url = parse_url(trim($value));
		if ($url === false) return null;

		$scheme = strtolower(isset($url['scheme']) ? $url['scheme'] : '');
		$host   = strtolower(isset($url['host']) ? $url['host'] : '');

		// Relative path: same origin, nothing to check
		if ($scheme === '' and $host === '') return $value;
		// Protocol-relative URL: the host still has to be allowlisted
		if ($scheme !== '' and !in_array($scheme, ['http', 'https'], true)) return null;

		return in_array($host, $this->hosts, true) ? $value : null;
	}

}
