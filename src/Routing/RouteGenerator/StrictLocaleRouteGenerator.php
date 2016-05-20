<?php
namespace BeSimple\I18nRoutingBundle\Routing\RouteGenerator;

use BeSimple\I18nRoutingBundle\Routing\Exception\MissingLocaleException;
use BeSimple\I18nRoutingBundle\Routing\Exception\UnknownLocaleException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * A class to enforce a supported set of locales.
 */
class StrictLocaleRouteGenerator implements RouteGeneratorInterface
{
    private $routeGenerator;
    private $locales;

    private $allowFallback = false;

    public function __construct(RouteGeneratorInterface $internalRouteGenerator, array $supportedLocales)
    {
        if (empty($supportedLocales)) {
            throw new \InvalidArgumentException('The supportedLocales must contain at least one locale.');
        }

        $this->routeGenerator = $internalRouteGenerator;
        $this->locales = $supportedLocales;
    }

    public function allowFallback($enabled = true)
    {
        $this->allowFallback = $enabled;
    }

    /**
     * Generate localized versions of the given route.
     *
     * @param $name
     * @param array $localesWithPaths
     * @param Route $baseRoute
     * @return RouteCollection
     */
    public function generateRoutes($name, array $localesWithPaths, Route $baseRoute)
    {
        $this->assertLocalesAreSupported(array_keys($localesWithPaths));

        return $this->routeGenerator->generateRoutes($name, $localesWithPaths, $baseRoute);
    }

    /**
     * Generate a localized version of the given route collection.
     *
     * @param array|string $localesWithPrefix
     * @param RouteCollection $baseCollection
     * @return RouteCollection
     */
    public function generateCollection($localesWithPrefix, RouteCollection $baseCollection)
    {
        if (is_array($localesWithPrefix)) {
            $this->assertLocalesAreSupported(array_keys($localesWithPrefix));
        }

        return $this->routeGenerator->generateCollection($localesWithPrefix, $baseCollection);
    }

    private function assertLocalesAreSupported(array $locales)
    {
        if (!$this->allowFallback) {
            $missingLocales = array_diff($this->locales, $locales);
            if (!empty($missingLocales)) {
                throw MissingLocaleException::shouldSupportLocale($missingLocales);
            }
        }

        $unknownLocales = array_diff($locales, $this->locales);
        if (!empty($unknownLocales)) {
            throw UnknownLocaleException::unexpectedLocale($unknownLocales, $this->locales);
        }
    }
}
