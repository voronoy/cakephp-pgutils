<?php
declare(strict_types=1);

namespace Voronoy\PgUtils\Database;

class GeoPoint implements \JsonSerializable
{
    /**
     * @var float
     */
    protected $_lng;
    /**
     * @var float
     */
    protected $_lat;

    /**
     * Parse the WKB data from PostgreSQL.
     *
     * @param string $value WKB string
     * @return static
     */
    public static function parse(string $value)
    {
        $data = pack('H*', $value);
        $unpacked = unpack('C/V/Vsrid/dlng/dlat', $data);
        if ($unpacked === false) {
            return null;
        }

        return new static($unpacked['lng'], $unpacked['lat']);
    }

    /**
     * GeoPoint constructor.
     *
     * @param float $lng Longitude
     * @param float $lat Latitude
     */
    public function __construct(float $lng, float $lat)
    {
        $this->_lng = $lng;
        $this->_lat = $lat;
    }

    /**
     * Latitude.
     *
     * @return float
     */
    public function lat(): float
    {
        return $this->_lat;
    }

    /**
     * Longitude.
     *
     * @return float
     */
    public function lng(): float
    {
        return $this->_lng;
    }

    /**
     * Returns the fields that will be serialized as JSON.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'lng' => $this->_lng,
            'lat' => $this->_lat,
        ];
    }
}
