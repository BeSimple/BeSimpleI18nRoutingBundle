<?php

namespace Bundle\I18nRoutingBundle\Routing;

use Symfony\Component\Routing\Route as BaseRoute;

class Route extends BaseRoute
{
    protected $isI18n;

    /**
     * Constructor.
     *
     * Available options:
     *
     *  * segment_separators: An array of allowed characters for segment separators (/ by default)
     *  * text_regex:         A regex that match a valid text name (.+? by default)
     *  * compiler_class:     A class name able to compile this route instance (RouteCompiler by default)
     *
     * @param string  $pattern       The pattern to match
     * @param array   $defaults      An array of default parameter values
     * @param array   $requirements  An array of requirements for parameters (regexes)
     * @param array   $options       An array of options
     * @param boolean $isI18n        If the route is an i18nRoute
     */
    public function __construct($pattern, array $defaults = array(), array $requirements = array(), array $options = array(), $isI18n = false)
    {
        $this->setIsI18n($isI18n);

        parent::__construct($pattern, $defaults, $requirements, $options);
    }

    /**
     * Return true if the route is an i18nRoute
     *
     * @return boolean Is i18nRoute
     */
    public function isI18n()
    {
        return (bool)$this->isI18n;
    }

    /**
     * Set i18nRoute.
     *
     * This method implements a fluent interface.
     *
     * @param boolean $i18nRoute The I18nRoute
     *
     * @return Route The current Route instance
     */
    public function setIsI18n($isI18n)
    {
        $this->isI18n = $isI18n;

        return $this;
    }
}