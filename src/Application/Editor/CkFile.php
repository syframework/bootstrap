<?php
namespace Sy\Bootstrap\Application\Editor;

use Sy\Bootstrap\Lib\Str;

trait CkFile {

	/**
	 * CKEditor Browse
	 */
	public function browseAction() {
		$func = $this->get('CKEditorFuncNum');
		$id   = $this->get('id');
		$item = str_replace('_', '-', Str::camlToSnake($this->action));
		$type = $this->get('type');

		if (is_null($id)) {
			$this->requestError(['message' => 'Missing id parameter']);
		}

		$dir = UPLOAD_DIR . "/$item/$type/$id";

		// Delete file action
		$file = $this->post('delete-file');
		if (!empty($file)) {
			if (is_file($dir . "/$file")) {
				unlink($dir . "/$file");
				$this->redirect($_SERVER['REQUEST_URI']);
			}
		}

		$c = new \Sy\Component\WebComponent();
		$c->setTemplateFile(__DIR__ . '/Browse.html');
		$c->addTranslator(LANG_DIR);

		if (file_exists($dir)) {
			$files = scandir($dir);
			if (count($files) > 2) { /* The 2 accounts for . and .. */
				// All files
				foreach ($files as $file) {
					if (file_exists($dir . '/' . $file) and $file != '.' and $file != '..' and !is_dir($dir . '/' . $file)) {
						$url = UPLOAD_ROOT . "/$item/$type/$id/$file";
						$c->setVars(array(
							'FILE_NAME'  => $file,
							'FILE_SRC'   => $url,
							'FILE_CLICK' => "window.opener.CKEDITOR.tools.callFunction($func, '$url'); window.close();",
							'DELETE_URL' => $_SERVER['REQUEST_URI']
						));
						$c->setBlock(strtoupper($type) . '_BLOCK');
					}
				}
			}
		}

		$this->ok($c);
	}

	/**
	 * CKEditor Upload
	 */
	public function uploadAction() {
		$func = $this->get('CKEditorFuncNum');
		$id   = $this->get('id');
		$item = str_replace('_', '-', Str::camlToSnake($this->action));
		$type = $this->get('type');

		$url = '';
		$message = 'error';

		try {
			if (!is_null($id)) {
				$parts = pathinfo($_FILES['upload']['name']);
				switch ($type) {
					case 'image':
						$checkfile = '\Sy\Bootstrap\Lib\Image::isImage';
						break;
					default:
						$checkfile = null;
						break;
				}
				$file = Str::slugify($parts['filename']) . '.' . strtolower($parts['extension']);
				\Sy\Bootstrap\Lib\Upload::proceed(UPLOAD_DIR . "/$item/$type/$id/$file", 'upload', $checkfile);

				$url = UPLOAD_ROOT . "/$item/$type/$id/$file";
				$message = '';
			}
		} catch (\Sy\Bootstrap\Lib\Upload\Exception $e) {
			$message = $e->getMessage();
		}

		// Ckeditor 4.9+ works only json response
		if (isset($_GET['json'])) {
			$res = [
				'uploaded' => (empty($message) ? 1 : 0),
				'filename' => $file,
				'url'      => $url,
			];

			if (!empty($message)) $res['error']['message'] = $message;
			return $this->ok($res);
		} else {
			// Works for ckeditor <= 4.8
			return $this->ok("<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($func, '$url', '$message');</script>");
		}
	}

}