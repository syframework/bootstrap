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

	/**
	 * @var string Last file created by projectPlugin()
	 */
	private $pluginFile;

	protected function setUp(): void {
		$this->dir = sys_get_temp_dir() . '/sy-plugin-test-' . getmypid() . '-' . uniqid();
		mkdir($this->dir, 0777, true);
	}

	protected function tearDown(): void {
		$this->removeDirectory($this->dir);
	}

	public function testInstallArgumentsPutNameBeforeVersion() {
		$this->assertSame(['article', 'dev-develop'], Plugin::parseInstallArguments(['article', 'dev-develop']));
		$this->assertSame(['article', '1.6.4'], Plugin::parseInstallArguments(['article', '1.6.4']));
		$this->assertSame(['article', null], Plugin::parseInstallArguments(['article']));
		$this->assertSame([null, null], Plugin::parseInstallArguments([]));
	}

	public function testInstallArgumentsTrimSpaces() {
		$this->assertSame(['article', 'dev-develop'], Plugin::parseInstallArguments([' article ', ' dev-develop ']));
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

	public function testSyncFilesCopiesTemplatesAndAssets() {
		$vendor  = $this->projectVendor();
		$plugin  = $this->projectPlugin($vendor, 'bootstrap-article/assets/ckeditor/templates/article/img/article.gif');
		$this->projectPlugin($vendor, 'bootstrap-article/templates/Application/Page/content/article.html');

		$copied = Plugin::syncFiles($plugin, $vendor);

		$this->assertCount(2, $copied);
		$this->assertFileExists($this->dir . '/project/protected/templates/Application/Page/content/article.html');
		$this->assertFileExists($this->dir . '/project/assets/ckeditor/templates/article/img/article.gif');
	}

	public function testSyncFilesNeverOverwritesAnExistingTemplate() {
		$vendor = $this->projectVendor();
		$plugin = $this->projectPlugin($vendor, 'bootstrap-article/templates/page.html', '// from the plugin');

		$projectTemplate = $this->dir . '/project/protected/templates/page.html';
		mkdir(dirname($projectTemplate), 0777, true);
		file_put_contents($projectTemplate, '// customised by the project');
		touch($projectTemplate, time() - 3600);

		$copied = Plugin::syncFiles($plugin, $vendor);

		$this->assertSame([], $copied);
		$this->assertSame('// customised by the project', file_get_contents($projectTemplate));
	}

	public function testSyncFilesRefreshesAnAssetWhenTheVendorCopyIsNewer() {
		$vendor = $this->projectVendor();
		$plugin = $this->projectPlugin($vendor, 'bootstrap-wysiwyg/assets/js/wysiwyg.js', '// v2');

		$projectAsset = $this->dir . '/project/assets/js/wysiwyg.js';
		mkdir(dirname($projectAsset), 0777, true);
		file_put_contents($projectAsset, '// v1');
		touch($projectAsset, time() - 3600);

		$this->assertSame(['js/wysiwyg.js'], Plugin::syncFiles($plugin, $vendor));
		$this->assertSame('// v2', file_get_contents($projectAsset));
	}

	public function testSyncFilesKeepsANewerProjectAsset() {
		$vendor = $this->projectVendor();
		$plugin = $this->projectPlugin($vendor, 'bootstrap-wysiwyg/assets/js/wysiwyg.js', '// vendor');
		touch($this->pluginFile, time() - 3600);

		$projectAsset = $this->dir . '/project/assets/js/wysiwyg.js';
		mkdir(dirname($projectAsset), 0777, true);
		file_put_contents($projectAsset, '// project');

		$this->assertSame([], Plugin::syncFiles($plugin, $vendor));
		$this->assertSame('// project', file_get_contents($projectAsset));
	}

	public function testSyncFilesOnAPluginWithoutDirectoriesCopiesNothing() {
		$this->assertSame([], Plugin::syncFiles($this->dir . '/nothing', $this->projectVendor()));
	}

	public function testNeedsBuildWhenTheCssIsMissing() {
		$plugin = $this->projectPlugin($this->projectVendor(), 'bootstrap-message/scss/message.scss');

		$this->assertTrue(Plugin::needsBuild($plugin, $this->dir . '/project/assets/css/app.css'));
	}

	public function testNeedsBuildComparesTheScssAndCssTimes() {
		$vendor = $this->projectVendor();
		$plugin = $this->projectPlugin($vendor, 'bootstrap-message/scss/message.scss');
		$css    = $this->dir . '/project/assets/css/app.css';
		mkdir(dirname($css), 0777, true);
		file_put_contents($css, '/* built */');
		// The build happened after the plugin was installed.
		touch($css, time() - 1800);

		// CSS newer than the SCSS: nothing to compile.
		touch($this->pluginFile, time() - 3600);
		$this->assertFalse(Plugin::needsBuild($plugin, $css));

		// The plugin SCSS changed after the last build.
		touch($this->pluginFile);
		$this->assertTrue(Plugin::needsBuild($plugin, $css));
	}

	public function testNeedsBuildIsFalseWithoutScss() {
		$plugin = $this->projectPlugin($this->projectVendor(), 'bootstrap-location/assets/js/location.js');

		$this->assertFalse(Plugin::needsBuild($plugin, $this->dir . '/project/assets/css/app.css'));
	}

	public function testSyncAllPluginsCopiesEveryInstalledPluginOnce() {
		$vendor = $this->projectVendor();
		$this->projectPlugin($vendor, 'bootstrap-wysiwyg/assets/js/wysiwyg.js');
		$this->projectPlugin($vendor, 'bootstrap-link/assets/js/link.js');

		$first = Plugin::syncAllPlugins($vendor);

		$this->assertCount(2, $first);
		$log = implode("\n", $first);
		$this->assertStringContainsString('sy/bootstrap-wysiwyg', $log);
		$this->assertStringContainsString('sy/bootstrap-link', $log);
		$this->assertFileExists($this->dir . '/project/assets/js/link.js');

		// Second run: nothing changed, nothing copied, no build.
		$this->assertSame([], Plugin::syncAllPlugins($vendor));
	}

	/**
	 * @return string The fake project vendor directory
	 */
	private function projectVendor() {
		$vendor = $this->dir . '/project/protected/vendor';
		mkdir($vendor, 0777, true);
		return $vendor;
	}

	/**
	 * Create a fake plugin directory and one file inside it.
	 *
	 * @param string      $vendor
	 * @param string      $relative Plugin name followed by the file path
	 * @param string      $content
	 * @return string The plugin directory
	 */
	private function projectPlugin(string $vendor, string $relative, string $content = '// fixture') {
		$path = "$vendor/sy/$relative";
		if (!is_dir(dirname($path))) mkdir(dirname($path), 0777, true);
		file_put_contents($path, $content);
		$this->pluginFile = $path;
		return "$vendor/sy/" . explode('/', $relative)[0];
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
