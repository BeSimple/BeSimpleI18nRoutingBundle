<?php
namespace BeSimple\I18nRoutingBundle\Routing\Loader;

use BeSimple\I18nRoutingBundle\Routing\RouteGenerator\I18nRouteGenerator;
use BeSimple\I18nRoutingBundle\Routing\RouteGenerator\RouteGeneratorInterface;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml as YamlParser;
use Symfony\Component\Config\Loader\FileLoader;

/**
 * YamlFileLoader loads Yaml routing files.
 */
class YamlFileLoader extends FileLoader
{
    private static $availableKeys = array(
        'locales', 'resource', 'type', 'prefix', 'pattern', 'path', 'host', 'schemes', 'methods', 'defaults', 'requirements', 'options', 'condition',
    );
    /**
     * @var YamlParser|null
     */
    private $yamlParser;
    /**
     * @var RouteGeneratorInterface
     */
    private $routeGenerator;

    public function __construct(FileLocatorInterface $locator, RouteGeneratorInterface $routeGenerator = null)
    {
        parent::__construct($locator);

        $this->routeGenerator = $routeGenerator ?: new I18nRouteGenerator();
    }

    /**
     * Loads a Yaml file.
     *
     * @param string      $file A Yaml file path
     * @param string|null $type The resource type
     *
     * @return RouteCollection A RouteCollection instance
     *
     * @throws \InvalidArgumentException When a route can't be parsed because YAML is invalid
     */
    public function load($file, $type = null)
    {
        $path = $this->locator->locate($file);

        if (!stream_is_local($path)) {
            throw new \InvalidArgumentException(sprintf('This is not a local file "%s".', $path));
        }

        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf('File "%s" not found.', $path));
        }

        if (null === $this->yamlParser) {
            $this->yamlParser = new YamlParser();
        }

        try {
            $parsedConfig = $this->yamlParser->parse(file_get_contents($path));
        } catch (ParseException $e) {
            throw new \InvalidArgumentException(sprintf('The file "%s" does not contain valid YAML.', $path), 0, $e);
        }

        $collection = new RouteCollection();
        $collection->addResource(new FileResource($path));

        // empty file
        if (null === $parsedConfig) {
            return $collection;
        }

        // not an array
        if (!is_array($parsedConfig)) {
            throw new \InvalidArgumentException(sprintf('The file "%s" must contain a YAML array.', $path));
        }

        foreach ($parsedConfig as $name => $config) {
            if (isset($config['pattern'])) {
                if (isset($config['path'])) {
                    throw new \InvalidArgumentException(sprintf('The file "%s" cannot define both a "path" and a "pattern" attribute. Use only "path".', $path));
                }

                $config['path'] = $config['pattern'];
                unset($config['pattern']);
            }

            $this->validate($config, $name, $path);

            if (isset($config['resource'])) {
                $this->parseImport($collection, $config, $path, $file);
            } else {
                $this->parseRoute($collection, $name, $config, $path);
            }
        }

        return $collection;
    }

    /**
     * @inheritdoc
     */
    public function supports($resource, $type = null)
    {
        return 'be_simple_i18n' === $type && is_string($resource) && in_array(pathinfo($resource, PATHINFO_EXTENSION), array('yml', 'yaml'), true);
    }

    /**
     * Parses a route and adds it to the RouteCollection.
     *
     * @param RouteCollection $collection A RouteCollection instance
     * @param string $name Route name
     * @param array $config Route definition
     * @param string $path Full path of the YAML file being processed
     */
    protected function parseRoute(RouteCollection $collection, $name, array $config, $path)
    {
        $defaults = isset($config['defaults']) ? $config['defaults'] : array();
        $requirements = isset($config['requirements']) ? $config['requirements'] : array();
        $options = isset($config['options']) ? $config['options'] : array();
        $host = isset($config['host']) ? $config['host'] : '';
        $schemes = isset($config['schemes']) ? $config['schemes'] : array();
        $methods = isset($config['methods']) ? $config['methods'] : array();
        $condition = isset($config['condition']) ? $config['condition'] : null;

        if (isset($config['locales'])) {
            $collection->addCollection(
                $this->routeGenerator->generateRoutes(
                    $name,
                    $config['locales'],
                    new Route('', $defaults, $requirements, $options, $host, $schemes, $methods, $condition)
                )
            );
        } else {
            $route = new Route($config['path'], $defaults, $requirements, $options, $host, $schemes, $methods, $condition);
            $collection->add($name, $route);
        }
    }

    /**
     * Parses an import and adds the routes in the resource to the RouteCollection.
     *
     * @param RouteCollection $collection A RouteCollection instance
     * @param array $config Route definition
     * @param string $path Full path of the YAML file being processed
     * @param string $file Loaded file name
     */
    protected function parseImport(RouteCollection $collection, array $config, $path, $file)
    {
        $type = isset($config['type']) ? $config['type'] : null;
        $prefix = isset($config['prefix']) ? $config['prefix'] : '';
        $defaults = isset($config['defaults']) ? $config['defaults'] : array();
        $requirements = isset($config['requirements']) ? $config['requirements'] : array();
        $options = isset($config['options']) ? $config['options'] : array();
        $host = isset($config['host']) ? $config['host'] : null;
        $condition = isset($config['condition']) ? $config['condition'] : null;
        $schemes = isset($config['schemes']) ? $config['schemes'] : null;
        $methods = isset($config['methods']) ? $config['methods'] : null;

        $this->setCurrentDir(dirname($path));

        $subCollection = $this->import($config['resource'], $type, false, $file);
        /* @var $subCollection \Symfony\Component\Routing\RouteCollection */
        $subCollection = $this->routeGenerator->generateCollection($prefix, $subCollection);
        if (null !== $host) {
            $subCollection->setHost($host);
        }
        if (null !== $condition) {
            $subCollection->setCondition($condition);
        }
        if (null !== $schemes) {
            $subCollection->setSchemes($schemes);
        }
        if (null !== $methods) {
            $subCollection->setMethods($methods);
        }
        $subCollection->addDefaults($defaults);
        $subCollection->addRequirements($requirements);
        $subCollection->addOptions($options);

        $collection->addCollection($subCollection);
    }

    /**
     * @inheritDoc
     */
    protected function validate($config, $name, $path)
    {
        if (!is_array($config)) {
            throw new \InvalidArgumentException(sprintf('The definition of "%s" in "%s" must be a YAML array.', $name, $path));
        }
        if ($extraKeys = array_diff(array_keys($config), self::$availableKeys)) {
            throw new \InvalidArgumentException(sprintf(
                'The routing file "%s" contains unsupported keys for "%s": "%s". Expected one of: "%s".',
                $path,
                $name,
                implode('", "', $extraKeys),
                implode('", "', self::$availableKeys)
            ));
        }
        if (isset($config['resource']) && isset($config['path'])) {
            throw new \InvalidArgumentException(sprintf(
                'The routing file "%s" must not specify both the "resource" key and the "path" key for "%s". Choose between an import and a route definition.',
                $path,
                $name
            ));
        }
        if (!isset($config['resource']) && isset($config['type'])) {
            throw new \InvalidArgumentException(sprintf(
                'The "type" key for the route definition "%s" in "%s" is unsupported. It is only available for imports in combination with the "resource" key.',
                $name,
                $path
            ));
        }
        if (!isset($config['resource']) && !isset($config['path']) && !isset($config['locales'])) {
            throw new \InvalidArgumentException(sprintf(
                'You must define a "path" for the route "%s" in file "%s".',
                $name,
                $path
            ));
        }
    }
}
