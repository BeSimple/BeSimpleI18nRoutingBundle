<?php
namespace BeSimple\I18nRoutingBundle\Routing\RouteGenerator\NameInflector;

/**
 * Deduce the route name to use for a localized route.
 */
interface RouteNameInflectorInterface
{
    /**
     * Return the route name and return it.
     *
     * @param string $name The route base name
     * @param string $locale The local
     *
     * @return string
     */
    public function inflect($name, $locale);
}
