<?php

namespace App;

use App\User\Server;
use App\User\ServerGroup;
use Symphograph\Bicycle\Errors\AppErr;

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

    /**
     * @return ServerGroup[]
     */
    public static function getServerGroups(): array
    {
        $Servers = self::getList();
        $groups = [];
        foreach ($Servers as $server){
            $groups[$server->groupId][] = $server;
        }
        $groupList = [];
        foreach ($groups as $groupId => $group){
            $ServerGroup = new ServerGroup();
            $ServerGroup->id = $groupId;
            $ServerGroup->servers = $group;
            $ServerGroup->initLabel();
            $groupList[] = $ServerGroup;
        }
        return $groupList;
    }

}