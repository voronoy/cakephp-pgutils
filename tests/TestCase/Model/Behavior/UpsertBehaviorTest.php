<?php
declare(strict_types=1);

namespace Voronoy\PgUtils\Test\TestCase\Model\Behavior;

use Cake\I18n\FrozenTime;
use Cake\ORM\Table;
use Cake\TestSuite\TestCase;

class UpsertBehaviorTest extends TestCase
{
    public Table $Articles;

    /**
     * Fixtures used by this test case.
     *
     * @var string[]
     */
    protected $fixtures = ['plugin.Voronoy/PgUtils.Articles'];

    public function setUp(): void
    {
        parent::setUp();
        $this->getTableLocator()->clear();
        $this->Articles = $this->getTableLocator()->get('Voronoy/PgUtils.Articles');
        $this->Articles->addBehavior('Voronoy/PgUtils.Upsert', [
            'uniqueKey' => ['external_id', 'author_id'],
            'updateColumns' => ['title', 'body'],
        ]);
    }

    public function testUpsert()
    {
        $result = $this->Articles->bulkUpsert([]);
        $this->assertNull($result);
        $records1 = [
            ['id' => 1, 'title' => 'Article 1 Mod', 'invalid_field1' => 12],
            ['title' => 'Article 4'],
            ['id' => null, 'title' => 'Article 5'],
        ];
        $this->Articles->bulkUpsert($records1, [
            'uniqueKey' => ['id'],
            'updateColumns' => ['title'],
        ]);
        $this->assertEquals(5, $this->Articles->find()->count());
        $this->assertEquals('Article 1 Mod', $this->Articles->get(1)->title);
        $records2 = [
            ['external_id' => 1, 'author_id' => 1, 'title' => 'Article 1 Mod 2', 'body' => 'Article 1 Body'],
            ['external_id' => 2, 'author_id' => 1, 'title' => 'Article 2'],
            ['external_id' => 1, 'author_id' => 2, 'title' => 'Article 6'],
        ];
        $this->Articles->bulkUpsert($records2);
        $this->assertEquals(6, $this->Articles->find()->count());
        $testRecord = $this->Articles->findByExternalIdAndAuthorId(1, 1)->first();
        $this->assertEquals('Article 1 Mod 2', $testRecord->title);
        $this->assertEquals('Article 1 Body', $testRecord->body);

        $statement = $this->Articles->bulkUpsert($records2, ['returning' => ['id']]);
        while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $this->assertArrayHasKey('id', $row);
        }

        $this->Articles->removeBehavior('Upsert');
        $this->Articles->addBehavior('Voronoy/PgUtils.Upsert');
        $this->Articles->bulkUpsert([
            ['id' => 1, 'title' => 'Article 1 Mod 3'],
        ]);
        $this->assertEquals(6, $this->Articles->find()->count());

        $this->Articles->bulkUpsert($records1, [
            'uniqueKey' => ['id'],
            'updateColumns' => ['title', 'invalid_field1'],
            'extra' => ['external_id' => 10, 'invalid_field2' => 10],
        ]);
        $first = $this->Articles->get(1);
        $this->assertEquals('Article 1 Mod', $first->title);
        $this->assertEquals(1, $first->external_id);

        $this->Articles->bulkUpsert($records1, [
            'uniqueKey' => ['id'],
            'updateColumns' => ['title', 'invalid_field1', 'external_id'],
            'extra' => ['external_id' => 10],
        ]);
        $first = $this->Articles->get(1);
        $this->assertEquals(10, $first->external_id);
    }

    public function testUpsert2()
    {
        $now = FrozenTime::now();
        $records2 = [
            ['external_id' => 2, 'author_id' => 1, 'title' => 'Article 2'],
            ['external_id' => 1, 'author_id' => 1, 'title' => 'Article 1 Mod 2', 'body' => 'MOD'],
            ['external_id' => 1, 'author_id' => 2, 'title' => 'Article 6', 'modified' => $now->addDay(-1)],
        ];
        $this->Articles->bulkUpsert($records2, [
            'updateColumns' => '*',
            'extra' => ['created' => $now, 'modified' => $now],
        ]);
        $this->assertEquals(4, $this->Articles->find()->count());
        $this->assertEquals('MOD', $this->Articles->findByExternalIdAndAuthorId(1, 1)->first()->body);
        $this->assertEquals($now, $this->Articles->findByExternalIdAndAuthorId(1, 1)->first()->modified);
        $this->assertEquals($now->addDay(-1), $this->Articles->findByExternalIdAndAuthorId(1, 2)->first()->modified);
    }
}
