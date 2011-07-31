<?php

namespace BeSimple\I18nRoutingBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Alias;

class OverrideRoutingCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('be_simple_i18n_routing.router')) {
            return;
        }

        if ($container->hasAlias('router')) {
            // router is an alias.
            // Register a private alias for this service to inject it as the parent
            $container->setAlias('be_simple_i18n_routing.router.parent', new Alias((string) $container->getAlias('router'), false));
        } else {
            // router is a definition.
            // Register it again as a private service to inject it as the parent
            $definition = $container->getDefinition('router');
            $definition->setPublic(false);
            $container->setDefinition('be_simple_i18n_routing.router.parent', $definition);
        }

        $container->setAlias('router', 'be_simple_i18n_routing.router');
    }
}
