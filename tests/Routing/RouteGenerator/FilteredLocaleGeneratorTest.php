<?php
namespace BeSimple\I18nRoutingBundle\Tests\Routing\RouteGenerator;

use BeSimple\I18nRoutingBundle\Routing\RouteGenerator\FilteredLocaleGenerator;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class FilteredLocaleGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\BeSimple\I18nRoutingBundle\Routing\RouteGenerator\RouteGenerator
     */
    private $internalGenerator;
    /**
     * @var FilteredLocaleGenerator
     */
    private $generator;

    public function setUp()
    {
        $this->internalGenerator = $this->getMock('BeSimple\I18nRoutingBundle\Routing\RouteGenerator\RouteGenerator');

        $this->generator = new FilteredLocaleGenerator($this->internalGenerator, array('en', 'nl', 'fr'));
    }

    /**
     * @test
     */
    public function it_requires_at_least_one_locale()
    {
        $this->setExpectedException('InvalidArgumentException');

        new FilteredLocaleGenerator($this->internalGenerator, array());
    }

    /**
     * @test
     * @dataProvider provideUnFilterAndFilteredLocales
     */
    public function when_generating_routes_it_removes_any_unsupported_locales($locales, $filteredLocales)
    {
        $route = new Route('');
        $collection = new RouteCollection();

        $this->internalGenerator
            ->expects($this->once())
            ->method('generateRoutes')
            ->with('test', $filteredLocales, $this->identicalTo($route))
            ->willReturn($collection);

        $this->assertSame(
            $collection,
            $this->generator->generateRoutes('test', $locales, $route)
        );
    }

    /**
     * @test
     * @dataProvider provideUnFilterAndFilteredLocales
     */
    public function when_generating_a_collection_it_removes_any_unsupported_locales($locales, $filteredLocales)
    {
        $originalCollection = new RouteCollection();

        $collection = new RouteCollection();

        $this->internalGenerator
            ->expects($this->once())
            ->method('generateCollection')
            ->with($filteredLocales, $this->identicalTo($originalCollection))
            ->willReturn($collection);

        $this->assertSame(
            $collection,
            $this->generator->generateCollection($locales, $originalCollection)
        );
    }

    public function provideUnFilterAndFilteredLocales()
    {
        return array(
            array(
                array(
                    'en' => 'test',
                    'nl' => 'testen',
                    'de' => 'probe',
                    'fr' => 'examine',
                    'pl' => 'badanie'
                ),
                array(
                    'en' => 'test',
                    'nl' => 'testen',
                    'fr' => 'examine'
                )
            ),
            array(
                array(
                    'nl' => 'testen',
                    'de' => 'probe',
                    'pl' => 'badanie'
                ),
                array(
                    'nl' => 'testen',
                )
            ),
            array(
                array(
                    'en' => 'test',
                    'nl' => 'testen',
                    'fr' => 'examine'
                ),
                array(
                    'en' => 'test',
                    'nl' => 'testen',
                    'fr' => 'examine'
                )
            )
        );
    }
}
