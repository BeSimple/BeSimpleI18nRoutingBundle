<?php
namespace BeSimple\I18nRoutingBundle\Routing\RouteGenerator;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

interface RouteGeneratorInterface
{
    /**
     * Generate localized versions of the given route.
     *
     * @param $name
     * @param array $localesWithPaths
     * @param Route $baseRoute
     * @return RouteCollection
     */
    public function generateRoutes($name, array $localesWithPaths, Route $baseRoute);

    /**
     * Generate a localized version of the given route collection.
     *
     * @param array|string $localesWithPrefix
     * @param RouteCollection $baseCollection
     * @return RouteCollection
     */
    public function generateCollection($localesWithPrefix, RouteCollection $baseCollection);
}
