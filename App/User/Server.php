<?php

namespace App\User;

use App\ServerList;
use PDO;
use Symphograph\Bicycle\Errors\AppErr;

class Server
{
    public int $id;
    public string $name;
    public int    $groupId;

    public function __set(string $name, $value): void{}

    public static function byId(int $id): self|bool
    {
        $qwe = qwe("select * from servers where id = :id", ['id' => $id]);
        return $qwe->fetchObject(self::class);
    }

    /**
     * @return self[]
     */
    public static function byGroup(int $groupId): array
    {
        $qwe = qwe("select * from servers where groupId = :groupId", ['groupId' => $groupId]);
        return $qwe->fetchAll(PDO::FETCH_CLASS,self::class) ?? [];
    }

    /**
     * @return self[]|false
     */
    public static function getList(): array|false
    {
        $qwe = qwe("select * from servers order by groupId");
        $Servers = $qwe->fetchAll(PDO::FETCH_CLASS,self::class);
        if(empty($Servers)) throw new AppErr('Servers is empty');
        return $Servers;
    }

    public static function getGroupId(int $serverId): false|int
    {
        $Server = ServerList::getServerById($serverId);
        return $Server->groupId;
    }
}