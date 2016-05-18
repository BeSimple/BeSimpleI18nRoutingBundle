<?php

namespace BeSimple\I18nRoutingBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class BeSimpleI18nRoutingExtension extends Extension
{
    /**
     * Loads the I18nRouting configuration.
     *
     * @param array            $configs   An array of array of configuration settings
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('routing.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->configureLocales($config, $container, $loader);
        $this->configureAttributeTranslator($config, $container, $loader);

        if (isset($config['route_name_inflector'])) {
            $container->setAlias('be_simple_i18n_routing.route_name_inflector', $config['route_name_inflector']);
        }

        $this->addClassesToCompile(array(
            'BeSimple\\I18nRoutingBundle\\Routing\\Router',
        ));
    }

    /**
     * Configures the attribute translator
     *
     * @param array $config
     * @param ContainerBuilder $container
     * @param LoaderInterface $loader
     */
    private function configureAttributeTranslator(array $config, ContainerBuilder $container, LoaderInterface $loader)
    {
        if (!isset($config['attribute_translator'])) {
            return;
        }
        $config = $config['attribute_translator'];

        switch ($config['type']) {
            case 'service':
                $container->setAlias('be_simple_i18n_routing.translator', $config['id']);
                return;

            case 'doctrine_dbal':
                $container->setParameter('be_simple_i18n_routing.doctrine_dbal.connection_name', $config['connection']);

                $loader->load('dbal.xml');

                // BC support for symfony factory
                $def = $container->getDefinition('be_simple_i18n_routing.doctrine_dbal.connection');

                if (method_exists($def, 'setFactory')) {
                    $def->setFactory(array(new Reference('doctrine'), 'getConnection'));
                } else {
                    $def->setFactoryService('doctrine')
                        ->setFactoryMethod('getConnection');
                }

                $this->configureDbalCacheDefinition($config['cache'], $container);
                $container->setAlias('be_simple_i18n_routing.translator', 'be_simple_i18n_routing.translator.doctrine_dbal');

                $attributes = array('event' => 'postGenerateSchema');
                if (null !== $config['connection']) {
                    $attributes['connection'] = $config['connection'];
                }
                $def = $container->getDefinition('be_simple_i18n_routing.translator.doctrine_dbal.schema_listener');
                $def->addTag('doctrine.event_listener', $attributes);
                return;

            case 'translator':
                $container->setAlias('be_simple_i18n_routing.translator', 'be_simple_i18n_routing.translator.translation');
                return;
        }

        throw new \InvalidArgumentException(sprintf('Unsupported attribute translator type "%s"', $config['type']));
    }

    /**
     * Configures the Doctrine cache definition
     *
     * @param array $cacheDriver
     * @param ContainerBuilder $container
     */
    private function configureDbalCacheDefinition(array $cacheDriver, ContainerBuilder $container)
    {
        if ($cacheDriver['type'] === 'memcache') {
            if (!empty($cacheDriver['class'])) {
                $container->setParameter('be_simple_i18n_routing.doctrine_dbal.cache.memcache.class', $cacheDriver['class']);
            }
            if (!empty($cacheDriver['instance_class'])) {
                $container->setParameter('be_simple_i18n_routing.doctrine_dbal.cache.memcache_instance.class', $cacheDriver['instance_class']);
            }
            if (!empty($cacheDriver['host'])) {
                $container->setParameter('be_simple_i18n_routing.doctrine_dbal.cache.memcache_host', $cacheDriver['host']);
            }
            if (!empty($cacheDriver['port'])) {
                $container->setParameter('be_simple_i18n_routing.doctrine_dbal.cache.memcache_port', $cacheDriver['port']);
            }
        }

        $container->setAlias('be_simple_i18n_routing.doctrine_dbal.cache', sprintf('be_simple_i18n_routing.doctrine_dbal.cache.%s', $cacheDriver['type']));

        // generate a unique namespace for the given application
        $container->setParameter('be_simple_i18n_routing.doctrine_dbal.cache.namespace', 'be_simple_i18n_'.md5($container->getParameter('kernel.root_dir')));
    }

    /**
     * Configures the supported locales
     *
     * @param array $config
     * @param ContainerBuilder $container
     * @param LoaderInterface $loader
     */
    private function configureLocales(array $config, ContainerBuilder $container, LoaderInterface $loader)
    {
        if (!isset($config['locales'])) {
            return;
        }
        $config = $config['locales'];

        $container->setParameter('be_simple_i18n_routing.default_locale', $config['default_locale']);
        $container->setParameter('be_simple_i18n_routing.locales', $config['supported']);

        // Configure the route generator
        $routeGenerator = 'be_simple_i18n_routing.route_generator.i18n';
        if ($config['strict'] !== false) {
            $container->getDefinition('be_simple_i18n_routing.route_generator.strict')
                ->replaceArgument(0, new Reference($routeGenerator));

            if ($config['strict'] === null) {
                $container->getDefinition('be_simple_i18n_routing.route_generator.strict')
                    ->addMethodCall('allowFallback', array(true));
            }

            $routeGenerator = 'be_simple_i18n_routing.route_generator.strict';
        }
        if ($config['filter']) {
            $container->getDefinition('be_simple_i18n_routing.route_generator.filter')
                ->replaceArgument(0, new Reference($routeGenerator));

            $routeGenerator = 'be_simple_i18n_routing.route_generator.filter';
        }

        $container->setAlias('be_simple_i18n_routing.route_generator', $routeGenerator);
    }
}
