<?php
declare(strict_types=1);

namespace Voronoy\PgUtils;

/**
 * Convert PostgreSQL array to PHP array.
 *
 * @param string|null   $data      Data to convert
 * @param callable|null $processor Additional processor
 * @return array|null
 */
function parse_pg_array(?string $data, ?callable $processor = null): ?array
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
            $return[] = parse_pg_array($openBraces . $part . $closeBraces, $processor);
        }

        return $return;
    } else {
        return array_map(function ($value) use ($processor) {
            if (strtolower($value) === 'null') {
                return null;
            } elseif (is_callable($processor)) {
                return call_user_func($processor, $value);
            } else {
                return stripcslashes($value);
            }
        }, str_getcsv(trim($data, '{}')));
    }
}

/**
 * Convert PHP array to PostgreSQL array.
 *
 * @param array|null $data Data to convert
 * @return string|null
 */
function to_pg_array(?array $data): ?string
{
    if ($data === null) {
        return null;
    }
    $result = [];
    foreach ($data as $t) {
        if (is_array($t)) {
            $result[] = to_pg_array($t);
        } else {
            if ($t === null) {
                $t = 'NULL';
            } else {
                $t = str_replace('"', '\\"', $t);
                if (!is_numeric($t)) {
                    $t = '"' . $t . '"';
                }
            }
            $result[] = $t;
        }
    }

    return '{' . implode(',', $result) . '}';
}
