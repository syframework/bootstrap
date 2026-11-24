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
		// Plugin name passed from arguments
		$args = $event->getArguments();
		$name = array_pop($args);
		$version = array_pop($args);
		if (is_null($name)) {
			echo "Missing plugin name: composer install-plugin [REQUIRED PLUGIN NAME] [OPTIONAL VERSION NUMBER]\n";
			exit(1);
		}

		// Run composer require
		$application = new Application();
		$plugin = "sy/bootstrap-$name";
		$input = new ArrayInput([
			'command' => 'require',
			'packages' => [$plugin . (is_null($version) ? '' : ":$version")],
		]);
		$application->run($input);
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
		$executor->execute('composer run-script install-project');
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

	public static function postPackageInstall(PackageEvent $event) {
		$plugin = $event->getOperation()->getPackage()->getName();
		if (!str_starts_with($plugin, 'sy/bootstrap-')) return;
		echo "Plugin install: $plugin\n";

		$name = substr($plugin, strlen('sy/bootstrap-'));
		echo "Plugin name: $name\n";

		$vendor = $event->getComposer()->getConfig()->get('vendor-dir');
		echo "Plugin vendor: $vendor\n";

		// Copy template files
		self::copyTemplates($vendor, $plugin);

		// Copy assets files
		self::copyAssets($vendor, $plugin);

		$requires = self::requiresInstall("$vendor/$plugin");

		// Rebuild all, only when the plugin ships scss
		if ($requires['build']) {
			$application = new Application();
			$application->setAutoExit(false);
			$application->run(new ArrayInput(['command' => 'build']));
		}

		// Db migrate, only when the plugin ships sql
		if ($requires['db']) {
			$executor = new ProcessExecutor();
			$command = "$vendor/bin/flyway --conf protected/conf/database.ini --sql protected/sql --task repair";
			$executor->execute($command);
			$command = "$vendor/bin/flyway --conf protected/conf/database.ini --sql protected/sql --task migrate";
			$executor->execute($command);
			self::migratePlugin($vendor, $plugin);
		}
	}

	public static function postPackageUpdate(PackageEvent $event) {
		$plugin = $event->getOperation()->getTargetPackage()->getName();
		if (!str_starts_with($plugin, 'sy/bootstrap-')) return;
		echo "Plugin update: $plugin\n";

		$name = substr($plugin, strlen('sy/bootstrap-'));
		echo "Plugin name: $name\n";

		$vendor = $event->getComposer()->getConfig()->get('vendor-dir');
		echo "Plugin vendor: $vendor\n";

		// Copy assets files
		self::copyAssets($vendor, $plugin);

		// Templates are never copied on update: they belong to the project

		$requires = self::requiresInstall("$vendor/$plugin");

		// Rebuild all, only when the plugin ships scss
		if ($requires['build']) {
			$application = new Application();
			$application->setAutoExit(false);
			$application->run(new ArrayInput(['command' => 'build']));
		}

		// Db migrate, only when the plugin ships sql
		if ($requires['db']) {
			self::migratePlugin($vendor, $plugin);
		}
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

	private static function copyTemplates(string $vendor, string $plugin) {
		if (!is_dir("$vendor/$plugin/templates")) return;
		self::copyDir("$vendor/$plugin/templates", "$vendor/../templates");
		echo "Copy template files\n";
	}

	private static function copyAssets(string $vendor, string $plugin) {
		if (!is_dir("$vendor/$plugin/assets")) return;
		self::copyDir("$vendor/$plugin/assets", "$vendor/../../assets");
		echo "Copy assets files\n";
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
	 * @param string $src
	 * @param string $dst
	 */
	private static function copyDir($src, $dst) {
		$dir = new \RecursiveDirectoryIterator($src, \RecursiveDirectoryIterator::SKIP_DOTS);
		$iterator = new \RecursiveIteratorIterator($dir, \RecursiveIteratorIterator::SELF_FIRST);
		foreach ($iterator as $item) {
			$destination = $dst . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
			if ($item->isDir()) continue;
			if (!file_exists(dirname($destination))) mkdir(directory: dirname($destination), recursive: true);
			copy($item, $destination);
		}
	}

}