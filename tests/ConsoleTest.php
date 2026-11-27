<?php
namespace Sy\Test;

use PHPUnit\Framework\TestCase;

// Load the installer without running its main entry point.
define('SY_CONSOLE_TEST', true);
require_once __DIR__ . '/../bin/sy';

/**
 * The installer decides what to copy, when to rebuild and which Flyway
 * commands to run. Getting it wrong means either a needless Docker run on a
 * deploy, a missing plugin asset in production, or a migration applied to the
 * wrong schema — so each rule is pinned down here, with the command strings
 * asserted through the sy_run seam instead of being executed.
 */
class ConsoleTest extends TestCase {

	private $dir;

	protected function setUp(): void {
		$this->dir = sys_get_temp_dir() . '/sy-console-' . getmypid() . '-' . uniqid();
		mkdir($this->dir, 0777, true);
		unset($GLOBALS['sy_run'], $GLOBALS['sy_quiet'], $GLOBALS['sy_dry_run']);
		$GLOBALS['sy_quiet'] = true;
	}

	protected function tearDown(): void {
		unset($GLOBALS['sy_run'], $GLOBALS['sy_quiet'], $GLOBALS['sy_dry_run'], $GLOBALS['_composer_bin_dir']);
		$this->removeDirectory($this->dir);
	}

	public function testPluginsAreDiscoveredByNamingConvention() {
		mkdir($this->dir . '/vendor/sy/bootstrap-article', 0777, true);
		mkdir($this->dir . '/vendor/sy/bootstrap-wysiwyg', 0777, true);
		mkdir($this->dir . '/vendor/sy/other-package', 0777, true);

		$this->assertSame(['article', 'wysiwyg'], array_keys(\sy_plugins($this->dir . '/vendor')));
	}

	public function testVendorDirectoryFollowsTheComposerConfig() {
		$this->writeJson('composer.json', ['config' => ['vendor-dir' => 'protected/vendor']]);
		$this->assertSame($this->dir . '/protected/vendor', \sy_vendor($this->dir));

		$this->writeJson('composer.json', []);
		$this->assertSame($this->dir . '/vendor', \sy_vendor($this->dir));
	}

	public function testTemplatesAreCopiedOnlyWhenMissing() {
		$this->write('vendor/sy/bootstrap-article/templates/Page/a.html', 'plugin');
		$this->write('templates/Page/a.html', 'customised');
		$this->write('vendor/sy/bootstrap-article/templates/Page/b.html', 'plugin');

		$copied = \sy_copy(
			$this->dir . '/vendor/sy/bootstrap-article/templates',
			$this->dir . '/templates',
			false
		);

		$this->assertSame(['Page/b.html'], $copied);
		$this->assertSame('customised', file_get_contents($this->dir . '/templates/Page/a.html'));
	}

	public function testAssetsAreRefreshedOnlyWhenTheVendorCopyIsNewer() {
		$this->write('vendor/sy/bootstrap-wysiwyg/assets/js/engine.js', 'new');
		$this->write('assets/js/engine.js', 'old');
		touch($this->dir . '/assets/js/engine.js', time() - 3600);
		$this->write('vendor/sy/bootstrap-wysiwyg/assets/js/stale.js', 'vendor');
		$this->write('assets/js/stale.js', 'newer');
		touch($this->dir . '/assets/js/stale.js', time() + 3600);

		$copied = \sy_copy(
			$this->dir . '/vendor/sy/bootstrap-wysiwyg/assets',
			$this->dir . '/assets',
			true
		);

		$this->assertSame(['js/engine.js'], $copied);
		$this->assertSame('new', file_get_contents($this->dir . '/assets/js/engine.js'));
		$this->assertSame('newer', file_get_contents($this->dir . '/assets/js/stale.js'));
	}

	public function testSyncReportsOnlyThePluginsThatCopiedSomething() {
		$this->write('vendor/sy/bootstrap-wysiwyg/assets/js/wysiwyg.js', 'engine');
		mkdir($this->dir . '/vendor/sy/bootstrap-message', 0777, true);

		$lines = \sy_sync($this->dir, $this->dir . '/vendor');

		$this->assertSame(['sync: sy/bootstrap-wysiwyg (1 files)'], $lines);
		$this->assertFileExists($this->dir . '/assets/js/wysiwyg.js');
		$this->assertSame([], \sy_sync($this->dir, $this->dir . '/vendor'));
	}

	public function testNeedsBuildFollowsTheScssAndCssTimestamps() {
		$this->write('vendor/sy/bootstrap-article/scss/article.scss', 'body{}');
		$css = $this->dir . '/assets/css/app.css';

		// No compiled CSS yet: build.
		$this->assertTrue(\sy_needs_build($this->dir . '/vendor', $css));

		// CSS newer than the plugin SCSS: nothing to do.
		$this->write('assets/css/app.css', 'compiled');
		touch($css, time() + 3600);
		$this->assertFalse(\sy_needs_build($this->dir . '/vendor', $css));

		// A plugin SCSS edit after the build: build again.
		touch($this->dir . '/vendor/sy/bootstrap-article/scss/article.scss', time() + 7200);
		$this->assertTrue(\sy_needs_build($this->dir . '/vendor', $css));
	}

	public function testNeedsBuildIgnoresPluginsWithoutScss() {
		mkdir($this->dir . '/vendor/sy/bootstrap-message', 0777, true);
		$this->assertFalse(\sy_needs_build($this->dir . '/vendor', $this->dir . '/assets/css/app.css'));
	}

	public function testDbCommandsCoverCoreAndEveryPluginSchema() {
		$this->writeJson('composer.json', ['config' => ['vendor-dir' => 'vendor']]);
		mkdir($this->dir . '/protected/sql', 0777, true);
		mkdir($this->dir . '/vendor/sy/bootstrap-article/sql', 0777, true);
		mkdir($this->dir . '/vendor/sy/bootstrap-wysiwyg', 0777, true);

		$commands = \sy_db_commands($this->dir, $this->dir . '/vendor', [], 'info');

		$this->assertSame(['core', 'plugin article'], array_column($commands, 'label'));
		$this->assertStringContainsString("--sql '" . $this->dir . "/protected/sql' --task 'info'", $commands[0]['command']);
		$this->assertStringContainsString('-table=flyway_article_history', $commands[1]['command']);
		$this->assertStringContainsString("--task 'info'", $commands[1]['command']);
	}

	public function testDbRefusesToRunOnAnUnconfiguredProject() {
		$this->writeJson('composer.json', []);
		$GLOBALS['sy_run'] = function ($command, $tty) {
			$this->fail('no command must run: ' . $command);
		};

		$this->assertSame(2, \sy_db_run($this->dir, $this->dir . '/vendor', [], ['migrate']));
	}

	public function testConfiguredFollowsTheDeclaredDatabaseFile() {
		$this->assertFalse(\sy_configured($this->dir, []));

		$this->write('protected/conf/database.ini', "host=localhost\n");
		$this->assertTrue(\sy_configured($this->dir, []));

		$this->assertTrue(\sy_configured($this->dir, ['ini' => ['database' => ['file' => 'protected/conf/database.ini']]]));
		$this->assertFalse(\sy_configured($this->dir, ['ini' => ['database' => ['file' => 'conf/db.ini']]]));
	}

	public function testMissingIniFilesAreTheOnlyOnesProvisioned() {
		$this->write('protected/conf/database.ini', "host=localhost\n");
		$config = ['ini' => [
			'database' => ['file' => 'protected/conf/database.ini', 'input' => ['host' => ['question' => 'Host']]],
			'partners' => ['file' => 'protected/conf/partners.ini', 'input' => ['token' => ['question' => 'Token']]],
		]];

		$this->assertSame(['partners'], array_keys(\sy_ini_missing($this->dir, $config)));
	}

	public function testProvisionRunsGeniniWithATtyThenTheInstallSteps() {
		$calls = [];
		$GLOBALS['sy_run'] = function ($command, $tty) use (&$calls) {
			$calls[] = [$command, $tty];
			return 0;
		};
		$config = [
			'ini'     => ['database' => ['file' => 'protected/conf/database.ini', 'input' => ['host' => ['question' => 'Host']]]],
			'install' => ['php protected/bin/seed.php'],
		];

		$this->assertSame(0, \sy_provision($this->dir, $this->dir . '/vendor', $config));
		$this->assertCount(2, $calls);
		$this->assertStringContainsString('genini', $calls[0][0]);
		$this->assertTrue($calls[0][1], 'genini must keep the terminal');
		$this->assertStringContainsString('seed.php', $calls[1][0]);
		$this->assertFalse($calls[1][1]);
	}

	public function testInstallIsIdempotent() {
		$this->writeJson('composer.json', [
			'config' => ['vendor-dir' => 'vendor'],
			'extra'  => ['sy' => ['build' => 'gulp build']],
		]);
		$this->write('protected/conf/database.ini', "host=localhost\n");
		$this->write('vendor/sy/bootstrap-wysiwyg/assets/js/wysiwyg.js', 'engine');
		$this->write('assets/css/app.css', 'compiled');
		touch($this->dir . '/assets/css/app.css', time() + 7200);

		$commands = [];
		$GLOBALS['sy_run'] = function ($command, $tty) use (&$commands) {
			$commands[] = $command;
			return 0;
		};

		$this->assertSame(0, \sy_install($this->dir));
		$first = $commands;
		$commands = [];

		$this->assertSame(0, \sy_install($this->dir));

		// No second build, only the schema pass again.
		$this->assertSame([], array_values(array_filter($commands, fn ($c) => str_contains($c, 'gulp'))));
		$this->assertCount(2, $first);
		$this->assertCount(2, $commands);
	}

	public function testDoctorFailsWhenAPluginAssetIsMissing() {
		$this->write('vendor/sy/bootstrap-wysiwyg/assets/js/wysiwyg.js', 'engine');
		$this->write('assets/css/app.css', 'compiled');

		$report = \sy_doctor($this->dir, $this->dir . '/vendor', []);
		$this->assertFalse($report['ok']);
		$this->assertStringContainsString('missing: sy/bootstrap-wysiwyg -> assets/js/wysiwyg.js', implode("\n", $report['lines']));

		$this->write('assets/js/wysiwyg.js', 'engine');
		$report = \sy_doctor($this->dir, $this->dir . '/vendor', []);
		$this->assertTrue($report['ok']);
	}

	public function testDoctorFailsWhenTheCompiledCssIsMissing() {
		$this->write('vendor/sy/bootstrap-wysiwyg/assets/js/wysiwyg.js', 'engine');
		$this->write('assets/js/wysiwyg.js', 'engine');

		$report = \sy_doctor($this->dir, $this->dir . '/vendor', []);
		$this->assertFalse($report['ok']);
		$this->assertStringContainsString('missing: assets/css/app.css', implode("\n", $report['lines']));
	}

	public function testUnknownOptionIsRejected() {
		$this->assertSame(2, \sy_main(['sy', 'install', '--nope']));
	}

	public function testUnknownVerbPrintsTheUsage() {
		ob_start();
		$code = \sy_main(['sy', 'nope']);
		$out  = ob_get_clean();

		$this->assertSame(2, $code);
		$this->assertStringContainsString('sy — project installer', $out);
	}

	public function testRootIsFoundFromTheComposerBinDirectory() {
		$this->writeJson('composer.json', []);
		mkdir($this->dir . '/protected/vendor/bin', 0777, true);
		mkdir($this->dir . '/protected/vendor/sy/bootstrap/bin', 0777, true);
		$this->writeJson('protected/vendor/sy/bootstrap/composer.json', ['name' => 'sy/bootstrap']);

		$GLOBALS['_composer_bin_dir'] = $this->dir . '/protected/vendor/bin';

		$this->assertSame($this->dir, \sy_root());
	}

	private function write(string $relative, string $content): void {
		$file = $this->dir . '/' . $relative;
		if (!is_dir(dirname($file))) mkdir(dirname($file), 0777, true);
		file_put_contents($file, $content);
	}

	private function writeJson(string $relative, array $data): void {
		$this->write($relative, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
	}

	private function removeDirectory(string $dir): void {
		if (!is_dir($dir)) return;
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
			\RecursiveIteratorIterator::CHILD_FIRST
		);
		foreach ($iterator as $item) {
			$item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
		}
		rmdir($dir);
	}

}
