<?php
declare(strict_types=1);

namespace Voronoy\PgUtils\Test\Fixture;

use Cake\Datasource\ConnectionInterface;
use Cake\TestSuite\Fixture\TestFixture;

class ArraysFixture extends TestFixture
{
    public $fields = [
        'id' => ['type' => 'integer', 'autoIncrement' => true],
        'txt1' => ['type' => 'string', 'null' => true],
        'txt2' => ['type' => 'string', 'null' => true],
        'int1' => ['type' => 'string', 'null' => true],
        'f1' => ['type' => 'string', 'null' => true],
        'bool1' => ['type' => 'string', 'null' => true],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ];

    public $records = [
        [
            'txt1' => '{blah,blah blah,123,"",NULL}',
            'txt2' => '{{{1,""},{test,\'br}},{{"\"q",6},{7,8}}}',
            'int1' => '{1,2,3,4,5}',
            'f1' => '{1.21,2.0,3}',
            'bool1' => '{true,false,1,f,null,0, TRUE}',
        ],
    ];

    /**
     * @inheritDoc
     */
    public function create(ConnectionInterface $db): bool
    {
        if (parent::create($db)) {
            $db->prepare('alter table arrays alter column txt1 type text[] using txt1::text[]')->execute();
            $db->prepare('alter table arrays alter column txt2 type text[][] using txt2::text[][]')->execute();
            $db->prepare('alter table arrays alter column int1 type int[] using int1::int[]')->execute();
            $db->prepare('alter table arrays alter column f1 type float[] using f1::int[]')->execute();
            $db->prepare('alter table arrays alter column bool1 type bool[] using bool1::bool[]')->execute();

            return true;
        }

        return false;
    }
}
