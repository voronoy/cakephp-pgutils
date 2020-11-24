<?php
declare(strict_types=1);

namespace Voronoy\PgUtils\Database\Type;

use Cake\Database\DriverInterface;
use Cake\Database\Type\BaseType;

class MacAddrType extends BaseType
{
    /**
     * @inheritDoc
     */
    public function toDatabase($value, DriverInterface $driver)
    {
        if ($value === null) {
            return null;
        }
        // 00:00:00:00:00:00
        if (preg_match('/^((([0-9A-Fa-f]{2}):){5})([0-9A-Fa-f]{2})$/', $value)) {
            return $value;
        }
        // 00-00-00-00-00-00
        if (preg_match('/^((([0-9A-Fa-f]{2})-){5})([0-9A-Fa-f]{2})$/', $value)) {
            return $value;
        }
        // 000000:000000 or 000000-000000
        if (preg_match('/^([0-9A-Fa-f]{6})([:\-])([0-9A-Fa-f]{6})$/', $value)) {
            return $value;
        }
        // 0000.0000.0000
        if (preg_match('/^([0-9A-Fa-f]{4})\.([0-9A-Fa-f]{4})\.([0-9A-Fa-f]{4})$/', $value)) {
            return $value;
        }
        // 0000-0000-0000
        if (preg_match('/^([0-9A-Fa-f]{4})-([0-9A-Fa-f]{4})-([0-9A-Fa-f]{4})$/', $value)) {
            return $value;
        }
        // 000000000000
        if (preg_match('/^([0-9A-Fa-f]{12})$/', $value)) {
            return $value;
        }
        throw new \InvalidArgumentException(sprintf('%s is not a properly formatted macaddr type.', $value));
    }

    /**
     * @inheritDoc
     */
    public function toPHP($value, DriverInterface $driver)
    {
        return $value;
    }

    /**
     * @inheritDoc
     */
    public function marshal($value)
    {
        return $value;
    }
}
