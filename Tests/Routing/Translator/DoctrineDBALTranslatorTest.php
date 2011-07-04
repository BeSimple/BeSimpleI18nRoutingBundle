<?php

namespace BeSimple\I18nRoutingBundle\Tests\Routing\Translator;

use BeSimple\I18nRoutingBundle\Routing\Translator\DoctrineDBALTranslator;
use BeSimple\I18nRoutingBundle\Routing\Translator\DoctrineDBAL\SchemaListener;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\DriverManager;

class DoctrineDBALTranslatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DoctrineDBALTranslator
     */
    private $translator;
    
    /**
     * @var DebugStack
     */
    private $logger;
    
    public function setUp()
    {
        if (!class_exists('Doctrine\DBAL\Version')) {
            $this->markTestSkipped('Only works when Doctrine DBAL is installed.');
        }
        
        $conn = DriverManager::getConnection(array(
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ));
        $this->logger = new \Doctrine\DBAL\Logging\DebugStack;
        $conn->getConfiguration()->setSQLLogger($this->logger);
        
        $cache = new ArrayCache;
        $this->translator = new DoctrineDBALTranslator($conn, $cache, false);
        $schema = new Schema();
        
        $schemaListener = new SchemaListener();
        $schemaListener->addRoutingTranslationsTable($schema);
        
        foreach ($schema->toSql($conn->getDatabasePlatform()) AS $sql) {
            $conn->exec($sql);
        }
    }
    
    public function testTranslate()
    {
        $this->translator->addTranslation('product_view', 'en', 'name', 'Cookie-Eater', 'Keks-Esser');
        $queryCount = count($this->logger->queries);
        
        $this->assertEquals('Keks-Esser', $this->translator->translate('product_view', 'en', 'name', 'Cookie-Eater'));
        $this->assertEquals($queryCount + 1 , count($this->logger->queries), "Query count should have increased by one.");
        
        // now from the cache
        $this->assertEquals('Keks-Esser', $this->translator->translate('product_view', 'en', 'name', 'Cookie-Eater'));
        $this->assertEquals($queryCount + 1 , count($this->logger->queries), "Query count should have increased by one.");
    }
 
    public function testTranslateUnknown()
    {
        $queryCount = count($this->logger->queries);
        
        $this->assertEquals('Cookie-Eater', $this->translator->translate('product_view', 'en', 'name', 'Cookie-Eater'));
        $this->assertEquals($queryCount + 1 , count($this->logger->queries), "Query count should have increased by one.");
        
        // now from the cache
        $this->assertEquals('Cookie-Eater', $this->translator->translate('product_view', 'en', 'name', 'Cookie-Eater'));
        $this->assertEquals($queryCount + 1 , count($this->logger->queries), "Query count should have increased by one.");
    }
    
    public function testReverseTranslate()
    {
        $this->translator->addTranslation('product_view', 'en', 'name', 'Cookie-Eater', 'Keks-Esser');
        $queryCount = count($this->logger->queries);
        
        $this->assertEquals('Cookie-Eater', $this->translator->reverseTranslate('product_view', 'en', 'name', 'Keks-Esser'));
        $this->assertEquals($queryCount + 1 , count($this->logger->queries), "Query count should have increased by one.");
        
        // now from the cache
        $this->assertEquals('Cookie-Eater', $this->translator->reverseTranslate('product_view', 'en', 'name', 'Keks-Esser'));
        $this->assertEquals($queryCount + 1 , count($this->logger->queries), "Query count should have increased by one.");
    }
 
    public function testReverseTranslateUnknown()
    {
        $queryCount = count($this->logger->queries);
        
        $this->assertEquals('Keks-Esser', $this->translator->reverseTranslate('product_view', 'en', 'name', 'Keks-Esser'));
        $this->assertEquals($queryCount + 1 , count($this->logger->queries), "Query count should have increased by one.");
        
        // now from the cache
        $this->assertEquals('Keks-Esser', $this->translator->reverseTranslate('product_view', 'en', 'name', 'Keks-Esser'));
        $this->assertEquals($queryCount + 1 , count($this->logger->queries), "Query count should have increased by one.");
    }
}