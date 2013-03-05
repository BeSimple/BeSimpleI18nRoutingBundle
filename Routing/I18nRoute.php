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
     * @param string  $name         The route name
     * @param array   $locales      An array with keys locales and values patterns
     * @param array   $defaults     An array of default parameter values
     * @param array   $requirements An array of requirements for parameters (regexes)
     * @param array   $options      An array of options
     */
    public function __construct($name, array $locales, array $defaults = array(), array $requirements = array(), array $options = array())
    {
        $this->collection = new RouteCollection();

        foreach ($locales as $locale => $pattern) {
            $defaults['_locale'] = $locale;

            $this->collection->add($name.'.'.$locale, new Route($pattern, $defaults, $requirements, $options));
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