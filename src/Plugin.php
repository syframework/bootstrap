<?php
namespace Sy\Bootstrap;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;
use Composer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Composer\Util\ProcessExecutor;

class Plugin {

	/**
	 * Command: composer install-plugin [PLUGIN_NAME]
	 *
	 * @param Event $event
	 */
	public static function install(Event $event) {
		[$name, $version] = self::parseInstallArguments($event->getArguments());
		if (is_null($name) or $name === '') {
			echo "Missing plugin name: composer install-plugin [REQUIRED PLUGIN NAME] [OPTIONAL VERSION NUMBER]\n";
			exit(1);
		}

		// Run composer require
		$application = new Application();
		$plugin  = "sy/bootstrap-$name";
		$suffix  = is_null($version) || $version === '' ? '' : ":$version";
		$input = new ArrayInput([
			'command' => 'require',
			'packages' => [$plugin . $suffix],
		]);
		$application->run($input);
	}

	/**
	 * Parse `composer install-plugin <name> [version]`.
	 *
	 * Composer appends the script arguments in command order, so the version
	 * is the last argument and the plugin name is the one before it.
	 *
	 * @param array $args
	 * @return array{0: string|null, 1: string|null}
	 */
	public static function parseInstallArguments(array $args): array {
		$version = count($args) > 1 ? array_pop($args) : null;
		$name    = array_pop($args);

		return [
			is_string($name) ? trim($name) : null,
			is_string($version) ? trim($version) : null,
		];
	}

	/**
	 * Command: composer db [TASK]
	 *
	 * @param Event $event
	 */
	public static function db(Event $event) {
		$args = $event->getArguments();
		$task = array_pop($args);
		if (is_null($task)) {
			echo "Missing task: composer db [REQUIRED TASK]\n";
			exit(1);
		}

		// Only migrate task is supported currently
		if ($task !== 'migrate') return;

		// Migrate all plugins
		self::forEachPlugin($event->getComposer()->getConfig()->get('vendor-dir'), function ($vendor, $plugin) {
			self::migratePlugin($vendor, $plugin);
		});
	}

	/**
	 * Command: composer install-project-first
	 *
	 * Run the full project installation once, on the very first install.
	 * Guarded by the database configuration: an already configured project
	 * must not be reinstalled (no prompts, no rebuild, no sudo on every
	 * `composer install`).
	 *
	 * @param Event $event
	 */
	public static function installProjectFirst(Event $event) {
		if (is_file('protected/conf/database.ini')) return;

		$executor = new ProcessExecutor();
		// Keep the terminal attached: genini uses readline() to prompt for the
		// database and SMTP settings. Without a TTY it silently writes empty
		// values instead of asking.
		$executor->executeTty('composer run-script install-project');
	}

	/**
	 * Command: composer db migrate, when the project is configured.
	 *
	 * `composer create-project` triggers post-update-cmd, not post-install-cmd
	 * (the new project has no lock file), while protected/conf/database.ini
	 * does not exist yet. A raw ["@db migrate"] cannot be interrupted mid
	 * chain, so the guard lives here, before the whole call.
	 *
	 * @param Event $event
	 */
	public static function dbMigrateIfConfigured(Event $event) {
		if (!is_file('protected/conf/database.ini')) return;

		$event->getComposer()->getEventDispatcher()->dispatchScript('db', false, ['migrate']);
	}

	/**
	 * Wire `post-package-install`: the plugin was just installed.
	 *
	 * @param PackageEvent $event
	 */
	public static function postPackageInstall(PackageEvent $event) {
		$plugin = $event->getOperation()->getPackage()->getName();
		if (!str_starts_with($plugin, 'sy/bootstrap-')) return;
		echo "Plugin install: $plugin\n";

		$vendor = $event->getComposer()->getConfig()->get('vendor-dir');
		self::installPackage($vendor, $plugin, true);
	}

	/**
	 * Wire `post-package-update`: the project templates are never touched.
	 *
	 * @param PackageEvent $event
	 */
	public static function postPackageUpdate(PackageEvent $event) {
		$plugin = $event->getOperation()->getTargetPackage()->getName();
		if (!str_starts_with($plugin, 'sy/bootstrap-')) return;
		echo "Plugin update: $plugin\n";

		$vendor = $event->getComposer()->getConfig()->get('vendor-dir');
		self::installPackage($vendor, $plugin, false);
	}

	/**
	 * Wire `post-install-cmd`, `post-update-cmd` and `post-create-project-cmd`:
	 * synchronise every installed plugin in one pass.
	 *
	 * The per-package events cannot run during a first install: Composer fires
	 * them before it dumps the autoloader, so this class is not loadable yet
	 * (C62). This scan runs from a `-cmd` hook, i.e. after the dump, and needs
	 * no state file: the copy is mtime based, so a second run copies nothing.
	 *
	 * Migrations stay with `dbMigrateIfConfigured()`, which already covers every
	 * plugin on `post-update-cmd`; the build runs only when a plugin SCSS file is
	 * newer than the compiled CSS.
	 *
	 * @param Event $event
	 */
	public static function installPlugins(Event $event) {
		foreach (self::syncAllPlugins($event->getComposer()->getConfig()->get('vendor-dir')) as $line) {
			echo $line . "\n";
		}
	}

	/**
	 * Synchronise every installed plugin, then rebuild the CSS once if a plugin
	 * SCSS file is newer than it.
	 *
	 * Separate from `installPlugins()` so it can run without a Composer event,
	 * and so the scan is testable on a fixture project.
	 *
	 * @param string $vendor Project vendor directory
	 * @return string[] One line per plugin that actually copied something
	 */
	public static function syncAllPlugins(string $vendor): array {
		$css   = dirname($vendor, 2) . '/assets/css/app.css';
		$log   = [];
		$build = false;

		foreach (glob("$vendor/sy/bootstrap-*", GLOB_ONLYDIR) as $dir) {
			$copied = self::syncFiles($dir, $vendor);
			if ($copied !== []) $log[] = 'Plugin sync: sy/' . basename($dir) . ' (' . count($copied) . ' files)';
			$build = $build or self::needsBuild($dir, $css);
		}

		if ($build) self::build();

		return $log;
	}

	/**
	 * Copy one plugin's files, then run the steps it requires.
	 *
	 * @param string $vendor
	 * @param string $plugin
	 * @param bool   $templates Copy the templates too (install only)
	 */
	private static function installPackage(string $vendor, string $plugin, bool $templates) {
		self::syncFiles("$vendor/$plugin", $vendor, $templates);

		$requires = self::requiresInstall("$vendor/$plugin");

		if ($requires['build']) self::build();

		if ($requires['db']) {
			$executor = new ProcessExecutor();
			$executor->execute("$vendor/bin/flyway --conf protected/conf/database.ini --sql protected/sql --task repair");
			$executor->execute("$vendor/bin/flyway --conf protected/conf/database.ini --sql protected/sql --task migrate");
			self::migratePlugin($vendor, $plugin);
		}
	}

	/**
	 * Copy the templates and the assets of one plugin into the project.
	 *
	 * Assets are refreshed when the vendor copy is newer. Templates are copied
	 * only when they are missing: once a project has customised one, it owns it,
	 * on install as well as on update. The return value lists the copied files,
	 * so a caller can tell a real change from a no-op.
	 *
	 * @param string $pluginDir Absolute plugin directory
	 * @param string $vendorDir Project vendor directory
	 * @param bool   $templates Copy the templates too
	 * @return string[] Copied files, relative to the project directory they went to
	 */
	public static function syncFiles(string $pluginDir, string $vendorDir, bool $templates = true): array {
		$copied = [];
		if ($templates) {
			$copied = self::copyDir("$pluginDir/templates", dirname($vendorDir) . '/templates', false);
		}
		return array_merge($copied, self::copyDir("$pluginDir/assets", dirname($vendorDir, 2) . '/assets', true));
	}

	/**
	 * Is a plugin SCSS file newer than the compiled CSS?
	 *
	 * Lets the stateless scan skip the build — a Docker run — when nothing
	 * changed, without remembering the last build anywhere.
	 *
	 * @param string $pluginDir Absolute plugin directory
	 * @param string $cssFile   Compiled CSS the project serves
	 * @return bool
	 */
	public static function needsBuild(string $pluginDir, string $cssFile): bool {
		$scss = self::newestScssMtime($pluginDir);
		if ($scss === 0) return false;
		if (!is_file($cssFile)) return true;
		return $scss > (int) filemtime($cssFile);
	}

	/**
	 * Newest modification time of the SCSS files a plugin ships (0 if none).
	 *
	 * @param string $pluginDir
	 * @return int
	 */
	private static function newestScssMtime(string $pluginDir): int {
		$newest = 0;
		foreach (['scss', 'templates'] as $sub) {
			$directory = "$pluginDir/$sub";
			if (!is_dir($directory)) continue;
			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
			);
			foreach ($iterator as $file) {
				if (!$file->isFile() or strtolower($file->getExtension()) !== 'scss') continue;
				$newest = max($newest, (int) $file->getMTime());
			}
		}
		return $newest;
	}

	/**
	 * Compile the project SCSS (Docker gulp wrapper).
	 */
	private static function build() {
		$application = new Application();
		$application->setAutoExit(false);
		$application->run(new ArrayInput(['command' => 'build']));
	}

	/**
	 * Which installation steps a plugin needs.
	 *
	 * Public because it is a pure function of a directory path: this is the
	 * only way to test it without booting a fake project.
	 *
	 * @param string $pluginDir Absolute plugin directory
	 * @return array{build: bool, db: bool}
	 */
	public static function requiresInstall(string $pluginDir): array {
		return [
			'build' => self::hasScss($pluginDir),
			'db'    => is_dir("$pluginDir/sql"),
		];
	}

	/**
	 * Does the plugin ship any scss to compile?
	 *
	 * Checks scss/ and templates/, recursively: glob() does not understand
	 * `**`, hence the explicit walk.
	 *
	 * @param string $pluginDir Absolute plugin directory
	 * @return bool
	 */
	private static function hasScss(string $pluginDir): bool {
		foreach (['scss', 'templates'] as $sub) {
			$directory = "$pluginDir/$sub";
			if (!is_dir($directory)) continue;

			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
			);
			foreach ($iterator as $file) {
				if ($file->isFile() and strtolower($file->getExtension()) === 'scss') return true;
			}
		}

		return false;
	}

	private static function forEachPlugin(string $vendor, callable $callback) {
		foreach (glob("$vendor/sy/bootstrap-*", GLOB_ONLYDIR) as $dir) {
			$callback($vendor, 'sy/' . basename($dir));
		}
	}

	private static function migratePlugin(string $vendor, string $plugin) {
		if (!is_dir("$vendor/$plugin/sql")) return;
		$name = substr($plugin, strlen('sy/bootstrap-'));
		$executor = new ProcessExecutor();
		$command = "$vendor/bin/flyway --conf protected/conf/database.ini --sql $vendor/$plugin/sql --task repair --args '-baselineOnMigrate=true -baselineVersion=0 -table=flyway_{$name}_history'";
		$executor->execute($command);
		$command = "$vendor/bin/flyway --conf protected/conf/database.ini --sql $vendor/$plugin/sql --task migrate --args '-baselineOnMigrate=true -baselineVersion=0 -table=flyway_{$name}_history'";
		$executor->execute($command);
	}

	/**
	 * Copy a directory into the project.
	 *
	 * @param string $src
	 * @param string $dst
	 * @param bool   $refresh Overwrite a destination older than its source
	 * @return string[] Copied files, relative to $dst
	 */
	private static function copyDir($src, $dst, bool $refresh): array {
		if (!is_dir($src)) return [];

		$copied = [];
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($src, \FilesystemIterator::SKIP_DOTS),
			\RecursiveIteratorIterator::SELF_FIRST
		);
		foreach ($iterator as $item) {
			if ($item->isDir()) continue;
			$relative    = $iterator->getSubPathName();
			$destination = $dst . DIRECTORY_SEPARATOR . $relative;
			if (is_file($destination)) {
				// A template belongs to the project once copied; an asset is
				// refreshed only when the vendor copy is newer.
				if (!$refresh or $item->getMTime() <= filemtime($destination)) continue;
			}
			if (!is_dir(dirname($destination))) mkdir(dirname($destination), 0777, true);
			copy($item->getPathname(), $destination);
			$copied[] = $relative;
		}
		return $copied;
	}

}