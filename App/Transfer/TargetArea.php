<?php

namespace App\Transfer;

class TargetArea
{
    protected static function sanitizeInt(string $string): int
    {
        $string = strip_tags($string);
        $string = preg_replace('/[^0-9]/ui', '',$string);
        return intval($string);
    }

    protected static function sanitizeItemName(string $string): string
    {
        $string = strip_tags($string);
        $string = preg_replace('/[^0-9a-zA-Zа-яА-ЯёЁ ,.(+)\'`\]\[_:«»\-]/ui', '',$string);
        return trim($string);
    }

    protected static function isIntInRange($value, int $min, int $max): bool
    {
        return is_int($value) && ($min <= $value) && ($value <= $max);
    }

    protected static function lettersOnly(string $string): string
    {
        $string = strip_tags($string);
        $string = preg_replace('/[^a-zA-Zа-яА-ЯёЁ ]/ui', '',$string);
        return trim($string);
    }


}