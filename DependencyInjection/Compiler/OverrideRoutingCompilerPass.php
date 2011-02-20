<?php

namespace Bundle\I18nRoutingBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OverrideRoutingCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->setParameter('routing.loader.xml.class', 'Bundle\\I18nRoutingBundle\\Routing\\Loader\\XmlFileLoader');
        $container->setParameter('routing.loader.yml.class', 'Bundle\\I18nRoutingBundle\\Routing\\Loader\\YamlFileLoader');

        $container->setAlias('router', 'i18n_routing.router');
    }
}
