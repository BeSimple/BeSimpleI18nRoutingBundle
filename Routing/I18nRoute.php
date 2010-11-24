<?php

namespace Bundle\I18nRoutingBundle\Routing;

use Symfony\Component\Routing\RouteCollection;

class I18nRoute
{
    protected $collection;

    public function __construct($name, array $locales, array $defaults = array(), array $requirements = array(), array $options = array())
    {
        $this->collection = new RouteCollection();

        foreach ($locales as $locale => $pattern) {
            $defaults['_locale'] = $locale;

            $this->collection->addRoute($locale.'_'.$name, new Route($pattern, $defaults, $requirements, $options, true));
        }
    }

    /**
     * @return RouteCollection
     */
    public function getCollection()
    {
        return $this->collection;
    }
}