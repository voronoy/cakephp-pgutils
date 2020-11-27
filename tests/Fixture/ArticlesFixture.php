<?php
declare(strict_types=1);

namespace Voronoy\PgUtils\Test\Fixture;

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
        ],
        [
            'external_id' => 2,
            'author_id' => 1,
            'title' => 'Article 2',
            'body' => 'Article 2 Body',
            'created' => '2017-09-01 00:00:00',
        ],
        [
            'external_id' => 3,
            'author_id' => 1,
            'title' => 'Article 3',
            'body' => 'Article 3 Body',
            'created' => '2017-09-01 00:00:00',
        ],
    ];
}
