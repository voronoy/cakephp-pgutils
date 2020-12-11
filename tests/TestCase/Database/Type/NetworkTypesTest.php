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
    protected $fixtures = ['plugin.Voronoy/PgUtils.Networks'];

    public function setUp(): void
    {
        parent::setUp();
        $this->getTableLocator()->clear();
        $this->Networks = $this->getTableLocator()->get(
            'Networks',
            [
                'className' => 'Voronoy\PgUtils\Test\TestApp\Model\Table\NetworksTable',
            ]
        );
    }

    public function testMacaddrTypes()
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
        $this->assertEquals(
            $expected,
            $this->Networks->find()
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

    public function testInetType()
    {
        $invalidValues = [
            'string',
            '192.168.0',
            '192.168.0.1/64',
            '2001:4f8:3',
            '2001:4f8:3:ba:2e0:81ff:fe22:d1f1/130',
        ];
        foreach ($invalidValues as $value) {
            $entity = $this->Networks->newEntity(['ip' => $value]);
            try {
                $this->Networks->save($entity);
            } catch (\Exception $e) {
                $this->assertTextContains('not a properly formatted inet type', $e->getMessage());
            }
        }
        $this->Networks->deleteAll([]);
        $data = [
            ['ip' => null],
            ['ip' => '192.168.100.128'],
            ['ip' => '10.1.2.3/8'],
            ['ip' => '2001:4f8:3:ba::'],
            ['ip' => '2001:4f8:3:ba:2e0:81ff:fe22:d1f1'],
            ['ip' => '::ffff:1.2.3.0/16'],
            ['ip' => '::ffff:1.2.3.0'],
        ];
        $entities = $this->Networks->newEntities($data);
        $this->Networks->saveMany($entities);
        $this->assertEquals(
            $data,
            $this->Networks->find()
                           ->select(['ip'])
                           ->disableHydration()
                           ->all()
                           ->toArray()
        );
    }
}
