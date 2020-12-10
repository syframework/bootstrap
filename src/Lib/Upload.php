<?php
namespace Sy\Bootstrap\Lib;

class Upload {

	/**
	 * This function transforms the php.ini notation for numbers (like '2M')
	 * to an integer (2*1024*1024 in this case)
	 *
	 * @param int $v
	 * @return int
	 */
	public static function getMaxFileSize() {
		$letToNum = function($v) {
			$l = substr($v, -1);
			$ret = substr($v, 0, -1);
			switch (strtoupper($l)) {
				case 'P':
					$ret *= 1024;
				case 'T':
					$ret *= 1024;
				case 'G':
					$ret *= 1024;
				case 'M':
					$ret *= 1024;
				case 'K':
					$ret *= 1024;
					break;
			}
			return $ret;
		};
		return min($letToNum(ini_get('post_max_size')), $letToNum(ini_get('upload_max_filesize')));
	}

	public static function proceed($fileName, $fileFieldName, $checkfile = null) {
		$dir = dirname($fileName);

		if (!file_exists($dir)) {
			mkdir($dir, 0777, true);
		}

		// check error
		if ($_FILES[$fileFieldName]['error'] !== UPLOAD_ERR_OK) {
			throw new Upload\Exception($_FILES[$fileFieldName]['error']);
		}

		// check file
		if (!is_null($checkfile) and is_callable($checkfile)) {
			if (!call_user_func($checkfile, $_FILES[$fileFieldName]['tmp_name'])) {
				throw new Upload\Exception('Check file error');
			}
		}

		// delete old file
		if (file_exists($fileName)) {
			unlink($fileName);
		}

		// copy uploaded file
		if (!move_uploaded_file($_FILES[$fileFieldName]['tmp_name'], $fileName)) {
			throw new Upload\Exception('Move uploaded file error');
		}
	}

	/**
	 * Delete uploaded files
	 *
	 * @param string $path Path to a folder or a file
	 * @return void
	 */
	public static function delete($path) {
		$dir = substr($path, 0, strlen(UPLOAD_DIR)) === UPLOAD_DIR ? $path : UPLOAD_DIR . "/$path";
		if (!file_exists($dir)) return;
		if (is_dir($dir)) {
			array_map('\Sy\Bootstrap\Lib\Upload::delete', glob("$dir/*"));
			rmdir($dir);
		} elseif (is_link($dir)) {
			unlink(readlink($dir));
			unlink($dir);
		} else {
			unlink($dir);
		}
	}

}

namespace Sy\Bootstrap\Lib\Upload;

class Exception extends \Exception {

	public function __construct($code) {
		$message = is_numeric($code) ? $this->codeToMessage($code) : $code;
		parent::__construct($message);
	}

	private function codeToMessage($code) {
		switch ($code) {
			case UPLOAD_ERR_INI_SIZE:
				$message = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
				break;
			case UPLOAD_ERR_FORM_SIZE:
				$message = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
				break;
			case UPLOAD_ERR_PARTIAL:
				$message = 'The uploaded file was only partially uploaded';
				break;
			case UPLOAD_ERR_NO_FILE:
				$message = 'No file was uploaded';
				break;
			case UPLOAD_ERR_NO_TMP_DIR:
				$message = 'Missing a temporary folder';
				break;
			case UPLOAD_ERR_CANT_WRITE:
				$message = 'Failed to write file to disk';
				break;
			case UPLOAD_ERR_EXTENSION:
				$message = 'File upload stopped by extension';
				break;
			default:
				$message = 'Unknown upload error';
				break;
		}
		return $message;
	}

}