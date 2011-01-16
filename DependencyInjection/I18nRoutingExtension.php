<?php

namespace Bundle\I18nRoutingBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class I18nRoutingExtension extends Extension
{
    /**
     * Loads the I18nRouting configuration.
     *
     * @param array            $config    An array of configuration settings
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function configLoad($config, ContainerBuilder $container)
    {
        if (isset($config['router'])) {
            $this->registerRouterConfiguration($config, $container);
        }
    }

    protected function registerRouterConfiguration($config, ContainerBuilder $container)
    {
        if (!$container->hasDefinition('router')) {
            $loader = new XmlFileLoader($container, __DIR__.'/../Resources/config');
            $loader->load('routing.xml');
        }

        $container->setParameter('routing.resource', $config['router']['resource']);

        $this->addClassesToCompile(array(
            'Symfony\\Component\\Routing\\RouterInterface',
            'Bundle\\I18nRoutingBundle\\Routing\\Router',
            'Symfony\\Component\\Routing\\Matcher\\UrlMatcherInterface',
            'Symfony\\Component\\Routing\\Matcher\\UrlMatcher',
            'Symfony\\Component\\Routing\\Generator\\UrlGeneratorInterface',
            'Symfony\\Component\\Routing\\Generator\\UrlGenerator',
            'Symfony\\Component\\Routing\\Loader\\LoaderInterface',
            'Symfony\\Bundle\\FrameworkBundle\\Routing\\LazyLoader',
        ));
    }

    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     */
    public function getXsdValidationBasePath()
    {
        return null;
    }

    /**
     * Returns the namespace to be used for this extension (XML namespace).
     *
     * @return string The XML namespace
     */
    public function getNamespace()
    {
        return 'http://www.symfony-project.org/schema/dic/symfony';
    }

    /**
     * Returns the recommended alias to use in XML.
     *
     * This alias is also the mandatory prefix to use when using YAML.
     *
     * @return string The alias
     */
    public function getAlias()
    {
        return 'i18n_routing';
    }
}