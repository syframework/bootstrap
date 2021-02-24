<?php
namespace Sy\Bootstrap\Application;

class Editor extends \Sy\Bootstrap\Component\Api {

	public function dispatch() {
		$this->actionDispatch(ACTION_TRIGGER);
	}

	/**
	 * CKEditor Upload
	 */
	public function uploadAction() {
		$func = $this->get('CKEditorFuncNum');
		$id   = $this->get('id');
		$item = $this->get('item');
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
				$file = \Sy\Bootstrap\Lib\Str::slugify($parts['filename']) . '.' . strtolower($parts['extension']);
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
				'url' => $url
			];

			if (!empty($message)) $res['error']['message'] = $message;
			echo json_encode($res);
		} else {
			// Works for ckeditor <= 4.8
			echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($func, '$url', '$message');</script>";
		}

		exit;
	}

	/**
	 * CKEditor Browse
	 */
	public function browseAction() {
		$func = $this->get('CKEditorFuncNum');
		$id   = $this->get('id');
		$item = $this->get('item');
		$type = $this->get('type');

		if (is_null($id)) exit();

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
		$c->setTemplateFile(__DIR__ . '/Editor/Browse.html');
		$c->addTranslator(LANG_DIR);

		if (file_exists($dir)) {
			$files = scandir($dir);
			if( count($files) > 2 ) { /* The 2 accounts for . and .. */
				// All files
				foreach( $files as $file ) {
					if( file_exists($dir . '/' . $file) and $file != '.' and $file != '..' and !is_dir($dir . '/' . $file) ) {
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

		echo $c;
		exit;
	}

	public function security() {
		$service = \Project\Service\Container::getInstance();
		$user = $service->user->getCurrentUser();
		$id   = $this->get('id');
		$item = $this->get('item');

		// Check if a plugin class exists
		$class = 'Sy\\Bootstrap\\Application\\Editor\\' . ucfirst($item);
		if (class_exists($class)) {
			$plugin = new $class();
			if (!$plugin->authorized($id)) exit;
		} else {
			if (!$user->hasPermission('page-update')) exit;
		}
	}

}