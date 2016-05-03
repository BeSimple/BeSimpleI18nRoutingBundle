<?php
namespace BeSimple\I18nRoutingBundle\Tests\Routing\RouteGenerator;

use BeSimple\I18nRoutingBundle\Routing\RouteGenerator\I18nRouteGenerator;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class I18nRouteGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerateRoutes()
    {
        $generator = new I18nRouteGenerator();
        $collection = $generator->generateRoutes('test', array('en' => '/en/', 'nl' => '/nl/'), new Route(''));

        $this->assertEquals(
            array(
                'test.en' => new Route('/en/', array('_locale' => 'en')),
                'test.nl' => new Route('/nl/', array('_locale' => 'nl')),
            ),
            $collection->all()
        );
    }

    public function testGenerateRoutesUsesNameInflector()
    {
        $inflector = $this->getMock('BeSimple\I18nRoutingBundle\Routing\RouteGenerator\NameInflector\RouteNameInflector');
        $inflector
            ->expects($this->exactly(2))
            ->method('inflect')
            ->willReturnMap(array(
                array('test', 'en', 'english'),
                array('test', 'nl', 'dutch')
            ));

        $generator = new I18nRouteGenerator($inflector);
        $collection = $generator->generateRoutes('test', array('en' => '/en/', 'nl' => '/nl/'), new Route(''));

        $this->assertEquals(
            array(
                'english' => new Route('/en/', array('_locale' => 'en')),
                'dutch' => new Route('/nl/', array('_locale' => 'nl')),
            ),
            $collection->all()
        );
    }

    public function testGenerateCollectionNormalPrefix()
    {
        $collection = new RouteCollection();
        $collection->add('test', new Route('/test'));
        $collection->add('probe', new Route('/probe', array('_locale' => 'de')));

        $generator = new I18nRouteGenerator();
        $localizedCollection = $generator->generateCollection(
            '/prefix',
            $collection
        );

        $this->assertEquals(
            array(
                'test' => new Route('/prefix/test'),
                'probe' => new Route('/prefix/probe', array('_locale' => 'de'))
            ),
            $localizedCollection->all()
        );
    }

    public function testGenerateCollectionLocalizeRoutes()
    {
        $collection = new RouteCollection();
        $collection->add('test', new Route('/test'));
        $collection->add('test_localized', new Route('/testing', array('_locale' => 'en')));

        $generator = new I18nRouteGenerator();
        $localizedCollection = $generator->generateCollection(
            array('en' => '/en/', 'nl' => '/nl/'),
            $collection
        );

        $this->assertEquals(
            array(
                'test.en' => new Route('/en/test', array('_locale' => 'en')),
                'test.nl' => new Route('/nl/test', array('_locale' => 'nl')),
                'test_localized' => new Route('/en/testing', array('_locale' => 'en')),
            ),
            $localizedCollection->all()
        );
    }

    public function testGenerateCollectionLocalizeRoutesWithMissingPrefixLocale()
    {
        $collection = new RouteCollection();
        $collection->add('test', new Route('/probe', array('_locale' => 'de')));

        $generator = new I18nRouteGenerator();

        $this->setExpectedException('BeSimple\I18nRoutingBundle\Routing\Exception\MissingRouteLocaleException');

        $generator->generateCollection(
            array('en' => '/en/'),
            $collection
        );
    }

    public function testGenerateCollectionLocaleReplace()
    {
        $collection = new RouteCollection();
        $collection->add('simple', new Route('/simple'));
        $collection->add('test.en', new Route('/testing', array('_locale' => 'en')));
        $collection->add('test.nl', new Route('/testen', array('_locale' => 'nl')));

        $generator = new I18nRouteGenerator();
        $localizedCollection = $generator->generateCollection(
            '/{_locale}/',
            $collection
        );

        $this->assertEquals(
            array(
                'simple' => new Route('/{_locale}/simple'),
                'test.en' => new Route('/en/testing', array('_locale' => 'en')),
                'test.nl' => new Route('/nl/testen', array('_locale' => 'nl')),
            ),
            $localizedCollection->all()
        );
    }
}
