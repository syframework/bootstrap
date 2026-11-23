<?php
namespace Sy\Test\Lib;

use PHPUnit\Framework\TestCase;
use Sy\Bootstrap\Lib\SlotMarker;

class SlotMarkerTest extends TestCase {

	public function testMarkAddsIncrementalIndexes() {
		$out = SlotMarker::mark(
			'<div data-sycomponent="A">a</div><div data-sycomponent="B">b</div>',
			'data-sycomponent-slot'
		);

		$this->assertStringContainsString('data-sycomponent-slot="slot-1"', $out);
		$this->assertStringContainsString('data-sycomponent-slot="slot-2"', $out);
	}

	public function testIdenticalComponentsGetDistinctIndexes() {
		$out = SlotMarker::mark(
			'<div data-sycomponent="A">x</div><div data-sycomponent="A">x</div>',
			'data-sycomponent-slot'
		);

		$this->assertStringContainsString('slot-1', $out);
		$this->assertStringContainsString('slot-2', $out);
	}

	public function testMarkHandlesLegacyAttribute() {
		$out = SlotMarker::mark('<div data-component="MAP">c</div>', 'data-component-slot');

		$this->assertStringContainsString('data-component-slot="slot-1"', $out);
	}

	public function testMarkIgnoresHtmlWithoutComponent() {
		$html = '<p>a</p><div class="x">b</div>';

		$this->assertSame($html, SlotMarker::mark($html, 'data-sycomponent-slot'));
	}

	public function testMarkRejectsUnknownSlotAttribute() {
		$html = '<div data-sycomponent="A">a</div>';

		$this->assertSame($html, SlotMarker::mark($html, 'data-whatever'));
	}

	public function testStripRemovesBothConventions() {
		$html = '<div data-sycomponent="A" data-sycomponent-slot="slot-1">a</div>'
			. '<div data-component="B" data-component-slot="slot-2">b</div>';
		$out = SlotMarker::strip($html);

		$this->assertStringNotContainsString('data-sycomponent-slot', $out);
		$this->assertStringNotContainsString('data-component-slot', $out);
		$this->assertStringContainsString('data-sycomponent="A"', $out);
		$this->assertStringContainsString('data-component="B"', $out);
	}

	public function testStripIsIdempotent() {
		$html = '<div data-sycomponent="A" data-sycomponent-slot="slot-1">a</div>';
		$once = SlotMarker::strip($html);

		$this->assertSame($once, SlotMarker::strip($once));
	}

	public function testStripWithoutSlotIsVerbatim() {
		$html = '<p>a</p>';

		$this->assertSame($html, SlotMarker::strip($html));
	}

	public function testMarkThenStripRoundTrip() {
		$html = '<p>x</p><div data-sycomponent="A">a</div>';
		$marked = SlotMarker::mark($html, 'data-sycomponent-slot');

		$this->assertNotSame($html, $marked);
		$this->assertSame($html, SlotMarker::strip($marked));
	}

	public function testEmptyInput() {
		$this->assertSame('', SlotMarker::mark('', 'data-sycomponent-slot'));
		$this->assertSame('', SlotMarker::strip(''));
	}

}
