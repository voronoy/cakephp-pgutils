<?php
declare(strict_types=1);

namespace Voronoy\PgUtils\Test\TestApp\Model\Table;

use Cake\Database\Schema\TableSchemaInterface;
use Cake\Database\Type;
use Cake\ORM\Table;
use Voronoy\PgUtils\Database\Type\ArrayType;
use Voronoy\PgUtils\Database\Type\IntArrayType;

class ArraysTable extends Table
{

    protected function _initializeSchema(TableSchemaInterface $schema): TableSchemaInterface
    {
        Type::map('array', ArrayType::class);
        Type::map('int_array', IntArrayType::class);
        $schema->setColumnType('txt1', 'array');
        $schema->setColumnType('txt2', 'array');
        $schema->setColumnType('int1', 'int_array');

        return $schema;
    }

}
