<?php
declare(strict_types=1);

namespace PgUtils;

function parse_pg_array(?string $data, ?callable $callback = null): ?array
{
    if (empty($data) || $data[0] !== '{') {
        return null;
    }
    $return = [];
    $depth  = 0;
    for ($i = 0; $i < strlen($data); $i++) {
        if ($data[$i] === '{') {
            $depth++;
        } else {
            break;
        }
    }
    if ($depth >= 2) {
        $closeBraces = str_repeat('}', $depth - 1);
        $openBraces  = str_repeat('{', $depth - 1);
        $delimiter   = $closeBraces . ',' . $openBraces;
        $string      = substr($data, $depth, -$depth);
        $parts       = explode($delimiter, $string);
        foreach ($parts as $part) {
            $return [] = parse_pg_array($openBraces . $part . $closeBraces, $callback);
        }

        return $return;
    } else {
        return array_map(function ($value) use ($callback) {
            if (strtolower($value) === 'null') {
                return null;
            } elseif (is_callable($callback)) {
                return call_user_func($callback, $value);
            } else {
                return stripcslashes($value);
            }
        }, str_getcsv(trim($data, '{}')));
    }
}

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