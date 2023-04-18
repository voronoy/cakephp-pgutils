<?php
declare(strict_types=1);

namespace Voronoy\PgUtils\Test\TestCase\Database\Type;

use Cake\TestSuite\TestCase;
use Voronoy\PgUtils\Database\GeoPoint;
use Voronoy\PgUtils\Database\Type\GeoPointType;

class GeoTypeTest extends TestCase
{
    /**
     * @var \Voronoy\PgUtils\Test\TestApp\Model\Table\GeosTable
     */
    public $Geo;

    protected $fixtures = ['plugin.Voronoy/PgUtils.Geos'];

    public function setUp(): void
    {
        parent::setUp();
        $this->getTableLocator()->clear();
        $this->Geo = $this->getTableLocator()->get(
            'Geos',
            [
                'className' => 'Voronoy\PgUtils\Test\TestApp\Model\Table\GeosTable',
            ]
        );
    }

    public function testPoint()
    {
        $first = $this->Geo->get(1);
        $second = $this->Geo->get(2);
        $this->assertTrue($first->get('pt') instanceof GeoPoint);
        $this->assertEquals(-118.146251, $first->get('pt')->lng());
        $this->assertEquals(33.82928, $first->get('pt')->lat());
        $this->assertEquals('{"id":1,"pt":{"lng":-118.146251,"lat":33.82928}}', json_encode($first));
        $this->assertNull($second->get('pt'));

        $lng = -118.017946;
        $lat = 33.954716;
        $entities = $this->Geo->newEntities([
            ['id' => 3, 'pt' => new GeoPoint($lng, $lat)],
            ['id' => 4, 'pt' => ['lng' => $lng, 'lat' => (string)$lat]],
            ['id' => 5, 'pt' => [$lng, (string)$lat]],
            ['id' => 6, 'pt' => "$lng,$lat"],
            ['id' => 7, 'pt' => null],
            ['id' => 8, 'pt' => 'invalid'],
        ]);
        $this->Geo->saveMany($entities);
        $records = $this->Geo->find()->where(['id >=' => 3])->all();
        $this->assertEquals(6, $records->count());
        foreach ($records as $record) {
            if ($record->id >= 7) {
                $this->assertNull($record->pt);
            } else {
                $this->assertTrue($record->pt instanceof GeoPoint);
                $this->assertEquals($lng, $record->pt->lng());
                $this->assertEquals($lat, $record->pt->lat());
            }
        }
    }

    public function testNonDefaultSchema()
    {
        GeoPointType::setConfig('schema', 'public');
        $lng = -118.017946;
        $lat = 33.954716;
        $entity = $this->Geo->newEntity(['id' => 6, 'pt' => "$lng,$lat"]);
        $this->Geo->save($entity);
        $record = $this->Geo->find()->where(['id' => 6])->first();
        $this->assertTrue($record->get('pt') instanceof GeoPoint);
        $this->assertEquals($lng, $record->get('pt')->lng());
        $this->assertEquals($lat, $record->get('pt')->lat());
    }
}
