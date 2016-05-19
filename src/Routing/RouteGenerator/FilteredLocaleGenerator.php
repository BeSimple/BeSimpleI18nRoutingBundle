<?php
namespace BeSimple\I18nRoutingBundle\Routing\RouteGenerator;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * A route generator to only allow a specified set of locales.
 * Any locales that are not within the supported list will not be generated.
 */
class FilteredLocaleGenerator implements RouteGeneratorInterface
{
    private $routeGenerator;
    private $locales;

    public function __construct(RouteGeneratorInterface $internalRouteGenerator, array $allowedLocales)
    {
        if (empty($allowedLocales)) {
            throw new \InvalidArgumentException('The allowedLocales must contain at least one locale.');
        }

        $this->routeGenerator = $internalRouteGenerator;
        $this->locales = array_flip(array_values($allowedLocales));
    }

    /**
     * @inheritdoc
     */
    public function generateRoutes($name, array $localesWithPaths, Route $baseRoute)
    {
        return $this->routeGenerator->generateRoutes(
            $name,
            array_intersect_key($localesWithPaths, $this->locales),
            $baseRoute
        );
    }

    /**
     * @inheritdoc
     */
    public function generateCollection($localesWithPrefix, RouteCollection $baseCollection)
    {
        if (is_array($localesWithPrefix)) {
            $localesWithPrefix = array_intersect_key($localesWithPrefix, $this->locales);
        }

        return $this->routeGenerator->generateCollection($localesWithPrefix, $baseCollection);
    }
}
