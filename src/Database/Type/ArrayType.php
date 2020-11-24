<?php
declare(strict_types=1);

namespace Voronoy\PgUtils\Database\Type;

use Cake\Database\DriverInterface;
use Cake\Database\Type\BaseType;
use Cake\Database\Type\BatchCastingInterface;
use Voronoy\PgUtils\Utility\PgArrayConverter;

class ArrayType extends BaseType implements BatchCastingInterface
{
    /**
     * @var string
     */
    protected string $type = 'text';

    /**
     * Convert array data into the database format.
     *
     * @param mixed                          $value  The value to convert.
     * @param \Cake\Database\DriverInterface $driver The driver instance to convert with.
     * @return string|null
     * @throws \Voronoy\PgUtils\Exception\PgArrayInvalidParam
     */
    public function toDatabase($value, DriverInterface $driver)
    {
        return PgArrayConverter::toPg($value, $this->type);
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
        return PgArrayConverter::fromPg($value, $this->type);
    }

    /**
     * @inheritDoc
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
            $values[$field] = PgArrayConverter::fromPg($values[$field], $this->type);
        }

        return $values;
    }
}
