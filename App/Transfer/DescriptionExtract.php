<?php

namespace App\Transfer;

class DescriptionExtract
{
    public static function extract(string $targetArea): string|false
    {
        return match (true){
            str_contains($targetArea, 'не нужен торговцам') => self::asUnTradable($targetArea),
            default => self::asDefault($targetArea)
        };
    }

    private static function asUnTradable(string $targetArea): string|false
    {
        $regExp = '#<hr class="hr_long">(.+?)<span class="notforsale">Этот предмет не нужен торговцам.</span>#is';
        if(!preg_match_all($regExp, $targetArea, $arr)){
            return false;
        }
        if(empty($descr = $arr[0][0] ?? '')){
            return false;
        }
        $descr = self::sanitizeDescription($descr);
        return str_replace('Ячейки для гравировки:<br>','', $descr);
    }


    private static function asDefault(string $targetArea): string|false
    {
        $regExp = '#<hr class="hr_long">(.+?)Цена#is';
        if(!preg_match_all($regExp, $targetArea, $arr)){
            return false;
        }
        $description = self::sanitizeDescription($arr[1][0] ?? '');
        $description = explode('Изготовление',$description);
        $description = $description[0];
        $description = explode('Стоимость:',$description);
        $description = $description[0];
        return str_replace('Ячейки для гравировки:<br>','',$description);
    }

    private static function sanitizeDescription(string $string): string
    {
        $string = str_replace('<hr class="hr_long">', '<br>',$string);
        $string = strip_tags($string,'<br>');
        $string = str_replace('123Ячейки для гравировки:', '',$string);
        $string = preg_replace('/[^0-9A-Za-zА-яёЁ ,.()\]\[_:«»\-?<br>]/ui', '',$string);
        return trim($string);
    }
}