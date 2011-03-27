<?php

namespace BeSimple\I18nRoutingBundle\Routing;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpFoundation\Session;
use Symfony\Component\Routing\Router as BaseRouter;

class Router extends BaseRouter
{
    protected $session;

    /**
     * Constructor.
     *
     * Available options:
     *
     *   * See Router class
     *
     * @param LoaderInterface $loader   A LoaderInterface instance
     * @param Session         $session  A Session instance
     * @param mixed           $resource The main resource to load
     * @param array           $options  An array of options
     * @param array           $context  The context
     * @param array           $defaults The default values
     *
     * @throws \InvalidArgumentException When unsupported option is provided
     */
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
     *
     * @throws \InvalidArgumentException When the route doesn't exists
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

    /**
     * {@inheritDoc}
     */
    public function match($url)
    {
        $match = parent::match($url);

        if (!empty($match['_locale']) && preg_match('#^(.+)\.'.preg_quote($match['_locale'], '#').'+$#', $match['_route'], $route)) {
            $match['_route'] = $route[1];
        }

        return $match;
    }

    /**
     * Generates a I18N URL from the given parameter
     *
     * @param string   $name       The name of the I18N route
     * @param string   $locale     The locale of the I18N route
     * @param  array   $parameters An array of parameters
     * @param  Boolean $absolute   Whether to generate an absolute URL
     *
     * @return string The generated URL
     *
     * @throws \InvalidArgumentException When the route doesn't exists
     */
    protected function generateI18n($name, $locale, $parameters, $absolute)
    {
        return $this->getGenerator()->generateI18n($name, $locale, $parameters, $absolute);
    }
}