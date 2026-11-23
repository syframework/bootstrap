<?php
namespace Sy\Bootstrap\Lib;

use Masterminds\HTML5;

/**
 * Slot markers shared by the CMS and Place consumers.
 *
 * A slot marker is an editing-only attribute carrying a stable index
 * (slot-1, slot-2, ...) used to restore the rendered inner HTML of a component
 * after the editor has reloaded the source. Markers must never reach the
 * database: strip() removes them from the saved source.
 */
class SlotMarker {

	/**
	 * Component marker attributes.
	 */
	private const MARKERS = ['data-sycomponent', 'data-component'];

	/**
	 * Slot attribute names, removed by strip().
	 */
	private const SLOTS = ['data-sycomponent-slot', 'data-component-slot'];

	/**
	 * Add an incremental slot attribute to every component of the fragment.
	 *
	 * @param string $html
	 * @param string $attribute data-sycomponent-slot or data-component-slot
	 * @return string
	 */
	public static function mark($html, $attribute) {
		if (!is_string($html) or $html === '') return '';
		if (!in_array($attribute, self::SLOTS, true)) return $html;

		$html5    = new HTML5();
		$fragment = $html5->loadHTMLFragment($html);

		$index = 0;
		foreach (self::elements($fragment) as $element) {
			if (self::componentClass($element) === null) continue;
			$element->setAttribute($attribute, 'slot-' . (++$index));
		}

		// saveHTML() re-serializes the fragment even without change; only pay
		// for it when at least one component was marked
		return $index === 0 ? $html : $html5->saveHTML($fragment);
	}

	/**
	 * Remove every slot attribute (both naming conventions).
	 *
	 * @param string $html
	 * @return string
	 */
	public static function strip($html) {
		if (!is_string($html) or $html === '') return '';

		$html5    = new HTML5();
		$fragment = $html5->loadHTMLFragment($html);

		$changed = false;
		foreach (self::elements($fragment) as $element) {
			foreach (self::SLOTS as $slot) {
				if (!$element->hasAttribute($slot)) continue;
				$element->removeAttribute($slot);
				$changed = true;
			}
		}

		return $changed ? $html5->saveHTML($fragment) : $html;
	}

	/**
	 * Elements in document order.
	 *
	 * @param \DOMNode $node
	 * @return \DOMElement[]
	 */
	private static function elements($node) {
		$elements = [];
		foreach ($node->childNodes as $child) {
			if (!($child instanceof \DOMElement)) continue;
			$elements[] = $child;
			foreach (self::elements($child) as $descendant) $elements[] = $descendant;
		}
		return $elements;
	}

	/**
	 * @param \DOMElement $element
	 * @return string|null
	 */
	private static function componentClass($element) {
		foreach (self::MARKERS as $marker) {
			$class = trim($element->getAttribute($marker));
			if ($class !== '') return $class;
		}
		return null;
	}

}
