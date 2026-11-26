<?php
namespace Sy\Test\Application;

use PHPUnit\Framework\TestCase;

/**
 * `bootstrap` must not ship editor-specific markup: the CKEditor sharedspace
 * targets belong to the consumer that configures `sharedSpaces` (CMS today).
 */
class PageBodyTemplateTest extends TestCase {

	private function template() {
		return file_get_contents(__DIR__ . '/../../src/Application/Page/Body.html');
	}

	public function testNoEditorSharedSpaceTargets() {
		$template = $this->template();

		$this->assertStringNotContainsString('sy-page-topbar', $template);
		$this->assertStringNotContainsString('sy-page-bottombar', $template);
	}

	public function testLayoutIsRendered() {
		$this->assertStringContainsString('{LAYOUT}', $this->template());
	}

	public function testNoEditorSharedSpaceStyles() {
		$scss = file_get_contents(__DIR__ . '/../../scss/toolbar.scss');

		$this->assertStringNotContainsString('sy-page-topbar', $scss);
		$this->assertStringNotContainsString('sy-page-bottombar', $scss);
		// The Page toolbar itself stays.
		$this->assertStringContainsString('#sy-page-toolbar', $scss);
	}

}
