<?php
declare(strict_types=1);

namespace Voronoy\PgUtils\Database\Type;

use Cake\Database\DriverInterface;
use Cake\Database\Type\BaseType;

class InetType extends BaseType
{
    /**
     * @inheritDoc
     */
    public function toDatabase($value, DriverInterface $driver)
    {
        if ($value === null) {
            return null;
        }
        $position = strpos($value, '/');
        if ($position) {
            [$ip, $mask] = explode('/', $value, 2);
            if (
                filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) &&
                is_numeric($mask) && $mask <= 32 && $mask >= 0
            ) {
                return $value;
            }
            if (
                filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) &&
                is_numeric($mask) && $mask <= 128 && $mask >= 0
            ) {
                return $value;
            }
        } elseif (filter_var($value, FILTER_VALIDATE_IP)) {
            return $value;
        }
        throw new \InvalidArgumentException(sprintf('%s is not a properly formatted inet type.', $value));
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
