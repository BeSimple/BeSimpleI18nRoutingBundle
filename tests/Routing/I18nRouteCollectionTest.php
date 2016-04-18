<?php

namespace BeSimple\I18nRoutingBundle\Tests\Routing;

use BeSimple\I18nRoutingBundle\Routing\I18nRouteCollection;
use Symfony\Component\Routing\Route;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class I18nRouteCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testCollectionLocaleReplace()
    {
        $collection = new I18nRouteCollection();
        $collection->add('test.en', new Route('/testing', array('_locale' => 'en')));
        $collection->add('test.nl', new Route('/testen', array('_locale' => 'nl')));

        $collection->addPrefix('/{_locale}/');

        $this->assertCount(2, $collection, '(count)');

        $enRoute = $collection->get('test.en');
        $this->assertNotNull($enRoute, '(en.missing)');
        $this->assertEquals('/en/testing', $enRoute->getPath(), '(en.path)');
        $this->assertEquals('en', $enRoute->getDefault('_locale'), '(en._locale)');

        $nlRoute = $collection->get('test.nl');
        $this->assertNotNull($nlRoute, '(nl.missing)');
        $this->assertEquals('/nl/testen', $nlRoute->getPath(), '(nl.path)');
        $this->assertEquals('nl', $nlRoute->getDefault('_locale'), '(nl._locale)');
    }

    public function testCollectionLocalizeRoutes()
    {
        $collection = new I18nRouteCollection();
        $collection->add('test', new Route('/test'));
        $collection->addPrefix(array('en' => '/en/', 'nl' => '/nl/'));

        $this->assertCount(2, $collection, '(count)');

        $enRoute = $collection->get('test.en');
        $this->assertNotNull($enRoute, '(en.missing)');
        $this->assertInstanceOf('\Symfony\Component\Routing\Route', $enRoute, '(en.instanceOf)');
        $this->assertEquals('/en/test', $enRoute->getPath(), '(en.path)');
        $this->assertEquals('en', $enRoute->getDefault('_locale'), '(en._locale)');

        $nlRoute = $collection->get('test.nl');
        $this->assertNotNull($nlRoute, '(nl.missing)');
        $this->assertInstanceOf('\Symfony\Component\Routing\Route', $nlRoute, '(nl.instanceOf)');
        $this->assertEquals('/nl/test', $nlRoute->getPath(), '(nl.path)');
        $this->assertEquals('nl', $nlRoute->getDefault('_locale'), '(nl._locale)');
    }
}
