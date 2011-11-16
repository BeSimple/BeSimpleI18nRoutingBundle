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
            'BeSimple\\I18nRoutingBundle\\Routing\\Generator\\UrlGenerator',
            'BeSimple\\I18nRoutingBundle\\Routing\\Router',
        ));

        foreach ($configs as $config) {
            if (isset($config['connection'])) {
                if (!isset($config['cache'])) {
                    $cacheDef = new Definition('Doctrine\Common\Cache\ArrayCache');
                } else {
                    $cacheDef = $this->getCacheDefinition($config['cache'], $container);
                }
                $container->setDefinition('i18n_routing.doctrine.cache', $cacheDef);

                $def = new Definition(
                    '%i18n_routing.translator.doctrine.class%', array(
                        new Reference('doctrine.dbal.'. $config['connection'].'_connection'),
                        new Reference('i18n_routing.doctrine.cache'),
                    )
                );
                $def->setPublic(true); // public, we need it to add translations!
                $container->setDefinition('i18n_routing.translator', $def);

                $def = $container->getDefinition('i18n_routing.translator.doctrine.schemalistener');
                $def->addTag('doctrine.event_listener', array(
                    'connection' => $config['connection'],
                    'event'      => 'postGenerateSchema',
                ));
            } elseif (isset($config['use_translator']) && true === $config['use_translator']) {
                $def = new Definition(
                    '%i18n_routing.translator.translation.class%', array(
                        new Reference('translator'),
                    )
                );
                $def->setPublic(false);
                $container->setDefinition('i18n_routing.translator', $def);
            }
        }
    }

    /**
     * This is almost copied completly from DoctrineExtension::getEntityManagerCacheDefinition().
     *
     * @param type $cacheDriver
     * @param ContainerBuilder $container
     *
     * @return Definition
     */
    protected function getCacheDefinition($cacheDriver, ContainerBuilder $container)
    {
        switch ($cacheDriver['type']) {
            case 'memcache':
                $memcacheClass         = !empty($cacheDriver['class']) ? $cacheDriver['class'] : '%doctrine.orm.cache.memcache.class%';
                $memcacheInstanceClass = !empty($cacheDriver['instance_class']) ? $cacheDriver['instance_class'] : '%doctrine.orm.cache.memcache_instance.class%';
                $memcacheHost          = !empty($cacheDriver['host']) ? $cacheDriver['host'] : '%doctrine.orm.cache.memcache_host%';
                $memcachePort          = !empty($cacheDriver['port']) ? $cacheDriver['port'] : '%doctrine.orm.cache.memcache_port%';

                $cacheDef         = new Definition($memcacheClass);
                $memcacheInstance = new Definition($memcacheInstanceClass);
                $memcacheInstance->addMethodCall('connect', array(
                    $memcacheHost,
                    $memcachePort,
                ));
                $container->setDefinition('i18n_routing.doctrine.cache_memcache');
                $cacheDef->addMethodCall('setMemcache', array(new Reference('i18n_routing.doctrine.cache_memcache')));
                break;
            case 'apc':
            case 'array':
            case 'xcache':
                $cacheDef = new Definition('%'.sprintf('doctrine.orm.cache.%s.class', $cacheDriver['type']).'%');
                break;
            default:
                throw new \InvalidArgumentException(sprintf('"%s" is an unrecognized Doctrine cache driver.', $cacheDriver['type']));
        }

        $cacheDef->setPublic(false);
        // generate a unique namespace for the given application
        $namespace = 'i18n_'.md5($container->getParameter('kernel.root_dir'));
        $cacheDef->addMethodCall('setNamespace', array($namespace));

        return $cacheDef;
    }
}
