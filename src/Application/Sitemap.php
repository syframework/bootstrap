<?php
namespace Sy\Bootstrap\Application;

use Sy\Bootstrap\Lib\Str;

class Sitemap extends \Sy\Component\WebComponent {

	private $providers;

	public function __construct() {
		parent::__construct();
		$this->providers = [];

		$this->mount(function () {
			$method = $this->request(ACTION_TRIGGER);
			$this->actionDispatch(ACTION_TRIGGER, $method ?? 'index');
			if (!is_null($method)) $this->$method();
			header('Content-Type: application/xml; charset=utf-8');
		});
	}

	/**
	 * Add a sitemap provider
	 *
	 * @param \Sy\Bootstrap\Application\Sitemap\IProvider $provider Sitemap urls provider
	 */
	public function addProvider(\Sy\Bootstrap\Application\Sitemap\IProvider $provider) {
		$this->providers[md5(get_class($provider))] = $provider;
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
	 * Return the full name of a provider class
	 *
	 * @param  string $name
	 * @return string|null
	 */
	protected function providerClass($name) {
		$class = get_class($this);
		$namespace = substr_replace($class, '', strrpos($class, '\\'));
		$class = $namespace . '\\Sitemap\\' . ucfirst(Str::snakeToCaml($name));
		if (class_exists($class)) return $class;
		$class = __NAMESPACE__ . '\\Sitemap\\' . ucfirst(Str::snakeToCaml($name));
		if (class_exists($class)) return $class;
		return null;
	}

	/**
	 * Generate a simple sitemap using its name
	 *
	 * @param string $name
	 * @param array $arguments
	 */
	public function __call($name, $arguments) {
		$class = $this->providerClass($name);

		if (is_null($class) or !isset($this->providers[md5($class)])) {
			http_response_code(404);
			return;
		}

		$this->setTemplateFile(__DIR__ . '/Sitemap/Sitemap.xml');

		foreach ($this->providers[md5($class)]->getUrls() as $url) {
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