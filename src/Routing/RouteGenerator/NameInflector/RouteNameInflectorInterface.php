<?php
namespace BeSimple\I18nRoutingBundle\Routing\RouteGenerator\NameInflector;
use Symfony\Component\Routing\RouteCollection;

/**
 * Deduce the route name to use for a localized route.
 */
interface RouteNameInflectorInterface
{
    /**
     * Inflect the route name.
     *
     * @param string $name The route base name
     * @param string $locale The local
     *
     * @return string
     */
    public function inflect($name, $locale);

    /**
     * Reverse the inflection on an inflected route name.
     *
     * @param $name
     * @param $locale
     * @return mixed
     */
    public function unInflect($name, $locale);

    /**
     * Is used in the matching process to determine if isValidMatch() should be checked on a matched route.
     *
     * @param $name
     * @param $locale
     * @return mixed
     */
    public function isBeSimpleRoute($name, $locale = '');

    /**
     * Checks if the constraints defined in the route definition are actually met.
     *
     * @param                 $name
     * @param                 $locale
     * @param RouteCollection $routeCollection
     * @return mixed
     */
    public function isValidMatch($name, $locale, RouteCollection $routeCollection);
}
