<?php

namespace App\User;

class NickNameGenerator
{
    public static function getNickName($size = 0): string
    {
        if (!$size) {
            $size = rand(2, 4);
        }
        $scheme = ['opened'];
        for ($i = 1; $i < $size; $i++) {
            $scheme[] = self::arr_rand_item(['closing', 'closed']);
        }

        if (rand(0, 1)) {
            $scheme[] = self::arr_rand_item(['closing', 'closed', 'opened', 'finish']);
        }

        $name = '';
        foreach ($scheme as $syl) {
            $name .= self::form_syllable($syl);
        }

        $enc = 'utf-8';
        return mb_strtoupper(mb_substr($name, 0, 1, $enc), $enc) . mb_substr($name, 1, mb_strlen($name, $enc), $enc);
    }

    private static function probability(array $array): bool|int|string
    {
        $max = array_sum($array);
        $rand = mt_rand(0, $max);
        $limit = 0;
        $result = false;
        foreach ($array as $key => $item) {
            $limit += $item;
            if ($rand <= $limit) {
                $result = $key;
                break;
            }
        }
        return $result;
    }

    private static function arr_rand_item($arr)
    {
        return $arr[array_rand($arr)];
    }

    private static function form_syllable($type = 'any'): string
    {
        $ltt = [
            'vw' => [

                'у' => 10, 'е' => 50, 'ы' => 3, 'а' => 100, 'о' => 30, 'э' => 1, 'я' => 3, 'и' => 20, 'ю' => 10
            ],
            'sn' => [
                'ц' => 1, 'к' => 40, 'н' => 40, 'г' => 10, 'ш' => 5, 'щ' => 3, 'з' => 10, 'х' => 3, 'ф' => 3, 'в' => 30, 'п' => 20, 'р' => 10, 'л' => 30, 'д' => 10, 'ж' => 10, 'ч' => 3, 'с' => 30, 'м' => 25, 'т' => 15, 'б' => 20
            ],
            'et' => [
                'й' => 0, 'ь' => 30,
            ],
        ];

        $schemes = [
            'closing' => ['vw', 'sn'],
            'closed'  => ['sn', 'vw', 'sn'],
            'opened'  => ['sn', 'vw'],
            'finish'  => ['sn', 'vw', 'sn', 'et']
        ];

        if (!in_array($type, array_keys($schemes))) {
            $type = array_rand($schemes);
        }

        $syllable = '';

        foreach ($schemes[$type] as $letter) {
            $syllable .= self::probability($ltt[$letter]);
        }

        return $syllable;
    }
}