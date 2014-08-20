<?php

namespace BeSimple\I18nRoutingBundle\Routing;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class I18nRouteCollectionBuilder
{
    /**
     * buildCollection.
     *
     * Available options:
     *
     *  * See Routing class
     *
     * @param  string          $name             The route name
     * @param  array           $localesWithPaths An array with keys locales and values path patterns
     * @param  array           $defaults         An array of default parameter values
     * @param  array           $requirements     An array of requirements for parameters (regexes)
     * @param  array           $options          An array of options
     * @param  string          $host             The host pattern to match
     * @param  string|array    $schemes          A required URI scheme or an array of restricted schemes
     * @param  string|array    $methods          A required HTTP method or an array of restricted methods
     * @return RouteCollection
     */
    public function buildCollection($name, array $localesWithPaths, array $defaults = array(), array $requirements = array(), array $options = array(), $host = '', $schemes = array(), $methods = array())
    {
        $collection = new RouteCollection();
        foreach ($localesWithPaths as $locale => $path) {
            $defaults['_locale'] = $locale;

            $collection->add($name.'.'.$locale, new Route($path, $defaults, $requirements, $options, $host, $schemes, $methods));
        }

        return $collection;
    }
}
