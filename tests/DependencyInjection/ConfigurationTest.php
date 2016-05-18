<?php
namespace BeSimple\I18nRoutingBundle\Tests\DependencyInjection;

use BeSimple\I18nRoutingBundle\DependencyInjection\Configuration;
use Matthias\SymfonyConfigTest\PhpUnit\AbstractConfigurationTestCase;

class ConfigurationTest extends AbstractConfigurationTestCase
{
    protected function getConfiguration()
    {
        return new Configuration();
    }

    /**
     * @test
     */
    public function processed_configuration_defaults()
    {
        $this->assertProcessedConfigurationEquals(
            array(),
            array()
        );
    }

    /**
     * @test
     */
    public function processed_configuration_for_attribute_translator_service_default()
    {
        $this->assertProcessedConfigurationEquals(
            array(
                array(
                    'attribute_translator' => array(
                        'type' => 'service',
                        'id' => 'test_service_id'
                    )
                )
            ),
            array(
                'attribute_translator' => array(
                    'type' => 'service',
                    'id' => 'test_service_id',
                    'connection' => null,
                    'cache' => array(
                        'type' => 'array',
                    )
                )
            ),
            'attribute_translator'
        );
    }

    /**
     * @test
     */
    public function processed_configuration_for_attribute_translator_service_requires_id()
    {
        $this->assertConfigurationIsInvalid(
            array(
                array(
                    'attribute_translator' => array(
                        'type' => 'service',
                    )
                )
            ),
            'attribute_translator'
        );
    }

    /**
     * @test
     */
    public function processed_configuration_for_attribute_translator_doctrine_dbal_default()
    {
        $this->assertProcessedConfigurationEquals(
            array(
                array(
                    'attribute_translator' => array(
                        'type' => 'doctrine_dbal'
                    )
                )
            ),
            array(
                'attribute_translator' => array(
                    'type' => 'doctrine_dbal',
                    'connection' => null,
                    'cache' => array(
                        'type' => 'array',
                    )
                )
            ),
            'attribute_translator'
        );
    }
}
