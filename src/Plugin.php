<?php
namespace Sy\Bootstrap;

use Composer\Script\Event;
use Composer\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

class Plugin {

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
		$input = new ArrayInput(array(
			'command' => 'require',
			'packages' => [$plugin . (is_null($version) ? '' : ":$version")],
		));
		$application->setAutoExit(false);
		$application->run($input);

		// Copy templates files
		$composer = $event->getComposer();
		$vendor = $composer->getConfig()->get('vendor-dir');
		if (is_dir("$vendor/$plugin/templates")) {
			self::copyDir("$vendor/$plugin/templates", "$vendor/../templates");
			echo "Copy template files\n";
		}

		// Copy lang files
		if (is_dir("$vendor/$plugin/lang")) {
			self::copyDir("$vendor/$plugin/lang", "$vendor/../lang");
			echo "Copy lang files\n";
		}

		// Create flyway migration file
		if (file_exists("$vendor/$plugin/sql/install.sql")) {
			$nextVersion = self::nextVersionFlywayMigrationFile("$vendor/../sql");
			copy("$vendor/$plugin/sql/install.sql", "$vendor/../sql/V{$nextVersion}__install_{$name}.sql");
			echo "Copy sql migration file\n";
		}

		// Copy scss files
		if (is_dir("$vendor/$plugin/scss")) {
			self::copyDir("$vendor/$plugin/scss", "$vendor/../scss");
			echo "Copy scss files\n";
		}

		// Copy assets files
		if (is_dir("$vendor/$plugin/assets")) {
			self::copyDir("$vendor/$plugin/assets", "$vendor/../assets");
			echo "Copy assets files\n";
		}

		// Rebuild all
		$application->run(new ArrayInput(['command' => 'install-project']));
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

	/**
	 * @param  string $folderPath
	 * @return string
	 */
	private static function nextVersionFlywayMigrationFile($folderPath) {
		// Get all SQL files in the folder
		$files = glob($folderPath . '/*.sql');
		$maxVersion = '';
		// Find the highest version number
		foreach ($files as $file) {
			if (preg_match('/V([\d.]+)__.*\.sql/', $file, $matches)) {
				$version = $matches[1];
				if (version_compare($version, $maxVersion, '>')) {
					$maxVersion = $version;
				}
			}
		}
		// Increment the last part of the version number
		$nextVersionParts = explode('.', $maxVersion);
		$nextVersionParts[count($nextVersionParts) - 1]++;
		return implode('.', $nextVersionParts);
	}

}