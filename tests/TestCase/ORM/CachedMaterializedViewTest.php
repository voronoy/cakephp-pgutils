<?php
declare(strict_types=1);

namespace Voronoy\PgUtils\Test\TestCase\ORM;

use Cake\Core\Configure;
use Cake\Database\Schema\CachedCollection;
use Cake\TestSuite\TestCase;

class CachedMaterializedViewTest extends TestCase
{
    /**
     * @var \Voronoy\PgUtils\Test\TestApp\Model\Table\CachedMatViewTable
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
        $this->MatView = $this->getTableLocator()->get('CachedMatView', [
            'className' => 'Voronoy\PgUtils\Test\TestApp\Model\Table\CachedMatViewTable',
        ]);
    }

    public function testMatViewCollection()
    {
        pr(Configure::read());
        $class = new \ReflectionClass($this->MatView);
        $method = $class->getMethod('getSchemaCollection');
        $method->setAccessible(true);
        $this->MatView->getSchema();
        $this->assertInstanceOf(CachedCollection::class, $method->invoke($this->MatView));
    }
}
