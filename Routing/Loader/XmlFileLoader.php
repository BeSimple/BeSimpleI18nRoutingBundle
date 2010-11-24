<?php

namespace Bundle\I18nRoutingBundle\Routing\Loader;

use Symfony\Component\Routing\Loader\XmlFileLoader as BaseXmlFileLoader;
use Symfony\Component\Routing\RouteCollection;
use Bundle\I18nRoutingBundle\Routing as Routing;

class XmlFileLoader extends BaseXmlFileLoader
{
    protected function parseRoute(RouteCollection $collection, $definition, $file)
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
            $route = new Routing\I18nRoute((string) $definition->getAttribute('id'), $locales, $defaults, $requirements, $options);

            $collection->addCollection($route->getCollection());
        } else {
            $route = new Routing\Route((string) $definition->getAttribute('pattern'), $defaults, $requirements, $options);

            $collection->addRoute((string) $definition->getAttribute('id'), $route);
        }
    }

    /**
     * @throws \InvalidArgumentException When xml doesn't validate its xsd schema
     */
    protected function validate(\DOMDocument $dom, $file)
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