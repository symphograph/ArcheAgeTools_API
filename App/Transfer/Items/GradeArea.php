<?php

namespace App\Transfer\Items;

use App\Transfer\TargetArea;

class GradeArea extends TargetArea
{
    public function __construct(string $content)
    {
        parent::__construct($content);
    }

    public static function extractSelf(string $page): self|false
    {
        $regExp = '#<div class="insider align_center">(.+?)</div>#is';
        preg_match_all($regExp, $page, $arr);

        return empty($arr[0][0]) ? false : new self($arr[0][0]);
    }

    public static function sanitizeGrade(string $string): string
    {
        $string = strip_tags($string);
        $string = preg_replace('/[^0-9]/ui', '',$string);
        return trim($string);
    }
}