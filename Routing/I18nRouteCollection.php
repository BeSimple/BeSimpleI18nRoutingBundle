<?php
namespace BeSimple\I18nRoutingBundle\Routing;

use BeSimple\I18nRoutingBundle\Routing\Exception\MissingRouteLocaleException;
use Symfony\Component\Routing\RouteCollection;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class I18nRouteCollection extends RouteCollection
{
    const LOCALE_REGEX = '#\{_locale\}#';
    const LOCALE_PARAM = '_locale';

    /**
     * {@inheritdoc}
     */
    public function addPrefix($prefix, array $defaults = array(), array $requirements = array())
    {
        $originalPrefix = false;
        if (is_array($prefix)) {
            foreach ($prefix as $locale => $localePrefix) {
                $prefix[$locale] = trim(trim($localePrefix), '/');
            }

            $this->localizeRoutes(array_keys($prefix));
        } elseif (is_string($prefix) && preg_match(self::LOCALE_REGEX, $prefix)) {
            $originalPrefix = trim(trim($prefix), '/');
            $prefix = array();
        } else {
            parent::addPrefix($prefix, $defaults, $requirements);

            return;
        }

        foreach ($this->all() as $name => $route) {
            $locale = $route->getDefault(self::LOCALE_PARAM);

            $routePrefix = $this->localizePrefix(
                $locale,
                $prefix,
                $originalPrefix,
                $name
            );

            $route->setPath('/' . $routePrefix . $route->getPath());
            $route->addDefaults($defaults);
            $route->addRequirements($requirements);
        }
    }

    /**
     * Remove any route that has no locale and generate locale routes based on the given locales
     *
     * @param string[] $locales The list of locales to generate for routes.
     */
    protected function localizeRoutes($locales)
    {
        $removeRoutes = array();
        $collection = new self();
        foreach ($this->all() as $name => $route) {
            if ($route->getDefault(self::LOCALE_PARAM) !== null) {
                continue;
            }
            $removeRoutes[] = $name;

            foreach ($locales as $locale) {
                /** @var \Symfony\Component\Routing\Route $localeRoute */
                $localeRoute = unserialize(serialize($route));
                $localeRoute->setDefault(self::LOCALE_PARAM, $locale);
                $collection->add($name.'.'.$locale, $localeRoute);
            }
        }

        $this->remove($removeRoutes);
        $this->addCollection($collection);
    }

    /**
     * Retrieve a localized prefix.
     *
     * @param  string|null $locale         The locale
     * @param  array       $prefixes       The given locale prefixes
     * @param  bool        $prefixOriginal The original prefix, this is used when $locale is NULL
     * @param  string      $routeName      The route name used for exceptions only
     * @return string
     */
    protected function localizePrefix($locale, array &$prefixes, $prefixOriginal = false, $routeName = 'unknown')
    {
        // No locale
        if ($locale === null) {
            // Return the original prefix is that is provided.
            if ($prefixOriginal !== false) {
                return $prefixOriginal;
            }

            // No original prefix so we throw a exception
            throw new MissingRouteLocaleException(sprintf('Route `%s`: no "%s" found', $routeName, self::LOCALE_PARAM));
        }

        // Create a locale prefix if a original prefix is provided and there is no prefix for the locale.
        if ($prefixOriginal !== false && !isset($prefixes[$locale])) {
            $prefixes[$locale] = preg_replace(static::LOCALE_REGEX, $locale, $prefixOriginal);
        }

        // No prefix for the locale, time for a exception
        if (!isset($prefixes[$locale])) {
            throw new MissingRouteLocaleException(sprintf('Route `%s`: No prefix found for locale "%s".', $routeName, $locale));
        }

        return $prefixes[$locale];
    }
}
