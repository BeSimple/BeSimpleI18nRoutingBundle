<?php
namespace BeSimple\I18nRoutingBundle\Routing\RouteNameInflector;

/**
 * A route name inflector that appends the locale to the routes name.
 */
class PostfixInflector implements RouteNameInflector
{
    /**
     * @inheritdoc
     */
    public function inflect($name, $locale)
    {
        return $name.'.'.$locale;
    }
}
