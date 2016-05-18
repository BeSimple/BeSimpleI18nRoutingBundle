<?php
namespace BeSimple\I18nRoutingBundle\Tests\DependencyInjection\Compiler;

use BeSimple\I18nRoutingBundle\DependencyInjection\Compiler\OverrideRoutingCompilerPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class OverrideRoutingCompilerPassTest extends AbstractCompilerPassTestCase
{
    /**
     * @inheritdoc
     */
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new OverrideRoutingCompilerPass());
    }

    /**
     * @test
     */
    public function it_requires_a_framework_router()
    {
        $this->setDefinition('be_simple_i18n_routing.router', new Definition());

        $this->setExpectedException('Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException');
        
        $this->compile();
    }

    /**
     * @test
     */
    public function it_should_override_the_router_and_set_parent_when_router_is_a_alias()
    {
        $this->setDefinition('be_simple_i18n_routing.router', new Definition());

        $this->setDefinition('aliased_router', new Definition());
        $this->container->setAlias('router', 'aliased_router');

        $this->compile();

        $this->assertContainerBuilderHasAlias('router', 'be_simple_i18n_routing.router');
        $this->assertContainerBuilderHasAlias('be_simple_i18n_routing.router.parent', 'aliased_router');
    }

    /**
     * @test
     */
    public function it_should_override_the_router_and_set_parent_when_router_is_a_definition()
    {
        $this->setDefinition('be_simple_i18n_routing.router', new Definition());

        $parentDef = new Definition(__CLASS__);
        $this->setDefinition('router', $parentDef);

        $this->compile();

        $this->assertContainerBuilderHasAlias('router', 'be_simple_i18n_routing.router');
        $this->assertContainerBuilderHasService('be_simple_i18n_routing.router.parent', __CLASS__);
    }
}
