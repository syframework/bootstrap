<?php
namespace Sy\Bootstrap\Application\Editor;

class Page extends \Sy\Bootstrap\Component\Api {

	public function security() {
		$service = \Project\Service\Container::getInstance();
		if (!$service->user->getCurrentUser()->hasPermission('page-update')) {
			$this->forbidden([
				'status' => 'ko',
				'message' => 'Permission denied'
			]);
		}
	}

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

}