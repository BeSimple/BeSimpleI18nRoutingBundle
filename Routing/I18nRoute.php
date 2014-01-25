<?php

namespace BeSimple\I18nRoutingBundle\Routing;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class I18nRoute
{
    protected $collection;

    /**
     * Constructor.
     *
     * Available options:
     *
     *  * See Routing class
     *
     * @param string $name         The route name
     * @param array $locales      An array with keys locales and values patterns
     * @param array $defaults     An array of default parameter values
     * @param array $requirements An array of requirements for parameters (regexes)
     * @param array $options      An array of options
     * @param $default_locale     The kernel.default_locale parameter
     */
    public function __construct($name, array $locales, array $defaults = array(), array $requirements = array(), array $options = array(), $host = '', $schemes = array(), $methods = array())
    {
        $this->collection = new RouteCollection();

        // $default_locale = array_key_exists('_locale', $defaults) ? $defaults['_locale'] : false;

        foreach ($locales as $locale => $pattern) {

            // if($locale == $default_locale){
            //     $defaults_base = $defaults;
            //     $defaults_base['_locale'] = $locale;
            //     $this->collection->add($name, new Route($pattern, $defaults_base, $requirements, $options, $host, $schemes, $methods));
            // }

            $defaults['_locale'] = $locale;
            $this->collection->add($name.'.'.$locale, new Route($pattern, $defaults, $requirements, $options, $host, $schemes, $methods));
        }
    }

    /**
     * Return the RouteCollection
     *
     * @return RouteCollection $collection The RouteCollection
     */
    public function getCollection()
    {
        return $this->collection;
    }
}