<?php

namespace Bundle\I18nRoutingBundle\Routing;

use Symfony\Component\Routing\Router as BaseRouter,
    Symfony\Component\Config\Loader\LoaderInterface,
    Symfony\Component\HttpFoundation\Session;

class Router extends BaseRouter
{
    protected $session;

    public function __construct(LoaderInterface $loader, Session $session = null, $resource, array $options = array(), array $context = array(), array $defaults = array())
    {
        parent::__construct($loader, $resource, $options, $context, $defaults);

        $this->session = $session;
    }

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

            return $this->generateI18n($name, $locale, $parameters, $absolute);
        }

        try {
            return parent::generate($name, $parameters, $absolute);
        } catch (\InvalidArgumentException $e) {
            if (null !== $this->session) {
                return $this->generateI18n($name, $this->session->getLocale(), $parameters, $absolute);
            } else {
                throw $e;
            }
        }
    }

    protected function generateI18n($name, $locale, $parameters, $absolute)
    {
        return $this->getGenerator()->generateI18n($name, $locale, $parameters, $absolute);
    }
}