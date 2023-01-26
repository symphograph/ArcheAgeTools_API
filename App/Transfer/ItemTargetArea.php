<?php

namespace App\Transfer;

class ItemTargetArea extends TargetArea
{
    public static function checkItemId(string $targetArea, int $itemId): bool
    {
        $regExp = '#ID: (.+?)td>#is';
        if(!preg_match_all($regExp, $targetArea, $arr)){
            return false;
        }
        return $itemId === self::sanitizeInt($arr[1][0] ?? '');
    }

    public static function extractItemName(string $targetArea): string
    {
        $regExp = '#id="item_name"(.+?)</span>#is';
        if(!preg_match_all($regExp, $targetArea, $arr)){
            return false;
        }
        return self::sanitizeItemName($arr[1][0] ?? '');
    }

    public static function isUnnecessary(string $itemName): bool
    {
       return !!preg_match('/deprecated|test|Тест: |тестовый|NO_NAME|Не используется/ui', $itemName);
    }

    public static function extractGrade(string $targetArea): int
    {
        $regExp = '#item_grade_(.+?)id#is';
        if(!preg_match_all($regExp, $targetArea, $arr)){
            return 1;
        }
        $grade = self::sanitizeInt($arr[1][0] ?? '');
        if(self::isIntInRange($grade, 1, 12)){
            return $grade;
        }
        return 1;
    }

    public static function extractCategoryName(string $targetArea): string
    {
        $regExp = '#<td class="item-icon">(.+?)<br>#is';
        if(!preg_match_all($regExp, $targetArea, $arr)){
            return false;
        }
        return self::lettersOnly($arr[1][0] ?? '');
    }

    public static function extractPrice(string $targetArea, string $regExp): false|int
    {
        if(!preg_match_all($regExp, $targetArea, $arr)){
            return false;
        }
        return self::sanitizeInt($arr[1][0] ?? '');
    }

    public static function extractCurrencyId(string $targetArea): false|int
    {
        $regExp = '#Цена покупки:(.+?)</tr>#is';
        if(!preg_match_all($regExp, $targetArea, $arr)){
            return false;
        }
        if (empty($arr[1][0])){
            return false;
        }
        $priceTypeArea = $arr[1][0];
        return match (true){
            str_contains($priceTypeArea, 'alt="bronze"') => 500, //gold
            str_contains($priceTypeArea, 'alt="lp"') => 3, //Ремесленная репутация
            str_contains($priceTypeArea, 'alt="honor_point') => 4, //Честь
            str_contains($priceTypeArea, 'item--23633') => 23633, //Дельфийская звезда
            str_contains($priceTypeArea, 'item--25816') => 25816, //Коллекционная монета «Джин»
            str_contains($priceTypeArea, 'item--26921') => 26921, //Звездный ролл
            str_contains($priceTypeArea, 'item--8001661') => 8001661, //Арткоин
            default => 0
        };
    }
}