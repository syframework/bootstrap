<?php
namespace Sy\Bootstrap\Application\Editor {

	/**
	 * Minimal plugin class used by EditorOriginTest.
	 */
	class FakePlugin {

	}

}

namespace Sy\Test\Application\Editor {

	use PHPUnit\Framework\TestCase;
	use Sy\Bootstrap\Component\Api\ForbiddenException;
	use Sy\Bootstrap\Component\Api\NotFoundException;

	class CountingEditor extends \Sy\Bootstrap\Application\Editor {

		public $securityCalls = 0;

		public function security() {
			$this->securityCalls++;
			parent::security();
		}

	}

	class EditorOriginTest extends TestCase {

		private $serverBackup;

		private $requestBackup;

		protected function setUp(): void {
			$this->serverBackup  = $_SERVER;
			$this->requestBackup = $_REQUEST;
		}

		protected function tearDown(): void {
			$_SERVER  = $this->serverBackup;
			$_REQUEST = $this->requestBackup;
		}

		public function testPluginBranchRequiresSameOrigin() {
			$editor = $this->editor('FakePlugin', null);

			try {
				$editor->dispatch();
				$this->fail('A missing origin must be forbidden');
			} catch (ForbiddenException $e) {
				$this->assertSame(1, $editor->securityCalls);
			}
		}

		public function testPluginBranchRejectsForeignOrigin() {
			$editor = $this->editor('FakePlugin', 'https://evil.example');

			$this->expectException(ForbiddenException::class);
			$editor->dispatch();
		}

		public function testPluginBranchAcceptsSameOrigin() {
			$editor = $this->editor('FakePlugin', 'https://example.org');
			$editor->dispatch();

			$this->assertSame(1, $editor->securityCalls);
		}

		public function testGenericBranchCallsSecurityOnlyOnce() {
			$editor = $this->editor('DoesNotExist', 'https://example.org');

			try {
				$editor->dispatch();
				$this->fail('An unknown action must not be dispatched');
			} catch (NotFoundException $e) {
				$this->assertSame(1, $editor->securityCalls);
			}
		}

		public function testGenericBranchRejectsMissingOrigin() {
			$editor = $this->editor('DoesNotExist', null);

			$this->expectException(ForbiddenException::class);
			$editor->dispatch();
		}

		/**
		 * @param string      $action
		 * @param string|null $origin
		 * @return CountingEditor
		 */
		private function editor($action, $origin) {
			$_SERVER['SERVER_NAME']    = 'example.org';
			$_SERVER['REQUEST_METHOD'] = 'GET';
			unset($_SERVER['HTTP_ORIGIN'], $_SERVER['HTTP_REFERER']);
			if ($origin !== null) $_SERVER['HTTP_ORIGIN'] = $origin;
			$_REQUEST[ACTION_TRIGGER] = $action;

			return new CountingEditor();
		}

	}

}
