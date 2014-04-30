<?php

namespace BeSimple\I18nRoutingBundle\Tests\Routing\Loader;

use BeSimple\I18nRoutingBundle\Routing\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

/**
 * @author Francis Besset <francis.besset@gmail.com>
 */
class XmlFileLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testBasicI18nRoute()
    {
        $routes = $this->load('basic_i18n_route.xml')->all();

        $this->assertEquals(3, count($routes));
    }

    public function testBasicRoutes()
    {
        $routes = $this->load('basic_routes.xml')->all();

        $this->assertEquals(4, count($routes));
    }

    public function testFullLocale()
    {
        $routes = $this->load('full_locale.xml')->all();

        $this->assertEquals(3, count($routes));
    }

    public function testImport()
    {
        $routes = $this->load('import.xml')->all();

        $this->assertEquals(7, count($routes));
    }

    public function testImportPrefix()
    {
        $routes = $this->load('import_prefix.xml')->all();

        $this->assertEquals(7, count($routes));
    }

    public function testImportPrefixLocalized()
    {
        $routes = $this->load('import_prefix_locale.xml')->all();

        $this->assertEquals(6, count($routes));
    }

    private function load($file)
    {
        $loader = new XmlFileLoader(new FileLocator(array(__DIR__.'/../../Fixtures')));

        return $loader->load($file, 'be_simple_i18n');
    }
}