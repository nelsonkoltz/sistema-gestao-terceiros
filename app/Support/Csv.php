<?php

namespace App\Support;

class Csv
{
    public static function safeRow(array $values): array
    {
        return array_map(function ($value) {
            $value = (string) ($value ?? '');
            return preg_match('/^[\s\x00-\x1f]*[=+@-]/u', $value) ? "'".$value : $value;
        }, $values);
    }
}
