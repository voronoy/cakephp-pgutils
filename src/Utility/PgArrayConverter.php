<?php
declare(strict_types=1);

namespace Voronoy\PgUtils\Utility;

use Voronoy\PgUtils\Exception\PgArrayInvalidParam;

class PgArrayConverter
{
    /**
     * Convert PostgreSQL array to PHP array.
     *
     * @param string|null $data Data to convert
     * @param string $type Array type
     * @return array|null
     */
    public static function fromPg(?string $data, string $type = 'text'): ?array
    {
        if (empty($data) || $data[0] !== '{') {
            return null;
        }
        $return = [];
        $depth = 0;
        $length = strlen($data);
        for ($i = 0; $i < $length; $i++) {
            if ($data[$i] === '{') {
                $depth++;
            } else {
                break;
            }
        }
        if ($depth >= 2) {
            $closeBraces = str_repeat('}', $depth - 1);
            $openBraces = str_repeat('{', $depth - 1);
            $delimiter = $closeBraces . ',' . $openBraces;
            $string = substr($data, $depth, -$depth);
            $parts = explode($delimiter, $string);
            foreach ($parts as $part) {
                $return[] = static::fromPg($openBraces . $part . $closeBraces, $type);
            }
        } else {
            $parsed = str_getcsv(trim($data, '{}'));
            if ($type === 'text') {
                $checkNulls = str_getcsv(trim($data, '{}'), ',', "\u{2007}");
            }
            foreach ($parsed as $i => $value) {
                if ($value === null) {
                    $return[] = null;
                } elseif ($value === 'NULL' && $type !== 'text') {
                    $return[] = null;
                } elseif ($value === 'NULL' && !empty($checkNulls[$i]) && $checkNulls[$i] === 'NULL') {
                    $return[] = null;
                } else {
                    switch ($type) {
                        case 'bool':
                        case 'boolean':
                            $return[] = $value === 't';
                            break;
                        case 'int':
                        case 'integer':
                            $return[] = (int)$value;
                            break;
                        case 'float':
                        case 'numeric':
                            $return[] = (float)$value;
                            break;
                        case 'text':
                        default:
                            $return[] = stripcslashes($value);
                            break;
                    }
                }
            }
        }

        return $return;
    }

    /**
     * Convert PHP array to PostgreSQL array.
     *
     * @param array|null $data Data to convert
     * @param string $type Array type
     * @return string|null
     * @throws \Voronoy\PgUtils\Exception\PgArrayInvalidParam
     */
    public static function toPg(?array $data, string $type = 'text'): ?string
    {
        if ($data === null) {
            return null;
        }
        $result = [];
        foreach ($data as $t) {
            if (is_array($t)) {
                $result[] = static::toPg($t, $type);
            } elseif ($t === null) {
                $result[] = 'NULL';
            } else {
                switch ($type) {
                    case 'bool':
                    case 'boolean':
                        $t = static::boolToPg($t);
                        break;
                    case 'int':
                    case 'integer':
                        if (is_numeric($t)) {
                            $t = (int)$t;
                        } else {
                            throw new PgArrayInvalidParam(sprintf('%s is not a properly formatted bool type.', $t));
                        }
                        break;
                    case 'float':
                    case 'numeric':
                        if (is_numeric($t)) {
                            $t = (float)$t;
                        } else {
                            throw new PgArrayInvalidParam(sprintf('%s is not a properly formatted bool type.', $t));
                        }
                        break;
                    case 'text':
                    default:
                        $t = '"' . str_replace('"', '\\"', $t) . '"';
                        break;
                }
                $result[] = $t;
            }
        }

        return '{' . implode(',', $result) . '}';
    }

    /**
     * Convert to PostgreSQL boolean.
     *
     * @param mixed $value Value to convert
     * @return int|null
     * @throws \Voronoy\PgUtils\Exception\PgArrayInvalidParam
     */
    public static function boolToPg($value): ?int
    {
        if ($value === null) {
            return null;
        }
        if (in_array($value, [true, 1, '1'], true)) {
            return 1;
        }
        if (in_array($value, [false, 0, '0', ''], true)) {
            return 0;
        }
        if (is_string($value)) {
            $value = strtolower($value);
            if (in_array($value, ['true', 'yes', 't'])) {
                return 1;
            }
            if (in_array($value, ['false', 'no', 'f'])) {
                return 0;
            }
        }
        throw new PgArrayInvalidParam(sprintf('%s is not a properly formatted bool type.', $value));
    }
}
