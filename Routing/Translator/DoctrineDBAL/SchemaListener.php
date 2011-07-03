<?php

namespace BeSimple\I18nRoutingBundle\Routing\Translator\DoctrineDBAL;

use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Doctrine\DBAL\Schema\Schema;

class SchemaListener
{
    public function postGenerateSchema(GenerateSchemaEventArgs $eventArgs)
    {
        $schema = $eventArgs->getSchema();
        $this->addRoutingTranslationsTable($schema);
    }
    
    public function addRoutingTranslationsTable(Schema $schema)
    {   
        $table = $schema->createTable('routing_translations');
        $table->addColumn('id', 'integer');
        $table->addColumn('route', 'string');
        $table->addColumn('locale', 'string');
        $table->addColumn('attribute', 'string');
        $table->addColumn('localized_value', 'string');
        $table->addColumn('original_value', 'string');
        $table->setPrimaryKey(array('id'));
        $table->addUniqueIndex(array('route', 'locale', 'attribute'));
        $table->addIndex(array('localized_value')); // this is much more selective than the unique index
    }
}