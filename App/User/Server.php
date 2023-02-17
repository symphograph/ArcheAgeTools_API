<?php

namespace App\User;

use PDO;

class Server
{
    public int $id;
    public string $name;
    public int $group;

    public function __set(string $name, $value): void{}

    public static function byId(int $id): self|bool
    {
        $qwe = qwe("select * from servers where id = :id", ['id' => $id]);
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        return $qwe->fetchObject(self::class);
    }

    /**
     * @return array<self>
     */
    public static function byGroup(int $group): array
    {
        $qwe = qwe("select * from servers where `group` = :group", ['group' => $group]);
        if(!$qwe || !$qwe->rowCount()){
            return [];
        }
        return $qwe->fetchAll(PDO::FETCH_CLASS,self::class);
    }

    /**
     * @return array<self>|false
     */
    public static function getList(): array|false
    {
        $qwe = qwe("select * from servers order by `group`");
        if(!$qwe || !$qwe->rowCount()){
            return false;
        }
        return $qwe->fetchAll(PDO::FETCH_CLASS,self::class);
    }

    public static function getGroup(int $serverId): false|int
    {
       if(!($Server = Server::byId($serverId))){
           return false;
       }
       return $Server->group;
    }
}