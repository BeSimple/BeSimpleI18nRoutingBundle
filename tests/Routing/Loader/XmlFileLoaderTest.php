<?php

namespace BeSimple\I18nRoutingBundle\Tests\Routing\Loader;

use BeSimple\I18nRoutingBundle\Routing\Loader\XmlFileLoader;
use BeSimple\I18nRoutingBundle\Routing\RouteNameInflector\RouteNameInflector;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Route;

/**
 * @author Francis Besset <francis.besset@gmail.com>
 */
class XmlFileLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testBasicI18nRoute()
    {
        $inflector = $this->getMock('BeSimple\I18nRoutingBundle\Routing\RouteNameInflector\RouteNameInflector');
        $inflector
            ->expects($this->exactly(3))
            ->method('inflect')
            ->willReturnMap(array(
                array('homepage_locale', 'en', 'english'),
                array('homepage_locale', 'de', 'german'),
                array('homepage_locale', 'fr', 'french')
            ));

        $routes = $this->load('basic_i18n_route.xml', $inflector)->all();

        $this->assertEquals(3, count($routes));

        $this->assertEquals(
            array(
                'english' => new Route('/en/', array(
                    '_locale' => 'en',
                    '_controller' => 'TestBundle:Frontend:homepageLocale'
                )),
                'german' => new Route('/de/', array(
                    '_locale' => 'de',
                    '_controller' => 'TestBundle:Frontend:homepageLocale'
                )),
                'french' => new Route('/fr/', array(
                    '_locale' => 'fr',
                    '_controller' => 'TestBundle:Frontend:homepageLocale'
                )),
            ),
            $routes
        );
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

    private function load($file, RouteNameInflector $routeNameInflector = null)
    {
        $loader = new XmlFileLoader(new FileLocator(array(__DIR__.'/../../Fixtures')), $routeNameInflector);

        return $loader->load($file, 'be_simple_i18n');
    }
}
