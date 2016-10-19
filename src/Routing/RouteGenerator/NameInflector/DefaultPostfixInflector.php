<?php
namespace BeSimple\I18nRoutingBundle\Routing\RouteGenerator\NameInflector;

/**
 * A route name inflector that appends the locale to the routes name except when the locale is the default locale.
 */
class DefaultPostfixInflector extends PostfixInflector
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

        return parent::inflect($name, $locale);
    }
}
