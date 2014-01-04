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
    protected $translator;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * The locale to use when neither the parameters nor the request context
     * indicate the locale to use.
     *
     * @var string
     */
    protected $defaultLocale;

    /**
     * Constructor
     *
     * @param \Symfony\Component\Routing\RouterInterface   $router
     * @param Translator\AttributeTranslatorInterface|null $translator
     * @param string                                       $defaultLocale
     */
    public function __construct(RouterInterface $router, AttributeTranslatorInterface $translator = null, $defaultLocale = null)
    {
        $this->router = $router;
        $this->translator = $translator;
        $this->defaultLocale = $defaultLocale;
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

            $locale = $this->getLocale($parameters);

            if (isset($parameters['locale'])) {
                unset($parameters['locale']);
            }

            if (null === $locale) {
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
            $locale = $this->getLocale($parameters);
            if (null !== $locale) {
                // at this point here we would never have $parameters['translate'] due to condition before
                return $this->generateI18n($name, $locale, $parameters, $absolute);
            }

            throw $e;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function match($pathinfo)
    {
        $match = $this->router->match($pathinfo);

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
     * Overwrite the locale to be used by default if the current locale could
     * not be found when building the route
     *
     * @param string $locale
     */
    public function setDefaultLocale($locale)
    {
        $this->defaultLocale = $locale;
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
    protected function generateI18n($name, $locale, $parameters, $absolute)
    {
        try {
            return $this->router->generate($name.'.'.$locale, $parameters, $absolute);
        } catch (RouteNotFoundException $e) {
            throw new RouteNotFoundException(sprintf('I18nRoute "%s" (%s) does not exist.', $name, $locale));
        }
    }

    /**
     * Determine the locale to be used with this request
     *
     * @param array $parameters the parameters determined by the route
     *
     * @return string
     */
    protected function getLocale($parameters)
    {
        if (isset($parameters['locale'])) {
            return $parameters['locale'];
        }

        if ($this->getContext()->hasParameter('_locale')) {
            return $this->getContext()->getParameter('_locale');
        }

        return $this->defaultLocale;
    }
}
