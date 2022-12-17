<?php


class Api
{
    const Monthes = ['', 'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
    public static function errorMsg(string $msg='Неизвестная ошибка') : string|bool
    {
        return json_encode(['error'=>$msg],JSON_UNESCAPED_UNICODE);
    }

    public static function resultMsg(string|array $msg = 'Готово'): bool|string
    {
        //global $User;
        //LogAction::putToDb($User->id, $msg);
        return json_encode(['result'=>$msg],JSON_UNESCAPED_UNICODE);
    }

    public static function resultData(array|object $data, string|array $msg = 'Готово'): bool|string
    {
        //global $User;
        //LogAction::putToDb($User->id, $msg);
        return json_encode(['result'=>$msg,'data' => $data],JSON_UNESCAPED_UNICODE);
    }

    public static function monthNumByName(string $month): bool|int|string
    {
        return array_search($month, self::Monthes);
    }

    public static function emptyArr() : array
    {
        return [];
    }

}