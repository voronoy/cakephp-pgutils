<?php
declare(strict_types=1);

namespace Voronoy\PgUtils\Database\Type;

use Cake\Database\DriverInterface;
use function Voronoy\PgUtils\parse_pg_array;

class FloatArrayType extends ArrayType
{
    /**
     * Convert float array values to PHP array
     *
     * @param mixed                          $value  The value to convert.
     * @param \Cake\Database\DriverInterface $driver The driver instance to convert with.
     * @return int[]|null
     */
    public function toPHP($value, DriverInterface $driver)
    {
        return parse_pg_array($value, 'floatval');
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
            $values[$field] = parse_pg_array($values[$field], 'floatval');
        }

        return $values;
    }
}
