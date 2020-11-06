<?php
declare(strict_types=1);

namespace Voronoy\PgUtils\Test\TestApp\Model\Table;

use Cake\Database\Schema\TableSchemaInterface;
use Cake\ORM\Table;

class NetworksTable extends Table
{
    protected function _initializeSchema(TableSchemaInterface $schema): TableSchemaInterface
    {
        $schema->setColumnType('mac', 'macaddr');
        $schema->setColumnType('mac8', 'macaddr8');

        return $schema;
    }

}
