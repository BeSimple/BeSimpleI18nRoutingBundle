<?php

namespace BeSimple\I18nRoutingBundle\Tests\Routing;

use BeSimple\I18nRoutingBundle\Routing\Router;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    public function testMatchLocaleRoute()
    {
        $parentRouter = $this->getMock('Symfony\Component\Routing\RouterInterface');
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
        $parentRouter = $this->getMock('Symfony\Component\Routing\RouterInterface');
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
        $parentRouter = $this->getMock('Symfony\Component\Routing\RouterInterface');
        $parentRouter->expects($this->once())
            ->method('generate')
            ->with($this->equalTo('test_route.en'), $this->equalTo(array('foo' => 'bar')), $this->equalTo(false))
        ;
        $router = new Router($parentRouter);

        $router->generate('test_route', array('foo' => 'bar', 'locale' => 'en'), false);
    }

    public function testGenerateDefault()
    {
        $parentRouter = $this->getMock('Symfony\Component\Routing\RouterInterface');
        $parentRouter->expects($this->once())
            ->method('generate')
            ->with($this->equalTo('test_route'), $this->equalTo(array('foo' => 'bar')), $this->equalTo(false))
        ;
        $router = new Router($parentRouter);

        $router->generate('test_route', array('foo' => 'bar'), false);
    }

    public function testGenerateI18nTranslated()
    {
        $parentRouter = $this->getMock('Symfony\Component\Routing\RouterInterface');
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
        if (method_exists('Symfony\Component\Routing\RouterInterface', 'getContext')) {
            $parentRouter = $this->getMock('Symfony\Component\Routing\RouterInterface');
        } else {
            // use the Router for Symfony 2.0 as it implements the needed methods but they were not in the interface
            $parentRouter = $this->getMockBuilder('Symfony\Component\Routing\Router')
                ->disableOriginalConstructor()
                ->getMock();
        }

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
     * @expectedException Symfony\Component\Routing\Exception\MissingMandatoryParametersException
     */
    public function testGenerateI18nTranslatedWithoutLocale()
    {
        if (method_exists('Symfony\Component\Routing\RouterInterface', 'getContext')) {
            $parentRouter = $this->getMock('Symfony\Component\Routing\RouterInterface');
        } else {
            // use the Router for Symfony 2.0 as it implements the needed methods but they were not in the interface
            $parentRouter = $this->getMockBuilder('Symfony\Component\Routing\Router')
                ->disableOriginalConstructor()
                ->getMock();
        }

        $context = $this->getMockBuilder('Symfony\Component\Routing\RequestContext')
            ->disableOriginalConstructor()
            ->setMethods(array('getParameter', 'hasParameter'))
            ->getMock()
        ;
        $context->expects($this->once())
            ->method('hasParameter')
            ->with($this->equalTo('_locale'))
            ->will($this->returnValue(false))
        ;
        $parentRouter
            ->expects($this->any())
            ->method('getContext')
            ->will($this->returnValue($context))
        ;

        $router = new Router($parentRouter);

        $router->generate('test_route', array('foo' => 'bar', 'translate' => 'foo'), false);
    }
}
