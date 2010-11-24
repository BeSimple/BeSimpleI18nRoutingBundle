<?php

namespace Bundle\I18nRoutingBundle\Routing;

use Symfony\Component\Routing\Router as BaseRouter;

class Router extends BaseRouter
{
    /**
     * Generates a URL from the given parameters.
     *
     * @param  string  $name       The name of the route
     * @param  array   $parameters An array of parameters
     * @param  Boolean $absolute   Whether to generate an absolute URL
     *
     * @return string The generated URL
     */
    public function generate($name, array $parameters = array(), $absolute = false)
    {
        if (isset($parameters['locale'])) {
            $locale = $parameters['locale'];
            unset($parameters['locale']);

            return $this->getGenerator()->generateI18n($name, $parameters, $locale, $absolute);
        }

        return parent::generate($name, $parameters, $absolute);
    }
}