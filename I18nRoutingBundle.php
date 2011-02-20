<?php

namespace BeSimple\I18nRoutingBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use BeSimple\I18nRoutingBundle\DependencyInjection\Compiler\OverrideRoutingCompilerPass;

class I18nRoutingBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new OverrideRoutingCompilerPass());

        parent::build($container);
    }
}
