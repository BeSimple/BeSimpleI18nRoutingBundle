<?php
namespace BeSimple\I18nRoutingBundle\Routing\Loader;

use BeSimple\I18nRoutingBundle\Routing\RouteGenerator\I18nRouteGenerator;
use BeSimple\I18nRoutingBundle\Routing\RouteGenerator\RouteGenerator;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Config\Util\XmlUtils;
use Symfony\Component\Routing\RouteCollection;

/**
 * XmlFileLoader
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Francis Besset <francis.besset@gmail.com>
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class XmlFileLoader extends FileLoader
{
    const NAMESPACE_URI = 'http://besim.pl/schema/i18n_routing';
    const SCHEME_PATH = '/schema/routing/routing-1.0.xsd';
    /**
     * @var RouteGenerator
     */
    private $routeGenerator;

    public function __construct(FileLocatorInterface $locator, RouteGenerator $routeGenerator = null)
    {
        parent::__construct($locator);

        $this->routeGenerator = $routeGenerator ?: new I18nRouteGenerator();
    }

    /**
     * Loads an XML file.
     *
     * @param string      $file An XML file path
     * @param string|null $type The resource type
     *
     * @return RouteCollection A RouteCollection instance
     *
     * @throws \InvalidArgumentException When the file cannot be loaded or when the XML cannot be
     *                                   parsed because it does not validate against the scheme.
     */
    public function load($file, $type = null)
    {
        $path = $this->locator->locate($file);

        $xml = $this->loadFile($path);

        $collection = new RouteCollection();
        $collection->addResource(new FileResource($path));

        // process routes and imports
        foreach ($xml->documentElement->childNodes as $node) {
            if (!$node instanceof \DOMElement) {
                continue;
            }

            $this->parseNode($collection, $node, $path, $file);
        }

        return $collection;
    }

    /**
     * Parses a node from a loaded XML file.
     *
     * @param RouteCollection $collection Collection to associate with the node
     * @param \DOMElement $node Element to parse
     * @param string $path Full path of the XML file being processed
     * @param string $file Loaded file name
     *
     * @throws \InvalidArgumentException When the XML is invalid
     */
    protected function parseNode(RouteCollection $collection, \DOMElement $node, $path, $file)
    {
        if (self::NAMESPACE_URI !== $node->namespaceURI) {
            return;
        }

        switch ($node->localName) {
            case 'route':
                $this->parseRoute($collection, $node, $path);
                break;
            case 'import':
                $this->parseImport($collection, $node, $path, $file);
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unknown tag "%s" used in file "%s". Expected "route" or "import".', $node->localName, $path));
        }
    }

    /**
     * @inheritdoc
     */
    public function supports($resource, $type = null)
    {
        return 'be_simple_i18n' === $type && is_string($resource) && 'xml' === pathinfo($resource, PATHINFO_EXTENSION);
    }

    /**
     * Parses a route and adds it to the RouteCollection.
     *
     * @param RouteCollection $collection RouteCollection instance
     * @param \DOMElement $node Element to parse that represents a Route
     * @param string $path Full path of the XML file being processed
     *
     * @throws \InvalidArgumentException When the XML is invalid
     */
    protected function parseRoute(RouteCollection $collection, \DOMElement $node, $path)
    {
        if ('' === ($id = $node->getAttribute('id'))) {
            throw new \InvalidArgumentException(sprintf('The <route> element in file "%s" must have an "id" attribute.', $path));
        }

        if ($node->hasAttribute('pattern')) {
            if ($node->hasAttribute('path')) {
                throw new \InvalidArgumentException(sprintf('The <route> element in file "%s" cannot define both a "path" and a "pattern" attribute. Use only "path".', $path));
            }

            $node->setAttribute('path', $node->getAttribute('pattern'));
            $node->removeAttribute('pattern');
        }

        $schemes = preg_split('/[\s,\|]++/', $node->getAttribute('schemes'), -1, PREG_SPLIT_NO_EMPTY);
        $methods = preg_split('/[\s,\|]++/', $node->getAttribute('methods'), -1, PREG_SPLIT_NO_EMPTY);

        list($defaults, $requirements, $options, $condition, $localesWithPaths) = $this->parseConfigs($node, $path);

        if ($localesWithPaths) {
            $collection->addCollection(
                $this->routeGenerator->generateRoutes(
                    $id,
                    $localesWithPaths,
                    new Route('', $defaults, $requirements, $options, $node->getAttribute('host'), $schemes, $methods, $condition)
                )
            );
        } else {
            if (!$node->hasAttribute('pattern') && !$node->hasAttribute('path')) {
                throw new \InvalidArgumentException(sprintf('The <route> element in file "%s" must have an "path" attribute.', $path));
            }

            $route = new Route($node->getAttribute('path'), $defaults, $requirements, $options, $node->getAttribute('host'), $schemes, $methods, $condition);
            $collection->add($id, $route);
        }
    }

    /**
     * Parses an import and adds the routes in the resource to the RouteCollection.
     *
     * @param RouteCollection $collection RouteCollection instance
     * @param \DOMElement $node Element to parse that represents a Route
     * @param string $path Full path of the XML file being processed
     * @param string $file Loaded file name
     *
     * @throws \InvalidArgumentException When the XML is invalid
     */
    protected function parseImport(RouteCollection $collection, \DOMElement $node, $path, $file)
    {
        if ('' === $resource = $node->getAttribute('resource')) {
            throw new \InvalidArgumentException(sprintf('The <import> element in file "%s" must have a "resource" attribute.', $path));
        }

        $type = $node->getAttribute('type');
        $prefix = $node->getAttribute('prefix');
        $host = $node->hasAttribute('host') ? $node->getAttribute('host') : null;
        $schemes = $node->hasAttribute('schemes') ? preg_split('/[\s,\|]++/', $node->getAttribute('schemes'), -1, PREG_SPLIT_NO_EMPTY) : null;
        $methods = $node->hasAttribute('methods') ? preg_split('/[\s,\|]++/', $node->getAttribute('methods'), -1, PREG_SPLIT_NO_EMPTY) : null;

        list($defaults, $requirements, $options, $condition, $localesWithPaths) = $this->parseConfigs($node, $path);

        $this->setCurrentDir(dirname($path));

        $subCollection = $this->import($resource, ('' !== $type ? $type : null), false, $file);
        /* @var $subCollection \Symfony\Component\Routing\RouteCollection */
        $subCollection = $this->routeGenerator->generateCollection(
            empty($localesWithPaths) ? $prefix : $localesWithPaths,
            $subCollection
        );
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
     * Loads an XML file.
     *
     * @param string $file An XML file path
     *
     * @return \DOMDocument
     *
     * @throws \InvalidArgumentException When loading of XML file fails because of syntax errors
     *                                   or when the XML structure is not as expected by the scheme -
     *                                   see validate()
     */
    protected function loadFile($file)
    {
        return XmlUtils::loadFile($file, __DIR__ . static::SCHEME_PATH);
    }

    /**
     * Parses the config elements (default, requirement, option).
     *
     * @param \DOMElement $node Element to parse that contains the configs
     * @param string      $path Full path of the XML file being processed
     *
     * @return array An array with the defaults as first item, requirements as second and options as third.
     *
     * @throws \InvalidArgumentException When the XML is invalid
     */
    private function parseConfigs(\DOMElement $node, $path)
    {
        $defaults = array();
        $requirements = array();
        $options = array();
        $condition = null;
        $locales = array();

        foreach ($node->getElementsByTagNameNS(self::NAMESPACE_URI, '*') as $n) {
            switch ($n->localName) {
                case 'default':
                    if ($this->isElementValueNull($n)) {
                        $defaults[$n->getAttribute('key')] = null;
                    } else {
                        $defaults[$n->getAttribute('key')] = trim($n->textContent);
                    }

                    break;
                case 'requirement':
                    $requirements[$n->getAttribute('key')] = trim($n->textContent);
                    break;
                case 'option':
                    $options[$n->getAttribute('key')] = trim($n->textContent);
                    break;
                case 'condition':
                    $condition = trim($n->textContent);
                    break;
                case 'locale':
                    $locales[$n->getAttribute('key')] = trim((string) $n->nodeValue);
                    break;
                default:
                    throw new \InvalidArgumentException(sprintf('Unknown tag "%s" used in file "%s". Expected "default", "requirement" or "option".', $n->localName, $path));
            }
        }

        return array($defaults, $requirements, $options, $condition, $locales);
    }

    private function isElementValueNull(\DOMElement $element)
    {
        $namespaceUri = 'http://www.w3.org/2001/XMLSchema-instance';

        if (!$element->hasAttributeNS($namespaceUri, 'nil')) {
            return false;
        }

        return 'true' === $element->getAttributeNS($namespaceUri, 'nil') || '1' === $element->getAttributeNS($namespaceUri, 'nil');
    }
}
