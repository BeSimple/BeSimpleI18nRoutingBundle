<?php

namespace BeSimple\I18nRoutingBundle\Tests\Routing;

use BeSimple\I18nRoutingBundle\Routing\Router;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RequestContext;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    public function testMatchLocaleRoute()
    {
        $parentRouter = $this->mockParentRouter();
        $parentRouter
            ->expects($this->at(0))
            ->method('match')
            ->with($this->equalTo('/foo'))
            ->will($this->returnValue(array('_route' => 'test.en', '_locale' => 'en')))
        ;
        $parentRouter
            ->expects($this->at(1))
            ->method('match')
            ->with($this->equalTo('/bar'))
            ->will($this->returnValue(array('_route' => 'test.de', '_locale' => 'de')))
        ;

        $router = new Router($parentRouter);

        $data = $router->match('/foo');
        $this->assertEquals('en', $data['_locale']);
        $this->assertEquals('test', $data['_route']);

        $data = $router->match('/bar');
        $this->assertEquals('de', $data['_locale']);
        $this->assertEquals('test', $data['_route']);
    }

    public function testMatchTranslateStringField()
    {
        $parentRouter = $this->mockParentRouter();
        $parentRouter->expects($this->any())
            ->method('match')
            ->with($this->equalTo('/foo/beberlei'))
            ->will($this->returnValue(array('_route' => 'test.en', '_locale' => 'en', '_translate' => 'name', 'name' => 'beberlei')))
        ;
        $translator = $this->getMock('BeSimple\I18nRoutingBundle\Routing\Translator\AttributeTranslatorInterface');
        $translator
            ->expects($this->once())
            ->method('translate')
            ->with($this->equalTo('test'), $this->equalTo('en'), $this->equalTo('name'), $this->equalTo('beberlei'))
            ->will($this->returnValue('Benjamin'))
        ;
        $router = new Router($parentRouter, $translator);

        $data = $router->match('/foo/beberlei');
        $this->assertEquals('en', $data['_locale']);
        $this->assertEquals('test', $data['_route']);
        $this->assertEquals('Benjamin', $data['name']);
    }

    public function testGenerateI18n()
    {
        $parentRouter = $this->mockParentRouter();
        $parentRouter->expects($this->once())
            ->method('generate')
            ->with($this->equalTo('test_route.en'), $this->equalTo(array('foo' => 'bar')), $this->equalTo(false))
        ;
        $router = new Router($parentRouter);

        $router->generate('test_route', array('foo' => 'bar', 'locale' => 'en'), false);
    }

    public function testGenerateDefault()
    {
        $parentRouter = $this->mockParentRouter();
        $parentRouter->expects($this->once())
            ->method('generate')
            ->with($this->equalTo('test_route'), $this->equalTo(array('foo' => 'bar')), $this->equalTo(false))
        ;
        $router = new Router($parentRouter);

        $router->generate('test_route', array('foo' => 'bar'), false);
    }

    public function testGenerateDefaultLocaleFallback()
    {
        $parentRouter = $this->mockParentRouter();
        $parentRouter
            ->expects($this->exactly(2))
            ->method('generate')
            ->withConsecutive(
                array('test_route', array('foo' => 'bar'), false),
                array('engish_route', array('foo' => 'bar'), false)
            )
            ->willReturnOnConsecutiveCalls(
                $this->throwException(new RouteNotFoundException()),
                '/english_route'
            );
        ;

        $routeNameInflector = $this->getMock('BeSimple\I18nRoutingBundle\Routing\RouteGenerator\NameInflector\RouteNameInflector');
        $routeNameInflector
            ->expects($this->once())
            ->method('inflect')
            ->with('test_route', 'en')
            ->willReturn('engish_route');

        $context = $this->getMockBuilder('Symfony\Component\Routing\RequestContext')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $context->expects($this->once())
            ->method('hasParameter')
            ->with($this->equalTo('_locale'))
            ->will($this->returnValue(false))
        ;
        $parentRouter->expects($this->any())->method('getContext')->willReturn($context);

        $router = new Router($parentRouter, null, 'en', $routeNameInflector);

        $this->assertEquals(
            '/english_route',
            $router->generate('test_route', array('foo' => 'bar'), false)
        );
    }

    public function testGenerateI18nTranslated()
    {
        $parentRouter = $this->mockParentRouter();
        $parentRouter->expects($this->once())
            ->method('generate')
            ->with($this->equalTo('test_route.en'), $this->equalTo(array('foo' => 'baz')), $this->equalTo(false))
        ;
        $translator = $this->getMock('BeSimple\I18nRoutingBundle\Routing\Translator\AttributeTranslatorInterface');
        $translator
            ->expects($this->once())
            ->method('reverseTranslate')
            ->with($this->equalTo('test_route'), $this->equalTo('en'), $this->equalTo('foo'), $this->equalTo('bar'))
            ->will($this->returnValue('baz'))
        ;
        $router = new Router($parentRouter, $translator);

        $router->generate('test_route', array('foo' => 'bar', 'translate' => 'foo', 'locale' => 'en'), false);
    }

    public function testGenerateI18nTranslatedContextLocale()
    {
        $parentRouter = $this->mockParentRouter();

        $parentRouter->expects($this->once())
            ->method('generate')
            ->with($this->equalTo('test_route.fr'), $this->equalTo(array('foo' => 'baz')), $this->equalTo(false))
        ;
        $translator = $this->getMock('BeSimple\I18nRoutingBundle\Routing\Translator\AttributeTranslatorInterface');
        $translator
            ->expects($this->once())
            ->method('reverseTranslate')
            ->with($this->equalTo('test_route'), $this->equalTo('fr'), $this->equalTo('foo'), $this->equalTo('bar'))
            ->will($this->returnValue('baz'))
        ;

        $context = $this->getMockBuilder('Symfony\Component\Routing\RequestContext')
            ->disableOriginalConstructor()
            ->setMethods(array('getParameter', 'hasParameter'))
            ->getMock()
        ;
        $context->expects($this->once())
            ->method('hasParameter')
            ->with($this->equalTo('_locale'))
            ->will($this->returnValue(true))
        ;
        $context->expects($this->once())
            ->method('getParameter')
            ->with($this->equalTo('_locale'))
            ->will($this->returnValue('fr'))
        ;
        $parentRouter
            ->expects($this->any())
            ->method('getContext')
            ->will($this->returnValue($context))
        ;

        $router = new Router($parentRouter, $translator);

        $router->generate('test_route', array('foo' => 'bar', 'translate' => 'foo'), false);
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\MissingMandatoryParametersException
     */
    public function testGenerateI18nTranslatedWithoutLocale()
    {
        $parentRouter = $this->mockParentRouter();

        $this->mockRouterContextWithoutLocale($parentRouter);

        $router = new Router($parentRouter);

        $router->generate('test_route', array('foo' => 'bar', 'translate' => 'foo'), false);
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\RouteNotFoundException
     */
    public function testGenerateWithoutDefaultLocale()
    {
        $parentRouter = $this->mockParentRouter();
        $parentRouter
            ->expects($this->any())
            ->method('generate')
            ->willThrowException(new RouteNotFoundException());
        ;

        $this->mockRouterContextWithoutLocale($parentRouter);

        $router = new Router($parentRouter);

        $router->generate('test_route', array('foo' => 'bar'), false);
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\RouteNotFoundException
     */
    public function testGenerateWithDefaultLocaleButWithoutRoute()
    {
        $parentRouter = $this->mockParentRouter();
        $parentRouter
            ->expects($this->exactly(2))
            ->method('generate')
            ->withConsecutive(
                array('test_route', array('foo' => 'bar'), false),
                array('test_route.en', array('foo' => 'bar'), false)
            )
            ->willThrowException(new RouteNotFoundException());
        ;

        $this->mockRouterContextWithoutLocale($parentRouter);

        $router = new Router($parentRouter);
        $router->setDefaultLocale('en');

        $router->generate('test_route', array('foo' => 'bar'), false);
    }

    public function testGetRouteCollectionProxy()
    {
        $parentRouter = $this->mockParentRouter();
        $parentRouter
            ->expects($this->once())
            ->method('getRouteCollection')
            ->willReturn('collection');

        $router = new Router($parentRouter);
        $this->assertSame('collection', $router->getRouteCollection());
    }

    public function testContextProxy()
    {
        $context = $this->getMockBuilder('Symfony\Component\Routing\RequestContext')
            ->disableOriginalConstructor()
            ->getMock();

        $parentRouter = $this->mockParentRouter();
        $parentRouter
            ->expects($this->once())
            ->method('getContext')
            ->willReturn('context');
        $parentRouter
            ->expects($this->once())
            ->method('setContext')
            ->with($context);

        $router = new Router($parentRouter);
        $router->setContext($context);
        $this->assertSame('context', $router->getContext());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\Routing\RouterInterface|\Symfony\Component\Routing\Router
     */
    private function mockParentRouter()
    {
        if (method_exists('Symfony\Component\Routing\RouterInterface', 'getContext')) {
            $parentRouter = $this->getMock('Symfony\Component\Routing\RouterInterface');
            return $parentRouter;
        } else {
            // use the Router for Symfony 2.0 as it implements the needed methods but they were not in the interface
            $parentRouter = $this->getMockBuilder('Symfony\Component\Routing\Router')
                ->disableOriginalConstructor()
                ->getMock();
            return $parentRouter;
        }
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $router
     */
    private function mockRouterContextWithoutLocale($router)
    {
        $context = $this->getMockBuilder('Symfony\Component\Routing\RequestContext')
            ->disableOriginalConstructor()
            ->setMethods(array('getParameter', 'hasParameter'))
            ->getMock();
        $context->expects($this->once())
            ->method('hasParameter')
            ->with($this->equalTo('_locale'))
            ->will($this->returnValue(false));
        $router
            ->expects($this->any())
            ->method('getContext')
            ->will($this->returnValue($context));
    }
}
