<?php

namespace App\Transfer;

use Symphograph\Bicycle\Helpers;

class TargetArea
{
    public string $error = '';

    public function __construct(public string $content)
    {
    }

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

    protected static function lettersOnly(string $string): string
    {
        $string = strip_tags($string);
        $string = preg_replace('/[^a-zA-Zа-яА-ЯёЁ ]/ui', '',$string);
        return trim($string);
    }

    protected static function isEmptyTag(string $string): bool
    {
        $string = strip_tags($string);
        $string = trim($string);
        return empty($string);
    }

    protected static function sanitizeDateTime(string $string): string
    {
        $string = strip_tags($string);
        $string = trim($string);
        if(!Helpers::isDate($string, 'Y-m-d H:i:s')){
           return '';
        }
        return $string;
    }


}