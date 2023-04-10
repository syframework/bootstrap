<?php
namespace Sy\Bootstrap\Lib\Url;

interface IConverter {

	/**
	 * Return an URL using an array of parameters
	 * Return false when conversion is not possible
	 *
	 * @param  array $params
	 * @return string|false
	 */
	public function paramsToUrl(array $params);

	/**
	 * Return an array of parameters using an URL
	 * Return false when conversion is not possible
	 *
	 * @param  string $url
	 * @return array|false
	 */
	public function urlToParams($url);

}