<?php

namespace BeSimple\I18nRoutingBundle\Routing;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use BeSimple\I18nRoutingBundle\Routing\Translator\AttributeTranslatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\RequestContext;

class Router implements RouterInterface
{
    /**
     * @var AttributeTranslatorInterface
     */
    private $translator;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * Constructor
     *
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param Translator\AttributeTranslatorInterface|null $translator
     */
    public function __construct(RouterInterface $router, AttributeTranslatorInterface $translator = null)
    {
        $this->router = $router;
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
            if (isset($parameters['locale'])) {
                $locale = $parameters['locale'];
                unset($parameters['locale']);
            } elseif ($this->getContext()->hasParameter('_locale')) {
                $locale = $this->getContext()->getParameter('_locale');
            } else {
                throw new MissingMandatoryParametersException('The locale must be available when using the "translate" option.');
            }

            if (isset($parameters['translate'])) {
                if (null !== $this->translator) {
                    foreach ((array) $parameters['translate'] as $translateAttribute) {
                        $parameters[$translateAttribute] = $this->translator->reverseTranslate(
                            $name, $locale, $translateAttribute, $parameters[$translateAttribute]
                        );
                    }
                }
                unset($parameters['translate']);
            }

            return $this->generateI18n($name, $locale, $parameters, $absolute);
        }

        try {
            return $this->router->generate($name, $parameters, $absolute);
        } catch (RouteNotFoundException $e) {
            if ($this->getContext()->hasParameter('_locale')) {
                // at this point here we would never have $parameters['translate'] due to condition before
                return $this->generateI18n($name, $this->getContext()->getParameter('_locale'), $parameters, $absolute);
            }

            throw $e;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function match($url)
    {
        $match = $this->router->match($url);

        // if a _locale parameter isset remove the .locale suffix that is appended to each route in I18nRoute
        if (!empty($match['_locale']) && preg_match('#^(.+)\.'.preg_quote($match['_locale'], '#').'+$#', $match['_route'], $route)) {
            $match['_route'] = $route[1];

            // now also check if we want to translate parameters:
            if (null !== $this->translator && isset($match['_translate'])) {
                foreach ((array) $match['_translate'] as $attribute) {
                    $match[$attribute] = $this->translator->translate(
                        $match['_route'], $match['_locale'], $attribute, $match[$attribute]
                    );
                }
            }
        }

        if (!empty($match['_locale']) && getenv('BESIMPLE_FORCE_LOCALE') !== false) {
            $match['_locale'] = getenv('BESIMPLE_FORCE_LOCALE');
        }

        return $match;
    }

    public function getRouteCollection()
    {
        return $this->router->getRouteCollection();
    }

    public function setContext(RequestContext $context)
    {
        $this->router->setContext($context);
    }

    public function getContext()
    {
        return $this->router->getContext();
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
     * @throws RouteNotFoundException When the route doesn't exists
     */
    private function generateI18n($name, $locale, $parameters, $absolute)
    {
        try {
            return $this->router->generate($name.'.'.$locale, $parameters, $absolute);
        } catch (RouteNotFoundException $e) {
            throw new RouteNotFoundException(sprintf('I18nRoute "%s" (%s) does not exist.', $name, $locale));
        }
    }
}
