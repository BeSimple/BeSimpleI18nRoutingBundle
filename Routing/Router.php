<?php

namespace BeSimple\I18nRoutingBundle\Routing;

use BeSimple\I18nRoutingBundle\Routing\Translator\AttributeTranslatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\RequestContext;
use Symfony\Bundle\FrameworkBundle\Routing\Router as BaseRouter;

class Router extends BaseRouter
{
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
     * @param AttributeTranslatorInterface $translator
     * @param ContainerInterface $container A ContainerInterface instance
     * @param mixed              $resource  The main resource to load
     * @param array              $options   An array of options
     * @param array              $context   The context
     * @param array              $defaults  The default values
     *
     * @throws \InvalidArgumentException When unsupported option is provided
     */
    public function __construct(AttributeTranslatorInterface $translator = null, ContainerInterface $container, $resource, array $options = array(), RequestContext $context = null, array $defaults = array())
    {
        parent::__construct($container, $resource, $options, $context, $defaults);

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
            return parent::generate($name, $parameters, $absolute);
        } catch (\InvalidArgumentException $e) {
            if ($this->getContext()->hasParameter('_locale')) {
                // at this point here we would never have $parameters['translate'] due to condition before
                return $this->generateI18n($name, $this->getContext()->getParameter('_locale'), $parameters, $absolute);
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
