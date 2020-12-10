<?php
namespace Sy\Bootstrap\Component;

class Sitemap extends \Sy\Component\WebComponent {

	public function __construct() {
		parent::__construct();
		$this->setTemplateFile(__DIR__ . '/Sitemap/Sitemap.xml');
	}

	public function out() {
		header("Content-type: text/xml; charset=utf-8");
		echo $this->__toString();
		exit();
	}

}