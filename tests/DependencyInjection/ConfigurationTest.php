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
            array(
                'route_name_inflector' => 'be_simple_i18n_routing.route_name_inflector.postfix',
            )
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

    /**
     * @test
     */
    public function processed_configuration_for_locales_defaults()
    {
        $this->assertProcessedConfigurationEquals(
            array(
                array(
                    'locales' => array()
                )
            ),
            array(
                'locales' => array(
                    'default_locale' => null,
                    'supported' => array(),
                    'filter' => false,
                    'strict' => false
                )
            ),
            'locales'
        );
    }

    /**
     * @test
     */
    public function processed_configuration_for_locales_full()
    {
        $this->assertProcessedConfigurationEquals(
            array(
                array(
                    'locales' => array(
                        'default_locale' => 'en',
                        'supported' => array('en', 'nl', 'de'),
                        'filter' => true,
                        'strict' => true
                    )
                )
            ),
            array(
                'locales' => array(
                    'default_locale' => 'en',
                    'supported' => array('en', 'nl', 'de'),
                    'filter' => true,
                    'strict' => true
                )
            ),
            'locales'
        );
    }

    /**
     * @test
     */
    public function processed_configuration_for_locales_locale_string()
    {
        $this->assertProcessedConfigurationEquals(
            array(
                array(
                    'locales' => array(
                        'supported' => 'en',
                    )
                )
            ),
            array(
                'locales' => array(
                    'default_locale' => null,
                    'supported' => array('en'),
                    'filter' => false,
                    'strict' => false
                )
            ),
            'locales'
        );
    }
}
