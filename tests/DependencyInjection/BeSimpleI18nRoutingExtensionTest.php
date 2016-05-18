<?php
namespace BeSimple\I18nRoutingBundle\Tests\DependencyInjection;

use BeSimple\I18nRoutingBundle\DependencyInjection\BeSimpleI18nRoutingExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

class BeSimpleI18nRoutingExtensionTest extends AbstractExtensionTestCase
{
    protected function setUp()
    {
        parent::setUp();

        // Add Kernel parameter's
        $this->container->setParameter('kernel.root_dir', __DIR__);
    }

    /**
     * @inheritdoc
     */
    protected function getContainerExtensions()
    {
        return array(
            new BeSimpleI18nRoutingExtension(),
        );
    }

    /**
     * @test
     */
    public function loading_with_default_values()
    {
        $this->load();

        $this->assertContainerBuilderHasService('be_simple_i18n_routing.router');
        $this->assertContainerBuilderHasService('be_simple_i18n_routing.loader.xml');
        $this->assertContainerBuilderHasService('be_simple_i18n_routing.loader.yaml');

        $this->assertContainerBuilderHasParameter('be_simple_i18n_routing.router.class', 'BeSimple\I18nRoutingBundle\Routing\Router');
        $this->assertContainerBuilderHasParameter('be_simple_i18n_routing.loader.xml.class', 'BeSimple\I18nRoutingBundle\Routing\Loader\XmlFileLoader');
        $this->assertContainerBuilderHasParameter('be_simple_i18n_routing.loader.yaml.class', 'BeSimple\I18nRoutingBundle\Routing\Loader\YamlFileLoader');
        $this->assertContainerBuilderHasParameter('be_simple_i18n_routing.translator.translation.class', 'BeSimple\I18nRoutingBundle\Routing\Translator\TranslationTranslator');
    }

    /**
     * @test
     */
    public function load_attribute_translator_service()
    {
        $this->load(array(
            'attribute_translator' => array(
                'type' => 'service',
                'id' => 'my_translator',
            )
        ));

        $this->assertContainerBuilderHasAlias('be_simple_i18n_routing.translator', 'my_translator');
    }

    /**
     * @test
     */
    public function load_attribute_translator_translator()
    {
        $this->load(array(
            'attribute_translator' => array(
                'type' => 'translator',
            )
        ));

        $this->assertContainerBuilderHasAlias('be_simple_i18n_routing.translator', 'be_simple_i18n_routing.translator.translation');
    }

    /**
     * @test
     */
    public function load_attribute_translator_dbal()
    {
        $this->load(array(
            'attribute_translator' => array(
                'type' => 'doctrine_dbal',
                'cache' => array(
                    'type' => 'array'
                )
            )
        ));

        $this->assertContainerBuilderHasParameter('be_simple_i18n_routing.doctrine_dbal.connection_name', null);
        $this->assertContainerBuilderHasParameter('be_simple_i18n_routing.doctrine_dbal.cache.namespace');

        $this->assertContainerBuilderHasAlias('be_simple_i18n_routing.translator', 'be_simple_i18n_routing.translator.doctrine_dbal');
        $this->assertContainerBuilderHasAlias('be_simple_i18n_routing.doctrine_dbal.cache', 'be_simple_i18n_routing.doctrine_dbal.cache.array');

        $this->assertContainerBuilderHasService('be_simple_i18n_routing.doctrine_dbal.cache.array', 'Doctrine\Common\Cache\ArrayCache');
        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            'be_simple_i18n_routing.translator.doctrine_dbal.schema_listener',
            'doctrine.event_listener',
            array('event' => 'postGenerateSchema')
        );
    }
    /**
     * @test
     */
    public function load_attribute_translator_dbal_with_connection()
    {
        $this->load(array(
            'attribute_translator' => array(
                'type' => 'doctrine_dbal',
                'connection' => 'my_connection',
                'cache' => array(
                    'type' => 'array'
                )
            )
        ));

        $this->assertContainerBuilderHasParameter('be_simple_i18n_routing.doctrine_dbal.connection_name', 'my_connection');

        $this->assertContainerBuilderHasServiceDefinitionWithTag(
            'be_simple_i18n_routing.translator.doctrine_dbal.schema_listener',
            'doctrine.event_listener',
            array('event' => 'postGenerateSchema', 'connection' => 'my_connection')
        );
    }
}
