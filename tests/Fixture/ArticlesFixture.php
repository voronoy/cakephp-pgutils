<?php
declare(strict_types=1);

namespace Voronoy\PgUtils\Test\Fixture;

use Cake\Datasource\ConnectionInterface;
use Cake\TestSuite\Fixture\TestFixture;

class ArticlesFixture extends TestFixture
{
    public $fields = [
        'id' => ['type' => 'integer', 'autoIncrement' => true],
        'external_id' => ['type' => 'integer'],
        'author_id' => ['type' => 'integer'],
        'title' => ['type' => 'string', 'length' => 255, 'null' => true],
        'body' => 'text',
        'created' => 'datetime',
        'modified' => 'datetime',
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
            'unique' => ['type' => 'unique', 'columns' => ['external_id', 'author_id']],
        ],
    ];

    public $records = [
        [
            'external_id' => 1,
            'author_id' => 1,
            'title' => 'Article 1',
            'body' => 'Article 1 Body',
            'created' => '2017-09-01 00:00:00',
            'modified' => '2017-09-01 00:00:00',
        ],
        [
            'external_id' => 2,
            'author_id' => 1,
            'title' => 'Article 2',
            'body' => 'Article 2 Body',
            'created' => '2017-09-01 00:00:00',
            'modified' => '2017-09-01 00:00:00',
        ],
        [
            'external_id' => 3,
            'author_id' => 1,
            'title' => 'Article 3',
            'body' => 'Article 3 Body',
            'created' => '2017-09-01 00:00:00',
            'modified' => '2017-09-01 00:00:00',
        ],
    ];

    /**
     * @inheritDoc
     */
    public function create(ConnectionInterface $db): bool
    {
        $sqls = [
            'CREATE TABLE matview_data (grp int, data numeric);',
            'INSERT INTO matview_data SELECT 1, random() FROM generate_series(1, 500);',
            'INSERT INTO matview_data SELECT 2, random() FROM generate_series(1, 500);',
            'CREATE MATERIALIZED VIEW mat_view AS SELECT grp, avg(data), count(*) FROM matview_data GROUP BY 1;',
            'CREATE MATERIALIZED VIEW mat_view2 AS SELECT grp, avg(data), count(*) FROM matview_data GROUP BY 1;',
        ];
        if (parent::create($db)) {
            foreach ($sqls as $sql) {
                $db->execute($sql);
            }

            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function drop(ConnectionInterface $db): bool
    {
        $sqls = [
            'DROP MATERIALIZED VIEW mat_view;',
            'DROP MATERIALIZED VIEW mat_view2;',
            'DROP TABLE matview_data;',
        ];
        foreach ($sqls as $sql) {
            $db->execute($sql);
        }

        return parent::drop($db);
    }
}
