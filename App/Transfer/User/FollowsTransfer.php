<?php

namespace App\Transfer\User;

use App\ServerList;
use App\User\AccSettings;
use App\User\Member;
use Symphograph\Bicycle\Logs\ErrorLog;


class FollowsTransfer
{
    public static function import(int $accountId, array $masters, int $serverGroup): void
    {

        foreach ($masters as $master){
           //Member::setFollow($accountId, $master, $serverGroup);
            $masterAccSets = AccSettings::byOldId($master);
            try{
                Member::setFollow($accountId, $masterAccSets->accountId, $serverGroup);
            } catch (\Throwable $err) {
                ErrorLog::writeToLog($err);
                //Response::data([$accountId, $master, $serverGroup]);
            }

        }
    }

    public static function toAllServerGroups(int $accountId, array $masters): void
    {
        $Servers = ServerList::getList();
        foreach ($Servers as $server){
            self::import($accountId, $masters, $server->group);
        }
    }
}