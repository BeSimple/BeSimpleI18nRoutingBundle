<?php

namespace BeSimple\I18nRoutingBundle\Routing\Annotation;

/**
 * Annotation class for @I18nRoute().
 *
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class I18nRoute
{
    private $locales;
    private $name;
    private $requirements = array();
    private $options = array();
    private $defaults = array();
    private $host;
    private $methods = array();
    private $schemes = array();
    private $condition;

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

        foreach ($data as $key => $value) {
            $method = 'set'.str_replace('_', '', $key);
            if (!method_exists($this, $method)) {
                throw new \BadMethodCallException(sprintf('Unknown property "%s" on annotation "%s".', $key, get_class($this)));
            }
            $this->$method($value);
        }
    }

    public function setLocales($locales)
    {
        $this->locales = $locales;
    }

    public function getLocales()
    {
        return $this->locales;
    }

    public function setHost($pattern)
    {
        $this->host = $pattern;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
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

    public function setOptions($options)
    {
        $this->options = $options;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setDefaults($defaults)
    {
        $this->defaults = $defaults;
    }

    public function getDefaults()
    {
        return $this->defaults;
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

    public function setCondition($condition)
    {
        $this->condition = $condition;
    }

    public function getCondition()
    {
        return $this->condition;
    }
}
