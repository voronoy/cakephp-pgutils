<?php
declare(strict_types=1);

namespace Voronoy\PgUtils\Database\Type;

use Cake\Core\StaticConfigTrait;
use Cake\Database\DriverInterface;
use Cake\Database\Expression\FunctionExpression;
use Cake\Database\ExpressionInterface;
use Cake\Database\Type\BaseType;
use Cake\Database\Type\ExpressionTypeInterface;
use Voronoy\PgUtils\Database\GeoPoint;

class GeoPointType extends BaseType implements ExpressionTypeInterface
{
    use StaticConfigTrait;

    /**
     * @inheritDoc
     */
    public function toDatabase($value, DriverInterface $driver)
    {
        return $value;
    }

    /**
     * @inheritDoc
     */
    public function toPHP($value, DriverInterface $driver)
    {
        if ($value === null) {
            return null;
        }

        return GeoPoint::parse($value);
    }

    /**
     * Marshals flat data into GeoPoint object.
     *
     * @param mixed $value The value to convert.
     * @return \Voronoy\PgUtils\Database\GeoPoint|null Converted value.
     */
    public function marshal($value): ?GeoPoint
    {
        if ($value instanceof GeoPoint) {
            return $value;
        }
        if (is_array($value) && isset($value['lng'], $value['lat'])) {
            return new GeoPoint((float)$value['lng'], (float)$value['lat']);
        }
        if (is_string($value)) {
            $value = explode(',', $value);
        }
        if (is_array($value) && isset($value[0], $value[1])) {
            return new GeoPoint((float)$value[0], (float)$value[1]);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function toExpression($value): ExpressionInterface
    {
        $value = $this->marshal($value);
        if ($value instanceof GeoPoint) {
            $lng = $value->lng();
            $lat = $value->lat();
        } else {
            $lng = null;
            $lat = null;
        }

        return new FunctionExpression($this->_schemaPrefix('ST_SetSRID'), [
            new FunctionExpression($this->_schemaPrefix('ST_Point'), [$lng, $lat]),
            static::getConfig('srid') ?? 4326,
        ]);
    }

    /**
     * Returns function name with schema prefix.
     *
     * @param string $function Function name.
     * @return string
     */
    protected function _schemaPrefix(string $function): string
    {
        $schema = static::getConfig('schema');
        if (!$schema) {
            return $function;
        }

        return $schema . '.' . $function;
    }
}
