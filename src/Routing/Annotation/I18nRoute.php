<?php

namespace BeSimple\I18nRoutingBundle\Routing\Annotation;

use Symfony\Component\Routing\Annotation\Route as BaseRoute;

/**
 * Annotation class for @I18nRoute().
 *
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class I18nRoute extends BaseRoute
{
    protected $locales;
    protected $requirements = array();
    protected $methods = array();
    protected $schemes = array();

    /**
     * Constructor.
     *
     * @param array $data An array of key/value parameters.
     *
     * @throws \BadMethodCallException
     */
    public function __construct(array $data)
    {
        if (isset($data['value'])) {
            $data['locales'] = $data['value'];
            unset($data['value']);
        }

        parent::__construct($data);
    }

    public function setLocales($locales)
    {
        $this->locales = $locales;
    }

    public function getLocales()
    {
        return $this->locales;
    }

    public function setRequirements($requirements)
    {
        if (isset($requirements['_method'])) {
            if (0 === count($this->methods)) {
                $this->methods = explode('|', $requirements['_method']);
            }
        }

        if (isset($requirements['_scheme'])) {
            if (0 === count($this->schemes)) {
                $this->schemes = explode('|', $requirements['_scheme']);
            }
        }

        $this->requirements = $requirements;
    }

    public function getRequirements()
    {
        return $this->requirements;
    }

    public function setSchemes($schemes)
    {
        $this->schemes = is_array($schemes) ? $schemes : array($schemes);
    }

    public function getSchemes()
    {
        return $this->schemes;
    }

    public function setMethods($methods)
    {
        $this->methods = is_array($methods) ? $methods : array($methods);
    }

    public function getMethods()
    {
        return $this->methods;
    }
}
