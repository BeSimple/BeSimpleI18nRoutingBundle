<?php

namespace BeSimple\I18nRoutingBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
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
                ->booleanNode('annotations')->defaultFalse()->end()
                ->scalarNode('route_name_inflector')->defaultValue('be_simple_i18n_routing.route_name_inflector.postfix')->end()
            ->end()
        ;

        $this->addLocalesSection($rootNode);
        $this->addAttributeTranslatorSection($rootNode);

        return $treeBuilder;
    }

    private function addLocalesSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('locales')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('default_locale')->defaultNull()->end()
                        ->arrayNode('supported')
                            ->treatNullLike(array())
                            ->beforeNormalization()
                                ->ifTrue(function ($v) { return !is_array($v); })
                                ->then(function ($v) { return array($v); })
                            ->end()
                            ->prototype('scalar')->end()
                        ->end()

                        ->booleanNode('filter')
                            ->defaultFalse()
                            ->info(
                                "set to true to filter out any unknown locales\n".
                                "set to false to disable filtering locales"
                            )
                        ->end()

                        ->scalarNode('strict')
                            ->defaultFalse()
                            ->validate()
                            ->ifTrue(function ($v) { return $v !== null && !is_bool($v); })
                                ->thenInvalid('Invalid type for path "strict". Expected boolean or null, but got %s.')
                            ->end()
                            ->info(
                                "set to true to throw a exception when a i18n route is found where the locale is unknown or where a locale is missing\n".
                                "set to false to disable exceptions so no locale missing or unknown exception are thrown\n".
                                "set to null to disable locale is missing for a route exception\n".
                                 "'true' is the preferred configuration in development mode, while 'false' or 'null' might be preferred in production"
                            )
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addAttributeTranslatorSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('attribute_translator')
                    ->canBeEnabled()
                    ->children()
                        ->enumNode('type')
                            ->defaultValue('translator')
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
    }
}
