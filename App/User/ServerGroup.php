<?php

namespace App\User;

class ServerGroup
{
    public int $id;
    public array $servers;

    public static function byId(int $serverGroupId)
    {
        $qwe = qwe("
            select * from servers 
            where `group` = :serverGroupId",
            ['serverGroupId' => $serverGroupId]
        );

    }
}