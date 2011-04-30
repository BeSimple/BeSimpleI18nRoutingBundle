<?php

namespace BeSimple\I18nRoutingBundle\Tests\Routing\Generator;

use BeSimple\I18nRoutingBundle\Routing\Generator\UrlGenerator;
use BeSimple\I18nRoutingBundle\Routing\I18nRoute;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;

/**
 * @author Francis Besset <francis.besset@gmail.com>
 */
class UrlGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $generator = $this->getGenerator();

        try {
            $generator->generateI18n('test', 'fr', array());

            $this->fail('An expected exception has not been raised.');
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('I18nRoute "test" (fr) does not exist.', $e->getMessage());
        }
    }

    /**
     * @dataProvider provideI18nRouteData
     */
    public function testGenerate($name, $locales)
    {
        $route = $this->getRoute($name, $locales);
        $generator = $this->getGenerator($route->getCollection());

        foreach ($locales as $locale => $pattern) {
            $this->assertEquals($pattern, $generator->generateI18n($name, $locale));
        }
    }

    public function provideI18nRouteData()
    {
        return array(
            array(
                'test',
                array(
                    'en' => '/welcome',
                    'fr' => '/bienvenue',
                    'de' => '/willkommen',
                ),
            ),

            array(
                'test_bis',
                array(
                    'en_GB' => '/welcome',
                    'fr_FR' => '/bienvenue',
                    'de_DE' => '/willkommen',
                ),
            ),
        );
    }

    private function getGenerator(RouteCollection $collection = null)
    {
        $collection = $collection ?: $this->getCollection();

        return new UrlGenerator($collection, new RequestContext());
    }

    private function getCollection()
    {
        return new RouteCollection();
    }

    public function getRoute($name, $locales)
    {
        return new I18nRoute($name, $locales);
    }
}