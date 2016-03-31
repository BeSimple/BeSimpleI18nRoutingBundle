<?php

namespace BeSimple\I18nRoutingBundle;

use BeSimple\I18nRoutingBundle\DependencyInjection\Compiler\OverrideRoutingCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BeSimpleI18nRoutingBundle extends Bundle
{
    const VERSION = '2.3.1-DEV';

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new OverrideRoutingCompilerPass());

        parent::build($container);
    }
}
