<?php
namespace Sy\Bootstrap\Lib;

class Number {

	/**
	 * Return a string to represent a distance in 'm' or 'km'
	 *
	 * @param  int $distance distance to format in meter
	 * @return string
	 */
	public static function formatDistance($distance) {
		$res = round($distance, 3);
		if (round($res) < 1) {
			return $res * (1000) . ' m';
		} else {
			return round($res, 1) . ' km';
		}
	}

	/**
	 * Return an encoded id
	 *
	 * @param int $id
	 */
	public static function encodeId($id) {
		$hashids = new \Hashids\Hashids(SALT);
		return $hashids->encode($id);
	}

	/**
	 * Return an encoded id
	 *
	 * @param int $id
	 */
	public static function decodeId($id) {
		$hashids = new \Hashids\Hashids(SALT);
		$res = $hashids->decode($id);
		return empty($res) ? null : $res[0];
	}

}