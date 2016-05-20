<?php
namespace BeSimple\I18nRoutingBundle\Routing\RouteGenerator\NameInflector;

/**
 * A route name inflector that appends the locale to the routes name.
 */
class PostfixInflector implements RouteNameInflectorInterface
{
    /**
     * @inheritdoc
     */
    public function inflect($name, $locale)
    {
        return $name.'.'.$locale;
    }
}
