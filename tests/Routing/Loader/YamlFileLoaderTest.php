<?php

namespace BeSimple\I18nRoutingBundle\Tests\Routing\Loader;

use BeSimple\I18nRoutingBundle\Routing\Loader\YamlFileLoader;
use BeSimple\I18nRoutingBundle\Routing\RouteNameInflector\RouteNameInflector;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Route;

/**
 * @author Francis Besset <francis.besset@gmail.com>
 */
class YamlFileLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testSupports()
    {
        $loader = $this->getYamlFileLoader();

        $this->assertTrue($loader->supports('foo.yml', 'be_simple_i18n'));
        $this->assertTrue($loader->supports('foo.yaml', 'be_simple_i18n'));
        $this->assertTrue($loader->supports('foo.bar.yml', 'be_simple_i18n'));

        $this->assertFalse($loader->supports('foo.yml'));
        $this->assertFalse($loader->supports('foo.yml', 'yaml'));
        $this->assertFalse($loader->supports('foo.xml', 'be_simple_i18n'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider getPathsToInvalidFiles()
     */
    public function testLoadThrowsInvalidArgumentExceptionWithInvalidFile($filePath)
    {
        $this->load($filePath);
    }

    public function getPathsToInvalidFiles()
    {
        return array(
            array('nonvalid_array.yml'),
            array('nonvalid_extrakeys.yml'),
            array('nonvalid_type_without_resource.yml'),
            array('nonvalid_without_resource_and_locales.yml'),
        );
    }

    /**
     * @expectedException \Symfony\Component\Config\Exception\FileLoaderLoadException
     * @dataProvider getPathsToInvalidImportFiles()
     */
    public function testLoadThrowsFileLoaderLoadExceptionWithInvalidFile($filePath)
    {
        $this->load($filePath);
    }

    public function getPathsToInvalidImportFiles()
    {
        return array(array('nonvalid_resource_with_locales.yml'),);
    }

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

        $routes = $this->load('basic_i18n_route.yml', $inflector)->all();

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

        $this->assertEquals(6, count($routes));
    }

    public function testImportPrefix()
    {
        $routes = $this->load('import_prefix.yml')->all();

        $this->assertEquals(6, count($routes));
    }

    public function testImportPrefixLocalized()
    {
        $routes = $this->load('import_prefix_locale.yml')->all();

        $this->assertEquals(6, count($routes));
    }

    private function load($file, RouteNameInflector $routeNameInflector = null)
    {
        return $this
            ->getYamlFileLoader($routeNameInflector)
            ->load($file, 'be_simple_i18n')
        ;
    }

    private function getYamlFileLoader(RouteNameInflector $routeNameInflector = null)
    {
        return new YamlFileLoader(new FileLocator(array(__DIR__.'/../../Fixtures')), $routeNameInflector);
    }
}
