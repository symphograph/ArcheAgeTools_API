<?php

namespace App\Transfer\User;

use App\User\AccSets;
use App\User\Member\Member;
use Symphograph\Bicycle\Logs\ErrorLog;
use Throwable;


class FollowsTransfer
{
    public static function import(int $accountId, array $masters, int $serverGroup): void
    {

        foreach ($masters as $master){
            $masterAccSets = AccSets::byOldId($master);
            try{
                Member::setFollow($accountId, $masterAccSets->accountId, $serverGroup);
            } catch (Throwable $err) {
                ErrorLog::writeToLog($err);
            }

        }
    }
}