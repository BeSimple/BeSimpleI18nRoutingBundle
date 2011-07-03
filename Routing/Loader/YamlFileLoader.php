<?php

namespace BeSimple\I18nRoutingBundle\Routing\Loader;

use BeSimple\I18nRoutingBundle\Routing\I18nRoute;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\Loader\YamlFileLoader as BaseYamlFileLoader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Yaml\Yaml;

class YamlFileLoader extends BaseYamlFileLoader
{
    private static $availableKeys = array(
        'locales', 'type', 'resource', 'prefix', 'pattern', 'options', 'defaults', 'requirements'
    );

    /**
     * {@inheritDoc}
     */
    public function load($file, $type = null)
    {
        $path = $this->locator->locate($file);

        $config = Yaml::parse($path);

        $collection = new RouteCollection();
        $collection->addResource(new FileResource($path));

        // empty file
        if (null === $config) {
            $config = array();
        }

        // not an array
        if (!is_array($config)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" must contain a YAML array.', $file));
        }

        foreach ($config as $name => $config) {
            $config = $this->normalizeRouteConfig($config);

            if (isset($config['resource'])) {
                $type = isset($config['type']) ? $config['type'] : null;
                $prefix = isset($config['prefix']) ? $config['prefix'] : null;
                $this->setCurrentDir(dirname($path));
                $collection->addCollection($this->import($config['resource'], $type), $prefix);
            } elseif (isset($config['pattern']) || isset($config['locales'])) {
                $this->parseRoute($collection, $name, $config, $path);
            } else {
                throw new \InvalidArgumentException(sprintf('Unable to parse the "%s" route.', $name));
            }
        }

        return $collection;
    }

    /**
     * {@inheritDoc}
     */
    protected function parseRoute(RouteCollection $collection, $name, $config, $file)
    {
        $defaults = isset($config['defaults']) ? $config['defaults'] : array();
        $requirements = isset($config['requirements']) ? $config['requirements'] : array();
        $options = isset($config['options']) ? $config['options'] : array();

        if (isset($config['locales'])) {
            $route = new I18nRoute($name, $config['locales'], $defaults, $requirements, $options);

            $collection->addCollection($route->getCollection());
        } elseif (isset($config['pattern'])) {
            $route = new Route($config['pattern'], $defaults, $requirements, $options);

            $collection->add($name, $route);
        } else {
            throw new \InvalidArgumentException(sprintf('You must define a "pattern" for the "%s" route.', $name));
        }
    }

    /**
     * {@inheritDoc}
     */
    private function normalizeRouteConfig(array $config)
    {
        foreach ($config as $key => $value) {
            if (!in_array($key, self::$availableKeys)) {
                throw new \InvalidArgumentException(sprintf(
                    'Yaml routing loader does not support given key: "%s". Expected one of the (%s).',
                    $key, implode(', ', self::$availableKeys)
                ));
            }
        }

        return $config;
    }
}
