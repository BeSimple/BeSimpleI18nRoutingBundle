<?php

namespace BeSimple\I18nRoutingBundle\Tests\Routing;

use BeSimple\I18nRoutingBundle\Routing\I18nRouteCollectionBuilder;

/**
 * @author Francis Besset <francis.besset@gmail.com>
 */
class I18nRouteCollectionBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideI18nRouteData
     */
    public function testCollection($name, $locales, $defaults, $requirements, $options, $variables)
    {
        $i18nRoute  = new I18nRouteCollectionBuilder;
        $collection = $i18nRoute->buildCollection($name, $locales, $defaults, $requirements, $options);

        foreach ($locales as $locale => $pattern) {
            $route = $collection->get($name.'.'.$locale);
            $compiled = $route->compile();

            $defaults['_locale']       = $locale;
            $options['compiler_class'] = 'Symfony\\Component\\Routing\\RouteCompiler';

            $this->assertEquals($pattern, $route->getPattern(), '(pattern)');
            $this->assertEquals($defaults, $route->getDefaults(), '(defaults)');
            $this->assertEquals($requirements, $route->getRequirements(), '(requirements)');
            $this->assertEquals($options, $route->getOptions(), '(options)');
            $this->assertEquals($variables, $compiled->getVariables(), '(variables)');
        }
    }

    public function provideI18nRouteData()
    {
        return array(
            array(
                'static_route',
                array('en' => '/welcome', 'fr' => '/bienvenue', 'de' => '/willkommen'),
                array(),
                array(),
                array(),
                array(),
            ),

           array(
                'dynamic_route',
                array('en' => '/en/{page}', 'fr' => '/fr/{page}', 'de' => '/de/{page}'),
                array(),
                array(),
                array(),
                array('page'),
            ),

            array(
                'default_route',
                array('en' => '/en/{page}', 'fr' => '/fr/{page}', 'de' => '/de/{page}'),
                array('page' => 'index.html'),
                array(),
                array(),
                array('page'),
            ),

            array(
                'requirement_route',
                array('en' => '/en/{page}.{extension}', 'fr' => '/fr/{page}.{extension}', 'de' => '/de/{page}.{extension}'),
                array('page' => 'index.html'),
                array('extension' => 'html|xml|json'),
                array(),
                array('page', 'extension'),
            ),

            array(
                'option_route',
                array('en' => '/en/{page}.{extension}', 'fr' => '/fr/{page}.{extension}', 'de' => '/de/{page}.{extension}'),
                array('page' => 'index.html'),
                array('page' => '\d+', 'extension' => 'html|xml|json'),
                array('page' => 1),
                array('page', 'extension'),
            ),

            array(
                'other_locales_route',
                array('en_GB' => '/en/{page}.{extension}', 'fr_FR' => '/fr/{page}.{extension}', 'de_DE' => '/de/{page}.{extension}'),
                array('page' => 'index.html'),
                array('page' => '\d+', 'extension' => 'html|xml|json'),
                array('page' => 1),
                array('page', 'extension'),
            ),
        );
    }
}
