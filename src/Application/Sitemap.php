<?php
namespace Sy\Bootstrap\Application;

class Sitemap extends \Sy\Bootstrap\Component\Sitemap {

	private $plugins = array('Place', 'Article');

	public function __construct() {
		parent::__construct();

		// Check if a plugin sitemap class exists
		$class = 'Sy\\Bootstrap\\Application\\Sitemap\\' . ucfirst(ACTION_TRIGGER);
		if (class_exists($class)) {
			$sitemap = new $class();
			$sitemap->init();
		}

		$this->actionDispatch(ACTION_TRIGGER, 'index');
	}

	public function indexAction() {
		$this->setTemplateFile(__DIR__ . '/Sitemap/Index.xml');

		foreach ($this->plugins as $plugin) {
			$class = 'Sy\\Bootstrap\\Application\\Sitemap\\' . $plugin;
			if (class_exists($class)) {
				$sitemap = new $class();
				$urls = $sitemap->index();

				foreach ($urls as $url) {
					$this->setVar('URL', $url);
					$this->setBlock('SITEMAP_BLOCK');
				}
			}
		}

		// pages with alias
		$this->setVar('URL', PROJECT_URL . \Sy\Bootstrap\Lib\Url::build('sitemap', 'page'));
		$this->setBlock('SITEMAP_BLOCK');

		$this->out();
	}

	public function pageAction() {
		$this->setTemplateFile(__DIR__ . '/Sitemap/Sitemap.xml');
		$service = \Sy\Bootstrap\Service\Container::getInstance();

		// Page
		$service->page->foreachRow(function($row) {
			$loc = \Sy\Bootstrap\Lib\Url\AliasManager::retrieveAlias('page/' . $row['id'], $row['lang']);
			if (is_null($loc)) return;

			$date = new \Sy\Bootstrap\Lib\Date($row['updated_at']);
			$this->setVars([
				'LOC'  => PROJECT_URL . '/' . $loc,
			]);
			$alt = json_decode($row['alternate'], true);
			if (count($alt) > 1) {
				foreach ($alt as $lang) {
					$this->setVars([
						'LANG' => $lang,
						'HREF' => PROJECT_URL . '/' . \Sy\Bootstrap\Lib\Url\AliasManager::retrieveAlias('page/' . $row['id'], $lang),
					]);
					$this->setBlock('ALT_BLOCK');
				}
			}
			$this->setBlock('URL_BLOCK');
		}, [
			'SELECT'   => "t_page.*, CONCAT('[', GROUP_CONCAT(CONCAT('\"', b.lang, '\"')), ']') AS 'alternate'",
			'JOIN'     => 'LEFT JOIN t_page b ON t_page.id = b.id',
			'GROUP BY' => 't_page.id, t_page.lang'
		]);

		$this->out();
	}

}