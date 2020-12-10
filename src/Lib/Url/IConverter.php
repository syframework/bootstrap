<?php
namespace Sy\Bootstrap\Lib\Url;

interface IConverter {

	public function paramsToUrl(array $params);

	public function urlToParams($url);

}