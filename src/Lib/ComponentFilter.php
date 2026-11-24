<?php
namespace Sy\Bootstrap\Lib;

use Masterminds\HTML5;

/**
 * Removes or neutralizes server-side components that are not allowlisted.
 *
 * A component is an element carrying the data-sycomponent marker attribute
 * and optional arguments.
 *
 * This filter only decides whether a component class is allowed to be rendered.
 * It is NOT an HTML sanitizer: it does not guarantee the absence of XSS.
 * HtmlSanitizer must run before it on any untrusted input.
 */
class ComponentFilter {

	/**
	 * Component marker attribute.
	 */
	private const MARKER = 'data-sycomponent';

	/**
	 * Neutralize every component whose class is not in $allowlist.
	 *
	 * An empty allowlist denies every component: this is what a consumer without
	 * server-side components (Article) expects. A consumer that wants to disable
	 * the filter simply does not call it.
	 *
	 * @param string $html      HTML fragment
	 * @param array  $allowlist Allowed component classes or slot names
	 * @return string
	 */
	public static function sanitize($html, array $allowlist) {
		if (!is_string($html) or $html === '') return '';

		$html5 = new HTML5();
		$fragment = $html5->loadHTMLFragment($html);

		$document = $fragment->ownerDocument;
		$changed  = false;

		foreach (self::collectElements($fragment) as $element) {
			$class = self::resolveClass($element);
			if ($class === null) continue;
			if (in_array($class, $allowlist, true)) continue;

			self::neutralize($element, $document, $class);
			$changed = true;
		}

		return $changed ? $html5->saveHTML($fragment) : $html;
	}

	/**
	 * Depth-first list of elements, children included.
	 *
	 * DOMDocumentFragment has no getElementsByTagName(), and a live NodeList
	 * cannot be modified while iterating, so the list is built first.
	 *
	 * @param \DOMNode $node
	 * @return \DOMElement[]
	 */
	private static function collectElements($node) {
		$elements = [];
		$stack    = [];
		foreach ($node->childNodes as $child) $stack[] = $child;

		while (!empty($stack)) {
			$current = array_pop($stack);
			if (!($current instanceof \DOMElement)) continue;
			$elements[] = $current;
			foreach ($current->childNodes as $child) $stack[] = $child;
		}

		return $elements;
	}

	/**
	 * @param \DOMElement $element
	 * @return string|null
	 */
	private static function resolveClass($element) {
		$class = trim($element->getAttribute(self::MARKER));
		return $class !== '' ? $class : null;
	}

	/**
	 * Replace the component by its real inner text, or drop it when the text is
	 * empty or only the {SLOT} placeholder.
	 *
	 * @param \DOMElement  $element
	 * @param \DOMDocument $document
	 * @param string       $class
	 */
	private static function neutralize($element, $document, $class) {
		$text = trim($element->textContent);

		if ($text === '' or preg_replace('/\s+/u', '', $text) === '{SLOT}') {
			$element->parentNode->removeChild($element);
			self::log($class, 'removed');
			return;
		}

		$element->parentNode->replaceChild($document->createTextNode($text), $element);
		self::log($class, 'text-only');
	}

	/**
	 * @param string $class
	 * @param string $action
	 */
	private static function log($class, $action) {
		error_log(sprintf('[sy/bootstrap] ComponentFilter: %s component "%s"', $action, $class));
	}

}
