<?php

namespace BeSimple\I18nRoutingBundle\Routing\Generator;

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

    public function testGenerate()
    {
        $route = $this->getRoute('test', array(
            'en' => '/welcome',
            'fr' => '/bienvenue',
            'de' => '/willkommen',
        ));

        $generator = $this->getGenerator($route->getCollection());
        $this->assertEquals('/welcome', $generator->generateI18n('test', 'en'));
        $this->assertEquals('/bienvenue', $generator->generateI18n('test', 'fr'));
        $this->assertEquals('/willkommen', $generator->generateI18n('test', 'de'));
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