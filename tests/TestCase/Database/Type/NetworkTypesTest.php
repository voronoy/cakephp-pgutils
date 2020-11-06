<?php
declare(strict_types=1);

namespace Voronoy\PgUtils\Test\TestCase\Database\Type;

use Cake\TestSuite\TestCase;
use Voronoy\PgUtils\Test\TestApp\Model\Table\NetworksTable;

class NetworkTypesTest extends TestCase
{

    public NetworksTable $Networks;

    private $records = [
        ['mac' => null, 'mac8' => null],
        ['mac' => '08:00:2b:01:02:03', 'mac8' => '08:00:2b:01:02:03:04:05'],
        ['mac' => '08-00-2b-01-02-03', 'mac8' => '08-00-2b-01-02-03-04-05'],
        ['mac' => '08002b:010203', 'mac8' => '08002b:0102030405'],
        ['mac' => '08002b-010203', 'mac8' => '08002b-0102030405'],
        ['mac' => '0800.2b01.0203', 'mac8' => '0800.2b01.0203.0405'],
        ['mac' => '0800-2b01-0203', 'mac8' => '0800-2b01-0203-0405'],
        ['mac' => '08002b010203', 'mac8' => '08002b0102030405'],
        ['mac' => null, 'mac8' => '08002b01:02030405'],
    ];

    /**
     * Fixtures used by this test case.
     *
     * @var string[]
     */
    public $fixtures = ['plugin.Voronoy/PgUtils.Networks'];

    public function setUp(): void
    {
        parent::setUp();
        $this->getTableLocator()->clear();
        $this->Networks = $this->getTableLocator()->get('Networks',
            [
                'className' => 'Voronoy\PgUtils\Test\TestApp\Model\Table\NetworksTable',
            ]);
    }

    public function testNetworkTypes()
    {

        $data = [
            ['mac' => '0800.2b01.0203', 'mac8' => null],
            ['mac' => null, 'mac8' => '0800.2b01.0203.0405'],
        ];
        $expected = [
            ['mac' => '08:00:2b:01:02:03', 'mac8' => null],
            ['mac' => null, 'mac8' => '08:00:2b:01:02:03:04:05'],
        ];
        $entities = $this->Networks->newEntities($data);
        $this->Networks->saveMany($entities);
        $this->assertEquals($expected, $this->Networks->find()
                                                      ->select(['mac', 'mac8'])
                                                      ->disableHydration()
                                                      ->all()
                                                      ->toArray()
        );
        $invalid = $this->Networks->newEntity(['mac' => '08:00:2b']);
        try {
            $this->Networks->save($invalid);
        } catch (\Exception $e) {
            $this->assertTextContains('not a properly formatted macaddr', $e->getMessage());
        }
        $invalid8 = $this->Networks->newEntity(['mac8' => '08:rr:00']);
        try {
            $this->Networks->save($invalid8);
        } catch (\Exception $e) {
            $this->assertTextContains('not a properly formatted macaddr8', $e->getMessage());
        }
        $allVariants = $this->Networks->newEntities($this->records);
        $result = $this->Networks->saveMany($allVariants);
        $this->assertEquals(9, count($result));

    }
}
