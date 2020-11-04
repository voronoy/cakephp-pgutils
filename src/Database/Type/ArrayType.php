<?php
declare(strict_types=1);

namespace Voronoy\PgUtils\Database\Type;

use Cake\Database\DriverInterface;
use Cake\Database\Type\BaseType;
use Cake\Database\Type\BatchCastingInterface;
use function Voronoy\PgUtils\parse_pg_array;
use function Voronoy\PgUtils\to_pg_array;

class ArrayType extends BaseType implements BatchCastingInterface
{
    /**
     * Convert array data into the database format.
     *
     * @param mixed                          $value  The value to convert.
     * @param \Cake\Database\DriverInterface $driver The driver instance to convert with.
     * @return string|null
     */
    public function toDatabase($value, DriverInterface $driver)
    {
        return to_pg_array($value);
    }

    /**
     * Convert array values to PHP array
     *
     * @param mixed                          $value  Value to be converted to PHP equivalent
     * @param \Cake\Database\DriverInterface $driver Object from which database preferences and configuration will be extracted
     * @return array|null
     */
    public function toPHP($value, DriverInterface $driver)
    {
        return parse_pg_array($value);
    }

    /**
     * Marshals flat data into PHP objects.
     *
     * @param mixed $value The value to convert.
     * @return array|null Converted value.
     */
    public function marshal($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (array)$value;
    }

    /**
     * @inheritDoc
     */
    public function manyToPHP(array $values, array $fields, DriverInterface $driver): array
    {
        foreach ($fields as $field) {
            if (!isset($values[$field]) || is_array($values[$field])) {
                continue;
            }
            $values[$field] = parse_pg_array($values[$field]);
        }

        return $values;
    }
}
