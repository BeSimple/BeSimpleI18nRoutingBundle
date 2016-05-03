<?php
namespace BeSimple\I18nRoutingBundle\Routing\RouteGenerator\NameInflector;

/**
 * A route name inflector that appends the locale to the routes name expect when the locale is the default locale.
 */
class DefaultPostfixInflector implements RouteNameInflector
{
    /**
     * @var string
     */
    private $defaultLocale;

    public function __construct($defaultLocale)
    {
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * @inheritdoc
     */
    public function inflect($name, $locale)
    {
        if ($this->defaultLocale === $locale) {
            return $name;
        }

        return $name.'.'.$locale;
    }
}
