<?php

namespace App\Server;

use Symphograph\Bicycle\DTO\AbstractList;

class ServerList extends AbstractList
{
    public static ServerList $serverList;

    /**
     * @var Server[]
     */
    public array $list;

    public static function getItemClass(): string
    {
        return Server::class;
    }

    public static function all(): static
    {
        if(isset(self::$serverList)) {
            return self::$serverList;
        }
        $sql = "select * from servers order by groupId";
        self::$serverList = static::bySql($sql);
        return self::$serverList;
    }

    /**
     * @return Server[]
     */
    public function getList(): array
    {
        return $this->list;
    }

    /**
     * @return ServerGroup[]
     */
    public function getServerGroups(): array
    {
        $groups = [];
        foreach ($this->list as $server){
            $groups[$server->groupId][] = $server;
        }
        $groupList = [];
        foreach ($groups as $groupId => $group){
            $groupList[] = new ServerGroup($groupId, $group);
        }
        return $groupList;
    }

}