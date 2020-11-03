<?php
declare(strict_types=1);

namespace PgUtils\Database\Type;

use Cake\Database\DriverInterface;
use Cake\Database\Type\BaseType;
use Cake\Database\Type\BatchCastingInterface;
use function PgUtils\parse_pg_array;
use function PgUtils\to_pg_array;

class ArrayType extends BaseType implements BatchCastingInterface
{

    /**
     * Convert array data into the database format.
     *
     * @param mixed           $value  The value to convert.
     * @param DriverInterface $driver The driver instance to convert with.
     *
     * @return string|null
     */
    public function toDatabase($value, DriverInterface $driver)
    {
        return to_pg_array($value);
    }

    /**
     * Convert array values to PHP array
     *
     * @param mixed           $value  Value to be converted to PHP equivalent
     * @param DriverInterface $driver Object from which database preferences and configuration will be extracted
     *
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
     *
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
     * @TODO
     */
    public function manyToPHP(array $values, array $fields, DriverInterface $driver): array
    {
        foreach ($values as &$value) {
            $value = $this->toPHP($value, $driver);
        }

        return $values;
    }

}
