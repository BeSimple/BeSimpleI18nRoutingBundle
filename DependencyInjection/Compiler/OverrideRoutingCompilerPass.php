<?php

namespace BeSimple\I18nRoutingBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OverrideRoutingCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->setParameter('routing.loader.xml.class', 'BeSimple\\I18nRoutingBundle\\Routing\\Loader\\XmlFileLoader');
        $container->setParameter('routing.loader.yml.class', 'BeSimple\\I18nRoutingBundle\\Routing\\Loader\\YamlFileLoader');

        $container->setAlias('router', 'i18n_routing.router');
    }
}
