<?php
declare(strict_types=1);

namespace Voronoy\PgUtils\Test\TestCase\Database\Type;

use Cake\Database\Type;
use Cake\TestSuite\TestCase;
use Voronoy\PgUtils\Test\TestApp\Model\Table\ArraysTable;
use function Voronoy\PgUtils\parse_pg_array;

class ArrayTypeTest extends TestCase
{

    public ArraysTable $Arrays;

    /**
     * Fixtures used by this test case.
     *
     * @var string[]
     */
    public $fixtures = ['plugin.Voronoy/PgUtils.Arrays'];

    public function setUp(): void
    {
        parent::setUp();
        $this->getTableLocator()->clear();
        $this->Arrays = $this->getTableLocator()->get('Arrays',
            [
                'className' => 'Voronoy\PgUtils\Test\TestApp\Model\Table\ArraysTable',
            ]);
    }

    public function testArray()
    {
        $first = $this->Arrays->find()->first();
        $this->assertTrue(is_array($first->txt1));
        $this->assertEquals(5, count($first->txt1));
        $this->assertNull($first->txt1[4]);
        $this->assertTrue(is_array($first->int1));
        $this->assertEquals(5, count($first->int1));
        $this->assertTrue($first->int1[0] === 1);

        $this->assertTrue(is_array($first->txt2));
        $this->assertEquals(2, count($first->txt2));
        $this->assertEquals('"q', $first->txt2[1][0][0]);
        $first->txt1 = ['update', 'with \'new values\''];
        $first->txt2 = [[['update', null]]];
        $this->Arrays->save($first);

        $second = $this->Arrays->newEntity([
            'txt1' => ['aa', 'bb'],
            'txt2' => null,
        ]);
        $this->Arrays->save($second);
        $second = $this->Arrays->get($second->id);
        $this->assertNull($second->txt2);
    }

    public function testNull()
    {
        $nulls = '{NULL,"null","","NULL"}';
        $actual = parse_pg_array($nulls);
        $this->assertNull($actual[0]);
        $this->assertEquals('null', $actual[1]);
        $this->assertEquals('', $actual[2]);
        $this->assertEquals('NULL', $actual[3]);
    }

    public function testManyToPHP()
    {
        $type = Type::build('array');
        $driver = $this->getMockBuilder('Cake\Database\Driver')->getMock();
        $values = [
            'a' => null,
            'b' => [1, 2, 3],
            'c' => '{1,2,3}',
            'd' => '{NULL,test}',
            'e' => '{{test},{test}}',
        ];
        $expected = [
            'a' => null,
            'b' => [1, 2, 3],
            'c' => [1, 2, 3],
            'd' => [null, 'test'],
            'e' => [['test'], ['test']],
        ];
        $actual = $type->manyToPHP($values, array_keys($values), $driver);
        $this->assertEquals(
            $expected,
            $actual
        );
    }

    public function testManyToPHPInt()
    {
        $type = Type::build('int_array');
        $driver = $this->getMockBuilder('Cake\Database\Driver')->getMock();
        $values = [
            'a' => null,
            'b' => [1, 2, 3],
            'c' => '{1,2,3}',
            'd' => '{{1},{2}}',
        ];
        $expected = [
            'a' => null,
            'b' => [1, 2, 3],
            'c' => [1, 2, 3],
            'd' => [[1], [2]],
        ];
        $actual = $type->manyToPHP($values, array_keys($values), $driver);
        $this->assertEquals(
            $expected,
            $actual
        );
        $this->assertTrue(is_int($actual['b'][1]));
    }

    public function testFloatArray()
    {
        $type = Type::build('float_array');
        $driver = $this->getMockBuilder('Cake\Database\Driver')->getMock();
        $values = [
            'a' => null,
            'b' => '{1.1,2.2}',
            'c' => '{{1.001},{2.002}}',
        ];
        $expected = [
            'a' => null,
            'b' => [1.1, 2.2],
            'c' => [[1.001], [2.002]],
        ];
        $actual = $type->manyToPHP($values, array_keys($values), $driver);
        $this->assertEquals(
            $expected,
            $actual
        );
        $this->assertTrue(is_float($actual['b'][1]));
        $this->assertEquals([3.1415], $type->toPHP('{3.1415000}', $driver));
    }

}
