<?php
namespace BeSimple\I18nRoutingBundle\Routing\Loader;

use BeSimple\I18nRoutingBundle\Routing\I18nRouteCollection;
use BeSimple\I18nRoutingBundle\Routing\I18nRouteCollectionBuilder;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\Loader\XmlFileLoader as BaseXmlFileLoader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Config\Util\XmlUtils;

/**
 * XmlFileLoader
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Francis Besset <francis.besset@gmail.com>
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class XmlFileLoader extends BaseXmlFileLoader
{
    const NAMESPACE_URI = 'http://besim.pl/schema/i18n_routing';

    /**
     * @var I18nRouteCollectionBuilder
     */
    protected $collectionBuilder;

    public function __construct(FileLocatorInterface $locator, I18nRouteCollectionBuilder $collectionBuilder = null)
    {
        parent::__construct($locator);

        if ($collectionBuilder === null) {
            $collectionBuilder = new I18nRouteCollectionBuilder();
        }
        $this->collectionBuilder = $collectionBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function load($file, $type = null)
    {
        $path = $this->locator->locate($file);

        $xml = $this->loadFile($path);

        $collection = new I18nRouteCollection();
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'be_simple_i18n' === $type && 'xml' === pathinfo($resource, PATHINFO_EXTENSION);
    }

    /**
     * {@inheritdoc}
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

        list($defaults, $requirements, $options, $localesWithPaths) = $this->parseConfigs($node, $path);

        if ($localesWithPaths) {
            $collection->addCollection(
                $this->collectionBuilder->buildCollection($id, $localesWithPaths, $defaults, $requirements, $options, $node->getAttribute('host'), $schemes, $methods)
            );
        } else {

            if (!$node->hasAttribute('pattern') && !$node->hasAttribute('path')) {
                throw new \InvalidArgumentException(sprintf('The <route> element in file "%s" must have an "path" attribute.', $path));
            }

            $route = new Route($node->getAttribute('path'), $defaults, $requirements, $options, $node->getAttribute('host'), $schemes, $methods);
            $collection->add($id, $route);
        }
    }

    /**
     * {@inheritdoc}
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
        $locales = array();

        foreach ($node->getElementsByTagNameNS(self::NAMESPACE_URI, '*') as $n) {
            /** @var \DOMElement $n */
            switch ($n->localName) {
                case 'default':
                    if ($n->hasAttribute('xsi:nil') && 'true' == $n->getAttribute('xsi:nil')) {
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
                case 'locale':
                    $locales[$n->getAttribute('key')] = trim((string) $n->nodeValue);
                    break;
                default:
                    throw new \InvalidArgumentException(sprintf('Unknown tag "%s" used in file "%s". Expected "default", "requirement", "option" or "locale".', $n->localName, $path));
            }
        }

        return array($defaults, $requirements, $options, $locales);
    }

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

        list($defaults, $requirements, $options, $prefixes) = $this->parseConfigs($node, $path);

        $this->setCurrentDir(dirname($path));

        $subCollection = $this->import($resource, ('' !== $type ? $type : null), false, $file);
        /* @var $subCollection RouteCollection */
        $subCollection->addPrefix(empty($prefixes) ? $prefix : $prefixes);
        if (null !== $host) {
            $subCollection->setHost($host);
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
}
