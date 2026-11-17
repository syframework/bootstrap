<?php
namespace Sy\Bootstrap\Application\Editor;

use Sy\Bootstrap\Lib\Image;
use Sy\Bootstrap\Lib\Str;
use Sy\Bootstrap\Lib\Upload;

trait CkFile {

	/**
	 * Item name pattern (place, article, content...)
	 */
	const ITEM_PATTERN = '/^[a-z][a-z0-9-]{0,31}$/';

	/**
	 * Record id pattern
	 */
	const ID_PATTERN = '/^[A-Za-z0-9_-]{1,64}$/';

	/**
	 * Allowed upload extensions, by type.
	 *
	 * SVG is intentionally excluded: it can carry scripts and is served
	 * from the same origin as the application.
	 *
	 * @var array
	 */
	private static $allowedExtensions = [
		'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
		'file'  => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'odt', 'ods', 'zip', 'txt', 'csv', 'mp3', 'mp4'],
	];

	/**
	 * CKEditor Browse
	 */
	public function browseAction() {
		$item = $this->requestItem();
		$type = $this->requestType();
		$id   = $this->requestId();

		if (is_null($item) or is_null($type) or is_null($id)) {
			$this->requestError(['message' => 'Invalid request parameters']);
			return;
		}

		$dir = self::resolveUploadDirectory($item, $type, $id);
		if (is_null($dir)) {
			$this->requestError(['message' => 'Invalid upload directory']);
			return;
		}

		$func = $this->requestFuncNum();

		// Delete file action
		$deleteFile = $this->post('delete-file');
		if (!empty($deleteFile)) {
			if (self::deleteFile($dir, $deleteFile)) {
				$this->redirect(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '');
				return;
			}
			$this->requestError(['message' => 'Invalid file']);
			return;
		}

		$c = new \Sy\Component\WebComponent();
		$c->setTemplateFile(__DIR__ . '/Browse.html');
		$c->addTranslator(LANG_DIR);
		$c->setVars([
			'CKEDITOR_FUNC_NUM' => $func,
			'DELETE_URL'        => self::escapeAttr(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : ''),
		]);

		if (is_dir($dir)) {
			$files = scandir($dir);
			if (is_array($files) and count($files) > 2) { /* The 2 accounts for . and .. */
				foreach ($files as $file) {
					if ($file === '.' or $file === '..') continue;

					$path = $dir . DIRECTORY_SEPARATOR . $file;
					if (!is_file($path)) continue;

					$c->setVars([
						'FILE_NAME' => self::escapeAttr($file),
						'FILE_SRC'  => self::escapeAttr(UPLOAD_ROOT . "/$item/$type/$id/" . rawurlencode($file)),
					]);
					$c->setBlock(strtoupper($type) . '_BLOCK');
				}
			}
		}

		return $this->ok($c);
	}

	/**
	 * CKEditor Upload
	 */
	public function uploadAction() {
		return $this->uploadResponse($this->processUpload());
	}

	/**
	 * Validate the request, move the uploaded file and return the result.
	 *
	 * No HTTP response is set here: CKEditor expects a 200 response with an
	 * "uploaded" flag, even on failure.
	 *
	 * @return array
	 */
	protected function processUpload() {
		$result = [
			'item'    => null,
			'type'    => null,
			'id'      => null,
			'file'    => null,
			'path'    => null,
			'url'     => '',
			'message' => '',
		];

		$item = $this->requestItem();
		$type = $this->requestType();
		$id   = $this->requestId();

		if (is_null($item) or is_null($type) or is_null($id)) {
			$result['message'] = 'Invalid request parameters';
			return $result;
		}

		$result['item'] = $item;
		$result['type'] = $type;
		$result['id']   = $id;

		$dir = self::resolveUploadDirectory($item, $type, $id);
		if (is_null($dir)) {
			$result['message'] = 'Invalid upload directory';
			return $result;
		}

		if (!isset($_FILES['upload']) or !is_array($_FILES['upload']) or !isset($_FILES['upload']['name'])) {
			$result['message'] = 'Missing upload file';
			return $result;
		}

		$file = self::buildFileName($type, $_FILES['upload']['name']);
		if (is_null($file)) {
			$result['message'] = 'File extension not allowed';
			return $result;
		}

		if (isset($_FILES['upload']['size']) and $_FILES['upload']['size'] > Upload::getMaxFileSize()) {
			$result['message'] = 'File is too big';
			return $result;
		}

		try {
			$checkfile = ($type === 'image') ? [Image::class, 'isImage'] : null;
			Upload::proceed($dir . DIRECTORY_SEPARATOR . $file, 'upload', $checkfile);
		} catch (\Sy\Bootstrap\Lib\Upload\Exception $e) {
			$result['message'] = $e->getMessage();
			return $result;
		} catch (\Throwable $e) {
			$result['message'] = 'Upload failed';
			return $result;
		}

		$result['file'] = $file;
		$result['path'] = $dir . DIRECTORY_SEPARATOR . $file;
		$result['url']  = UPLOAD_ROOT . "/$item/$type/$id/" . rawurlencode($file);

		return $result;
	}

	/**
	 * @param array $result Result of processUpload()
	 * @return mixed
	 */
	protected function uploadResponse(array $result) {
		$func    = $this->requestFuncNum();
		$file    = $result['file'];
		$url     = $result['url'];
		$message = $result['message'];

		// Ckeditor 4.9+ works only json response
		if (isset($_GET['json'])) {
			$res = [
				'uploaded' => (empty($message) ? 1 : 0),
				'filename' => $file,
				'url'      => $url,
			];

			if (!empty($message)) $res['error']['message'] = $message;
			return $this->ok($res);
		}

		// Works for ckeditor <= 4.8
		$args = [];
		foreach ([$func, $url, $message] as $value) {
			$args[] = json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
		}

		return $this->ok("<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction(" . implode(', ', $args) . ');</script>');
	}

	/**
	 * Resolve the item name.
	 *
	 * An explicit "item" parameter wins, otherwise fall back to the
	 * controller action name (Article, Content, Place...).
	 *
	 * @return string|null
	 */
	protected function requestItem() {
		$item = $this->get('item');
		if (is_string($item) and $item !== '') {
			return self::isValidItem($item) ? $item : null;
		}

		$item = str_replace('_', '-', Str::camlToSnake(is_string($this->action) ? $this->action : ''));
		return self::isValidItem($item) ? $item : null;
	}

	/**
	 * @return string|null
	 */
	protected function requestType() {
		$type = $this->get('type');
		return (is_string($type) and self::isValidType($type)) ? $type : null;
	}

	/**
	 * @return string|null
	 */
	protected function requestId() {
		$id = $this->get('id');
		if (is_int($id)) $id = (string) $id;
		return (is_string($id) and self::isValidId($id)) ? $id : null;
	}

	/**
	 * @return int
	 */
	protected function requestFuncNum() {
		$func = $this->get('CKEditorFuncNum');
		if (is_int($func)) return max(0, $func);
		if (!is_string($func) or !ctype_digit($func)) return 0;
		return (int) $func;
	}

	/**
	 * @param mixed $item
	 * @return bool
	 */
	public static function isValidItem($item) {
		return is_string($item) and preg_match(self::ITEM_PATTERN, $item) === 1;
	}

	/**
	 * @param mixed $type
	 * @return bool
	 */
	public static function isValidType($type) {
		return is_string($type) and in_array($type, ['image', 'file'], true);
	}

	/**
	 * @param mixed $id
	 * @return bool
	 */
	public static function isValidId($id) {
		return is_string($id) and preg_match(self::ID_PATTERN, $id) === 1;
	}

	/**
	 * @param string $type
	 * @return array
	 */
	public static function allowedExtensions($type) {
		return isset(self::$allowedExtensions[$type]) ? self::$allowedExtensions[$type] : [];
	}

	/**
	 * Build a safe file name from the original uploaded name.
	 *
	 * @param string $type
	 * @param string $originalName
	 * @return string|null Null when the extension is missing or not allowed
	 */
	public static function buildFileName($type, $originalName) {
		$parts = pathinfo((string) $originalName);
		if (!isset($parts['extension']) or $parts['extension'] === '') return null;

		$extension = strtolower($parts['extension']);
		if (!in_array($extension, self::allowedExtensions($type), true)) return null;

		$base = isset($parts['filename']) ? Str::slugify($parts['filename']) : '';
		if ($base === '') $base = 'file';

		return $base . '.' . $extension;
	}

	/**
	 * Resolve "<UPLOAD_DIR>/<item>/<type>/<id>" and make sure it stays
	 * inside UPLOAD_DIR, even when intermediate directories do not exist yet
	 * or when a symlink tries to escape the root.
	 *
	 * @param string $item
	 * @param string $type
	 * @param string $id
	 * @return string|null
	 */
	public static function resolveUploadDirectory($item, $type, $id) {
		if (!self::isValidItem($item) or !self::isValidType($type) or !self::isValidId($id)) return null;

		$root = realpath(UPLOAD_DIR);
		if ($root === false) return null;

		$dir = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $item . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $id;

		$probe = $dir;
		while (!file_exists($probe)) {
			$parent = dirname($probe);
			if ($parent === $probe) return null;
			$probe = $parent;
		}

		$real = realpath($probe);
		if ($real === false) return null;

		if ($real !== $root and strncmp($real . DIRECTORY_SEPARATOR, $root . DIRECTORY_SEPARATOR, strlen($root) + 1) !== 0) {
			return null;
		}

		return $dir;
	}

	/**
	 * Delete a file from a directory, refusing any path traversal.
	 *
	 * @param string $dir
	 * @param mixed $name
	 * @return bool
	 */
	public static function deleteFile($dir, $name) {
		if (!is_string($name) or $name === '') return false;
		if (strpos($name, "\0") !== false) return false;
		if (basename($name) !== $name) return false;

		$path = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $name;
		if (!is_file($path)) return false;

		$realDir  = realpath($dir);
		$realPath = realpath($path);
		if ($realDir === false or $realPath === false) return false;
		if (strncmp($realPath, $realDir . DIRECTORY_SEPARATOR, strlen($realDir) + 1) !== 0) return false;

		return unlink($path);
	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public static function escapeAttr($value) {
		return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
	}

}
