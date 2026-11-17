<?php
namespace Sy\Test\Application\Editor;

use PHPUnit\Framework\TestCase;

if (!defined('UPLOAD_DIR')) {
	define('UPLOAD_DIR', sys_get_temp_dir() . '/sy-ckfile-test-' . getmypid());
}
if (!defined('UPLOAD_ROOT')) {
	define('UPLOAD_ROOT', '/upload');
}

/**
 * Minimal harness exposing the protected resolvers of the CkFile trait
 * without booting a full Component.
 */
class CkFileHarness {

	use \Sy\Bootstrap\Application\Editor\CkFile;

	public $action = 'Article';

	protected $query = [];

	protected $posted = [];

	public function setQuery(array $query) {
		$this->query = $query;
	}

	public function setPost(array $posted) {
		$this->posted = $posted;
	}

	protected function get($param, $default = null) {
		return array_key_exists($param, $this->query) ? $this->query[$param] : $default;
	}

	protected function post($param, $default = null) {
		return array_key_exists($param, $this->posted) ? $this->posted[$param] : $default;
	}

	public function item() {
		return $this->requestItem();
	}

	public function type() {
		return $this->requestType();
	}

	public function id() {
		return $this->requestId();
	}

	public function funcNum() {
		return $this->requestFuncNum();
	}

}

class CkFileTest extends TestCase {

	private $root;

	protected function setUp(): void {
		$this->root = UPLOAD_DIR;
		if (!is_dir($this->root)) {
			mkdir($this->root, 0777, true);
		}
	}

	protected function tearDown(): void {
		$this->removeDir($this->root);
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

	private function harness(array $query = [], array $posted = []) {
		$h = new CkFileHarness();
		$h->setQuery($query);
		$h->setPost($posted);
		return $h;
	}

	public function testItemComesFromActionByDefault() {
		$this->assertEquals('article', $this->harness()->item());

		$h = $this->harness();
		$h->action = 'Content';
		$this->assertEquals('content', $h->item());

		$h = $this->harness();
		$h->action = 'Place';
		$this->assertEquals('place', $h->item());
	}

	public function testExplicitItemWins() {
		$this->assertEquals('place', $this->harness(['item' => 'place'])->item());
	}

	public function testInvalidItemIsRejected() {
		$this->assertNull($this->harness(['item' => '../etc'])->item());
		$this->assertNull($this->harness(['item' => 'Article'])->item());
		$this->assertNull($this->harness(['item' => 'a/b'])->item());
		$this->assertNull($this->harness(['item' => str_repeat('a', 33)])->item());
	}

	public function testItemValidation() {
		$this->assertTrue(CkFileHarness::isValidItem('place'));
		$this->assertTrue(CkFileHarness::isValidItem('article-2'));
		$this->assertFalse(CkFileHarness::isValidItem('Place'));
		$this->assertFalse(CkFileHarness::isValidItem('-place'));
		$this->assertFalse(CkFileHarness::isValidItem('place/../'));
		$this->assertFalse(CkFileHarness::isValidItem(''));
		$this->assertFalse(CkFileHarness::isValidItem(null));
	}

	public function testTypeValidation() {
		$this->assertEquals('image', $this->harness(['type' => 'image'])->type());
		$this->assertEquals('file', $this->harness(['type' => 'file'])->type());

		$this->assertNull($this->harness(['type' => 'svg'])->type());
		$this->assertNull($this->harness(['type' => 'Image'])->type());
		$this->assertNull($this->harness([])->type());
	}

	public function testIdValidation() {
		$this->assertEquals('abc-123_X', $this->harness(['id' => 'abc-123_X'])->id());
		$this->assertEquals('42', $this->harness(['id' => 42])->id());

		$this->assertNull($this->harness(['id' => '../etc'])->id());
		$this->assertNull($this->harness(['id' => 'a/b'])->id());
		$this->assertNull($this->harness(['id' => ''])->id());
		$this->assertNull($this->harness(['id' => str_repeat('a', 65)])->id());
		$this->assertNull($this->harness([])->id());
	}

	public function testFuncNumValidation() {
		$this->assertSame(3, $this->harness(['CKEditorFuncNum' => '3'])->funcNum());
		$this->assertSame(7, $this->harness(['CKEditorFuncNum' => 7])->funcNum());
		$this->assertSame(0, $this->harness(['CKEditorFuncNum' => '3abc'])->funcNum());
		$this->assertSame(0, $this->harness(['CKEditorFuncNum' => '-1'])->funcNum());
		$this->assertSame(0, $this->harness(['CKEditorFuncNum' => ''])->funcNum());
		$this->assertSame(0, $this->harness([])->funcNum());
	}

	public function testAllowedExtensions() {
		$images = CkFileHarness::allowedExtensions('image');
		$this->assertContains('jpg', $images);
		$this->assertContains('png', $images);
		$this->assertContains('webp', $images);
		$this->assertNotContains('svg', $images);
		$this->assertNotContains('php', $images);

		$files = CkFileHarness::allowedExtensions('file');
		$this->assertContains('pdf', $files);
		$this->assertNotContains('svg', $files);
		$this->assertNotContains('php', $files);

		$this->assertEquals([], CkFileHarness::allowedExtensions('unknown'));
	}

	public function testBuildFileName() {
		$this->assertEquals('photo-ete.jpg', CkFileHarness::buildFileName('image', 'Photo Été.JPG'));
		$this->assertEquals('report.pdf', CkFileHarness::buildFileName('file', 'report.pdf'));

		$this->assertNull(CkFileHarness::buildFileName('image', 'shell.php'));
		$this->assertNull(CkFileHarness::buildFileName('image', 'vector.svg'));
		$this->assertNull(CkFileHarness::buildFileName('file', 'noextension'));
		$this->assertNull(CkFileHarness::buildFileName('file', ''));
		$this->assertNull(CkFileHarness::buildFileName('unknown', 'file.pdf'));
	}

	public function testResolveUploadDirectory() {
		$dir = CkFileHarness::resolveUploadDirectory('article', 'image', '42');
		$this->assertNotNull($dir);
		$this->assertStringStartsWith(realpath($this->root), $dir);
	}

	public function testResolveUploadDirectoryRejectsTraversal() {
		$this->assertNull(CkFileHarness::resolveUploadDirectory('../../etc', 'image', '42'));
		$this->assertNull(CkFileHarness::resolveUploadDirectory('article', 'image', '../../etc'));
		$this->assertNull(CkFileHarness::resolveUploadDirectory('article', 'php', '42'));
	}

	public function testResolveUploadDirectoryRejectsSymlinkEscape() {
		$outside = sys_get_temp_dir() . '/sy-ckfile-outside-' . getmypid();
		if (!is_dir($outside)) mkdir($outside, 0777, true);

		$itemDir = $this->root . '/article';
		if (!is_dir($itemDir)) mkdir($itemDir, 0777, true);

		$link = $itemDir . '/image';
		if (file_exists($link) or is_link($link)) {
			@unlink($link);
		}

		if (!@symlink($outside, $link)) {
			@rmdir($outside);
			$this->markTestSkipped('Symlink not supported');
		}

		$this->assertNull(CkFileHarness::resolveUploadDirectory('article', 'image', '42'));

		unlink($link);
		rmdir($outside);
	}

	public function testDeleteFile() {
		$dir = $this->root . '/article/image/42';
		mkdir($dir, 0777, true);
		file_put_contents($dir . '/photo.jpg', 'x');

		$this->assertTrue(CkFileHarness::deleteFile($dir, 'photo.jpg'));
		$this->assertFileDoesNotExist($dir . '/photo.jpg');
	}

	public function testDeleteFileRejectsTraversal() {
		$dir = $this->root . '/article/image/42';
		mkdir($dir, 0777, true);
		file_put_contents($this->root . '/secret.txt', 'x');

		$this->assertFalse(CkFileHarness::deleteFile($dir, '../secret.txt'));
		$this->assertFalse(CkFileHarness::deleteFile($dir, '../../secret.txt'));
		$this->assertFalse(CkFileHarness::deleteFile($dir, '/etc/passwd'));
		$this->assertFalse(CkFileHarness::deleteFile($dir, 'missing.txt'));
		$this->assertFalse(CkFileHarness::deleteFile($dir, ''));
		$this->assertFileExists($this->root . '/secret.txt');

		unlink($this->root . '/secret.txt');
	}

	public function testEscapeAttr() {
		$this->assertEquals(
			'&quot;&lt;script&gt;&#039;&amp;',
			CkFileHarness::escapeAttr('"<script>\'&')
		);
	}

	public function testBrowseTemplateHasNoInlineCallback() {
		$template = file_get_contents(__DIR__ . '/../../../src/Application/Editor/Browse.html');
		$this->assertIsString($template);

		$this->assertStringNotContainsString('{FILE_CLICK}', $template);
		$this->assertStringNotContainsString('onclick=', $template);
		$this->assertStringContainsString('data-file-url="{FILE_SRC}"', $template);
		$this->assertStringContainsString('data-ckeditor-func-num="{CKEDITOR_FUNC_NUM}"', $template);
		$this->assertStringContainsString('data-confirm=', $template);
	}

	public function testBrowseTemplateEscapesFileNames() {
		$c = new \Sy\Component\WebComponent();
		$c->setTemplateFile(__DIR__ . '/../../../src/Application/Editor/Browse.html');
		$c->setVars([
			'CKEDITOR_FUNC_NUM' => 3,
			'DELETE_URL'        => CkFileHarness::escapeAttr('/editor/article/browse?id=1&type=image'),
		]);
		$c->setVars([
			'FILE_NAME' => CkFileHarness::escapeAttr('a"b<c.jpg'),
			'FILE_SRC'  => CkFileHarness::escapeAttr('/upload/article/image/1/a%22b.jpg'),
		]);
		$c->setBlock('IMAGE_BLOCK');
		$html = (string) $c;

		$this->assertStringNotContainsString('onclick=', $html);
		$this->assertStringContainsString('a&quot;b&lt;c.jpg', $html);
		$this->assertStringNotContainsString('title="a"b', $html);
		$this->assertStringContainsString('data-file-url="/upload/article/image/1/a%22b.jpg"', $html);
	}

}
