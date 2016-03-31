<?php

namespace BeSimple\I18nRoutingBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This class contains the configuration information for the bundle
 *
 * This information is solely responsible for how the different configuration
 * sections are normalized, and merged.
 *
 * @author Christophe Coevoet <stof@notk.org>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('be_simple_i18n_routing');

        $rootNode
            ->children()
                ->arrayNode('attribute_translator')
                    ->children()
                        ->enumNode('type')
                            ->isRequired()
                            ->values(array('service', 'doctrine_dbal', 'translator'))
                        ->end()
                        ->scalarNode('id')->end()
                        ->scalarNode('connection')->defaultNull()->end()
                        ->arrayNode('cache')
                            ->addDefaultsIfNotSet()
                            ->beforeNormalization()
                                ->ifString()
                                ->then(function ($value) {
                                    return array('type' => $value);
                                })
                            ->end()
                            ->children()
                                ->enumNode('type')
                                    ->defaultValue('array')
                                    ->values(array('memcache', 'apc', 'array', 'xcache'))
                                ->end()
                                ->scalarNode('host')->end()
                                ->scalarNode('port')->end()
                                ->scalarNode('instance_class')->end()
                                ->scalarNode('class')->end()
                            ->end()
                        ->end()
                    ->end()
                    ->validate()
                        ->ifTrue(function ($value) {
                            return 'service' === $value['type'] && !isset($value['id']);
                        })
                        ->thenInvalid('The id has to be specified to use a service as attribute translator')
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
