<?php

namespace BeSimple\I18nRoutingBundle\Tests\Routing;

use BeSimple\I18nRoutingBundle\Routing\Router;
use BeSimple\I18nRoutingBundle\Routing\I18nRoute;
use Symfony\Component\HttpFoundation\Session;
use Symfony\Component\HttpFoundation\SessionStorage\ArraySessionStorage;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    private $router;
    private $translator;

    public function setUp()
    {
        $this->translator = $this->getMock('BeSimple\I18nRoutingBundle\Routing\Translator\AttributeTranslatorInterface');

        $container    = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->router = $this->getMock('BeSimple\I18nRoutingBundle\Routing\Router', array('getMatcher', 'getGenerator'), array(
            $this->translator,
            $container,
            null,
        ));
    }

    public function testMatchLocaleRoute()
    {
        $route = new I18nRoute('test', array('en' => '/foo', 'de' => 'bar'));
        $this->expectMatchCollection($route->getCollection());

        $data = $this->router->match('/foo');

        $this->assertEquals('en', $data['_locale']);
        $this->assertEquals('test', $data['_route']);

        $data = $this->router->match('/bar');

        $this->assertEquals('de', $data['_locale']);
        $this->assertEquals('test', $data['_route']);
    }

    public function testMatchTranslateStringField()
    {
        $requestName  = "beberlei";
        $originalName = "Benjamin";

        $route = new I18nRoute('test', array('en' => '/foo/{name}'), array('_translate' => 'name'));
        $this->expectMatchCollection($route->getCollection());

        $this->translator
             ->expects($this->once())
             ->method('translate')
             ->with($this->equalTo('test'), $this->equalTo('en'), $this->equalTo('name'), $this->equalTo($requestName))
             ->will($this->returnValue($originalName))
        ;

        $data = $this->router->match('/foo/beberlei');

        $this->assertEquals('en', $data['_locale']);
        $this->assertEquals('test', $data['_route']);
        $this->assertEquals('Benjamin', $data['name']);
    }

    public function testGenerateI18n()
    {
        $absolute  = false;
        $generator = $this->getMock('Symfony\Component\Routing\Generator\UrlGeneratorInterface', array('generateI18n', 'generate', 'setContext', 'getContext'));
        $generator->expects($this->once())
            ->method('generateI18n')
            ->with($this->equalTo('test_route'), $this->equalTo('en'), $this->equalTo(array('foo' => 'bar')), $this->equalTo($absolute))
        ;

        $this->router
            ->expects($this->once())
            ->method('getGenerator')
            ->will($this->returnValue($generator))
        ;

        $this->router->generate('test_route', array('foo' => 'bar', 'locale' => 'en'), $absolute);
    }

    public function testGenerateDefault()
    {
        $absolute  = false;
        $generator = $this->getMock('Symfony\Component\Routing\Generator\UrlGeneratorInterface', array('generateI18n', 'generate', 'setContext', 'getContext'));
        $generator->expects($this->once())
            ->method('generate')
            ->with($this->equalTo('test_route'), $this->equalTo(array('foo' => 'bar')), $this->equalTo($absolute))
        ;

        $this->router
            ->expects($this->once())
            ->method('getGenerator')
            ->will($this->returnValue($generator))
        ;

        $this->router->generate('test_route', array('foo' => 'bar'), $absolute);
    }

    public function testGenerateI18nTranslated()
    {
        $originalValue  = 'bar';
        $localizedValue = 'baz';

        $absolute  = false;
        $generator = $this->getMock('Symfony\Component\Routing\Generator\UrlGeneratorInterface', array('generateI18n', 'generate', 'setContext', 'getContext'));
        $generator->expects($this->once())
            ->method('generateI18n')
            ->with($this->equalTo('test_route'), $this->equalTo('en'), $this->equalTo(array('foo' => $localizedValue)), $this->equalTo($absolute))
        ;

        $this->translator
            ->expects($this->once())
            ->method('reverseTranslate')
            ->with($this->equalTo('test_route'), $this->equalTo('en'), $this->equalTo('foo'), $this->equalTo($originalValue))
            ->will($this->returnValue($localizedValue))
        ;

        $this->router
            ->expects($this->once())
            ->method('getGenerator')
            ->will($this->returnValue($generator))
        ;

        $this->router->generate('test_route', array('foo' => $originalValue, 'translate' => 'foo', 'locale' => 'en'), $absolute);
    }

    public function testGenerateI18nTranslatedContextLocale()
    {
        $originalValue  = 'bar';
        $localizedValue = 'baz';

        $absolute  = false;
        $generator = $this->getMock('Symfony\Component\Routing\Generator\UrlGeneratorInterface', array('generateI18n', 'generate', 'setContext', 'getContext'));
        $generator->expects($this->once())
            ->method('generateI18n')
            ->with($this->equalTo('test_route'), $this->equalTo('en'), $this->equalTo(array('foo' => $localizedValue)), $this->equalTo($absolute))
        ;

        $this->translator
            ->expects($this->once())
            ->method('reverseTranslate')
            ->with($this->equalTo('test_route'), $this->equalTo('en'), $this->equalTo('foo'), $this->equalTo($originalValue))
            ->will($this->returnValue($localizedValue))
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
            ->will($this->returnValue('en'))
        ;
        $this->router = $this->getMock('BeSimple\I18nRoutingBundle\Routing\Router', array('getMatcher', 'getGenerator', 'getContext'), array(
            $this->translator,
            $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface'),
            null,
        ));
        $this->router
            ->expects($this->once())
            ->method('getGenerator')
            ->will($this->returnValue($generator))
        ;
        $this->router
            ->expects($this->any())
            ->method('getContext')
            ->will($this->returnValue($context))
        ;

        $this->router->generate('test_route', array('foo' => $originalValue, 'translate' => 'foo'), $absolute);
    }

    /**
     * @expectedException Symfony\Component\Routing\Exception\MissingMandatoryParametersException
     */
    public function testGenerateI18nTranslatedWithoutLocale()
    {
        $originalValue  = 'bar';
        $absolute  = false;

        $this->router->generate('test_route', array('foo' => $originalValue, 'translate' => 'foo'), $absolute);
    }

    public function expectMatchCollection($collection)
    {
        $context = $this->getMock('Symfony\Component\Routing\RequestContext', array(), array(), '', false);
        $matcher = new \Symfony\Component\Routing\Matcher\UrlMatcher($collection, $context);

        $this->router->expects($this->any())
            ->method('getMatcher')
            ->will($this->returnValue($matcher))
        ;
    }
}
