<?php

namespace BeSimple\I18nRoutingBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
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
                        ->scalarNode('type')->isRequired()->end()
                        ->scalarNode('id')->end()
                        ->scalarNode('connection')->defaultNull()->end()
                        ->arrayNode('cache')
                            ->addDefaultsIfNotSet()
                            ->beforeNormalization()
                                ->ifString()
                                ->then(function($v) { return array('type' => $v); })
                            ->end()
                            ->children()
                                ->scalarNode('type')->defaultValue('array')->end()
                                ->scalarNode('host')->end()
                                ->scalarNode('port')->end()
                                ->scalarNode('instance_class')->end()
                                ->scalarNode('class')->end()
                            ->end()
                        ->end()
                    ->end()
                    ->validate()
                        ->ifTrue(function($v) { return 'service' === $v['type'] && !isset($v['id']); })
                        ->thenInvalid('The id has to be specified to use a service as attribute translator')
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
