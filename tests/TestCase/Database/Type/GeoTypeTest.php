<?php
/**
 * Copyright 2020 PulseCaster, Inc dba Serious Development. All Rights Reserved
 */

namespace Voronoy\PgUtils\Test\TestCase\Database\Type;


use Cake\TestSuite\TestCase;
use Voronoy\PgUtils\Database\GeoPoint;
use Voronoy\PgUtils\Test\TestApp\Model\Table\GeosTable;

class GeoTypeTest extends TestCase
{
    public GeosTable $Geo;

    protected $fixtures = ['plugin.Voronoy/PgUtils.Geos'];

    public function setUp(): void
    {
        parent::setUp();
        $this->getTableLocator()->clear();
        $this->Geo = $this->getTableLocator()->get('Geos',
            [
                'className' => 'Voronoy\PgUtils\Test\TestApp\Model\Table\GeosTable',
            ]);
    }

    public function testPoint()
    {
        $first = $this->Geo->get(1);
        $second = $this->Geo->get(2);
        $this->assertTrue($first->pt instanceof GeoPoint);
        $this->assertEquals(-118.146251, $first->pt->lng());
        $this->assertEquals(33.82928, $first->pt->lat());
        $this->assertEquals('{"id":1,"pt":{"lng":-118.146251,"lat":33.82928}}', json_encode($first));
        $this->assertNull($second->pt);

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
}
