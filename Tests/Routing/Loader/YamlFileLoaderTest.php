<?php

namespace BeSimple\I18nRoutingBundle\Tests\Routing\Loader;

use BeSimple\I18nRoutingBundle\Routing\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

/**
 * @author Francis Besset <francis.besset@gmail.com>
 */
class YamlFileLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testBasicI18nRoute()
    {
        $routes = $this->load('basic_i18n_route.yml')->all();

        $this->assertEquals(3, count($routes));
    }

    public function testBasicRoutes()
    {
        $routes = $this->load('basic_routes.yml')->all();

        $this->assertEquals(4, count($routes));
    }

    public function testFullLocale()
    {
        $routes = $this->load('full_locale.yml')->all();

        $this->assertEquals(3, count($routes));
    }

    public function testImport()
    {
        $routes = $this->load('import.yml')->all();

        $this->assertEquals(7, count($routes));
    }

    public function testImportPrefix()
    {
        $routes = $this->load('import_prefix.yml')->all();

        $this->assertEquals(7, count($routes));
    }

    private function load($file)
    {
        $loader = new YamlFileLoader(new FileLocator(array(__DIR__.'/../../Fixtures')));

        return $loader->load($file);
    }
}