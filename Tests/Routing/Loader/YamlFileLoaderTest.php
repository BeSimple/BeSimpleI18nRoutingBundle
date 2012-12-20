<?php

namespace BeSimple\I18nRoutingBundle\Tests\Routing\Loader;

use BeSimple\I18nRoutingBundle\Routing\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

/**
 * @author Francis Besset <francis.besset@gmail.com>
 */
class YamlFileLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testSupports()
    {
        $loader = $this->getYamlFileLoader();

        $this->assertTrue($loader->supports('foo.yml', 'be_simple_i18n'));
        $this->assertTrue($loader->supports('foo.bar.yml', 'be_simple_i18n'));

        $this->assertFalse($loader->supports('foo.yml'));
        $this->assertFalse($loader->supports('foo.yml', 'yaml'));
        $this->assertFalse($loader->supports('foo.xml', 'be_simple_i18n'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider getPathsToInvalidFiles()
     */
    public function testLoadThrowsExceptionWithInvalidFile($filePath)
    {
        $this->load($filePath);
    }

    public function getPathsToInvalidFiles()
    {
        return array(array('nonvalid_array.yml'), array('nonvalid_extrakeys.yml'), array('nonvalid_resource_with_locales.yml'), array('nonvalid_type_without_resource.yml'), array('nonvalid_without_resource_and_locales.yml'), array('nonvalid_basic_routes.yml'));
    }

    public function testBasicI18nRoute()
    {
        $routes = $this->load('basic_i18n_route.yml')->all();

        $this->assertEquals(3, count($routes));
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

    private function load($file)
    {
        return $this
            ->getYamlFileLoader()
            ->load($file)
        ;
    }

    private function getYamlFileLoader()
    {
        return new YamlFileLoader(new FileLocator(array(__DIR__.'/../../Fixtures')));
    }
}
