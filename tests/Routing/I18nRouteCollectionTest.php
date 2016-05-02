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

    public function testAddI18nRoute()
    {
        $collection = new I18nRouteCollection();
        $collection->addI18n('test', array('en' => '/en/', 'nl' => '/nl/'), new Route(''));

        $this->assertEquals(
            array(
                'test.en' => new Route('/en/', array('_locale' => 'en')),
                'test.nl' => new Route('/nl/', array('_locale' => 'nl')),
            ),
            $collection->all()
        );
    }

    public function testAddI18nUsesNameInflector()
    {
        $inflector = $this->getMock('BeSimple\I18nRoutingBundle\Routing\RouteNameInflector\RouteNameInflector');
        $inflector
            ->expects($this->exactly(2))
            ->method('inflect')
            ->willReturnMap(array(
                array('test', 'en', 'english'),
                array('test', 'nl', 'dutch')
            ));

        $collection = new I18nRouteCollection($inflector);
        $collection->addI18n('test', array('en' => '/en/', 'nl' => '/nl/'), new Route(''));

        $this->assertEquals(
            array(
                'english' => new Route('/en/', array('_locale' => 'en')),
                'dutch' => new Route('/nl/', array('_locale' => 'nl')),
            ),
            $collection->all()
        );
    }

    public function testAddPrefixWillUseNameInflector()
    {
        $inflector = $this->getMock('BeSimple\I18nRoutingBundle\Routing\RouteNameInflector\RouteNameInflector');
        $inflector
            ->expects($this->exactly(2))
            ->method('inflect')
            ->willReturnMap(array(
                array('test', 'en', 'english'),
                array('test', 'nl', 'dutch')
            ));

        $collection = new I18nRouteCollection($inflector);
        $collection->add('test', new Route('/test'));
        $collection->addPrefix(array('en' => '/en/', 'nl' => '/nl/'));

        $this->assertEquals(
            array(
                'english' => new Route('/en/test', array('_locale' => 'en')),
                'dutch' => new Route('/nl/test', array('_locale' => 'nl')),
            ),
            $collection->all()
        );
    }
}
