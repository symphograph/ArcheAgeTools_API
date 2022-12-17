<?php

namespace User;

use PDO;

class Pers
{
    public int $id;
    public int $user_id;
    public string $nickName = 'Не авторизован';
    public int $grade = 1;
    public string $ava;
    public int $server_id;

    public function __set(string $name, $value): void{}

    public static function byId(int $id): self|bool
    {
        $qwe = qwe("select * from user_perses where id = :id", ['id'=>$id]);
        if(!$qwe || !$qwe->rowCount()) {
            return false;
        }
        return $qwe->fetchObject(self::class);
    }

    /**
     * @return array<self>|bool
     */
    public static function byUser(int $user_id): array|null
    {
        $qwe = qwe("select * from user_perses where user_id = :user_id", ['user_id'=>$user_id]);
        if(!$qwe || !$qwe->rowCount()) {
            return null;
        }
        return $qwe->fetchAll(PDO::FETCH_CLASS, self::class);
    }
}