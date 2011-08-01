<?php

namespace BeSimple\I18nRoutingBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
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

        $this->addClassesToCompile(array(
            'BeSimple\\I18nRoutingBundle\\Routing\\Router',
        ));

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (isset($config['attribute_translator'])) {
            switch ($config['attribute_translator']['type']) {
                case 'service':
                    $container->setAlias('be_simple_i18n_routing.translator', $config['attribute_translator']['id']);
                    break;

                case 'doctrine_dbal':
                    $loader->load('dbal.xml');
                    $this->configureCacheDefinition($config['cache'], $container);
                    $container->setAlias('be_simple_i18n_routing.translator', 'be_simple_i18n_routing.translator.doctrine_dbal');

                    $attributes = array('event' => 'postGenerateSchema');
                    if (null !== $config['connection']) {
                        $attributes['connection'] = $config['connection'];
                    }
                    $def = $container->getDefinition('be_simple_i18n_routing.translator.doctrine_dbal.schema_listener');
                    $def->addTag('doctrine.event_listener', $attributes);
                    break;

                case 'translator':
                    $container->setAlias('be_simple_i18n_routing.translator', 'be_simple_i18n_routing.translator.translation');
                    break;

                default:
                    throw new \InvalidArgumentException(sprintf('Unsupported attribute translator type "%s"', $config['attribute_translator']['type']));
            }
        }
    }

    /**
     * Configures the Doctrine cache definition
     *
     * @param array $cacheDriver
     * @param ContainerBuilder $container
     */
    private function configureCacheDefinition(array $cacheDriver, ContainerBuilder $container)
    {
        switch ($cacheDriver['type']) {
            case 'memcache':
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
            case 'apc':
            case 'array':
            case 'xcache':
                $container->setAlias('be_simple_i18n_routing.doctrine_dbal.cache', sprintf('be_simple_i18n_routing.doctrine_dbal.cache.%s', $cacheDriver['type']));
                break;
            default:
                throw new \InvalidArgumentException(sprintf('"%s" is an unrecognized Doctrine cache driver.', $cacheDriver['type']));
        }

        // generate a unique namespace for the given application
        $container->setParameter('be_simple_i18n_routing.doctrine_dbal.cache.namespace', 'be_simple_i18n_'.md5($container->getParameter('kernel.root_dir')));
    }
}
