<?php
namespace Sy\Test\Lib;

use PHPUnit\Framework\TestCase;
use Sy\Bootstrap\Lib\EditorAssets;

class EditorAssetsTest extends TestCase {

	public function testEngineDefaultsToWebRoot() {
		$this->assertSame('/webroot/assets/js/wysiwyg.js', EditorAssets::engine());
	}

	public function testJsFallsBackToCdn() {
		$this->assertStringContainsString('cdn.ckeditor.com', EditorAssets::js());
		$this->assertStringEndsWith('/ckeditor.js', EditorAssets::js());
	}

	public function testRootDefaultsToWebRoot() {
		$this->assertSame('/webroot/assets/ckeditor', EditorAssets::root());
	}

	public function testPluginsDefaultsToWebRoot() {
		$this->assertSame('/webroot/assets/js/plugins', EditorAssets::plugins());
	}

	public function testAllowedExtensions() {
		$this->assertContains('jpg', EditorAssets::allowedExtensions('image'));
		$this->assertContains('webp', EditorAssets::allowedExtensions('image'));
		$this->assertNotContains('svg', EditorAssets::allowedExtensions('image'));
		$this->assertContains('pdf', EditorAssets::allowedExtensions('file'));
		$this->assertNotContains('php', EditorAssets::allowedExtensions('file'));
		$this->assertSame([], EditorAssets::allowedExtensions('unknown'));
	}

	/**
	 * Constants are global and tests/conf.php defines WEB_ROOT: both cases are
	 * exercised in a child process that only loads the autoloader.
	 *
	 * @dataProvider missingWebRootProvider
	 */
	public function testMissingWebRootThrows($method) {
		$this->assertSame('WEB_ROOT is not defined', $this->runIsolated(
			'try { \\Sy\\Bootstrap\\Lib\\EditorAssets::' . $method . '(); echo "NO-EXCEPTION"; }'
			. 'catch (\\RuntimeException $e) { echo $e->getMessage(); }'
		));
	}

	public static function missingWebRootProvider() {
		return [
			'engine'  => ['engine'],
			'root'    => ['root'],
			'plugins' => ['plugins'],
		];
	}

	public function testConstantsWin() {
		$output = $this->runIsolated(
			'define("WYSIWYG_ENGINE", "https://cdn.example/wysiwyg.js");'
			. 'define("WYSIWYG_PLUGINS", "https://cdn.example/plugins");'
			. 'define("CKEDITOR_JS", "https://cdn.example/ckeditor.js");'
			. 'define("CKEDITOR_ROOT", "https://cdn.example/ckeditor");'
			. 'echo \\Sy\\Bootstrap\\Lib\\EditorAssets::engine(), "|",'
			. '\\Sy\\Bootstrap\\Lib\\EditorAssets::plugins(), "|",'
			. '\\Sy\\Bootstrap\\Lib\\EditorAssets::js(), "|",'
			. '\\Sy\\Bootstrap\\Lib\\EditorAssets::root();'
		);

		$this->assertSame(
			'https://cdn.example/wysiwyg.js|https://cdn.example/plugins|https://cdn.example/ckeditor.js|https://cdn.example/ckeditor',
			$output
		);
	}

	/**
	 * Run PHP code in a fresh process, with the autoloader only.
	 *
	 * @param string $code
	 * @return string
	 */
	private function runIsolated($code) {
		$autoload = var_export(__DIR__ . '/../../vendor/autoload.php', true);
		$command  = escapeshellarg(PHP_BINARY) . ' -r ' . escapeshellarg('require ' . $autoload . ';' . $code) . ' 2>&1';

		exec($command, $output);

		return trim(implode("\n", $output));
	}

}
