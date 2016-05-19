<?php
namespace BeSimple\I18nRoutingBundle\Routing\RouteGenerator;

use BeSimple\I18nRoutingBundle\Routing\Exception\MissingRouteLocaleException;
use BeSimple\I18nRoutingBundle\Routing\RouteGenerator\NameInflector\PostfixInflector;
use BeSimple\I18nRoutingBundle\Routing\RouteGenerator\NameInflector\RouteNameInflectorInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class I18nRouteGenerator implements RouteGeneratorInterface
{
    const LOCALE_REGEX = '#\{_locale\}#';
    const LOCALE_PARAM = '_locale';
    /**
     * @var RouteNameInflectorInterface
     */
    private $routeNameInflector;

    public function __construct(RouteNameInflectorInterface $routeNameInflector = null)
    {
        $this->routeNameInflector = $routeNameInflector ?: new PostfixInflector();
    }

    /**
     * @inheritdoc
     */
    public function generateRoutes($name, array $localesWithPaths, Route $baseRoute)
    {
        $collection = new RouteCollection();

        foreach ($localesWithPaths as $locale => $path) {
            /** @var \Symfony\Component\Routing\Route $localeRoute */
            $localeRoute = clone $baseRoute;
            $localeRoute->setDefault(self::LOCALE_PARAM, $locale);
            $localeRoute->setPath($path);

            $collection->add(
                $this->routeNameInflector->inflect($name, $locale),
                $localeRoute
            );
        }

        return $collection;
    }

    /**
     * Generate a localized version of the given route collection.
     *
     * @param array|string $prefix
     * @param RouteCollection $baseCollection
     * @return RouteCollection
     */
    public function generateCollection($prefix, RouteCollection $baseCollection)
    {
        $collection = clone $baseCollection;

        if (is_array($prefix)) {
            $prefixes = array();
            foreach ($prefix as $locale => $localePrefix) {
                $prefixes[$locale] = trim(trim($localePrefix), '/');
            }

            $this->localizeCollection($prefixes, $collection);
        } elseif (is_string($prefix) && preg_match(self::LOCALE_REGEX, $prefix)) {
            $originalPrefix = trim(trim($prefix), '/');
            $this->localizeCollectionLocaleParameter($originalPrefix, $collection);
        } else {
            // A normal prefix so just add it and return the original collection
            $collection->addPrefix($prefix);
        }

        return $collection;
    }

    /**
     * Localize a route collection.
     *
     * @param array $prefixes
     * @param RouteCollection $collection
     */
    protected function localizeCollection(array $prefixes, RouteCollection $collection)
    {
        $removeRoutes = array();
        $newRoutes = new RouteCollection();
        foreach ($collection->all() as $name => $route) {
            $routeLocale = $route->getDefault(self::LOCALE_PARAM);
            if ($routeLocale !== null) {
                if (!isset($prefixes[$routeLocale])) {
                    throw new MissingRouteLocaleException(sprintf('Route `%s`: No prefix found for locale "%s".', $name, $routeLocale));
                }

                $route->setPath('/' . $prefixes[$routeLocale] . $route->getPath());

                continue;
            }

            // No locale found for the route so localize the route
            $removeRoutes[] = $name;

            foreach ($prefixes as $locale => $prefix) {
                /** @var \Symfony\Component\Routing\Route $localeRoute */
                $localeRoute = clone $route;
                $localeRoute->setPath('/' . $prefix . $route->getPath());
                $localeRoute->setDefault(self::LOCALE_PARAM, $locale);

                $newRoutes->add(
                    $this->routeNameInflector->inflect($name, $locale),
                    $localeRoute
                );
            }
        }

        $collection->remove($removeRoutes);
        $collection->addCollection($newRoutes);
    }

    /**
     * Localize the prefix `_locale` of all routes.
     *
     * @param string $prefix A prefix containing _locale
     * @param RouteCollection $collection A RouteCollection instance
     */
    protected function localizeCollectionLocaleParameter($prefix, RouteCollection $collection)
    {
        $localizedPrefixes = array();
        foreach ($collection->all() as $name => $route) {
            $locale = $route->getDefault(self::LOCALE_PARAM);
            if ($locale === null) {
                // No locale so nothing to do
                $routePrefix = $prefix;
            } else {
                // A locale was found so localize the prefix
                if (!isset($localizedPrefixes[$locale])) {
                    $localizedPrefixes[$locale] = preg_replace(static::LOCALE_REGEX, $locale, $prefix);
                }

                $routePrefix = $localizedPrefixes[$locale];
            }

            $route->setPath('/' . $routePrefix . $route->getPath());
        }
    }
}
