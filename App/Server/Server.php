<?php

namespace App\Server;

use PDO;
use Symphograph\Bicycle\Errors\AppErr;

class Server
{
    public int $id;
    public string $name;
    public int    $groupId;

    public static function byId(int $id): self|bool
    {
        $qwe = qwe("select * from servers where id = :id", ['id' => $id]);
        return $qwe->fetchObject(self::class);
    }

    public static function byIdFromMemory(int $id): self
    {
        $servers = ServerList::all()->getList();
        foreach ($servers as $server){
            if ($server->id === $id) {
                return $server;
            }
        }
        throw new AppErr("Server $id does not exist in SelverList");
    }

    public static function getGroupId(int $serverId): int
    {
        $Server = static::byIdFromMemory($serverId);
        return $Server->groupId;
    }
}