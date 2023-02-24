<?php

namespace App;
use Error;
use JetBrains\PhpStorm\NoReturn;

class Api
{
    const Monthes = ['', 'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];



    public static function monthNumByName(string $month): bool|int|string
    {
        return array_search($month, self::Monthes);
    }

    public static function emptyArr() : array
    {
        return [];
    }

    public static function curl(string $plink, array $post): bool|string
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl,CURLOPT_POST,1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        curl_setopt($curl, CURLOPT_FAILONERROR, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); // allow redirects
        curl_setopt($curl, CURLOPT_TIMEOUT, 10); // times out after 4s
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // return into a variable
        curl_setopt($curl, CURLOPT_URL, $plink);
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.1.5) Gecko/20091102 Firefox/3.5.5 GTB6");
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $somePage = curl_exec($curl);
        curl_close($curl);
        return $somePage;
    }

    public static function get($key, $default=NULL) {
        return array_key_exists($key, $_GET) ? $_GET[$key] : $default;
    }

    public static function session($key, $default=NULL) {
        return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : $default;
    }

}