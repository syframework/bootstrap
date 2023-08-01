<?php
namespace Sy\Test\Application;

use PHPUnit\Framework\TestCase;
use Sy\Bootstrap\Application\Sitemap;
use Sy\Bootstrap\Application\Sitemap\SitemapProviderOne;
use Sy\Bootstrap\Application\Sitemap\SitemapProviderTwo;

function minify($code) {
	$search = array("\t", "\r", "\n");
	$code = str_replace($search, ' ', $code);
	$code = preg_replace('/\s+/', ' ', $code);
	return trim($code);
}

class SitemapTest extends TestCase {

	public function testConstruct() {
		$sitemap = new Sitemap();
		$this->assertXmlStringEqualsXmlString(minify('
			<?xml version="1.0" encoding="UTF-8"?>
			<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
			</sitemapindex>
		'), minify(strval($sitemap)));

		$sitemap = new Sitemap();
		$sitemap->addProvider(new SitemapProviderOne());
		$this->assertXmlStringEqualsXmlString(minify('
			<?xml version="1.0" encoding="UTF-8"?>
			<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
			<sitemap>
				<loc>https://sitemap.one</loc>
			</sitemap>
			</sitemapindex>
		'), minify(strval($sitemap)));

		$sitemap = new Sitemap();
		$sitemap->addProvider(new SitemapProviderTwo());
		$this->assertXmlStringEqualsXmlString(minify('
			<?xml version="1.0" encoding="UTF-8"?>
			<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
			<sitemap>
				<loc>https://sitemap.one</loc>
			</sitemap>
			<sitemap>
				<loc>https://sitemap.two</loc>
			</sitemap>
			</sitemapindex>
		'), minify(strval($sitemap)));
	}

	public function testActionOne() {
		$_REQUEST[ACTION_TRIGGER] = 'sitemap-provider-one';
		$sitemap = new Sitemap();
		$sitemap->addProvider(new SitemapProviderOne());
		$this->assertXmlStringEqualsXmlString(minify('
			<?xml version="1.0" encoding="UTF-8"?>
			<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
					xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
					xmlns:video="http://www.google.com/schemas/sitemap-video/1.1"
					xmlns:xhtml="http://www.w3.org/1999/xhtml">
				<url>
					<loc>https://page.one</loc>
				</url>
			</urlset>
		'), minify(strval($sitemap)));
	}

	public function testActionTwo() {
		$_REQUEST[ACTION_TRIGGER] = 'sitemap-provider-two';
		$sitemap = new Sitemap();
		$sitemap->addProvider(new SitemapProviderTwo());
		$this->assertXmlStringEqualsXmlString(minify('
			<?xml version="1.0" encoding="UTF-8"?>
			<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
					xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
					xmlns:video="http://www.google.com/schemas/sitemap-video/1.1"
					xmlns:xhtml="http://www.w3.org/1999/xhtml">
				<url>
					<loc>https://page.one</loc>
				</url>
				<url>
					<loc>https://page.two</loc>
				</url>
			</urlset>
		'), minify(strval($sitemap)));
	}

}

namespace Sy\Bootstrap\Application\Sitemap;

use Sy\Bootstrap\Application\Sitemap\IProvider;

class SitemapProviderOne implements IProvider {

	public function getIndexUrls() {
		return ['https://sitemap.one'];
	}

	public function getUrls() {
		return [['loc' => 'https://page.one']];
	}

}

class SitemapProviderTwo implements IProvider {

	public function getIndexUrls() {
		return [
			'https://sitemap.one',
			'https://sitemap.two',
		];
	}

	public function getUrls() {
		return [
			['loc' => 'https://page.one'],
			['loc' => 'https://page.two'],
		];
	}

}