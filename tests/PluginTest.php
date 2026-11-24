<?php
namespace Sy\Test;

use PHPUnit\Framework\TestCase;
use Sy\Bootstrap\Plugin;

/**
 * The installer decides, per plugin, whether to run the Docker build and the
 * Flyway migrations. Getting it wrong means either a needless docker pull on
 * `composer require` or a missing migration, so each directory shape is
 * pinned down here.
 */
class PluginTest extends TestCase {

	/**
	 * @var string
	 */
	private $dir;

	protected function setUp(): void {
		$this->dir = sys_get_temp_dir() . '/sy-plugin-test-' . getmypid() . '-' . uniqid();
		mkdir($this->dir, 0777, true);
	}

	protected function tearDown(): void {
		$this->removeDirectory($this->dir);
	}

	public function testPluginWithoutDirectoryNeedsNothing() {
		$this->assertSame(
			['build' => false, 'db' => false],
			Plugin::requiresInstall($this->dir)
		);
	}

	public function testScssDirectoryTriggersBuild() {
		$this->write('scss/plugin.scss');

		$this->assertSame(
			['build' => true, 'db' => false],
			Plugin::requiresInstall($this->dir)
		);
	}

	public function testSqlDirectoryTriggersDb() {
		$this->write('sql/V1__init.sql');

		$this->assertSame(
			['build' => false, 'db' => true],
			Plugin::requiresInstall($this->dir)
		);
	}

	public function testScssAndSqlTriggerBoth() {
		$this->write('scss/plugin.scss');
		$this->write('sql/V1__init.sql');

		$this->assertSame(
			['build' => true, 'db' => true],
			Plugin::requiresInstall($this->dir)
		);
	}

	public function testNestedTemplateScssTriggersBuild() {
		// glob() does not handle `**`: this case fails with a naive glob
		$this->write('templates/Application/Page/content/article.scss');

		$this->assertSame(
			['build' => true, 'db' => false],
			Plugin::requiresInstall($this->dir)
		);
	}

	public function testEmptyScssDirectoryDoesNotTriggerBuild() {
		mkdir($this->dir . '/scss');

		$this->assertSame(
			['build' => false, 'db' => false],
			Plugin::requiresInstall($this->dir)
		);
	}

	public function testTemplatesWithoutScssDoNotTriggerBuild() {
		// bootstrap-cms: templates and sql, but no scss
		$this->write('templates/Application/Page/content/page.html');
		$this->write('sql/V1__init.sql');

		$this->assertSame(
			['build' => false, 'db' => true],
			Plugin::requiresInstall($this->dir)
		);
	}

	public function testEmptySqlDirectoryStillTriggersDb() {
		// db is a directory test, not a file test: migratePlugin() does the
		// real check, requiresInstall() must not duplicate it
		mkdir($this->dir . '/sql');

		$this->assertSame(
			['build' => false, 'db' => true],
			Plugin::requiresInstall($this->dir)
		);
	}

	public function testRealPluginShapes() {
		// Measured on the checkout: the shapes that matter for the lot 0b gate
		$cases = [
			// location: no scss, no sql -> neither docker nor flyway
			'location' => [[], ['build' => false, 'db' => false]],
			// cms: sql only -> migration without build
			'cms'      => [['sql/V1__init.sql'], ['build' => false, 'db' => true]],
			// link/message: scss + sql
			'message'  => [['scss/message.scss', 'sql/V1__init.sql'], ['build' => true, 'db' => true]],
		];

		foreach ($cases as $label => [$files, $expected]) {
			$dir = $this->dir . '/' . $label;
			mkdir($dir, 0777, true);
			foreach ($files as $file) {
				$this->write($label . '/' . $file);
			}

			$this->assertSame($expected, Plugin::requiresInstall($dir), $label);
		}
	}

	/**
	 * @param string $relative
	 */
	private function write($relative) {
		$path = $this->dir . '/' . $relative;
		if (!is_dir(dirname($path))) mkdir(dirname($path), 0777, true);
		file_put_contents($path, '// fixture');
	}

	/**
	 * @param string $directory
	 */
	private function removeDirectory($directory) {
		if (!is_dir($directory)) return;

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
			\RecursiveIteratorIterator::CHILD_FIRST
		);
		foreach ($iterator as $item) {
			if ($item->isDir()) {
				rmdir($item->getPathname());
			} else {
				unlink($item->getPathname());
			}
		}

		rmdir($directory);
	}

}
