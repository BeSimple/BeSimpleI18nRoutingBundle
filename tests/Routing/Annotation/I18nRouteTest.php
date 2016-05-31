<?php
namespace BeSimple\I18nRoutingBundle\Tests\Routing;


use BeSimple\I18nRoutingBundle\Routing\Annotation\I18nRoute;

class I18nRouteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \BadMethodCallException
     */
    public function testInvalidRouteParameter()
    {
        new I18nRoute(array('foo' => 'bar'));
    }

    /**
     * @dataProvider getValidParameters
     */
    public function testRouteParameters($parameter, $value, $getter)
    {
        $route = new I18nRoute(array($parameter => $value));
        $this->assertEquals($route->$getter(), $value);
    }

    public function getValidParameters()
    {
        return array(
            array('value', '/Blog', 'getLocales'),
            array('value', array('en' => '/BlogEn', 'nl' => '/Nl'), 'getLocales'),
            array('requirements', array('locale' => 'en'), 'getRequirements'),
            array('options', array('compiler_class' => 'RouteCompiler'), 'getOptions'),
            array('name', 'blog_index', 'getName'),
            array('defaults', array('_controller' => 'MyBlogBundle:Blog:index'), 'getDefaults'),
            array('schemes', array('https'), 'getSchemes'),
            array('methods', array('GET', 'POST'), 'getMethods'),
            array('host', '{locale}.example.com', 'getHost'),
            array('condition', 'context.getMethod() == "GET"', 'getCondition'),
        );
    }
}
