<?php
namespace Sy\Test\Lib;

use PHPUnit\Framework\TestCase;
use Sy\Bootstrap\Lib\Image;

class ImageTest extends TestCase {

	private $dir;

	private $oldPath;

	protected function setUp(): void {
		if (DIRECTORY_SEPARATOR !== '/') {
			$this->markTestSkipped('Requires a Unix shell');
		}
		if (!function_exists('imagecreatetruecolor')) {
			$this->markTestSkipped('GD extension required');
		}

		$this->dir = sys_get_temp_dir() . '/sy-image-test-' . getmypid();
		$bin = $this->dir . '/bin';
		if (!is_dir($bin)) mkdir($bin, 0777, true);

		// Record the commands instead of running ImageMagick.
		$this->writeFakeCommand($bin . '/mogrify');
		$this->writeFakeCommand($bin . '/pngquant');

		$path = getenv('PATH');
		$this->oldPath = ($path === false) ? '' : $path;
		putenv('PATH=' . $bin . ($this->oldPath === '' ? '' : PATH_SEPARATOR . $this->oldPath));
	}

	protected function tearDown(): void {
		if (!is_null($this->oldPath)) putenv('PATH=' . $this->oldPath);
		$this->removeDir($this->dir);
	}

	public function testResizeKeepsTheOriginalFormatByDefault() {
		$file = $this->createJpeg('photo.jpg');

		Image::resize($file, 900, 600);

		$log = $this->commands();
		$this->assertStringContainsString('-format jpg', $log);
		$this->assertStringNotContainsString('-format jpeg', $log);
	}

	public function testResizeUsesTheExplicitType() {
		$file = $this->createJpeg('photo.jpg');

		Image::resize($file, 900, 600, 'png');

		$this->assertStringContainsString('-format png', $this->commands());
	}

	public function testResizeWritesBackToTheSameFile() {
		$file = $this->createJpeg('photo.jpg');
		$real = realpath($file);

		Image::resize($file, 900, 600);

		$this->assertStringContainsString("-format jpg -write $real $real", $this->commands());
	}

	private function createJpeg($name) {
		$file = $this->dir . '/' . $name;
		$im = imagecreatetruecolor(1200, 800);
		imagefill($im, 0, 0, imagecolorallocate($im, 200, 30, 30));
		imagejpeg($im, $file, 90);
		$this->assertFileExists($file);
		return $file;
	}

	private function writeFakeCommand($path) {
		$log = escapeshellarg($this->dir . '/commands.log');
		file_put_contents($path, "#!/bin/sh\necho \"\$*\" >> $log\n");
		chmod($path, 0755);
	}

	private function commands() {
		$log = $this->dir . '/commands.log';
		return is_file($log) ? file_get_contents($log) : '';
	}

	private function removeDir($dir) {
		if (!is_dir($dir)) return;
		foreach (scandir($dir) as $entry) {
			if ($entry === '.' or $entry === '..') continue;
			$path = $dir . '/' . $entry;
			if (is_dir($path) and !is_link($path)) {
				$this->removeDir($path);
			} else {
				unlink($path);
			}
		}
		rmdir($dir);
	}

}
