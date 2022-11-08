<?php
declare(strict_types=1);

namespace Voronoy\PgUtils\Test\Fixture;

use Cake\Datasource\ConnectionInterface;
use Cake\TestSuite\Fixture\TestFixture;

class GeosFixture extends TestFixture
{
    public $fields = [
        'id' => ['type' => 'integer', 'autoIncrement' => false],
        'pt' => ['type' => 'string', 'null' => true],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ];
    public $records = [
        [
            'id' => 1,
            'pt' => '0101000020E61000007880272D5C895DC00A9DD7D825EA4040',
        ],
        [
            'id' => 2,
            'pt' => null,
        ],
    ];

    /**
     * @inheritDoc
     */
    public function create(ConnectionInterface $db): bool
    {
        if (parent::create($db)) {
            $db->prepare('alter table geos alter column pt type geography using pt::geography')->execute();

            return true;
        }

        return false;
    }
}
