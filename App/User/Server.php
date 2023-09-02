<?php

namespace App\User;

use App\ServerList;
use PDO;
use Symphograph\Bicycle\Errors\AppErr;

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
     * @return self[]
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
     * @return self[]|false
     */
    public static function getList(): array|false
    {
        $qwe = qwe("select * from servers order by `group`");
        $Servers = $qwe->fetchAll(PDO::FETCH_CLASS,self::class);
        if(empty($Servers)) throw new AppErr('Servers is empty');
        return $Servers;
    }

    public static function getGroupId(int $serverId): false|int
    {
        $Server = ServerList::getServerById($serverId);
        return $Server->group;
    }
}