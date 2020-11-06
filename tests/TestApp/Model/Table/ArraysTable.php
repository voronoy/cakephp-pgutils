<?php
declare(strict_types=1);

namespace Voronoy\PgUtils\Test\TestApp\Model\Table;

use Cake\Database\Schema\TableSchemaInterface;
use Cake\ORM\Table;

class ArraysTable extends Table
{

    protected function _initializeSchema(TableSchemaInterface $schema): TableSchemaInterface
    {
        $schema->setColumnType('txt1', 'array');
        $schema->setColumnType('txt2', 'array');
        $schema->setColumnType('int1', 'int_array');

        return $schema;
    }

}
