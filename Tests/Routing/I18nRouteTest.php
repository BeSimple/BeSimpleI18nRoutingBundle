<?php

namespace BeSimple\I18nRoutingBundle\Tests\Routing;

use BeSimple\I18nRoutingBundle\Routing\I18nRoute;

/**
 * @author Francis Besset <francis.besset@gmail.com>
 */
class I18nRouteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideI18nRouteData
     */
    public function testCollection($name, $locales, $defaults, $requirements, $options, $variables)
    {
        $i18nRoute  = new I18nRoute($name, $locales, $defaults, $requirements, $options);
        $collection = $i18nRoute->getCollection();

        foreach ($locales as $locale => $pattern) {
            $compiled = $collection->get($name.'.'.$locale)->compile();
            
            $defaults['_locale']       = $locale;
            $options['compiler_class'] = 'Symfony\\Component\\Routing\\RouteCompiler';

            $this->assertEquals($pattern, $compiled->getPattern(), '(pattern)');
            $this->assertEquals($defaults, $compiled->getDefaults(), '(defaults)');
            $this->assertEquals($requirements, $compiled->getRequirements(), '(requirements)');
            $this->assertEquals($options, $compiled->getOptions(), '(options)');
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