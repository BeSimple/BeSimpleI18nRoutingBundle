<?php

namespace Bundle\I18nRoutingBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

class I18nRoutingExtension extends Extension
{
    /**
     * Loads the I18nRouting configuration.
     *
     * @param array            $configs    An array of array of configuration settings
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        foreach ($configs as $config) {
            if (isset($config['router'])) {
                $this->registerRouterConfiguration($config, $container);
            }
        }
    }

    protected function registerRouterConfiguration($config, ContainerBuilder $container)
    {
        if (!$container->hasDefinition('router')) {
            $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
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
            'Symfony\\Bundle\\FrameworkBundle\\Routing\\LazyLoader',
        ));
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