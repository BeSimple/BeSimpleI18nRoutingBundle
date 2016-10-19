<?php
namespace BeSimple\I18nRoutingBundle\Tests\Routing\RouteGenerator\NameInflector;


use BeSimple\I18nRoutingBundle\Routing\RouteGenerator\NameInflector\PostfixInflector;

class PostfixInflectorTest extends \PHPUnit_Framework_TestCase
{
    public function testInflect()
    {
        $inflector = new PostfixInflector();

        $this->assertSame(
            'route.name.be-simple-i18n.en',
            $inflector->inflect('route.name', 'en')
        );
        $this->assertSame(
            'route.name.be-simple-i18n.nl',
            $inflector->inflect('route.name', 'nl')
        );
    }

    public function testUnInflect()
    {
        $inflector = new PostfixInflector();
        $this->assertSame('test', $inflector->unInflect('test' . $inflector::INFIX . 'nl', $locale = 'nl'));
    }

    public function testisBeSimpleRoute()
    {
        $inflector = new PostfixInflector();
        $this->assertFalse($inflector->isBeSimpleRoute('test', 'nl'), "'test' is not at BeSimple route");
        $this->assertTrue($inflector->isBeSimpleRoute('test' . PostfixInflector::INFIX . 'nl', 'nl'), 'Should have been a BeSimple route.');
    }

    public function testIsValidMatch()
    {
        $inflector = new PostfixInflector();
        $this->assertTrue($inflector->isValidMatch('test' . PostfixInflector::INFIX . 'nl', 'nl'));
        $this->assertFalse($inflector->isValidMatch('test' . PostfixInflector::INFIX . 'nl', 'en'));

        // see if it works with more than one locale in a collection, all with the same path spec
        $routeMock = $this->getMockBuilder('Symfony\Component\Routing\Route')
            ->disableOriginalConstructor()->getMock();
        $routeMock->expects($this->any())->method('getPath')->willReturn('/{_locale}/sites');

        $routes = [
            $inflector->inflect('sites', 'nl') => $routeMock,
            $inflector->inflect('sites', 'de') => $routeMock,
            $inflector->inflect('sites', 'se') => $routeMock,
            $inflector->inflect('sites', 'fr') => $routeMock,
            $inflector->inflect('sites', 'es') => $routeMock,
        ];

        $routeCollection = $this->getMock('Symfony\Component\Routing\RouteCollection');
        $routeCollection->expects($this->any())->method('getIterator')->willReturn(new \ArrayIterator($routes));

        $this->assertTrue($inflector->isValidMatch('sites' . PostfixInflector::INFIX . 'nl', 'nl'));
        $this->assertTrue($inflector->isValidMatch('sites' . PostfixInflector::INFIX . 'de', 'de'));
        $this->assertTrue($inflector->isValidMatch('sites' . PostfixInflector::INFIX . 'se', 'se'));
        $this->assertTrue($inflector->isValidMatch('sites' . PostfixInflector::INFIX . 'fr', 'fr'));
        $this->assertTrue($inflector->isValidMatch('sites' . PostfixInflector::INFIX . 'es', 'es'));

        $this->assertFalse($inflector->isValidMatch('sites' . PostfixInflector::INFIX . 'es', 'nl'));
        $this->assertFalse($inflector->isValidMatch('sites' . PostfixInflector::INFIX . 'de', 'nl'));
        $this->assertFalse($inflector->isValidMatch('sites' . PostfixInflector::INFIX . 'fr', 'nl'));
        $this->assertFalse($inflector->isValidMatch('sites' . PostfixInflector::INFIX . 'se', 'nl'));
    }
}
