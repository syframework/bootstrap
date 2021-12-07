<?php
namespace Sy\Bootstrap\Application;

class Sitemap extends \Sy\Component\WebComponent {

	private $providers;

	public function __construct() {
		parent::__construct();
		$this->providers = [];
	}

	public function __toString() {
		$method = $this->get(ACTION_TRIGGER, 'index') . 'Action';
		$this->$method();
		return parent::__toString();
	}

	/**
	 * @param string $name Sitemap name
	 * @param \Sy\Bootstrap\Application\Sitemap\IProvider $provider Sitemap urls provider
	 */
	public function addProvider(string $name, \Sy\Bootstrap\Application\Sitemap\IProvider $provider) {
		$this->providers[$name] = $provider;
	}

	/**
	 * Generate the sitemap index
	 */
	public function indexAction() {
		$this->setTemplateFile(__DIR__ . '/Sitemap/Index.xml');

		foreach ($this->providers as $provider) {
			foreach ($provider->getIndexUrls() as $url) {
				$this->setVar('URL', $url);
				$this->setBlock('SITEMAP_BLOCK');
			}
		}
	}

	/**
	 * Generate a simple sitemap using its name
	 *
	 * @param string $name
	 * @param array $arguments
	 */
	public function __call($name, $arguments) {
		if (!str_ends_with($name, 'Action')) return;
		$name = substr_replace($name, '', -6);
		if (!isset($this->providers[$name])) {
			http_response_code(404);
			return;
		}

		$this->setTemplateFile(__DIR__ . '/Sitemap/Sitemap.xml');

		foreach ($this->providers[$name]->getUrls() as $url) {
			$this->setVar('LOC', $url['loc']);

			if (isset($url['alternate'])) {
				foreach ($url['alternate'] as $lang => $href) {
					$this->setVars([
						'LANG' => $lang,
						'HREF' => $href,
					]);
					$this->setBlock('ALT_BLOCK');
				}
			}

			if (isset($url['lastmod'])) {
				$this->setVar('LAST', $url['lastmod']);
				$this->setBlock('LAST_BLOCK');
			}

			$this->setBlock('URL_BLOCK');
		}
	}

}