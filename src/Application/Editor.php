<?php
namespace Sy\Bootstrap\Application;

use Sy\Bootstrap\Lib\Str;

class Editor extends \Sy\Bootstrap\Component\Api {

	public function security() {
		$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : null;
		if (empty($origin) and isset($_SERVER['HTTP_REFERER'])) {
			$origin = $_SERVER['HTTP_REFERER'];
		}
		if (empty($origin)) {
			throw new \Sy\Bootstrap\Component\Api\ForbiddenException('No HTTP origin found');
		}
		if ($_SERVER['SERVER_NAME'] !== parse_url($origin)['host']) {
			throw new \Sy\Bootstrap\Component\Api\ForbiddenException('Server name do not match with HTTP origin');
		}
	}

	public function dispatch() {
		// If no action method found, check if a plugin api class exists
		$c = $this->get('item');
		if (is_null($c)) return $this->requestError(['message' => 'Missing item parameter']);

		$class = 'Sy\\Bootstrap\\Application\\Editor\\' . ucfirst(Str::snakeToCaml($c));
		if (class_exists($class)) {
			$this->setVar('RESPONSE', new $class());
			return;
		}

		parent::dispatch();
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

		echo $c;
		exit;
	}

}