<?php
declare(strict_types=1);

namespace Voronoy\PgUtils\Test\TestCase\Model\Behavior;

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
    public $fixtures = ['plugin.Voronoy/PgUtils.Articles'];

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
            ['id' => 1, 'title' => 'Article 1 Mod'],
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
            ['external_id' => 1, 'author_id' => 1, 'title' => 'Article 1 Mod 2'],
            ['external_id' => 2, 'author_id' => 1, 'title' => 'Article 2'],
            ['external_id' => 1, 'author_id' => 2, 'title' => 'Article 6'],
        ];
        $this->Articles->bulkUpsert($records2);
        $this->assertEquals(6, $this->Articles->find()->count());
        $this->assertEquals('Article 1 Mod 2', $this->Articles->findByExternalIdAndAuthorId(1, 1)->first()->title);

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
    }


}
