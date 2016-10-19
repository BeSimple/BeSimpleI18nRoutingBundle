<?php
namespace BeSimple\I18nRoutingBundle\Routing\RouteGenerator\NameInflector;
use Symfony\Component\Routing\RouteCollection;

/**
 * A route name inflector that appends the locale to the routes name.
 */
class PostfixInflector implements RouteNameInflectorInterface
{
    const INFIX = '.be-simple-i18n.';

    /**
     * @inheritdoc
     */
    public function inflect($name, $locale)
    {
        return $name . self::INFIX . $locale;
    }

    /**
     * Reverse the inflection on an inflected route name.
     *
     * @param $name
     * @param $locale
     * @return mixed
     */
    public function unInflect($name, $locale)
    {
        $truncateHere = strpos($name, self::INFIX);
        return substr($name, 0, $truncateHere);
    }

    /**
     * Is used in the matching process to determine if isValidMatch() should be checked on a matched route.
     *
     * @param $name
     * @param $locale
     * @return mixed
     */
    public function isBeSimpleRoute($name, $locale = '')
    {
        return false !== strpos($name, self::INFIX);
    }

    /**
     * Checks if the constraints defined in the route definition are actually met.
     *
     * @param                 $name
     * @param                 $locale
     * @param RouteCollection $routeCollection
     * @return mixed
     */
    public function isValidMatch($name, $locale, RouteCollection $routeCollection = null)
    {
        $matchedRoute = $this->unInflect($name, $locale);

         // locale does not match postfixed locale
         if ($locale !== ($otherLocale = substr($name, - strlen($locale)))) {
             if (is_null($routeCollection)) {
                 return false;
             }

             // check if no another registered route does match, and then throw an exception
             $otherRoute = $this->inflect($matchedRoute, $locale);

             $allRoutes = $routeCollection->getIterator();

             // there is no valid route for the other locale
             if ( ! key_exists($otherRoute, $allRoutes)) {
                 return false;
             }

             // there is a valid route for the other locale, but does the pathinfo match?
             $originalPathInfo = $allRoutes[$name]->getPath();
             $otherPathInfo = $allRoutes[$otherRoute]->getPath();

             if ($originalPathInfo !== $otherPathInfo) {
                 // mismatch
                 return false;
             }
         }

         return true;
    }
}
