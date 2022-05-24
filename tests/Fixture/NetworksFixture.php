<?php
declare(strict_types=1);

namespace Voronoy\PgUtils\Test\Fixture;

use Cake\Datasource\ConnectionInterface;
use Cake\TestSuite\Fixture\TestFixture;

class NetworksFixture extends TestFixture
{
    public $fields = [
        'id' => ['type' => 'integer', 'autoIncrement' => true],
        'mac' => ['type' => 'string', 'null' => true],
        'mac8' => ['type' => 'string', 'null' => true],
        'ip' => ['type' => 'string', 'null' => true],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ];

    /**
     * @inheritDoc
     */
    public function create(ConnectionInterface $db): bool
    {
        if (parent::create($db)) {
            $db->prepare('alter table networks alter column mac type macaddr using mac::macaddr')->execute();
            $db->prepare('alter table networks alter column mac8 type macaddr8 using mac8::macaddr8')->execute();
            $db->prepare('alter table networks alter column ip type inet using ip::inet')->execute();

            return true;
        }

        return false;
    }
}
