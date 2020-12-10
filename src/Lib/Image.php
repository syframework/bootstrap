<?php
namespace Sy\Bootstrap\Lib;

class Image {

	/**
	 * Resize an image with extending the canvas
	 *
	 * @param string $fileName
	 * @param int $width
	 * @param int $height
	 * @param type $type
	 */
	public static function resize($fileName, $width, $height, $type = 'jpeg') {
		list($wo, $ho) = getimagesize($fileName);
		if (($wo / $ho) > ($width / $height)) {
			$resize = $width . 'x';
		} else {
			$resize = 'x' . $height;
		}
		$file = realpath($fileName);
		exec("mogrify -resize $resize $file");
		exec("mogrify -format $type -write $file $file");
		exec("mogrify -transparent white -gravity center -extent {$width}x$height $file");
		if ($type === 'png') {
			exec("pngquant -o $file --force $file");
		}
	}

	/**
	 * Resize image with cropping
	 *
	 * @param type $fileName
	 * @param type $width
	 * @param type $height
	 * @param type $type
	 */
	public static function crop($fileName, $width, $height, $type = 'jpeg') {
		list($wo, $ho) = getimagesize($fileName);
		if (($wo / $ho) > ($width / $height)) {
			$w = $width * $ho / $height;
			$h = $ho;
			$x = ($wo - $w) / 2;
			$y = 0;
		} else {
			$w = $wo;
			$h = $height * $w / $width;
			$x = 0;
			$y = ($ho - $h) / 2;
		}
		$image = imagecreatefromstring(file_get_contents($fileName));
		$new_image = imagecreatetruecolor($width, $height);
		if ($type === 'png') {
			imagealphablending($new_image, false);
			imagesavealpha($new_image, true);
		}
		imagecopyresampled($new_image, $image, 0, 0, $x, $y, $width, $height, $w, $h);
		$f = "image$type";
		$f($new_image, $fileName);
		imagedestroy($image);
		imagedestroy($new_image);
		if ($type === 'png') {
			$file = realpath($fileName);
			exec("pngquant -o $file --force $file");
		}
	}

	public static function isImage($file) {
		if (\file_exists($file) and \filesize($file) > 11 and in_array(\exif_imagetype($file), array(\IMAGETYPE_GIF, \IMAGETYPE_JPEG, \IMAGETYPE_PNG, \IMAGETYPE_BMP, \IMAGETYPE_TIFF_II, \IMAGETYPE_TIFF_MM))) {
			return true;
		} else {
			return false;
		}
	}

}