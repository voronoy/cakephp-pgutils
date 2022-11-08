<?php
declare(strict_types=1);

namespace Voronoy\PgUtils\Test\TestCase\ORM;

use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\TestCase;
use Voronoy\PgUtils\Database\Schema\MaterializedViewCollection;

class MaterializedViewTest extends TestCase
{
    /**
     * @var \Voronoy\PgUtils\Test\TestApp\Model\Table\MatViewTable
     */
    public $MatView;

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
        $this->MatView = $this->getTableLocator()->get('MatView', [
            'className' => 'Voronoy\PgUtils\Test\TestApp\Model\Table\MatViewTable',
        ]);
    }

    public function testMatView()
    {
        $connection = ConnectionManager::get('test');
        $collection = new MaterializedViewCollection($connection);

        $views = $collection->listTables();
        $this->assertContains('mat_view', $views);
        $schema = $collection->describe('mat_view');
        $this->assertContains('grp', $schema->columns());
        $this->assertContains('avg', $schema->columns());
        $this->assertEquals('decimal', $schema->getColumnType('avg'));

        $this->MatView->refresh();
        $this->assertEquals(2, $this->MatView->find()->count());
        $entity = $this->MatView->find()->first();
        $entity->grp = 3;
        $this->assertEquals(false, $this->MatView->save($entity));
        $this->assertEquals(false, $this->MatView->delete($entity));
    }

    public function testExceptions()
    {
        $this->MatView->truncate();
        $this->expectException(\PDOException::class);
        $this->MatView->find()->count();
    }

    public function testMatViewCollection()
    {
        $class = new \ReflectionClass($this->MatView);
        $method = $class->getMethod('getSchemaCollection');
        $method->setAccessible(true);
        $this->MatView->getSchema();
        $this->assertInstanceOf(MaterializedViewCollection::class, $method->invoke($this->MatView));
    }
}
