<?php
namespace BeSimple\I18nRoutingBundle\Tests\Routing\Loader;

use BeSimple\I18nRoutingBundle\Routing\Loader\AnnotatedRouteControllerLoader;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;

class AnnotatedRouteControllerLoaderTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        if (!method_exists('Symfony\Component\Routing\Loader\AnnotationClassLoader', 'getGlobals')) {
            self::markTestSkipped('Unsupported symfony version 2.5 or greater is required.');
        }
    }

    public function testRoutesWithoutLocales()
    {
        $loader = new AnnotatedRouteControllerLoader(new AnnotationReader());
        AnnotationRegistry::registerLoader('class_exists');

        $rc = $loader->load('BeSimple\I18nRoutingBundle\Tests\Fixtures\Controller\NoLocalesController');

        $this->assertInstanceOf('Symfony\Component\Routing\RouteCollection', $rc);
        $this->assertContainsOnlyInstancesOf('Symfony\Component\Routing\Route', $rc);
        $this->assertCount(2, $rc);

        $this->assertEquals('/base/', $rc->get('index')->getPath());
        $this->assertEquals('/base/new', $rc->get('new')->getPath());
    }

    public function testRoutesWithLocales()
    {
        $loader = new AnnotatedRouteControllerLoader(new AnnotationReader());
        AnnotationRegistry::registerLoader('class_exists');

        $rc = $loader->load('BeSimple\I18nRoutingBundle\Tests\Fixtures\Controller\NoPrefixController');

        $this->assertInstanceOf('Symfony\Component\Routing\RouteCollection', $rc);
        $this->assertContainsOnlyInstancesOf('Symfony\Component\Routing\Route', $rc);
        $this->assertCount(4, $rc);

        $this->assertEquals('/', $rc->get('besimple_i18nrouting_tests_fixtures_noprefix_index.en')->getPath());
        $this->assertEquals('/nl/', $rc->get('besimple_i18nrouting_tests_fixtures_noprefix_index.nl')->getPath());
        $this->assertEquals('/new', $rc->get('new_action.en')->getPath());
        $this->assertEquals('/nieuw', $rc->get('new_action.nl')->getPath());
    }

    public function testRoutesWithPrefixedLocales()
    {
        $loader = new AnnotatedRouteControllerLoader(new AnnotationReader());
        AnnotationRegistry::registerLoader('class_exists');

        $rc = $loader->load('BeSimple\I18nRoutingBundle\Tests\Fixtures\Controller\PrefixedLocalesController');

        $this->assertInstanceOf('Symfony\Component\Routing\RouteCollection', $rc);
        $this->assertContainsOnlyInstancesOf('Symfony\Component\Routing\Route', $rc);
        $this->assertCount(7, $rc);

        $this->assertEquals('/en/', $rc->get('idx.en')->getPath());
        $this->assertEquals('/nl/', $rc->get('idx.nl')->getPath());
        $this->assertEquals('/fr/', $rc->get('idx.fr')->getPath());

        $this->assertEquals('/en/edit', $rc->get('edit.en')->getPath());

        $this->assertEquals('/en/new', $rc->get('new.en')->getPath());
        $this->assertEquals('/nl/nieuw', $rc->get('new.nl')->getPath());
        $this->assertEquals('/fr/nouveau', $rc->get('new.fr')->getPath());
    }

    public function testRoutesWithStringPrefix()
    {
        $loader = new AnnotatedRouteControllerLoader(new AnnotationReader());
        AnnotationRegistry::registerLoader('class_exists');

        $rc = $loader->load('BeSimple\I18nRoutingBundle\Tests\Fixtures\Controller\PlainPrefixController');

        $this->assertInstanceOf('Symfony\Component\Routing\RouteCollection', $rc);
        $this->assertContainsOnlyInstancesOf('Symfony\Component\Routing\Route', $rc);
        $this->assertCount(3, $rc);

        $this->assertEquals('/color/', $rc->get('idx.en')->getPath());
        $this->assertEquals('/color/test', $rc->get('idx.test')->getPath());
        $this->assertEquals('/color/plain', $rc->get('new')->getPath());
    }

    /**
     * @expectedException \BeSimple\I18nRoutingBundle\Routing\Exception\MissingLocaleException
     */
    public function testRoutesMissingPrefixLocale()
    {
        $loader = new AnnotatedRouteControllerLoader(new AnnotationReader());
        AnnotationRegistry::registerLoader('class_exists');

        $loader->load('BeSimple\I18nRoutingBundle\Tests\Fixtures\Controller\MissingPrefixedLocalesController');
    }
}
