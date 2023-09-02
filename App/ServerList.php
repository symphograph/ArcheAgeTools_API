<?php

namespace App;

use App\User\Server;
use Symphograph\Bicycle\Errors\AppErr;
use Symphograph\Bicycle\Helpers;

class ServerList
{
    /**
     * @var Server[]
     */
    public array $list;

    public static function getSelf()
    {
        global $ServerList;
        if(isset($ServerList)){
            return $ServerList;
        }
        $ServerList = new self();
        $ServerList->list = Server::getList();
        return $ServerList;
    }

    /**
     * @return Server[]
     */
    public static function getList(): array
    {
        return self::getSelf()->list;
    }

    public static function getServerById(int $serverId): Server
    {
        $list = self::getList();
        $filtered = array_filter($list, fn($server) => $server->id === $serverId);
        foreach ($filtered as $server){
            return $server;
        }
        throw new AppErr("Server $serverId does not exist in SelverList");
    }

}