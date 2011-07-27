<?php

namespace BeSimple\I18nRoutingBundle\Routing;

use BeSimple\I18nRoutingBundle\Routing\Translator\AttributeTranslatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session;
use Symfony\Component\Routing\RequestContext;
use Symfony\Bundle\FrameworkBundle\Routing\Router as BaseRouter;

class Router extends BaseRouter
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var AttributeTranslatorInterface
     */
    private $translator;

    /**
     * Constructor.
     *
     * Available options:
     *
     *   * See Router class
     *
     * @param Session            $session   A Session instance
     * @param ContainerInterface $container A ContainerInterface instance
     * @param mixed              $resource  The main resource to load
     * @param array              $options   An array of options
     * @param array              $context   The context
     * @param array              $defaults  The default values
     *
     * @throws \InvalidArgumentException When unsupported option is provided
     */
    public function __construct(Session $session = null, AttributeTranslatorInterface $translator = null, ContainerInterface $container, $resource, array $options = array(), RequestContext $context = null, array $defaults = array())
    {
        parent::__construct($container, $resource, $options, $context, $defaults);

        $this->session    = $session;
        $this->translator = $translator;
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
    public function generate($name, $parameters = array(), $absolute = false)
    {
        if (isset($parameters['locale']) || isset($parameters['translate'])) {
            $locale = isset($parameters['locale']) ? $parameters['locale'] : $this->session->getLocale();
            unset($parameters['locale']);

            if (isset($parameters['translate'])) {
                foreach (array($parameters['translate']) as $translateAttribute) {
                    $parameters[$translateAttribute] = $this->translator->reverseTranslate(
                        $name, $locale, $translateAttribute, $parameters[$translateAttribute]
                    );
                }
                unset($parameters['translate']);
            }

            return $this->generateI18n($name, $locale, $parameters, $absolute);
        }

        try {
            return parent::generate($name, $parameters, $absolute);
        } catch (\InvalidArgumentException $e) {
            if (null !== $this->session) {
                // at this point here we would never have $parameters['translate'] due to condition before
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

        // if a _locale parameter isset remove the .locale suffix that is appended to each route in I18nRoute
        if (!empty($match['_locale']) && preg_match('#^(.+)\.'.preg_quote($match['_locale'], '#').'+$#', $match['_route'], $route)) {
            $match['_route'] = $route[1];

            // now also check if we want to translate parameters:
            if (isset($match['_translate'])) {
                foreach ((array)$match['_translate'] as $attribute) {
                    $match[$attribute] = $this->translator->translate(
                        $match['_route'], $match['_locale'], $attribute, $match[$attribute]
                    );
                }
            }
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
