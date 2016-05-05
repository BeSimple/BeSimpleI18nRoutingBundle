<?php
namespace BeSimple\I18nRoutingBundle\Tests\Routing\RouteGenerator;

use BeSimple\I18nRoutingBundle\Routing\RouteGenerator\StrictLocaleRouteGenerator;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class StrictLocaleRouteGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\BeSimple\I18nRoutingBundle\Routing\RouteGenerator\RouteGenerator
     */
    private $internalGenerator;
    /**
     * @var StrictLocaleRouteGenerator
     */
    private $generator;

    public function setUp()
    {
        $this->internalGenerator = $this->getMock('BeSimple\I18nRoutingBundle\Routing\RouteGenerator\RouteGenerator');

        $this->generator = new StrictLocaleRouteGenerator($this->internalGenerator, array('en', 'nl', 'fr'));
    }

    /**
     * @test
     */
    public function it_requires_at_least_one_locale()
    {
        $this->setExpectedException('InvalidArgumentException');

        new StrictLocaleRouteGenerator($this->internalGenerator, array());
    }

    /**
     * @test
     */
    public function when_generating_routes_with_all_locales_no_exception_is_triggered()
    {
        $locales = array(
            'en' => 'test',
            'nl' => 'testen',
            'fr' => 'examine'
        );

        $route = new Route('');
        $collection = new RouteCollection();

        $this->internalGenerator
            ->expects($this->once())
            ->method('generateRoutes')
            ->with('test', $locales, $this->identicalTo($route))
            ->willReturn($collection);

        $this->assertSame(
            $collection,
            $this->generator->generateRoutes('test', $locales, $route)
        );
    }

    /**
     * @test
     * @dataProvider provideMissingLocales
     */
    public function when_generating_routes_where_locales_a_missing_then_a_exception_is_triggered($locales)
    {
        $this->internalGenerator
            ->expects($this->never())
            ->method('generateRoutes');

        $this->setExpectedException('BeSimple\I18nRoutingBundle\Routing\Exception\MissingLocaleException');

        $this->generator->generateRoutes('test', $locales, new Route(''));
    }

    /**
     * @test
     * @dataProvider provideUnknownLocales
     */
    public function when_generating_routes_where_locales_a_unknown_then_a_exception_is_triggered($locales, $useFallback = false)
    {
        $this->internalGenerator
            ->expects($this->never())
            ->method('generateRoutes');

        if ($useFallback) {
            $this->generator->allowFallback();
        }

        $this->setExpectedException('BeSimple\I18nRoutingBundle\Routing\Exception\UnknownLocaleException');

        $this->generator->generateRoutes('test', $locales, new Route(''));
    }

    /**
     * @test
     */
    public function when_generating_a_collection_with_all_locales_no_exception_is_triggered()
    {
        $locales = array(
            'en' => 'test',
            'nl' => 'testen',
            'fr' => 'examine'
        );

        $originalCollection = new RouteCollection();
        $collection = new RouteCollection();

        $this->internalGenerator
            ->expects($this->once())
            ->method('generateCollection')
            ->with($locales, $this->identicalTo($originalCollection))
            ->willReturn($collection);

        $this->assertSame(
            $collection,
            $this->generator->generateCollection($locales, $originalCollection)
        );
    }

    /**
     * @test
     * @dataProvider provideMissingLocales
     */
    public function when_generating_a_collection_where_locales_a_missing_then_a_exception_is_triggered($locales)
    {
        $this->internalGenerator
            ->expects($this->never())
            ->method('generateCollection');

        $this->setExpectedException('BeSimple\I18nRoutingBundle\Routing\Exception\MissingLocaleException');

        $this->generator->generateCollection($locales, new RouteCollection());
    }

    /**
     * @test
     * @dataProvider provideUnknownLocales
     */
    public function when_generating_a_collection_where_locales_a_unknown_then_a_exception_is_triggered($locales, $useFallback = false)
    {
        $this->internalGenerator
            ->expects($this->never())
            ->method('generateCollection');

        if ($useFallback) {
            $this->generator->allowFallback();
        }

        $this->setExpectedException('BeSimple\I18nRoutingBundle\Routing\Exception\UnknownLocaleException');

        $this->generator->generateCollection($locales, new RouteCollection());
    }

    public function provideMissingLocales()
    {
        return array(
            array(
                array()
            ),
            array(
                array(
                    'en' => 'test',
                    'nl' => 'testen',
                )
            ),
            array(
                array(
                    'nl' => 'testen',
                    'fr' => 'examine',
                )
            )
        );
    }

    public function provideUnknownLocales()
    {
        return array(
            array(
                array(
                    'en' => 'test',
                    'nl' => 'testen',
                    'fr' => 'examine',

                    'de' => 'probe',
                )
            ),
            array(
                array(
                    'en' => 'test',
                    'nl' => 'testen',
                    'fr' => 'examine',

                    'de' => 'probe',
                    'pl' => 'badanie',
                )
            ),
            array(
                array(
                    'en' => 'test',

                    'pl' => 'badanie',
                ),
                true
            )
        );
    }
}
