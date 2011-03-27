<?php

namespace BeSimple\I18nRoutingBundle\Routing\Loader;

use BeSimple\I18nRoutingBundle\Routing\I18nRoute;
use Symfony\Component\Routing\Loader\XmlFileLoader as BaseXmlFileLoader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class XmlFileLoader extends BaseXmlFileLoader
{
    /**
     * Parses a route and adds it to the RouteCollection.
     *
     * @param RouteCollection $collection A RouteCollection instance
     * @param \DOMElement     $definition Route definition
     * @param string          $file       An XML file path
     *
     * @throws \InvalidArgumentException When the definition cannot be parsed
     */
    protected function parseRoute(RouteCollection $collection, \DOMElement $definition, $file)
    {
        $defaults = array();
        $requirements = array();
        $options = array();
        $locales = array();

        foreach ($definition->childNodes as $node) {
            if (!$node instanceof \DOMElement) {
                continue;
            }

            switch ($node->tagName) {
                case 'default':
                    $defaults[(string) $node->getAttribute('key')] = trim((string) $node->nodeValue);
                 break;
                case 'option':
                    $options[(string) $node->getAttribute('key')] = trim((string) $node->nodeValue);
                    break;
                case 'requirement':
                    $requirements[(string) $node->getAttribute('key')] = trim((string) $node->nodeValue);
                    break;
                case 'locale':
                    $locales[(string) $node->getAttribute('key')] = trim((string) $node->nodeValue);
                    break;
                default:
                    throw new \InvalidArgumentException(sprintf('Unable to parse tag "%s"', $node->tagName));
            }
        }

        if ($locales) {
            $route = new I18nRoute((string) $definition->getAttribute('id'), $locales, $defaults, $requirements, $options);

            $collection->addCollection($route->getCollection());
        } else {
            $route = new Route((string) $definition->getAttribute('pattern'), $defaults, $requirements, $options);

            $collection->add((string) $definition->getAttribute('id'), $route);
        }
    }

    /**
     * Validates a loaded XML file.
     *
     * @param \DOMDocument $dom A loaded XML file
     *
     * @throws \InvalidArgumentException When XML doesn't validate its XSD schema
     */
    protected function validate(\DOMDocument $dom)
    {
        $parts = explode('/', str_replace('\\', '/', __DIR__.'/schema/routing/routing-1.0.xsd'));
        $drive = '\\' === DIRECTORY_SEPARATOR ? array_shift($parts).'/' : '';
        $location = 'file:///'.$drive.implode('/', $parts);

        $current = libxml_use_internal_errors(true);
        if (!$dom->schemaValidate($location)) {
            throw new \InvalidArgumentException(implode("\n", $this->getXmlErrors()));
        }
        libxml_use_internal_errors($current);
    }
}