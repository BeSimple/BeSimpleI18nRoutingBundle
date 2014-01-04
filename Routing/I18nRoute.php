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
     * @param string       $name             The route name
     * @param array        $localesWithPaths An array with keys locales and values path patterns
     * @param array        $defaults         An array of default parameter values
     * @param array        $requirements     An array of requirements for parameters (regexes)
     * @param array        $options          An array of options
     * @param string       $host             The host pattern to match
     * @param string|array $schemes          A required URI scheme or an array of restricted schemes
     * @param string|array $methods          A required HTTP method or an array of restricted methods
     */
    public function __construct($name, array $localesWithPaths, array $defaults = array(), array $requirements = array(), array $options = array(), $host = '', $schemes = array(), $methods = array())
    {
        $this->collection = new RouteCollection();

        foreach ($localesWithPaths as $locale => $path) {
            $defaults['_locale'] = $locale;

            $this->collection->add($name.'.'.$locale, new Route($path, $defaults, $requirements, $options, $host, $schemes, $methods));
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